<?php
// 根据浏览器支持情况输出gzip压缩
if( !defined('NoHTML') ){
	if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) ){
		if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
	}
}

// 服务器使用标准时间
date_default_timezone_set('Etc/GMT+0');

//使用集中式模板
if( !defined('centreModel') ) define( 'centreModel', false );

//设置调试IP
if( !defined('DEBUGIP') ) define( 'DEBUGIP', '127.0.0.1' );

//定义语言包文件
if( !defined('LANGBASE') ) define( 'LANGBASE', 'cn' );

//数据查询记录集占用最大内存
if( !defined('SqlMaxMemorySize') ) define( 'SqlMaxMemorySize', 16777216 );

// 关闭系统错误
if(empty($_GET['debug'])) error_reporting(0);	

// 用于调试
if(!empty($_GET['debug'])) debug::g();
?>