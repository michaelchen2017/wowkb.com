<?php
include 'inc.comm.php';

// ignore_user_abort(true);
// set_time_limit(0);
// //  $filename = DOCUROOT."/plugins/home/{$_SERVER["HTTP_HOST"]}.php";
// //  if(file_exists($filename)) include $filename;

define( 'AppName', "prewowkb" );
// $files = DOCUROOT . "/data/test/test.jpg";
// $cmd = DOCUROOT . "/krpano-1.19-pr8/krpanotools makepano -config=" .DOCUROOT ."/krpano-1.19-pr8/templates/normal.config -panotype=sphere -hfov=360  ".$files;

// $output = exec($cmd);

// echo $output;
// // print_r($output);
//echo "<pre>$output</pre>";


$app = new Factory();
$app->sess=true;
$app->run("account");
// debug::d($app);exit;

?>	


<!-- <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en"> -->
<!--   <head> -->
<!--     <title>SWFObject dynamic embed - step 3</title> -->
<!--     <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> -->
<!--     <script type="text/javascript" src="/js/swfobject.js"></script> -->

<!--     <script type="text/javascript"> -->
<!-- //         swfobject.embedSWF("/data/livingroom.swf", "myContent", "100%", "100%", "9.0.0"); -->
<!--     </script> -->

<!--   </head> -->
<!--   <body> -->
<!--     <div id="myContent" width=100% height=100%> -->
<!--       <p>Alternative content</p> -->
<!--     </div> -->
<!--   </body> -->
<!-- </html> -->

