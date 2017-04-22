<?php
include 'inc.comm.php';

// ignore_user_abort(true);
// set_time_limit(0);
// //  $filename = DOCUROOT."/plugins/home/{$_SERVER["HTTP_HOST"]}.php";
// //  if(file_exists($filename)) include $filename;

define( 'AppName', "prewowkb" );

$app = new Factory();
$app->sess=true;
$app->run("account");


?>	


