<?php
class admin extends Action{
	public $obj_user;
	
	function __construct(){
		parent::__construct();
	
		$this->obj_user = load("account_users");
		
		//$_SESSION ['UserLevel'] = 6; //模拟管理登录，用于debug
		if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])){
				
			$this->assign("userid", $_SESSION['userid']);
			$userid = $_SESSION['userid'];
			$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
			$user_type = $user_type_arr['account_type'];
				
			$this->assign("user_type", $user_type);
		}else{
			$userid = 0;
			unset($_SESSION ['UserLevel']);
			$this->assign("userid", $userid);
		}
	}
	
	function ACT_index(){
		
	}
	
	function ACT_supplier_intro(){
		
	}
	
	function ACT_supplier_product_design(){
		
	}
	
	function ACT_supplier_product(){
		
	}
	
	function ACT_supplier_service(){
		
	}
	
	function ACT_supplier_showroom(){
		
	}
	
	function ACT_supplier_design(){
		
	}
}