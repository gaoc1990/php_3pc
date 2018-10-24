<?php 
namespace tpc\common;

/**
 * 公共配置文件
 */

$config = array(
	'tx' => array(
		'class' => "TxDatabase",
		'trans_init' => "getInstance",
		'trans_begin' => "beginTransaction",
		'trans_commit' => "commit",
		'trans_rollback' => "rollback"
	),
	'sw_client' => array(
		'timeout' => 1000,
		
	),
	'db' => array(
		
	),
	'redis' => array(

	),
	'log' => array(

	)
);

return $config;