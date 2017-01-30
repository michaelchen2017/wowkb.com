<?php
/**
 * smarty 注册函数，直接在模板中读取根据地域信息处理过的内容
 * 
 * {%geoFilterCode name="assign_name"  data=$somedata%}
 *  在smarty中正常调用经过地理信息过滤后的　$assign_name 变量
 * 
 * $geoinfo = Array
(
    [continent_code] => NA
    [country_code] => US
    [country_code3] => USA
    [country_name] => United States
    [region] => 
    [city] => 
    [postal_code] => 
    [latitude] => 38
    [longitude] => -97
    [dma_code] => 0
    [area_code] => 0
)


在代码中的定义示例：
[geocity:fremont,san jose,new york]<a href="somewher"><img src='...'></a>[/geocity]

 * 
 * @param array
 * @return string
 */
function smarty_function_geoFilterCode($params, &$smarty){
	if(empty($params['data'])) return;
	$code = $params['data'];
	
	$geoinfo = func_initGeoInfo();
	
	//选项及与geoinfo对应的键值
	$funcs = array(
		'country'=>'country_code',
		'state'=>'region',
		'city'=>'city',
		'dmacode'=>'dma_code'
	);
	
	//默认值
	$geoDefaultCode="";
	if(strstr($code,'[geodefault]')){
		$geoDefaultCode = strings::findMe($code, "[geodefault]", "[/geodefault]");
		if(!empty($geoDefaultCode)){
			$code = str_replace("[geodefault]{$geoDefaultCode}[/geodefault]", "", $code);
		}
	}
	
	//对所有可用选项遍历
	foreach($funcs as $func=>$geokey){
		
		if(!strstr($code,"[geo{$func}:")) continue;
		
		$value = empty($geoinfo[$geokey])?'':$geoinfo[$geokey];
		if(!empty($value)){
			$value = trim($value);
			$value = strtolower($value);
		}
		$contents = strings::findMeAll($code, "[geo{$func}:", "[/geo{$func}]");
		
		//对找到的所有内容遍历
		foreach($contents as $content){
			$pos = strpos($content,"]");
			if(empty($pos)) continue;//找不到定义符，定义不完整，停止执行
			
			$configstr = substr($content,0,$pos);//地域定义字符
			$adContent = substr($content,$pos+1);//广告内容
			
			$config = explode(',',$configstr);
			$trueContent = '';
			
			if(!empty($value)){
				//对所有定义值遍历
				foreach($config as $conf){
					if(empty($conf)) continue;
					$conf = trim($conf);
					$conf = strtolower($conf);
					
					if($value==$conf) {
						$trueContent = $adContent;//只有当前的地域信息与定义中的值相对应时记录广告内容
						break;//有一项对应上即可
					}
				}
			}
			
			//替换原始代码中的对应内容
			$code = str_replace("[geo{$func}:{$configstr}]{$adContent}[/geo{$func}]", $trueContent, $code);
			
		}
	}
	
	$code=trim($code);
	if(empty($code)) $code = $geoDefaultCode;
	
	if(isset($params['format'])){
		if($params['format']=='js'){
			$str = '';
			$tmp = explode("\n",$code);
			foreach($tmp as $line){
				if(!empty($line))$str.=' '.trim($line);
			}
			$code = str_replace("'", "\'", $str);
		}
	}
	
	//向smarty输出$name = $result
	$smarty->assign($params['name'], $code );
}