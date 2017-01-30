<?php
/**
 * smarty 修饰函数，直接在模板中读取全局变量，并返回数组信息
 * {%'说明注释信息'|conf:$pointer:$key%}
 * @param array
 * 		pointer：conf()调用的对象
 * 		key：conf()读取的数据	
 */
function smarty_modifier_conf($string,$pointer,$key=null)
{
	//安全检查
	if(in_array($pointer,array('db','appname','sess'))) return;
	
	$result=conf($pointer,$key);
	if(isset($result['system']))unset($result['system']);
	if(isset($result['svn']))unset($result['svn']);
	
	return $result;
}