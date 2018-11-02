<?php 
/**
 * redis操作类
 */
class RedisHash 
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
		if( !is_null(self::$_redis) ){
			return true;
		}

		$config = ConfigHelper::get('redis');
		if(empty($config)){
			throw new Exception('no redis config ',10001);
		}

		self::$_redis = new Redis();
		$isConn = self::$_redis->connect($config['host'], $config['port']);
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

		return self::$_redis->hSet($key, $field, $value);
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

		return self::$_redis->hGet($key, $field);
	}

	public static function  hGetAll($key, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return self::$_redis->hGetAll($key);
	}
	/**
	 * 删除key
	 * 
	 * @param  [type]  $key     [description]
	 * @param  integer $redisdb [description]
	 * @return [type]           [description]
	 */
	public static function del($key, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return self::$_redis->del($key);
	}
	
	public static function hKeys($key,$redisdb=0){
		self::init();
		self::$_redis->select($redisdb);

		return self::$_redis->hKeys($key);
	}

	public static function hIncrBy($key, $field, $num, $redisdb = 0){
		self::init();
		self::$_redis->select($redisdb);

		return self::$_redis->hIncrBy($key, $field, $num);
	}

}