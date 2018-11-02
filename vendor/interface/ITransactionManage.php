<?php 

interface ITransactionManage{
	/**
	 * 预提交接口
	 * @return [type] [description]
	 */
	function precommit($socketData,$fd);

	/**
	 * 提交接口
	 * @return [type] [description]
	 */
	function commit($transactionList);

	/**
	 * 回滚接口
	 * @return [type] [description]
	 */
	function rollback($groupId, $transactionList = array());

}