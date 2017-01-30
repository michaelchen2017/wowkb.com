<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
/**
 * 针对中文及utf8环境作了修改
 * PS：$length中的字符数对于中文是，1个中文字符=2
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...',$count_words = true)
{
	mb_internal_encoding("UTF-8");
	//原始输入字符串
	$subject = $string;
	
	$wordscut="";
	if ($length == 0) return '';

	if ( strlen( $string ) <= $length ) return $string;
	$string = $search = strip_tags($string);//去掉html标签截取,记录查找条件
	
	preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $info);
	
	$replace='';//截取后的字符
	
	if( $count_words ){
		$j = 0;
		for($i=0; $i<count($info[0]); $i++) {
			$wordscut .= $info[0][$i];
			if( ord( $info[0][$i] ) >=128 ){
				$j = $j+2;
			}else{
				$j = $j + 1;
			}
			if ($j >= $length ) {
				$replace = $wordscut.$etc;
				break;
			}
		}
		if(empty($replace)) $replace = join('', $info[0]);
	}
	if(empty($replace)) $replace = join("",array_slice( $info[0],0,$length ) ).$etc;
	
	//判断是否还原html标签
	$result = (strstr($subject, $search))?str_replace($search, $replace, $subject) : $replace;
	
	return $result;
}
?>