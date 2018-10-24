<?php 

interface ITransactionManage{
	/**
	 * 预提交接口
	 * @return [type] [description]
	 */
	function precommit();

	/**
	 * 提交接口
	 * @return [type] [description]
	 */
	function commit();

	/**
	 * 回滚接口
	 * @return [type] [description]
	 */
	function rollback();

}