<?php 
/**
 * 资源管理器接口
 */
interface DtrInterface {
	/**
	 * 准备阶段接口
	 * @return [type] [description]
	 */
	function cancommit();

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