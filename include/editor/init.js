var editor={
	id:'content',
	width:"100%",
	height:"100%",
	toolbar:"simple",
	
	userID:0,
	dataID:0,
	dataType:'',
	css:'', //加载应用程序定义的样式
	
	Instance:null,//编辑器对象
	toolbarVisible:true,
	
	$$:function(id){
		return document.getElementById(id);
	},
	
	init:function(){
		var html ='<input  id="'+this.id+'___Config" type="hidden" value="'+this.addConfig()+'"/>';
			html+='<iframe id="'+this.id+'___Frame"  src="/include/editor/fck/editor/fckeditor.html?InstanceName='+this.id+'&amp;Toolbar='+this.toolbar+'" width="'+this.width+'" height="'+this.height+'" frameborder="0" scrolling="no"></iframe>';
		
			html+='<input  id="'+this.id+'___userID" type="hidden" value="'+this.userID+'"/>';
			html+='<input  id="'+this.id+'___dataID" type="hidden" value="'+this.dataID+'"/>';
			html+='<input  id="'+this.id+'___dataType" type="hidden" value="'+this.dataType+'"/>';
			html+='<input  id="'+this.id+'___TempID" type="hidden" value=""/>';
			html+='<input  id="'+this.id+'___PanelStatus" type="hidden" value="0"/>';
			html+='<div id="'+this.id+'___Tools"></div>';
			
		document.write(html);
	},

	addConfig:function(){
		var val='';
		val +='SkinPath=/include/editor/api/skin/';
		val +='&amp;CustomConfigurationsPath=/include/editor/api/config/'+this.toolbar+'.js';
		
		return val;
	},
	
	OnComplete:function(editorInstance){
		this.Instance = editorInstance;
	},
	
	htmlMode:function(){
		if(this.toolbarVisible){
			this.__setPanelClose();
			this.hideAllPanel();
			this.Instance.SwitchEditMode();
			this.Instance.ToolbarSet.Collapse();
			this.toolbarVisible=false;
			
			var ifrdoc = this.__getIfrDoc( this.id+'___Frame' );
			ifrdoc.getElementById('xCollapsed').style.display='none';
		}
	},
	
	editorMode:function(){
		if(!this.toolbarVisible){
			this.Instance.SwitchEditMode();
			this.Instance.ToolbarSet.Expand();
			this.toolbarVisible=true;
		}
	},
	
	switchMode:function(){
		this.Instance.SwitchEditMode();
		
		if(this.toolbarVisible){
			this.Instance.ToolbarSet.Collapse();
			this.toolbarVisible=false;
			
			var ifrdoc = this.__getIfrDoc( this.id+'___Frame' );
			ifrdoc.getElementById('xCollapsed').style.display='none';
		}else{
			this.Instance.ToolbarSet.Expand();
			this.toolbarVisible=true;
		}
	},
	
	hideAllPanel:function(){
		var list = document.getElementsByTagName("iframe");
		for(var i=0;i<list.length;i++){
			if(list[i].src =='javascript:void(0)') list[i].style.width = list[i].style.height = '0px';
		}
	},
	
	__getIfrDoc:function(id) {
	    var rv = null;
	    if (this.$$(id).contentWindow.document){
	        // if contentDocument exists, W3C compliant (Mozilla)
	        rv = this.$$(id).contentWindow.document;
	    } else {
	        // IE
	        rv = document.frames[id].document;
	    }
	    return rv;
	},
	
	__setPanelClose:function(){
		this.$$(this.id+'___PanelStatus').value=0;
	}
}