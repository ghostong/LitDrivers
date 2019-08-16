<?php

require(__DIR__.'/vendor/autoload.php');

//MySQL 操作类测试
use  \lit\litool\limysql;

//可连接多个数据库
$mysql = new limysql('192.168.0.230','3306','root','123456','click') ;
//$mysql2 = new limysql('192.168.0.244','3306','root','123456','dbname') ;

//获得帮助
//$mysql->help();

$mysql->FetchAll ("show variables like '%version%'") ;
//$mysql2->FetchAll ("show variables like '%version%'") ;

//从结果集中获取一行
$mysql-> FetchOne ('select * from `user` where `id` = 1') ;

//获取包含结果集中所有行的数组
$mysql-> FetchAll ('select * from `user` limit 10') ;

//根据条件获取一条数据
$mysql->GetOne( 'user', 'id = ? and id = ?', 1, 3 ) ;

//根据条件获取多条数据
$mysql->GetAll( 'user', '1 limit ?', 4 );

//添加一条数据
$mysql->Add ('user', array('user_name'=>'lily', 'age'=>12) ) ;

//删除数据
$mysql->Del ( 'user', 'id= ? or id = ?', 12 , 24 );

//更新数据
$mysql->Update( 'user', 'user_name=? where id = ?', 'lucy', 12 ) ;

//获取最后的错误信息
$mysql->LastError() ;

//获取最后的SQL语句
$mysql->LastSql() ;

//获取最后的自增ID
$mysql->LastInsertId() ;

//more ...


//Redis 操作类测试
use  \lit\litool\redis;

//可连接多个Redis
$redis = new liRedis('192.168.0.231');
$redis2 = new liRedis('192.168.0.232');

//保存一条数据到Redis
$redis->set('OneOfRedisKey', 'I love Redis', 3600);

//从Redis中获取一条数据
$redis->get ('OneOfRedisKey') ;

$redis->Lpush('OneOfList','I love Redis');

$redis->Rpop('OneOfList','I love Redis') ;

//more ...



//Memcache 部分
use  \lit\litool\memcache;

//可连接多个Memcached 集群
$mem = new liMemcached('192.168.0.230',11211);
$mem2 = new liMemcached('192.168.0.231',11211);

//从Memcached中获取一个值
$mem->get('OneOfMemcacheKey');

//保存一条数据到Memcached
$mem->set('OneOfMemcacheKey','30',0);

$mem->FetchAll(['OneOfMemcacheKey','OneOfMemcacheKey1']) ;

//more ...