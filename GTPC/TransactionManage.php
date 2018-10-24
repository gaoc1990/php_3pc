<?php

/**
 * 事务管理器，全局管理分布式事务
 */
class TransactionManage 
{
	

	private $transaction = null;

	public function __construct()
	{
		$this->transaction = new Transaction();
	}

	/**
	 * 创建事务
	 */
	public function beginTrans(){

	}
	/**
	 * 准备阶段
	 * @return [type] [description]
	 */
	public function canCommit(){

	}
	/**
	 * 预提交
	 * @return [type] [description]
	 */
	public function preCommit(){

	}
	/**
	 * 提交分布式事务
	 * @return [type] [description]
	 */
	public function doCommit(){

	}
	/**
	 * 回滚分布式事务
	 * @return [type] [description]
	 */
	public function rollback(){

	}

}