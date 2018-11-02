<?php 

/**
 * 同步客户端 操作类
 */
class TxClientManage
{
	public $swoole_client;
	public $sw_client_config;
	
	public function __construct()
	{
		$config = ConfigHelper::get('sw_client');
		if(empty($config['host'])) {
			throw new Exception("Error txmanage config", 1);
		}
		$this->sw_client_config = $config;
		$this->swoole_client = new Swoole\Client(SWOOLE_TCP);

	}

	public function connect($config = array()){
		if(!empty($config)){
			$this->sw_client_config = array_merge($this->sw_client_config,$config);
		}
		$this->swoole_client->set(array(
			'open_eof_check' => true,
    		'package_eof' => "\r\n\r\n",
    		'package_max_length' => 1024 * 1024 * 2
		));

		//同步模式连接（是够考虑设置重试）
		$isconnected = $this->swoole_client->connect($this->sw_client_config['host'],$this->sw_client_config['port'],$this->sw_client_config['timeout'],0);
		if(!$isconnected){
			throw new Exception("cannot connect txmanage server", 1);
		}

		return $isconnected;
	}

	/**
	 * 发送数据与实务协调器通信(socket)
	 * @return [type] [description]
	 */
	public function sendMsg($socketData){
		$socketData->checkValid();
		//格式化消息
		$strMsg = serialize($socketData);
		$res = $this->swoole_client->send($strMsg . "\r\n\r\n");
		return $res;
	}

	/**
	 * 回复成功
	 * @return [type] [description]
	 */
	public function responseOk(){
		$socketData = new TxSocketData();
		$socketData->result = Constant::$socket_result_success;
		$strMsg = serialize($socketData);

		$res = $this->swoole_client->send($strMsg . "\r\n\r\n");
		return $res;
	}

	/**
	 * 回复失败
	 * @return [type] [description]
	 */
	public function responseFail(){
		$socketData = new TxSocketData();
		$socketData->result = Constant::$socket_result_success;
		$strMsg = serialize($socketData);

		$res = $this->swoole_client->send($strMsg. "\r\n\r\n");
		return $res;
	}

	/**
	 * 接收消息
	 * @return [type] [description]
	 */
	public function recv(){
		$msg = $this->swoole_client->recv();
		if(!$msg){
			return false;
		}
		
		$socketData = TxSocketData::fromMsg($msg);

		return $socketData;
	}

}