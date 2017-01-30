<?php
/**
参数说明：
	$argv[1]=项目名称
	$argv[2]=SVN根目录
	$argv[3]=WWW根目录
	$argv[4]=svn用户
	$argv[5]=svn密码
	$argv[6]=运行用户
	$argv[7]=运行组

调用示例：
	include("inc.comm.php");
	include( DOCUROOT."/incldue/svn/trunk.php");
	debug::g();		
	if(empty($argv[1])){echo "请输入参数！\n";exit;}
	$trunkroot=empty($argv[2])?"/pub/svn":$argv[2];
	$wwwroot=empty($argv[3])?"/pub/www":$argv[3];
	$config=array(
		"svnuser"=>empty($argv[4])?"weiqi":$argv[4],
		"svnpass"=>empty($argv[5])?"jgaoabj":$argv[5],
		"user"=>empty($argv[6])?"www":$argv[6],
		"group"=>empty($argv[7])?"www":$argv[7],
	);
	
	$obj=new trunk();
	$obj->init($argv[1],$trunkroot,$wwwroot);
	$obj->build($config);
*/
class trunk{
	var $trunk;
	var $trunkroot;
	var $htdoc;
	var $wwwroot;
	var $item;
	
	var $debug=true;

	//初始化变量
	function init($item,$trunkroot,$wwwroot){
		$this->item=$item;
		$this->trunkroot=$trunkroot;
		$this->trunk=$this->trunkroot."/".$this->item;
		$this->wwwroot=$wwwroot;
		$this->htdoc=$this->wwwroot."/".$this->item;
	}
	
	//创建执行程序
	function build($config){
		$this->createSVNRoot();
		$this->createTrunk();
		$this->setHooks($config);
		$this->createWwwRoot();
		$this->setAutoScript();
		$this->checkout($config);
		$this->setOwn($config);				
	}

	//建立版本库文件夹
	function createSVNRoot(){		
		files::mkdirs( $this->trunk );		
		$this->debug("己创建目录".$this->trunk);
	}
	
	//建立版本库
	function createTrunk(){
		$output = shell_exec("svnadmin create --fs-type bdb ". $this->trunk);
		if(!empty($output)){$this->debug($output);}
		$this->debug("己创建 SVN 版本库 ".$this->trunk);
	}
	
	//设置钩子程序
	function setHooks($config){
		$tpl="#!/bin/sh\n";
		$tpl.="export LANG=zh_CN.UTF-8\n";
		$tpl.="export LC_ALL=zh_CN.UTF-8\n";
		$tpl.="/usr/bin/svn update {$this->htdoc} --username={$config['svnuser']} --password={$config['svnpass']}\n";
		
		file_put_contents($this->trunk."/hooks/post-commit",$tpl);
		$output = shell_exec("chmod +x {$this->trunk}/hooks/post-commit");
		$this->debug("己设置钩子程序".$this->trunk."/hooks/post-commit");
	}
	
	//建立网页目录
	function createWwwRoot(){		
		files::mkdirs( $this->htdoc );		
		$this->debug("己创建目录".$this->htdoc);
	}
	
	//设置自动更新程序
	function setAutoScript(){
		$output = shell_exec("cp ".DOCUROOT."/config/*.php ".$this->htdoc."/");
		$output = shell_exec("cp ".DOCUROOT."/system/svn/svnsync.php ".$this->htdoc."/");
		$this->debug("己设置自动更新程序".$this->htdoc."/svnsync.php");
	}	
	
	//检出版本库0版本文件
	function checkout($config){
		$cmd="svn checkout http://127.0.0.1/svn/{$this->item} {$this->htdoc}  --username={$config["svnuser"]} --password={$config["svnpass"]}";
		$this->debug($cmd);
		
		$cmd="chown -R {$config["user"]}:{$config["group"]} {$this->htdoc}";
		$this->debug($cmd);
		
		//$output = shell_exec();
		//if(!empty($output)){$this->debug($output);}		
	}	
	
	//设置目录权限
	function setOwn($config){
		$output = shell_exec("chown -R {$config["user"]}:{$config["group"]} {$this->trunk}");
		$this->debug("己设置版本库权限为".$config["user"]);
		
		$output = shell_exec("chown -R {$config["user"]}:{$config["group"]} {$this->htdoc}");
		$this->debug("己设置web目录权限为".$config["user"]);
		
		$this->debug("===========================================");
		$this->debug("操作完成!");
	}
	
	function debug($msg){
		if ($this->debug){
			flush();
			echo $msg."\n";
		}
	}
	
}
?>