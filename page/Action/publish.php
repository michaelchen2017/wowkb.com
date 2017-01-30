<?php
class publish extends Action{
	public $obj_post;
	
	function __construct(){
		parent::__construct();
		//查看用户登录

		$this->obj_post = load("page_posts");
		//$_SESSION ['UserLevel'] = 6; //模拟管理登录，用于debug
		
		if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])){
			$this->assign("userid", $_SESSION['userid']);
		}else{
			go("/");
		}
		
	}
	
	function ACT_index(){
		/*
		 * if(empty($this->userinfo['pk_id'])){
		 * 
		 * }
		 */
		//debug::d($_POST); 
		//debug::d($_FILES);
		//exit;
		
		//debug::d($_SESSION);exit;
		if(!isset($_SESSION['userid'])){// check if the user login
			go("/");
		}
		
		if(!empty($_POST['register-submit'])){
			$_POST['fk_uid'] = $_SESSION['userid'];
			$_POST['created_time'] = times::getTime(); //times::getTime
			
			
// 			$_POST['modified_time'] = $_POST['created_time'];
// 			$id = page_postHelper::save_post($_POST);
			
			
			if(!empty($id)){
				//deal with upload file and save pic path on table post_pics
// 				page_postUploadFile::post_uploadfile($_FILES, $id);
					
				go("/page/publish.php?id={$id}");
			}else{
				$errorMsg = "post failure ! please try again!";
				$this->assign("errorMsg", $errorMsg);
			}
		}
		else{
			if(!empty($_GET['id'])){ //edit a post
				//page_post::update_post($_POST);
				$id = intval($_GET['id']);
				$result = $this->obj_post->getOne("*", array("pk_id"=>$id));
				
				$userid = $result['fk_uid'];
				if($_SESSION['userid'] != $userid){
					go("/");
				}
				
				$this->assign("result", $result);
				$this->assign("id", $id);
			}
		}
	}
	
	function ACT_delete(){
		if(!empty($_GET['id'])){
			$result = $this->obj_post->getOne(array("fk_uid"), array("pk_id"=>$_GET['id'], "visible"=>1));
			if($_SESSION['userid'] == $result['fk_uid']){
				$res = $this->obj_post->Update(array("visible"=>0), array("pk_id"=>$_GET['id']));
				if(!empty($res)){
					go("/page/publish.php");
				}
			}else{
				go("/");
			}
		}else{
			go("/");
		}
	}
}
?>
