<?php
include "../inc.comm.php";
define( 'AppName', 'account' );
// Report all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

$controller = strtolower($_GET['c']);
if (empty($controller)) $controller = 'account';

$app = new Factory();
$act = $_GET['act'];
if (($controller == 'ajax') && ($act == 'change_my_avatar')) {
	$app->sess = false;
} else $app->sess = true;
$app->debug = false;
$app->run($controller);
