<?php 

/**
 * 事务组的操作
 * 事务组信息用redis hash存储
 * 
 */
class TransGroupManage {
    
    public static $obj = null;


    private $key_group = "swdtc_group_";
	private $key_item = "swdtc_item_";

    private $stage_arr = array(
        'precommit' => 'count'
    );


    public static function getInstance(){
        if(is_null(self::$obj)){
            self::$obj = new TransGroupManage();
        }
        return self::$obj;
    }

    /**
     * 保存事务组信息
     * @param  [type] $group [description]
     * @return [type]                     [description]
     */
    public function saveTransGroup($group, $transaction) {
        try {

            //保存数据 到sortSet
            RedisHash::hSet($this->key_group . $group->groupId, "status", Constant::$tx_status_begin);
            //保存事务发起者
            RedisHash::hSet("{$this->key_item}{$group->groupId}", $transaction->transId, serialize($transaction));
            RedisHash::hSet("{$this->key_group}{$group->groupId}", 'starter', $transaction->transId);
            RedisHash::hSet($this->key_group . $group->groupId, "count", 0);
            
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
    /**
     * 获取事务组的发起者
     * @param  [type] $groupId [description]
     * @return [type]          [description]
     */
    public function getTransGroupStarter($groupId){
        $transId = RedisHash::hGet("{$this->key_group}{$groupId}", 'starter');
        $starter = RedisHash::hGet("{$this->key_item}{$groupId}", $transId);
        $starter = unserialize($starter);

        return $starter;
    }

    /**
     * 添加事务参与者
     * @param [type] $groupId         [description]
     * @param [type] $item [description]
     */
    public function addTxTransaction($groupId, $item) {
        try {
            RedisHash::hSet("{$this->key_item}{$groupId}", $item->transId, serialize($item));
            RedisHash::hIncrBy($this->key_group . $groupId, "count", 1);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * 根据事务组Id获取事务参与者列表
     * 
     * @param  [type] $groupId [description]
     * @return [type]            [description]
     */
    public function getItemsByGroupId($groupId) {
        $list = RedisHash::hGetAll("{$this->key_item}{$groupId}");
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
     * @param  [type] $groupId [description]
     * @return [type]            [description]
     */
    public function removeGroupItems($groupId) {
        return RedisHash::del("{$this->key_item}{$groupId}");
    }

    /**
     * 更新事务组参与者的状态
     */
    public function updateItemStatus($groupId, $transaction, $status) {
        try {
            $item = RedisHash::hGet("{$this->key_item}{$groupId}", $transaction->transId);
            if(empty($item)){
                throw new Exception("have no txTransactionItem info", 10001);
            }
            $trans = unserialize($item);
            $trans->status = $status;

            //计算耗时
            $now = microtime(true);
            $createtime = $trans->createTime? $trans->createTime : 0;
            $trans->consumeTime = $now - $createtime;

            RedisHash::hSet("{$this->key_item}{$groupId}", $transaction->transId, serialize($trans));
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 更新事务组状态
     * @param  [type] $groupId [description]
     * @param  [type] $status  [description]
     * @return [type]          [description]
     */
    public function updateGroupStatus($groupId, $status){
        try {
            $groupStatus = RedisHash::hGet("{$this->key_group}{$groupId}", "status");
            if(empty($groupStatus)){
                throw new Exception("have no txTransactionItem info", 10001);
            }
            RedisHash::hSet("{$this->key_group}{$groupId}", "status", $status);
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 查看事务组当前状态
     * @param  [type] $groupId [description]
     * @return [type]            [description]
     */
    public function getGroupStatus($groupId) {
        try {
            $groupStatus = RedisHash::hGet($this->key_group . $groupId, "status");
            return $groupStatus;
        } catch (Exception $e) {
            return Constant::$tx_status_rollback;
        }
    }

    public function getStatusCount($groupId,$stage){
        return RedisHash::hGet($this->key_group . $groupId, $this->stage_arr[$stage]);
    }

    public function statusCount($groupId, $stage){
        return RedisHash::hIncrBy($this->key_group . $groupId, $this->stage_arr[$stage], -1);
    }

}