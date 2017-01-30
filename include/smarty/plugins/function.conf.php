<?php
/**
 * smarty 注册函数，直接在模板中读取全局变量,不能返回数组信息
 * {%conf pointer="global" key="uid" val="somevaluekey"%}
 * @param array
 * 		pointer：conf()调用的对象
 * 		key：conf()读取的数据	
 * 		val:conf()返回的数据为数组时，指定返回数组中指定key对应的value；
 */
function smarty_function_conf($params){
	$pointer="global";
	$key=null;
	$val=null;

	foreach($params as $_key => $_val) {
		if($_key=='pointer') $pointer=$_val;
		if($_key=='key') $key=$_val;
		if($_key=='val') $val=$_val;
	}

	//安全检查
	if(in_array($pointer,array('db','appname','sess'))) return;
	
	$result=conf($pointer,$key);
	if(is_array($result)) {
		$result=empty($val)?"Array":$result[$val];
	}
	
	return $result;
}