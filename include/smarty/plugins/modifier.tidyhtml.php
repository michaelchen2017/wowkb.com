<?php
function smarty_modifier_tidyhtml($html)
{
	
	if(!empty($html)){
		$tidy = new tidy();
		$html = $tidy->repairString($html, array('show-body-only'=>true), 'utf8');
	}
	return $html;
}
?>