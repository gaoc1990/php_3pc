<?php 
/**
 * 事务参与者类
 */
class Participator
{
	
	//所属事务唯一标识
	private $xid;
	//准备阶段上下文
	private $prepare_context;
	//预提交上下文
	private $precommit_context;
	//提交事务上下文
	private $commit_context;
	//回滚上下文
	private $rollback_context;


	function __construct()
	{
		# code...
	}

	/**
	 * [prepare description]
	 * @return [type] [description]
	 */
	public function prepare(){

	}

	/**
	 * 调用参与者（分支事务）预提交接口
	 * @return [type] [description]
	 */
	public function preCommit(){

	}

	/**
	 * 调用参与者(分支事务)回滚接口
	 * @return [type] [description]
	 */
	public function rollback(){

	}

	/**
	 * 调用参与者(分支事务)提交操作
	 * @return [type] [description]
	 */
	public function commit(){

	}

}