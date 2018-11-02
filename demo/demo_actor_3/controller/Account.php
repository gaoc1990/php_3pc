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
		require_once(BASE_DIR . "service/AccountService.php");
		$res = AccountService::getInstance()->pay();
	}  
}