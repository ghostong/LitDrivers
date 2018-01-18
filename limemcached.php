<?php

/**
 * Memcached 操作类
 * */

class LiMemcached {
    protected $Host;
    protected $Port;
    protected $Servers;
    protected $DSNMd5;
    private static $Instance = array ();
    function __construct ( $Host='127.0.0.1', $Port='11211', $Servers = array() ) {
        $this->Host = $Host;
        $this->Port = $Port;
        $this->Servers = $Servers;
        $this->DSNMd5 = md5( $Host.':'.$Port.':'.serialize ($Servers) );
        $this->Env = 'product';
    }

    //创建连接
    public function Connect () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new Memcached();
                if ( !empty($this->Servers) ) {
                    $ConnObj->addServers( $this->Servers );
                }else{
                    $ConnObj->addServer( $this->Host, $this->Port );
                }
            } catch (Exception $e) {
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
        $Mem = $this->Connect();
        return $Mem->get( $Key );
    }

    public function Set ( $Key, $Val, $Ttl = 0 ) {
        $Key = $this->Key( $Key );
        $Mem = $this->Connect();
        return $Mem->set ($Key,$Val,$Ttl);
    }


    public function Help(){
        Reflection::Export(new ReflectionClass(__CLASS__));
    }

}
