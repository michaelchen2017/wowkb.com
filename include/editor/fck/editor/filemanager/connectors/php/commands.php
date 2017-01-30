<?php
/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2009 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * This is the File Manager Connector for PHP.
 */

function GetFolders( $resourceType, $currentFolder )
{
	// Map the virtual path to the local server path.
	$sServerDir = ServerMapFolder( $resourceType, $currentFolder, 'GetFolders' ) ;

	// Array that will hold the folders names.
	$aFolders	= array() ;

	$oCurrentFolder = @opendir( $sServerDir ) ;

	if ($oCurrentFolder !== false)
	{
		while ( $sFile = readdir( $oCurrentFolder ) )
		{
			if ( $sFile != '.' && $sFile != '..' && is_dir( $sServerDir . $sFile ) )
				$aFolders[] = '<Folder name="' . ConvertToXmlAttribute( $sFile ) . '" />' ;
		}
		closedir( $oCurrentFolder ) ;
	}

	// Open the "Folders" node.
	echo "<Folders>" ;

	natcasesort( $aFolders ) ;
	foreach ( $aFolders as $sFolder )
		echo $sFolder ;

	// Close the "Folders" node.
	echo "</Folders>" ;
}

function GetFoldersAndFiles( $resourceType, $currentFolder )
{
	// Map the virtual path to the local server path.
	$sServerDir = ServerMapFolder( $resourceType, $currentFolder, 'GetFoldersAndFiles' ) ;

	// Arrays that will hold the folders and files names.
	$aFolders	= array() ;
	$aFiles		= array() ;

	$oCurrentFolder = @opendir( $sServerDir ) ;

	if ($oCurrentFolder !== false)
	{
		while ( $sFile = readdir( $oCurrentFolder ) )
		{
			if ( $sFile != '.' && $sFile != '..' )
			{
				if ( is_dir( $sServerDir . $sFile ) )
					$aFolders[] = '<Folder name="' . ConvertToXmlAttribute( $sFile ) . '" />' ;
				else
				{
					$iFileSize = @filesize( $sServerDir . $sFile ) ;
					if ( !$iFileSize ) {
						$iFileSize = 0 ;
					}
					if ( $iFileSize > 0 )
					{
						$iFileSize = round( $iFileSize / 1024 ) ;
						if ( $iFileSize < 1 )
							$iFileSize = 1 ;
					}

					$aFiles[] = '<File name="' . ConvertToXmlAttribute( $sFile ) . '" size="' . $iFileSize . '" />' ;
				}
			}
		}
		closedir( $oCurrentFolder ) ;
	}

	// Send the folders
	natcasesort( $aFolders ) ;
	echo '<Folders>' ;

	foreach ( $aFolders as $sFolder )
		echo $sFolder ;

	echo '</Folders>' ;

	// Send the files
	natcasesort( $aFiles ) ;
	echo '<Files>' ;

	foreach ( $aFiles as $sFiles )
		echo $sFiles ;

	echo '</Files>' ;
}

function CreateFolder( $resourceType, $currentFolder )
{
	if (!isset($_GET)) {
		global $_GET;
	}
	$sErrorNumber	= '0' ;
	$sErrorMsg		= '' ;

	if ( isset( $_GET['NewFolderName'] ) )
	{
		$sNewFolderName = $_GET['NewFolderName'] ;
		$sNewFolderName = SanitizeFolderName( $sNewFolderName ) ;

		if ( strpos( $sNewFolderName, '..' ) !== FALSE )
			$sErrorNumber = '102' ;		// Invalid folder name.
		else
		{
			// Map the virtual path to the local server path of the current folder.
			$sServerDir = ServerMapFolder( $resourceType, $currentFolder, 'CreateFolder' ) ;

			if ( is_writable( $sServerDir ) )
			{
				$sServerDir .= $sNewFolderName ;

				$sErrorMsg = CreateServerFolder( $sServerDir ) ;

				switch ( $sErrorMsg )
				{
					case '' :
						$sErrorNumber = '0' ;
						break ;
					case 'Invalid argument' :
					case 'No such file or directory' :
						$sErrorNumber = '102' ;		// Path too long.
						break ;
					default :
						$sErrorNumber = '110' ;
						break ;
				}
			}
			else
				$sErrorNumber = '103' ;
		}
	}
	else
		$sErrorNumber = '102' ;

	// Create the "Error" node.
	echo '<Error number="' . $sErrorNumber . '" />' ;
}

function FileUpload( $resourceType, $currentFolder, $sCommand )
{
	if (!isset($_FILES)) {
		global $_FILES;
	}
	$sErrorNumber = '0' ;
	$sFileName = '' ;

	if ( isset( $_FILES['NewFile'] ) && !is_null( $_FILES['NewFile']['tmp_name'] ) )
	{
		global $Config ;
		
		$oFile = $_FILES['NewFile'] ;

		// Map the virtual path to the local server path.
		// $sServerDir = ServerMapFolder( $resourceType, $currentFolder, $sCommand ) ;
		
		// 使用本地路径
		$sServerDir = getUploadParentFolder();
		
		// Get the extension.
		$sExtension = substr( $oFile['name'], ( strrpos($oFile['name'], '.') + 1 ) ) ;
		$sExtension = strtolower( $sExtension ) ;
		
		// Get the uploaded file name.
		$sFileName = getUploadFileName($oFile['name'],$sExtension) ;
		$sFileName = SanitizeFileName( $sFileName ) ;

		$sOriginalFileName = $sFileName ;

		if ( isset( $Config['SecureImageUploads'] ) )
		{
			if ( ( $isImageValid = IsImageValid( $oFile['tmp_name'], $sExtension ) ) === false )
			{
				$sErrorNumber = '202' ;
			}
		}

		if ( isset( $Config['HtmlExtensions'] ) )
		{
			if ( !IsHtmlExtension( $sExtension, $Config['HtmlExtensions'] ) &&
				( $detectHtml = DetectHtml( $oFile['tmp_name'] ) ) === true )
			{
				$sErrorNumber = '202' ;
			}
		}

		// Check if it is an allowed extension.
		if ( !$sErrorNumber && IsAllowedExt( $sExtension, $resourceType ) )
		{
			$iCounter = 0 ;

			while ( true )
			{
				$sFilePath = $sServerDir . $sFileName ;

				if ( is_file( $sFilePath ) )
				{
					$iCounter++ ;
					$sFileName = RemoveExtension( $sOriginalFileName ) . '(' . $iCounter . ').' . $sExtension ;
					$sErrorNumber = '201' ;
				}
				else
				{
					upload::move_uploaded_file( $oFile['tmp_name'], $sFilePath ) ;

					if ( is_file( $sFilePath ) )
					{
						if ( isset( $Config['ChmodOnUpload'] ) && !$Config['ChmodOnUpload'] )
						{
							break ;
						}

						$permissions = 0777;

						if ( isset( $Config['ChmodOnUpload'] ) && $Config['ChmodOnUpload'] )
						{
							$permissions = $Config['ChmodOnUpload'] ;
						}

						$oldumask = umask(0) ;
						chmod( $sFilePath, $permissions ) ;
						umask( $oldumask ) ;
					}

					break ;
				}
			}

			if ( file_exists( $sFilePath ) )
			{
				//previous checks failed, try once again
				if ( isset( $isImageValid ) && $isImageValid === -1 && IsImageValid( $sFilePath, $sExtension ) === false )
				{
					@unlink( $sFilePath ) ;
					$sErrorNumber = '202' ;
				}
				else if ( isset( $detectHtml ) && $detectHtml === -1 && DetectHtml( $sFilePath ) === true )
				{
					@unlink( $sFilePath ) ;
					$sErrorNumber = '202' ;
				}
			}
		}
		else
			$sErrorNumber = '202' ;
	}
	else
		$sErrorNumber = '202' ;


	$sFileUrl = CombinePaths( GetResourceTypePath( $resourceType, $sCommand ) , $currentFolder ) ;
	$sFileUrl = CombinePaths( $sFileUrl, $sFileName ) ;

	//使用数据库记录上传文件信息
	RecordUploadFile( $sErrorNumber, $sFileUrl, $sFileName ,$oFile) ;
	SendUploadResults( $sErrorNumber, $sFileUrl, $sFileName ) ;

	exit ;
}

function getUploadFileName($oFileName,$sExtension){
	if(preg_match("/^[A-Za-z0-9-_.]+$/",$oFileName)){
		return $oFileName;
	}else{
		$nFileName = time().'.'.$sExtension;
		return $nFileName;
	}
}

//设置上传目录
function getUploadParentFolder(){
	global $Config;
	
	$path = DOCUROOT.$Config['UserFilesPath'];
	
	if(!is_dir($path)) {
		files::mkdirs($path);
	}
	
	//如果上传目录不是以 “/“ 结尾，此处补上
	if(substr($path,strlen($path)-1)!='/')$path=$path.'/';
	
	return $path;
}

//记录上传文件 
function RecordUploadFile($sErrorNumber, $sFileUrl, $sFileName,$oFile){
	//上传错误的情况不进行记录
	if( $sErrorNumber=='202' ) return;
	
	global $Config;
	
	//按文件类别，指定记录存储时调用的数据库
	$cate=$Config['UploadParam']['cate'];
	$conf=conf('article.editorConf','db');
	$db=isset($conf[$cate])?$conf[$cate]:$cate; 
		
	$conn=func_getDB($db);
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
		'ext'=>strtolower(files::getExt($sFileName)),		
		'temp'=>($Config['UploadParam']['temp']=='noValue')?null:$Config['UploadParam']['temp'],
	);
	
	
	
	$id=$conn->Insert($data,'upload');
	$conn->Update(array('order'=>$id),array('id'=>$id),'upload');
}
?>