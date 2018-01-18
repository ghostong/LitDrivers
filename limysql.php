<?php

/**
 * MySQL数据库操作类
 * */

class LiMySQL {

    protected $DSN;
    protected $DSNMd5;
    protected $UserName;
    protected $PassWord;
    protected $ErrorInfo;
    protected $LastInsertId = 0;
    protected $LastSql='';
    private static $Instance = array();

    function __construct ( $Host='127.0.0.1', $Port='3306', $UserName='', $PassWord='', $DBName='', $Charset='utf8' ) {
        $this->DSN = "mysql:host={$Host};port=$Port;dbname={$DBName};charset={$Charset}";
        $this->DSNMd5 = md5 ($this->DSN );
        $this->UserName = $UserName;
        $this->PassWord = $PassWord;
        $this->Env = 'product';
    }
    
    //创建连接
    protected function Connect () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new PDO ( $this->DSN, $this->UserName, $this->PassWord );
                $ConnObj->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            } catch ( PDOException $e ) {
                if ( $this->Env == 'product' ) {
                    die ('Database connection failed');
                }else{
                    echo $e->getMessage();
                }
            }
        }
        return $ConnObj;
    }

    //执行一条复杂语句
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

    //从结果集中获取下一行
    public function FetchOne ( $Sql ) {
        $PDOStatement = $this->Query( $Sql );
        if( !$PDOStatement ){
            return array();
        }else{
            return $PDOStatement->fetch( PDO::FETCH_ASSOC );
        }
    }

    //返回一个包含结果集中所有行的数组
    public function FetchAll ( $Sql ) {
        $PDOStatement = $this->Query( $Sql );
        if( !$PDOStatement ){
            return array();
        }else{
            return $PDOStatement->fetchAll( PDO::FETCH_ASSOC ) ;
        }
    }

    //执行一条预处理语句
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

    //根据条件获取一条数据
    public function GetOne ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "select * from {$Table} where $Expression limit 1";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return array();
        }else{
            return $PDOStatement->fetch( PDO::FETCH_ASSOC );
        }
    }

    //根据条件获取多条数据
    public function GetAll ( $Table, $Expression /*[, $InputParam, $InputParam, ... ]*/ ) {
        $Sql = "select * from {$Table} where $Expression ";
        $InputParam = $this->GetInputParam(func_get_args(),2);
        $PDOStatement = $this->Execute ( $Sql , $InputParam );
        if ( !$PDOStatement ) {
            return array();
        }else{
            return $PDOStatement->fetchAll( PDO::FETCH_ASSOC );
        }
    }

    //写入数据
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

    //删除
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

    //更新
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
        Reflection::Export(new ReflectionClass(__CLASS__));
    }

    function __destruct () {
    
    }

}
