<?php 
include '../../_inc.php';

//测试内容需要登录用户
func_initSession();
if(empty($_SESSION['UserID'])){
	$obj=load("passport_translate");
	if(!empty($obj))$obj->init();
}
if(empty($_SESSION['UserID'])) go( "/passport/admin.php?act=admin&errmsg=accessdeny&checkAuth=true&redirect=".rawurlencode( "/include/editor/api/demo.php" ) );


$obj=load("document_test");

if(!empty($_POST)){
	$_POST['title']=$_POST['title'];
	$_POST['text']=$_POST['content'];
	$_POST['datetime']=time();
	$obj->Update($_POST,array('datatype'=>'editor'));
}

$rs=$obj->getOne("*",array('datatype'=>'editor'));

/*输入编辑器的内容要使用htmlspecialchars编码*/
$title = htmlspecialchars($rs['title']);
$html = htmlspecialchars($rs['text']);

$datetime = date("Y-m-d H:i:s",times::getTime($rs['datetime']));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=6;IE=7;IE=8;IE=9">
		<title>编辑器测试－演示文档</title>
		<style type="text/css">
			body{font-size:12px;padding:10px;width:830px;margin:0 auto;}
			h2{margin:4px;}
			a:link,a:visited,a:hover,a:active{font-size:14px;line-height:32px;}
			a:link{color:#00F;text-decoration:none;}
			a:visited {color:#00F;}
			a:hover   {color:#F47B20;text-decoration:underline;}
			a:active  {color:#F00;} 
			#submit,#cancel{height:28px;margin:15px 0 ;padding:0 25px;font-size:14px;}
		</style>
	</head>
	<body>
	<?php 
		//显示提交的数据
		if(empty($_GET['act'])){
			echo "<h2>{$rs['title']}</h2> {$datetime} [<a href=\"./demo.php?act=edit\" style=\"font-size:14px;\">修改</a>] <hr>";
			echo $rs['text'];
		}else{
	?>
	<form action="./demo.php" method="post" name="form1" id="form1">
		<input type="text" id="title" name="title" style="width:100%;font-size:14px;line-height:26px;height:26px;" value="<?php echo $title;?>" />
		<br>
		<!-- 编辑器调用开始,编辑器ID content,一个页面仅支持一个编辑器 -->
		<iframe src="/include/editor/api/html/pre-load.html" width="0" height="0" frameborder="0" scrolling="no"></iframe>
		<script type="text/javascript" src="/include/editor/init.js"></script>
		
		[ <a onclick="editor.editorMode(); return false;" href="#editor">所见即所得|预览模式</a> ]&nbsp;&nbsp;
		[ <a onclick="editor.htmlMode(); return false;" href="#html">HTML源代码</a> ]
		
		<input type="hidden" id="content" name="content" value="<?php echo $html;?>" />
		<script type="text/javascript">
			/**定义编辑器*/
			editor.id = 'content'
			editor.width = "100%";
			editor.height = "480";
			editor.toolbar = 'simple';

			/**标识数据*/
			editor.dataID='1022';
			editor.dataType='blog';
			
			editor.init();
			
			function FCKeditor_OnComplete( editorInstance ){ editor.OnComplete(editorInstance);}
		</script>
		<!-- 结束 -->
		
		<input type="submit" id="submit" value="保 存"> 
		<input type="button" id="cancel" value="取 消" onclick="self.location='./demo.php';"> 
	</form>
	<?php 
		}
	?>
	</body>
</html>