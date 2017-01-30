<?php

/*
 * FileName:class_debug.php
 * Created on 2007-11-5
 * By weiqi<weiqi228@gmail.com>
 */
class debug {
	// display all System errors and return  old config array
	static public function getError() {
		$error_reporting = error_reporting(2047);
		$display_errors = ini_get('display_errors');
		$display_startup_errors = ini_get('display_startup_errors');
		if ($display_errors) {
			$display_errors = 1;
		} else {
			$display_errors = 0;
		}
		if ($display_startup_errors) {
			$display_startup_errors = 1;
		} else {
			$display_startup_errors = 0;
		}
		ini_set('display_errors', 'ON');
		ini_set('display_startup_errors', 'ON');
		return array (
				'error_reporting' => $error_reporting,
				'display_errors' => $display_errors,
				'display_startup_errors' => $display_startup_errors
		);
	}
	// debug array
	static public function displayValue($arr, $debug = false) {
		echo ("<pre>");
		print_r($arr);
		echo ("</pre>");
		if ($debug) {
			exit;
		}
	}
	// display error message and exit program
	static public function displayError($mid) {
		global $smarty;
		$smarty->display("error.tpl");
		exit;
	}
	
	// check the debug status
	static public function check($val){
		if(!empty($_GET['debug'])){
			func_initSession();
			if($_GET['debug']==$val && !empty($_SESSION['UserLevel'])) return true;
		}
		
		return false;
	}	

	// quickly function
	static public function d($arr, $debug = false){
		debug::displayValue($arr, $debug = false);
	}
	// quickly function
	static public function g(){
		debug::getError();
	}

}

?>