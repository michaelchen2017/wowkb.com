<?php
/**
 ####################################################################
 ##	File:	picture.php
 ##	By weiqi<weiqi228@gmail.com>
 ##	@Version 1.0<2006-12-04>
 ##	图片处理类
 ## 类参数第一组为必选参数，第二组为可选参数
 ####################################################################
 */
class picture{

	var $save_dir;                               // 保存路径
	var $leixing;                                // 1：宽度优先；2：高度优先；3：适应区域；4：裁剪原图；
	var $filepath;                               // 图像路径
	var $width;                                  // 最终宽度
	var $height;                                 // 最终高度

	var $filename;                               // 图像文件名
	var $ext;                                    // 图像扩展名
	var $extout;                                 // 图像输出类型
	var $image;                                  // 源图像数据
	var $image_p;                                // 目标图像数据
	var $zuobiao=array(0,0,0,0);        	     // 输出点坐标（0,0）原图拷贝点坐标(0,0)
	var $width_tar;                              // 原图拷贝范围的宽
	var $height_tar;                             // 原图拷贝范围的高
	var $width_orig;                             // 原图的宽
	var $height_orig;                            // 原图的高
	var $extnum;                                 // 原始图片扩展名标识
	var $waterfile; 						     // 默认水印图片地址
	var $error_message="";                       // string to be output if neccesary

	static $IMAGETYPE=array(
		1 => 'GIF',
		2 => 'JPG',
		3 => 'PNG',
		4 => 'SWF',
		5 => 'PSD',
		6 => 'BMP',
		7 => 'TIFF',//(intel byte order)
		8 => 'TIFF',//(motorola byte order)
		9 => 'JPC',
		10 =>' JP2',
		11 => 'JPX',
		12 => 'JB2',
		13 => 'SWC',
		14 => 'IFF',
		15 => 'WBMP',
		16 =>'XBM'
	);

	function __destruct( ){
		@imagedestroy($this->image_p);
		@imagedestroy($this->image);
	}

	// 输出图片
	function echoimage($type=null){
		if( is_file($this->filepath) ){
			if(!empty($type)){
				$this->GetSize($type);
			}else{
				$this->GetSize($this->leixing);
			}
			
			$this->readimage();
			$this->writeimage();
		}else{
			return false;
		}
	}

	// 获取图片尺寸信息
	function GetSize( $type ){
		list($w, $h, $extnum) = getimagesize($this->filepath);
		$this->width_orig = $w;
		$this->height_orig = $h;
		$this->extnum = $extnum;
		
		if(empty($this->width) && empty($this->height)) return false;
		if(empty($this->width)){$this->heightFirst($w,$h);return;}
		if(empty($this->height)){$this->widthFirst($w,$h);return;}
		
		if($type==1) $this->widthFirst($w, $h);
		if($type==2) $this->heightFirst($w, $h);
		if($type==3) $this->resize($w, $h);
		if($type==4) $this->cut($w, $h);
	}

	//优先宽度满足要求
	private function widthFirst($w,$h){
		$temp_w=$this->width;
		$temp_h=($this->width / $w) * $h;
		$this->height =intval($temp_h);
	}

	//优先高度满足要求
	private function heightFirst($w,$h){
		$temp_h=$this->height;
		$temp_w=($this->height / $h) * $w;
		$this->width =intval($temp_w);
	}

	//重置图片大小到指定区域
	private function resize($w,$h){
		if($w < $this->width){
			$temp_w=$w;
			$temp_h=$h;
			if($temp_h>$this->height){
				$temp_w = ($this->height / $temp_h) * $temp_w;
				$temp_h=$this->height;
			}
			$this->width=$temp_w;$this->height =$temp_h;
		}else{
			$temp_w=$this->width;
			$temp_h=($this->width / $w) * $h;
			if($temp_h>$this->height){
				$temp_w = ($this->height / $temp_h) * $temp_w;
				$temp_h=$this->height;
			}
			$this->width=$temp_w;$this->height =$temp_h;
		}
	}

	//裁剪图片到指定大小
	private function cut($w,$h){
		//针对已经手动设置了裁剪条件的情况
		if(!empty($this->width_tar)||!empty($this->height_tar)) return;
		
		if($this->width > $this->height){
			//默认认为需要裁剪高
			$ratio_h = round($w*$this->height/$this->width);
			$this->width_tar=$w;
			$this->height_tar=$ratio_h;

			//如果缩放比例不对，默认参数更换为裁剪宽
			if($ratio_h<$this->height) {
				$ratio_w = round($h*$this->width/$this->height);

				//正常情况
				$this->width_tar=$ratio_w;
				$this->height_tar=$h;

				//采样范围超出原图处理
				if($ratio_w>$w){
					$this->width_tar=$w;
					$this->height_tar=$this->height*$w/$this->width;
				}
			}
		}else{
			//默认认为需要裁剪宽
			$ratio_w = round($h*$this->width/$this->height);
			$this->width_tar=$ratio_w;
			$this->height_tar=$h;

			//如果缩放比例不对，默认参数更换为裁剪高
			if($ratio_w>$this->width) {
				$ratio_h = round($w*$this->height/$this->width);

				//正常情况
				$this->width_tar=$w;
				$this->height_tar=$ratio_h;

				//采样范围超出原图处理
				if($ratio_h>$h){
					$this->width_tar=$this->width*$h/$this->height;
					$this->height_tar=$h;
				}

			}
		}
	}
	
	// 读取图片数据
	function readimage () {
		//如果没有选择操作类型，默认缩放图片到指定大小
		if(empty($this->extnum)) $this->GetSize(3);
		
		//输出点坐标
		$dst_x=$this->zuobiao[0];
		$dst_y=$this->zuobiao[1];

		//图片输出尺寸
		$width=$this->width;
		$height=$this->height;

		//采样点坐标
		$src_x=$this->zuobiao[2];
		$src_y=$this->zuobiao[3];

		// 原图取样范围
		$width_tar=$this->width_tar;
		$height_tar=$this->height_tar;

		if(empty($width_tar)){
			$width_tar=$this->width_orig;
		}
		if(empty($height_tar)){
			$height_tar=$this->height_orig;
		}

		// 图片随机名
		if($this->filename==""){$this->filename=time();}

		// 图片扩展名
		$IMAGETYPE=picture::$IMAGETYPE;
			
		if($this->ext==''){
			$this->ext=empty($IMAGETYPE[$this->extnum])?'png':strtolower($IMAGETYPE[$this->extnum]);
		}
		$this->extout=strtolower($this->extout);
		if($this->extout==""){$this->extout=$this->ext;}
		
		//建立目标图像对象
		if($this->extnum==1){
			$image_p = imagecreate($this->width, $this->height);
		}else{
			$image_p = imagecreatetruecolor($this->width, $this->height);
		}

		switch ($this->extnum) {
			case 1:
				$image=imagecreatefromgif($this->filepath);
				break;
			case 2:
				$image = imagecreatefromjpeg($this->filepath);
				break;
			case 3:
				$image=imagecreatefrompng($this->filepath);
				break;
			case 6:
				$image=$this->ImageCreateFromBMP($this->filepath);
				break;
			default :
				return false;
				break;
		}
		//print_r(array( $dst_x,$dst_y, $src_x,$src_y, $width, $height, $this->width_tar, $this->height_tar));
		imagecopyresampled($image_p, $image, $dst_x,$dst_y, $src_x,$src_y, $width, $height, $width_tar, $height_tar);

		//输出目标图像数据
		$this->image_p=$image_p;
		//记录源图采样数据
		$this->image=$image;
	}

	// 输出图片
	function writeimage(){
		$image_p = $this->image_p;
		$this->extout=($this->extout=='bmp')?'jpg':$this->extout;
		$filename = $this->filename.".".$this->extout;
		if(!is_dir($this->save_dir)){ files::mkdirs($this->save_dir);}
		if( substr($this->save_dir,strlen($this->save_dir)-1)!='/' ) $this->save_dir=$this->save_dir.'/';
		
		switch($this->extout){
			case "gif":
				//针对gif类型的文件，没有质量参数
				imagegif($image_p, $this->save_dir.$filename);
				break;
			case "jpg":
				//针对jpg类型的文件，质量参数的范围是0~100
				imagejpeg($image_p, $this->save_dir.$filename, 80);
				break;
			case "jpeg":
				imagejpeg($image_p, $this->save_dir.$filename, 80);
				break;
			case "png":
				//针对png类型的文件，质量参数的范围是0~9
				imagepng($image_p, $this->save_dir.$filename,8);
				break;
			case "bmp":
				imagejpeg($image_p, $this->save_dir.$filename, 80);
				break;
			default :
				return false; //不支持的源图像格式！
				break;
		}
		imagedestroy($this->image_p);
		imagedestroy($this->image);
		return true;
	}

	//图片水印
	function imgwater($img, $position='RB', $waterimg='', $quality=75 ){
		if($waterimg==''){
			$waterimg=DOCUROOT.picture::$waterfile;
		}
		$imginfo = getimagesize($img);		//获取图片信息
		$wimg = getimagesize($waterimg);	//获取水印图片信息
		$position=explode(",",$position);   //获得位置数组

		if( ( $imginfo && count( $imginfo ) ) && ( $imginfo && count( $wimg ) ) ){

			switch ( $imginfo[2] ) {
				case 1: $im = imagecreatefromgif( $img );break;
				case 2: $im = imagecreatefromjpeg( $img );break;
				case 3: $im = imagecreatefrompng( $img );break;
			}
			switch ( $wimg[2] ) {
				case 1: $im2 = imagecreatefromgif( $waterimg );break;
				case 2: $im2 = imagecreatefromjpeg( $waterimg );break;
				case 3: $im2 = imagecreatefrompng( $waterimg );break;
			}
			if( $im && $im2 ){
				$width = imagesx( $im );
				$height = imagesy( $im );
				$ww = imagesx( $im2 );
				$wh = imagesy( $im2 );
				//判断是否可以添加水印
				if( $ww>$width||$wh>$height ){return false; }

				foreach($position as $val){
					//水印坐标
					$p=$this->getWaterXY($val,$width,$height,$ww,$wh);
					imagecopy( $im,$im2,$p["x"],$p["y"],0,0,$ww,$wh );
				}

				switch ($imginfo[2]) {
					case 1: Imagegif ( $im,$img ); break;
					case 2: ImageJpeg ( $im,$img,$quality ); break;
					case 3: Imagepng ( $im,$img );break;
				}

				ImageDestroy( $im );
				ImageDestroy( $im2 );

				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	//获取水印坐标
	function getWaterXY($type,$width,$height,$ww,$wh){

		switch($type){
			case 'RB':
				$dst_x=$width-$ww;
				$dst_y=$height-$wh;
				break;
			case 'RT':
				$dst_x=$width-$ww;
				$dst_y=0;
				break;
			case 'LB':
				$dst_x=0;
				$dst_y=$height-$wh;
				break;
			case 'LT':
				$dst_x=0;
				$dst_y=0;
				break;
			case 'CC':
				$dst_x=($width-$ww)/2;
				$dst_y=($height-$wh)/2;
				break;
			default:
				break;
		}
		$xy=array("x"=>$dst_x,"y"=>$dst_y);
		return $xy;
	}

	static function getImageInfo($filename){
		$IMAGETYPE= picture::$IMAGETYPE;
		if(!list($w, $h,$extnum) = @getimagesize( $filename )) return false;
		$ext=empty($IMAGETYPE[$extnum])?'png':strtolower($IMAGETYPE[$extnum]);
		$result=array(
		  	 "w"=>$w,
		  	 "h"=>$h,
		  	 "ext"=>$ext,
		);
		return $result;
	}
	
	static function getPicTrueType($path,$info=false){
		if(!file_exists($path)) return false;
		list($w,$h,$extnum) = getimagesize($path);
		$type = empty(picture::$IMAGETYPE[$extnum])?'none':strtolower(picture::$IMAGETYPE[$extnum]);
		
		if($info) return array( 'w'=>$w,'h'=>$h,'type'=>$type );
		
		return $type;
	}
	
	//根据真实图片信息返回符合条件的尺寸
	static function getImgWH($info,$maxW,$maxH){
		$ratio = $info['w']/$info['h'];
		
		$temp_w = $info['w'];
		$temp_h = $info['h'];
		
		if($temp_w > $maxW ){
			$temp_w = $maxW;
			$temp_h = $temp_w / $ratio;
		}
		
		if( $temp_h > $maxH){
			$temp_h = $maxH;
			$temp_w = $temp_h * $ratio;
		}
		
		$info['w']=round($temp_w);
		$info['h']=round($temp_h);
		
		return $info;
	}

	//获取网络图片,filename为不带扩展名的文件名称
	static function saveImg($url="",$path="",$filename="",$iswrite=true, $refer=""){
		if($url==''||$path==''){
			return false;
		}
		$imginfo=getimagesize ($url);
		if(empty($imginfo[2])){
			$ext=".png";
		}else{
			$IMAGETYPE=array(
			1 => 'GIF',
			2 => 'JPG',
			3 => 'PNG',
			4 => 'SWF',
			5 => 'PSD',
			6 => 'BMP',
			7 => 'TIFF',//(intel byte order)
			8 => 'TIFF',//(motorola byte order)
			9 => 'JPC',
			10 =>' JP2',
			11 => 'JPX',
			12 => 'JB2',
			13 => 'SWC',
			14 => 'IFF',
			15 => 'WBMP',
			16 =>'XBM'
			);
			$ext=empty($IMAGETYPE[$imginfo[2]])?'png':strtolower($IMAGETYPE[$imginfo[2]]);
			$ext=".".$ext;
		}
		if($filename==""){
			$filename=substr(md5(microtime()),0,8).$ext;
		}else{
			$filename=$filename.$ext;
		}
		if(substr($path,strlen($path)-1)!="/"){$path=$path."/";}
		
		if(empty($refer)){
			$urlarr = parse_url($url);
			$refer = $urlarr['scheme']. '://' .$urlarr['host'];
		}
		
		//使用浏览器获取图片文件，自动添加referer
		$img = browser::getFile($url,$refer);

		//根据$iswrite设置决定是覆盖还是留原文件
		if($iswrite){
			if(file_exists($path.$filename)) unlink($path.$filename);
		}else{
			if(file_exists($path.$filename)) rename( $path.$filename,  $path.time()."_".$filename );
		}

		$fp=fopen($path.$filename, "a");
		fwrite($fp,$img);
		fclose($fp);

		return $filename;
	}

	//输出用于人为识别的复杂验证码
	static function randomcode($radomcode=null,$w=62,$h=25,$bg=true,$boder=false,$sessname="radomcode"){
		if(!isset($_SESSION)) func_initSession();
		if(empty($radomcode)) $radomcode=strings::getRandom(4,'num');

		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");

		$_SESSION[$sessname]=$radomcode;

		$im = imagecreate($w, $h);
		$backgroundcolor = imagecolorallocate ($im, 255, 255, 255);

		//设置字符
		$i=0;
		$radomcodearr = str_split($radomcode);
		foreach($radomcodearr as $word) {
			$imcodefile = DOCUROOT.'/include/font/number/'.$word.'.gif';
			$x = $i * 13 + mt_rand(0, 4) - 2;
			$y = mt_rand(0, 3);
			if(file_exists($imcodefile)) {
				$imcode = imagecreatefromgif($imcodefile);
				$data = getimagesize($imcodefile);
				imagecolorset($imcode, 0 ,mt_rand(50, 255), mt_rand(50, 128), mt_rand(50, 255));
				imagecopyresized($im, $imcode, $x, $y, 0, 0, $data[0] + mt_rand(0, 6) - 3, $data[1] + mt_rand(0, 6) - 3, $data[0], $data[1]);
			} else {
				$text_color = imagecolorallocate($im, mt_rand(50, 255), mt_rand(50, 128), mt_rand(50, 255));
				imagechar($im, 5, $x + 10, $y + 3, $word, $text_color);
			}
			$i++;
		}

		//设置背景
		if($bg){
			$linenums = mt_rand(10, 32);
			for($i=0; $i <= $linenums; $i++) {
				$linecolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				$linex = mt_rand(0, $w);
				$liney = mt_rand(0, $h);
				imageline($im, $linex, $liney, $linex + mt_rand(0, 4) - 2, $liney + mt_rand(0, 4) - 2, $linecolor);
			}

			for($i=0; $i <= ($w-1); $i++) {
				$pointcolor = imagecolorallocate($im, mt_rand(50, 255), mt_rand(50, 255), mt_rand(50, 255));
				imagesetpixel($im, mt_rand(0, $w), mt_rand(0, $h), $pointcolor);
			}
		}

		//设置边框
		if($boder){
			$bordercolor = imagecolorallocate($im , 150, 150, 150);
			imagerectangle($im, 0, 0, $w-1, $h-1, $bordercolor);
		}

		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
		exit;
	}

	//输出用于人为识别的简单验证码
	static function radomcodesimple($radomcode=null,$w=45,$h=20,$sessname="radomcode"){
		if(!isset($_SESSION)) func_initSession();
		if(empty($radomcode)) $radomcode=strings::getRandom(4,'num');

		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");

		$_SESSION[$sessname]=$radomcode;

		$im = @imagecreate($w, $h)
		or die("cann't create image stream!");
		$background_color = imagecolorallocate($im, 60, 60, 60);
		$text_color = imagecolorallocate($im, 255, 255, 255);
		imagestring($im, 4, 5, 2, $radomcode, $text_color);

		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
		exit;
	}

	/*********************************************/
	/* Fonction: ImageCreateFromBMP              */
	/* Author:   DHKold                          */
	/* Contact:  admin@dhkold.com                */
	/* Date:     The 15th of June 2005           */
	/* Version:  2.0B                            */
	/*********************************************/

	function ImageCreateFromBMP($filename)
	{
		//Ouverture du fichier en mode binaire
		if (! $f1 = fopen($filename,"rb")) return FALSE;

		//1 : Chargement des entetes FICHIER
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
		if ($FILE['file_type'] != 19778) return FALSE;

		//2 : Chargement des entetes BMP
		$BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
		$BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
		if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
		$BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
		$BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
		$BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
		$BMP['decal'] = 4-(4*$BMP['decal']);
		if ($BMP['decal'] == 4) $BMP['decal'] = 0;

		//3 : Chargement des couleurs de la palette
		$PALETTE = array();
		if ($BMP['colors'] < 16777216)
		{
			$PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
		}

		//4 : Creation de l'image
		$IMG = fread($f1,$BMP['size_bitmap']);
		$VIDE = chr(0);

		$res = imagecreatetruecolor($BMP['width'],$BMP['height']);
		$P = 0;
		$Y = $BMP['height']-1;
		while ($Y >= 0)
		{
			$X=0;
			while ($X < $BMP['width'])
			{
				if ($BMP['bits_per_pixel'] == 24)
				$COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
				elseif ($BMP['bits_per_pixel'] == 16)
				{
					$COLOR = unpack("n",substr($IMG,$P,2));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 8)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 4)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				elseif ($BMP['bits_per_pixel'] == 1)
				{
					$COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
					if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
					elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
					elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
					elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
					elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
					elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
					elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
					elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
					$COLOR[1] = $PALETTE[$COLOR[1]+1];
				}
				else
				return FALSE;
				imagesetpixel($res,$X,$Y,$COLOR[1]);
				$X++;
				$P += $BMP['bytes_per_pixel'];
			}
			$Y--;
			$P+=$BMP['decal'];
		}

		//Fermeture du fichier
		fclose($f1);

		return $res;
	}

	/**
	 * google api 二维码生成【QRcode可以存储最多4296个字母数字类型的任意文本，具体可以查看二维码数据格式】
	 * @param string $data 二维码包含的信息，可以是数字、字符、二进制信息、汉字。不能混合数据类型，数据必须经过UTF-8 URL-encoded.如果需要传递的信息超过2K个字节，请使用POST方式
	 * @param int $widhtHeight 生成二维码的尺寸设置
	 * @param string $EC_level 可选纠错级别，QR码支持四个等级纠错，用来恢复丢失的、读错的、模糊的、数据。
	 *                         L-默认：可以识别已损失的7%的数据
	 *                         M-可以识别已损失15%的数据
	 *                         Q-可以识别已损失25%的数据
	 *                         H-可以识别已损失30%的数据
	 * @param int $margin 生成的二维码离图片边框的距离
	 */
	static function QRCode($data,$widhtHeight='150',$EC_level='L',$margin='0'){
		$url=urlencode($data);
		return 'http://chart.apis.google.com/chart?chs='.$widhtHeight.'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$data;
	}
}
?>