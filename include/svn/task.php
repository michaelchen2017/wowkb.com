<?php
/*发布svn同步指令*/
set_time_limit(0);
func_checkCliEnv();

if(defined("svnServerList")){
	$conf = include svnServerList;
}else{
	$conf = include DOCUROOT."/admin/tools/srcSync/svnServerList.php";
}
$path = empty($argv[1])?"root":$argv[1];

$obj = func_initMemcached("sourceNode");;
echo "SVN Rsync {$path}\n";
foreach($conf as $key){
	$obj->add(systemVersion."svn_".$key,$path,false,0);
	if(!empty($argv[2])){
		$version=intval($argv[2]);
		if(!empty($version)) $obj->add(systemVersion."svn_ver_".$key,$version,false,0);
	}
	echo "Added {$key}...\n";
}

echo "The system is going to finish sync in 5～30 Sec....\n";
?>