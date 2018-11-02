<?php 

/**
 * 分布式事务资源管理器Transaction类
 */
class TxTransaction extends TransactionHandler implements ITxTransaction
{

	public $transDb;
	public $trans_begin;
	public $trans_commit;
	public $trans_rollback;


	public function __construct($db = null){
		//初始化swool_client
		$this->initSwooleClient();
		//初始化本地事务对象
		$this->initTxGroup($db);
		
		//初始化数据库对象
		$this->initTransDb($db);
	}

	public function getTransGroup(){
		return $this->transGroup;
	}

	/**
	 * 开启本地事务
	 * @return [type] [description]
	 */
	public function begin(){
		//添加全局事务
		if($this->transaction->role == Constant::$txgroup_role_starter)	{
			$res = $this->addTxGroup();
			if(!$res){
				throw new Exception("create transGroup error", 1);
			}
		}
		//发起者开始事务
		$this->transDb && $this->transDb->{$this->trans_begin}();
		return $this->transDb;
	}

	/**
	 * 回滚
	 */
	public function rollback(){
		return $this->doRollback();
	}


	/**
	 * 等待提交指令
	 * @return [type] 
	 */
	public function commit()
	{
		if($this->transaction->role == Constant::$txgroup_role_actor)
		{
			//参与者业务执行成功
			$this->transaction->status = Constant::$tx_status_cancommit;
			//注册参与者到全局事务组
			$trans = $this->registTransaction();
			if(!$trans){
				//如果是fpm返回并继续执行
				if(PHP_SAPI == "fpm-fcgi" || PHP_SAPI == "cgi-fcgi"){
					echo Constant::$tx_complete_fail;
					$size=ob_get_length();  
			        header("Content-Length: $size");
			        header("Connection: Close");   
					// 刷新buffer
				    ob_flush();
				    flush();
				    // 断开浏览器连接
				    fastcgi_finish_request();
				}
				//注册失败回滚事务
				$this->rollback();
			}

			//如果是fpm返回并继续执行
			if(PHP_SAPI == "fpm-fcgi" || PHP_SAPI == "cgi-fcgi"){
				echo Constant::$tx_complete_ok;
				$size=ob_get_length();  
		        header("Content-Length: $size");
		        header("Connection: Close");   
				// 刷新buffer
			    ob_flush();
			    flush();
			    // 断开浏览器连接
			    fastcgi_finish_request();
			}
			Log::getInstance()->info('actor :  wait pre-commit');

			//同步阻塞等待调用
			$this->wait();
		}
		else if($this->transaction->role == Constant::$txgroup_role_starter)
		{
			$this->transaction->status = Constant::$tx_status_cancommit;
			Log::getInstance()->info("starter: pre-commit start ");
			//发起者commit说明准备阶段都执行成功
			//1.执行precommit
			$ret_precommit = $this->preCommit();
			if(!$ret_precommit){
				Log::getInstance()->info("starter: rollback start ");
				$this->rollback();
				return false;
			}
			//2.执行commit
			Log::getInstance()->info("starter: doCommit start ");
			$ret_commit = $this->doCommit();

			return $ret_commit;
		}
	}

}