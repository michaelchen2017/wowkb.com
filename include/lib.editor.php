<?php
//所有上传的文件都会记录在数据库中,文件实际存放的物理路径由具体程序指定，
//除page和document使用main库外，其他数据单元都使用自己的独立库

class editor{
	
	/**
	 * 使用php加载新的编辑器
	 * 示例参见：/include/editor/api/demo_1.php
	 * 
	 * @param $config = array(
			 	'id'=>'content',
			 	'width' =>'100%',
			 	'height' =>'450',
			 	'toolbar' =>'simple',
			 	'userID' =>123,
			 	'dataID' =>0,
			 	'dataType' =>'blog',
	  		  );
	   @param htmlspecial_encode string $content 
	 * @param array $config
	 */
	static function load($config,$content){
		$html ='<input type="hidden" id="'. $config["id"] . '" name="'. $config["id"] . '" value="'.$content.'" />';
		$html.='<iframe src="/include/editor/api/html/pre-load.html" width="0" height="0" frameborder="0" scrolling="no"></iframe>';
		
		$html.='<input  id="'. $config["id"] . '___Config" type="hidden" value="SkinPath=/include/editor/api/skin/&amp;CustomConfigurationsPath=/include/editor/api/config/'. $config["toolbar"] . '.js"/>';
		$html.='<iframe id="'. $config["id"] . '___Frame"  src="/include/editor/fck/editor/fckeditor.html?InstanceName='. $config["id"] . '&amp;Toolbar='. $config["toolbar"] . '" width="'.$config['width'].'" height="' . $config['height'].'" frameborder="0" scrolling="no"></iframe>';
	
		$html.='<input  id="'. $config["id"] . '___userID" type="hidden" value="'.$config['userID'].'"/>';
		$html.='<input  id="'. $config["id"] . '___dataID" type="hidden" value="'.$config['dataID'].'"/>';
		$html.='<input  id="'. $config["id"] . '___dataType" type="hidden" value="'.$config['dataType'].'"/>';
		$html.='<input  id="'. $config["id"] . '___TempID" type="hidden" value=""/>';
		$html.='<input  id="'. $config["id"] . '___PanelStatus" type="hidden" value="0"/>';
		$html.='<div id="'. $config["id"] . '___Tools"></div>';
		
		$html.='<script type="text/javascript" src="/include/editor/core.js"></script>';
		$html.='<script type="text/javascript">editor.id="'.$config["id"].'"; function FCKeditor_OnComplete( editorInstance ){ editor.OnComplete(editorInstance);} </script>';
	
		return $html;
	}
	
	/**
	 * 根据$config的定义建立编辑器内容
	 * $config=array(
	 *     "name"=>"编辑器实例名称",
	 *     "toolbar"=>"调用的编辑器配置",
	 *     "width"=>"宽度"，
	 *     "height"=>"高度",
	 *     "path"=>上传路径,
	 *     "sess"=>"是否检测缓存",
	 *
	 *     "others"=>"FCK定义的参数"
	 * );
	 *
	 * @param array $config
	 * @param string $content$config["sess"]=false
	 */
	static function get($config,$content=""){
		$FckBasePath="/include/editor/fck/";
		if(!class_exists("FCKeditor"))include(DOCUROOT.$FckBasePath."fckeditor.php");

		//默认调用编辑器的时候要检测session
		if (empty($_SESSION['UserID']) && !isset($config["sess"]) ) {
			http::meta();
			$html = "<h1 style='color:red;'>用户登录信息失效，无法创建编辑器！</h1>";
			return $html;
		}

		//建立编辑器
		$FCK = new FCKeditor( $config["name"] );
		$FCK->BasePath = $FckBasePath;
		$FCK->ToolbarSet = empty($config["toolbar"])?"Default":$config["toolbar"];
		$FCK->Value =  $content;
		$FCK->Width =$config["width"]= empty($config["width"])?"100%":$config["width"];
		$FCK->Height =$config["height"]= empty($config["height"])?"100%":$config["height"];
		$FCK->Upload =  !isset($config["upload"])?true:$config["upload"];

		if(empty($config['SkinPath'])) $config['SkinPath']=$FckBasePath.'editor/skins/silver/';
		if(empty($config['CustomConfigurationsPath'])) $config['CustomConfigurationsPath']='/include/editor/defaultConfig.js';
		if(empty($config['path'])) $config['path']='/upload/'.date('y-m/d').'/';
		$config['path']=strings::endstr($config['path']);

		//配置参数
		$skip=array('name','toolbar','width','height','sess','item');
		foreach($config as $k=>$v){
			if(in_array($k,$skip)) continue;
			$FCK->Config[$k] = $v ;
		}

		//处理上传参数
		$mid=empty($config['item']['mid'])?0:$config['item']['mid'];
		$cate=empty($config['item']['cate'])?'nocate':$config['item']['cate'];
		$temp=empty($mid)?md5(time().$cate.$_SESSION['UserID']):'noValue';

		//加载上传路径
		$uppath=array(
			'LinkUploadURL' => $FckBasePath."editor/filemanager/connectors/php/upload.php?Type=File&mid={$mid}&cate={$cate}&temp={$temp}",
			'ImageUploadURL' => $FckBasePath."editor/filemanager/connectors/php/upload.php?Type=Image&mid={$mid}&cate={$cate}&temp={$temp}",
			'FlashUploadURL' => $FckBasePath."editor/filemanager/connectors/php/upload.php?Type=Flash&mid={$mid}&cate={$cate}&temp={$temp}",

			'LinkBrowserURL' => "/include/editor/browser.php?Type=File&mid={$mid}&cate={$cate}&temp={$temp}",
			'ImageBrowserURL' => "/include/editor/browser.php?Type=Image&mid={$mid}&cate={$cate}&temp={$temp}",
			'FlashBrowserURL' => "/include/editor/browser.php?Type=Flash&mid={$mid}&cate={$cate}&temp={$temp}",
		);

		foreach($uppath as $k=>$v){
			$FCK->Config[$k] = $v."&uppath=".rawurlencode($config['path']) ;
		}

		$html=$FCK->CreateHtml();

		//输出用于SET时的参数字串
		$html.='<input name="'.$config["name"].'temp" id="'.$config["name"].'temp" type="hidden" value="'.$temp.'"/>';
		
		//标识导航状态的容器
		$html.='<input  id="'. $config["name"] . '___PanelStatus" type="hidden" value="0"/>';
		
		return $html;
	}

	/**
	 * 对于有附件上传功能的编辑器，上传结束后更新相关附件记录的关联信息
	 * 返回影响的列数
	 *
	 * @param int $mid
	 * @param string $element
	 * @param string $cate
	 * @return int $num
	 */
	static function set($mid,$element,$cate){
		if(empty($_POST[ $element.'temp' ])) return;

		$conf=conf('cms.editorConf','db');
		$db=isset($conf[$cate])?$conf[$cate]:$cate;

		$conn=func_getDB($db);
		$fields=array(
			'SQL'=>'`order`=id',
			'mid'=>$mid,
			'temp'=>null
		);
		$where=array(
			'user'=>empty($_SESSION["UserID"])?0:$_SESSION["UserID"],
			'temp'=>$_POST[ $element.'temp' ],
			'categorise'=>$cate,
		);
		$num=$conn->Update($fields,$where,'upload');

		return $num;
	}

	//找到编辑器内容中的图片，并存储到本地
	static function saveImg($content,$localPath='',$refe){
		$html = htmlDomNode::str_get_html( $content );
		$localPath = strings::endstr($localPath) . Cache::getDeepFolder( strings::getRandom(6),3);
		$dir = DOCUROOT . $localPath ;
		
		$images=array();
		foreach($html->find('img') as $element){
			$src=$element->src;
			$src=str_replace('\"', '', $src);
			if( strtolower( substr($src, 0, 4) ) != 'http' ) continue;
			if( !in_array($src, $images) ) $images[]=$src ;
		}

		if(!empty($images)){
			if( !is_dir($dir) ) files::mkdirs($dir);
			foreach($images as $img){
				$filename = picture::saveImg($img,$dir,files::getCleanFilename($img), true, $refe);
				//$filename = basename( $img );
				$content = str_replace($img, $localPath.$filename, $content);//替换原内容中的图片地址
			}
		}
		
		return $content;
	}
	
    function formatLink($html){
		$html=preg_replace_callback("/(href=[\'|\"])(http:\/\/)?([^\"]+)/",array($this,"setHref"),$html);
		return $html;
	}

	private function setHref($match){
		//针对外链地址增加过滤限制
		if( !$this->checkHref($match[3]) ){
			$href = "/";
		}else{
			$href = '/service/analytics/?type=editor&url='.rawurlencode( $match[2].$match[3] );
		}
		
		$url = $match[1]. $href ;
		return $url;
	}
	
	//TODO 加入更多的外链限制条件
	private function checkHref($match){
		//if( strstr($match,'tinypic.com') ) return false;
		
		return true;
	}
}
?>