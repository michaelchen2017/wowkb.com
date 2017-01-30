<?php
/*
 * Created on 2008-4-5 By Weiqi
 * Date and Time
 * Update 06/11/26
 */

class times{
	/**
	 * data compare function
	 * dateDiff ($interval, $DateStart,$DateEnd)
	 * $interval:date unit (w/d/h/n/s)  $DateStart:timestamp, $DateEnd:timestamp
	 * func_DateDiff ("d", time(),time()+24*60*60) => 1 Days
	 */
	static public function dateDiff ($interval, $date1,$date2) {
		$timedifference = $date2 - $date1;
		switch ($interval) {
			case "w": $retval = bcdiv($timedifference ,604800); break;
			case "d": $retval = bcdiv( $timedifference,86400); break;
			case "h": $retval = bcdiv ($timedifference,3600); break;
			case "n": $retval = bcdiv( $timedifference,60); break;
			case "s": $retval = $timedifference; break;
		}
		return $retval;
	}

	/**
	 * date compute function
	 * dateAdd($string, $int, $timestamp)
	 * $interval:date unit (y/q/m/d/w/h/n/s/)  $number:int, $date:timestamp
	 * func_DateAdd(d, -1, time()) => yestoday
	 */
	static public function dateAdd($interval, $number, $timestamp) {
		$date_time_array = getdate($timestamp);

		$hours = $date_time_array["hours"];
		$minutes = $date_time_array["minutes"];
		$seconds = $date_time_array["seconds"];
		$month = $date_time_array["mon"];
		$day = $date_time_array["mday"];
		$year = $date_time_array["year"];

		//TODO 加减月份时自动处理上级关联
		switch ($interval) {
			case "y": $year +=$number; break;
			case "q": $month +=($number*3); break;
			case "m":
				$month +=$number;
				if($month>12){
					$year+=floor($month/12);
					$month = $month%12;
				}
				break;
			case "d": $day+=$number; break;
			case "w": $day+=($number*7); break;
			case "h": $hours+=$number; break;
			case "n": $minutes+=$number; break;
			case "s": $seconds+=$number; break;
		}
		$timestamp = mktime($hours,$minutes,$seconds,$month,$day,$year);
		return $timestamp;
	}

	/**
	 * 获取精确时间
	 * @return float
	 */
	static public function getMicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
	
	/**
	 * 获取易读时间
	 * 所有数据库中存储的时间都是国际0区标准时间
	 * @param 时间 $timestamp
	 */
	static public function getHumanDateTime($timestamp=null){
		if(empty($timestamp)) $timestamp = time();
		
		//当前时区的时刻,之后的计算可以不再考虑时区问题
		$now=times::getTime();
		$timestamp=times::getTime($timestamp);
		
		//今天零点时刻
		$zero = strtotime(date("Y/m/d 00:00:00",$now));
		
		$sign=date("A",$timestamp);
		
		//今天
		if( $timestamp > $zero ){
			return "今天 ".date("h:i",$timestamp)." ".$sign;
		}
		
		//昨天
		if($timestamp > $zero-24*3600){
			return "昨天 ".date("h:i",$timestamp)." ".$sign;
		}
		
		$y1=date('Y',$now);
		$y2=date('Y',$timestamp);
		if($y1!=$y2){
			$str=date("Y年m月d日 h:i",$timestamp);//其它年份
		}else{
			$str=date("m月d日 h:i",$timestamp);//今年
		}
		
		return $str;
		
	}

	/**
	 * 格式化日期
	 */
	static public function formatWeek($time=null,$pos=null){
		if(empty($time)) $time=times::getTime();
		if(empty($pos)) $pos=date('w',$time);
		$week=array('1'=>'一','2'=>'二','3'=>'三','4'=>'四','5'=>'五','6'=>'六','0'=>'日',);
		$val=empty($week[$pos])?'--':"星期".$week[$pos];
		return $val;
	}
	
	/**
	 * 格式化时间
	 * @param int $timestamp
	 */
	static public function formatTime($timestamp,$chineseFormat=false){
		$day = '';
		$hour = '';
		
		//超过一小时
		if($timestamp>3600){
			$hour = floor($timestamp/3600);
			$timestamp = $timestamp-$hour*3600;
			
			//超过一天
			if($hour>24){
				$day = floor($hour/24);
				$hour = $hour-$day*24;
			}
			
			if($hour<10) $hour="0".$hour;
		}
		
		$min = floor($timestamp/60);
		if($min<10) $min = "0".$min;
				
		$sec = $timestamp-$min*60;
		if($sec<10)$sec="0".$sec;
				
		if($chineseFormat){
			$datestr = "{$min}分{$sec}秒";
			if(!empty($hour)) $datestr = "{$hour}小时{$datestr}";
			if(!empty($day)) $datestr = "{$day}天 {$datestr}";
		}else{
			$datestr = "{$min}:{$sec}";
			if(!empty($hour)) $datestr = "{$hour}:{$datestr}";
			if(!empty($day)) $datestr = "{$day} {$datestr}";
		}
		
		return $datestr;
	}

	/**
	 * 根据时区设置返回本地化的时间戳
	 * 系统默认时区，由系统常量TIMEZONE定义
	 * 用户时区由 ，$_COOKIE['tz']定义
	 * @param int $timestamp
	 * @return int
	 */
	static public function getTime($timestamp=null, $keepnull=false){
		if(empty($timestamp)) {
			if($keepnull) return null;
			$timestamp=time();
		}
		
		static $localTimeOffset;
		if(empty($localTimeOffset)){
			$timezoneDefine = empty($_COOKIE['tz'])?TIMEZONE:$_COOKIE['tz'];
			try {
		        $localTimeZone = new DateTimeZone( $timezoneDefine );
		   }catch(Exception $e) {
		        $localTimeZone = new DateTimeZone( "America/Los_Angeles" );
		    }
			$localDateTimeObj = new DateTime("now",$localTimeZone);
			$localTimeOffset = $localTimeZone->getOffset($localDateTimeObj);
		}
		
		$timestamp = $timestamp + $localTimeOffset;
		return $timestamp;
	}
	
	/**
	 * 根据时区设置返回GMT0的时间戳
	 * 系统默认时区，由系统常量TIMEZONE定义
	 * 用户时区由 ，$_COOKIE['tz']定义
	 * @param int $timestamp
	 * @return int
	 */
	static public function getGMT($timestamp=null, $keepnull=false){
		if(empty($timestamp)) return time();
	
		static $localTimeOffset;
		if(empty($localTimeOffset)){
			$timezoneDefine = empty($_COOKIE['tz'])?TIMEZONE:$_COOKIE['tz'];
			try {
				$localTimeZone = new DateTimeZone( $timezoneDefine );
			}catch(Exception $e) {
				$localTimeZone = new DateTimeZone( "America/Los_Angeles" );
			}
			$localDateTimeObj = new DateTime("now",$localTimeZone);
			$localTimeOffset = $localTimeZone->getOffset($localDateTimeObj);
		}
	
		$timestamp = $timestamp - $localTimeOffset;
		return $timestamp;
	}
	
	/**
	 * 根据时区设置返回本地化的时间戳
	 * 系统默认时区，由系统常量TIMEZONE定义
	 * 用户时区由 ，$_COOKIE['tz']定义
	 * @param string $datetime
	 * @return int
	 */
	static public function getTimeStamp($datetime){
		$timestamp = strtotime($datetime);
		$timestamp = times::getTime($timestamp);
		return $timestamp;
	}
	
	/**
	 * 由秒计量当前时间
	 * @param int $timesec
	 */
	static public function setupTime($timesec){
		$d= round($timesec/86400, 0, PHP_ROUND_HALF_DOWN);
		$timesec= $timesec%86400;
		
		$h= round($timesec/3600, 0, PHP_ROUND_HALF_DOWN);
		$timesec= $timesec%3600;
		
		$m= round($timesec/60, 0, PHP_ROUND_HALF_DOWN);
		$timesec= $timesec%60;
		
		return array('day'=>$d,'hour'=>$h, 'min'=>$m , 'sec'=>$timesec);
	}

	/**
	 * 常用时区中文标注
	 *
	 * @return array
	 */
	static public function getTimeZone(){
		$tz=array(
			'UTC-1200'=>array('val'=>'-1200','cn'=>'国际换日线','cnarea'=>'西12区：安尼威土克、瓜甲兰','en'=>'','enarea'=>''),
			'UTC-1100'=>array('val'=>'-1100','cn'=>'萨摩亚','cnarea'=>'西11区：中途岛、萨摩亚群岛','en'=>'','enarea'=>''),
			'UTC-1000'=>array('val'=>'-1000','cn'=>'夏威夷','cnarea'=>'西10区：夏威夷','en'=>'HST','enarea'=>''),
			'UTC-0900'=>array('val'=>'-0900','cn'=>'阿拉斯加','cnarea'=>'西9区：阿拉斯加','en'=>'AKST','enarea'=>''),
			'UTC-0800'=>array('val'=>'-0800','cn'=>'太平洋时间','cnarea'=>'西8区：太平洋时间（美国和加拿大），蒂华纳','en'=>'PST','enarea'=>''),
			'UTC-0700'=>array('val'=>'-0700','cn'=>'美加山区','cnarea'=>'西7区：山区时间(美加)、亚利桑那','en'=>'MST','enarea'=>''),
			'UTC-0600'=>array('val'=>'-0600','cn'=>'加拿大中部','cnarea'=>'西6区：中部时间（美国和加拿大），特古西加尔巴，萨斯喀彻温省','en'=>'CST','enarea'=>''),
			'UTC-0500'=>array('val'=>'-0500','cn'=>'墨西哥','cnarea'=>'西5区：墨西哥城、塔克西卡帕','en'=>'','enarea'=>''),
			'UTC-0600'=>array('val'=>'-0600','cn'=>'南美洲太平洋','cnarea'=>'西6区：中部时间(美加)','en'=>'','enarea'=>''),
			'UTC-0500'=>array('val'=>'-0500','cn'=>'美加东部','cnarea'=>'西5区：东部时间（美国和加拿大）、波哥大、利马、基多','en'=>'EST','enarea'=>''),
			'UTC-0400'=>array('val'=>'-0400','cn'=>'南美洲西部','cnarea'=>'西4区：加拉卡斯、拉巴斯','en'=>'','enarea'=>''),
			'UTC-0400'=>array('val'=>'-0400','cn'=>'大西洋','cnarea'=>'西4区：大西洋时间（加拿大）','en'=>'AST','enarea'=>''),
			'UTC-0330'=>array('val'=>'-0330','cn'=>'纽芬兰','cnarea'=>'西3:30区：新岛(加拿大东岸)','en'=>'NST','enarea'=>''),
			'UTC-0300'=>array('val'=>'-0300','cn'=>'东南美洲','cnarea'=>'西3区：波西尼亚','en'=>'','enarea'=>''),
			'UTC-0300'=>array('val'=>'-0300','cn'=>'南美洲东部','cnarea'=>'西3区：布鲁诺斯爱丽斯、乔治城','en'=>'','enarea'=>''),
			'UTC-0200'=>array('val'=>'-0200','cn'=>'大西洋中部','cnarea'=>'西3区：大西洋中部','en'=>'','enarea'=>''),
			'UTC-0100'=>array('val'=>'-0100','cn'=>'亚速尔','cnarea'=>'西1区：亚速尔群岛，佛得角群岛','en'=>'','enarea'=>''),
			'UTC+0000'=>array('val'=>'+0000','cn'=>'英国夏令','cnarea'=>'格林威治时间、都柏林、爱丁堡、伦敦','en'=>'GMT','enarea'=>''),
			'UTC+0000'=>array('val'=>'+0000','cn'=>'格林威治标准','cnarea'=>'格林尼治平均时：伦敦，都柏林，爱丁堡，里斯本，卡萨布兰卡，蒙罗维亚','en'=>'','enarea'=>''),
			'UTC+0100'=>array('val'=>'+0100','cn'=>'罗马','cnarea'=>'东1区：阿姆斯特丹，伯尔尼，贝尔格莱德，布拉迪斯拉发，卢布尔雅那，布拉格，哥本哈根，马德里，巴黎，萨拉热窝，斯科普里，索非亚，华沙，萨格勒布','en'=>'GET','enarea'=>''),
			'UTC+0100'=>array('val'=>'+0100','cn'=>'中欧','cnarea'=>'东1区：布拉格, 华沙, 布达佩斯','en'=>'EET','enarea'=>''),
			'UTC+0100'=>array('val'=>'+0100','cn'=>'西欧','cnarea'=>'东1区：柏林、斯德哥尔摩、罗马、伯恩、布鲁赛尔、维也纳','en'=>'','enarea'=>''),
			'UTC+0200'=>array('val'=>'+0200','cn'=>'以色列','cnarea'=>'东2区：布加勒斯特，哈拉雷，比勒陀尼亚，赫尔辛基，里加，塔林，明斯克，以色列','en'=>'','enarea'=>''),
			'UTC+0200'=>array('val'=>'+0200','cn'=>'东欧','cnarea'=>'东2区：东欧','en'=>'','enarea'=>''),
			'UTC+0200'=>array('val'=>'+0200','cn'=>'埃及','cnarea'=>'东2区：开罗','en'=>'','enarea'=>''),
			'UTC+0200'=>array('val'=>'+0200','cn'=>'GFT','cnarea'=>'东2区：雅典、赫尔辛基、伊斯坦布尔','en'=>'','enarea'=>''),
			'UTC+0200'=>array('val'=>'+0200','cn'=>'南非','cnarea'=>'东2区：赫拉雷、皮托里','en'=>'','enarea'=>''),
			'UTC+0300'=>array('val'=>'+0300','cn'=>'沙乌地阿拉伯','cnarea'=>'东3区：巴格达、科威特、奈洛比(肯亚)、里雅德(沙乌地)','en'=>'','enarea'=>''),
			'UTC+0300'=>array('val'=>'+0300','cn'=>'俄罗斯','cnarea'=>'东3区：利雅得，莫斯科，圣彼得堡，伏尔加格勒，内罗毕','en'=>'','enarea'=>''),
			'UTC+0330'=>array('val'=>'+0330','cn'=>'伊朗','cnarea'=>'东3:30区：德黑兰','en'=>'','enarea'=>''),
			'UTC+0400'=>array('val'=>'+0400','cn'=>'阿拉伯','cnarea'=>'东4区：阿布扎比，马斯喀特，巴库、第比利斯、阿布达比(东阿拉伯)、莫斯凯、塔布理斯(乔治亚共和)','en'=>'','enarea'=>''),
			'UTC+0430'=>array('val'=>'+0430','cn'=>'阿富汗','cnarea'=>'东4:30区：喀布尔','en'=>'','enarea'=>''),
			'UTC+0500'=>array('val'=>'+0500','cn'=>'西亚','cnarea'=>'东5区：叶卡特琳堡、卡拉奇、塔什干、伊斯兰马巴德、克洛奇、伊卡特林堡','en'=>'','enarea'=>''),
			'UTC+0530'=>array('val'=>'+0530','cn'=>'印度','cnarea'=>'东5:30区：孟买，加尔各答，马德拉斯，新德里','en'=>'','enarea'=>''),
			'UTC+0600'=>array('val'=>'+0600','cn'=>'中亚','cnarea'=>'东6区：阿拉木图、科伦坡、阿马提、达卡','en'=>'','enarea'=>''),
			'UTC+0700'=>array('val'=>'+0700','cn'=>'曼谷','cnarea'=>'东7区：曼谷，河内，雅加达','en'=>'','enarea'=>''),
			'UTC+0800'=>array('val'=>'+0800','cn'=>'台北','cnarea'=>'东8区：北京、台湾、香港、新加坡','en'=>'CST','enarea'=>''),
			'UTC+0900'=>array('val'=>'+0900','cn'=>'东京','cnarea'=>'东9区：平壤，汉城，东京，大阪，札幌，雅库茨克','en'=>'JST','enarea'=>''),
			'UTC+0930'=>array('val'=>'+0930','cn'=>'澳洲中部','cnarea'=>'东9:30区：阿得莱德，达尔文','en'=>'ACST','enarea'=>''),
			'UTC+1000'=>array('val'=>'+1000','cn'=>'席德尼','cnarea'=>'东10区：布里斯本、墨尔本、席德尼','en'=>'AEST','enarea'=>''),
			'UTC+1000'=>array('val'=>'+1000','cn'=>'塔斯梅尼亚','cnarea'=>'东10区：霍巴特','en'=>'','enarea'=>''),
			'UTC+1000'=>array('val'=>'+1000','cn'=>'西太平洋','cnarea'=>'东10区：关岛，莫尔兹比港，霍巴特，堪培拉，悉尼（注：悉尼市今年8月27日起至明年3月夏令时采用东11区时间）','en'=>'','enarea'=>''),
			'UTC+1100'=>array('val'=>'+1100','cn'=>'太平洋中部','cnarea'=>'东11区：马加丹，所罗门群岛，新喀里多尼亚','en'=>'','enarea'=>''),
			'UTC+1200'=>array('val'=>'+1200','cn'=>'纽芬兰','cnarea'=>'西3:30区：威灵顿、奥克兰','en'=>'','enarea'=>''),
			'UTC+1200'=>array('val'=>'+1200','cn'=>'斐济','cnarea'=>' 东12区：奥克兰，惠灵顿，斐济，堪察加半岛，马绍尔群岛','en'=>'','enarea'=>''),
		);
		return $tz;
	}
}
?>