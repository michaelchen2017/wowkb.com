<?php
class Env{
	var $ACT=null;
	
	function __construct($obj){
		$this->ACT=$obj;
	}
	
	//加载后台环境
	function admin(){
		include(  DOCUROOT."/admin/dashboard/Config/userface.php" );
		$this->ACT->assign( "userface", USERFACE );
		$this->ACT->env="admin";
	}

	//加载用户空间环境
	function space(){
		$obj=load("panel_info");
		$obj->loadSpaceInfo($this->ACT);
		
		$sitetpl = conf('global','tpl');
		$this->ACT->tpl = DOCUROOT . "/space/panel/Tpl/{$sitetpl}/index.html";
		$this->ACT->env="space";
	}
}
?>