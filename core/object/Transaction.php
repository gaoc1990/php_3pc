<?php

class Transaction {

    private static $obj = null;

    private function __construct()
    {
    }
    /**
     * [getInstance description]
     * @return DtcSysInfo
     */
    public function getInstance(){
        if(is_null(self::$obj)){
            self::$obj = new DtcSysInfo();
        }
        return self::$obj;
    }

    public function init($txGroupId,$waitMaxTime,$propagation){
        $this->txGroupId = $txGroupId;
        $this->waitMaxTime = $waitMaxTime;
        $this->propagation = $propagation;
    }
    
    /**
     * 分布式事务组.
     */
    public $txGroupId;

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

}