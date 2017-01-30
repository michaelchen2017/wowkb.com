<?php
/**
 * 用于处理不同尺寸图片的预览显示
 * Created on 2008-3-18 By Weiqi
 *
 * 访问方式:使用重写间接访问,用于规范化处理对图片文件的请求
 * 
 * 重写设置后的地址
 * /images/170/170/data/somewhere/shop/1189476668-16631.gif?default=bbs
 *
 * 重写语句
 * RewriteRule ^images/([0-9]+)/([0-9]+)/(.*)$ /include/plugins/picture.php?w=$1&h=$2&path=$3 [QSA,L]
 *
 * ###由于性能方面有问题，暂不使用此功能###
 * 	由于path是经过rawurlencode的带/=>%2F的特殊编码路径,所以要在
 * 	apache中开启‘确定是否允许URL中使用经过编码的路径分割符’这个功能的支持
 * 	AllowEncodedSlashes On
 * 	作用域：server config, virtual host
 * ###由于性能方面有问题，暂不使用此功能###
 */

include '../_inc.php';
setlocale(LC_ALL, 'zh_CN.UTF8');//支持中文文件名
ini_set("memory_limit","64M");

new pic2cache();

class pic2cache{
	var $tmp;

	function __construct(){
		//处理url传递过来的参数
		$this->tmp['path']=empty($_GET["path"])?null:rawurldecode($_GET["path"]);

		//容错
		if(empty($this->tmp['path'])) $this->goDefault();
				
		$this->init();
	}

	private function init(){
		// 原始文件信息
		$filename=$this->checkFile();
		$fileinfo = picture::getImageInfo($filename); 
		
		$path=$this->tmp['path'];
		$w=empty($_GET['w'])?$fileinfo['w']:$_GET['w'];
		$h=empty($_GET['h'])?$fileinfo['h']:$_GET['h'];
		
		//不需要处理的图片
		if($fileinfo['w']<=$w && $fileinfo['h']<=$h){
			$path = defined( "CDNSERVER" )? "http://" .CDNSERVER. $path : $path;
			go( $path );
		}
		
		//预处理
		$fileinfo["ext"]=($fileinfo["ext"]=='bmp')?'jpg':$fileinfo["ext"];
		
		//cache文件ID
		$cacheID = $w."_".$h."-".basename($path);
		
		//去掉原文件后缀
		$cacheID = str_replace('.'.files::getExt($cacheID),'',$cacheID); 
		
		//原始文件的基础路径
		$baseFolder = dirname($path)."/"; 
		
		//缓存存储路径
		$cacheFolder = $this->getCacheFolder( $baseFolder );
		
		//缓存文件
		$CacheFile = DOCUROOT . $cacheFolder . $cacheID . "." . $fileinfo["ext"];

		//如果没有缓存或过期则建立缓存,正常情况下调用处理过的缓存文件
		if( $this->CheckPicCache($CacheFile,$filename) ){
			$pic=new picture();
			$pic->filepath=$filename;
			$pic->save_dir= DOCUROOT.$cacheFolder;
			$pic->leixing=3;

			$pic->width=$w;
			$pic->height=$h;
			$pic->filename=$cacheID;
			$pic->echoimage();

			//修改缓存文件的修改时间与源文件相同
			touch( $CacheFile , filemtime($filename) );
		}


		//输出图像
		$url = $cacheFolder . $cacheID . "." . $fileinfo["ext"];
		$url = defined( "CDNSERVER" )? "http://" .CDNSERVER. $url : $url;
		go( $url );

	}

	//检测目标文件是否存在
	private function checkFile(){
		$this->tmp['path']=$path=(substr($this->tmp['path'],0,1)=='/')?$this->tmp['path']:'/'.$this->tmp['path'];
		$filename= DOCUROOT.$path;// 原始文件
		
		if(!is_file($filename)) $this->goDefault();

		return $filename;
	}


	private function goDefault(){
		go("/images/".$this->getNonePicPath());
	}

	private function getNonePicPath(){
		$default=conf('global','images');
		$key=empty($_GET["default"])?null:$_GET["default"];
		$filename=empty($default[$key])?'none.jpg':$default[$key];
		return $filename;
	}
	
	private function getCacheFolder($baseFolder){
		$cacheFolder= "/cache/pic/".$baseFolder;
		
		if(substr($baseFolder,0,8)=='/upload/'){
			$cacheFolder = "/cache_upload/".substr($baseFolder,8);
			return $cacheFolder;
		}
		
		if(substr($baseFolder,0,6)=='/data/'){
			$cacheFolder = "/cache_data/".substr($baseFolder,6);
			return 	$cacheFolder;
		}
		
		return $cacheFolder;
	}

	function CheckPicCache($CacheFile,$filename){
		if(!file_exists( $CacheFile )){
			//没有建立
			return true;
		}
		if(isset($_GET["clear"])){
			//强制清除
			return true;
		}
		if(filemtime($filename)!=filemtime($CacheFile)){
			//源文件被更新
			return true;
		}
		return false;
	}
}
?>