<?php

require(DOCUROOT . '/spreadsheet/php-excel-reader/excel_reader2.php');
require(DOCUROOT . '/spreadsheet/SpreadsheetReader.php');

class admin extends Action{
	public $obj_material;
	public $obj_user;
	
	function __construct() {
		parent::__construct();
	
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		$this->assign("userid", $userid);
		
		$this->obj_material = load("account_material");
		$this->obj_user = load("account_users");
		
		if(!isset($_SESSION ['UserLevel']) || $_SESSION ['UserLevel'] != 6){
// 			go("/");
		}
	}
	
	function ACT_index(){
		
		
	}
	
	function ACT_admin_dashboard(){
		$user_type = "admin";
		$this->assign("user_type", $user_type);
	}
	
	function ACT_admin_manage(){
		$user_type = "admin";
		$this->assign("user_type", $user_type);
	}
	
	function ACT_admin_multiupload(){
		$user_type = "admin";
		$this->assign("user_type", $user_type);
	}
	
	function ACT_admin_singleupload(){
		$user_type = "admin";
		$this->assign("user_type", $user_type);
		
		
	}
	
	function ACT_single_upload(){
		// 		debug::d($_POST);
		// 		debug::d($_FILES);
		// 		exit;
	
		if(!empty($_POST['brand'])){
				
			if(!empty($_POST['projno_name'])){
				$item_id = time() . "_" . $_POST['projno_name'] . "_" . rand(1, 1000);
	
	
				$target_dir = DOCUROOT . "/image/material/";
				$target_file = $target_dir . basename($_FILES["file"]["name"]);
	
				$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
				$imageFileType = strtolower($imageFileType);
	
				$file_name = $item_id . '.' . $imageFileType;
	
				$target_file = $target_dir . $file_name;
				$pic_path = "/image/material/" . $file_name;
				$uploadOk = 1;
	
	
	
				// Check if image file is a actual image or fake image
				// 					if(isset($_POST["submit"])) {
				// 						$check = getimagesize($_FILES["file"]["tmp_name"]);
				// 						if($check !== false) {
				// 							//echo "File is an image - " . $check["mime"] . ".";
				// 							$uploadOk = 1;
				// 						} else {
				// 							//echo "File is not an image.";
				// 							$uploadOk = 0;
				// 						}
				// 					}
				// Check if file already exists
				if (file_exists($target_file)) {
					//echo "Sorry, file already exists.";
					$uploadOk = 0;
				}
				// Check file size
				if ($_FILES["file"]["size"] > 5000000) {
					//echo "Sorry, your file is too large.";
					$uploadOk = 0;
				}
				// Allow certain file formats
				$file_type = "图片";
				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
						&& $imageFileType != "gif"  && $imageFileType != "xlsx" && $imageFileType != "xls") {
							//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
							$uploadOk = 0;
							$file_type = "非图片";
						}
						// Check if $uploadOk is set to 0 by an error
						if ($uploadOk == 0) {
							//echo "Sorry, your file was not uploaded.";
							// if everything is ok, try to upload file
						} else {
							if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
								//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
	
							} else {
								//echo "Sorry, there was an error uploading your file.";
							}
						}
						// 							if(isset($_FILES["file"]["name"]) && !empty($_FILES["file"]["name"]) && $uploadOk){
						// 								$res = $this->obj_user_pics->getOne("*", array("fk_user_id"=>$_POST['userid'], "is_head_pic"=>1, "visible"=>1, "order"=>array("pk_id"=>"DESC")));
						// 								if(!empty($res)){
						// 									$old_pic_name = DOCUROOT . $res['pic_path'];
	
						// 									$is_updated = $this->obj_user_pics->Update(array("pic_path"=>$pic_path, "is_head_pic"=>1), array("fk_user_id"=>$_POST['userid'], "is_head_pic"=>1, "visible"=>1, "order"=>array("pk_id"=>"DESC")));
						// 									if($is_updated && file_exists($old_pic_name)){
						// 										unlink($old_pic_name);
						// 									}
	
						// 								}else{
						// 									$this->obj_material->Insert(array("fk_user_id"=>$_POST['userid'], "pic_path"=>$pic_path, "is_head_pic"=>1));
						// 								}
						// 							}
						$res = array(
									
								"item_id"=>$item_id,
								"created_time"=>time(),
								"brand"=>$_POST['brand'],
								"category"=>$_POST['category'],
								"projno_name"=>$_POST['projno_name'],
								"texture"=>$_POST['texture'],
								"size"=>$_POST['size'],
								"description"=>$_POST['description'],
								"price"=>$_POST['price'],
								"pic_path"=>$pic_path,
								"file_type"=>$file_type,
								"file_format"=>$imageFileType,
								"file_size"=>$_FILES["file"]["size"]
									
						);
						// 				debug::d($uploadOk);exit;
	
						$is_insert = $this->obj_material->insert($res);
						go("/account/admin.php?act=preview&id={$item_id}");
			}
	
		}
		else
		{
			echo "empty";
			go("/account/admin.php?act=admin_singleupload");
		}
	
			
	}
	
	function ACT_preview(){
	
		if(!empty($_GET["id"])){
			$res = $this->obj_material->getOne("*", array("item_id"=>$_GET['id']));
			$res['file_size'] = round($res['file_size'] / 1024.0 /1024.0, 2);
		}
		else{
			go("/");
		}
		//getList("SELECT *  FROM  post WHERE content LIKE '%$keyword%' and visible = 1");
		// 		$keyword = "abc-7823";
		// 		$search = $this->obj_material->getAll("SELECT *  FROM  material WHERE item_id LIKE '%$keyword%' and visible = 1");
		// 		debug::d($search);exit;
		$this->assign("res", $res);
	
	}
	
	function ACT_single_modify(){
		if(isset($_POST) && !empty($_POST['submit'])){
			$this->obj_material->Update($_POST, array("item_id"=>$_POST['item_id']));
				
			go("/account/admin.php?act=preview&id={$_POST['item_id']}");
		}
		go("/");
	}
	
	
	function ACT_delete_user(){
		
	}
	
	function ACT_find_all_users(){
		
	}
	
	
}