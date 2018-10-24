<?php 
/**
 * 事务组类
 */
class TransactionGroup
{
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

    /**
     * 事务参与者列表Transaction
     * @var [type]
     */
    public $itemList;

}