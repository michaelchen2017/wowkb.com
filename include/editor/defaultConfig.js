//语言选项
FCKConfig.AutoDetectLanguage = false ;
FCKConfig.DefaultLanguage = "zh-cn" ;
FCKConfig.EnterMode = 'br' ;

//定义字体
FCKConfig.FontNames		= '宋体;黑体;隶书;楷体_GB2312;Arial;Comic Sans MS;Courier New;Tahoma;Times New Roman;Verdana' ;

//笑脸&图标
FCKConfig.SmileyPath	= '/images/editor/icon/' ;
FCKConfig.SmileyImages	= ['i01.gif','i02.gif','i03.gif','i04.gif','i05.gif',
						   'i06.gif','i07.gif','i08.gif','i09.gif','i10.gif',
						   'i11.gif','i12.gif','i13.gif','i14.gif','i15.gif',
						   'i16.gif','i17.gif','i18.gif',
						   '001.gif','002.gif','003.gif','004.gif','005.gif',
						   '006.gif','007.gif','008.gif','009.gif','010.gif',
						   '011.gif','012.gif','013.gif','014.gif','015.gif',
						   '016.gif','017.gif','018.gif','019.gif','020.gif',
						   '021.gif','022.gif','023.gif','024.gif','025.gif',
						   '026.gif','027.gif','028.gif','029.gif','030.gif',						   						   
						   ] ;
						   
//上传选项
FCKConfig.LinkBrowser = false ;
FCKConfig.LinkBrowserURL = "/include/editor/browser.php?type=link";

FCKConfig.ImageBrowser = false ;
FCKConfig.ImageBrowserURL = "/include/editor/browser.php?type=image";

FCKConfig.FlashBrowser = false ;
FCKConfig.FlashBrowserURL ="/include/editor/browser.php?type=flash";

FCKConfig.LinkUpload = true ;
FCKConfig.LinkUploadURL = "/include/editor/fck/editor/filemanager/connectors/php/upload.php?type=link";

FCKConfig.ImageUpload = true ;
FCKConfig.ImageUploadURL = "/include/editor/fck/editor/filemanager/connectors/php/upload.php?type=image";

FCKConfig.FlashUpload = true ;
FCKConfig.FlashUploadURL = "/include/editor/fck/editor/filemanager/connectors/php/upload.php?type=flash";

// 定义工具栏
FCKConfig.ToolbarSets["Article"] = 	[						  
	['Paste','PasteText','PasteWord','Image','Flash','Table','Rule','TextColor','FitWindow','Bold','Italic','Underline','-','OrderedList','UnorderedList','JustifyLeft','JustifyCenter','JustifyRight','Link','Unlink','PageBreak'],
	'/',
	['Style','FontFormat','FontName','FontSize']
] ;

FCKConfig.ToolbarSets["Blog"] = [										  
	['Bold','Italic','JustifyLeft','JustifyCenter','JustifyRight','TextColor','Undo','Redo','RemoveFormat','-','OrderedList','UnorderedList','Image','Flash','Table','Link','Unlink','-','FitWindow','Smiley']
] ;

FCKConfig.ToolbarSets["BBS"] = [
	['FontName','FontSize','-','Bold','Italic','Underline','StrikeThrough','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull','-','TextColor','BGColor','Link','Unlink','Image','Flash']
] ;

FCKConfig.ToolbarSets["t"] = [										  
	['Bold','Italic','JustifyLeft','JustifyCenter','JustifyRight','TextColor','Undo','Redo','RemoveFormat','-','OrderedList','UnorderedList','Image','Flash','Table','Link','Unlink']
] ;

FCKConfig.ToolbarSets["WiKi"] = [
	['Bold','Italic','Underline','-','OrderedList','UnorderedList'],
	['Outdent','Indent','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Cut','Copy','Paste','PasteWord'],
	['Link','Unlink','-','Image']
] ;

FCKConfig.UserConfigXMLpath="/include/editor/ajax.php";