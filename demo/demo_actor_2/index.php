<?php 

define("CORE_DIR", dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor/');
define("BASE_DIR", dirname(__FILE__) . "/");

//注册自动加载
include_once(CORE_DIR . 'autoload.php');
$config = include(BASE_DIR . "config.php");

$dispatch = new Dispatch($_SERVER['PATH_INFO']);
$dispatch->doRequest();