<?php
/**
 * 清理缓存调用测试
 * http://beta.wenxuecity.com/include/plugins/cleartb.php?mod=struct
 * http://beta.wenxuecity.com/include/plugins/cleartb.php?mod=bbs_user
 */
include '../_inc.php';

func_initSession();
if( empty( $_GET['mod'] ) ){echo "mod is empty!"; exit;}

$mod = $_GET['mod'];

if(empty($_GET['node'])){
	if(empty($_SESSION['UserLevel'])){echo "Please Login First!";exit;}
	$servers = include DOCUROOT.'/admin/tools/srcSync/svnServerList.php';

	echo "Rebuid cache of {$mod}...<hr><br>";
	foreach($servers as $host) echo file_get_contents("http://{$host}/include/plugins/cleartb.php?node={$host}&mod={$mod}");
	
	exit;
}else{
	//系统结构
	if($mod=='struct'){
		$_GET['clear']='clearstruct';
		$obj = new System();
		$obj->getAppNameConfig();
	}
	
	//全部表结构
	if($mod=='alltb'){
		$cachePath = DOCUROOT."/cache/table/*";
		shell_exec("rm -rf {$cachePath}");
	}
	
	//指定表结构
	if(!in_array($mod, array('struct','alltb'))){
		$obj=load($mod);
		if(!empty($obj))$obj->flushTableInfo();
	}
	
	echo "{$_GET['node']} is OK!<br>";
}