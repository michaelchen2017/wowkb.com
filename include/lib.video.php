<?php
class video{
	
	function init($link,$pic,$w,$h){
		if(empty($link)) return '';
		
		$w=empty($w)?480:$w;
		$h=empty($h)?400:$h;
		
		$ext=strtolower(files::getExt($link));
		if($ext=='flv') return $this->flv($link,$w,$h);
		
		return $this->swf($link,$w,$h);
	}

	private function flv($src,$pic,$w,$h){
		$html = '';
		
		$html.='<video width="'.$w.'" height="'.$h.'" id="player2" poster="'.$pic.'" controls="controls" preload="none">';
		$html.='	<source type="video/flv" src="'.$link.'" />';
		$html.='	<object width="640" height="360" type="application/x-shockwave-flash" data="/js/video/flashmediaelement.swf">'; 		
		$html.='		<param name="movie" value="/js/video/flashmediaelement.swf" />'; 
		$html.='		<param name="flashvars" value="controls=true&amp;file='.$link.'" />'; 		
		$html.='		<img src="'.$pic.'" width="640" height="360" alt="Here we are" title="No video playback capabilities" />';
		$html.='	</object>'; 	
		$html.='</video>';
		$html.='<script>';
		$html.="$('audio,video').mediaelementplayer({";
		$html.='	success: function(player, node) {';
		$html.='	}';
		$html.='});';
		$html.='</script>';
		
		return $html;
	}

	private function swf($src,$w,$h){
		$html = '';
		
		$html.='<object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" name="player" width="'.$w.'" height="'.$h.'">';
		$html.='	<param name="movie" value="'.$src.'" />';
		$html.='	<param name="allowfullscreen" value="true" />';
		$html.='	<param name="allowscriptaccess" value="always" />';
		$html.='	<param name="wmode" value="opaque" />';
		$html.='	<embed';
		$html.='		type="application/x-shockwave-flash"';
		$html.='		id="player2"';
		$html.='		name="player2"';
		$html.='		src="'.$src.'"';
		$html.='		width="'.$w.'"';
		$html.='		height="'.$h.'"';
		$html.='		allowscriptaccess="always"';
		$html.='		allowfullscreen="true"';
		$html.='		wmode="opaque"';
		$html.='	/>';
		$html.='</object>';
		
		return $html;
	}
}
?>