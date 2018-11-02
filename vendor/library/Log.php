<?php 


/**
 * 日志处理类
 */
class Log
{
	
	private static $obj = null;
	private $log_dir = "/tmp/swdtc/";
	private $log_file = "default.log";

	private function __construct(){
		$config = ConfigHelper::get('log');
		if(isset($config['log_dir'])){
			$this->log_dir = $config['log_dir'];
		}
		if(isset($config['log_file'])){
			$this->log_file = $config['log_file'];
		}

		if(!file_exists($this->log_dir)){
			@mkdir($this->log_dir);
			@chmod($this->log_dir, 0777);
		}
		$this->log_file = date('Y-m-d') . '.log';

	}

	/**
	 * [getInstance description]
	 * @return Log
	 */
	public static function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new Log();
		}

		return self::$obj;
	}


	public function _write_log($format,$log){
		$filename = $this->log_dir . $this->log_file;
		if(!$fp = fopen($filename,'ab')){
			throw new Exception("cant open logfile", 10001);
			return false;
		}
		@chmod($filename,0777);

		$time_format = date('Y-m-d H:i:s');
		if(is_array($log) || is_string($log)){
			$log = json_encode($log);
		}
		flock($fp, LOCK_EX);
		fwrite($fp, "$time_format $format {$log}\n");
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