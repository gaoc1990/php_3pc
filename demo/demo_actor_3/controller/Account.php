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
		$db = TxDatabase::getInstance()->getConn('t_user3');
		$trans = new TxTransaction($db);
		$trans->begin(1);

		$money = $_REQUEST['money'];
		try
		{
		    require_once(BASE_DIR . "service/AccountService.php");
			$num = AccountService::getInstance()->pay($money);
			if(!$num || $num < 1){
				throw new Exception("update error", 1);
			}
		}
		catch(Exception $e)
		{
		    Log::getInstance()->error($e->getMessage());
		    $trans->rollback();
		    exit(Constant::$tx_complete_fail);
		}
		$result = $trans->commit();

		exit(Constant::$tx_complete_ok);
	}
}