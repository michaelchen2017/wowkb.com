<?php
include '../_inc.php';

//授权判断
$url = rawurldecode($_GET['system_cache_url']);
$key = md5($url.conf( 'system.md5key' ));
if( $_GET['system_cache_key']!=$key ){ echo ' Key Error!'; exit; }


//获取缓存文件清单
$cache=new Cache();
if(!empty($_GET['system_cache_type']))$cache->cacheType=$_GET['system_cache_type'];
$cacheID=md5($url);
$n=intval($_GET['system_cache_n']);

$list=array();
if(!empty($_GET['system_cache_domain'])){
	$domain=explode(',',$_GET['system_cache_domain']);
	foreach($domain as $val){
		$list[]=DOCUROOT.$cache->getCacheFile($cacheID,$n,$val);
	}
}else{
	$cache->cacheDomain=false;
	$list[]=DOCUROOT.$cache->getCacheFile($cacheID,$n);
}


//执行清理
$result=$cache->doDelete($list);

debug::d($result);

?>