<?php
/**
 * DEMO:
 * 
 * $.ajax({
		type: "GET",
	    url: "/include/plugins/jquery.php",
  	    data: "app=profile,ajax&sess=true&func=saveAvatar&args[]="+ val0+"&args[]="+ val1+"&args[]="+ val2+"&args[]="+ val3,
	    success:function( val ){eval(val); profile.funcname(res); }
	});
 */
include '../_inc.php';

if( empty($_GET['app']) || empty($_GET['func']) ) exit;
if( isset($_GET['sess']) ) func_initSession();

$app = explode(",", $_GET['app']); //profile,ajax  => 应用程序, 执行文件
$func = $_GET['func'];
$filename = DOCUROOT.'/'.conf('appname',$app[0]).'/script/'.$app[1].'.php';

if( file_exists($filename) ) include $filename;
if( !function_exists($func)) exit;

//全局session检测设置
$ajax_sess_check = isset($ajax_sess_check)?$ajax_sess_check:array();

$ajax=new Ajax();
if(!empty($_GET['json']))$ajax->json=true;
$ajax->load( $func );
?>