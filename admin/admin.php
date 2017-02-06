<?php
include "../inc.comm.php";
define( 'AppName', 'admin' );
$app = new Factory();
$app->sess = true;
$app->run("admin");