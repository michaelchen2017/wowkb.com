var editor={
	id:'content',
	
	Instance:null,//编辑器对象
	toolbarVisible:true,
	
	$$:function(id){
		return document.getElementById(id);
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