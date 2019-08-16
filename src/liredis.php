<?php

/**
 * Redis 操作类
 * */

namespace lit\drivers;

class LiRedis {

    protected $Host;
    protected $Port;
    protected $UserName;
    protected $PassWord;
    protected $DbNum;
    protected $LastKey;
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
    protected function Connect () {
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
            } catch ( Exception $e ) {
                if ( $this->Env == 'product' ) {
                    die ('Redis connection failed');
                }else{
                    echo $e->getMessage();
                }
            }
        }
        return $ConnObj;
    }

    /**
     * string 部分
     * */
    public function Get ( $Key ) {
        $Rds = $this->Connect();
        return $Rds->get ( $Key );
    }

    public function Set ( $Key, $Val, $Seconds = 0 ) {
        $Rds = $this->Connect();
        if ( $Seconds > 0 ) {
            return $Rds->set ( $Key, $Val, $Seconds );
        }else{
            return $Rds->set ( $Key, $Val );
        }
    }

    public function Del($Key) {
        $Rds = $this->Connect();
        return $Rds->del($Key);
    }
    
    public function Keys ($Filed){
        $Rds = $this->Connect();
        return $Rds->keys($Filed);
    }

    public function Exists ($Filed) {
        $Rds = $this->Connect();
        return $Rds->exists($Filed);
    }

    /**
     * List部分
     * */
    public function Lpush($Key , $str = ''){
        $Rds = $this->Connect();
        return $Rds->lpush($Key,$str);
    }
    
    public function Rpush($Key , $str = ''){
        $Rds = $this->Connect();
        return $Rds->rpush($Key,$str);
    }

    public function Rpop($Key){
        $Rds = $this->Connect();
        return $Rds->Rpop($Key);
    }

    public function Lpop($Key){
        $Rds = $this->Connect();
        return $Rds->Lpop($Key);
    }

    public function Llen($Key){
        $Rds = $this->Connect();
        return $Rds->Llen($Key);
    }
    
    public function LRange($Key,$Start,$End){
        $Rds = $this->Connect();
        return $Rds->lRange($Key,$Start,$End);
    }
    
    public function LRem($Key,$Start,$End){
        $Rds = $this->Connect();
        return $Rds->lRem($Key,$Start,$End);
    }
    
    public function LTrim($Key,$Start,$End){
        $Rds = $this->Connect();
        return $Rds->lTrim($Key,$Start,$End);
    }

    /**
     * hashes 部分
     * */
    public function Hset($Key,$Filed,$Value){
        $Rds = $this->Connect();
        return $Rds->Hset($Key,$Filed,$Value);
    }

    public function Hget($Key,$Filed){
        $Rds = $this->Connect();
        return $Rds->Hget($Key,$Filed);
    }

    public function HgetAll($Key){
        $Rds = $this->Connect();
        return $Rds->HgetAll($Key);
    
    }

    public function Hlen($Key){
        $Rds = $this->Connect();
        return $Rds->hlen($Key);
    }

    public function Hdel($Key,$Field){
        $Rds = $this->Connect();
        return $Rds->hdel($Key,$Field);
    }

    public function Hexists($Key,$Field){
        $Rds = $this->Connect();
        return $Rds->hExists($Key,$Field);
    }

    /**
     * set 类型
     * */
    public function Sadd ($Key,$Field) {
        $Rds = $this->Connect();
        return $Rds->sadd($Key,$Field);
    }

    public function Smembers ($Key) {
        $Rds = $this->Connect();
        return $Rds->smembers($Key);
    }

    public function Srem ($Key, $value) {
        $Rds = $this->Connect();
        return $Rds->srem($Key, $value);
    }

    /**
     * 有序集合部分
     * */
    public function Zadd ( $Key,$Score,$Member ){
        $Rds = $this->Connect();
        return $Rds->Zadd($Key,$Score,$Member);
    }
    
    public function Zcard  ( $Key ){
        $Rds = $this->Connect();
        return $Rds->Zcard ($Key);
    }

    public function Zrevrange ($Key, $Start = 0,$End = 10) {
        $Rds = $this->Connect();
        return $Rds->zrevrange($Key,$Start,$End);
    }
    
    public function Zrange ($Key, $Start = 0,$End = 10) {
        $Rds = $this->Connect();
        return $Rds->zrange($Key,$Start,$End);
    }

    public function Zremrangebyrank  ($Key, $Start = 0,$End = 10) {
        $Rds = $this->Connect();
        return $Rds->zremrangebyrank($Key,$Start,$End);
    }

    /**
     * 订阅部分
     * */
    public function Subscribe ($Channel,$Callback){
        $Rds = $this->Connect();
        return $Rds->subscribe($Channel,$Callback);
    }

    public function Publish ($Channel,$Val){
        $Rds = $this->Connect();
        return $Rds->publish($Channel,$Val);
    }

    public function getLastError (){
        $Rds = $this->Connect();
        return $Rds->getLastError();
    }

    public function Rename($oldName , $newName){ 
        $Rds = $this->Connect();
        return $Rds->rename($oldName,$newName);
    }

    public function multi (){
        $Rds = $this->Connect();
        return $Rds->multi(Redis::PIPELINE);
    }

    public function exec (){
        $Rds = $this->Connect();
        $Rds->exec();
    }

    public function RedisClose(){
        $Rds = self::$Instance[$this->DSNMd5];
        if ( is_object($Rds) ){
            $Rds->close();
            unset ($Rds);
        }
    }
    
    public function Expire($Key,$Seconds = 0){
        $Rds = $this->Connect();
    	return $Rds->expire($Key,$Seconds);
    }
    
    /**
     * 计数器
     * $Key 键
     * $ac  + 加1;- 减1;0 重置;Null(或留空)获取计数器的值
     * */
    public function Counter ( $Key , $ac = Null) {
        $Rds = $this->Connect();
        if($ac === '+') {
            return $Rds->incr($Key);
        }elseif ($ac === '-') {
            return $Rds->decr($Key);
        }elseif ($ac == '0') {
            return $this->Set($Key,0);
        }elseif ($ac === Null) {
            return $this->Get($Key);
        }else{
            return false;
        }
    }

    public function Help(){
        Reflection::Export( new ReflectionClass(__CLASS__) );
    }
    
    function __destruct () {
    
    }
}
