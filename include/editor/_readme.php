<?php
/**
 * 编译JS参见：
 * java -jar compiler.jar --js hello.js --js_output_file hello-compiled.js
 * 
 * 
 * 设置参见：http://docs.cksource.com/FCKeditor_2.x/Developers_Guide
 * 
 * 初始化fck删除： '_'开头的文件，非php的server端脚本,source文件,多余的语言包，
 * 
 * 删除 editor/filemanager/connectors 中多余的软件包
 * 
 * 在editor基类中，设置调用自定义配置文件 defaultConfig.js
 * 
 * 在defaultConfig.js中定义常用参数，在初始化editor类时可以指定动态参数,如上传文件的路径
 * 
 * 修改 php 文件
 * =================================
    30行开启上传功能
	/editor/filemanager/connectors/php/config.php
	设置上传路径 并开启上传
	// Path to user files relative to the document root.
	$Config['UserFilesPath'] = empty($_GET["uppath"])?"/data/temp/":$_GET["uppath"] ;

	35行左右
	//设置上传文件的相关信息
	// user upload defined 
	$Config['UploadParam'] = array(
		'mid'=>empty($_GET["mid"])?0:intval($_GET["mid"]),
		'temp'=>empty($_GET["temp"])?'an-impossible-string':$_GET["temp"],
		'cate'=>empty($_GET["cate"])?"nocate":$_GET["cate"]
	);

 * =================================
	/editor/filemanager/connectors/php/commands.php
	增加：
	function getUploadFileName($oFileName){
		if(preg_match("/^[A-Za-z0-9-_.]+$/",$oFileName)){
			return $oFileName;
		}else{
			return time();
		}
	}
	// Get the uploaded file name.后增加
	$sFileName = getUploadFileName($oFile['name']) ;

	增加：
	//设置上传目录
	function getUploadParentFolder(){
		global $Config;
		
		if(empty($Config['UserFilesPath'])||substr($Config['UserFilesPath'],0,6)!='/data/'){
			$path = DOCUROOT.'/data/'.date('y-m/d').'/';
		}else{
			$path = DOCUROOT.$Config['UserFilesPath'];
		}
		
		if(!is_dir($path)) {
			files::mkdirs($path);
		}
		
		return $path;
	}

	在179行左右 // Get the uploaded file name前面,如下，注释掉原来的路径，换成本地路径
	// Map the virtual path to the local server path.
	// $sServerDir = ServerMapFolder( $resourceType, $currentFolder, $sCommand ) ;
	
	// 使用本地路径
	$sServerDir = getUploadParentFolder();

	在280行左右，SendUploadResults之前增加这段代码
	//使用数据库记录上传文件信息
	RecordUploadFile( $sErrorNumber, $sFileUrl, $sFileName ) ;

	在页面底部增加
	//记录上传文件
	function RecordUploadFile($sErrorNumber, $sFileUrl, $sFileName,$oFile){
		global $Config;
		$conn=func_getDB('upload');
		$now=time();
		
		$data=array(
			'mid'=>$Config['UploadParam']['mid'],
			'categorise'=>$Config['UploadParam']['cate'],
			'name'=>$oFile['name'],
			'save_name'=>$sFileName,
			'size'=>$oFile['size'],
			'type'=>$oFile['type'],
			'path_info'=>$sFileUrl,
			'note'=>null,
			'order'=>null,
			'filltime'=>$now,
			'updatetime'=>$now,
			'user'=>empty($_SESSION["UserID"])?0:$_SESSION["UserID"],
			'getpoint'=>empty($_POST["point"])?0:intval($_POST["point"]),
			'downloads'=>0,
			'ext'=>files::getExt($sFileName),		
			'temp'=>$Config['UploadParam']['temp'],
		);
		
		$id=$conn->Insert($data,'upload');
		$conn->Update(array('order'=>$id),array('id'=>$id),'upload');
	}
			
		
 * ==================================
    /editor/filemanager/connectors/php/upload.php
        在头部加载框架环境及初始化session
    require('../../../../../../_inc.php');
	func_initSession();
 *
 * ==================================
 * /editor/lang/zh-cn.js 改FontSize名称，大小=>字号
 * 
 * 
 * 自定义插件的设置
 * 1、修改zh-cn.js 152 行加// Plugins 
 * 2、
 * 3、fckeditorcode_gecko.js 84行,97行  增加新的命令
 * 
 * 
 * ==================================
 * 设置服务器浏览
 * TODO
 * 
 * ==================================
 * 设置自定义链接描述，显示详细词义
 * TODO
 */
?>