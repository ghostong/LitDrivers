<?php

require(__DIR__ . '/vendor/autoload.php');

//MySQL 操作类测试
use \Lit\Drivers\LiMySQL;

//可连接多个数据库
$mysql = new LiMySQL('192.168.0.230', '3306', 'root', '123456', 'click');
$mysql2 = new LiMySQL('192.168.0.244', '3306', 'root', '123456', 'dbname');

$mysql->fetchAll("show variables like '%version%'");
//$mysql2->FetchAll ("show variables like '%version%'") ;

//从结果集中获取一行
$mysql->fetchOne('select * from `user` where `id` = 1');

//获取包含结果集中所有行的数组
$mysql->fetchAll('select * from `user` limit 10');

//根据条件获取一条数据
$mysql->getOne('user', [['id', '=', '1'], ['age', '=', 2]]);

//根据条件获取多条数据
$mysql->getAll('user', [['id', '>', '1'], ['age', '>', 2]]);

//添加一条数据
$mysql->add('user', array('user_name' => 'lily', 'age' => 12));

//删除数据
$mysql->del('user', 'id= ? or id = ?', 12, 24);

//更新数据
$mysql->update('user', 'user_name=? where id = ?', 'lucy', 12);

//获取最后的错误信息
$mysql->lastError();

//获取最后的SQL语句
$mysql->lastSql();

//获取最后的自增ID
$mysql->lastInsertId();

//more ...


//Redis 操作类测试
use \Lit\Drivers\LiRedis;

//可连接多个Redis
$redis = new LiRedis('192.168.0.231');
$redis2 = new LiRedis('192.168.0.232');

//保存一条数据到Redis
$redis->set('OneOfRedisKey', 'I love Redis', 3600);

//从Redis中获取一条数据
$redis->get('OneOfRedisKey');

$redis->lpush('OneOfList', 'I love Redis');

$redis->rpop('OneOfList');

$redis->counter("testKey", "+");
var_dump($redis->counter("testKey"));

//more ...


//Memcache 部分
use \Lit\Drivers\LiMemcached;

//可连接多个Memcached 集群
$mem = new LiMemcached('192.168.0.230', 11211);
$mem2 = new LiMemcached('192.168.0.231', 11211);

//从Memcached中获取一个值
$mem->get('OneOfMemcacheKey');

//保存一条数据到Memcached
$mem->set('OneOfMemcacheKey', '30', 0);

$mem->fetchAll(['OneOfMemcacheKey', 'OneOfMemcacheKey1']);

//more ...