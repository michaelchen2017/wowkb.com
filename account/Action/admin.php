<?php
class admin extends Action{
	
	function __construct() {
		parent::__construct();
	
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		$this->assign("userid", $userid);
		
		if(!isset($_SESSION ['UserLevel']) || $_SESSION ['UserLevel'] != 6){
			go("/");
		}
	}
	
	function ACT_index(){
		
		
	}
	
	function ACT_delete_user(){
		
	}
	
	function ACT_find_all_users(){
		
	}
	
	
}