<?php 

/**
 * socket消息发送类
 */
abstract class SocketMsgSender 
{
	/**
	 * swoole_client用于像服务端发送消息
	 * @var [type]
	 */
	protected $sw_client;

	/**
	 * socket服务雨来发送消息
	 * @var [type]
	 */
	protected $sw_server;

	/**
	 * 公共序列化方法
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function serialize($data){
		return $serialize($data);
	}
	/**
	 * 公共反序列化方法
	 * @param  [type] $msg [description]
	 * @return [type]      [description]
	 */
	public static function unserialize($msg){
		return unserialize($msg);
	}

	/**
	 * swoole服务器发送数据
	 * @param  [type]      $fd         [description]
	 * @param  ISocketData $socketData [description]
	 * @return [type]                  [description]
	 */
	public function server_send($fd, ISocketData $socketData){
		//验证消息格式
		$socketData->checkValid();

		$strMsg = static::serialize($socketData);
		$this->sw_client->send($strMsg);
	}
	/**
	 * swoole客户端像服务端发送数据
	 * @param  [type] $socketData [description]
	 * @return [type]             [description]
	 */
	public function client_send(ISocketData $socketData){
		$socketData->checkValid();
		//格式化消息
		$strMsg = serialize();
		$this->sw_client->send($strMsg);
	}

	/**
	 * 客户端接受消息(同步)
	 * @return [type] [description]
	 */
	public function client_recv($size = 65535, $flags = 0){
		$msg = $this->sw_client->recv($size,$flags);
		if($msg === false){
			throw new Exception("recv msg error", $this->sw_client->errCode);
		}else if($msg === ''){
			throw new Exception("connect closed", 10001);
		}

	}

}
