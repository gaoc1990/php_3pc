<?php 

/**
 * 回应消息
 */
class TxSocketResponse implements ISocketData
{
	
	function __construct()
	{
		
	}

	public function checkValid(){
		return true;
	}

	public $code = 200;

	public $msg = "success";
}