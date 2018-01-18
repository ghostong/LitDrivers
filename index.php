<?php

#error_reporting(E_ALL & ~E_NOTICE);

ini_set ('display_errors','on');

header("Content-type: text/html; charset=utf-8");


include ('limysql.php');
include ('liredis.php');
include ('limemcached.php');

$mysql = new limysql('127.0.0.1','3306','root','123456','cdn_log');

#var_dump ( $mysql-> FetchOne ('select * from ali_user where ali_id = 1') );
#var_dump ( $mysql-> FetchAll ('select * from ali_user limit 10') );
#var_dump ( $mysql->GetOne('ali_user','ali_id = ?','i-23a4s1z9k'));
#var_dump ( $mysql->GetAll('ali_user','1 limit ?',4));
#var_dump ( $mysql->Add ('ali_user',array('ali_id'=>'12124','user_name'=>'22341')) );
#var_dump ( $mysql->Del ('ali_user', 'ali_id= ? or ali_id = ?', 0 , 12124 ) );
#var_dump ( $mysql->Update('ali_user','user_name=? where ali_id = ?','haha',12124));

#var_dump ( $mysql->LastError() );
#var_dump ( $mysql->LastSql() );
#var_dump ( $mysql->LastInsertId() );

$redis = new liRedis('192.168.0.231');

//var_dump ( $redis->get ('aaaa') );
//$redis->set('aaaa','111');

$mem = new liMemcached('192.168.0.230',11211);

#var_dump($mem->get('aaa'));

#$mem->set('aaa','30',0);
