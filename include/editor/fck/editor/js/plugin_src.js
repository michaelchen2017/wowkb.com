/**
 * FileName:plugin.js
 * 在fck编辑器已有功能基础上增加表情,上传音乐,上传视频,上传图片几个功能的支持
 * 具体功能与php开发框架内的相关模块绑定,与整个网站系统的编码风格一致
 * 
 * Created on 2011-10-5
 * By weiqi　wang<weiqi228@gmail.com>
 */
//==Face=================================================================
var FCKFaceCommand = function( type )
{
	this.Name = type ;
	this.Type = type ;

	var oWindow ;

	if ( FCKBrowserInfo.IsIE )
		oWindow = window ;
	else if ( FCK.ToolbarSet._IFrame )
		oWindow = FCKTools.GetElementWindow( FCK.ToolbarSet._IFrame ) ;
	else
		oWindow = window.parent ;

	this._Panel = new FCKPanel( oWindow ) ;
	this._Panel.AppendStyleSheet( FCKConfig.SkinEditorCSS ) ;
	this._Panel.MainNode.className = 'FCK_Panel' ;
	this._CreatePanelBody( this._Panel.Document, this._Panel.MainNode ) ;
	FCK.ToolbarSet.ToolbarItems.GetItem( this.Name ).RegisterPanel( this._Panel ) ;

	FCKTools.DisableSelection( this._Panel.Document.body ) ;
}

FCKFaceCommand.prototype.Execute = function( panelX, panelY, relElement )
{
	// Show the Color Panel at the desired position.
	this._Panel.Show( panelX, panelY, relElement ) ;
}

FCKFaceCommand.prototype.SetHTML = function( filename )
{
	var HTML = '<img src="'+FCKConfig.FaceBaseDir+'/'+filename+'.gif" border="0">';
	FCK.InsertHtml(HTML);
	
	FCK.Focus() ;
	FCK.Events.FireEvent( 'OnSelectionChange' ) ;
}

FCKFaceCommand.prototype.GetState = function()
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return FCK_TRISTATE_DISABLED ;
	return FCK_TRISTATE_OFF ;
}

function FCKFaceCommand_OnMouseOver()
{
	this.className = 'ColorSelected' ;
}

function FCKFaceCommand_OnMouseOut()
{
	this.className = 'ColorDeselected' ;
}

function FCKFaceCommand_OnClick( ev, command, filename )
{
	this.className = 'ColorDeselected' ;
	command.SetHTML(filename) ;
	command._Panel.Hide();
}

FCKFaceCommand.prototype._CreatePanelBody = function( targetDocument, targetDiv )
{
	function CreateSelectionDiv()
	{
		var oDiv = targetDocument.createElement( "DIV" ) ;
		oDiv.className = 'ColorDeselected' ;
		FCKTools.AddEventListenerEx( oDiv, 'mouseover', FCKFaceCommand_OnMouseOver ) ;
		FCKTools.AddEventListenerEx( oDiv, 'mouseout', FCKFaceCommand_OnMouseOut ) ;

		return oDiv ;
	}

	// Create the Table that will hold all faces.
	var oTable = targetDiv.appendChild( targetDocument.createElement( "TABLE" ) ) ;
	oTable.className = 'ForceBaseFont' ;		// Firefox 1.5 Bug.
	oTable.style.tableLayout = 'fixed' ;
	oTable.cellPadding = 0 ;
	oTable.cellSpacing = 0 ;
	oTable.border = 0 ;
	oTable.width = 180 ;

	// Create an array of all face pic based on the configuration file.
	var faceList = FCKConfig.FaceLists.toString().split(',') ;
	var facemsgList = FCKConfig.FaceMsgLists.toString().split(',') ;

	// Create the colors table based on the array.
	var iCounter = 0 ;
	while ( iCounter < faceList.length )
	{
		var oRow = oTable.insertRow(-1) ;

		for ( var i = 0 ; i < 5 ; i++, iCounter++ )
		{
			// The div will be created even if no more colors are available.
			// Extra divs will be hidden later in the code. (#1597)
			if ( iCounter < faceList.length )
			{
				var filename = faceList[iCounter] ;
				var filemsg  = facemsgList[iCounter] ;
			}

			oDiv = oRow.insertCell(-1).appendChild( CreateSelectionDiv() ) ;
			oDiv.innerHTML = '<div><img src="'+FCKConfig.FaceBaseDir+'s/'+filename+'.gif" alt="'+filemsg+'" title="'+filemsg+'"></div>' ;

			if ( iCounter >= faceList.length )
				oDiv.style.visibility = 'hidden' ;
			else
				FCKTools.AddEventListenerEx( oDiv, 'click', FCKFaceCommand_OnClick, [ this, filename ] ) ;
		}
	}

}

//==Picture=================================================================
var FCKPictureCommand = function( type )
{
	this.Name = type ;
	this.Type = type ;

	var oWindow ;

	if ( FCKBrowserInfo.IsIE )
		oWindow = window ;
	else if ( FCK.ToolbarSet._IFrame )
		oWindow = FCKTools.GetElementWindow( FCK.ToolbarSet._IFrame ) ;
	else
		oWindow = window.parent ;

	this._Panel = new FCKPanel( oWindow ) ;
	this._Panel.AppendStyleSheet( FCKConfig.SkinEditorCSS ) ;
	this._Panel.MainNode.className = 'FCK_Panel' ;
	this._CreatePanelBody( this._Panel.Document, this._Panel.MainNode ) ;
	FCK.ToolbarSet.ToolbarItems.GetItem( this.Name ).RegisterPanel( this._Panel ) ;
	
	//FCKTools.DisableSelection( this._Panel.Document.body ) ;
}

FCKPictureCommand.prototype.Execute = function( panelX, panelY, relElement )
{
	// Show the Color Panel at the desired position.
	this._Panel.Show( panelX, panelY, relElement ) ;
}

FCKPictureCommand.prototype.GetState = function()
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return FCK_TRISTATE_DISABLED ;
	return FCK_TRISTATE_OFF ;
}

FCKPictureCommand.prototype._CreatePanelBody = function( targetDocument, targetDiv )
{
	function LoadJquery(){
		var jq = targetDocument.createElement('script'); 
		jq.type = 'text/javascript'; 
		jq.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js';
		return jq;
	}
	
	function LoadPictureJs(){
		var js = targetDocument.createElement('script'); 
		js.type = 'text/javascript'; 
		js.src = '/include/editor/api/js/o.js';
		return js;
	}
	
	function LoadPictureCss(){
		var css = targetDocument.createElement('link'); 
		css.rel = 'stylesheet'; 
		css.type = 'text/css';
		css.href = '/include/editor/api/css/picture.css';
		css.media = 'all';
		return css;
	}
	
	function PictureBox()
	{
		var oDiv = targetDocument.createElement( "DIV" ) ;
		return oDiv ;
	}
	
	function GetConf(id){
		return parent.document.getElementById(FCK.Name+'___'+id).value;
	}
	
	// Create the Table that will hold video area.
	var oTable = targetDiv.appendChild( targetDocument.createElement( "TABLE" ) ) ;
	oTable.className = 'ForceBaseFont' ;		// Firefox 1.5 Bug.
	oTable.style.tableLayout = 'fixed' ;
	oTable.cellPadding = 0 ;
	oTable.cellSpacing = 0 ;
	oTable.border = 0 ;
	oTable.width = 328 ;

	var oCell = oTable.insertRow(-1).insertCell(-1) ;
	oCell.colSpan = 1 ;

	// load jquery & video js
	oCell.appendChild( LoadJquery() );
	oCell.appendChild( LoadPictureJs() );
	oCell.appendChild( LoadPictureCss() );
	
	// Create videobox.
	var oDiv = oCell.appendChild( PictureBox() ) ;
	
	var html='';
	
	html+='<div id="pictureBox">';
	
	html+='	  <form id="picture_upload_input" name="picture_upload_input" action="" style="display:block;" method="POST" enctype="multipart/form-data" target="picture_upload_targert">';
	html+='		<div id="select"><input type="file" size="12" name="picture" id="picture" class="smallbtn" onchange="pictureCore.addfile();"></div>';
	html+='		<div id="uploading"><img src="/images/loading/03.gif">文件上传中，请稍候...</div>';
	html+='	    <input type="hidden" value="" id="userID" name="userID"/>';
	html+='	    <input type="hidden" value="" id="NickName" name="NickName"/>';
	html+='	    <input type="hidden" value="' + GetConf('dataID') + '" id="dataID" name="dataID"/>';
	html+='	    <input type="hidden" value="' + GetConf('dataType') + '" id="dataType" name="dataType"/>';
	html+='	    <input type="hidden" value="' + GetConf('TempID') + '" id="TempID" name="TempID"/>';
	html+='	    <input type="hidden" value="" id="fileID" name="fileID"/>';
	html+='	    <input type="hidden" value="" id="filePathInfo" name="filePathInfo"/>';
	html+='		<input id="cancel" type="button" value="取消" class="smallbtn" onclick="pictureCore.cancel();">';
	html+='		<input id="confirm" type="button" value="确定" class="smallbtn" onclick="pictureCore.setHtml();">';
	html+='	  </form>';
	html+='	  <iframe name="picture_upload_targert" id="picture_upload_targert" style="display:none;" width="0" height="0" src="/include/plugins/blank.html" frameborder="0" ></iframe>';
	
	html+='		<div id="preview">暂无预览</div>';
	
	html+='		<div id="setup">对齐方式: ';
	html+='			<select name="picalign" id="picalign"><option value="default">默认</option><option value="left">居左</option><option value="middle">居中</option><option value="right">居右</option></select>';
	html+='			&nbsp;&nbsp;';
	html+='			图片大小: <select name="picsize" id="picsize"><option value="origin">原始大小</option><option value="auto">自动缩放</option></select><br>';
	html+='		</div>';
	
	html+='</div>';
	
	oDiv.innerHTML =html;
}

//==Video=================================================================
var FCKVideoCommand = function( type )
{
	this.Name = type ;
	this.Type = type ;

	var oWindow ;

	if ( FCKBrowserInfo.IsIE )
		oWindow = window ;
	else if ( FCK.ToolbarSet._IFrame )
		oWindow = FCKTools.GetElementWindow( FCK.ToolbarSet._IFrame ) ;
	else
		oWindow = window.parent ;

	this._Panel = new FCKPanel( oWindow ) ;
	this._Panel.AppendStyleSheet( FCKConfig.SkinEditorCSS ) ;
	this._Panel.MainNode.className = 'FCK_Panel' ;
	this._CreatePanelBody( this._Panel.Document, this._Panel.MainNode ) ;
	FCK.ToolbarSet.ToolbarItems.GetItem( this.Name ).RegisterPanel( this._Panel ) ;

	FCKTools.DisableSelection( this._Panel.Document.body ) ;
}

FCKVideoCommand.prototype.Execute = function( panelX, panelY, relElement )
{
	// Show the Color Panel at the desired position.
	this._Panel.Show( panelX, panelY, relElement ) ;
}

FCKVideoCommand.prototype.GetState = function()
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return FCK_TRISTATE_DISABLED ;
	return FCK_TRISTATE_OFF ;
}

FCKVideoCommand.prototype._CreatePanelBody = function( targetDocument, targetDiv )
{
	function LoadJquery(){
		var jq = targetDocument.createElement('script'); 
		jq.type = 'text/javascript'; 
		jq.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js';
		return jq;
	}
	
	function LoadVideoJs(){
		var js = targetDocument.createElement('script'); 
		js.type = 'text/javascript'; 
		js.src = '/include/editor/api/js/o.js';
		return js;
	}
	
	function LoadVideoCss(){
		var css = targetDocument.createElement('link'); 
		css.rel = 'stylesheet'; 
		css.type = 'text/css';
		css.href = '/include/editor/api/css/video.css';
		css.media = 'all';
		return css;
	}
	
	function VideoBox()
	{
		var oDiv = targetDocument.createElement( "DIV" ) ;
		return oDiv ;
	}
	
	// Create the Table that will hold video area.
	var oTable = targetDiv.appendChild( targetDocument.createElement( "TABLE" ) ) ;
	oTable.className = 'ForceBaseFont' ;		// Firefox 1.5 Bug.
	oTable.style.tableLayout = 'fixed' ;
	oTable.cellPadding = 0 ;
	oTable.cellSpacing = 0 ;
	oTable.border = 0 ;
	oTable.width = 246 ;

	var oCell = oTable.insertRow(-1).insertCell(-1) ;
	oCell.colSpan = 1 ;

	// load jquery & video js
	oCell.appendChild( LoadJquery() );
	oCell.appendChild( LoadVideoJs() );
	oCell.appendChild( LoadVideoCss() );
	
	// Create videobox.
	var oDiv = oCell.appendChild( VideoBox() ) ;
	
	var html='';
	
	html+='<div id="videoBox">';
	html+='<div class="box">';
	html+='<input id="video-w" type="text" value="420" maxlength="3" style="width:45px;">x';
	html+='<input id="video-h" type="text" value="360" maxlength="3" style="width:45px;">';
	html+='</div>';
	html+='<div class="box"><input id="video-url" type="text" value="" maxlength="124"></div>';
	html+='<div class="box">';
	html+='<input type="button" value="预览" class="smallbtn" onclick="videoCore.preview();">';
	html+='<input type="button" value="确定" class="smallbtn" onclick="videoCore.setHtml();">';
	html+='<input type="button" value="取消" class="smallbtn" onclick="videoCore.cancel();">';
	html+='</div>';
	html+='<p class="box">本站目前仅支持YouTube视频分享，如有不便，敬主谅解。</p>';
	html+='<iframe id="video-ifr" width="240" height="180" src="/include/editor/api/html/Video.html" frameborder="0"></iframe>';
	html+='</div>';
	
	oDiv.innerHTML =html;
}

//==Mp3=================================================================
var FCKMp3Command = function( type )
{
	this.Name = type ;
	this.Type = type ;

	var oWindow ;

	if ( FCKBrowserInfo.IsIE )
		oWindow = window ;
	else if ( FCK.ToolbarSet._IFrame )
		oWindow = FCKTools.GetElementWindow( FCK.ToolbarSet._IFrame ) ;
	else
		oWindow = window.parent ;

	this._Panel = new FCKPanel( oWindow ) ;
	this._Panel.AppendStyleSheet( FCKConfig.SkinEditorCSS ) ;
	this._Panel.MainNode.className = 'FCK_Panel' ;
	this._CreatePanelBody( this._Panel.Document, this._Panel.MainNode ) ;
	FCK.ToolbarSet.ToolbarItems.GetItem( this.Name ).RegisterPanel( this._Panel ) ;
	
	//FCKTools.DisableSelection( this._Panel.Document.body ) ;
}

FCKMp3Command.prototype.Execute = function( panelX, panelY, relElement )
{
	// Show the Color Panel at the desired position.
	this._Panel.Show( panelX, panelY, relElement ) ;
}

FCKMp3Command.prototype.GetState = function()
{
	if ( FCK.EditMode != FCK_EDITMODE_WYSIWYG )
		return FCK_TRISTATE_DISABLED ;
	return FCK_TRISTATE_OFF ;
}

FCKMp3Command.prototype._CreatePanelBody = function( targetDocument, targetDiv )
{
	function LoadJquery(){
		var jq = targetDocument.createElement('script'); 
		jq.type = 'text/javascript'; 
		jq.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js';
		return jq;
	}
	
	function LoadMediaJs(){
		var js = targetDocument.createElement('script'); 
		js.type = 'text/javascript'; 
		js.src = '/include/editor/api/js/o.js';
		return js;
	}
	
	function LoadMediaCss(){
		var css = targetDocument.createElement('link'); 
		css.rel = 'stylesheet'; 
		css.type = 'text/css';
		css.href = '/include/editor/api/css/media.css';
		css.media = 'all';
		return css;
	}
	
	function MusicBox()
	{
		var oDiv = targetDocument.createElement( "DIV" ) ;
		return oDiv ;
	}
	
	function GetConf(id){
		return parent.document.getElementById(FCK.Name+'___'+id).value;
	}
	
	// Create the Table that will hold video area.
	var oTable = targetDiv.appendChild( targetDocument.createElement( "TABLE" ) ) ;
	oTable.className = 'ForceBaseFont' ;		// Firefox 1.5 Bug.
	oTable.style.tableLayout = 'fixed' ;
	oTable.cellPadding = 0 ;
	oTable.cellSpacing = 0 ;
	oTable.border = 0 ;
	oTable.width = 328 ;

	var oCell = oTable.insertRow(-1).insertCell(-1) ;
	oCell.colSpan = 1 ;

	// load jquery & video js
	oCell.appendChild( LoadJquery() );
	oCell.appendChild( LoadMediaJs() );
	oCell.appendChild( LoadMediaCss() );
	
	// Create videobox.
	var oDiv = oCell.appendChild( MusicBox() ) ;
	
	var html='';
	html+='<div id="musicBox">';
	html+='	  <form id="music_upload_input" name="music_upload_input" action="" style="display:block;" method="POST" enctype="multipart/form-data" target="music_upload_targert">';
	html+='		<input id="autostart" name="autostart" type="checkbox" checked="checked"><span style="line-height:24px"> 页面加载时自动播放</span><br>';
	html+='		<div id="select"><input type="file" size="12" name="music" id="music" class="smallbtn" onchange="mediaCore.addfile();"></div>';
	html+='		<div id="uploading"><img src="/images/loading/03.gif">文件上传中，请稍候...</div>';
	html+='	    <input type="hidden" value="" id="userID" name="userID"/>';
	html+='	    <input type="hidden" value="" id="NickName" name="NickName"/>';
	html+='	    <input type="hidden" value="' + GetConf('dataID') + '" id="dataID" name="dataID"/>';
	html+='	    <input type="hidden" value="' + GetConf('dataType') + '" id="dataType" name="dataType"/>';
	html+='	    <input type="hidden" value="' + GetConf('TempID') + '" id="TempID" name="TempID"/>';
	html+='	    <input type="hidden" value="" id="fileID" name="fileID"/>';
	html+='	    <input type="hidden" value="" id="filePathInfo" name="filePathInfo"/>';
	html+='		<input id="cancel" type="button" value="取消" class="smallbtn" onclick="mediaCore.cancel();">';
	html+='		<input id="confirm" type="button" value="确定" class="smallbtn" onclick="mediaCore.setHtml();">';
	html+='	  </form>';
	
	html+='	  <iframe id="music-ifr" width="320" height="24" src="/include/editor/api/script/audio.php" frameborder="0"></iframe>';
	html+='	  <iframe name="music_upload_targert" id="music_upload_targert" style="display:none;" width="0" height="0" src="/include/plugins/blank.html" frameborder="0" ></iframe>';
	
	html+='</div>';
	
	oDiv.innerHTML =html;
}