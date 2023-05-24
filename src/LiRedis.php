<?php

/**
 * Redis 操作类
 * */

namespace Lit\Drivers;

class LiRedis
{
    protected $host;
    protected $port;
    protected $auth;
    protected $dbNum;
    protected $timeout;
    protected $dsnMd5;
    private static $instance = array();
    protected $errorInfo = null;
    protected $pConnect = false;


    function __construct($host = '127.0.0.1', $port = 6379, $auth = '', $dbNum = 0, $timeout = 3, $pConnect = false) {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
        $this->dbNum = $dbNum;
        $this->timeout = $timeout;
        $this->pConnect = $pConnect;
        $this->dsnMd5 = md5($host . ':' . $port . ':' . $auth . ':' . $dbNum . ':' . $pConnect);
    }

    public function connect($force = false) {
        $redisObject = &self::$instance[$this->dsnMd5];
        if (is_null($redisObject) || !is_object($redisObject) || $force) {
            try {
                $redisObject = new \Redis();
                if ($this->pConnect) {
                    $redisObject->pconnect($this->host, $this->port, $this->timeout);
                } else {
                    $redisObject->connect($this->host, $this->port, $this->timeout);
                }
                if ($this->auth) {
                    $redisObject->auth($this->auth);
                }
                if (is_numeric($this->dbNum)) {
                    $redisObject->select($this->dbNum);
                }
            } catch (\Exception $e) {
                $this->errorInfo = $e->getMessage();
            }
        }
        return $redisObject;
    }


    /**
     * 计数器
     * @param string $key 键
     * @param string $ac + 加1;- 减1;0 重置;Null(或留空)获取计数器的值
     * @return int
     * */
    public function counter($key, $ac = null) {
        $redisClient = $this->connect();
        if ($ac === '+') {
            return $redisClient->incr($key);
        } elseif ($ac === '-') {
            return $redisClient->decr($key);
        } elseif ($ac == '0') {
            return $this->set($key, 0);
        } elseif ($ac === Null) {
            return $this->get($key);
        } else {
            return 0;
        }
    }

    //解决Redis断线重连问题
    function __call($name, $arguments) {
        $redis = $this->connect();
        try {
            return call_user_func_array([$redis, $name], $arguments);
        } catch (\Exception $e) {
            $this->connect(true);
            return call_user_func_array([$redis, $name], $arguments);
        }
    }

    public function lastError() {
        if ($this->errorInfo) {
            return $this->errorInfo;
        } else {
            $redisClient = $this->connect();
            return $redisClient->getLastError();
        }
    }

}
