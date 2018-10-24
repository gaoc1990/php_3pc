<?php 
namespace tpc\common;

/**
 * 日志处理类
 */
class Log
{
	
	private static $obj = null;
	private $log_dir = "/tmp/swdtc/";
	private $log_file = "default.log";

	private function __construct(){
		$config = ConfigHelper::get('config.log');
		if(!isset($config['log_dir']) || !isset($config['log_file'])){
			throw new Exception("error log config", 10001);
		}
		if(!file_exists($config['log_dir'])){
			@mkdir($config['log_dir']);
		}
		$this->log_file = date('Y-m-d') . '.log';
	}

	/**
	 * [getInstance description]
	 * @return Log
	 */
	public function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new Log();
		}

		return self::$obj;
	}


	public function _write_log($format,$log){
		$filename = $this->log_dir . $this->log_file;
		if(!$fp = fopen($filename,'ab')){
			throw new Exception("cant open logfile", 10001);
		}
		$time_format = date('Y-m-d H:i:s');
		if(is_array($log) || is_string($log)){
			$log = json_encode($log);
		}
		flock($fp, LOCK_EX);
		fwrile($fp, "$time_format $format {$log}\n");
		flock($fp, LOCK_UN);
		fclose($fp);

		return true;
	}


	public function error($log){
		$format = "Error";
		return $this->_write_log($format,$log);
	}

	public function info($log){
		$format = "Info";
		return $this->_write_log($format,$log);
	}

	public function warn($log){
		$format = "Warning";
		return $this->_write_log($format,$log);
	}

}