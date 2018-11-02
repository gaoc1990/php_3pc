<?php 
/**
 * 配置获取
 */
class ConfigHelper
{
	/**
	 * 获取公共配置
	 * @param  [type] $path [description]
	 * @return [type]       [description]
	 */
	public static function get($path=""){
		//系统的配置
		$config = include(dirname(__DIR__) .  DIRECTORY_SEPARATOR . 'Config.php');
		$path_arr = explode('.',$path);

		$ret = $config;
		if(count($path_arr) == 0 ){
			return $config;
		}

		foreach($path_arr as $key){
			$ret = isset($ret[$key]) ? $ret[$key] : false;
		}

		return $ret;
	}


}