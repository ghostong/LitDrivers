<?php

/**
 * MySQL数据库操作类
 * */

namespace Lit\Drivers;

class LiMySQL {

    protected $DSN;
    protected $DSNMd5;
    protected $UserName;
    protected $PassWord;
    protected $ErrorInfo;
    protected $LastInsertId = 0;
    protected $LastSql='';
    protected $Env;
    private static $Instance = array();

    function __construct ( $Host='127.0.0.1', $Port='3306', $UserName='', $PassWord='', $DBName='', $Charset='utf8' ) {
        $this->DSN = "mysql:host={$Host};port=$Port;dbname={$DBName};charset={$Charset}";
        $this->DSNMd5 = md5 ($this->DSN );
        $this->UserName = $UserName;
        $this->PassWord = $PassWord;
        $this->Env = 'product';
    }

    //设置运行环境
    public function SetEnv ( $env ) {
        $this->Env = $env;
    }
    
    //创建连接
    protected function Connect () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new \PDO ( $this->DSN, $this->UserName, $this->PassWord );
                $ConnObj->setAttribute(\PDO::ATTR_EMULATE_PREPARES,false);
            } catch ( \PDOException $e ) {
                if ( $this->Env == 'product' ) {
                    die ('Database connection failed');
                }else{
                    die ($e->getMessage());
                }
            }
        }
        return $ConnObj;
    }

    /**
     * 执行一条复杂语句
     * example:
     * Query ('select * from `table` where `id` = 8 limit 1 ');
     * */
    public function Query ( $Sql ) {
        $PDO = $this->Connect();
        $PDOStatement = $PDO->query($Sql);
        $this->LastSql = $Sql;
        if( !$PDOStatement ){
            $this->ErrorInfo = $PDO->errorInfo();
            return false;
        }else{
            return $PDOStatement;
        }
    }

    /**
     * 从结果集中获取下一行
     * example: 
     * FetachOne ('select * from `table` where `id` = 8 limit 1 ');
     * */
    public function FetchOne ( $Sql ) {
        $PDOStatement = $this->Query( $Sql );
        if( !$PDOStatement ){
            return array();
        }else{
            return $PDOStatement->fetch( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 返回一个包含结果集中所有行的数组
     * example:
     * FetchAll ('select * from `table` where `class_id` = 10');
     * */
    public function FetchAll ( $Sql ) {
        $PDOStatement = $this->Query( $Sql );
        if( !$PDOStatement ){
            return array();
        }else{
            return $PDOStatement->fetchAll( \PDO::FETCH_ASSOC ) ;
        }
    }

    /**
     * 执行一条预处理语句
     * example:
     * Execute ( 'select * from `table` where `id` = ? limit ? ', array(8,1) ) ;
     * Or
     * Execute ( 'select * from `table` where `id` = :id limit 1 ', array('id'=>8) ) ;
     * */
    public function Execute  ( $Sql, $InputParam = array() ) {
        $this->LastSql = $Sql;
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare( $Sql );
        if( !$PDOStatement ){
            $this->ErrorInfo = $PDO->errorInfo();
            return false;
        }
        if ( $PDOStatement->execute( $InputParam ) ) {
            $this->LastInsertId = $PDO->lastInsertId();
            return $PDOStatement;
        }else{
            $this->ErrorInfo=$PDOStatement->errorInfo();
            return false;
        }
    }

    /**
     * 根据条件获取一条数据
     * example:
     * GetOne ( 'table', 'id = ? and `class_id` = ?' , 8, 6 );
     * */
    public function GetOne ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "select * from {$Table} where $Expression limit 1";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return array();
        }else{
            return $PDOStatement->fetch( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 根据条件获取多条数据
     * example:
     * GetAll ( 'table', '`class_id` = ? ' , 6 );
     * */
    public function GetAll ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "select * from {$Table} where $Expression ";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return array();
        }else{
            return $PDOStatement->fetchAll( \PDO::FETCH_ASSOC );
        }
    }

    /**
     * 写入一条数据
     * example: 
     * Add ( 'table', array('name'=>'lucy','age'=>10) )
     * Add ( 'table', array('name'=>'lily','age'=>10) )
     * */
    public function Add ( $Table, $Data ) {
        if ( !is_array($Data) || empty($Data) ) {
            return false;
        }
        $Fields = '';
        $Values = '';
        $InputParam = array();
        foreach ( $Data as $Key => $Val ) {
            $Fields .= " `$Key` ,";
            $Values .= " ? ,";
            $InputParam[] = $Val;
        }
        $Fields = rtrim( $Fields, ',' );
        $Values = rtrim( $Values, ',' );
        $Sql = "insert into `{$Table}` ({$Fields}) values ({$Values})";
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if( !$PDOStatement ){
            return false;
        }else{
            if ( $PDOStatement->rowCount() > 0 ) {
                return true;
            }else{
                $this->ErrorInfo = $PDOStatement->errorInfo();
                return false;
            }
        }
    }

    /**
     * 删除数据
     * example:
     * Del ( 'table' , 'id = ? or class_id = ?' , 7, 5 );
     * */
    public function Del ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "delete from `{$Table}` where {$Expression}";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return 0;
        }else{
            return $PDOStatement->rowCount();
        }
    }

    /**
     * 更新操作
     * example:
     * Update ( 'table', 'set `age` = `age` + 1 where `id` < ?', 10 )
     * */
    public function Update ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "update `{$Table}` set $Expression";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return 0;
        }else{
            return $PDOStatement->rowCount();
        }
    }

    //获取参数
    private function GetInputParam ( $Args, $Num ) {
        for ( $i = 0; $i < $Num ; $i ++ ) {
            unset($Args[$i]);
        }
        return array_values($Args);
    }

    public function LastError () {
        return $this->ErrorInfo;
    }

    public function LastInsertId () {
        return $this->LastInsertId;
    }

    public function LastSql () {
        return $this->LastSql;
    }
    
    public function Help(){
        \Reflection::Export( new \ReflectionClass(__CLASS__) );
    }

    function __destruct () {
    
    }

}
