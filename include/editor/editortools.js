var editortools={
    doadd:function () {
		var code = editor.Instance.GetHTML(true);
		code =  code.replace(/<br \/>/ig,"<br /><br />");
		editor.Instance.SetHTML(code);
     },
    doclear:function () {
		var code = editor.Instance.GetHTML(true);
		 
		code = this.striptag('span',code);
		code = this.striptag('div',code);
		code = this.striptag('p',code);
		code = this.dodel(code);
		
		editor.Instance.SetHTML(code);
     },
    striptag:function (tag,code){
		var startRule =new RegExp("<"+tag+"[^>]*"+">","ig"); 
		var endRule =new RegExp("</"+tag+">","ig"); 
		
		code =  code.replace(startRule,"");
		code =  code.replace(endRule,"<br />");
		
		return code;
     },
    dodel:function(code){
		code =  code.replace(/\&nbsp;<br \/>\n/ig,"<br />");
		code =  code.replace(/<br \/>\n/ig,"<br />");
		        
		code =  code.replace(/<br \/><br \/><br \/><br \/><br \/><br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/><br \/><br \/><br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/><br \/><br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/><br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/><br \/>/ig,"<br />");
		code =  code.replace(/<br \/><br \/>/ig,"<br />");
		
		code =  code.replace(/<br \/>/ig,"<br />\n");
        
		return code;    
     }
 } 