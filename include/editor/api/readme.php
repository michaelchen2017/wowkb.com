<?php
/**
 * 项目根目录：　/include/editor/
 * /include/editor/fck/editor/lang/zh-cn.js 152 行;加入与新插件有关的文字说明　// Plugins
 * ==================================================================================================================================================================
 * /include/editor/fck/editor/js/fckeditorcode_gecko.js 
 * 
 * 84行,
 * 		case 'Picture':B=new FCKPictureCommand('Picture');break;
 * 		case 'DlgPicture':B=new FCKDialogCommand('DlgPicture',FCKLang.PictureToolTip,'dialog/fck_picture.html',400,300);break;
 * 		case 'Video':B=new FCKVideoCommand('Video');break;
 * 		case 'DlgVideo':B=new FCKDialogCommand('DlgVideo',FCKLang.VideoToolTip,'dialog/fck_video.html',400,300);break;
 *		case 'Mp3':B=new FCKMp3Command('Mp3');
 *		case 'DlgMp3':B=new FCKDialogCommand('DlgMp3',FCKLang.Mp3ToolTip,'dialog/fck_music.html',400,150);break;
 *		case 'Face':B=new FCKFaceCommand('Face');break;
 * 
 * 97行  增加新的命令
 * 		case 'Picture':B=new FCKToolbarPanelButton('Picture',FCKLang.Picture,FCKLang.PictureToolTip,FCK_TOOLBARITEM_ICONTEXT,77);break;
 * 		case 'DlgPicture':B=new FCKToolbarButton('DlgPicture',FCKLang.Picture,FCKLang.PictureToolTip,null,false,true,77);break;
 * 		case 'Video':B=new FCKToolbarPanelButton('Video',FCKLang.Video,FCKLang.VideoToolTip,FCK_TOOLBARITEM_ICONTEXT,78);break;
 * 		case 'DlgVideo':B=new FCKToolbarButton('DlgVideo',FCKLang.Video,FCKLang.VideoToolTip,null,false,true,78);break;
 * 		case 'Mp3':B=new FCKToolbarPanelButton('Mp3',FCKLang.Mp3,FCKLang.Mp3ToolTip,FCK_TOOLBARITEM_ICONTEXT,79);break;
 * 		case 'DlgMp3':B=new FCKToolbarButton('DlgMp3',FCKLang.Mp3,FCKLang.Mp3ToolTip,null,false,true,79);break;
 * 		case 'Face':B=new FCKToolbarPanelButton('Face',FCKLang.Face,FCKLang.FaceToolTip,FCK_TOOLBARITEM_ICONTEXT,80);break;
 * 
 * 可选项：FCK_TOOLBARITEM_ONLYICON，可选的有FCK_TOOLBARITEM_ONLYTEXT和FCK_TOOLBARITEM_ICONTEXT
 * 
 * /include/editor/fck/editor/js/fckeditorcode_gecko.js 85行,修改FCKPanel_Window_OnBlur（）
 * 增加：
 * 		var panelStatusID=FCK.Name+'___PanelStatus';var cmd=(this._FCKToolbarPanelButton)?this._FCKToolbarPanelButton.CommandName:'None';parent.document.getElementById(panelStatusID).value=(cmd=='Picture'||cmd=='Video'||cmd=='Mp3')?1:0;
 * 		var panelStatusID=FCK.Name+'___PanelStatus';if(parent.document.getElementById(panelStatusID).value==1)return false;
 *
 * /include/editor/fck/editor/js/plugin.js中编辑新命令的具体内容
 * 
 * ==================================================================================================================================================================
 * /include/editor/fck/editor/js/fckeditorcode_ie.js 
 * 
 * 84行,
 * 		case 'DlgPicture':B=new FCKDialogCommand('DlgPicture',FCKLang.PictureToolTip,'dialog/fck_picture.html',400,300);break;
 * 		case 'DlgVideo':B=new FCKDialogCommand('DlgVideo',FCKLang.VideoToolTip,'dialog/fck_video.html',400,300);break;
 * 		case 'DlgMp3':B=new FCKDialogCommand('DlgMp3',FCKLang.Mp3ToolTip,'dialog/fck_music.html',400,150);break;
 *		case 'Face':B=new FCKFaceCommand('Face');break;
 *
 * 98行  增加新的命令
 * 		case 'DlgPicture':B=new FCKToolbarButton('DlgPicture',FCKLang.Picture,FCKLang.PictureToolTip,null,false,true,77);break;
 * 		case 'DlgVideo':B=new FCKToolbarButton('DlgVideo',FCKLang.Video,FCKLang.VideoToolTip,null,false,true,78);break;
 * 		case 'DlgMp3':B=new FCKToolbarButton('DlgMp3',FCKLang.Mp3,FCKLang.Mp3ToolTip,null,false,true,79);break;
 * 		case 'Face':B=new FCKToolbarPanelButton('Face',FCKLang.Face,FCKLang.FaceToolTip,FCK_TOOLBARITEM_ICONTEXT,80);
 * 
 * ==================================================================================================================================================================
 * /include/editor/api/skin/fck_strip.gif中为相关命令增加图标
 * 
 * 修改　/include/editor/fck/editor/fckdialog.html
 * 
 * 708增加
 * =========================================================================
 * 		var url =document.getElementById("frmMain").src;
		var checkfile = false;
		
		if( url.indexOf('fck_picture')!=-1 ) checkfile = true;
		if( url.indexOf('fck_video')!=-1 ) checkfile = true;
		if( url.indexOf('fck_music')!=-1 ) checkfile = true;
		
		if(checkfile){
			var doc = document.getElementById("frmMain").contentWindow;
			doc.Cancel();
		}	
 * =========================================================================
 * 
 * 创建：
 *   /include/editor/fck/editor/dialog/fck_picture.html
 *   /include/editor/fck/editor/dialog/fck_video.html
 *   /include/editor/fck/editor/dialog/fck_music.html
 *	
 */

