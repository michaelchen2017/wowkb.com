<?php
/**
 * smarty 注册函数，直接在模板中读取全局变量呈现广告
 * 
 * 指定广告ID
 * {%ad aid="domainID_ADID"  q=$smarty.get.q%}
 * 
 * 根据网站配置
 * {%ad type="Leaderboard_946_90" q=$smarty.get.q%}
 * 
 * @param array
 * @return string
 */
function smarty_function_ad($params){

	$type='';
	$q='';
	$aid='';
	
	foreach($params as $_key => $_val) {
		if($_key=='type') $type=$_val;
		if($_key=='q') $q=$_val;
		if($_key=='aid') $aid=$_val;
	}
	
	//没有直接指定aid时调用系统广告ID
	if(empty($aid)){
		if( empty($type) ) return "";//广告位类型
		
		$aid=conf("global","ad.{$type}");
		if( empty($aid) ) return "";
	}
	
	$domain=strings::endstr(conf('global','ad.serviceDomain'));
	$q=empty($q)?"":"?q=".$q;
	
	$adstr='<SCRIPT language="JavaScript1.1" SRC="'.$domain.'show/'.$aid.'/'.$q.'"></SCRIPT>';
	
	return $adstr;
}
