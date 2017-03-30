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
		if(isset($_POST)){
// 			debug::d($_POST);exit;
			$service_regions = "";
			$services = "";
			if(!empty($_POST['service_regions'])){
				foreach ($_POST['service_regions'] as $val){
					if(!empty($service_regions)){
						$service_regions = $service_regions . "," .$val;
					}
					else{
						$service_regions = $val;
					}
				}
			}
			
			if(!empty($_POST['services'])){
				foreach ($_POST['services'] as $val){
					if(!empty($services)){
						$services = $services . "," .$val;
					}
					else{
						$services = $val;
					}
				}
			}
			
			
			$res = array(
			
										"name"=>$_POST['name'],
										"brand"=>$_POST['brand'],
										"service_regions"=>$service_regions,
										"services"=>$services,
										"person_to_contact"=>$_POST['person_to_contact'],
										"email"=>$_POST['email'],
										"tel"=>$_POST['tel'],
										"website"=>$_POST['website'],
										"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
										"state"=>$_POST['state'],
										"city"=>$_POST['city'],
										"zipcode"=>$_POST['zipcode'],
										"intro"=>$_POST['intro'],
									
			
						);
// 			debug::d($res);exit;
								$this->obj_tmp_constructor->insert($res);
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
// 							"email"=>$_POST['email'],
// 							"tel"=>$_POST['tel'],
// 							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
// 							"state"=>$_POST['state'],
// 							"city"=>$_POST['city'],
// 							"zipcode"=>$_POST['zipcode'],
// 							"website"=>$_POST['website'],
// 							"certificate"=>$pic_path
		
// 					);
				
// 					$this->obj_tmp_constructor->insert($res);
		
// 					// go("/account/space.php?act=index&id={$userid}");
// 					go("/");
						
		}else{
			go("/");
		}
	}
	
	function ACT_form_apply_supplier(){
		
	}
	
	function ACT_form_apply_supplier_process(){
// 		debug::d($_POST);exit;
		if(isset($_POST)){
			$company_type = "";
			if(!empty($_POST['company_type'])){
				foreach ($_POST['company_type'] as $val){
					if(!empty($company_type)){
						$company_type = $company_type . "," . $val;
					}else{
						$company_type = $val;
					}
				}
			}
			$res = array(
			
							"name"=>$_POST['name'],
							"brand"=>$_POST['brand'],
							"company_type"=>$company_type,
							"country_of_origin"=> $_POST['country_of_origin'],
							"city_of_origin" => $_POST['city_of_origin'],
							"type_of_product" => $_POST['type_of_product'],
							"sales_region" => $_POST['sales_region'],
							"num_of_sales_network" => $_POST['num_of_sales_network'],
							"website"=>$_POST['website'],
							"person_to_contact" => $_POST['person_to_contact'],
							"tel"=>$_POST['tel'],
							"email" => $_POST['email'],
							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
							"state"=>$_POST['state'],
							"city"=>$_POST['city'],
							"zipcode"=>$_POST['zipcode'],
							"retail_address" => $_POST['retail_address'],
							"intro"=>$_POST['intro'],
			
						);
//  			debug::d($res);exit;
			$this->obj_tmp_supplier->insert($res);
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
// 							"website"=>$_POST['website'],
// // 							"tel"=>$_POST['tel'],
// 							"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
// 							"state"=>$_POST['state'],
// 							"city"=>$_POST['city'],
// 							"zipcode"=>$_POST['zipcode'],
						
// 							"intro"=>$_POST['intro'],
		
// 					);
		
// 					$this->obj_tmp_supplier->insert($res);
		
// 					// go("/account/space.php?act=index&id={$userid}");
// 					go("/");
		
		}else{
			go("/");
		}
	}
	
	function ACT_form_customize(){
		$service_type = "";
		if(isset($_GET['msg']) && !empty($_GET['msg'])){
			$service_type = $_GET['msg'];		
		}
		
		$this->assign("service_type", $service_type);
	}

	function ACT_form_customize_process(){
		if(isset($_POST)){
	
			$res = array(
										"service_type"=>$_POST['service_type'],
										"name"=>$_POST['name'],
										"email"=>$_POST['email'],
										"tel"=>$_POST['tel'],
										"address"=>trim($_POST['addr1']) . trim($_POST['addr2']),
										"state"=>$_POST['state'],
										"city"=>$_POST['city'],
										"zipcode"=>$_POST['zipcode'],
										"demand"=>$_POST['demand'],
			
								);
								$this->obj_tmp_customize->insert($res);
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
// 							"service_type"=>$_POST['service_type'],
// 							"name"=>$_POST['name'],
							
// 							"tel"=>$_POST['tel'],
// 							"address"=>trim($_POST['addr']),
// // 							"state"=>$_POST['state'],
// // 							"city"=>$_POST['city'],
// // 							"zipcode"=>$_POST['zipcode'],
// 							"region"=>$_POST['region'],
// 							"email"=>$_POST['email']
		
// 					);
		
// 					$this->obj_tmp_customize->insert($res);
		
// 					// go("/account/space.php?act=index&id={$userid}");
// 					go("/");
		
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
// 		debug::d($res);exit;
			$this->assign("res", $res);
			$this->assign("pics", $pics);
			$this->assign("other_relative_zuopins", $other_relative_zuopins);
		}
	}
}
	
