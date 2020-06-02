<?php

/**
 * MySQL数据库操作类
 * */

namespace Lit\Drivers;

class LiMySQL {

    protected $dsn;
    protected $dsnMd5;
    protected $userName;
    protected $passWord;
    protected $errorInfo = null;
    protected $lastInsertId = 0;
    protected $lastSql='';
    private static $instance = array();

    function __construct ( $host = '127.0.0.1', $port = '3306', $userName = '', $passWord = '', $dbName = '', $charSet = 'utf8' ) {
        $this->dsn = "mysql:host={$host};port=$port;dbname={$dbName};charset={$charSet}";
        $this->dsnMd5 = md5 ($this->dsn );
        $this->userName = $userName;
        $this->passWord = $passWord;
    }
    
    //创建连接
    protected function connect ( $force = false) {
        $mySqlObject = &self::$instance[$this->dsnMd5];
        if ( is_null($mySqlObject) || !is_object($mySqlObject) || $force ) {
            try {
                $mySqlObject = new \PDO ( $this->dsn, $this->userName, $this->passWord );
                $mySqlObject->setAttribute(\PDO::ATTR_EMULATE_PREPARES,false);
            } catch ( \PDOException $e ) {
                $this->errorInfo = $e->getMessage();
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
    public function query ( $sql ) {
        $pdo = $this->connect();
        $pdoStatement = $pdo->query( $sql );
        $this->lastSql = $sql;
        if( !$pdoStatement ){
            $pdo = $this->connect(true);
            $pdoStatement = $pdo->query( $sql );
            $this->lastSql = $sql;
            if( !$pdoStatement ){
                $this->errorInfo = $pdo->errorInfo();
                return false;
            }else{
                return $pdoStatement;
            }
        }else{
            return $pdoStatement;
        }
    }

    /**
     * 从结果集中获取下一行
     * @example:  fetchOne ('select * from `table` where `id` = 8 limit 1 ');
     * @param $sql
     * @return array|mixed
     */
    public function fetchOne ( $sql ) {
        $pdoStatement = $this->query( $sql );
        if( !$pdoStatement ){
            return array();
        }else{
            return $pdoStatement->fetch( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 返回一个包含结果集中所有行的数组
     * @example: fetchAll ('select * from `table` where `class_id` = 10');
     * @param $sql
     * @return array
     */
    public function fetchAll ( $sql ) {
        $pdoStatement = $this->query( $sql );
        if( !$pdoStatement ){
            return array();
        }else{
            return $pdoStatement->fetchAll( \PDO::FETCH_ASSOC ) ;
        }
    }

    /**
     * 执行一条预处理语句
     * @example:
     * execute ( 'select * from `table` where `id` = ? limit ? ', array(8,1) ) ;
     * Or
     * execute ( 'select * from `table` where `id` = :id limit 1 ', array('id'=>8) ) ;
     * @param $sql
     * @param array $inputParam
     * @return bool|\PDOStatement
     */
    public function execute  ( $sql, $inputParam = array() ) {
        $this->lastSql = $sql;
        $pdo = $this->connect();
        $pdoStatement = $pdo->prepare( $sql );
        if( !$pdoStatement ){
            $pdo = $this->connect(true);
            $pdoStatement = $pdo->prepare( $sql );
            if( !$pdoStatement ){
                $this->errorInfo = $pdo->errorInfo();
                return false;
            }
        }
        if ( $pdoStatement->execute( $inputParam ) ) {
            $this->lastInsertId = $pdo->lastInsertId();
            return $pdoStatement;
        }else{
            $this->errorInfo=$pdoStatement->errorInfo();
            return false;
        }
    }

    /**
     * 根据条件获取一条数据
     * @example: getOne ( 'table', 'id = ? and `class_id` = ?' , 8, 6 );
     * @param $table
     * @param $expression
     * @return array|mixed
     */
    public function getOne ( $table, $expression /*[, $inputParam, $inputParam, ... ]*/ ) {
        $sql = "select * from {$table} where {$expression} limit 1";
        $inputParam = $this->getInputParam(func_get_args(),2);
        $pdoStatement = $this->execute ( $sql , $inputParam );
        if ( !$pdoStatement ) {
            return array();
        }else{
            return $pdoStatement->fetch( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 根据条件获取多条数据
     * @example: getAll ( 'table', '`class_id` = ? ' , 6 );
     * @param $table
     * @param $expression
     * @return array
     */
    public function getAll ( $table, $expression /*[, $inputParam, $inputParam, ... ]*/ ) {
        $sql = "select * from {$table} where $expression ";
        $inputParam = $this->getInputParam(func_get_args(),2);
        $pdoStatement = $this->execute ( $sql , $inputParam );
        if ( !$pdoStatement ) {
            return array();
        }else{
            return $pdoStatement->fetchAll( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 写入一条数据
     * @example:
     * Add ( 'table', array('name'=>'lucy','age'=>10) )
     * Add ( 'table', array('name'=>'lily','age'=>10) )
     * @param $table
     * @param $data
     * @return bool
     */
    public function add ( $table, $data ) {
        if ( !is_array($data) || empty($data) ) {
            return false;
        }
        $fields = '';
        $values = '';
        $inputParam = array();
        foreach ( $data as $key => $val ) {
            $fields .= " `$key` ,";
            $values .= " ? ,";
            $inputParam[] = $val;
        }
        $fields = rtrim( $fields, ',' );
        $values = rtrim( $values, ',' );
        $sql = "insert into `{$table}` ({$fields}) values ({$values})";
        $pdoStatement = $this->execute ( $sql , $inputParam );
        if( !$pdoStatement ){
            return false;
        }else{
            if ( $pdoStatement->rowCount() > 0 ) {
                return true;
            }else{
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
    public function del ( $table, $expression /*[, $inputParam, $inputParam, ... ]*/ ) {
        $sql = "delete from `{$table}` where {$expression}";
        $inputParam = $this->getInputParam(func_get_args(),2);
        $pdoStatement = $this->execute ( $sql , $inputParam );
        if ( !$pdoStatement ) {
            return 0;
        }else{
            return $pdoStatement->rowCount();
        }
    }

    /**
     * 更新操作
     * @example: update ( 'table', 'set `age` = `age` + 1 where `id` < ?', 10 )
     * @param $table
     * @param $expression
     * @return int
     */
    public function update ( $table, $expression /*[, $inputParam, $inputParam, ... ]*/ ) {
        $sql = "update `{$table}` set {$expression}";
        $inputParam = $this->getInputParam(func_get_args(),2);
        $pdoStatement = $this->execute ( $sql , $inputParam );
        if ( !$pdoStatement ) {
            return 0;
        }else{
            return $pdoStatement->rowCount();
        }
    }

    //获取参数
    private function getInputParam ( $args, $num ) {
        for ( $i = 0; $i < $num ; $i ++ ) {
            unset($args[$i]);
        }
        return array_values($args);
    }

    public function lastError () {
        return $this->errorInfo;
    }

    public function lastInsertId () {
        return $this->lastInsertId;
    }

    public function lastSql () {
        return $this->lastSql;
    }
    
    public function help(){
        \Reflection::export( new \ReflectionClass(__CLASS__) );
    }

    function __destruct () {
    
    }

}
