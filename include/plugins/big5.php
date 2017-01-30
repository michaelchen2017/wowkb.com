<?php
/**
 * 设置或取消繁体转换
 */
include '../_inc.php';

$key = 'lang_big5';
$_GET['translate'] = isset($_GET['translate'])?intval($_GET['translate']):0;

if(!empty($_GET['translate'])){
	setcookie($key,$_GET['translate'],time()+31536000,"/",conf('global','session.sessiondomain'));
}else{
	setcookie($key, '', 1, '/', conf('global','session.sessiondomain'));
	if (isset($_COOKIE[$key])) unset($_COOKIE[$key]);
}

$url = empty($_SERVER["HTTP_REFERER"])?"/":$_SERVER["HTTP_REFERER"];
go($url);