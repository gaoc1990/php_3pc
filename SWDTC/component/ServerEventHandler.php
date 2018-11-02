<?php 

require_once BASE_DIR ."/component/TransactionManage.php";
require_once BASE_DIR ."/component/TransGroupManage.php";


/**
 * 事务管理器处理外部事务请求
 */
class ServerEventHandler
{
	
	private static $obj = null;

	private $server = null;


	function __construct($server = null)
	{
		$this->server = $server;	
	}
	/**
	 * 获取单例
	 * @return ServerEventHandler
	 */
	public static function getInstance($server = null){
		if(is_null(self::$obj)){
			self::$obj = new ServerEventHandler($server);
		}
		return self::$obj;
	}

	/**
	 * 接收
	 * @param  [type] $serv    [description]
	 * @param  [type] $fd      [description]
	 * @param  [type] $from_id [description]
	 * @param  [type] $data    [description]
	 * @return [type]          [description]
	 */
	public function dealReceive($serv, $fd, $from_id, $data){
		//解析data数据
		$socketData = TxSocketData::fromMsg($data);
		$groupId = $socketData->transGroup->groupId;

		if(!$socketData->checkValid()){
			throw new Exception("error msg format", 10001);
		}

		switch ($socketData->action) {
			case Constant::$socket_action_regtxactor:

				Log::getInstance()->info("txmanage register transaction");
				try{
					//判断是否有事务
					$transStatus = TransGroupManage::getInstance()->getGroupStatus($groupId);

					if($transStatus != Constant::$tx_status_begin)
					{
						throw new Exception("regist actor to an error trans group",1);
					}
					$socketData->transaction->fd = $fd;
					$res = TransGroupManage::getInstance()->addTxTransaction($groupId, $socketData->transaction);
					if(!$res)
					{
						throw new Exception("regist actor error",1);
					}
					//成功返回
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_ok);
					$res = $this->sendMsg($fd, $socketData);

				}catch(Exception $e){
					Log::getInstance()->error($e->getMessage());
					//失败返回
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
					$res = $this->sendMsg($fd, $socketData);
				}
				Log::getInstance()->info("txmanage register transaction end");
				break;

			case Constant::$socket_action_starttrans:
				try
				{
					Log::getInstance()->info("txmanage add group info");

					if(empty($groupId)){
						throw new Exception("Error Processing Request", 10001);
					}
					//判断是否有事务
					$transStatus = TransGroupManage::getInstance()->getGroupStatus($groupId);
					if($transStatus){
						throw new Exception("groupid conflict", 1);
					}
					$socketData->transaction->fd = $fd;
					//添加事务组(redis存储)
					$res = TransGroupManage::getInstance()->saveTransGroup($socketData->transGroup, $socketData->transaction);

					if($res){
						Log::getInstance()->error("add group right");
						$socketData = TxSocketData::createResponse(Constant::$tx_complete_ok);
						$res = $this->sendMsg($fd, $socketData);
					}else{
						throw new Exception("add transGroup error",1);
					}		
				}
				catch(Exception $e)
				{
					Log::getInstance()->error($e->getMessage());
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
					$res = $this->sendMsg($fd, $socketData);
				}

				break;

			case Constant::$socket_action_precommit:
				{
					//更新redis状态为cancommit
					TransGroupManage::getInstance()->updateItemStatus($groupId, $socketData->transaction, Constant::$tx_status_cancommit);
					TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_cancommit);

					Log::getInstance()->info("txmanage precommit start");
					//查找事务组信息
					$transactionList = TransGroupManage::getInstance()->getItemsByGroupId($groupId);
					if(empty($transactionList))
					{
						$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
						$this->sendMsg($fd, $socketData);

						throw new Exception("commit cancel: have no transaction actor", 10001);
					}
					try
					{
						$transactionList = TransactionManage::getInstance()->precommit($socketData, $fd);
					}
					catch(Exception $e)
					{
						Log::getInstance()->error($e->getMessage());
						//通知发起者回滚
						$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
						$this->sendMsg($fd,$socketData);
					}
					Log::getInstance()->info("txmanage precommit end");
				}
				break;

			case Constant::$socket_action_commit:
				{
					Log::getInstance()->info("txmanage commit start");

					TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_commit);

					TransactionManage::getInstance()->commit($groupId);
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_ok);
					$this->sendMsg($fd, $socketData);

					Log::getInstance()->info("txmanage commit end");
				}
				break;

			case Constant::$socket_action_rollback:
				try{
					Log::getInstance()->info("txmanage rollback start");

					//查找事务组信息
					$transactionList = TransGroupManage::getInstance()->getItemsByGroupId($groupId);
					if(empty($transactionList)){
						throw new Exception("rollback cancel : have no transaction actor", 10001);
					}
					TransactionManage::getInstance()->rollback($groupId);

					//通知发起者回滚
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_ok);
					$this->sendMsg($fd,$socketData);

				}catch(Exception $e){
					Log::getInstance()->error($e->getMessage());
					//通知发起者回滚
					$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
					$this->sendMsg($fd,$socketData);
				}
				Log::getInstance()->info("txmanage rollback end");
				break;

			case Constant::$socket_result_precommit:
				{
					Log::getInstance()->info('txmanage:  actor response  precommit start, result:' . $socketData->result);

					$groupStatus = TransGroupManage::getInstance()->getGroupStatus($groupId);
					if($socketData->result == Constant::$tx_complete_fail ){
						if($groupStatus == Constant::$tx_status_cancommit){
							//设置事务组状态为rollback
							TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_rollback);
							$response= TxSocketData::createResponse(Constant::$tx_complete_fail);

							//给发起者发送失败消息
							$starter = TransGroupManage::getInstance()->getTransGroupStarter($groupId);
							$this->sendMsg($starter->fd, $socketData);
						}
					}else{

						TransGroupManage::getInstance()->updateItemStatus($groupId, $socketData->transaction, Constant::$tx_status_precommit);
						$count = TransGroupManage::getInstance()->statusCount($groupId,'precommit');
						if($count == 0){
							TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_precommit);
							//恢复成功
							$response = TxSocketData::createResponse(Constant::$tx_complete_ok);

							//给发起者发送成功消息
							$starter = TransGroupManage::getInstance()->getTransGroupStarter($groupId);
							$this->sendMsg($starter->fd, $response);
						}

					}
				}
				break;
			default:
				break;
		}
	}

	public function sendMsg($fd, $socketData){
		if(empty($this->server)){
			return false;
		}

		$socketData->checkValid();
		$strMsg = serialize($socketData) ."\r\n\r\n";
		$res = $this->server->send($fd, $strMsg);

		return $res;
	}

}