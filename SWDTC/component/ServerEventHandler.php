<?php 

/**
 * 事务管理器处理外部事务请求
 */
class ServerEventHandler
{
	
	private static $obj = null;
	function __construct()
	{
		# code...
	}
	/**
	 * 获取单例
	 * @return [type] [description]
	 */
	public function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new TxTransactionManage();
		}
		return self::$obj;
	}

	/**
	 * 处理websocket消息
	 * 
	 * @return [type] [description]
	 */
	public function dealMessage(swoole_server $serv, swoole_websocket_frame $frame){
		$fd = $frame->fd;
		$data = $frame->data;

		//解析data数据
		$socketData = SocketData::from($data);
		if(!$socketData->checkValid()){
			throw new Exception("error msg format", 10001);
		}

		switch ($socketData->action) {
			case Constant::$socket_action_regtxactor:
				try{
					$socketData->txTransaction->fd = $fd;
					$res = TxGroupComponent::getInstance()->addTxTransaction($socketData->txTransaction->txGroupId, $socketData->txTransaction);
				}catch(Exception $e){
					//失败返回
					
					SocketMsgSender::send();
				}
				break;
			default:
				break;
		}
	}
	/**
	 * 处理request消息
	 * 
	 * @return [type] [description]
	 */
	public function dealRequest(swoole_http_request $request, swoole_http_response $response){
		$groupId = $request->server['groupId'];
		$action = $request->server['action'];
		switch($action){
			case 'addGroupInfo':
				{
					//处理请求参数
					$uns_transGroup = $request->server['transGroup'];
					$transGroup = unserialize($uns_transGroup);
					if(empty($transGroup->groupId) || $groupId != $transGroup->groupId){
						throw new Exception("Error Processing Request", 10001);
					}
					//添加事务组(redis存储)
					TransGroupManage::getInstance()->saveTxTransactionGroup($transGroup);
					
					break;
				}

			case 'commit':
				{

					//查找事务组信息
					$transactionList = TransGroupManage::getInstance()->listByTxGroupId($groupId);
					if(empty($transactionList)){
						throw new Exception("have no transaction actor", 10001);
					}
					try{
						$transactionList = TransactionManage::getInstance()->precommit($groupId);
						TransactionManage::getInstance()->commit($transactionList);

					}catch(Exception $e){
						TransactionManage::getInstance()->rollback($groupId,$transactionList);
					}
					
				}
				break;

			case 'rollback':
				{

					//查找事务组信息
					$transactionList = TransGroupManage::getInstance()->listByTxGroupId($groupId);
					if(empty($transactionList)){
						throw new Exception("have no transaction actor", 10001);
					}
					TransactionManage::getInstance()->rollback($groupId);
					
				}
				break;

			default:
				break;
		}
	}

	/**
	 * 处理receive消息
	 * 
	 */
	public function dealRecieve($serv, $fd, $from_id, $data){
		return true;
	}

}