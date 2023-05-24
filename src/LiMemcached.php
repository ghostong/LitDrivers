<?php

/**
 * Memcached 操作类
 * */

namespace Lit\Drivers;

class LiMemcached
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $servers;
    protected $dsnMd5;
    protected $errorInfo = null;
    private static $instance = array();

    function __construct($host = '127.0.0.1', $port = '11211', $username = '', $password = '', $servers = array()) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->servers = $servers;
        $this->dsnMd5 = md5($host . ':' . $port . ':' . $username . ':' . $password . ":" . serialize($servers));
    }

    //创建连接
    public function connect() {
        $memcacheObject = &self::$instance[$this->dsnMd5];
        if (!isset($memcacheObject) || !is_object($memcacheObject)) {
            try {
                $memcacheObject = new \Memcached();
                $memcacheObject->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                $memcacheObject->setOption(\Memcached::OPT_TCP_NODELAY, true); //php memcached有个bug,get不存在,有固定40ms延迟，开启此参数可以避免
                if (!empty($this->servers)) {
                    $memcacheObject->addservers($this->servers);
                } else {
                    $memcacheObject->addServer($this->host, $this->port);
                }
                if ($this->username && $this->passWord) {
                    $memcacheObject->setSaslAuthData($this->username, $this->passWord);
                }
            } catch (\Exception $e) {
                $this->errorInfo = $e->getMessage();
            }
        }
        return $memcacheObject;
    }

    public function get($Key) {
        $mem = $this->connect();
        return $mem->get($Key);
    }

    public function set($Key, $Val, $Ttl = 0) {
        $mem = $this->connect();
        return $mem->set($Key, $Val, $Ttl);
    }

    public function fetchAll($KeyArr) {
        $mem = $this->connect();
        $mem->getDelayed($KeyArr);
        $data = $mem->fetchAll();
        $Ret = array();
        foreach ($data as $v) {
            $Ret[$v['key']] = $v['value'];
        }
        return $Ret;
    }

    public function lastError() {
        return $this->errorInfo;
    }

}
