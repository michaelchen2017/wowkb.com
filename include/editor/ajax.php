<?php
/**
 * 此文件多处调用，根据url参数key生成与厂商全称name相关的搜索结果，并以xml形式返回,供AJAX调用
 * weiqi  07/01/10
 * TODO 使用前缀匹配的搜索引擎方式实现
 */

include '../_inc.php';

if (!isset($_SESSION)){func_initSession();}
$conn = func_getDB( "user" );

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
echo "<root>\n";

if(isset($_SESSION['UserID'])){
	$word = urldecode ($_GET['key']);
	$word = str_replace("'","''",$word);
	$sql = "SELECT id,name FROM manufacturer WHERE zt=1 AND name like '{$word}%' ORDER BY name ASC LIMIT 0,20";
	$rs = $conn->getAll($sql);
	$id='0';
	$name='none';
	foreach($rs as $val){
		$temp=xml::formatxml($val['name']);
		$temp=str_replace("|","",$temp);

		$id=$id.'|'.$val['id'];
		$name=$name.'|'.$temp;
	}
	echo "<id>{$id}</id>\n";
	echo "<name>{$name}</name>\n";
}else{
	echo "<id>0</id>\n";
	echo "<name>未登录，无法读取系统信息!</name>\n"; 
}
echo "</root>\n";
?>