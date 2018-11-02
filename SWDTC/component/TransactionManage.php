<?php 
/**
 * 事务操作
 */
class TransactionManage implements ITransactionManage
{
	
	private static $obj = null;

	public static function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new TransactionManage();
		}
		return self::$obj;
	}

	function __construct(){
		
	}

	/**
	 * 预提交
	 * @return [type] [description]
	 */
	public function precommit($socketData, $fd){
		$groupId = $socketData->transGroup->groupId;
		$list = TransGroupManage::getInstance()->getItemsByGroupId($groupId);
		if(empty($list)){
			throw new Exception("have no groupinfo", 1);
		}
		$hasPart = 0;
		foreach($list as $item){
			if($item->role == Constant::$txgroup_role_starter || $item->propagation == Constant::$propagation_naver){
				continue;
			}
			$hasPart = 1;
			if(App::$server->exist($item->fd)){
				//发送消息
				$ret = $this->sendMsg($item->fd, $socketData);
				if(!$ret){
					Log::getInstance()->error("txmanage: send precommit msg fail, fd: {$item->fd}");
				}else{
					Log::getInstance()->info("txmanage: send actor precommit");
				}
			}else{
				//记录补偿信息
				Log::getInstance()->error("txmanage: precommit, error actor's  fd info, fd: {$item->fd}");
			}
		}

		// if(!$hasPart){
		// 	TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_precommit);
		// 	$socketData = TxSocketData::createResponse(Constant::$tx_complete_ok);
		// 	$strMsg = serialize($socketData) ."\r\n\r\n";
		// 	App::$server->send($fd, $strMsg);
		// 	return true;
		// }

		//设置超时定时器
		$timer = swoole_timer_after(3000, function() use ($groupId, $fd) {
			$status =  TransGroupManage::getInstance()->getGroupStatus($groupId);
			if($status == Constant::$tx_status_precommit){
				Log::getInstance()->info('other workers already response precommit status');
			}else{
				//事务组已经超时，response fail 
				TransGroupManage::getInstance()->updateGroupStatus($groupId, Constant::$tx_status_rollback);
				$socketData = TxSocketData::createResponse(Constant::$tx_complete_fail);
				$strMsg = serialize($socketData) ."\r\n\r\n";
				App::$server->send($fd, $strMsg);
			}
		});
	}

	/**
	 * 遍历提交事务参与者
	 * @param  [type] $transactionList [description]
	 * @return [type]                  [description]
	 */
	public function commit($groupId){
		$socketData = new TxSocketData();
		$socketData->action = Constant::$socket_action_commit;
		$socketData->transGroup = new TransactionGroup($groupId);

		$list = TransGroupManage::getInstance()->getItemsByGroupId($groupId);
		foreach($list as $item){
			if($item->role == Constant::$txgroup_role_starter || $item->propagation == Constant::$propagation_naver){
				continue;
			}
			if(App::$server->exist($item->fd)){
				$socketData->transaction = $item;
				$ret = $this->sendMsg($item->fd, $socketData);
				if(!$ret){
					Log::getInstance()->error("txmanage: send commit msg fail, fd: {$item->fd}");
				}
			}else{
				//记录补偿信息
				Log::getInstance()->error("txmanage: commit, error actor's  fd info, fd: {$item->fd}");
			}
		}

		return true;
	}

	/**
	 * 遍历参与者回滚
	 * @param  [type] $groupId         [description]
	 * @param  array  $transactionList [description]
	 * @return [type]                  [description]
	 */
	public function rollback($groupId, $transactionList = array()){
		if(empty($transactionList)){
			//获取事务组参与者
			$list = TransGroupManage::getInstance()->getItemsByGroupId($groupId);
		}else{
			$list = $transactionList;
		}

		$socketData = new TxSocketData();
		foreach($list as $item){
			if($item->role = Constant::$txgroup_role_starter){
				continue;
			}
			//拿到连接并发送回滚消息
			if(App::$server->exist($item->fd)){
				$socketData->transGroup = new TransactionGroup($groupId);
				$socketData->transaction = $item;
				$socketData->action = Constant::$socket_action_rollback;

				//发送消息
				$ret = $this->sendMsg($item->fd, $socketData);
				if(!$ret){
					Log::getInstance()->error("txmanage: send rollback msg fail, fd: {$item->fd}");
				}
			}else{
				//fd无效
				Log::getInstance()->error("txmanage: rollback, error actor's  fd info, fd: {$item->fd}");
			}
		}

	}

	/**
	 * [sendMsg description]
	 * @param  [type] $fd         [description]
	 * @param  [type] $socketData [description]
	 * @return [type]             [description]
	 */
	public function sendMsg($fd, $socketData){
		if(empty(App::$server)){
			return false;
		}

		$socketData->checkValid();
		$strMsg = serialize($socketData) ."\r\n\r\n";
		$res = App::$server->send($fd, $strMsg);

		return $res;
	}

}