<?php 
/**
 * server基类
 */
class BaseServer
{
	/**
	 * $config 
	 * @var null
	 */
	public static $config = [];

	/**
	 * $server swoole服务器对象实例
	 * @var null
	 */
	public static $server = null;
	/**
	 * $Service 
	 * @var null 服务实例，适用于TCP,UDP,RPC
	 */
	public static $service = null;

	/**
	 *  是否启用协程
	 * @var boolean
	 */
	public static $isEnableCoroutine = false;

	/**
	 * pack检查的方式
	 * @var [type]
	 */
	protected static $pack_check_type = null;

	/**
	 * $_startTime 进程启动时间
	 * @var int
	 */
	protected static $_startTime = 0;

	/**
	 * $swoole_process_model swoole的进程模式，默认swoole_process
	 * @var [type]
	 */
	protected static $swoole_process_mode = SWOOLE_PROCESS;

	/**
	 * $swoole_socket_type swoole的socket设置类型
	 * @var [type]
	 */
	protected static $swoole_socket_type = SWOOLE_SOCK_TCP;

	public static $_table_tasks = [];

	function __construct()
	{
		self::checkSapiEnv();
	}


	/**
	 * getStartTime 服务启动时间
	 * @return   time
	 */
	public static function getStartTime() {
		return self::$_startTime;
	}

	/**
	 * getConfig 获取服务的全部配置
	 * @return   array
	 */
	public static function getConf() {
		return static::$config;
	}
	/**
	 * getSetting 获取swoole的配置项
	 * @return   array
	 */
	public static function getSetting() {
		return self::$config['setting'];
	}
	/**
	 * getLastError 返回最后一次的错误代码
	 * @return   int
	 */
	public static function getLastError() {
		return self::$server->getLastError();
	}

	/**
	 * getLastErrorMsg 获取swoole最后一次的错误信息
	 * @return   string
	 */
	public static function getLastErrorMsg() {
		$code = swoole_errno();
		return swoole_strerror($code);
	}

	/**
	 * getStatus 获取swoole的状态信息
	 * @return   array
	 */
	public static function getStats() {
		return self::$server->stats();
	}

	/**
	 * clearCache 清空字节缓存
	 * @return  void
	 */
	public static function clearCache() {
		if(function_exists('apc_clear_cache')){
        	apc_clear_cache();
    	}
	    if(function_exists('opcache_reset')){
	        opcache_reset();
	    }
	}

	/**
	 * setSwooleSockType 设置socket的类型
	 */
	protected static function setSwooleSockType() {
		if(isset(static::$setting['swoole_process_mode']) && static::$setting['swoole_process_mode'] == SWOOLE_BASE) {
			self::$swoole_process_mode = SWOOLE_BASE;
		}

		if(self::isUseSsl()) {
			self::$swoole_socket_type = SWOOLE_SOCK_TCP | SWOOLE_SSL;
		}
		return;
	}

	/**
	 * serviceType 获取当前主服务器使用的协议
	 * @return   mixed
	 */
	public static function getServiceProtocol() {
		// websocket
		if(static::$server instanceof \Swoole\WebSocket\Server) {
			return 'websocket';
		}else if(static::$server instanceof \Swoole\Http\Server) {
			return 'http';
		}else if(static::$server instanceof \Swoole\Server) {
			if(self::$swoole_socket_type == 'udp') {
				return 'udp';
			}
			return 'tcp';
		}
		return false;

	} 

	/**
	 * checkSapiEnv 判断是否是cli模式启动
	 * @return void
	 */
	public static function checkSapiEnv() {
        // Only for cli.
        if(php_sapi_name() != 'cli') {
            throw new \Exception("only run in command line mode \n", 1);
        }
    }

    /**
     * usePackEof 是否是pack的eof
     * @return boolean
     */
    protected static function isPackEof() {
    	if(self::$pack_check_type == self::PACK_CHECK_EOF) {
    		return true;
    	}
    	return false;
    }

}


