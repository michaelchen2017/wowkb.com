<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {ubbcode} function plugin
 *
 * Type:     function<br>
 * Name:     ubbcode<br>
 * Purpose:	 ubbcode<br>
 * @author kokko <kokko313@gmail.com>
 * @param String
 * @param String
 */

function smarty_modifier_ubbcode($str)
{
    $str = eregi_replace('\[face\]([a-zA-Z0-9@:%_.~#-\?&]+)\[\/face\]', '<img src="/images/bbs/smilies/\\1'.'.gif"'.'>', $str); //img 
	//$str = eregi_replace('\[quote\](.+)\[\/quote\]', '<pre class="quote">\\1</pre>', $str); //quote 
	$str=preg_replace("/\[quote\]/is","<div class=\"quote\">",$str,-1); 
	$str=preg_replace("/\[\/quote\]/is","</div>",$str,-1); 
    return $str;
} 
/* vim: set expandtab: */
?>