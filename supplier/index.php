<?php
include "../inc.comm.php";
define('AppName','supplier');

$app = new Factory();
$app->sess = true;
$app->run('admin');

?>