<?php
include "../inc.comm.php";
define('AppName','page');

$app = new Factory();
$app->sess = true;
$app->run('listing');

?>