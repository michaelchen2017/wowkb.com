<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty strip modifier plugin
 *
 * Type:     modifier<br>
 * Name:     strip<br>
 * Purpose:  Replace all repeated spaces, newlines, tabs
 *           with a single space or supplied replacement string.<br>
 * Example:  {$var|strip} {$var|strip:"&nbsp;"}
 * Date:     September 25th, 2002
 * @link http://smarty.php.net/manual/en/language.modifier.strip.php
 *          strip (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @version  1.0
 * @param string
 * @param string
 * @return string
 */

/**
 * 过滤不安全不允许的字符
 */
function smarty_modifier_notallow( $string,$encode='UTF-8' )
{
	$un_string = @include( DOCUROOT."/bbs/Config/filter-word.php" );
	$string =  trim( $string );
	$string = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$string);
    $string = preg_replace("/<script(.*?)>(.*?)<\/script>/si","",$string);
    $string = preg_replace("/<iframe(.*?)>(.*?)<\/iframe>/si","",$string);
	$string = preg_replace("/<script(.*?)/si","",$string);
	foreach( $un_string as $key=>$val ){
		$val = iconv( 'GBK',$encode,$val );
		$string = preg_replace("/$val/si","",$string);
	}
    return $string;
}
/* vim: set expandtab: */

?>
