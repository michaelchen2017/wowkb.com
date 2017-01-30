//--------------------------AJAX function-----------------------------
var http_request = false;
var mainzt=0;
//转换xml特殊字符
function wwq_rCode(s,a,b,i){//s:原始字符串  a：目标字符 b：替换字符 i：是否区分大小写
	a = a.replace("?","\\?");
	if (i==null)
	{

		var r = new RegExp(a,"gi");
	}else if (i) {
		var r = new RegExp(a,"g");
	}
	else{
		var r = new RegExp(a,"gi");
	}
	return s.replace(r,b); 
}
function doxml_str(str){
if (str!=""){
	str = wwq_rCode(str,"&lt;", "<");									
	str = wwq_rCode(str,"&gt;",">");
	str = wwq_rCode(str,"&amp;","&");
	str = wwq_rCode(str,"&quot;","\"");
	str = wwq_rCode(str,"&apos;","'");
}else{
	str="(空)";
}
return str;
}

//获取xml文件
function getmyxml(url) {
	http_request = false;

	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			http_request.overrideMimeType('text/xml');
		}
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}

	if (!http_request) {
		window.alert('浏览器无法正确获得XML对象！');
		return false;
	}
	http_request.onreadystatechange = writeContents;
	http_request.open('GET', url, true);
	http_request.send(null);

}

//输出数据
function writeContents() {      
	if (http_request.readyState == 4) {
		if (http_request.status == 200) {
			var xmlDocument = http_request.responseXML; 
			var id = xmlDocument.getElementsByTagName('id').item(0).firstChild.data;
			var name = xmlDocument.getElementsByTagName('name').item(0).firstChild.data;
			
			//针对没有登录的情况
			if(id=="0"){
				alert(name);
			}else{
				var id_arr=id.split("|");
				var name_arr=name.split("|");
				if(id_arr.length>1){
				var template='<select id="selectM" style="WIDTH: 100%;" onChange="fillinput()">\n';
				      template+='<option value="'+id_arr[1]+'|'+name_arr[1]+'" >'+name_arr[1]+'</option>\n';
					  template+='<option value="'+id_arr[1]+'|'+name_arr[1]+'">-------------------</option>\n';
				for(i=1;i<id_arr.length;i++){
					  template+='<option value="'+id_arr[i]+'|'+name_arr[i]+'" >'+name_arr[i]+'</option>\n';
				}
				template+='</select>\n';
	            document.getElementById('manufacturer').innerHTML = template;
				}else{
				document.getElementById('manufacturer').innerHTML = '';
				}				
			}
			mainzt=0;			
		} else {
			window.alert('获得XML对象时浏览器出错，请检查您的本地浏览器设置！');
		}
	}
}
//检查AJAX调用的类别
function checkhelp(str){
	if(str!=''){
	var lb=document.getElementById('selectLB').options[document.getElementById('selectLB').selectedIndex].value;
	if(lb==1&&mainzt==0){getmyxml( FCKConfig.UserConfigXMLpath+'?key='+encodeURI(str));mainzt=1;}
	}
}
//操作辅助选项时更新表单数据
function fillinput(){
	var arr=document.getElementById('selectM').options[document.getElementById('selectM').selectedIndex].value.split("|");
	document.getElementById('txtLinkTitle').value=arr[1];
	document.getElementById('txtLinkValue').value=arr[0];
	document.getElementById('manufacturer').innerHTML = '';
}
//其他选项显示判断
function checklb(){
	var str=document.getElementById('selectLB').options[document.getElementById('selectLB').selectedIndex].value;
	if(str!=1){document.getElementById('manufacturer').innerHTML = '';}
}
//-----------------------------------------------------------------------------