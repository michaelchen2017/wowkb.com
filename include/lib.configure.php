<?php
/**
 * 处理与配置信息相关的内容
 * 相关模板 /include/template/configure.html
 * 
 * @author weiqi
 */
class configure {
	
	/**
	 * 初始化html模板变量
	 * 
	 * @param array $config 用户录入数据
	 * @param array $default 默认配置
	 * 
	 * @return array $default 前台调用的模板变量
	 */
	public static function init( $config, $default ){
		if(!empty($default)){
			$config = configure::checkArray($config);
			
			foreach($default as $k=>$v){
				if(isset($config[$k]))$default[$k]['defaultValue'] = $config[$k];
	
				if($v['type']=='checkbox'){
					$tmpstr = isset($config[$k])?$config[$k]:$default[$k]['defaultValue'];
					$pools = explode(',', $tmpstr);
					foreach($v['init'] as $key=>$val){
						$default[$k]['init'][$key]['checked']=(in_array($val['value'],$pools))?true:false;
					}
				}
				if($v['type']=='select'){
					$value = isset($config[$k])?$config[$k]:$default[$k]['defaultValue'];
					foreach($v['init'] as $key=>$val){
						$default[$k]['init'][$key]['checked']=($val['value']==$value)?true:false;
					}
				}
			}
		}

		return $default;
	}
	
	/**
	 * 综合用户录入和默认配置获取最终配置信息
	 * 
	 * @param array $config 用户录入数据
	 * @param array $default 默认配置
	 * 
	 * @return array() $config 最终配置信息
	 */
	public static function getValue( $config, $default ){
		$config = configure::checkArray($config);
		
		if(!empty($default)){
			foreach($default as $k=>$v){
				if(!isset($config[$k])){
					if( $v['type']=='checkbox' ) 
						$config[$k]=explode(',',$v['defaultValue']);
					else
						$config[$k]=$v['defaultValue'];
				}
			}
		}
		
		return $config;
	}
	
	/**
	 * 处理$_POST过来的数据
	 * @param array $default 默认配置
	 * 
	 * @return string  $result
	 */
	public static function setValue( $default ){
		
		$result=array();
		
		if(!empty($_POST)){
			foreach($_POST as $k=>$v){
				if(substr($k,0,6)!='item__'||empty($v)) continue;
	
				$config=explode('__',$k);
				$result[$config[1]]=is_array($v)?implode(',',$v):$v;
			}
			
			/**
			 * 设置没有数据的项目值为空，这样再初始化时就不会调用配置文件的默认值
			 * 对于原来数据库中没有保存的新加配置选项，由于没有经过POST过程，仍会显示新加的默认值
			 */
			foreach($default as $k=>$v){
				if(empty($result[$k])) $result[$k]='';
			}
		}
		
		$result = serialize($result);
		return $result;
		
	}
	
	//检查数组变量，自动转换序列化数据
	public static function checkArray($config){
		if(empty($config)) return array();
		if(is_array($config)) return $config;
		if(is_string($config)) return unserialize($config);
		
		return array();
	}
	
}