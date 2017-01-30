<?php
class view extends Action{
	public $post_obj;
	public $post_pic_obj;
	public $post_comments_obj;
	public $username_obj;
	
	function __construct(){
		parent::__construct();
		
		$this->post_obj = load("page_post");
		$this->post_pic_obj = load("page_postPics");
		$this->post_comments_obj = load("page_postComments");
		$this->username_obj = load("account_metaUser");
		//handle userid login
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		$this->assign("userid", $userid);
	}
	
	function ACT_index(){
		//debug::d($_SESSION);exit;
		if(isset($_GET['id']) && !empty($_GET['id'])){
			$result = $this->post_obj->getOne("*", array("post_id"=>$_GET['id'], "visible"=>1));
			$pic_res  = $this->post_pic_obj->getOne("*", array("fk_post_id"=>$_GET['id'], "visible"=>1));
			
			//handle comments
			$comments_res  = $this->post_comments_obj->getAll("*", array("fk_post_id"=>$_GET['id'], "visible"=>1));
			$num_of_comments = count($comments_res);
			
			foreach($comments_res as $i=>$val){
				$userid = $val['user_id'];
				$username_array = $this->username_obj->getOne(array("username"), array("pk_id"=>$userid, "visible"=>1));
				$comments_res[$i]['username'] = $username_array['username'];
			}
			
			$this->assign("post_id", $_GET['id']);
			$this->assign("result", $result);
			$this->assign("pic_res", $pic_res);
			$this->assign("comments", $comments_res);
			$this->assign("numofcomments", $num_of_comments);
			
		}else{
			go("/");
		}
	}
	function ACT_comments(){
		if(isset($_POST['submit'])){
			if(!isset($_SESSION['userid'])){
				go("/account/account.php?act=login");
			}
			$comment_info = array(
					"fk_post_id" => $_POST['post_id'],
					"comment" => $_POST['comment'],
					"user_id" => $_SESSION['userid'],
					"created_time" => times::getTime(),
			);
			
			$res  = $this->post_comments_obj->Insert($comment_info);
			if(!empty($res)){
				go("/page/view.php?act=index&id={$_POST['post_id']}");
			}else{
				go("/");
			}
		}
	}
	
	function ACT_conversation(){
		
	}
}