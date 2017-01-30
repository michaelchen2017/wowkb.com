<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Demo Of AudioPlayer</title>
		<script type="text/javascript" src="/js/audio-player/audio-player.js"></script>  
        <script type="text/javascript">  
            AudioPlayer.setup("/js/audio-player/player.swf", {  
                width: 320 ,
                initialvolume: 75,  
		        transparentpagebg: "yes",  
		        left: "000000",  
		        lefticon: "FFFFFF"  
            });  
        </script>
        
		<style>
			body{margin:0;padding:0;overflow:hidden;}
			p{margin:0;padding:0;}
			#nothing{width:298px;height:22px;line-height:22px;text-align:left;padding:0;font-size:12px;}
		</style> 
</head>
<body scroll="no">
<?php 
$url = empty($_GET['url'])? "" : trim($_GET['url']);
$username = empty($_GET['username'])? "unknown" : rawurldecode($_GET['username']);
$msg = empty($_GET['msg'])?"请浏览您要上传的音频文件，系统目前仅支持mp3格式":$_GET['msg'];
$autostart = empty($_GET['autostart'])?"yes":$_GET['autostart'];

if(!empty($url)){?>
<p id="audioplayer_1"></p>
<script type="text/javascript">  
    AudioPlayer.embed("audioplayer_1", {soundFile: "<?php echo $url;?>",titles:"User's Music",artists: "<?php echo "$username";?>",autostart: "<?php echo $autostart;?>"});  
</script>  
<?php }else{ ?>
	<div id="nothing"><?php echo $msg;?></div>	
<?php }?>
</body>
</html>