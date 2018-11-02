<?php 
/**
 * 事务组类
 */
class TransactionGroup
{
    public function __construct($groupId="" ){
        if(empty($groupId)){
            $this->groupId = Util::createXid();
        }else{
            $this->groupId = $groupId;
        }
        $this->status = Constant::$tx_status_begin;
        $this->waitTime = 3;
    }
    /**
     * 事务组id.
	*/
	public $groupId;

    /**
     * 事务等待时间.
     */
    public $waitTime;

    /**
     * 事务状态.
     */
    public $status;
}