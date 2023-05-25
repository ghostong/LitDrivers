<?php

/**
 * MySQL数据库操作类
 * */

namespace Lit\Drivers;

class LiMySQL
{

    protected $dsn;
    protected $dsnMd5;
    protected $userName;
    protected $passWord;
    protected $errorInfo = null;
    protected $lastInsertId = 0;
    protected $lastSql = '';
    private static $instance = array();

    function __construct($host = '127.0.0.1', $port = '3306', $userName = '', $passWord = '', $dbName = '', $charSet = 'utf8') {
        $this->dsn = "mysql:host={$host};port=$port;dbname={$dbName};charset={$charSet}";
        $this->dsnMd5 = md5($this->dsn);
        $this->userName = $userName;
        $this->passWord = $passWord;
    }

    //创建连接
    protected function connect($force = false) {
        $mySqlObject = &self::$instance[$this->dsnMd5];
        try {
            if (is_null($mySqlObject) || !is_object($mySqlObject) || $force) {
                $mySqlObject = new \PDO ($this->dsn, $this->userName, $this->passWord);
                $mySqlObject->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            }
        } catch (\Exception $exception) {
            $this->errorInfo = $exception->getMessage();
        }

        try {
            $mySqlObject->query("select 1");
        } catch (\Exception $exception) {
            if ($exception->getCode() == "HY000") {
                self::connect(true);
            } else {
                $this->errorInfo = $exception->getMessage();
            }
        }
        return $mySqlObject;
    }

    /**
     * 执行一条复杂语句
     * example:
     * query ('select * from `table` where `id` = 8 limit 1 ');
     * @param $sql
     * @return bool|false|\PDOStatement
     */
    public function query($sql) {
        $pdo = $this->connect();
        $pdoStatement = $pdo->query($sql);
        $this->lastSql = $sql;
        if (!$pdoStatement) {
            $this->errorInfo = $pdo->errorInfo();
            return false;
        } else {
            return $pdoStatement;
        }
    }

    /**
     * 从结果集中获取下一行
     * @param $sql
     * @return array|mixed
     * @example:  fetchOne ('select * from `table` where `id` = 8 limit 1 ');
     */
    public function fetchOne($sql) {
        $pdoStatement = $this->query($sql);
        if (!$pdoStatement) {
            return array();
        } else {
            return $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * 返回一个包含结果集中所有行的数组
     * @param $sql
     * @return array
     * @example: fetchAll ('select * from `table` where `class_id` = 10');
     */
    public function fetchAll($sql) {
        $pdoStatement = $this->query($sql);
        if (!$pdoStatement) {
            return array();
        } else {
            return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * 执行一条预处理语句
     * @param $sql
     * @param array $inputParam
     * @return bool|\PDOStatement
     * @example:
     * execute ( 'select * from `table` where `id` = ? limit ? ', array(8,1) ) ;
     * Or
     * execute ( 'select * from `table` where `id` = :id limit 1 ', array('id'=>8) ) ;
     */
    public function execute($sql, $inputParam = array()) {
        $this->lastSql = $sql;
        $pdo = $this->connect();
        $pdoStatement = $pdo->prepare($sql);
        if (!$pdoStatement) {
            $this->errorInfo = $pdo->errorInfo();
            return false;
        }
        if ($pdoStatement->execute($inputParam)) {
            $this->lastInsertId = $pdo->lastInsertId();
            return $pdoStatement;
        } else {
            $this->errorInfo = $pdoStatement->errorInfo();
            return false;
        }
    }

    /**
     * 根据条件获取一条数据
     * @param string $table 表名
     * @param array $wheres where条件数组
     * @return array|mixed
     * @example: getOne ( 'table', [['id',8],['class_id', '= ', 6]] );
     */
    public function getOne($table, $wheres) {
        list($where, $inputParam) = $this->sqlWhereFormat($wheres);
        $sql = "select * from `{$table}` where {$where} limit 1";
        $pdoStatement = $this->execute($sql, $inputParam);
        if (!$pdoStatement) {
            return array();
        } else {
            return $pdoStatement->fetch(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * 根据条件获取多条数据
     * @param string $table 表名
     * @param array $wheres where 条件数组
     * @return array
     * @example: getAll ( 'table', [['id', '<', 8],['class_id', '>', 6]] );
     */
    public function getAll($table, $wheres, $limit = null) {
        list($where, $inputParam) = $this->sqlWhereFormat($wheres);
        $sql = "select * from `{$table}` where {$where} " . ($limit ? " limit " . $limit : "");
        $pdoStatement = $this->execute($sql, $inputParam);
        if (!$pdoStatement) {
            return array();
        } else {
            return $pdoStatement->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    /**
     * 写入一条数据
     * @param $table
     * @param $data
     * @return bool
     * @example:
     * Add ( 'table', array('name'=>'lucy','age'=>10) )
     * Add ( 'table', array('name'=>'lily','age'=>10) )
     */
    public function add($table, $data) {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $fields = '';
        $values = '';
        $inputParam = array();
        foreach ($data as $key => $val) {
            $fields .= " `$key` ,";
            $values .= " ? ,";
            $inputParam[] = $val;
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');
        $sql = "insert into `{$table}` ({$fields}) values ({$values})";
        $pdoStatement = $this->execute($sql, $inputParam);
        if (!$pdoStatement) {
            return false;
        } else {
            if ($pdoStatement->rowCount() > 0) {
                return true;
            } else {
                $this->errorInfo = $pdoStatement->errorInfo();
                return false;
            }
        }
    }

    /**
     * 删除数据
     * example:
     * del ( 'table' , 'id = ? or class_id = ?' , 7, 5 );
     * @param $table
     * @param $expression
     * @return int
     */
    public function del($table, $expression /*[, $inputParam, $inputParam, ... ]*/) {
        $sql = "delete from `{$table}` where {$expression}";
        $inputParam = $this->getInputParam(func_get_args(), 2);
        $pdoStatement = $this->execute($sql, $inputParam);
        if (!$pdoStatement) {
            return 0;
        } else {
            return $pdoStatement->rowCount();
        }
    }

    /**
     * 更新操作
     * @param string $table 表名称
     * @param array $sets set数组
     * @param array $wheres where条件数组
     * @return int
     * @example: update ( 'table', ['age' => 1],[['id', '<', 10],['age', 0]] )
     */
    public function update($table, $sets, $wheres) {
        if (empty($sets) || empty($wheres)) {
            return 0;
        }
        list($set, $inputParam1) = $this->sqlSetFormat($sets);
        list($where, $inputParam2) = $this->sqlWhereFormat($wheres);

        $sql = "update `{$table}` set {$set} where {$where}";
        $inputParam = array_merge($inputParam1, $inputParam2);
        $pdoStatement = $this->execute($sql, $inputParam);
        if (!$pdoStatement) {
            return 0;
        } else {
            return $pdoStatement->rowCount();
        }
    }

    //获取参数
    private function getInputParam($args, $num) {
        for ($i = 0; $i < $num; $i++) {
            unset($args[$i]);
        }
        return array_values($args);
    }

    public function lastError() {
        return $this->errorInfo;
    }

    public function lastInsertId() {
        return $this->lastInsertId;
    }

    public function lastSql() {
        return $this->lastSql;
    }

    protected function sqlSetFormat($sets) {
        $set = array_map(function ($k) {
            return "`$k` = ?";
        }, array_keys($sets));
        return [implode(", ", $set), array_values($sets)];
    }

    protected function sqlWhereFormat($wheres) {
        $whereArray = $inputValues = [];
        foreach ($wheres as $where) {
            if (count($where) == 2) {
                $whereArray[] = "`{$where[0]}` = ?";
            } elseif (count($where) == 3) {
                $whereArray[] = "`{$where[0]}` {$where[1]} ?";
            }
            $inputValues[] = end($where);
        }
        return [implode(" and ", $whereArray), $inputValues];
    }

}
