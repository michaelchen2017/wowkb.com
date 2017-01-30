<?php
/**
 * update from sajax
 * http://www.modernmethod.com/sajax/ 
 * 
 * 调用示例
 * function a($a){return $a;}
 * function b($b){return $b;}
 * function c($c){return $c;}
 * function d($d){return $d;}
 * 
 * $ajax=new Ajax();
 * 
 * 所有参数都有默认值
 * $ajax->request_type="GET";
 * $ajax->……=……;
 * 
 * $ajax->export('a','b','c','d');
 */

class Ajax{
	var $request_type='POST';
	var $remote_uri="";
	var $failure_redirect="";
	var $bodycode=true;
	var $debug=false;
	var $json=false;
	
	var $debug_mode_str;
	var $export_list=array();
	var $js_list=array();

	function __construct(){
		header('Content-Type: application/x-javascript');
		$this->remote_uri=$_SERVER["REQUEST_URI"];
	}
	
	/**
	 * 设置需要输出的函数
	 *
	 */
	function export(){
		$n = func_num_args();
		for ($i = 0; $i < $n; $i++) {
			$this->export_list[] = func_get_arg($i);
		}
		$this->debug_mode_str=empty($this->debug)?"false":"true";
		
		$this->show();
		$this->handle();
	}
	
	/**
	 * 设置需要输出的函数
	 *
	 */
	function multiExport($arr){
		foreach ($arr as $val) {
			if(substr($val,0,2)=='js'){
				$this->js_list[] = $val;
			}else{
				$this->export_list[] = $val;
			}
		}
		$this->debug_mode_str=empty($this->debug)?"false":"true";
		
		$this->show();
		$this->handle();
	}
	
	function load( $func_name, $argsName='args' ){
		global $ajax_sess_check;
		
		if(in_array($func_name,$ajax_sess_check)){
			if(empty($_SESSION['UserID'])) $result=array(0,'网络连接超时，请重新登录！'); 
		}
		
		if(!isset($result)){
			$args = empty($_GET[$argsName])?array():$_GET[$argsName];
			$result = call_user_func_array($func_name, $args);
		}
		if($this->json)
			echo json_encode($result);
		else
			echo "var res = " . trim($this->getJsValue($result)) . ";";
	}

	/**
	 * 显示输出的代码
	 *
	 * @param boolean $bodycode  //是否输出js类库
	 */
	private function show(){
		//如果是返回结果数据，则跳过
		if(!empty($_GET["rs"]) || !empty($_POST["rs"])) return;
		
		$html = empty($this->bodycode)?"":$this->commonjs();
		
		//输出相关函数
		if(!empty($this->export_list)){
			foreach ($this->export_list as $func) {
				$html .= $this->getFunction($func);
			}
		}
		
		//加载纯js
		if(!empty($this->js_list)){
			foreach ($this->js_list as $func) {
				$html .= "\n".$func();
			}
		}
		
		echo $html;
	}

	/**
	 * 返回用户定义函数处理的结果
	 *
	 */
	private function handle(){
		
		$mode = "";
		if (!empty($_GET["rs"])) $mode = "get";
		if (!empty($_POST["rs"])) $mode = "post";
		if (empty($mode)) return;

		$target = "";

		if ($mode == "get") {
			// Bust cache in the head
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			// always modified
			header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
			header ("Pragma: no-cache");                          // HTTP/1.0
			$func_name = $_GET["rs"];
			if (! empty($_GET["rsargs"]))
			$args = $_GET["rsargs"];
			else
			$args = array();
		}
		else {
			$func_name = $_POST["rs"];
			if (! empty($_POST["rsargs"]))
			$args = $_POST["rsargs"];
			else
			$args = array();
		}

		if (! in_array($func_name, $this->export_list))
		echo "-:$func_name not callable";
		else {
			$result = call_user_func_array($func_name, $args);
			echo "+:";
			echo "var res = " . trim($this->getJsValue($result)) . ";";
		}
		exit;
	}

	/**
	 * Helper function to return an eval()-usable representation
	 * of an object in JavaScript.
	 */
	private function getJsValue($value) {
		$type = gettype($value);

		if ($type == "boolean") {
			return ($value) ? "Boolean(true)" : "Boolean(false)";
		}
		elseif ($type == "integer") {
			return "parseInt($value)";
		}
		elseif ($type == "double") {
			return "parseFloat($value)";
		}
		elseif ($type == "array" || $type == "object" ) {
			//
			// XXX Arrays with non-numeric indices are not
			// permitted according to ECMAScript, yet everyone
			// uses them.. We'll use an object.
			//
			$s = "{ ";
			if ($type == "object") {
				$value = get_object_vars($value);
			}
			foreach ($value as $k=>$v) {
				$esc_key = $this->esc($k);
				if (is_numeric($k))
				$s .= "$k: " . $this->getJsValue($v) . ", ";
				else
				$s .= "\"$esc_key\": " . $this->getJsValue($v) . ", ";
			}
			if (count($value))
			$s = substr($s, 0, -2);
			return $s . " }";
		}
		else {
			$esc_val = $this->esc($value);
			$s = "'$esc_val'";
			return $s;
		}
	}

	private function getFunction($func_name) {
		$html = "function x_{$func_name}(){ajax.do_call('{$func_name}', x_remote_uri, x_{$func_name}.arguments);}\n";
		return $html;
	}
	
	private function esc($val)
	{
		$val = str_replace("\\", "\\\\", $val);
		$val = str_replace("\r", "\\r", $val);
		$val = str_replace("\n", "\\n", $val);
		$val = str_replace("'", "\\'", $val);
		return str_replace('"', '\\"', $val);
	}

	private function commonjs(){
		$js='// remote scripting library
if(typeof(ajax)=="undefined"){
	var ajax={
		debug_mode : '.$this->debug_mode_str.',
		request_type : "'.$this->request_type.'",
		target_id : "",
		failure_redirect : "'.$this->failure_redirect.'",
	
		debug:function(text) {
			if (ajax.debug_mode)
				alert(text);
		},
		
	 	init_object:function() {
	 		ajax.debug("init_object() called..");
	 		
	 		var A;
	 		
	 		var msxmlhttp = new Array(
				\'Msxml2.XMLHTTP.5.0\',
				\'Msxml2.XMLHTTP.4.0\',
				\'Msxml2.XMLHTTP.3.0\',
				\'Msxml2.XMLHTTP\',
				\'Microsoft.XMLHTTP\');
			for (var i = 0; i < msxmlhttp.length; i++) {
				try {
					A = new ActiveXObject(msxmlhttp[i]);
				} catch (e) {
					A = null;
				}
			}
	 		
			if(!A && typeof XMLHttpRequest != "undefined")
				A = new XMLHttpRequest();
			if (!A)
				ajax.debug("Could not create connection object.");
			return A;
		},
		
		requests : new Array(),
		
		do_call:function(func_name, uri, args) {
			var i, x, n;
			var post_data;
			var target_id;
			
			ajax.debug("in do_call().." + ajax.request_type + "/" + ajax.target_id);
			target_id = ajax.target_id;
			if (typeof(ajax.request_type) == "undefined" || ajax.request_type == "") 
				ajax.request_type = "GET";
			
			if (ajax.request_type == "GET") {
			
				if (uri.indexOf("?") == -1) 
					uri += "?rs=" + escape(func_name);
				else
					uri += "&rs=" + escape(func_name);
				uri += "&rst=" + escape(target_id);
				uri += "&rsrnd=" + new Date().getTime();
				
				for (i = 0; i < args.length-1; i++) 
					uri += "&rsargs[]=" + escape(args[i]);
	
				post_data = null;
			} 
			else if (ajax.request_type == "POST") {
				post_data = "rs=" + escape(func_name);
				post_data += "&rst=" + escape(target_id);
				post_data += "&rsrnd=" + new Date().getTime();
				
				for (i = 0; i < args.length-1; i++) 
					post_data = post_data + "&rsargs[]=" + escape(args[i]);
			}
			else {
				alert("Illegal request type: " + ajax.request_type);
			}
			
			x = ajax.init_object();
			if (x == null) {
				if (ajax.failure_redirect != "") {
					location.href = ajax.failure_redirect;
					return false;
				} else {
					ajax.debug("NULL ajax object for user agent:\n" + navigator.userAgent);
					return false;
				}
			} else {
				x.open(ajax.request_type, uri, true);
				// window.open(uri);
				
				ajax.requests[ajax.requests.length] = x;
				
				if (ajax.request_type == "POST") {
					x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
					x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				}
			
				x.onreadystatechange = function() {
					if (x.readyState != 4) 
						return;
	
					ajax.debug("received " + x.responseText);
				
					var status;
					var data;
					var txt = x.responseText.replace(/^\s*|\s*$/g,"");
					status = txt.charAt(0);
					data = txt.substring(2);
	
					if (status == "") {
						// let\'s just assume this is a pre-response bailout and let it slide for now
					} else if (status == "-") 
						alert("Error: " + data);
					else {
						if (target_id != "") 
							document.getElementById(target_id).innerHTML = eval(data);
						else {
							try {
								var callback;
								var extra_data = false;
								if (typeof args[args.length-1] == "object") {
									callback = args[args.length-1].callback;
									extra_data = args[args.length-1].extra_data;
								} else {
									callback = args[args.length-1];
								}
								callback(eval(data), extra_data);
							} catch (e) {
								ajax.debug("Caught error " + e + ": Could not eval " + data );
							}
						}
					}
				}
			}
			
			ajax.debug(func_name + " uri = " + uri + "/post = " + post_data);
			x.send(post_data);
			ajax.debug(func_name + " waiting..");
			delete x;
			return true;
		}
	}
}
';

		$js.="var x_remote_uri = '{$this->remote_uri}';\n";
		
		return $js;
	}
}
?>