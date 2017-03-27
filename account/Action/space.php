<?php
class space extends Action{
	public $obj_user;
	public $obj_panos;
	public $obj_zuopin;
	public $obj_zuopin_materials;
	public $obj_materials;
	public $obj_tmp_zuopin;
	public $obj_tmp_zuopin_wuliao_pics;
	
	function __construct() {
		parent::__construct();
	
		$this->obj_user = load("account_users");
		$this->obj_panos = load("account_panos");
		$this->obj_zuopin = load("account_zuopin");
		$this->obj_materials = load("account_material");
		$this->obj_zuopin_materials = load("account_zuopin_material");
		
		$this->obj_tmp_zuopin = load("account_tmp_zuopin");
		$this->obj_tmp_zuopin_wuliao_pics = load("account_tmp_zuopin_wuliao_pics");
		
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		if(empty($userid)){
			go("/");
		}
		$this->assign("userid", $userid);
		$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
		$user_type = $user_type_arr['account_type'];
		
		$this->assign("user_type", $user_type);
	}
	
	
	function ACT_index(){
		if(isset($_GET['id']) && !empty($_GET['id'])){
			$flag = true;
			if(!isset($_SESSION['userid']) || $_SESSION['userid'] != $_GET['id']){
				$flag = false;
				go("/");
			}
		
			$this->assign("flag", $flag);
			
			$userid = $_GET['id'];
			
			$res_user = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
			
// 			$res_user_pic = $this->obj_user_pics->getOne("*", array("fk_user_id"=>$userid, "is_head_pic"=>1, "visible"=>1, "order"=>array("pk_id"=>"DESC")));
			
			//$this->assign("userid", $userid); //用户登录session id
			$this->assign("res_user", $res_user);
			
// 			$this->assign("res_user_pic", $res_user_pic);
			
		}else{
			go("/");
		}
	}
	
	function ACT_upload(){
		// 		print_r($_FILES["files"]);exit;
	
		if(isset($_POST['submit'])){
			//print_r($_FILES["files"]);exit;
			foreach ($_FILES["files"]["name"] as $i => $value){
				// 		        debug::d($_FILES["files"]["name"]);exit;
				$file_extension = pathinfo($value,PATHINFO_EXTENSION);
				$target_dir = DOCUROOT . "/upload/images/";
				$new_file_name = md5($value + time() + rand(1,100)) . '.' .  $file_extension;
				$target_file = $target_dir . $new_file_name;
					
// 				$pic_path = "/upload/images/" . basename($_FILES["files"]["name"][$i]);
				$uploadOk = 1;
	//write panos path into database
				if(isset($_FILES["files"]["name"][$i]) && !empty($_FILES["files"]["name"][$i])){
						$res = $this->obj_panos->getOne("*", array("fk_uid"=>$_POST['userid'], "visible"=>1, "order"=>array("pk_id"=>"DESC")));
					if(!empty($res)){
						$this->obj_panos->Update(array("pano_path"=>$target_file), array("fk_uid"=>$_POST['userid'], "visible"=>1, "order"=>array("pk_id"=>"DESC")));
					}else{
						$this->obj_panos->Insert(array("fk_uid"=>$_POST['userid'], "pano_path"=>$target_file, "visible"=>1));
					}
				}
	
					
				// Check if image file is a actual image or fake image
					
				$check = getimagesize($_FILES["files"]["tmp_name"][$i]);
				if($check !== false) {
					//echo "File is an image - " . $check["mime"] . ".";
					$uploadOk = 1;
				} else {
					//echo "File is not an image.";
					$uploadOk = 0;
				}
	
				// Check if file already exists
				if (file_exists($target_file)) {
					//echo "Sorry, file already exists.";
					$uploadOk = 0;
				}
				// Check file size
				//if ($_FILES["fileToUpload"]["size"] > 500000) {
				//echo "Sorry, your file is too large.";
				//	$uploadOk = 0;
				//}
				// Allow certain file formats
				$file_extension = strtolower($file_extension);
				if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg"
						&& $file_extension != "gif" && $file_extension != "swf") {
							//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
							$uploadOk = 0;
						}
						// Check if $uploadOk is set to 0 by an error
						if ($uploadOk == 0) {
							//echo "Sorry, your file was not uploaded.";
							// if everything is ok, try to upload file
						}
						else {
							if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file)) {
								//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
								//go("/demo/upload.php?act=index&id={$i}");
							} else {
								//echo "Sorry, there was an error uploading your file.";
							}
						}
						//go("/account/space.php?id={$_POST['userid']}");
	
							
	
			}//foreach
	
		}//if
	
	}//index
	
	function ACT_upload_user_pic(){
			
			if(isset($_POST['submit'])){
				
				$target_dir = DOCUROOT . "/upload/user/";
				$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
				
				$pic_path = "/upload/user/" . basename($_FILES["fileToUpload"]["name"]);
				$uploadOk = 1;
				
				if(isset($_FILES["fileToUpload"]["name"]) && !empty($_FILES["fileToUpload"]["name"])){
					$res = $this->obj_user_pics->getOne("*", array("fk_user_id"=>$_POST['userid'], "is_head_pic"=>1, "visible"=>1, "order"=>array("pk_id"=>"DESC")));
					if(!empty($res)){
						$this->obj_user_pics->Update(array("pic_path"=>$pic_path, "is_head_pic"=>1), array("fk_user_id"=>$_POST['userid'], "is_head_pic"=>1, "visible"=>1, "order"=>array("pk_id"=>"DESC")));
					}else{
						$this->obj_user_pics->Insert(array("fk_user_id"=>$_POST['userid'], "pic_path"=>$pic_path, "is_head_pic"=>1));
					}
				}
				
				$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
				// Check if image file is a actual image or fake image
				if(isset($_POST["submit"])) {
					$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
					if($check !== false) {
						//echo "File is an image - " . $check["mime"] . ".";
						$uploadOk = 1;
					} else {
						//echo "File is not an image.";
						$uploadOk = 0;
					}
				}
				// Check if file already exists
				if (file_exists($target_file)) {
					//echo "Sorry, file already exists.";
					$uploadOk = 0;
				}
				// Check file size
				//if ($_FILES["fileToUpload"]["size"] > 500000) {
					//echo "Sorry, your file is too large.";
				//	$uploadOk = 0;
				//}
				// Allow certain file formats
				$imageFileType = strtolower($imageFileType);
				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
						&& $imageFileType != "gif" ) {
							//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
							$uploadOk = 0;
						}
						// Check if $uploadOk is set to 0 by an error
						if ($uploadOk == 0) {
							//echo "Sorry, your file was not uploaded.";
							// if everything is ok, try to upload file
						} else {
							if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
								//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
								
							} else {
								//echo "Sorry, there was an error uploading your file.";
							}
						}
			go("/account/space.php?id={$_POST['userid']}");
				
			}
			else if(isset($_GET['id']) && !empty($_GET['id'])){
				$userid = $_GET['id'];
				$this->assign("userid", $userid);
			}
			else{
				go("/");
			}
		
	}
	
	function ACT_designer_dashboard(){
		$user_type = "designer";
		$this->assign("user_type", $user_type);
	}
	
	function ACT_designer_history(){
		$user_type = "designer";
		$this->assign("user_type", $user_type);
	}
	
	function ACT_designer_manage(){
		$user_type = "designer";
		$this->assign("user_type", $user_type);
		
	}
	
	function ACT_designer_upload(){
	
	}
	
	function ACT_designer_upload_process(){
//  		debug::d($_POST);
//  		debug::d($_FILES);
//  		exit;

		if(!empty($_POST['name'])){
		
			if(!empty($_POST['name'])){
				$item_id = time() . "_" . $_POST['name'] . "_" . rand(1, 1000);
		
		
				$target_dir = DOCUROOT . "/image/material/";
				$target_file = $target_dir . basename($_FILES["file"]["name"]);
		
				$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
				$imageFileType = strtolower($imageFileType);
		
				$file_name = $item_id . '.' . $imageFileType;
		
				$target_file = $target_dir . $file_name;
				$pic_path = "/image/material/" . $file_name;
				$uploadOk = 1;
				
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
							
								$res = array(
											
										"name"=>$_POST['name'],
										"category"=>$_POST['category'],
										"price"=>$_POST['price'],
										"size"=>$_POST['size'],
										"texture"=>$_POST['texture'],
										"interior"=>$_POST['interior'],
										"color"=>$_POST['color'],
										"application"=>$_POST['application'],
										"tags"=>$_POST['tags'],
										"pic_path"=>$pic_path,
										"intro"=>$_POST['intro'],
										"fk_uid"=> $_SESSION['userid'],
										
								);
// 								debug::d($res);exit;
//                                 var_dump($this->obj_tmp_zuopin);exit;
								$id = $this->obj_tmp_zuopin->insert($res);
// 								go("/account/space.php?act=index&id={$userid}");
								go("/account/space.php?act=addpics&id={$id}");
					}
		
				}
// 				else
// 				{
// 					echo "empty";
// 					go("/account/admin.php?act=admin_singleupload");
// 				}
		
		
	}
	function ACT_addpics(){
		if(isset($_GET) && !empty($_GET['id'])){
			$this->assign("id", $_GET['id']);
		}
		else{
			go("/");
		}
	}
	
	function ACT_addpics_process(){
// 		debug::d($_POST);
// 		debug::d($_FILES);
// 		exit;
// 		debug::d($_GET['id']);exit;
		if(isset($_POST) && !empty($_POST['id'])){
			if(isset($_POST) && !empty($_POST['submit'])){
				// 						debug::d($_FILES["files"]["name"]); exit;
				$pic_path_base ="/upload/images/".time()."/";
				$target_dir = DOCUROOT . $pic_path_base;
					
				//mkdir("/path/to/my/dir", 0777);
				mkdir($target_dir, 0777);
				foreach ($_FILES["files"]["name"] as $i => $value){
					// 						        debug::d($_FILES["files"]["name"]);exit;
					$file_extension = pathinfo($value,PATHINFO_EXTENSION);
			
			
					$files = explode(".", $_FILES["files"]["name"][$i]);
					// 				debug::d($files);
					$file_name = md5($fi[0]) . rand(1,1000);
					$new_file_name = $file_name . $file_extension;
					
					$pic_path = $pic_path_base . $new_file_name;
					$target_file = $target_dir . $new_file_name;
					
					$uploadOk = 1;
						
					// Check if image file is a actual image or fake image
						
					$check = getimagesize($_FILES["files"]["tmp_name"][$i]);
					if($check !== false) {
						//echo "File is an image - " . $check["mime"] . ".";
						$uploadOk = 1;
					} else {
						//echo "File is not an image.";
						$uploadOk = 0;
					}
						
					// Check if file already exists
// 					if (file_exists($target_file)) {
// 						//echo "Sorry, file already exists.";
// 						$uploadOk = 0;
// 					}
			
					// Allow certain file formats
					$file_type = "图片";
					$file_extension = strtolower($file_extension);
					if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg"
							&& $file_extension != "gif") {
								//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
								$uploadOk = 0;
								$file_type = "非图片";
							}
							// Check if $uploadOk is set to 0 by an error
								
								
							if ($uploadOk == 0) {
								//echo "Sorry, your file was not uploaded.";
								// if everything is ok, try to upload file
							}
							else {
								if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file)) {
										
									$pics = array(
											"fk_id" => $_POST['id'],
											"pic_path" => $pic_path,
											
									);
									
									$this->obj_tmp_zuopin_wuliao_pics->insert($pics);
									
										
								} else {
									//echo "Sorry, there was an error uploading your file.";
								}
							}
							//go("/account/space.php?id={$_POST['userid']}");
								
				}//foreach
// 				go("/account/admin.php?act=multi_preview");

				go("/account/space.php?act=user_dashboard");
			}
		}
		else{
			go("/");
		}
		
		
	}
	
	function ACT_zuopin_post(){
		$user_type = "designer";
		$this->assign("user_type", $user_type);

		if(isset($_POST) && !empty($_POST['title'])){
			$target_dir = DOCUROOT . "/image/material/";

			$imageFileType = pathinfo($_FILES["file"]["name"],PATHINFO_EXTENSION);
		
			$imageFileType = strtolower($imageFileType);
			
			$file_name = md5($_FILES["file"]["name"]) . '.' . $imageFileType;
			
			$target_file = $target_dir . $file_name;
			$pic_path = "/image/material/" . $file_name;
			$uploadOk = 1;
			
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
					
			
			$res_arr = array(
					"designer"=> $_POST['designer'],
					"title"  => $_POST['title'],
					"company" => "wowkb",
					"description" => $_POST['description'],
					"file_type" => "none",
					"file_format" => "none",
					"file_size" => 1000,
					"views" => 100,
					"download_num" => "1000",
					"size" => $_POST['size'],
					"pic_path" => $pic_path,
					"final_sum" => $_POST['final_sum'],
			);
// 			debug::d($res_arr);exit;
			$pk_id = $this->obj_zuopin->insert($res_arr);
// 			debug::d($resid);exit;
// 			$res_id = $this->obj_zuopin->getOne("*", array("pic_path"=>$pic_path, "visible"=>1));
// 			$pk_id = $res_id['pk_id'];
			
// 			go('/account/space.php?act=zuopin_preview&id='.$pk_id);
			go("/account/space.php?act=designer_upload_select&id=".$pk_id);
		}
		
		else {
			go("/");
		}
	}
	
	function ACT_designer_upload_select(){
		if(isset($_GET['id']) && empty($_GET['id'])){
			go("/");
		}
		$res = $this->obj_materials->getAll("*", array("visible"=>1));
		$pk_id = $_GET['id'];
		
		$this->assign("res", $res);
		$this->assign("pk_id", $pk_id);
		
		
	}
	
	function ACT_designer_upload_select_process(){
// 		if(isset($_GET['id']) && empty($_GET['id'])){
// 			go("/");
// 		}
		$pk_id = $_POST['pk_id'];
		foreach ($_POST['check_list'] as $selected){
			$post_arr = array(
				"fk_item_id" => $selected,
				"fk_zid" => $pk_id
			);
			
			$this->obj_zuopin_materials->insert($post_arr);
		}
		
		go('/account/space.php?act=zuopin_preview&id='.$pk_id);
	}
	
	function ACT_zuopin_modify(){
// 		debug::d($_POST);exit;
		if(isset($_POST) && !empty($_POST['title'])){
			$this->obj_zuopin->Update($_POST, array("pk_id"=>$_POST['pk_id']));
			go("/account/space.php?act=zuopin_preview&id={$_POST['pk_id']}");
		}
		else{
			go("/");
		}
		
	}
	
	function ACT_zuopin_preview(){
		$user_type = "designer";
		$this->assign("user_type", $user_type);
		
		if(!empty($_GET['id'])){
			$res = $this->obj_zuopin->getOne("*", array("pk_id"=>$_GET['id'], "visible"=>1));
			
			$this->assign("res", $res);
			$this->assign("id", $_GET['id']);
		}
		else{
			
		}
	}
	
	function ACT_user_collection(){
		
	}
	
	function ACT_user_dashboard(){
		
		
		
	}
	
	function ACT_user_order(){
		
	}
	
	function ACT_user_history(){
		
		
	}
	
	
	
	
}