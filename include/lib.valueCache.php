<?php
/**
 * 使用文件系统或memcache缓存数组变量
 * 提供针对数组数据的标准读写操作
 * 
 * TODO 
 * 1、在 lib.valueCache 中设置多服务器支持，
 * 修改服务器参数为key=>value数据，而后根据 cacheID 和 serverID 的匹配度提高性能 
 * 
 * 2、增加对其它程序的支持，如ad
 * 
 * 
 * 
 * 操作示例：
 * 
 * $cache=func_initValueCache($filename,$cacheID);
 * 读
 * $value=$cache->get($id);
 * 写
 * $cache->set($value);
 * 删
 * $cache->del();
 */
class valueCache{
	static public $cache=array();
	
	var $filename;
	var $cacheID;
	
	private $memcacheConfig;

	function __construct($config){
		global $_GlobalConfig;
		$this->memcacheConfig = empty($config)?$_GlobalConfig:$config;
		if(empty($this->memcacheConfig['port']))$this->memcacheConfig['port']='11211';
	}

	//此处ID为全局变量指针，用于对全局变量的调用，对不同类型的数据,id要进行行区分,默认使用cacheID进行区分;
	function get($id=null){
		if(empty($id)) $id = $this->cacheID;
		
		if(!isset(self::$cache[$id])){
			self::$cache[$id]=( !empty($this->memcacheConfig['host']) )?$this->getConfigFromMemCache($this->cacheID):$this->getConfigFromFile($this->filename);
		}
		return self::$cache[$id];
	}

	function set($value,$expire=0){
		if( !empty($this->memcacheConfig['host']) ){
			$memcache=func_initMemcached($this->memcacheConfig['host'],$this->memcacheConfig['port']);
			
			$cache=$memcache->get($this->cacheID);
			if(empty($cache))
				$status = $memcache->add($this->cacheID,$value,false, $expire);
			else
				$status = $memcache->set($this->cacheID,$value,false, $expire);
		}else{
			$folder=dirname($this->filename);
			if(!is_dir($folder)) files::mkdirs($folder);
			file_put_contents($this->filename,serialize($value));
			$status = true;
		}
		
		return $status;
	}
	
	function del(){
		if( !empty($this->memcacheConfig['host']) ){
			$memcache=func_initMemcached($this->memcacheConfig['host'],$this->memcacheConfig['port']);
			$memcache->delete($this->cacheID);
		}else{
			if(file_exists($this->filename))unlink($this->filename);
		}
	}

	//解析配置文件
	private function getConfigFromFile($filename){
		$value=array();
		
		if( is_file($filename) ){
			$value=file_get_contents($filename);
			$value=unserialize($value);
		}
		
		return $value;
	}

	//读取配置信息
	private function getConfigFromMemCache($cacheID){
		$value=array();
		
		$memcache=func_initMemcached($this->memcacheConfig['host'],$this->memcacheConfig['port']);
		
		if(!empty($memcache)) $value=$memcache->get($cacheID);
		
		return $value;
	}
}