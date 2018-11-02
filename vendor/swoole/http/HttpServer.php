<?php 

/**
 * HTTP 服务器类
 */
class HttpServer extends BaseServer
{

	public function __construct(){
		self::clearCache();
		parent::__construct();
	}

	/**
	 * 启动服务
	 * @return [type] [description]
	 */
	public function run($config){
		self::$config = array_merge(self::$config, $config);
		//多进程的模式建立server,TCP方式
		self::$server = new swoole_http_server('0.0.0.0', self::$config['port']);
		self::$server->set(array(
		    'document_root' => BASE_DIR,
		    'enable_static_handler' => true,
		    'http_parse_post' => true
		));
		
		self::$server->on('WorkerStart', array($this,'onWorkerStart'));
		self::$server->on('request', array($this,'onRequest'));
		
		self::$server->start();
	}

	/**
	 * http请求回调
	 * 
	 * @return [type] [description]
	 */
	public function onRequest(swoole_http_request $request, swoole_http_response $response){
		try
		{
			
			$_SERVER = $request->server;
			$_GET = empty($request->get) ? array() : $request->get;
			$_POST = empty($request->post) ? array() : $request->post;
			$_FILES = empty($request->files) ? array() : $request->files;
			$_COOKIE = empty($request->cookie) ? array(): $request->cookie;
			$body = empty($request->getData()) ? "" : $request->getData();

			$_REQUEST = array_merge($_POST, $_GET);

			require_once("Dispatch.php");
			$dispatch = new Dispatch($_SERVER['path_info']);
			$data = $dispatch->doRequest();
			$response->end("data:".json_encode($data));
		}
		catch(Exception $e)
		{
			$response->write($e->getMessage());
			$response->status(500);
			$response->end();
		}
	}

	public function onWorkerStart(){
		echo "worker start\n";
		$_REQUEST = array();
		$_SERVER = array();
		$_POST = array();
		$_COOKIE = array();
		$_FILES = array();
		$_GET = array();
		$body = "";
	}
}