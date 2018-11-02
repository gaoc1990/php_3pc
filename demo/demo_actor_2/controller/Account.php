<?php 
/**
 * 
 */
class Account
{
	
	function __construct()
	{
		
	}

	public function update(){
		require_once(BASE_DIR . "library/TxDatabase.php");
		$db = TxDatabase::getInstance()->getConn('t_user2');
		$trans = new TxTransaction($db);
		$trans->begin();

		$money = $_REQUEST['money'];
		try
		{
		    require_once(BASE_DIR . "service/AccountService.php");
			$num = AccountService::getInstance()->pay($money);
		}
		catch(Exception $e)
		{
		    Log::getInstance()->error($e->getMessage());
		    $trans->rollback();
		}
		$trans->commit();

		exit("finish");

	}  
}