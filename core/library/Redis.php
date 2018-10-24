<?php 
/**
 * redis操作类
 */
class Redis 
{
	/**
	 * redis链接对象
	 */
	public static $_redis = null;

	function __construct()
	{
		# code...
	}

	/**
	 * 初始化
	 * @return
	 */
	public static function init(){
		if( !is_null(self::$_instance) ){
			return true;
		}

		$config = ConfigHelper::get('redis');
		if(empty($config)){
			throw new Exception('no redis config ',10001);
		}

		$redis = new Redis();
		$isConn = $redis->connect($config['host'], $config['port'], $config['timeout']);
		if(!$isConn){
			throw new Exception('connect time out',10001);
		}
         
        if($config['auth'])
        {
            $res = self::$_redis->auth($config['auth']);
            if(!$res){
            	throw new Exception('auth fail ',10001);
            }
        }
        return true;
	}

	/**
	 * 获取redis实例
	 * 
	 * @return [type] [description]
	 */
	public static function getRedis(){
		return self::$_redis;
	}

	/**
	 * hash添加
	 * 
	 * @param  [type]  $key     [description]
	 * @param  [type]  $value   [description]
	 * @param  integer $redisdb [description]
	 * @return [type]           [description]
	 */
	public static function hSet($key, $field, $value, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return $_redis->hSet($key, $field, $value);
	}

	/**
	 * hash获取
	 * 
	 * @param  [type]  $key     [description]
	 * @param  integer $redisdb [description]
	 * @return [type]           [description]
	 */
	public static function hGet($key, $field, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return $_redis->hGet($key, $field);
	}

	public static function  hGetAll($key, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return $_redis->hGetAll($key);
	}
	/**
	 * 删除key
	 * 
	 * @param  [type]  $key     [description]
	 * @param  integer $redisdb [description]
	 * @return [type]           [description]
	 */
	public function del($key, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return $_redis->del($key);
	}
	
	public function hKeys($key,$redisdb=0){
		self::init();
		self::$_redis->select($redisdb);

		return $_redis->hKeys($key);
	}


}