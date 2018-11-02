<?php 

/**
 * 事务协调器启动类
 */
class DtcServer extends BaseServer
{

	public function __construct(){
		self::clearCache();
		self::$config = include_once('config.php');
		parent::__construct();
	}

	/**
	 * 启动服务
	 * @return [type] [description]
	 */
	public function run(){
		//多进程的模式建立server,TCP方式
		self::$server = new swoole_server('0.0.0.0', self::$config['websocket_port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
		self::$server->set(array(
		    'worker_num' => 4,
		    'backlog' => 128,  //listen队列长度
		    'max_request' => 50,
		    'daemonize' => 1,
		    'log_file' => self::$config['sw_log_file'],
		    'heartbeat_idle_time' => 60,//超过时间的链接没数据发送，将被关闭单位s
		 	'open_eof_check' => true, //打开buffer
		 	'package_eof' => "\r\n\r\n" //设置EOF
		));

		self::$server->on('WorkerStart',function(swoole_server $serv, $worker_id){
			echo "worker start\n";
			App::$server = self::$server;
		});

		self::$server->on('connect', array($this, 'onConnection'));
		self::$server->on('receive', array($this, 'onReceive'));
		self::$server->on('close', array($this,'onClose'));
		

		self::$server->start();

	}

	public function onReceive($serv, $fd, $from_id, $data){
		//当有客户端发送请求过来
		try{
			require_once(BASE_DIR . "/component/ServerEventHandler.php");
			ServerEventHandler::getInstance($serv)->dealReceive($serv, $fd, $from_id, $data);
		}catch(Exception $e){
			Log::getInstance()->error($e->getMessage());
		}
	}

	public function onConnection(){
		Log::getInstance()->info("dtc server connection");
	}

	public function onClose(){
		Log::getInstance()->info("dtc server close");
	}

}