<?php 

/**
 * 事务管理器核心类
 */
class Transaction 
{

	private static $obj = null;
	//事务全局唯一的标识
	private $xid = null;
	//事务参与者列表
	private $resource_list = array();

	
	function __construct()
	{
		
	}
	/**
	 * 单例
	 * @return Transaction
	 */
	public function getInstance(){
		if(is_null(self::$obj))
		{
			self::$obj = new Transaction();
		}
		return self::$obj;
	}

	/**
	 * 添加一个事务参与者
	 * @param $participator
	 */
	public function addParticipator(Participator $participator){

	}

	/**
	 * 删除一个事务参与者
	 * @param  Participator $participator [description]
	 * @return [type]                     [description]
	 */
	public function delParticipator(Participator $participator){

	}

	/**
	 * 获取参与者的数量
	 * @return [type] [description]
	 */
	public function getCount(){

	}

	public function prepare(){

	}

	public function preCommit(){

	}

	public function commit(){

	}

	public function rollback(){

	}

	public function setXid(XidResource $xid){

	}


}