<?php

require(DOCUROOT . '/spreadsheet/php-excel-reader/excel_reader2.php');
require(DOCUROOT . '/spreadsheet/SpreadsheetReader.php');

class admin extends Action{
	public $obj_material;
	public $obj_user;
	
	function __construct() {
		parent::__construct();
// 		$user_type = "admin";
// 		$this->assign("user_type", $user_type);
	
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		if(empty($userid)){
			go("/");
		}
		$this->assign("userid", $userid);
		
		$this->obj_material = load("account_material");
		$this->obj_user = load("account_users");
		
		$user_type_arr = $this->obj_user->getOne("*", array("uid"=>$userid, "visible"=>1));
		$user_type = $user_type_arr['account_type'];
		if($user_type != "admin")
			go("/");
			
		$this->assign("user_type", $user_type);
		
		if(!isset($_SESSION ['UserLevel']) || $_SESSION ['UserLevel'] != 6){
// 			go("/");
		}
	}
	
	function ACT_index(){
		
		
	}
	
	function ACT_admin_dashboard(){
		$res = $this->obj_material->getAll("*", array("visible"=>1));
		$count = count($res);
		
		$this->assign("count", $count);
		
		
	}
	
	function ACT_admin_manage(){
// 		$user_type = "admin";
// 		$this->assign("user_type", $user_type);
		
		$res = $this->obj_material->getList("*", array("visible"=>1), 5);
		$this->assign("res", $res);
		
		
	}
	
	function ACT_material_delete(){
// 		debug::d($_GET["id"]);exit;

		if(isset($_GET) && !empty($_GET['id'])){
			$this->obj_material->Update(array("visible"=>0), array("item_id"=>$_GET['id'], "visible"=>1));
			go("/account/admin.php?act=admin_manage");
		}
		else{
			go("/");
		}
	}
	
	function ACT_admin_multiple(){
		
	}
	
	function ACT_admin_multiupload(){
// 		$user_type = "admin";
// 		$this->assign("user_type", $user_type);
		
		if(isset($_POST) && !empty($_POST['submit'])){
		
			$basefilename = time() . "_" . md5($_FILES["file"]["name"]) . "_" . rand(1, 1000);
				
				
			$target_dir = DOCUROOT . "/upload/material/";
			$target_file = $target_dir . basename($_FILES["file"]["name"]);
				
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$imageFileType = strtolower($imageFileType);
				
			$file_name = $basefilename . '.' . $imageFileType;
				
			$target_file = $target_dir . $file_name;
			$pic_path = "/upload/material/" . $file_name;
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
		
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
					&& $imageFileType != "gif"  && $imageFileType != "xlsx" && $imageFileType != "xls") {
						//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
						$uploadOk = 0;
							
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
							exit;
						}
					}
					$reader = new SpreadsheetReader($target_file);

					foreach ($reader as $i=>$row){
						
						$item_id = time() . "_" . $row[2] . "_" . rand(1, 1000);
						if($i > 1 && is_numeric($row[0])){
							$res = array(
		
									"item_id"=> $item_id,
									"created_time"=>time(),
									"brand"=>empty($row[1])?'0':$row[1],
									"project_no"=>empty($row[2])?'0':$row[2],
									"name"=>empty($row[3])?'0':$row[3],
									"category" => empty($row[4])?'0':$row[4],
									"texture" => empty($row[5])?'0':$row[5],
									"style" => empty($row[6])?'0':$row[6],
									"colour" => empty($row[7])?'0':$row[7],
									"w" => empty($row[8])?0:$row[8],
									"h" => empty($row[9])?0:$row[9],
									"d" => empty($row[10])?0:$row[10],
									"weight" => empty($row[11])?0:$row[11],
									"description" => empty($row[12])?'0':$row[12],
									"retail_price" => empty($row[13])?0:$row[13],
									"discount_price" => empty($row[14])?0:$row[14],
									"discount_proportion" => str_replace('%', '', $row[15]) / 100,
									"stock" => empty($row[16])?0:$row[16],
									"unit" => empty($row[17])?'0':$row[17],
									"price" => "0",
									"file_type" => "0",
									"file_format" => "0",
									"file_size" => "0",
									"pic_path" => "0",
		
							);
							
							$this->obj_material->insert($res);
							
						}
					}
					go("/account/admin.php?act=multi_pics");
						
		}
		
	}
	
	function ACT_multi_pics(){
	
	}
	
	function ACT_multipics_process(){
	
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
	
				$res_pic = $this->obj_material->getOne("*", array("project_no"=>$files[0], "visible"=>1, "status"=>"pending", "pic_path"=>"0"));
// 							debug::d($res_pic);exit;
				if(!isset($res_pic) || empty($res_pic['item_id'])){
					continue;
				}
	
				$new_file_name = $res_pic['item_id'] . '.' .  $file_extension;
				$target_file = $target_dir . $new_file_name;
					
				$pic_path = $pic_path_base . $new_file_name;
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
				if (file_exists($target_file)) {
					//echo "Sorry, file already exists.";
					$uploadOk = 0;
				}
	
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
									
								$update_pic = array(
										"pic_path" => $pic_path,
										"file_type" => $file_type,
										"file_size"	=> $_FILES["files"]["size"][$i],
										"file_format" => $file_extension,
	
								);
	
								$this->obj_material->update($update_pic, array("item_id"=>$res_pic['item_id'], "visible"=>1));
								
									
							} else {
								//echo "Sorry, there was an error uploading your file.";
							}
						}
						//go("/account/space.php?id={$_POST['userid']}");
							
			}//foreach
			go("/account/admin.php?act=multi_preview");
		}
	}
	
	function ACT_multi_preview(){
		$res = $this->obj_material->getList("*", array("status"=>"pending", "visible"=>1, "order"=>array("created_time"=>'DESC')), 5);
		// 		debug::d($res);exit;
		$this->assign("res", $res);
	}
	
	
	function ACT_admin_singleupload(){

		
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
	
	function ACT_admin_applycheck(){
		$obj_constructors = load("prewowkb_tmp_apply_constructor");
		$obj_designers = load("prewowkb_tmp_apply_designer");
		$obj_suppliers = load("prewowkb_tmp_apply_supplier");
		$obj_services = load("prewowkb_tmp_customize_service");
		
		$con_res = $obj_constructors->getAll("*",array("visible"=>1));
		$des_res = $obj_designers->getAll("*",array("visible"=>1));
		$sup_res = $obj_suppliers->getAll("*",array("visible"=>1));
		$ser_res = $obj_services->getAll("*",array("visible"=>1));
		
		
		$this->assign("con_res", $con_res);
		$this->assign("des_res", $des_res);
		$this->assign("sup_res", $sup_res);
		$this->assign("ser_res", $ser_res);
		
		
	}
	
	function ACT_admin_supplier_detail(){
		$obj_supplier = load("prewowkb_tmp_apply_supplier");
		$res =" ";
		if(isset($_GET) && !empty($_GET['id'])){
			$res = $obj_supplier->getOne("*", array("id"=>$_GET['id'], "visible"=>1));
		}
// 		debug::d($res);exit;
		$this->assign("res", $res);
	}
	
	function ACT_admin_designer_detail(){
// 		$obj_designer = load("prewowkb_tmp_apply_designer");
		$obj_designer = load("account_tmp_apply_designer");
		$res = "";

		if(isset($_GET) && !empty($_GET['id'])){
			$res = $obj_designer->getOne("*", array("id"=>$_GET['id'], "visible"=>1));

		}
		$this->assign("res", $res);
	}
	
	function ACT_admin_constructor_detail(){
		$obj_constructor = load("prewowkb_tmp_apply_constructor");
		$res = "";
		if(isset($_GET) && !empty($_GET['id'])){
			$res = $obj_constructor->getOne("*", array("id"=>$_GET['id'], "visible"=>1));
		}
		$this->assign("res", $res);
	}
	
	function ACT_admin_service_detail(){
		$obj_service = load("prewowkb_tmp_customize_service");
		$res = "";
		if(isset($_GET) && !empty($_GET['id'])){
			$res = $obj_service->getOne("*", array("id"=>$_GET['id'], "visible"=>1));
		}
		$this->assign("res", $res);
	}
	
	function ACT_admin_applycheck_detail(){
		
	}
	
}