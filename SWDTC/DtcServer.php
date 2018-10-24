<?php 

/**
 * 事务协调器启动类
 */
class DtcServer extends BaseService
{
	private $server;
	private $sw_config;

	public function __construct(){
		
	}

	/**
	 * 启动服务
	 * @return [type] [description]
	 */
	public function run(){
		//加载config
		$this->sw_config = include_once('config.php');
		//多进程的模式建立server,TCP方式
		$this->server = new swoole_websocket_server('0.0.0.0', $this->sw_config['websocket_port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
		$this->server->set(array(
		    'worker_num' => 4,
		    'backlog' => 128,  //listen队列长度
		    'max_request' => 50,
		    'dispatch_mode' => 1,
		    'daemonize' => 1,
		    'log_file' => $this->config['sw_log_file'],
		    'heartbeat_idle_time' => 60 //超过时间的链接没数据发送，将被关闭单位s
		));

		$this->server->on('WorkerStart',function(swoole_server $serv, $worker_id){
			App::$service = $this->server;
		});

		$this->server->on('connection', array(this,'onConnection'));
		$this->server->on('receive', array(this,'onReceive'));
		$this->server->on('close', array(this,'onClose'));

		$this->server->on('request', array(this,'onRequest'));
		$this->server->on('message', array(this,'onMessage'));

		$this->server->start();
	}

	/**
	 * http请求回调
	 * 
	 * @return [type] [description]
	 */
	public function onRequest(swoole_http_request $request, swoole_http_response $response){
		try{
			TxTransactionManage::getInstance()->dealRequest($request, $response);
		}catch(Exception $e){

		}
	}

	public function onMessage(swoole_server $serv, swoole_websocket_frame $frame){
		//当有客户端发送请求过来
		try{
			TxTransactionManage::getInstance()->dealMessage($serv, $frame);
		}catch(Exception $e){

		}
	}

	public function onReceive($serv, $fd, $from_id, $data){
		//当有客户端发送请求过来
		try{
			TxTransactionManage::getInstance()->dealReceive($serv, $fd, $from_id, $data);
		}catch(Exception $e){

		}
		
	}

	public function onConnection(){
		Log::info("connection");
	}

	public function onClose(){
		Log::info("close");
	}

}