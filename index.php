<?php

#error_reporting(E_ALL & ~E_NOTICE);

ini_set ('display_errors','on');

header("Content-type: text/html; charset=utf-8");


include ('limysql.php');
include ('liredis.php');
include ('limemcached.php');

$mysql = new limysql('127.0.0.1','3306','root','123456','cdn_log');

//var_dump ( $mysql-> FetchOne ('select * from ali_user where ali_id = ? ', array('i-i-bp1fu4aqltezc0pgjjiq') ) );
//var_dump ( $mysql-> FetchAll ('select * from ali_user limit 10') );
//var_dump ( $mysql->Add ('ali_user',array('ali_id'=>'1224','user_name'=>'2234')) );
//var_Dump ( $mysql->LastInsertId());
//var_dump ( $mysql->Del ('ali_user', array('ali_id'=>'0','user_name'=>'0'),2 ) );
//var_Dump ( $mysql->Update('ali_user',array ('user_name'=>rand(10000,99999)), array ('ali_id'=>'22234'),1));
//$Sql  = " select * from ali_user";
//$Query = $mysql->query ($Sql);
//var_dump ( $Query->Fetch() );
//var_dump ( $mysql->LastError() );


$redis = new liRedis('192.168.0.231');

//var_dump ( $redis->get ('aaaa') );
//$redis->set('aaaa','111');

$mem = new liMemcached('192.168.0.230',11211);

var_dump($mem->get('aaa'));

$mem->set('aaa','30',0);
