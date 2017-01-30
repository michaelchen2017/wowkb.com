<?php
/**
 * 调用方式
 * include("inc.comm.php");
 * $skip=array('someFolderName','...','...');
 * include(DOCUROOT."/include/svn/svnsync.php");
 * 
 * 或在后台中调用
 * include("inc.comm.php");
 * $admin = true;
 * 
 * $defaultserver="default";
 * $servers=array(
 *     'default'=>'/pub/www/',
 *     'othersite'=>'/pub/www/',
 * );
 * 
 * include DOCUROOT."/admin/dashboard/Config/userface.php" ;
 * include DOCUROOT."/include/svn/svnsync.php";
 */

//初始化
if(!isset($_SESSION)){ func_initSession();}

//检查权限
if(!defined('SKIP_SVN_ADMIN')) checkAuth();

//服务器信息
$servername = ( !empty($servers) && !empty($_GET['server']))? $_GET['server'] : $_SERVER["SERVER_NAME"];
if( !empty($servers) ){
	$serveroot=$servers[$defaultserver];
	if(!empty($_GET['server'])){
		if(!empty($servers[$_GET['server']])) $serveroot = $servers[$_GET['server']];
	}
}else{
	$serveroot = DOCUROOT;
}

$str = null;
$parent=null;
$path=null;
$version=null;

//加载变量
if ( !empty ( $_POST )) {
	$parent=$_POST["parent"];
	$path=$_POST["path"];
	$version=$_POST["version"];
	
	$str = mysvn();
	if(empty($str)) $str="SVN 同步失败！";
	if(!empty($_POST["publish"])) $res = publish();
}

//加载路径信息
$list = files::fileList( $serveroot );
$target = array();
$target[]=".";

foreach($list as $value){
	if( is_dir( $serveroot."/".$value ) && is_dir( $serveroot."/".$value."/.svn" )  ){
		if(isset($skip)){
			if(!in_array($value,$skip)) $target[] = $value;
		}else{
			$target[] = $value;
		}
	}
}

//生产环境同步
function publish(){
	if(empty($_POST["parent"])) return;
	if($_POST["parent"]=='.') return;
	
	if(defined("svnServerList")){
		$conf = include svnServerList;
	}else{
		$conf = include DOCUROOT."/admin/tools/srcSync/svnServerList.php";
	}
	
	$path = "/".$_POST["parent"];
	if(!empty($_POST["path"])){
		if(substr($_POST["path"],0,1)=='/'){
			$path .= $_POST["path"];
		}else{
			$path .= '/'.$_POST["path"];
		}
	}
	
	$obj = func_initMemcached("sourceNode");
	
	$checkPos = false;
	foreach($conf as $key){
		$checkval = $obj->get(systemVersion."svn_".$key);
		if(!empty($checkval)) {
			$msg= $key.": 其它用户正在执行发布程序，请稍后再试!";
			$checkPos=true;
			break;
		}
	}
	
	if(!$checkPos){
		$msg="SVN Rsync {$path}\n";
		foreach($conf as $key){
			$obj->add(systemVersion."svn_".$key,$path,false,0);
			if(!empty($_POST["version"])){
				$version=intval($_POST["version"]);
				if(!empty($version)) $obj->add(systemVersion."svn_ver_".$key,$version,false,0);
			}
			$msg .= "Added {$key}...\n";
		}
	}
	
	return $msg;
}

//SVN同步操作
function mysvn() {
	$memObj = func_initMemcached("sourceNode");
	
	$memCheckID = systemVersion.'source_code_checkout_lock';
	$check = $memObj->get($memCheckID);
	if(!empty($check)) return $memCheckID.":其它用户正在执行发布程序，请稍后再试!";
	
	//加锁
	$memObj->set($memCheckID,1,false,0);
	
	global $serveroot;
	$config=conf("global","svn");
	
	$cmd=array(
		'linux'=>"export LANG=zh_CN.UTF-8\nexport LC_ALL=zh_CN.UTF-8\nsudo /usr/bin/svn",
		'freebsd'=>"setenv LC_CTYPE zh_CN.UTF-8\n/usr/local/bin/svn",
		'hostmonster'=>"export LANG=zh_CN.UTF-8\nexport LC_ALL=zh_CN.UTF-8\n ".str_replace('/public_html','',$serveroot)."/svn/bin/svn",
	);
	
	$path = $_POST["path"];
	if(substr($path,0,1)=='/'){
		$path=substr($path,1);
	}
	
	$v=intval($_POST["version"]);
	$revision = empty( $v )?"":"--revision ".$_POST["version"];
	$filename = ($_POST['parent']=='.')?$serveroot."/".$path : $serveroot."/".$_POST['parent']."/".$path;

	$cmdstr=$cmd[$config['os']]." update  {$filename} {$revision}";
	if($config['svnpass']!="123456") $cmdstr.=" --username={$config['svnuser']} --password={$config['svnpass']}";
	$output = shell_exec($cmdstr);

	if(!empty($output)){
		svnlog($output);
	}
	
	//解锁
	$memObj->delete($memCheckID);
	
	return $output;
}

//SVN日志操作
function svnlog($output){
	global $serveroot;
	$folder = $serveroot . "/data/logs/svn/".date("y-m-d",time()+8*3600)."/";
	if( !file_exists( $folder) ){
		files::mkdirs( $folder);
	}
	$userID = empty($_SESSION["UserID"])?0:$_SESSION["UserID"];
	file_put_contents( $folder. $userID . "_" .date("H-i-s",time()+8*3600) .".log", $output);
}

//权限认证
function checkAuth($level=1,$ids=array(0,1)){
	$html='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	$html='<h1>权限检测失败，请以管理员身份在当前站点重新登录!</h1>';

	//是否登录
	if(empty($_SESSION ["UserID"])){
		echo $html;exit;
	}
	if($level==1){
		return true;
	}

	//是否为管理员
	if( $_SESSION["UserLevel"]!=1 ){
		echo $html;exit;
	}
	if($level==2){
		return true;
	}

	if( !in_array($_SESSION ["UserID"], $ids)){
		echo $html;exit;
	}


	return true;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>SVN 同步设置</title>
	<style>
		#post{
			float:left;
			width:auto;
			margin: 0px auto 0px auto; 
			padding: 0px;			
		}
		#content{
			margin: 0px auto 0px auto; 
			padding: 0px 20px 0px 20px;
		}
		hr{
			width:99%;
		}
		strong{
			color:red;
		}
	</style>	
	<script type=text/javascript>
	<!--
	function checkfrom(){
		if(document.getElementById('parent').value==""){
			window.alert("请选择要同步的父目录");
			return false;
		}else if(isNaN(document.getElementById("version").value)){
			window.alert('版本信息必须是数字！');
            document.getElementById("version").select();
            document.getElementById("version").focus();
            return false;
		}else if(document.getElementById('path').value==""||document.getElementById('path').value=="/"){
			if(checkclick("确认同步整个 ‘"+getParentPath(document.getElementById('parent').value)+"’ 目录么？")){
				return true;
			}else{
				document.getElementById('parent').focus;
				return false;
			}
		}else{
			return true;
		}	
	}
	function getParentPath(pathinfo){
		if(pathinfo=='.'){
			return "站点根目录";
		}
		return pathinfo;
	}
	function checkclick(msg){
	    if (confirm(msg)) {
	        return true;
	    }
	    else {
	        return false;
	    }
	}
	-->
	</script>
	<?php if(!empty($admin)){ ?>
	<style>
		#box{
			width:98%;
			height:24px;		
			padding:5px;
			margin:0px auto 6px auto;			
			line-height:24px;
			clear:both;
		}
		input,select,span,div{
			font-size:12px;
		}
	</style>
	<link href="/css/admin/basic.css" rel="stylesheet" type="text/css"/>
    <link href="/css/admin/<?php echo USERFACE;?>.css" rel="stylesheet" type="text/css"/>
	<?php }else{?>
	<style>
		#box{
			background-color:#E5E5E5;
			width:98%;
			height:30px;		
			padding:5px;
			margin:0px auto 6px auto;			
			line-height:30px;
			clear:both;
		}
		input,select,span,div{
			font-size:16px;
		}
	</style>
	<?php } ?>
</head>
<body>
	<?php if(!empty($admin)){ ?><div id="top">
	  <table width="100%" height="30" border="0" cellpadding="0" cellspacing="0" class="dhbg">
	    <tr>
	      <td align="left">&nbsp;&nbsp;<span class="whitelink">版本同步</span></td>
	      <td align="right" class="whitestyle">&nbsp;&nbsp;</td>
	    </tr>
	  </table>
	  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bline">
	    <tr>
	      <td><table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
	          <tr>
	            <td height="24">您的位置：>> 版本同步 >> 服务器 <?php echo $servername; ?></td>
	            <td align="right"></td>
	          </tr>
	        </table></td>
	    </tr>
	  </table>
	  <table width="100%" height="6"  border="0" align="center" cellpadding="0" cellspacing="0">
	    <tr>
	      <td></td>
	    </tr>
	  </table>
	</div>
	<?php } ?>
	<div id='box'>
		<form name="svn" id="post" method="post" action="<?php echo $_SERVER["SCRIPT_NAME"]?>?server=<?php echo $servername;?>" onsubmit="return checkfrom();">			
			<?php 
			if( empty($servers) ){
			?>
			<span style="float:left;"><strong><?php echo $servername; ?></strong> 
			<?php 
			}else{
				echo "<select name='serverpath' onchange='window.location=\"".$_SERVER['SCRIPT_NAME']."?server=\"+this.options[this.selectedIndex].text'>";
				foreach($servers as $k=>$v){
					$status='';
					if(!empty($_GET['server'])){
						if($_GET['server']==$k) $status="selected";
					}
					echo "<option value='{$v}' {$status}>{$k}</option>";
				}
				echo "</select>";
			}
			?>	
			SVN 同步设置:</span>
			<select name="parent" id="parent">
				<option value="">请选择目录</option>
				<?php foreach ($target as $value){ ?>
				<option value="<?php echo $value;?>"
				<?php if($value==$parent) {echo "selected";}?>><?php echo $value;?></option>
				<?php }?>
			</select> 			
			<input type="text" name="path" id="path" value="<?php echo $path;?>" size="35" title="要更新的目标文件"/> 
			<input type="text" name="version" id="version" value="<?php echo $version;?>" size="5" title="目标版本，留空为最新版本"/>
			&nbsp;发布到生产环境：<input type="checkbox" name="publish" value="1">&nbsp;&nbsp;
			<input type="submit" name="submit" value="提交" />	
		</form>
		<div>&nbsp;&nbsp;清理数据表结构：http://<?php echo $servername; ?>/include/plugins/cleartb.php?mod=数据对象</div>
	</div>
	<hr style="clear:both;">
	<div id="content">
	<?php
		echo "<pre>";
		if(!empty( $res ))echo $res;
		if(!empty( $str ))echo $str;
		echo "</pre>";
	?>
	</div>	
	<br>
	<br>
</body>
</html>