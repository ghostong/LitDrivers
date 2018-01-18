<?php

/**
 * Redis 操作类
 * */

class LiRedis {

    protected $Host;
    protected $Port;
    protected $UserName;
    protected $PassWord;
    protected $DbNum;
    private static $Instance = array ();

    function __construct ( $Host = '127.0.0.1', $Port = 6379, $UserName = '', $PassWord = '', $DbNum = 0) {
        $this->Host = $Host;
        $this->Port = $Port;
        $this->UserName = $UserName;
        $this->PassWord = $PassWord;
        $this->DbNum = $DbNum;
        $this->DSNMd5 = md5( $Host.':'.$Port.':'.$UserName.':'.$PassWord.':'.$DbNum );
        $this->Env = 'product';
    }

    //创建连接
    protected function Content () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new Redis();
                $ConnObj->pconnect( $this->Host, $this->Port );
                if ( $this->UserName && $this->PassWord ) {
                    $ConnObj -> auth ( $this->UserName.':'.$this->PassWord );
                }
                if ( $this->DbNum > 0 ){
                    $ConnObj->select( $this->DbNum );
                }
            } catch ( RedisException $e ) {
                if ( $this->Env == 'product' ) {
                    die ('Redis connection failed');
                }else{
                    echo $e->getMessage();
                }
            }
        }
        return $ConnObj;
    }

    public function Key ( $Key ) {
        return substr(md5($Key),0,16);
    }

    public function Get ( $Key ) {
        $Key = $this->Key( $Key );
        $Rds = $this->Content();
        return $Rds->get ( $Key );
    }

    public function Set ( $Key, $Val ) {
        $Key = $this->Key( $Key );
        $Rds = $this->Content();
        return $Rds->set ( $Key, $Val );
    }
    
    public function Help(){
        Reflection::Export(new ReflectionClass(__CLASS__));
    }
}
