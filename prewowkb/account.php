<?php
include "../inc.comm.php";
define( 'AppName', 'prewowkb' );
$app = new Factory();
$app->sess = true;
$app->run("account");

?>