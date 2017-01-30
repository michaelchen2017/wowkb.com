<?php
/**
 * smarty 注册函数，直接在模板中读取根据地域信息处理过的内容
 * 
 * {%geoFilterVal name="assign_name"  data=$somedata%}
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
 * 
 * @param array
 * @return string
 */
function smarty_function_geoFilterVal($params, &$smarty){
	if(empty($params['data'])) return;
	
	$geoinfo = func_initGeoInfo();
	
	$result = array();
	foreach($params['data'] as $key=>$val){
		//没有设置地区检查时直接返回
		if(empty($val['areacheck'])) {
			$result[$key] = $val;
			continue;
		}
		
		//在任何一个字段满足检查条件时返回
		foreach($geoinfo as $item){
			if(empty($item)) continue;
			if($val['areacheck']==$item) {
				$result[$key] = $val;
				break;
			}
		}
	}
	
	//向smarty输出$name = $result
	$smarty->assign($params['name'], $result);
}