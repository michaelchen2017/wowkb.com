<?php
/**
 * 设置或取消移动版
 */
include '../_inc.php';

$key = 'noWap';
$_GET['noWap'] = isset($_GET['noWap'])?intval($_GET['noWap']):0;

if(!empty($_GET['noWap'])){
	setcookie($key,$_GET['noWap'],time()+31536000,"/",conf('global','session.sessiondomain'));
}else{
	setcookie($key, '', 1, '/', conf('global','session.sessiondomain'));
	if (isset($_COOKIE[$key])) unset($_COOKIE[$key]);
}

$url = empty($_SERVER["HTTP_REFERER"])?"/":$_SERVER["HTTP_REFERER"];
go($url);