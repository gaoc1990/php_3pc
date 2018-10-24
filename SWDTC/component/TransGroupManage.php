<?php 

/**
 * 事务组的操作
 * 事务组信息用redis hash存储
 * 
 */
class TransGroupManage {
    private $key_group_list = "swdtc_group_list";
	private $key_pre = "swdtc_group_item_";

    /**
     * 保存事务组信息
     * @param  [type] $txTransactionGroup [description]
     * @return [type]                     [description]
     */
    public function saveTxTransactionGroup($txTransactionGroup) {
        try {
            $groupId = $txTransactionGroup.getId();
            //保存数据 到sortSet
            Redis::hSet($this->key_group_list, $groupId, Constant::$tx_status_begin);
            $list = $txTransactionGroup->getItemList();
            if(!empty($list)){
                foreach($list as $item){
                    //保存事务参与者
                    Redis::hSet("{$this->key_pre}{$groupId}", $item->getTaskKey(), serialize($item));
                }

            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 添加事务参与者
     * @param [type] $txGroupId         [description]
     * @param [type] $txTransactionItem [description]
     */
    public function addTxTransaction($txGroupId, $txTransactionItem) {
        try {
            Redis::hSet("{$this->key_pre}{$txGroupId}", $txTransactionItem->getTaskKey(), serialize($txTransactionItem));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 根据事务组Id获取事务参与者列表
     * 
     * @param  [type] $txGroupId [description]
     * @return [type]            [description]
     */
    public function listByTxGroupId($txGroupId) {
        $list = Redis::hGetAll("{$this->key_pre}{$txGroupId}");
        if(empty($list)){
            return false;
        }
        $itemList = array();
        foreach($list as $v){
            $itemList[] = unserialize($v);
        }

        return $itemList;
    }

    /**
     * 根据事务组ID删除事务信息
     * 
     * @param  [type] $txGroupId [description]
     * @return [type]            [description]
     */
    public function removeRedisByTxGroupId($txGroupId) {
        return Redis::del("{$this->key_pre}{$txGroupId}");
    }

    /**
     * 更新事务组参与者的状态
     */
    public function updateTxTransactionItemStatus($groupId, $tastKey, $status, $message) {
        try {
            $item = Redis::hGet("{$this->key_pre}{$groupId}", $tastKey);
            if(empty($item)){
                throw new Exception("have no txTransactionItem info", 10001);
            }
            $txTransactionItem = unserialize($item);

            $txTransactionItem->setStatus(status);
            if (!empty($message)) {
                $txTransactionItem->setMessage($message);
            }

            //计算耗时
            $now = microtime(true);
            $createtime = $txTransactionItem->getCreateTime();
            $txTransactionItem = $txTransactionItem->setConsumeTime($now - $createtime);

            Redis::hSet("{$this->key_pre}{$groupId}",$tastKey, serialize($txTransactionItem));
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 查看事务组状态
     * @param  [type] $txGroupId [description]
     * @return [type]            [description]
     */
    public function findTxTransactionGroupStatus($txGroupId) {
        try {
            $txTransactionItemStatus = Redis::hGet($this->key_group_list,$txGroupId);
            return $txTransactionItemStatus;
        } catch (Exception $e) {
            return Constant::$tx_status_rollback;
        }
    }

    /**
     * 删除完成的事务组
     */
    public function removeCommitTxGroup() {
        $groupIds = Redis::hKeys($this->key_group_list);
        foreach($groupIds as $groupId){
            $items = Redis::hGetAll($this->key_pre . $groupId);
            $cant = 0;
            foreach ($items as $key => $value) {
                $item = unserialize($value);
                if($status != Constant::$tx_status_commit){
                    $cant = 1;
                }
            }
            if(!$cant ){
                Redis::del($this->key_pre . $groupId);
            }
            
        }

        return true;

    }

    public function removeRollBackTxGroup() {
        
        $groupIds = Redis::hKeys($this->key_group_list);
        foreach($groupIds as $groupId){
            $items = Redis::hGetAll($this->key_pre . $groupId);
            $cant = 0;
            foreach ($items as $key => $value) {
                $item = unserialize($value);
                if($status != Constant::$tx_status_rollback){
                    $cant = 1;
                }
            }
            if(!$cant ){
                Redis::del($this->key_pre . $groupId);
            }
            
        }

        return true;
    }

}