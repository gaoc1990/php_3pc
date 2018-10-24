<?php
/**
 * RPC SERVER
 * 
 */


include_once SWOOLEFY_CORE_ROOT_PATH.'/EventInterface.php';

use Swoolefy\Core\Swfy;
use Swoolefy\Tcp\TcpServer;
use Swoolefy\Core\EventInterface;

abstract class RpcServer extends TcpServer {
	/**
	 * __construct 初始化
	 * @param array $config
	 */
	public function __construct(array $config=[]) {
		// 获取当前服务文件配置
		$config = array_merge(
				include(__DIR__.'/config.php'),
				$config
			);
		parent::__construct($config);
		// 设置当前的服务名称
		self::$serverName = 'rpc';
	}

	/**
	 * onWorkerStart worker进程启动时回调处理
	 * @param  object $server
	 * @param  int    $worker_id
	 * @return void       
	 */
	public abstract function onWorkerStart($server, $worker_id);

	/**
	 * onConnet socket连接上时回调函数
	 * @param  object $server
	 * @param  int    $fd    
	 * @return void        
	 */
	public abstract function onConnet($server, $fd);

	/**
	 * onReceive 接收数据时的回调处理，$data是一个完整的数据包，底层已经封装好，只需要配置好，直接使用即可
	 * @param  object $server
	 * @param  int    $fd
	 * @param  int    $reactor_id
	 * @param  mixed  $data
	 * @return mixed
	 */
	public function onReceive($server, $fd, $reactor_id, $data) {
		\Swoole\Serialize::unpack(self::$service)->run($fd, $data);
	}

	/**
	 * onTask 任务处理函数调度
	 * @param   object  $server
	 * @param   int     $task_id
	 * @param   int     $from_id
	 * @param   mixed   $data
	 * @return  void
	 */
	public function onTask($server, $task_id, $from_id, $data) {
		list($callable, $taskData, $fd) = $data;		
		\Swoole\Serialize::unpack(self::$service)->run($fd, [$callable, $taskData]);
		return ;
	}

	/**
	 * onFinish 异步任务完成后调用
	 * @param    int     $task_id
	 * @param    mixed   $data
	 * @return   void
	 */
	public abstract function onFinish($server, $task_id, $data);

	/**
	 * onPipeMessage 
	 * @param    object  $server
	 * @param    int     $src_worker_id
	 * @param    mixed   $message
	 * @return   void
	 */
	public abstract function onPipeMessage($server, $from_worker_id, $message);

	/**
	 * onClose tcp连接关闭时回调函数
	 * @param  object $server
	 * @param  int    $fd    
	 * @return void
	 */
	public abstract function onClose($server, $fd);
	
}
