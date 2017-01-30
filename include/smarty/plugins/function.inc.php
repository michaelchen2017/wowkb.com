<?php
/**
 * smarty 注册函数，直接在模板中读取全局缓存内容
 * {%inc type="page" id="528"%}  向下级页面提供嵌入数据
 * {%inc type="homepage" id="528"%}  向首页提供嵌入数据
 * 
 * @param array
 * @return string
 * 
 */
function smarty_function_inc($params){

	$id="";
	$type="";
	
	foreach($params as $_key => $_val) {
		if($_key=='id') $id=$_val;
		if($_key=='type') $type=$_val;
	}

	if( empty($id) ) return "";
	if(!strstr($id,'_')) $id=conf('global','uid')."_".$id;
	
	$filename = DOCUROOT."/data/application/{$type}/{$id}.html";
	$cacheID='IncFile_'.$type.'_'.$id;
	$obj=func_initValueCache($filename,$cacheID);

	$content=$obj->get( $type.'_'.$id );
	if(empty($content)) $content='';
	
	if(debug::check('showIncData')){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
		if(empty($content)){
			echo "<h2>Null</h2>";
		}else{
			echo $content;
		}
		exit;
	}
	
	return $content;
}