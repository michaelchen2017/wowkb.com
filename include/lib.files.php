<?php

/*
 * FileName:class_file.php
 * Created on 2007-11-5
 * By weiqi<weiqi228@gmail.com>
 */

class files {
	/**
	 * 此文件使用重写方式间接访问
	 * 用于规范化处理对内部文件的请求，处理所有从服务器端通过php读取的下载请求
	 */
	public static $mimetypes = array(
		".*"=>"application/octet-stream",
		'ez' => 'application/andrew-inset',
		'hqx' => 'application/mac-binhex40',
		'cpt' => 'application/mac-compactpro',
		'doc' => 'application/msword',
		'bin' => 'application/octet-stream',
		'dms' => 'application/octet-stream',
		'lha' => 'application/octet-stream',
		'lzh' => 'application/octet-stream',
		'exe' => 'application/octet-stream',
		'class' => 'application/octet-stream',
		'so' => 'application/octet-stream',
		'dll' => 'application/octet-stream',
		'oda' => 'application/oda',
		'pdf' => 'application/pdf',
		'ai' => 'application/postscript',
		'eps' => 'application/postscript',
		'ps' => 'application/postscript',
		'smi' => 'application/smil',
		'smil' => 'application/smil',
		'mif' => 'application/vnd.mif',
		'xls' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'wbxml' => 'application/vnd.wap.wbxml',
		'wmlc' => 'application/vnd.wap.wmlc',
		'wmlsc' => 'application/vnd.wap.wmlscriptc',
		'bcpio' => 'application/x-bcpio',
		'vcd' => 'application/x-cdlink',
		'pgn' => 'application/x-chess-pgn',
		'cpio' => 'application/x-cpio',
		'csh' => 'application/x-csh',
		'dcr' => 'application/x-director',
		'dir' => 'application/x-director',
		'dxr' => 'application/x-director',
		'dvi' => 'application/x-dvi',
		'spl' => 'application/x-futuresplash',
		'gtar' => 'application/x-gtar',
		'hdf' => 'application/x-hdf',
		'js' => 'application/x-javascript',
		'skp' => 'application/x-koan',
		'skd' => 'application/x-koan',
		'skt' => 'application/x-koan',
		'skm' => 'application/x-koan',
		'latex' => 'application/x-latex',
		'nc' => 'application/x-netcdf',
		'cdf' => 'application/x-netcdf',
		'sh' => 'application/x-sh',
		'shar' => 'application/x-shar',
		'swf' => 'application/x-shockwave-flash',
		'sit' => 'application/x-stuffit',
		'sv4cpio' => 'application/x-sv4cpio',
		'sv4crc' => 'application/x-sv4crc',
		'tar' => 'application/x-tar',
		'tcl' => 'application/x-tcl',
		'tex' => 'application/x-tex',
		'texinfo' => 'application/x-texinfo',
		'texi' => 'application/x-texinfo',
		't' => 'application/x-troff',
		'tr' => 'application/x-troff',
		'roff' => 'application/x-troff',
		'man' => 'application/x-troff-man',
		'me' => 'application/x-troff-me',
		'ms' => 'application/x-troff-ms',
		'ustar' => 'application/x-ustar',
		'src' => 'application/x-wais-source',
		'xhtml' => 'application/xhtml+xml',
		'xht' => 'application/xhtml+xml',
		'zip' => 'application/zip',
		'au' => 'audio/basic',
		'snd' => 'audio/basic',
		'mid' => 'audio/midi',
		'midi' => 'audio/midi',
		'kar' => 'audio/midi',
		'mpga' => 'audio/mpeg',
		'mp2' => 'audio/mpeg',
		'mp3' => 'audio/mpeg',
		'aif' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'aifc' => 'audio/x-aiff',
		'm3u' => 'audio/x-mpegurl',
		'ram' => 'audio/x-pn-realaudio',
		'rm' => 'audio/x-pn-realaudio',
		'rpm' => 'audio/x-pn-realaudio-plugin',
		'ra' => 'audio/x-realaudio',
		'wav' => 'audio/x-wav',
		'pdb' => 'chemical/x-pdb',
		'xyz' => 'chemical/x-xyz',
		'bmp' => 'image/bmp',
		'gif' => 'image/gif',
		'ief' => 'image/ief',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpe' => 'image/jpeg',
		'png' => 'image/png',
		'tiff' => 'image/tiff',
		'tif' => 'image/tiff',
		'djvu' => 'image/vnd.djvu',
		'djv' => 'image/vnd.djvu',
		'wbmp' => 'image/vnd.wap.wbmp',
		'ras' => 'image/x-cmu-raster',
		'pnm' => 'image/x-portable-anymap',
		'pbm' => 'image/x-portable-bitmap',
		'pgm' => 'image/x-portable-graymap',
		'ppm' => 'image/x-portable-pixmap',
		'rgb' => 'image/x-rgb',
		'xbm' => 'image/x-xbitmap',
		'xpm' => 'image/x-xpixmap',
		'xwd' => 'image/x-xwindowdump',
		'igs' => 'model/iges',
		'iges' => 'model/iges',
		'msh' => 'model/mesh',
		'mesh' => 'model/mesh',
		'silo' => 'model/mesh',
		'wrl' => 'model/vrml',
		'vrml' => 'model/vrml',
		'css' => 'text/css',
		'html' => 'text/html',
		'htm' => 'text/html',
		'txt' => 'text/plain',
		'asc' => 'text/plain',
		'rtx' => 'text/richtext',
		'rtf' => 'text/rtf',
		'sgml' => 'text/sgml',
		'sgm' => 'text/sgml',
		'tsv' => 'text/tab-separated-values',
		'wml' => 'text/vnd.wap.wml',
		'wmls' => 'text/vnd.wap.wmlscript',
		'etx' => 'text/x-setext',
		'xsl' => 'text/xml',
		'xml' => 'text/xml',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'qt' => 'video/quicktime',
		'mov' => 'video/quicktime',
		'mxu' => 'video/vnd.mpegurl',
		'avi' => 'video/x-msvideo',
		'movie' => 'video/x-sgi-movie',
		'ice' => 'x-conference/x-cooltalk',
	);
	
	
	public static $mimeext=array(
		'application/x-rar'=>'rar',
		'application/zip'=>'zip',
		"application/pdf"=>'pdf',
	
		'image/jpeg'=>'jpg',
		'image/png'=>'png',
		'image/gif'=>'gif',
		'image/x-ms-bmp'=>'bmp',
		
		'text/plain'=>'txt',
		'application/msword'=>'doc',
		'application/vnd.ms-office'=>'office',
	);
	/**
	 * File system function
	 * Update 06/11/26
	 */
	// Creat folder
	public static function mkdirs($dir, $mode = 0777) {
		$stack = array (basename($dir));
		$path = null;
		while (($d = dirname($dir))) {
			if (!is_dir($d)) {
				$stack[] = basename($d);
				$dir = $d;
			} else {
				$path = $d;
				break;
			}
		}

		if (($path = realpath($path)) === false)
		return false;

		$created = array ();
		for ($n = count($stack) - 1; $n >= 0; $n--) {
			$s = $path . '/' . $stack[$n];
			if (!mkdir($s, $mode)) {
				for ($m = count($created) - 1; $m >= 0; $m--)
				rmdir($created[$m]);
				return false;
			}
			$created[] = $s;
			$path = $s;
		}
		return true;
	}
	/**
	 * Delete folder and it's subfolder
	 * @param string $dir
	 * @param bool $tag   if true:delete folder self, false:only delete it's sub folder and it's files
	 * @return bool
	 */
	/**
	 * 删除该目录下的所有文件及文件夹
	 * @author kokko<kokko313@gamil.com>
	 * @param string $dir:目录 比如c:\www1\ 为绝对路?
	 * @param string $tag:true:同时删除该目录，false:仅仅删除该目录下的文件及子目?
	 * @return bool
	 */
	public static function rmdirs($dir, $tag = false) {
		$dir=strtolower($dir);
		if(substr($dir,strlen($dir)-1)!="/") {$dir=$dir."/";}
		if(!is_dir($dir))	return false;

		//目标目录后的第一级必须存在
		$first = substr($dir, strlen(DOCUROOT)+1);
		$pos = strpos($first, "/", 0);
		if ($pos == 0)return false;
			
		//定义函数作用区域，避免误删除网站中的非数据目录;
		$parent=substr($first, 0,$pos);
		if( $parent!='data' && $parent!='cache' ) return false;

		//目标目录后的第二级目录必须存在
		$second = substr($first, $pos +1);
		$pos = strpos($second, "/", 0);
		if ($pos == 0 )return false;

		//目标目录后的第三级目录必须存在
		$third = substr($second, $pos +1);
		$pos = strpos($third, "/", 0);
		if ($pos == 0 )return false;

		if ($handle = @ opendir($dir)) {
			while (($file = @ readdir($handle)) !== false) {
				if ($file != "." && $file != "..") {
					$filepath = $dir . "/" . $file;
					if (is_file($filepath)) {
						@ unlink($filepath);
					}
					elseif (is_dir($filepath)) {
						files::rmdirs($filepath, true);
					}
				}
			}
			closedir($handle);
		}

		if ($tag) {
			@ rmdir($dir);
		}
		return true;
	}

	public static function _unlink($resource,$exp_time=null){
		if(isset($exp_time)) {
			if(time() - @filemtime($resource) >= $exp_time) {
				return @unlink($resource);
			}
		} else {
			return @unlink($resource);
		}
	}

	/**
	 * 取得字节数所对应的相关单位值
	 * @author weiqi<weiqi@eefocus.com>
	 * @param string $lenght
	 * @return string
	 */
	public static function setupSize($lenght) {
		$units = array (
				'B',
				'KB',
				'MB',
				'GB',
				'TB',
				'PB',
				'EB',
				'ZB',
				'YB'
				);
				foreach ($units as $unit) {
					if ($lenght > 1024)
					$lenght = round($lenght / 1024, 1);
					else
					break;
				}
				if (intval($lenght) == 0) {
					return ("0 Bytes");
				}
				return $lenght . ' ' . $unit;
	}
	
	/*
	 * 获取远程HTTP文件大小
	 * */
	public static function getHttpFileSize($url){
		$str = files::getHttpFileInfo($url,"Content-Length");
		if(!empty($str)) return files::setupSize($str);
	}
	
	/*
	 * 获取远程HTTP文件大小
	 * */
	public static function getHttpFileType($url){
		return files::getHttpFileInfo($url,"Content-Type");
	}
	
	/*
	 * 获取远程HTTP信息
	 * */
	public static function getHttpFileInfo($url,$type){
		$url = parse_url($url);
		if($fp = @fsockopen($url['host'],empty($url['port'])?80:$url['port'],$error)){
			fputs($fp,"GET ".(empty($url['path'])?'/':$url['path'])." HTTP/1.1\r\n");
			fputs($fp,"Host:$url[host]\r\n\r\n");
			while(!feof($fp)){
				$tmp = fgets($fp);
				if(trim($tmp) == ''){
					break;
				}else if(preg_match('/'.$type.':(.*)/si',$tmp,$arr)){
					return trim($arr[1]);
				}
			}
		}
	}

	/*
	 * 获取远程FTP文件大小
	 * */
	public static function getFtpFileSize($host,$file,$user='anonymous',$pass='anonymous'){
		$conn = ftp_connect($host);
		ftp_login( $conn,$user,$pass );
		$size = files::setupSize(ftp_size( $conn, $file ));
		ftp_close($conn);

		return $size;
	}
	
	/**
	 * 获取系统图片
	 * @param 唯一ID $id
	 * @param 图标类型 $type
	 * @param 默认值 $default
	 */
	public static function getImg($id,$type,$default="/images/space/nobody.gif"){
		
		$filename = '/upload/'.$type.'/'.Cache::getDeepFolder(md5($id.'O$y8'), 3).$id;
		
		if( is_file(DOCUROOT.$filename.'.gif') ) return $filename.'.gif';
		if( is_file(DOCUROOT.$filename.'.jpg') ) return $filename.'.jpg';
		if( is_file(DOCUROOT.$filename.'.png') ) return $filename.'.png';
		
		return $default;
	}
	
	/**
	 * 获得系统上传文件路径
	 * @param 唯一ID $id
	 * @param 图标类型 $type
	 */
	public static function getUploadPath($id,$type){
		$path = '/upload/'.$type.'/'.Cache::getDeepFolder(md5($id.'O$y8'), 3);
		return $path;
	}

	// Read folder size
	public static function folderSize($dir) {
		if (!preg_match('#/$#', $dir)) {
			$dir .= '/';
		}
		$totalsize = 0;
		foreach (files :: fileList($dir) as $name) {
			$totalsize += (@ is_dir($dir . $name) ? files :: folderSize("$dir$name/") : (int) @ filesize($dir .
			$name));
		}
		return $totalsize;
	}
	// 判断目录是否为空
	public static function isEmptyDir( $dir ){
		$dh=opendir($dir);
		while(false!==($f=readdir($dh))){
			if($f!="." && $f!=   ".." ){
				return false;
			}
		}
		return true;
	}

	// 删除空目录
	public static function delEmptyFolder( $dir ){exit;//有重大问题
	if( $handle = @opendir( $dir ) ){
		while( ( $file = @readdir( $handle ) ) !== false ){
			if( $file != "." && $file != ".." ){
				$filepath = $dir."/".$file;
				if(is_dir($filepath)){
					if(files::isEmptyDir($filepath)){
						while(files::isEmptyDir($filepath)){
							rmdir($filepath);echo $filepath;
							$filepath=dirname($filepath);
						}
					}else{
						files::delEmptyFolder($dir);
					}
				}
			}
		}
		closedir( $handle );
	}
	}

	// Get all files
	public static function fileAll($path) {
		$list = array ();
		if (($hndl = @ opendir($path)) === false) {
			return $list;
		}
		while (($file = readdir($hndl)) !== false) {
			if ($file != '.' && $file != '..') {
				$list[] = $file;
			}
		}
		closedir($hndl);
		return $list;
	}
	// Get files list
	public static function fileList($path) {
		if (!preg_match('#/$#', $path)) {
			$path .= '/';
		}
		$f = $d = array ();
		foreach (files :: fileAll($path) as $name) {
			if (@ is_dir($path . $name)) {
				$d[] = $name;
			} else
			if (@ is_file($path . $name)) {
				$f[] = $name;
			}
		}
		natcasesort($d);
		natcasesort($f);
		return array_merge($d, $f);
	}
	//无扩展名的目标保存名称
	public static function getCleanFilename($filename){
		$filename=basename($filename);
		$filename=substr($filename,0,strlen($filename)-strlen(strrchr($filename, '.')));
		return $filename;
	}

	//返回小写的文件扩展名
	public static function getExt($filename) {
		if (strstr($filename, "\\") || strstr($filename, "/")) {
			$filename = basename($filename);
		}
		if (strstr($filename, ".")) {
			$ext = mb_substr(strrchr($filename, '.'), 1);
			$ext = strtolower($ext);
		} else {
			$ext = false;
		}
		return $ext;
	}
	
	public static function getMimeExt($filename){
		//php version < 5.30
		$info = mime_content_type($filename);
		$type = isset(files::$mimeext[$info])?files::$mimeext[$info]:'unknown';
		
		return $type;
	}
	
	public static function getFolder($dir) {
		$folder = array ();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && substr($file, 0, 1) != ".") {
					if (is_dir($dir . $file)) {
						$folder[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return $folder;
	}
	public static function getFile($dir) {
		$files = array ();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && substr($file, 0, 1) != ".") {
					if (is_file($dir . $file)) {
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return $files;
	}
	/**
	 * Make files with some text character
	 * @param string $file_name
	 * @param string $content
	 * @return bool
	 */
	public static function makeFile($file_name, $content) {
		if (!file_exists($file_name)) {
			$fp = fopen($file_name, "w+");
		} else {
			$fp = fopen($file_name, "w");
		}
		if (!$fp) {
			return false;
		}

		if (!fwrite($fp, $content)) {
			fclose($fp);
			return false;
		} else {
			fclose($fp);
			return true;
		}
	}
	
	/**
	 * 获得缓存配置文件的存储位置
	 * 
	 * @param string $fileID
	 * @param string $fileType
	 */
	public static function getCacheFilePathID($fileID,$fileType){
		$cid=substr(md5($fileID),8,16).'_'.$fileID;
		$filename=DOCUROOT."/cache/{$fileType}/".Cache::getDeepFolder($cid,3).$cid;
		return $filename;
	}
}

?>