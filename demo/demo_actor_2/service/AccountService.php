<?php 
require_once( BASE_DIR . "library/TxDatabase.php"); 

/**
 * 账户服务类
 */
class AccountService 
{
	public static $obj = null;

	public static function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new AccountService();
		}

		return self::$obj;
	}
	
	function __construct()
	{
	}

	/**
	 * 支付扣款
	 * @return [type] [description]
	 */
	public static function pay($money){
		$db = TxDatabase::getInstance()->getConn('t_user2');
		$sql ="update t_user2.account set money = money + $money where uid = 213147";
		$db->query($sql,array());
		$num = $db->affectedCount();

		return $num;
	}
  	
}