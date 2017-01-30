<?php
func_checkCliEnv();

$memObj = func_initMemcached("sourceNode");
	
$memCheckID = systemVersion.'source_code_checkout_lock';
$status = $memObj->delete($memCheckID);
echo "Delete source_code_checkout_lock..\n";
echo var_dump($status);
echo "\n";


if(defined("svnServerList")){
	$conf = include svnServerList;
}else{
	$conf = include DOCUROOT."/admin/tools/srcSync/svnServerList.php";
}
$servers = explode(',',$conf);//所有需要同步的服务器节点

if(!empty($servers)){
	foreach($servers as $key){
		if(empty($key))continue;
		$status = $memObj->delete(systemVersion."svn_".$key);
		echo "Delete {$key}..\n";
		echo var_dump($status);
		echo "\n";
	}
}

echo "\nsvn cleanup ".DOCUROOT."\n";
shell_exec("svn cleanup ".DOCUROOT);