<?php 


/**
 * 事务操作
 */
class TransactionManage implements ITransactionManage
{
	
	function __construct(){
		
	}

	/**
	 * 预提交
	 * @return [type] [description]
	 */
	public function precommit($groupId){
		$list = TransGroupManage::getInstance()->listByTxGroupId($groupId);
		if(empty($list)){
			throw new Exception("have no groupinfo", 1);
		}
		foreach($list as $item){
			if(App::$server->exist($item->fd)){
				//发送消息
				$ret = SocketMsgSender::send($item->fd, $socketData);
				if(!ret){
					throw new Exception("send error", 10001);
				}
				return $list;
			}else{
				//记录补偿信息
				throw new Exception("connection error", 10001);
			}
		}

	}

	/**
	 * 遍历提交事务参与者
	 * @param  [type] $transactionList [description]
	 * @return [type]                  [description]
	 */
	public function commit($transactionList){
		$socketData = new SocketData();
		foreach($list as $item){
			if(App::$server->exist($item->fd)){
				//发送消息
				$ret = SocketMsgSender::send($item->fd, $socketData);
				if(!ret){
					//记录补偿信息
					
				}
			}else{
				//记录补偿信息
				
			}
		}
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
			$list = TransGroupManage::getInstance()->listByTxGroupId($groupId);
		}else{
			$list = $transactionList;
		}

		$socketData = new SocketData();
		foreach($list as $item){
			//拿到连接并发送回滚消息
			if(App::$server->exist($item->fd)){
				//发送消息
				$ret = SocketMsgSender::send($item->fd, $socketData);
				if(!ret){
					//记录补偿信息
					
				}
			}else{
				//记录补偿信息
				
			}
		}

	}

	public function compensation(){

	}

}