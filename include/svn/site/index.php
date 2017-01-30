<?php
include 'files.php';

$arr=files::fileAll("/storage/svn/");

echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
echo "<h2>SVN of yestogo.com</h2><hr>";
echo "<p style='margin:0 0 0 50px;line-height:22px;'>";
foreach($arr as $f){
        if(is_dir("/storage/svn/{$f}")){
                echo "<a href='/repos/{$f}/' target='_self'>{$f}</a>&nbsp;&nbsp;<span style='font-size:8pt'>";
                echo date("Y-m-d H:i",filemtime("/storage/svn/{$f}"));
                echo "</span></br>";
        }
}
echo '</p>';
echo "<hr>";
echo "&copy; 2012 www.yestogo.com All Rights Reserved.";
?>