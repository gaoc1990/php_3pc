<?php 

/**
 * 事务协调器当前状态信息
 */
class DtcSysInfo 
{
	private static $obj = null;

	private function __construct()
	{
	}
	/**
	 * [getInstance description]
	 * @return TxManageInfo
	 */
	public function getInstance(){
		if(is_null(self::$obj)){
			self::$obj = new TxManageInfo();
		}
		return self::$obj;
	}

	/**
	 * 事务组数量
	 * @var [type]
	 */
	private $group_num;

	/**
     * socket ip.
     */
    private $ip;

    /**
     * socket port.
     */
    private $port;

}