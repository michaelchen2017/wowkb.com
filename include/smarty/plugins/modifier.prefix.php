<?php
function smarty_modifier_prefix($string,$search,$prefix)
{
	if(substr($string, 0,strlen($search))!=$search){
		$string = $prefix.$string;
	}
    return $string;
}
?>