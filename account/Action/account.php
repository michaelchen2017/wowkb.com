<?php
class account extends Action{
	public $obj_user;
	public $obj_user_favorite;
	
	function __construct() {
		parent::__construct();
		
		$this->obj_user = load("account_users");
		$this->obj_user_favorite = load("account_user_favorite");
	}
	
	function ACT_index(){
		go("/");
	}
	
	function ACT_register(){
		
	}
	
	function ACT_signup(){
		/*
		 * Array
			(
			    [username] => michael
			    [email] => chenzixing2009@gmail.com
			    [pswd] => Abc1234567
			    [tel] => 4089812398
			    [register-submit] => 马上注册
			)
		 */
		if(isset($_POST['register-submit'])){
			$user_register_info = array(
					"reg_time" => times::getTime(),
					"username" => $_POST['username'],
					"email" => $_POST['email'],
					"pswd" => $_POST['password'],
					"tel" => isset($_POST['gender'])?$_POST['gender']:"",
					"ip" => http::getIP(),
					);
			
			//debug::d($user_register_info);exit;
			$if_exist = $this->obj_user->getOne("*", array("email"=>$_POST['email']));
			if(!empty($if_exist)){
				$error_msg = "该邮件已经注册！请选择其他邮箱，谢谢！";
				$this->assign("error_msg", $error_msg);
			}else{
				$res = $this->obj_user->Insert($user_register_info);
				
				if($res){
					go("/account/account.php?act=register_success");
				}else{
					$error_msg = "对不起哦亲~ 没有注册成功，请再次尝试，谢谢！";
					$this->assign("error_msg", $error_msg);
				}
			}
			
		}else{
			go("/");
		}
		
	}
	
	function ACT_register_success(){
		
	}
	
	function ACT_activate(){
		
	}
	
	function ACT_login(){
		/*
		 * Array
			(
			    [email] => chenzixing2009@gmail.com
			    [password] => Abc1234567
			    [login-submit] => 登 录
			)
					 
		 */
		
		if(isset($_POST) && !empty($_POST["email"]) && !empty($_POST["password"])){
			
			$result = $this->obj_user->getOne(array("uid", "pswd"), array("email"=>dbtools::escape($_POST["email"]), "visible"=>1));
// 			debug::d($result);exit;
			
			if(isset($result) && $result["pswd"] == $_POST["password"]){
				$_SESSION["userid"] = $result["uid"];
				
				go("/");
			}else{
				//邮箱或者密码不对
				$error_msg = "邮箱或者密码不对";
				$this->assign("error_msg", $error_msg);
			}
		}
		
		
	}
	
	function ACT_logout(){
		if(isset($_SESSION['userid'])){
			unset($_SESSION['userid']);
			go("/");
		}
	}
	
	function ACT_reset(){
		
	}
}