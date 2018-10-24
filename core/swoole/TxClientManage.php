<?php 

/**
 * 客户端集合代码
 */
class TxClientManage extends SocketMsgSender
{
	private $swoole_client;
	private $sw_client_config;
	
	public function __construct()
	{
		$config = ConfigHelper::get('tx.txmanage');
		if(empty($config['host'])) {
			throw new Exception("Error txmanage config", 1);
		}
		$this->sw_client_config = $config;
		$this->swoole_client = new Swoole\Client();
	}

	public function connect($config){
		if(!empty($config)){
			$this->sw_client_config = array_merge($this->sw_client_config,$config);
		}

		//同步模式连接（是够考虑设置重试）
		$isconnected = $this->swoole_client->connect($config['host'],$config['port'],$config['timeout'],0);
		if(!$isconnected){
			throw new Exception("cannot connect txmanage server", 1);
		}

		return $isconnected;
	}

	/**
	 * 发送数据与实务协调器通信
	 * @return [type] [description]
	 */
	public function sendMsg($socketData){
		$socketData->checkValid();

		//处理消息格式
		$strMsg = serialize($socketData);

		$msgLength = $this->swoole_client->send($socketData);
	}
	/**
	 * 添加事务组信息（发起者）
	 * @return [type] [description]
	 */
	public function addTxGroup(){

	}

	/**
	 * 注册事务参与者
	 * @return [type] [description]
	 */
	public function registTransaction(){

	}
	/**
	 * 等待接受事务管理器的指令
	 * @return [type] [description]
	 */
	public function receiveTransCommand(){

	}

}