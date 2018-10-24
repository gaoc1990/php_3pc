<?php

/**
 * 可序列化的事务回复对象
 */

class Recover
{
    /**
     * 主键id.
     */
    private  $id;

    /**
     * 重试次数.
     */
    private $retriedCount;

    /**
     * 创建时间.
     */
    private $createTime;

    /**
     * 上次执行时间.
     */
    private $lastTime;

    /**
     * 版本控制 防止并发问题.
     */
    private $version = 1;

    /**
     * 事务组id.
     */
    private $groupId;

    /**
     * 任务id.
     */
    private $taskId;

    /**
     * 事务执行方法.
     * Invocation
     */
    private $transactionInvocation;

    /**
     * 状态.
     */
    private $status;

    /***
     * 任务完成标志
     */
    private $completeFlag;

    /**
     * 日志更新操作
     */
    private $operation;

    public function __construct(){
        $this->createTime = time();
        $this->lastTime = time();
    }

    
}
