<?php 
namespace tpc\common;

/**
 * 公共配置文件
 */

$config = array(
	'tx' => array(
		'class' 		=> "TxDatabase",
		'trans_begin' 	=> "beginTransaction",
		'commit' 		=> "commit",
		'rollback' 		=> "rollback"
	),
	'sw_client' => array(
		'host' => '127.0.0.1',
		'port' => '9501',
		'timeout' => 10
	),
	'redis' => array(
		'host' => '127.0.0.1',
		'port' => 6379,
		'timeout' => null,
		'auth' => ''
	),
	'log' => array(

	)
);

return $config;