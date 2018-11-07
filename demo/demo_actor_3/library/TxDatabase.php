<?php

class TxDatabase {

    private $pdo = null;
    private $stmt = null;
    private $prep_sql = array();
    private $prep_data = array();
    // 类静态变量
    private static $obj = null;
    private static $arrdbset = array();
    private $retry = 0;

    /**
     * pdo值类型
     *
     * @var Array
     */
    private $pdo_type = array(
        'integer' => PDO::PARAM_INT,
        'int' => PDO::PARAM_INT,
        'boolean' => PDO::PARAM_BOOL,
        'bool' => PDO::PARAM_BOOL,
        'string' => PDO::PARAM_STR,
        'null' => PDO::PARAM_NULL,
        'object' => PDO::PARAM_LOB,
        'float' => PDO::PARAM_STR,
        'double' => PDO::PARAM_STR
    );

    private function __construct() {
        
    }

    /**
     * 单例获取方法
     *
     * @return TxDatabase
     */
    public static function getInstance() {
        if (is_null(self::$obj)) {
            self::$obj = new TxDatabase();
        }
        return self::$obj;
    }

    /**
     * 获取链接句柄
     * @param bool $isReader 是否使用从库
     * @return TxDatabase
     */
    public function getConn($dbname = "user") {
        if (isset(self::$arrdbset[$dbname])) {
            $this->pdo = self::$arrdbset[$dbname];
        } else {
            $this->_connect($dbname);
            self::$arrdbset[$dbname] = $this->pdo;
        }
        return $this;
    }
	
    /**
     * 链接数据库
     * @param array $config //数据库配置
     */
    protected function _connect($dbname = "t_user3") {
        try {
            //获取数据库配置
            $config = include(BASE_DIR . "dbconfig.php");
            $dbconfig = $config[$dbname];
            $this->pdo = new PDO("mysql:host={$dbconfig['hostname']};dbname={$dbconfig['database']};port={$dbconfig['port']};charset={$dbconfig['char_set']}", $dbconfig['username'], $dbconfig['password'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbconfig['char_set']}",
            ));
        } catch (PDOException $e) {
            throw new Exception("database connection error", 10001);
        }
    }
	
	/**
     * 关闭链接
     */
    protected function _close() {
        if (!empty($this->pdo) && !($this->pdo->inTransaction())) {
            $this->stmt = null;
            // $this->pdo = null;
            $this->prep_sql = '';
            $this->prep_data = array();
            $this->retry = 0;
        }
    }

    /**
     * 初始化sql和数据
     * @param String $sql
     * @param array $data
     * @throws Exception
     * @return XiuDatabase
     */
    public function query($sql, $data) {
        if (!is_array($data)) {
            throw new Exception('The data must be array.');
        }
        if (!is_string($sql)) {
            throw new Exception('The sql must be string.');
        }
        $this->prep_sql = $sql;
        $this->prep_data = $data;

        return $this;
    }

    /**
     * 启动事务
     * @return TxDatabase
     */
    public function beginTransaction() {
        // 开启事务之前清除参与执行sql
        if (!empty($this->prep_sql)) {
            $this->prep_sql = '';
        }
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
        return $this;
    }

    /**
     * 事务回滚
     * @return boolean
     */
    public function rollback() {
        $res = false;

        if ($this->pdo->inTransaction()) {
            try {
                $res = $this->pdo->rollBack();
            } catch (PDOException $e) {
                $this->_close();
            }
        }
        $this->_close();
        return $res;
    }

    /**
     * 提交事务
     * @throws Exception
     * @return boolean|unknown
     */
    public function commit() {
        $res = null;
        if ($this->pdo->inTransaction()) {
            try {
                $res = $this->pdo->commit();
            } catch (PDOException $e) {
                // 出问题回滚 写入日志
                $this->pdo->rollBack();
                $this->_close();
                
                return false;
            }
            $this->_close();
            return $res;
        } else {
            $this->_close();
            throw new Exception('There is no active transaction.');
        }
    }

    /**
     * 查询所有数据
     * @return array
     */
    public function fetchAll() {
        $this->checkStatus();
        $exe = $this->exec();
        if($exe){
            $res = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
            $res = false;
        }
        $this->_close();
        return $res;
    }

    /**
     * 查询结果集中指定的一列
     * @param int $column_num
     * @return array
     */
    public function fetchColumn($column_num) {
        $this->checkStatus();
        $exe = $this->exec();
        if($exe){
            $res = $this->stmt->fetchColumn($column_num);
        }else{
            $res = false;
        }
        $this->_close();
        return $res;
    }

    /**
     * 查询一条数据
     * @return array
     */
    public function fetchOne() {
        $this->checkStatus();
        $exe = $this->exec();
        if($exe){
            $res = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            $res = false;
        }
        $this->_close();
        return $res;
    }

    /**
     * 获取最后插入的自增索引id
     * @return number
     */
    public function lastInsertId() {
        $this->checkStatus();
        $exe = $this->exec();
        if ($exe) {
            $res = $this->pdo->lastInsertId();
        } else {
            $res = false;
        }
        $this->_close();
        return $res;
    }

    /**
     * 获取sql执行后影响的行数
     * @return int
     */
    public function affectedCount() {
        $this->checkStatus();
        $exe = $this->exec();
        if ($exe) {
            $res = $this->stmt->rowCount();
        } else {
            $res = false;
        }
        $this->_close();
        return $res;
    }

    /**
     * 更新记录
     * @param String $table
     * @param array $updateArr
     * @param array $where
     */
    public function update($table, $updateArr, $where) {
        if (empty($updateArr) || empty($where)) {
            return 0;
        }
        $setStr = "";
        $whereStr = "";
        $data = array();
        foreach ($updateArr as $field => $value) {
            $setStr .= "`{$field}`=:{$field},";
            $data[":{$field}"] = $value;
        }
        foreach ($where as $field => $value) {
            $whereStr .= "`{$field}`=:{$field},";
            $data[":{$field}"] = $value;
        }
        $setStr = substr($setStr, 0, count($setStr) - 2);
        $whereStr = substr($whereStr, 0, count($whereStr) - 2);
        $sql = "update `{$table}` set {$setStr} where {$whereStr}";
        $this->query($sql, $data);
        unset($setStr);
        unset($whereStr);
        unset($data);
    }

    /**
     * 插入一条记录
     * @param String $table
     * @param array $insertArr
     */
    public function insert($table, $insertArr) {
        if (empty($insertArr)) {
            return 0;
        }
        $fieldStr = "";
        $valueStr = "";
        $data = array();
        foreach ($insertArr as $field => $value) {
            $fieldStr .= "`{$field}`,";
            $valueStr .= ":{$field},";
            $data[":{$field}"] = $value;
        }
        $fieldStr = substr($fieldStr, 0, count($fieldStr) - 2);
        $valueStr = substr($valueStr, 0, count($valueStr) - 2);
        $sql = "insert into `{$table}`({$fieldStr}) values({$valueStr})";
        $this->query($sql, $data);
        unset($fieldStr);
        unset($valueStr);
        unset($data);
    }

    /**
     * 检查pdo句柄
     * @throws Exception
     */
    private function checkStatus() {
        if (empty($this->pdo)) {
            throw new Exception('pdo is null.');
        }
    }

    /**
     * 执行语句
     * 调整为单条执行，执行完毕后清理 sql
     * @throws Exception
     */
    private function exec() {
        // 校验数据是否正确
        if (!is_string($this->prep_sql) || !is_array($this->prep_data)) {
            throw new Exception('Type is error.');
        }
        $this->stmt = $this->pdo->prepare($this->prep_sql, array(
            PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY
        ));
        $data_keys = array_keys($this->prep_data);
        $data_values = array_values($this->prep_data);
        for ($j = 0; $j < count($this->prep_data); $j ++) {
            $type = $this->pdo_type[gettype($data_values[$j])];
            if ($type == PDO::PARAM_INT) {
                $data_values[$j] = intval($data_values[$j]);
            }
            $this->stmt->bindParam($data_keys[$j], $data_values[$j], $type);
        }
		try{
			$res = $this->stmt->execute();
		} catch (Exception $ex) {
			$res = false;
		}
		if(!$res){
			//执行出错，如果是连接失败，重连
			$errInfo = $this->stmt->errorInfo();
            $errno = $errInfo[1];
            $err = $this->prep_sql." params: ".json_encode($this->prep_data)." err: ".json_encode($errInfo);
            
            if ($errno == 2006) {
                $dbname = array_search($this->pdo, self::$arrdbset);
                if ($this->retry > 3) {
                    
                    return false;
                }
                $this->retry++;
				//重新连接
                $this->_connect($dbname);
				self::$arrdbset[$dbname] = $this->pdo;
                return $this->exec();
            }
            return false;
		}
		
        $this->prep_sql = '';
        $this->prep_data = array();
        return true;
    }

    /**
     * 显示完整的sql
     * @param String $sql
     * @param array $param
     * @return string|mixed
     */
    public function showSql($sql, $param) {
        if (empty($param)) {
            return "";
        }
        foreach ($param as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_numeric($value)) {
                $values[] = intval($value);
            } else {
                $values[] = '"' . $value . '"';
            }
        }
        return preg_replace($keys, $values, $sql);
    }

}
