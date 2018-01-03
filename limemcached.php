<?php

class LiMemcached {
    protected $Host;
    protected $Port;
    protected $Servers;
    function __construct ( $Host='127.0.0.1', $Port='11211', $Servers = array() ) {
        $this->Host = $Host;
        $this->Port = $Port;
        $this->Servers = $Servers;
    }

    //创建连接
    public function Connect () {
        try {
            $Connect = new Memcached();
            if ( !empty($this->Servers) ) {
                $Connect->addServers( $this->Servers );
            }else{
                $Connect->addServer( $this->Host, $this->Port );
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $Connect;
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
