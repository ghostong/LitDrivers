<?php

/**
 * Redis 操作类
 * */

namespace Lit\Drivers;

class LiRedis {

    protected $host;
    protected $port;
    protected $auth;
    protected $passWord;
    protected $dbNum;
    protected $timeOut;
    protected $lastKey;
    protected $dsnMd5;
    protected $errorInfo = null;
    private static $instance = array ();

    function __construct ( $host = '127.0.0.1', $port = 6379, $auth = '', $dbNum = 0, $timeOut = 0) {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
        $this->dbNum = $dbNum;
        $this->timeOut = $timeOut;
        $this->dsnMd5 = md5( $host.':'.$port.':'.$auth.':'.$dbNum.':'.$timeOut );
    }

    //创建连接
    protected function connect () {
        $redisObject = &self::$instance[$this->dsnMd5];
        if ( is_null($redisObject) || !is_object( $redisObject ) ) {
            try {
                $redisObject = new \Redis();
                $redisObject->connect( $this->host, $this->port, $this->timeOut );
                if ( $this->auth ) {
                    $redisObject->auth ( $this->auth );
                }
                if ( is_numeric( $this->dbNum ) ){
                    $redisObject->select( $this->dbNum );
                }
            } catch ( \Exception $e ) {
                $this->errorInfo = $e->getMessage();
            }
        }
        return $redisObject;
    }


    // string 部分

    public function get ( $key ) {
        $redisClient = $this->connect();
        return $redisClient->get ( $key );
    }

    public function set ( $key, $val, $Seconds = 0 ) {
        $redisClient = $this->connect();
        if ( $Seconds > 0 ) {
            return $redisClient->set ( $key, $val, $Seconds );
        }else{
            return $redisClient->set ( $key, $val );
        }
    }

    public function del($key) {
        $redisClient = $this->connect();
        return $redisClient->del($key);
    }
    
    public function keys ($Filed){
        $redisClient = $this->connect();
        return $redisClient->keys($Filed);
    }

    public function exists ($Filed) {
        $redisClient = $this->connect();
        return $redisClient->exists($Filed);
    }


    // List部分

    public function lPush($key , $str = ''){
        $redisClient = $this->connect();
        return $redisClient->lPush($key,$str);
    }
    
    public function rPush($key , $str = ''){
        $redisClient = $this->connect();
        return $redisClient->rPush($key,$str);
    }

    public function rPop($key){
        $redisClient = $this->connect();
        return $redisClient->rPop($key);
    }

    public function lPop($key){
        $redisClient = $this->connect();
        return $redisClient->lPop($key);
    }

    public function lLen($key){
        $redisClient = $this->connect();
        return $redisClient->lLen($key);
    }
    
    public function lRange($key,$Start,$End){
        $redisClient = $this->connect();
        return $redisClient->lRange($key,$Start,$End);
    }
    
    public function lRem($key,$Start,$End){
        $redisClient = $this->connect();
        return $redisClient->lRem($key,$Start,$End);
    }
    
    public function lTrim($key,$Start,$End){
        $redisClient = $this->connect();
        return $redisClient->lTrim($key,$Start,$End);
    }


    // hashes 部分

    public function hSet($key,$Filed,$value){
        $redisClient = $this->connect();
        return $redisClient->hSet($key,$Filed,$value);
    }

    public function hGet($key,$Filed){
        $redisClient = $this->connect();
        return $redisClient->hGet($key,$Filed);
    }

    public function hGetAll($key){
        $redisClient = $this->connect();
        return $redisClient->hGetAll($key);
    
    }

    public function hLen($key){
        $redisClient = $this->connect();
        return $redisClient->hLen($key);
    }

    public function hDel($key,$field){
        $redisClient = $this->connect();
        return $redisClient->hDel($key,$field);
    }

    public function hExists($key,$field){
        $redisClient = $this->connect();
        return $redisClient->hExists($key,$field);
    }


    // set 类型

    public function sAdd ($key,$field) {
        $redisClient = $this->connect();
        return $redisClient->sAdd($key,$field);
    }

    public function sMembers ($key) {
        $redisClient = $this->connect();
        return $redisClient->sMembers($key);
    }

    public function sRem ($key, $value) {
        $redisClient = $this->connect();
        return $redisClient->sRem($key, $value);
    }


     // 有序集合部分
    
    public function zAdd ( $key, $score, $member ){
        $redisClient = $this->connect();
        return $redisClient->zAdd($key,$score,$member);
    }

    public function zRem( $key, $member ){
        $redisClient = $this->connect();
        return $redisClient->zRem($key, $member);
    }
    
    public function zCard  ( $key ){
        $redisClient = $this->connect();
        return $redisClient->zCard ($key);
    }

    public function zCount  ( $key, $start, $end){
        $redisClient = $this->connect();
        return $redisClient->zCount ($key, $start, $end );
    }

    public function zRevRange ($key, $Start = 0,$End = 10) {
        $redisClient = $this->connect();
        return $redisClient->zRevRange($key,$Start,$End);
    }
    
    public function zRange ($key, $Start = 0,$End = 10) {
        $redisClient = $this->connect();
        return $redisClient->zRange( $key, $Start, $End );
    }

    public function zRemRangeByRank ( $key, $Start = 0,$End = 10 ) {
        $redisClient = $this->connect();
        return $redisClient->zRemRangeByRank($key,$Start,$End);
    }

    public function zRevRangeByScore( $key, $start, $end, $options = array() ) {
        $redisClient = $this->connect();
        return $redisClient->zRevRangeByScore( $key, $start, $end,  $options );
    }

    public function zRangeByScore( $key, $start, $end, $options = array() ) {
        $redisClient = $this->connect();
        return $redisClient->zRangeByScore( $key, $start, $end,  $options );
    }
    
    
    // 订阅部分
    
    public function subscribe ($Channel,$Callback){
        $redisClient = $this->connect();
        return $redisClient->subscribe($Channel,$Callback);
    }

    public function publish ($Channel,$val){
        $redisClient = $this->connect();
        return $redisClient->publish($Channel,$val);
    }


    //批量操作

    public function mGet( $keys = array()){
        $redisClient = $this->connect();
        return $redisClient->mget($keys);
    }

    public function multi (){
        $redisClient = $this->connect();
        return $redisClient->multi(\Redis::PIPELINE);
    }

    public function exec (){
        $redisClient = $this->connect();
        $redisClient->exec();
    }

    public function scan( & $iterator, $key ){
        $redisClient = $this->connect();
        return $redisClient->scan( $iterator, $key );
    }

    //key操作

    public function rename($oldName , $newName){
        $redisClient = $this->connect();
        return $redisClient->rename($oldName,$newName);
    }

    public function expire($key,$Seconds = 0){
        $redisClient = $this->connect();
    	return $redisClient->expire($key,$Seconds);
    }


    
    //redis扩展功能
    /**
     * 计数器
     * @param $key 键
     * @param $ac  + 加1;- 减1;0 重置;Null(或留空)获取计数器的值
     * @return int
     * */
    public function counter ( $key , $ac = null) {
        $redisClient = $this->connect();
        if($ac === '+') {
            return $redisClient->incr($key);
        }elseif ($ac === '-') {
            return $redisClient->decr($key);
        }elseif ($ac == '0') {
            return $this->Set($key,0);
        }elseif ($ac === Null) {
            return $this->Get($key);
        }else{
            return 0;
        }
    }

    public function redisClose(){
        $redisClient = self::$instance[$this->dsnMd5];
        if ( is_object($redisClient) ){
            $redisClient->close();
            unset ($redisClient);
        }
    }
    public function lastError (){
        if ($this->errorInfo) {
            return $this->errorInfo;
        }else{
            $redisClient = $this->connect();
            return $redisClient->getLastError();
        }
    }

    public function Help(){
        \Reflection::export( new \ReflectionClass(__CLASS__) );
    }
    
    function __destruct () {
    
    }
}
