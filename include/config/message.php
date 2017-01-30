<?php
return array(
	"NoneAction"=>array("title"=>"模块错误","content"=>"找不到指定的模块！"),
	"NoneMethod"=>array("title"=>"操作错误","content"=>"程序无法找到您指定的操作模块！"),
	"errorID"=>array("title"=>"参数错误","content"=>"程序无法读取指定的参数！"),
	"NotEmpty"=>array("title"=>"内容不为空","content"=>"您要删除的项目内空不为空，或还有与系统其它部分关联的内容！"),
	"ErrorDomain"=>array("name"=>"域名未授权","content"=>"当前页面在您使用的域名下还没有授权！"),
	"NonePage"=>array("title"=>"访问址错误！","content"=>"请使用正确的URL访问内容！"),
	"NonePageTpl"=>array("title"=>"当前页面没设定显示模板！","content"=>"请正确设定页面访问模板，在此之前页面不能被正常浏览！"),
	"ClosePage"=>array("title"=>"页面已经关闭","content"=>"当前页面内容已经被管理员关闭！"),
	"NonePageApp"=>array("title"=>"应用程序定义错误！","content"=>"当前访问的页面没有定义任何数据单元！"),

	"400"=>array("title"=>"Bad request 错误请求","content"=>"Bad request 错误请求","tpl"=>"site"),
	"401"=>array("title"=>"Authorization Required 需要验证","content"=>"Authorization Required 需要验证","tpl"=>"site"),
	"403"=>array("title"=>"Forbidden 禁止","content"=>"Forbidden 禁止","tpl"=>"site"),
	"404"=>array("title"=>"Wrong page 找不到页面","content"=>"Wrong page 找不到页面","tpl"=>"site"),
	"500"=>array("title"=>"Internal Server Error 内部服务器错误","content"=>"Internal Server Error 内部服务器错误","tpl"=>"site"),
);
?>