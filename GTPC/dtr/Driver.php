<?php 

/**
 * 数据库驱动的父类
 */
abstract class Driver implements DtrInterface
{
	
	function __construct()
	{
		
	}

	/**
	 * 执行查询操作
	 * @return [type] [description]
	 */
	public function query(){

	}

	public function prepare(){
		return $this->_prepare();
	}

	public function precommit(){
		return $this->_precommit();
	}

	public function commit(){
		return $this->precommit();
	}

	public function rollback(){
		return $this->rollback();
	}


}