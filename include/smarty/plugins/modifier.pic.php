<?php
//判断并输出缩略图
function smarty_modifier_pic($string,$w,$h)
{
   	if(empty($string)) return '/images/none.jpg';
	if(substr($string,0,1)=='/'){
		if(!file_exists( DOCUROOT. $string )) return '/images/none.jpg';
		if(!empty($w)&&!empty($h)){
			return "/images/{$w}/{$h}".$string;
		}
	}
	
	return $string;
}