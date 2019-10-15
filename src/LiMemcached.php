<?php

/**
 * Memcached 操作类
 * */

namespace Lit\Drivers;

class LiMemcached {
    protected $Host;
    protected $Port;
    protected $UserName;
    protected $PassWord;
    protected $Servers;
    protected $DSNMd5;
    protected $Env;
    private static $Instance = array ();
    function __construct ( $Host='127.0.0.1', $Port='11211', $UserName='', $PassWord='', $Servers = array() ) {
        $this->Host = $Host;
        $this->Port = $Port;
        $this->UserName = $UserName;
        $this->PassWord = $PassWord;
        $this->Servers = $Servers;
        $this->DSNMd5 = md5( $Host.':'.$Port.':'.$UserName.':'.$PassWord.":".serialize ($Servers) );
        $this->Env = 'product';
    }

    //创建连接
    public function Connect () {
        $ConnObj = &self::$Instance[$this->DSNMd5];
        if ( !isset( $ConnObj ) || !is_object( $ConnObj ) ) {
            try {
                $ConnObj = new \Memcached();
                $ConnObj->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                $ConnObj->setOption(\Memcached::OPT_TCP_NODELAY, true); //php memcached有个bug,get不存在,有固定40ms延迟，开启此参数可以避免
                if ( !empty($this->Servers) ) {
                    $ConnObj->addServers( $this->Servers );
                }else{
                    $ConnObj->addServer( $this->Host, $this->Port );
                }
                if ($this->UserName && $this->PassWord) {
                    $ConnObj->setSaslAuthData($this->UserName,$this->PassWord);
                }
            } catch (Exception $e) {
                if ( $this->Env == 'product' ) {
                    die ('Memcached connection failed');
                }else{
                    echo $e->getMessage();
                }
            }
        }
        1;
        return $ConnObj;
    }

    public function Get ( $Key ) {
        $Mem = $this->Connect();
        return $Mem->get( $Key );
    }

    public function Set ( $Key, $Val, $Ttl = 0 ) {
        $Mem = $this->Connect();
        return $Mem->set ($Key,$Val,$Ttl);
    }

    public function FetchAll ( $KeyArr ) {
        $Mem = $this->Connect();
        $Mem->getDelayed($KeyArr);
        $Data = $Mem->fetchAll();
        $Ret = array();
        foreach ( $Data as $v ) {
            $Ret[$v['key']] = $v['value'];
        }
        return $Ret;
    }

    public function Help(){
        \Reflection::Export(new \ReflectionClass(__CLASS__));
    }

}
