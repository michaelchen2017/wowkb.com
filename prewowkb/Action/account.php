<?php
class account extends Action{
	public $obj_tmp_designer;
	public $obj_tmp_constructor;
	public $obj_tmp_supplier;
	public $obj_tmp_customize;
	
	
	function __construct() {
		parent::__construct();
		
		$this->obj_tmp_designer = load("prewowkb_tmp_apply_designer");
		$this->obj_tmp_constructor = load("prewowkb_tmp_apply_constructor");
		$this->obj_tmp_supplier = load("prewowkb_tmp_apply_supplier");
		$this->obj_tmp_customize = load("prewowkb_tmp_customize_service");
		$userid = isset($_SESSION['userid'])?$_SESSION['userid']:"";
		$this->assign("userid", $userid);
	}
	
	
	function ACT_index(){
		$obj_tmp_zuopins = load("account_tmp_zuopin");
		$res = $obj_tmp_zuopins->getList("*", array("visible"=>1), 12);
		
		$this->assign("res", $res);
	}
	
	function ACT_form_apply_designer(){
		
	}
	
	function ACT_form_apply_designer_process(){
// 		debug::d($_POST);exit;

		if(isset($_POST) && !empty($_POST['name'])){
			$designfields = "";
			if(!empty($_POST['design_fields'])){
				foreach ($_POST['design_fields'] as $val){
					if(!empty($designfields)){
						$designfields = $designfields . "," . $val;
					}
					else {
						$designfields = $val;
					}
				}
			}
			
			
				$softwarelist = "";
				if(!empty($_POST['software_list'])){
					foreach ($_POST['software_list'] as $val){
						if(!empty($softwarelist)){
							$softwarelist = $softwarelist . "," . $val;
						}
						else {
							$softwarelist = $val;
						}
					}
				}
			
			
			$res = array(
					"name"=>$_POST['name'],
					"design_fields" => $designfields,
					"used_software" => $softwarelist,
					"school" => $_POST['school'],
					"major" => $_POST['major'],
					"others" => $_POST['others'],
					"price" => $_POST['price'],
					"website" => $_POST['website'],
					"email"=>$_POST['email'],
					"tel"=>$_POST['tel'],
					"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
					"state"=>$_POST['state'],
					"city"=>$_POST['city'],
					"zipcode"=>$_POST['zipcode'],
			
			);
				
			$this->obj_tmp_designer->insert($res);
			go("/");
				
// 			$item_id = time() . "_" . md5($_POST['name']) . "_" . rand(1, 1000);
			
			
// 			$target_dir = DOCUROOT . "/image/material/";
// 			$target_file = $target_dir . basename($_FILES["file"]["name"]);
			
// 			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// 			$imageFileType = strtolower($imageFileType);
			
// 			$file_name = $item_id . '.' . $imageFileType;
			
// 			$target_file = $target_dir . $file_name;
// 			$pic_path = "/image/material/" . $file_name;
// 			$uploadOk = 1;
			
			
// 			// Check file size
// 			if ($_FILES["file"]["size"] > 5000000) {
// 				//echo "Sorry, your file is too large.";
// 				$uploadOk = 0;
// 			}
// 			// Allow certain file formats
// 			$file_type = "图片";
// 			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
// 					&& $imageFileType != "gif"  && $imageFileType != "xlsx" && $imageFileType != "xls") {
// 						//echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
// 						$uploadOk = 0;
// 						$file_type = "非图片";
// 					}
// 					// Check if $uploadOk is set to 0 by an error
// 					if ($uploadOk == 0) {
// 						//echo "Sorry, your file was not uploaded.";
// 						// if everything is ok, try to upload file
// 					} else {
// 						if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
// 							//echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			
// 						} else {
// 							//echo "Sorry, there was an error uploading your file.";
// 						}
// 					}
						
// 					$res = array(
								
// 							"name"=>$_POST['name'],
// 							"design_fields" => $designfields,
// 							"used_software" => $softwarelist,
// 							"school" => $_POST['school'],
// 							"major" => $_POST['major'],
// 							"others" => $_POST['others'],
// 							"price" => $_POST['price'],
// 							"website" => $_POST['website'],
// 							"email"=>$_POST['email'],
// 							"tel"=>$_POST['tel'],
// 							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
// 							"state"=>$_POST['state'],
// 							"city"=>$_POST['city'],
// 							"zipcode"=>$_POST['zipcode'],
				
// 					);
													
// 					$this->obj_tmp_designer->insert($res);

// 					// go("/account/space.php?act=index&id={$userid}");
// 					go("/");
			
		}else{
			go("/");
		}
		
	}
	
	function ACT_form_apply_constructor(){
		
	}
	
	function ACT_form_apply_constructor_process(){
		if(isset($_POST) && !empty($_POST['name'])){
			$item_id = time() . "_" . md5($_POST['name']) . "_" . rand(1, 1000);
				
				
			$target_dir = DOCUROOT . "/image/material/";
			$target_file = $target_dir . basename($_FILES["file"]["name"]);
				
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$imageFileType = strtolower($imageFileType);
				
			$file_name = $item_id . '.' . $imageFileType;
				
			$target_file = $target_dir . $file_name;
			$pic_path = "/image/material/" . $file_name;
			$uploadOk = 1;
				
				
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
							"email"=>$_POST['email'],
							"tel"=>$_POST['tel'],
							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
							"state"=>$_POST['state'],
							"city"=>$_POST['city'],
							"zipcode"=>$_POST['zipcode'],
							"website"=>$_POST['website'],
							"certificate"=>$pic_path
		
					);
				
					$this->obj_tmp_constructor->insert($res);
		
					// go("/account/space.php?act=index&id={$userid}");
					go("/");
						
		}else{
			go("/");
		}
	}
	
	function ACT_form_apply_supplier(){
		
	}
	
	function ACT_form_apply_supplier_process(){
		if(isset($_POST) && !empty($_POST['name'])){
			$item_id = time() . "_" . md5($_POST['name']) . "_" . rand(1, 1000);
		
		
			$target_dir = DOCUROOT . "/image/material/";
			$target_file = $target_dir . basename($_FILES["file"]["name"]);
		
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$imageFileType = strtolower($imageFileType);
		
			$file_name = $item_id . '.' . $imageFileType;
		
			$target_file = $target_dir . $file_name;
			$pic_path = "/image/material/" . $file_name;
			$uploadOk = 1;
		
		
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
							"website"=>$_POST['website'],
// 							"tel"=>$_POST['tel'],
							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
							"state"=>$_POST['state'],
							"city"=>$_POST['city'],
							"zipcode"=>$_POST['zipcode'],
						
							"intro"=>$_POST['intro']
		
					);
		
					$this->obj_tmp_supplier->insert($res);
		
					// go("/account/space.php?act=index&id={$userid}");
					go("/");
		
		}else{
			go("/");
		}
	}
	
	function ACT_form_customize(){
		
	}

	function ACT_form_customize_process(){
		if(isset($_POST) && !empty($_POST['name'])){
			$item_id = time() . "_" . md5($_POST['name']) . "_" . rand(1, 1000);
		
		
			$target_dir = DOCUROOT . "/image/material/";
			$target_file = $target_dir . basename($_FILES["file"]["name"]);
		
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			$imageFileType = strtolower($imageFileType);
		
			$file_name = $item_id . '.' . $imageFileType;
		
			$target_file = $target_dir . $file_name;
			$pic_path = "/image/material/" . $file_name;
			$uploadOk = 1;
		
		
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
							"service_type"=>$_POST['service_type'],
							"name"=>$_POST['name'],
							
							"tel"=>$_POST['tel'],
							"address"=>trim($_POST['addr']),
// 							"state"=>$_POST['state'],
// 							"city"=>$_POST['city'],
// 							"zipcode"=>$_POST['zipcode'],
							"region"=>$_POST['region'],
							"email"=>$_POST['email']
		
					);
		
					$this->obj_tmp_customize->insert($res);
		
					// go("/account/space.php?act=index&id={$userid}");
					go("/");
		
		}else{
			go("/");
		}
	}
	function ACT_about(){
		
	}
	
	function ACT_begincustomize(){
		
	}
	
	function ACT_designer(){
		
	}
	
	function ACT_productsupplier(){
		
	}
	
	function ACT_service(){
		
	}
	
	function ACT_designshow(){
		
	}
	
	function ACT_design_detail(){
		if(isset($_GET) && !empty($_GET['id'])){
			$obj_tmp_zuopin = load("account_tmp_zuopin");
			$obj_tmp_zuopin_wuliao = load("account_tmp_zuopin_wuliao_pics");
			
			$res = $obj_tmp_zuopin->getOne("*", array("pk_id"=>$_GET['id'], "visible"=>1));
			$pics = $obj_tmp_zuopin_wuliao->getAll(array("pic_path"), array("fk_id"=>$_GET['id'], "visible"=>1));
			//$res['category']
			$other_relative_zuopins = $obj_tmp_zuopin->getList("*", array("category"=>$res['category'], "visible"=>1), 6);
			foreach ($other_relative_zuopins as $i=>$value){
				if($value['pk_id'] == $_GET['id']){
					unset($other_relative_zuopins[$i]);
				}
			}
		
			$this->assign("res", $res);
			$this->assign("pics", $pics);
			$this->assign("other_relative_zuopins", $other_relative_zuopins);
		}
	}
}
	
