<?php 

define("SERVER_NAME", 'SWDTC');

define("CORE_DIR", dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'vendor/');

define("BASE_DIR", dirname(__FILE__) . "/");

//注册自动加载
include_once(CORE_DIR . 'autoload.php'); 

error_reporting(E_ALL);

require_once(BASE_DIR . "library/TxDatabase.php");
$db = TxDatabase::getInstance()->getConn('t_user');
$trans = new TxTransaction($db);
$trans->begin();

$money = 50;
try
{ 
    //本地事务
    require_once(BASE_DIR ."service/AccountService.php");
    $num = AccountService::getInstance()->pay($money);
    if(!$num || $num<1){
         throw new Exception("starter fail", 1); 
    }
    
    //远程服务1
    $res1 = Util::post("http://my.weibo.com/actor_2/account/update", array('money'=>$money,'groupId' => $trans->getTransGroup()->groupId));
    if($res1 == Constant::$tx_complete_fail){
        throw new Exception("actor_2 fail", 1);  
    }

    // //远程服务2
    // $res2 = Util::post("127.0.0.1:8080/?c=account&a=update",array('money'=>$money));
    // if($res2 == Constant::$tx_complete_fail){
    //     throw new Exception("actor_2 fail", 1);  
    // }
}
catch(Exception $e)
{
    Log::getInstance()->error($e->getMessage());
    $trans->rollback();
    echo "fail";
    exit;
}

$result = $trans->commit();

if(!$result){
    exit('fail');
}else{
    exit('commit');
}







