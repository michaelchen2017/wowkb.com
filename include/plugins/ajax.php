<?php
/**
 * i.g.
 * /include/plugins/ajax.php?q=service/newsletter/ajax:getCategorise
 * 
 * 命名空间定义：
 * 为避免函数重名及发生冲突，需要程序员人按下列规则定义相关函数名称
 * 1、要加载的ajax目标文件都必须有一个主标识信息，在文件开头注释中注明，主标识格式：{项目应用标识}+{ajax执行文件名}; 如 comment_ajax
 * 2、js代码输出函数格式: js{主标识},其中主标识首字母大写; 如 js_comment_ajax();
 * 3、函数名格式： {主标识}_{函数名}; 如 comment_ajax_initList();
 * 3、私有函数名格式：_{主标识}_{函数名}; 如 _comment_ajax_checkUser();
 * 
 * 
 * 传递过来的数组中：
 * key为要加载的函数文件，文件必须在app的script目录中
 * value是要输出的函数名，支持多个,其中以js开头的函数负责输出js信息，其它的函数负责执行功能
 * 
 * $_GET['q']=service/newsletter/ajax:getCategorise|efg|msn,article/cate:abc|efg|msn,space/panel/action:userstatus|jsPanel
 */

include '../_inc.php';
func_initSession();

//加载系统默认的常用函数
$func=array('online');
if(!empty($_SESSION['UserLevel'])) $func[] = 'delfiles';

function online() {
	if (empty ($_SESSION["UserID"])) {
		return 0;
	} else {
		return 1;
	}
}

function delfiles($path,$id){
	if(!empty($_SESSION['UserLevel'])) @unlink(DOCUROOT.$path);
	return $id;
}

//加载通过URL传递过来的函数
$q=empty($_GET['q'])?null:$_GET['q'];

$config=strings::configStrDecode($q);
if(!empty($config)){
	foreach($config as $key=>$val){
		//主标识
		$tmp=explode('/',$key);
		if(in_array($tmp[0],conf('system'))){
			$id=$tmp[1].'_'.$tmp[2];
		}else{
			$id=$tmp[0].'_'.$tmp[1];
		}
		
		//文件路径
		$name=basename($key);
		$path=str_replace($name,'script/'.$name,$key);
		$filename=DOCUROOT.'/'.$path.'.php';
		if(file_exists($filename)) include $filename;
		
		//函数名
		$tmp=explode('|',$val);
		foreach($tmp as $fn){
			//按文件名直接加载
			if(function_exists($fn)){
				if(!in_array($fn,$func)) $func[]=$fn;
			}
			
			//按命名规则处理函数名后加载
			$fn_sys=$id."_".$fn;
			if(function_exists($fn_sys)){
				if(!in_array($fn_sys,$func)) $func[]=$fn_sys;
			}
			
			//加载默认js输出函数
			if(function_exists('js_'.$id)) $func[]='js_'.$id;
		}
	}
}

//初始化Ajax
$ajax=new Ajax();
$ajax->multiExport($func);