<?php
/**
 * 由动态变量$value设置新变量
 * {%assign var="fn" value=$value.id|var:'fields'%}
 * 
 * @param string $value
 * @param string $prefix
 * @return string
 */
function smarty_modifier_var($value, $prefix)
{
   return $prefix."_".$value;
}