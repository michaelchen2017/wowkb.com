<?php
class System{
	//不设置应用程序的目录
	protected $NotApp=array(
					'data',
					'upload',
					'cache',
					'template',
					'include',
					'js',
					'css',
					'images',
					'plugins'
				);
				
	function getAppNameConfig(){
		//启用valueCache,初始化相关变量
		$filename = DOCUROOT."/cache/site/structure_".systemVersion.".ldb";
		$cacheID = "site_system_struct_".systemVersion;
		$obj = func_initValueCache($filename,$cacheID);

		//读取
		$config=$obj->get('site_system_struct');

		if(empty($config) || $this->checkClear() ){
			$config=array();
			$config['updated']=date('Y-m-d H:i:s',times::getTime());

			//加载根目录的应用
			$tmp=files::fileList( DOCUROOT );
			foreach($tmp as $app){
				if( $this->checkApp($app) ){
					$config[$app]=$app;
				}
			}
				
			//加载集合目录下的应用
			foreach(conf('system') as $cate){
				$tmp=files::fileList( DOCUROOT.'/'.$cate );
				foreach($tmp as $app){
					if( $this->checkApp($app,$cate)){
						$config[$app] = $cate.'/'.$app;
					}
				}
			}
				
			//写入
			$obj->set($config);
		}

		return $config;
	}
	
	//检测App
	private function checkApp($app,$categorise=''){
		
		$path=empty($categorise)?$app:$categorise.'/'.$app;
		if(!is_dir(DOCUROOT.'/'.$path)) return false; 
		
		if( in_array($app,$this->NotApp) ) return false;
		if( in_array($app,conf('system')) ) return false;
		if( substr($app,0,1)=='.' ) return false;
		
		return true;
		
	}

	//判断是否要强制清除缓存
	private function checkClear(){
		if( !empty($_GET['clear']) ){
			if( $_GET['clear']=='struct' ){
				echo 'Rebuild system structure cache OK!<br>';
				return true;
			}
			
			//用于批量删除时判断
			if( $_GET['clear']=='clearstruct' ) return true;
		}
		
		return false;
	}
}
?>