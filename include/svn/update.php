<?php
/*每分钟自动执行，使用svn同步同步源码*/
set_time_limit(0);
func_checkCliEnv();

if(!is_file( "/etc/updateid.php" ))exit();
$key=include "/etc/updateid.php";

$obj = func_initMemcached("sourceNode");
$val = $obj->get(systemVersion."svn_".$key);
$version = $obj->get(systemVersion."svn_ver_".$key);

if(!empty($val)){
	$obj->delete(systemVersion."svn_".$key);
	if(!empty($version))$obj->delete(systemVersion."svn_ver_".$key);
	
	if($val=='root'){
		$path = DOCUROOT;
	}else{
		if(substr($val,0,1)!='/') $val = "/".$val;
		if(substr($val,0,strlen(DOCUROOT))!=DOCUROOT) $val = DOCUROOT . $val;
		$path = $val;
	}
	
	$log = "";
	if(empty($version)){
		$log.= "/usr/bin/svn update {$path}\n";
		$log.= shell_exec("/usr/bin/svn update {$path}");
	}else{
		$log.= "/usr/bin/svn -r {$version} update {$path}\n";
		$log.= shell_exec("/usr/bin/svn -r {$version} update {$path}");
	}
	
	if(!empty($log)) file_put_contents("/var/log/svnUpdate.log", $log);
}
?>