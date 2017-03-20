<?php
class view extends Action{
	public $post_obj;
	public $post_pic_obj;
	public $post_comments_obj;
	public $username_obj;
	public $obj_zuopin;
	public $obj_zuopin_material;
	public $obj_product;
	public $obj_product_zuopins;
	public $obj_user;
	
	function __construct(){
		parent::__construct();
		
		$this->post_obj = load("page_post");
		$this->post_pic_obj = load("page_postPics");
		$this->post_comments_obj = load("page_postComments");
		$this->username_obj = load("account_metaUser");
		$this->obj_zuopin = load("account_zuopin");
		$this->obj_zuopin_material = load("account_zuopin_material");
		$this->obj_user = load("account_users");
		
		$this->obj_product = load("account_material");
		$this->obj_product_zuopins = load("account_zuopin_material");
		//handle userid login
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		$this->assign("userid", $userid);
		
		
		$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
		$user_type = $user_type_arr['account_type'];
		
		$this->assign("user_type", $user_type);
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
	
	function ACT_design_detail(){
		$id = $_GET['id'];
		if(empty($id))
		{
			go("/");
		}
		$res = $this->obj_zuopin->getOne("*", array("pk_id"=>$id, "visible"=>1));
		
		$zuopin_material_ids = $this->obj_zuopin_material->getAll("*", array("fk_zid" => $id, "visible"=>1));
		
		$zuopin_materials = array();
		
		foreach ($zuopin_material_ids as $i => $val){
			$product = $this->obj_product->getOne("*", array("item_id"=>$val['fk_item_id'], "visible"=>1));
			$zuopin_materials[$i] = $product;
		}
		
		
		$this->assign("res", $res);
		$this->assign("zuopin_materials", $zuopin_materials);
		
	}
	
	function ACT_download(){
		if(isset($_GET['id']) && !empty($_GET['id'])){
			$res = $this->obj_zuopin->getOne("*", array("pk_id"=>$_GET['id'], "visible"=>1));
			$file_path = $res['pic_path'];
			$fullPath = DOCUROOT . $file_path;
		}
		else {
			go("/");
		}
		
		if ($fd = fopen ($fullPath, "r")) {
			$fsize = filesize($fullPath);
			$path_parts = pathinfo($fullPath);
			$ext = strtolower($path_parts["extension"]);
			switch ($ext) {
				case "pdf":
					header("Content-type: application/pdf");
					header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a file download
					break;
					// add more headers for other content types here
				default;
				header("Content-type: application/octet-stream");
				header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
				break;
			}
			header("Content-length: $fsize");
			header("Cache-control: private"); //use this to open files directly
			while(!feof($fd)) {
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}
		fclose ($fd);
		exit;
	}
	
	function ACT_product_detail(){
		$id = $_GET['id'];
		if(empty($id)){
			go("/");
		}
		
		$res = $this->obj_product->getOne("*", array("item_id"=>$id, "visible"=>1));
		$product_zuopin_ids = $this->obj_product_zuopins->getAll("*", array("fk_item_id"=>$res['item_id'], "visible"=>1));
		
		$product_zuopins = array();
		foreach ($product_zuopin_ids as $i => $val){
			$zuopin = $this->obj_zuopin->getOne("*", array("pk_id"=>$val["fk_zid"], "visible"=>1));
			$product_zuopins[$i] = $zuopin;
			
		}
		
		
		$this->assign("res", $res);
		$this->assign("product_zuopins", $product_zuopins);
		
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