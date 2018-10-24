<?php 

/**
 * 分布式事务资源管理器Transaction类
 */
class TxTransaction implements ITxTransaction
{

	private static $obj = null;
	public $transDb;
	public $trans_begin;
	public $trans_commit;
	public $trans_rollback;

	public $swoole_client;


	private function __construct(){
		$this->initTransDb();
		//初始化swool_client
		$this->initSwooleClient();
	}

	
	/**
	 * @return TxTransaction
	 */
	public static function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new TxTransaction();
		}

		return self::$obj;
	}

	public function initTransDb(){
		//获取当前系统设置的数据库操作对象
		$config = ConfigHelper::get('tx');
		if( empty($config['TxDatabase']) || empty($config['trans_begin']) || empty($config['trans_commit']) || empty($config['trans_rollback'])){
			throw new Exception("error TxDatabase config", 1);
		}
		if(!class_exists('TxDatabase')){
			throw new Exception("TxDatabase class not exists", 1);
		}
		
		$refObj = new ReflectionClass($config['TxDatabase']);
		if(!$refObj->hasMethod($config['trans_init'])){
			throw new Exception("TxDatabase init error", 1);
		}
		$this->transDb = $config['TxDatabase']::$config['trans_init']();

		$this->trans_begin = $config['trans_begin'];
		$this->trans_commit = $config['trans_commit'];
		$this->trans_rollback = $config['trans_rollback'];
	}

	public function initSwooleClient(){
		
	}


	/**
	 * 注册全局事务节点
	 * @return [type] [description]
	 */
	public function begin(){
		$groupId = Util::dealParam();
		//判断是否有txGroupId(没有事务组ID是发起者走发起者逻辑，有事务组ID是参与者走参与者逻辑）
		if($groupId){
			//发起参与
			ActorTransactionComponent::getInstance()->registTransaction($groupId);
		}else{
			StartTransactionComponent::getInstance()->handler();
		}
		//发起者开始事务
		$this->transDb->{$this->trans_begin}();

		return $this->transDb;
	}

	/**
	 * 等待提交指令
	 * @param  [type] $localTransaction [description]
	 * @return [type]                   [description]
	 */
	public function waitCommit($localTransaction){

		try{
			$cmd_precommit = $swoole_client->recv();
			//解析指令
			$socketData = SocketData::from($cmd_precommit);

			if($socketData->action == Constant::$socket_action_precommit){
				//应该OK
				$sd_ack = new SocketData();
				$sd_ack->data = "ack";
				$swoole_client->send($sd_ack);
			}else{
				throw new Exception("tx manager command error", 10001);
			}

			$cmd_commit = $swoole_client->recv();
			$socketData = SocketData::from($cmd_commit);
			if($socketData->action == Constant::$socket_action_commit){
				//提交请求
				$localTransaction->commit();
			}

		}catch(Exception $e){
			//补偿
			
			
		}

	}

}