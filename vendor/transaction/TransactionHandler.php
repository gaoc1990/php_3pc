<?php 

/**
 * 
 */
class TransactionHandler 
{
	
	protected $transGroup;
	protected $transaction;

	protected $sw_client;

	function __construct()
	{
	}

	protected function initTransDb($db = null){
		//获取当前系统设置的数据库操作对象
		$config = ConfigHelper::get('tx');
		
		if( empty($config['class']) || empty($config['trans_begin']) || empty($config['commit']) || empty($config['rollback'])){
			throw new Exception("error TxDatabase config", 1);
		}

		if(get_class($db) != $config['class']){
			throw new Exception("error db type", 1);
		}

		$this->transDb = $db;
		$this->trans_begin = $config['trans_begin'];
		$this->trans_commit = $config['commit'];
		$this->trans_rollback = $config['rollback'];
	}

	protected function initSwooleClient(){
		$this->sw_client = new TxClientManage();
	}

	protected function initTxGroup($db = null){
		$groupId = isset($_REQUEST['groupId'])?$_REQUEST['groupId']:"";

		//创建事务组信息
        $transGroup = new TransactionGroup($groupId);
        $propagation = $db ? Constant::$propagation_have : Constant::$propagation_naver;
        $role = $groupId? Constant::$txgroup_role_actor: Constant::$txgroup_role_starter;

        //添加发起者
        $transaction = new Transaction();
        $transaction->transId 		= Util::createStarterId($transGroup->groupId);
        $transaction->role 		= $role;
        $transaction->status 		= Constant::$tx_status_begin;
        $transaction->groupId 		= $transGroup->groupId;
        $transaction->waitMaxTime 	= 3;
        $transaction->createTime 	= time();
        $transaction->propagation 	= $propagation;

        //设置事务执行类和事务补偿类（反射执行）
        $invocation = new Invocation();
        $transaction->invocation = $invocation;

        $this->transGroup = $transGroup;
        $this->transaction = $transaction;
	}
	
	/**
	 * 发起者注册事务组
	 * @return [type] [description]
	 */
	public function addTxGroup(){
        //封装请求数据
        $socketData = new TxSocketData();
        $socketData->transGroup = $this->transGroup;
        $socketData->action = Constant::$socket_action_starttrans;
        $socketData->transaction = $this->transaction;

        $this->sw_client->connect();
        $this->sw_client->sendMsg($socketData);

        $ret = $this->sw_client->recv();
        if($ret->result == Constant::$tx_complete_ok){
            return $this->transGroup;
        }else{
            return false;
        }
	}

	/**
	 * 注册事务参与者
	 * @return [type] [description]
	 */
	protected function registTransaction(){
        //封装socket数据
        $socketData = new TxSocketData();
        $socketData->transGroup = $this->transGroup;
        $socketData->action = Constant::$socket_action_regtxactor;
        $socketData->transaction = $this->transaction;

        $this->sw_client->connect();
        $this->sw_client->sendMsg($socketData);

        $ret = $this->sw_client->recv();

        if($ret->result == Constant::$tx_complete_ok){
            return $this->transaction;
        }else{
            return false;
        }

	}

	/**
	 * 发起者发起precommit
	 * @return [type] [description]
	 */
	protected function preCommit(){
		//发送
		$socketData = new TxSocketData();
		$socketData->transGroup = $this->transGroup;
		$socketData->action = Constant::$socket_action_precommit;
		$socketData->transaction = $this->transaction;

		$ret = $this->sw_client->sendMsg($socketData);
		if(!$ret){
			//rollback
			Log::getInstance()->error("starter: send precommit failure , will rollback");
		}else{
			$result = $this->sw_client->recv();
			
			if(!empty($result) && $result->result == Constant::$tx_complete_ok){

				$this->transaction->status = Constant::$tx_status_precommit;
				return true;
			}else{
				//rollback
				Log::getInstance()->error("starter: precommit recv  response failure");
			}
		}
		$this->rollback();
		$this->transaction->status = Constant::$tx_status_rollback;

		return false;
	}

	/**
	 * 发起者发起docommit
	 * 
	 * @return [type] [description]
	 */
	protected function doCommit(){
		//发送指令
		$socketData = new TxSocketData();
		$socketData->transGroup = $this->transGroup;
		$socketData->transaction = $this->transaction;
		$socketData->action = Constant::$socket_action_commit;

		$ret = $this->sw_client->sendMsg($socketData);
		if(!$ret){
			//发送commit失败，位链接上
			Log::getInstance()->error("starter: send commit msg failure, local transaction will commit");
		}else{
			//接收commit回复
			$result = $this->sw_client->recv();
			if(!$result){
				//接收超时或失败
				Log::getInstance()->error("starter: wait commit response timeout");
			}
			if($result->result == Constant::$tx_complete_fail){
				Log::getInstance()->error("starter: txmansge preCommit failure");
			}
		}

		//提交本地事务
		$this->transDb->{$this->trans_commit}();
		$this->transaction->status = Constant::$tx_status_commit;

		return true;
	}
	/**
	 * 发起回滚
	 * @return [type] [description]
	 */
	protected function doRollback(){
		if($this->transaction->role == Constant::$txgroup_role_starter)
		{
			//发送
			$socketData = new TxSocketData();
			$socketData->transGroup = $this->transGroup;
			$socketData->action = Constant::$socket_action_rollback;
			$socketData->transaction = $this->transaction;
			$ret = $this->sw_client->sendMsg($socketData);
			if(!$ret){
				//rollback
				Log::getInstance()->error("starter: send rollback failure , local rollback");
			}

		}
		Log::getInstance()->error("local rollback");
		//本地事务回滚
		$this->transDb->{$this->trans_rollback}();
		$this->transaction->status = Constant::$tx_status_rollback;

		return true;
	}


	/**
	 * 参与者等待调用
	 * @return [type] [description]
	 */
	protected function wait(){
		$socketData = $this->sw_client->recv();
		if(!$socketData || $socketData->action != Constant::$socket_action_precommit)
		{
			//没有等到pre_commit
			$this->transaction->status = Constant::$tx_status_rollback;
			//超时或者不是正常事务操作视为超时，直接本地事务回滚操作
			$this->tranDb->{$this->trans_rollback}();
			Log::getInstance()->info("actor: wait precommit timeout ");
			return false;

		}
		else if($socketData->action == Constant::$socket_action_precommit)
		{
			//判断事务状态
			if($this->transaction->status != Constant::$tx_status_cancommit){
				$socketData->result = Constant::$tx_complete_ok;
				$socketData->action = Constant::$socket_result_precommit;
				$this->sw_client->sendMsg($socketData);

				Log::getInstance()->info("actor: preCommit response failure");
				return false;
			}

			Log::getInstance()->info("actor: preCommit response ok");
			//设置事务状态
			$this->transaction->status = Constant::$tx_status_precommit;

			$socketData->result = Constant::$tx_complete_ok;
			$socketData->action = Constant::$socket_result_precommit;
			$this->sw_client->sendMsg($socketData);
		}

		$command_commit = $this->sw_client->recv();
		if(!$command_commit || $command_commit->action == Constant::$socket_action_commit)
		{
			if($this->transaction->status == Constant::$tx_status_precommit){
				$this->transDb->{$this->trans_commit}();
				$this->transaction->status = Constant::$tx_status_commit;
			}
		}
		else if($command_commit->action == Constant::$socket_action_rollback)
		{
			$this->transDb->{$this->trans_rollback}();
			$this->transaction->status = Constant::$tx_status_rollback;
		}

		return true;
	}

}