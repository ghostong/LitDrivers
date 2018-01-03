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
    private static $Instance = array();

    function __construct ( $Host='127.0.0.1', $Port='3306', $UserName='', $PassWord='', $DBName='', $Charset='utf8' ) {
        $this->DSN = "mysql:host={$Host};port=$Port;dbname={$DBName};charset={$Charset}";
        $this->DSNMd5 = md5 ($this->DSN );
        $this->UserName = $UserName;
        $this->PassWord = $PassWord;
    }
    
    //创建连接
    protected function Connect () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new PDO ( $this->DSN, $this->UserName, $this->PassWord );
                $ConnObj->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            } catch (PDOException $e) {
                //根据业务逻辑自己定制逻辑
                if ( true ) {
                    die ('Database connection failed');
                }else{
                    echo $e->getMessage();
                }
            }
        }
        return $ConnObj;
    }

    //执行一条复杂语句
    public function Query ($Sql) {
        $PDO = $this->Connect();
        $PDOStatement = $PDO->query($Sql);
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return false;
        }else{
            return $PDOStatement;
        }
    }

    //从结果集中获取下一行
    public function FetchOne ( $Sql, $Where = array() ) {
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare( $Sql );
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return array();
        }
        $PDOStatement->execute( $Where );
        return $PDOStatement->fetch(PDO::FETCH_ASSOC);
    }

    //返回一个包含结果集中所有行的数组
    public function FetchAll ( $Sql, $Where = array() ) {
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare( $Sql );
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return array();
        }
        $PDOStatement->execute( $Where );
        return $PDOStatement->fetchAll( PDO::FETCH_ASSOC ) ;
    }

    //写入一条数据
    public function Add ( $Table, $Data = array() ) {
        if ( empty($Data) ) {
            return false;
        }
        $Fields = '';
        $Values = '';
        foreach ( $Data as $Key => $Val ) {
            $Fields .= " `$Key`,";
            $Values .= " :{$Key},";
        }
        $Fields = rtrim( $Fields, ',' );
        $Values = rtrim( $Values, ',' );
        $Sql = "insert into `{$Table}` ({$Fields}) values ({$Values})";
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare ( $Sql );
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return 0;
        }
        foreach ( $Data as $Key => $Val ) {
            $PDOStatement->bindValue( ':'.$Key, $Val );
        }
        $PDOStatement->execute();
        $this->ErrorInfo=$PDOStatement->errorInfo();
        $this->LastInsertId = $PDO->lastInsertId();
        if ( $PDOStatement->rowCount() > 0 ) {
            return true;
        }else{
            return false;
        }
    }

    //简单删除,只包含and条件
    public function Del ( $Table, $Where = array (), $Limit = 1 ) {
        if (empty($Where)) {
            return 0;
        }
        $WhereArr = array();
        foreach ( $Where as $Key => $Val ){
            $WhereArr[]= "`{$Key}` = :{$Key}";
        }
        $WhereStr = implode (" and ",$WhereArr);
        if ( is_numeric($Limit) && $Limit > 0 ) {
            $LimitStr = " limit {$Limit}";
        }else{
            $LimitStr = '';
        }
        $Sql = "delete from `{$Table}` where {$WhereStr} {$LimitStr}";
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare ( $Sql );
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return 0;
        }
        foreach ( $Where as $Key => $Val ) {
            $PDOStatement->bindValue( ':'.$Key, $Val );
        }
        $PDOStatement->execute();
        $this->ErrorInfo=$PDOStatement->errorInfo();
        return $PDOStatement->rowCount();
    }

    //简单更新,只包含and条件
    public function Update ( $Table, $Data = array (), $Where = array(), $Limit = 1) {
        if ( empty($Data) || empty($Where) ) {
            return 0;
        }
        $SetArr = array ();
        foreach ($Data as $Key => $Val) {
            $SetArr[] = " `{$Key}` = :Set_{$Key}";
        }
        $SetStr = implode (" , ",$SetArr);

        $WhereArr = array();
        foreach ( $Where as $Key => $Val ){
            $WhereArr[]= "`{$Key}` = :Where_{$Key}";
        }
        $WhereStr = implode (" and ",$WhereArr);
        if ( is_numeric($Limit) && $Limit > 0 ) {
            $LimitStr = " limit {$Limit}";
        }else{
            $LimitStr = '';
        }
        $Sql = "update `{$Table}` set {$SetStr} where {$WhereStr} $LimitStr";
        $PDO = $this->Connect();
        $PDOStatement = $PDO->prepare ( $Sql );
        if(!$PDOStatement){
            $this->ErrorInfo = $PDO->errorInfo();
            return 0;
        }
        foreach ( $Data as $Key => $Val ) {
            $PDOStatement->bindValue( ':Set_'.$Key, $Val );
        }
        foreach ( $Where as $Key => $Val ) {
            $PDOStatement->bindValue( ':Where_'.$Key, $Val );
        }
        $PDOStatement->execute();
        $this->ErrorInfo=$PDOStatement->errorInfo();
        return $PDOStatement->rowCount();
    }

    public function LastError () {
        return $this->ErrorInfo;
    }

    public function LastInsertId () {
        return $this->LastInsertId;
    }
    
    public function Help(){
        Reflection::Export(new ReflectionClass(__CLASS__));
    }

    function __destruct () {
    
    }

}
