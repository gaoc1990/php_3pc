<?php 
/**
 * 事务协调器启动程序
 */

define("SERVER_NAME", 'SWDTC');
define("CORE_DIR", dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor/');
define("BASE_DIR", dirname(__FILE__)) . "/";

//加载配置

//注册自动加载
include_once(CORE_DIR . '/autoload.php');

//启动服务
include_once(BASE_DIR . "/server/DtcServer.php");

$server = new DtcServer();
$server->run();