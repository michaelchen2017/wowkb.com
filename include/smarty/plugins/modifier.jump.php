<?php
//{%$somevalue|jump:$type:$id:$keyword%}
function smarty_modifier_jump($string,$type,$id,$keyword="")
{
	if(!empty($keyword)) $string=str_replace('KEYWORD',$keyword,$string);
	$url="http://analytics.yestogo.com/jump/{$type}_{$id}/?url=".rawurlencode($string);
    return $url;
}