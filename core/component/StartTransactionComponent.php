<?php 

/**
 * 开启一个全局事务,发起者使用
 */
class StartTransactionComponent 
{
	
	function __construct(){
		
	}

	/**
	 * 发起
	 * @return [type] [description]
	 */
	public function handler(){
		$txGroupId = Util::createXid();
		$taskKey = Util::createTaskKey();
        $transactionInfo = new Transaction();

		$txGroupInfo = $this->createTransGroup($groupId, $taskKey, $transactionInfo);
		
		return $txGroupInfo;
	}


	private function createTransGroup($groupId, $taskKey, $transactionInfo) {
        //创建事务组信息
        $transactionGroup = new TransactionGroup();
        $transactionGroup->groupId = $groupId;

        //添加发起者
        $item = new Transaction();
        $item->taskKey = $taskKey;
        $item->transId = Util::createStarterId($groupId);
        $item->role = Constant::$txgroup_role_starter;
        $item->status = Constant::$tx_status_begin;
        $item->groupId = $groupId;
        $item->waitMaxTime = $transactionInfo->waitMaxTime;
        $item->createTime = time();

        //设置事务执行类和事务补偿类（反射执行）
        $invocation = new Invocation();

        $item->invocation = $invocation;

        //添加到事务组
        $transactionGroup->itemList[] = $item;

        return $transactionGroup;
    }
}

