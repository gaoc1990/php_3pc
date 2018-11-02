
<?php

/**
 * socket请求数据格式
 */
class TxSocketData implements ISocketData {

	public function __construct()
	{
        
	}

    public static function fromMsg($msg){
        $msg = trim($msg);
        $obj = unserialize($msg);
        if(!$obj instanceof TxSocketData){
            throw new Exception("error socketdata",1);
        }
        return $obj;
    } 

    public static function createResponse($response){
        $obj = new TxSocketData();
        $obj->result = $response;
        return $obj;
    }

	/**
	 * 验证合法性
	 * @return boolean
	 */
	public function checkValid(){
		if(is_null($this->result) && empty($this->action)){
            throw new Exception("error socket data format", 1);
        }
		return true;
	}
    /**
     * 执行动作
     */
    public $action;

    /**
     * result
     **/
    public $result = NULL;

    /**
     * 事务信息.
     */
    public $transaction;

    /**
     * 事务组信息
     * @var [type]
     */
    public $transGroup;
}

