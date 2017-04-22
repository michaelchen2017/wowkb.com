<?php
class listing extends Action{
	public $obj_panos;
	public $obj_posts;
	public $obj_zuopin;
	public $obj_user;
	public $obj_products;
	
	function __construct(){
		parent::__construct();
		
		$this->obj_panos = load("account_panos");
		$this->obj_posts = load("account_posts");
		$this->obj_zuopin = load("account_zuopin");
		$this->obj_user = load("account_users");
		$this->obj_products = load("account_material");
		
		$user_type = "";
		
		//$_SESSION ['UserLevel'] = 6; //模拟管理登录，用于debug
		if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])){
			
			$this->assign("userid", $_SESSION['userid']);
			$userid = $_SESSION['userid'];
			$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
			$user_type = $user_type_arr['account_type'];
			
// 			$this->assign("user_type", $user_type);
		}else{
			$userid = 0;
			unset($_SESSION ['UserLevel']);
			$this->assign("userid", $userid);
		}
		$this->assign("user_type", $user_type);
	}
	
	function ACT_index(){
		//debug::d($_SESSION);exit;
		$tmp = array();
		$result = $this->obj_panos->getList("*", array("visible"=>1, "order"=>array("pk_id"=>'DESC')), 30);
		
		$this->assign("result", $result);
	}
	
	function ACT_designs(){
		$res = $this->obj_zuopin->getAll("*", array("visible"=>1));
		$this->assign("res", $res);
	}
	
	function ACT_products(){
		$res = $this->obj_products->getList("*", array("visible"=>1) , 30);
		$this->assign("res", $res);
	}
	
// 	function ACT_search(){
// 		//debug::d($_POST);exit;
// 		if(!empty($_POST['keyword'])){
// 			$keyword = dbtools::escape($_POST['keyword']);
// 			//debug::d($keyword);exit;
// 			$results = $this->post->getList("SELECT *  FROM  post WHERE content LIKE '%$keyword%' and visible = 1");
// 			//$this->assign("results", $results);
// 			$_SESSION['results'] = $results;
			
// 		}
// 		go("/page/listing.php?act=search_results");
// 	}
	
// 	function ACT_search_results(){
// 		if(isset($_SESSION['results']) && !empty($_SESSION['results'])){
// 			$results = $_SESSION['results'];
// 			unset($_SESSION['results']);
// 		}else{
// 			$results = 0;
// 		}
// 		$this->assign("results", $results);
// 	}
}