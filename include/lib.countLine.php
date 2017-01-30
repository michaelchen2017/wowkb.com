<?php

/**

	此程序可以计算源代码行数，下面是使用示例
	
	set_time_limit(0);
	
	$obj=new countLine();
	$obj->debug=true;
	
	//读取指定文件行数
	//echo $obj->countFile('/pub/Workspace/php/core/configure/inc.db.php');
	
	//读取指定目录下文件的行数
	$obj->countDir('/pub/Workspace/php/coreframe');
	echo $obj->all;

*/

class countLine{
	var $all=0;
	var $debug=false;
	var $ext=array('php','htm','html','css','js');

	function fileAll($path) {
		$list = array ();
		if (($hndl = @ opendir($path)) === false) {
			return $list;
		}
		while (($file = readdir($hndl)) !== false) {
			if ($file != '.' && $file != '..') {
				$list[] = $file;
			}
		}
		closedir($hndl);
		return $list;
	}

	function countDir($path){
		if(substr($path,strlen($path)-1)!='/') $path=$path.'/';
		
		foreach ($this->fileAll($path) as $name) {
			if (is_dir($path . $name)) {
				$this->countDir($path . $name);
			} 
			if (is_file($path . $name)) {
				$ext=$this->getext($path . $name);
				if(in_array($ext,$this->ext)) $this->all+=$this->countFile($path . $name);
			}
		}
	}

	function getext($filename){
		$filename=strtolower($filename);
		if (strstr($filename, "\\") || strstr($filename, "/")) {
			$filename = basename($filename);
		}
		if (strstr($filename, ".")) {
			return mb_substr(strrchr($filename, '.'), 1);
		} else {
			return false;
		}
	}
	
	function countFile($file_path){
		$i = 0 ;
		$handle = fopen($file_path, "r");
		while ($line = fgets($handle)){
			$i++;
		}
		fclose($handle);
		if($this->debug) echo $file_path."<br>\n";
		return $i;
	}
}
?>