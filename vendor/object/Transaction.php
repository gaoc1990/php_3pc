<?php

class Transaction {

    private static $obj = null;

    public function __construct()
    {
    }
    /**
     * [getInstance description]
     * @return DtcSysInfo
     */
    public function Transaction(){
        if(is_null(self::$obj)){
            self::$obj = new Transaction();
        }
        return self::$obj;
    }

    public function init($groupId,$waitMaxTime,$propagation){
        $this->groupId = $groupId;
        $this->waitMaxTime = $waitMaxTime;
        $this->propagation = $propagation;
    }
    
    /**
     * 分布式事务组.
     */
    public $groupId;

    public $transId;

    public $role;

    public $status;

    public $createTime;

    /**
     * 事务等待时间.
     */
    public $waitMaxTime;

    //事务传播性质
    public $propagation;

    /**
     * 事务补偿id.
     */
    public $compensationId;

    /**
     * 补偿方法对象.
     */
    public $invocation;

    public $fd; //链接句柄

    /**
     * 事务消耗时间
     * @var [type]
     */
    public $consumeTime;

}