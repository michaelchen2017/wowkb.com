<?php
include "../inc.comm.php";
define( 'AppName', 'demo' );
$app = new Factory();
$app->sess = true;
$app->run("upload");