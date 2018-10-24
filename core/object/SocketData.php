
<?php

/**
 * socket请求数据格式
 */
class SocketData {
	private static $obj = null;

	private function __construct()
	{
	}
	/**
	 * [getInstance description]
	 * @return SocketData
	 */
	public function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new SocketData();
		}
		return self::$obj;
	}

    /**
     * 执行动作
     */
    public $action;

    /**
     * 执行发送数据任务task key.
     */
    public $key;

    /**
     * result
     **/
    public $result;

    /**
     * 事务信息.
     */
    public $txTransaction;

    /**
     * 事务组信息
     * @var [type]
     */
    public $txTransactionGroup;
}

