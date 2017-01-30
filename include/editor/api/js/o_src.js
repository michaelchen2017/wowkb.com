//erwseretrdtrd//
var pictureCore ={
	type:"panel",	
		
	g:function(sid){
		return document.getElementById(sid);
	},		
	
	getEditorDoc:function(){
		var doc= (this.type=='panel') ? parent : parent.parent ;
		return doc;
	},
	
	init:function(){
		$("#dataID").val(this.GetConf("dataID"));
		$("#dataType").val(this.GetConf("dataType"));
		$("#TempID").val(this.GetConf("TempID"));
	},
	
	showDemo:function(userID,NickName,tmpID,w,h,fileID,filePathInfo){
		$("#userID").val(userID);
		$("#NickName").val(NickName);
		$("#fileID").val(fileID);
		$("#filePathInfo").val(filePathInfo);
		
		//设置临时ID,用于同步和自动保存
		var doc = this.getEditorDoc();
		var tmpIDName = doc.editor.Instance.Name+'___TempID';
		if( doc.document.getElementById(tmpIDName).value == "" ){
			doc.document.getElementById(tmpIDName).value = tmpID;
		}
		
		$("#uploading").css('display','none');
		$("#picture").val("");
		$("#select").css('display','block');
		$("#confirm").css('display','block');
		
		var style="height:180px;";
		if(h<180) style = "height:"+h+"px;";
		
		var html = '<img src="'+filePathInfo+'" style="'+style+'" onclick="pictureCore.setHtml();">';
		$("#preview").html(html);
	},
	
	setHtml:function(){
		var filePathInfo= $("#filePathInfo").val();
		var align= $("#picalign").val();
		
		//设置对齐
		var html = "";
		if(align=='default'){
			html='<img border="0" src="'+filePathInfo+'">';
		}else{
			html='<img border="0" src="'+filePathInfo+'" align="'+align+'">';
		}
		
		$("#fileID").val("");
		$("#filePathInfo").val("");
		
		this.cancel();
		var doc = this.getEditorDoc();
		doc.editor.Instance.InsertHtml( html );
		
		//关闭dialog
		if(this.type!="panel") parent.Cancel();
	},
	
	cancel:function(){
		$("#uploading").css('display','none');
		$("#picture").val("");
		$("#picalign").val("default");
		$("#picsize").val("auto");
		$("#select").css('display','block');
		$("#confirm").css('display','none');
		$("#preview").html("暂无预览");
		
		if($("#fileID").val()!='') this.delfile();
		
		if(this.type=='panel'){
			this.setPanelClose();		
			parent.editor.Instance.Focus();
			parent.editor.Instance.Events.FireEvent( 'OnSelectionChange' ) ;
		}
	},
	
	setPanelClose:function(){
		var idname = parent.editor.Instance.Name+'___PanelStatus';
		parent.document.getElementById(idname).value=0;
	},
	
	addfile:function(){
		
		$("#uploading").css('display','block');
		$("#select").css('display','none');
		
		if(!this.fileCheck()) return;
		
		var formObj = this.g("picture_upload_input");
		formObj.action="/album/script/upload.php?type=e";
		formObj.submit();
	},
	
	delfile:function(){
		$.ajax({
			type: "POST",
		    url: "/include/plugins/jquery.php?app=album,data&sess=true&func=del&args[]="+$("#fileID").val(),
	  	    data: {},
		    success:function( val ){ 
		    	eval(val);
		    	
		    	$("#fileID").val("");
				$("#filePathInfo").val("");
				
		    	if(res[0]==0){this.notice(0);return;}
		    }
		});
	},
	
	notice:function(n){
		$("#uploading").css('display','none');
		$("#picture").val("");
		$("#select").css('display','block');
		
		var msg='';
		if(n==0) msg="上传文件时，您必须是登录状态!\n您的当前的状态是没有登录或登录时间已经超时,请重新登录!";
		if(n==1) msg="很抱歉, 目前我们的系统不支持您上传的图片格式!";
		if(n==2) msg="服务器错误，上传文件过大!";
		if(n==3) msg="服务器错误，上传文件不合法!";
		if(n==500) msg="服务器发生错误，请与管理员联系!";
		
		alert(msg);
	},
	
	fileCheck:function(){
		var extensions='jpg, jpeg, png, gif';
		var ext = this.fileExt($("#picture").val());
		if(extensions.toUpperCase().indexOf(ext.toUpperCase()) == -1){
			this.fileMsg(ext,extensions);
			return false;
		}
		return true;
	},
	
	fileExt:function(filePath) { 
		fileName = ((filePath.indexOf('/') > -1) ? filePath.substring(filePath.lastIndexOf('/')+1,filePath.length) : filePath.substring(filePath.lastIndexOf('\\')+1,filePath.length));
		return fileName.substring(fileName.lastIndexOf('.')+1,fileName.length);
	},
	
	fileMsg:function(ext,extensions){
		$("#uploading").css('display','none');
		$("#picture").val("");
		$("#select").css('display', 'block');
		
		var msg = '当前文件类型: "'+ext+'" 不允许上传!\n只有以下类型的文件才被允许上传: ' + extensions + '\n请重新选择文件上传.';
		alert( msg );
	},
	
	GetConf:function(id){
		var doc = this.getEditorDoc();
		return doc.document.getElementById(doc.editor.Instance.Name+'___'+id).value;
	}
}
var videoCore ={
		type:"panel",
		
		getEditorDoc:function(){
			var doc= (this.type=='panel') ? parent : parent.parent ;
			return doc;
		},
		
		preview:function(){
			var url = this.getEmbedCode( $("#video-url").val() );
			if(!url)return;
			
			document.getElementById("video-ifr").src = url;
		},
		
		setHtml:function(){
			var w,h,url;
			
			url = this.getEmbedCode( $("#video-url").val() );
			if(!url)return;
			
			if($("#video-w").val()==''){
				w=420;
			}else{
				w=parseInt($("#video-w").val());
				if(w==0)w=420;
			}
			
			if($("#video-h").val()==''){
				h=360;
			}else{
				h=parseInt($("#video-h").val());
				if(h==0)h=360;
			}
			
			this.cancel();
			
			var doc = this.getEditorDoc();
			doc.editor.Instance.InsertHtml('<iframe width="'+w+'" height="'+h+'" src="'+url+'" frameborder="0" style="z-index:0;" scrolling="no" allowfullscreen></iframe>');
			
			var HTML = doc.editor.Instance.GetHTML(true);
			doc.editor.Instance.SetHTML(HTML);
			
			//关闭dialog
			if(this.type!="panel") parent.Cancel();
		},
		
		getEmbedCode:function(src){
			var url='';
		
			if( src==''){
				alert("请输入要分享的视频地址");
				$("#video-url").focus();
				return false;
			}
			
			if(((src.indexOf('http://www.youtube.com/')!=-1)||(src.indexOf('https://www.youtube.com/')!=-1)) && (src.indexOf('?v=')!=-1 || src.indexOf('&v=')!=-1)){
				var start = 0;
				if(src.indexOf('?v=')!=-1)start = src.indexOf('?v=')+3;
				if(src.indexOf('&v=')!=-1)start = src.indexOf('&v=')+3;
				var stop = src.indexOf('&',start);
				
				var token='';
				if(stop!=-1){
					token = src.substring(start,stop);
				}else{
					token = src.substring(start);
				}
				
				var tmp = "http://www.youtube.com/embed/";
				if(src.indexOf('https://www.youtube.com/')!=-1){
					tmp = "http://www.youtube.com/embed/";
				}
				url = tmp+token+"?rel=0";
			}
			
			if((src.indexOf('http://youtu.be/')!=-1)||(src.indexOf('https://youtu.be/')!=-1)){
				url = src.replace("youtu.be/","www.youtube.com/embed/")+"?rel=0";
			}
			
			if(url==''){
				alert("请输入正确的视频分享地址, \n您可以直接使用YouTube的分享代码或视频的URL地址,当做此处的视频分享地址\n本站目前仅支持YouTube视频分享, 如有不便, 敬主谅解");
				$("#video-url").focus();
				$("#video-url").select();
				return false;
			}
			
			url = url + "&wmode=transparent";
			
			return url;
		},
		
		cancel:function(){
			if(this.type=='panel'){
				this.setPanelClose();		
				parent.editor.Instance.Focus();
				parent.editor.Instance.Events.FireEvent( 'OnSelectionChange' ) ;
			}
		},
		
		setPanelClose:function(){
			$("#video-w").val('420');
			$("#video-h").val('360');
			$("#video-url").val("");
			document.getElementById("video-ifr").src = "/include/editor/api/html/Video.html";
			parent.document.getElementById(parent.editor.Instance.Name+'___PanelStatus').value=0;
		}
}
var mediaCore ={
	type:"panel",	
		
	g:function(sid){
		return document.getElementById(sid);
	},	
	
	getEditorDoc:function(){
		var doc= (this.type=='panel') ? parent : parent.parent ;
		return doc;
	},
	
	init:function(){
		$("#dataID").val(this.GetConf("dataID"));
		$("#dataType").val(this.GetConf("dataType"));
		$("#TempID").val(this.GetConf("TempID"));
	},
	
	showDemo:function(userID,NickName,tmpID,fileID,filePathInfo){
		$("#userID").val(userID);
		$("#NickName").val(NickName);
		$("#fileID").val(fileID);
		$("#filePathInfo").val(filePathInfo);
		
		//设置临时ID,用于同步和自动保存
		var doc = this.getEditorDoc();
		var tmpIDName = doc.editor.Instance.Name+'___TempID';
		if( doc.document.getElementById(tmpIDName).value == "" ){
			doc.document.getElementById(tmpIDName).value = tmpID;
		}
		
		$("#uploading").css('display','none');
		$("#music").val("");
		$("#select").css('display','block');
		$("#confirm").css('display','block');
		
		this.g('music-ifr').src='/include/editor/api/script/audio.php?uid='+userID+'&username='+NickName+'&url='+filePathInfo;
	},
	
	setHtml:function(){
		var uid= $("#userID").val();
		var username= $("#NickName").val();
		var url= $("#filePathInfo").val();
			
		$("#fileID").val("");
		$("#filePathInfo").val("");
		var autostart = (this.g("autostart").checked)?"yes":"no";
		
		this.cancel();
		var doc = this.getEditorDoc();
		doc.editor.Instance.InsertHtml('<iframe width="320" height="24" src="/include/editor/api/script/audio.php?uid='+uid+'&username='+username+'&url='+ url +'&autostart='+autostart+'" scrolling="no" frameborder="0"></iframe>');
		
		var HTML = doc.editor.Instance.GetHTML(true);
		doc.editor.Instance.SetHTML(HTML);
		
		//关闭dialog
		if(this.type!="panel") parent.Cancel();
	},
	
	cancel:function(){
		$("#uploading").css('display','none');
		$("#music").val("");
		$("#select").css('display','block');
		$("#confirm").css('display','none');
		this.g('music-ifr').src='/include/editor/api/script/audio.php';
		
		if($("#fileID").val()!='') this.delfile();
		
		if(this.type=='panel'){
			this.setPanelClose();		
			parent.editor.Instance.Focus();
			parent.editor.Instance.Events.FireEvent( 'OnSelectionChange' ) ;
		}
	},
	
	setPanelClose:function(){
		var idname = parent.editor.Instance.Name+'___PanelStatus';
		parent.document.getElementById(idname).value=0;
	},
	
	addfile:function(){
		
		$("#uploading").css('display','block');
		$("#select").css('display','none');
		
		if(!this.fileCheck()){
			this.notice(1);
			return;
		}
		
		var formObj = this.g("music_upload_input");
		formObj.action="/media/script/upload.php?type=e";
		formObj.submit();
	},
	
	delfile:function(){
		$.ajax({
			type: "POST",
		    url: "/include/plugins/jquery.php?app=media,data&sess=true&func=del&args[]="+$("#fileID").val(),
	  	    data: {},
		    success:function( val ){ 
		    	eval(val);
		    	
		    	$("#fileID").val("");
				$("#filePathInfo").val("");
				
		    	if(res[0]==0){mediaCore.notice(0);return;}
		    }
		});
	},
	
	notice:function(n){
		$("#uploading").css('display','none');
		$("#music").val("");
		$("#select").css('display','block');
		
		var msg='';
		if(n==0) msg="上传文件时,您必须是登录状态!\n您的当前的状态是没有登录或登录时间已经超时, 请重新登录!";
		if(n==1) msg="很抱歉,目前我们的系统仅支持mp3格式的音频文件!";
		if(n==2) msg="服务器错误, 上传文件过大!";
		if(n==3) msg="服务器错误, 上传文件不合法!";
		if(n==500) msg="服务器发生错误, 请与管理员联系!";
		
		alert(msg);
	},
	
	fileCheck:function(){
		var extensions='mp3';
		var ext = this.fileExt($("#music").val());
		if(extensions.toUpperCase().indexOf(ext.toUpperCase()) == -1){
			return false;
		}
		return true;
	},
	
	fileExt:function(filePath) { 
		fileName = ((filePath.indexOf('/') > -1) ? filePath.substring(filePath.lastIndexOf('/')+1,filePath.length) : filePath.substring(filePath.lastIndexOf('\\')+1,filePath.length));
		return fileName.substring(fileName.lastIndexOf('.')+1,fileName.length);
	},
	
	GetConf:function(id){
		var doc = this.getEditorDoc();
		return doc.document.getElementById(doc.editor.Instance.Name+'___'+id).value;
	}
}