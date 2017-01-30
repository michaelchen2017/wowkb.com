<?php
class upload{
	/**
	 * 与php内置函数　move_uploaded_file　接口保持一致
	 * 根据上传文件的性质，进行处理
	 */
	function move_uploaded_file($source,$target){
		$fileType = upload::getFileType($source);
		
		//使用php内置函数执行上传文件操作
		move_uploaded_file($source,$target);
		
		//没定义处理的情况，直接返回
		if(empty($fileType)) return;
		
		//执行特殊处理
		upload::$fileType($target);
	}
	
	function getFileType($source){
		static $pos;
		if(!isset($pos)) $pos = upload::getFiles();
		
		if(empty($pos[$source]['name'])) return null;
		
		$srcFileExt = strtolower(files::getExt($pos[$source]['name']));
		
		//图片处理
		if(in_array($srcFileExt,array('jpg','jpeg','gif','png','bmp'))) return 'picture';
	
		//TODO 加入其它处理
		
		return null;
	}
	
	//建立以临时文件名命名的数组缓存
	function getFiles(){
		global $_FILES;
		
		$list=array();
		foreach($_FILES as $key=>$val){
			$val['fields'] = $key;
			$list[$val['tmp_name']]=$val;
		}
		
		return $list;
	}
	
	//对图片进行过滤读取操作
	function picture($filename){
		$fileinfo = picture::getImageInfo($filename);
		
		$pic=new picture();
		$pic->filepath=$filename;
		$pic->save_dir= dirname($filename);
		$pic->leixing=3;

		$pic->width=$fileinfo['w'];
		$pic->height=$fileinfo['h'];
		$pic->filename=files::getCleanFilename($filename);
		$pic->echoimage();
		
		
		//处理水印
		$imagesDefine = conf('global','imagesDefault');
		if(!empty($imagesDefine['waterFile'])){
			$waterimg=DOCUROOT.$imagesDefine['waterFile'];
			$position=empty($imagesDefine['waterFilePos'])?'RB':$imagesDefine['waterFilePos'];
			$pic->imgwater($filename, $position, $waterimg);
		}
		
		unset($pic);
	}
}
?>