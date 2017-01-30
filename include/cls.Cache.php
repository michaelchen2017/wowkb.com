<?php
/**
 * @category A、单点缓存，正常创建，使用 clearCache 删除
 * 
 * @category B、同类标识缓存，使用MemCache做为缓存介质，当MemCache不存在时，关闭相关缓存创建时指明缓存验证标识
 *
 * 原理：
 * 1、创建或更新时使用setCacheLabel在MemCache中设置相关标识
 * 2、程序读取缓存前检测是否有Label标识，
 * 		如果存在Label标识且Label标识时间大于当前缓存时间生成时间则重新生成缓存
 * 		否则直接读取静态缓存文件
 * 3、当使用setCacheLabel更新标识后，所有标识为此类的缓存都会更新
 * 
 * @category C、部分缓存页面功能
 * 
 *  在Factory 中增加下面选项
 * 	#是否启用部分缓存机制
 * 	public $cacheLib=false;
 * 	#部分缓存检测池
 * 	public $cacheLibPool=false;
 *
 * 原理：
 * 1、使用目前缓存机制生成的缓存做为显示时的模板
 * 2、在原模板中用 {##}标识不被缓存的那部分程序代码
 * 3、在对应的app中,调用Lib/下的一个类，并建一个单独的页面对应Action中的ACT_index();
 *    来生成不缓存部分的动态内容，默认调用一个空方法，不执行任何操作
 * 4、run时将要缓存的部分写入到动态的模板中
 * 5、设置app调用的模板为经过缓存处理的模板
 * 6、app执行时调用缓存后再执行由Factory类中act对应的指定方法;仅生成动态的数据
 * 7、cache在调用缓存时判断是调用全部，还是调用部分内容
 * 
 * @category D、PHP程序生命周期内的缓存
 * 
 */
class Cache{
	// 启用调试
	public $cacheDebug=false;
	// 目录深度
	public $cacheDeep=0;
	// 强制删除缓存时的验证标识
	public $cacheLabel=null;
	// 是否使用分域名存储
	public $cacheDomain=true;

	// 缓存ID
	protected $cacheID=null;
	// 缓存时间
	protected $cacheTime=0;
	// 缓存类型
	public $cacheType='html';
	// 缓存选项
	protected $isCache=false;
	// 缓存文件是否有效
	protected $isCached=false;
	// 缓存路径
	protected $cacheDir="";
	// 全局通用缓存
	public static $values=array();

	function __construct(){
		if( !empty($_SESSION['UserLevel']) && !empty($_GET['debug']) ){
			if($_GET['debug']=='showcache') $this->cacheDebug=true;
		}
	}
	
	//在一个程序生命周期内快速设置缓存
	public static function setValue($id,$value){
		Cache::$values[$id] = $value;
	}
	
	//在一个程序生命周期内快速访问缓存
	public static function getValue($id){
		$result = isset(Cache::$values[$id])?Cache::$values[$id]:null;
		return $result;
	}
	
	// 设置目录深度
	static public function getDeepFolder($id,$n){
		$n=intval($n);
		$n=$n>4?4:$n;

		if($n>0){
			$strarr=array();
			for($c=0;$c<$n;$c++){
				$strarr[]=substr($id,$c*2,2);
			}
			$pathstr = implode("/",$strarr).'/';
			return $pathstr;
		}
		return "";
	}

	/**
	 * 根据URL获得缓存ID
	 * 获得当前的缓存ID，针对有clear参数的情况做了对应处理,debug时不需要处理cacheid
	 * @return string CacheID
	 */
	public function getCacheID(){
		$url=$this->getCacheUrl();
		$cacheID=md5($url);

		$this->debug('CacheID: '.$cacheID);
		return $cacheID;
	}

	/**
	 * 获取当前的URL
	 * @return string
	 */
	private function getCacheUrl(){
		static $url;
		if(!empty($url)) return $url;

		if(isset($_GET["clear"])){
			$url=str_replace("?clear=".$_GET["clear"]."&","?",$_SERVER["REQUEST_URI"]);
			$url=str_replace("?clear=".$_GET["clear"],"",$url);
			$url=str_replace("&clear=".$_GET["clear"],"",$url);
		}else{
			$url=$_SERVER["REQUEST_URI"];
		}

		$this->debug('CacheUrl: '.$url);
		return $url;
	}

	// 检测缓存 
	public function isCached(){
		//没有启用缓存
		if( !$this->isCache ) return false;
		
		//没有缓存文件
		$filename = $this->cacheDir.$this->cacheID;
		if( !is_file($filename) ) return false;
		
		//检查缓存时间
		$cacheFileModifyTime = filemtime($filename);
		if( $this->cacheTime > 0 ) {
			if((time()-$cacheFileModifyTime > $this->cacheTime) ) return false;
		}
		
		//检测缓存标识
		if( !empty($this->cacheLabel) ){
			//处理多个缓存标识
			if( !is_array($this->cacheLabel) ) $this->cacheLabel = array($this->cacheLabel);
			foreach( $this->cacheLabel as $label )
				if( $this->labelExists($label, $cacheFileModifyTime) ) return false;
		}

		//返回缓存
		$this->isCached=true;
		return true;
	}
	
	private function labelExists($label, $cacheFileModifyTime){
		global $_GlobalConfig;
		$memcache = func_initMemcached( $_GlobalConfig['host'], $_GlobalConfig['port']);
		if(empty($memcache)) return false; //此处代表标签不存在
		
		$cacheExpireDateTime = $memcache->get("Cache_{$label}");
		if(!empty($cacheExpireDateTime)){
			/**
			 * 缓存创建时间在验证数据记录时间前的，缓存失效
			 * 由于此处$cacheExpireDateTime要对多个缓存文件生效
			 * $cacheExpireDateTime在创建时会指定自动销毁的时间
			 */
			if($cacheFileModifyTime<$cacheExpireDateTime) return true; //存在标签且更新时间在现有缓存之后
		}
		
		return false;
	}

	/**
	 * 设置缓存验证标识
	 * @param $cacheLabel  标识
	 * @param $expire  失效时间
	 * 
	 * @return void
	 */
	public function setCacheLabel($cacheLabel,$expire){
		global $_GlobalConfig;
		$memcache = func_initMemcached( $_GlobalConfig['host'], $_GlobalConfig['port']);
		if(empty($memcache)) return false; 
		
		$status=$memcache->add("Cache_{$label}", time(), false, $expire);
		$this->debug('Create cache label status: '.strval($status));
	}

	/**
	 * 使用访问的URL清除模板缓存
	 * i.g.  $cache->clearCache('/your/file/path.php?x=y',3,'www.eefocus.com','blog');
	 */
	public function clearCache($url,$n=0,$domain=null,$serverType='all'){
		$this->debug('ClearCacheURL: '.$url);

		$this->isCache = false;//关闭缓存
		$result=array();//执行的结果
		$servers=conf('global','server.worknode');//所有分布的服务器
		$cacheID=md5($url);//缓存ID

		$this->debug('ClearCacheID: '.$cacheID);

		//是否启用域名目录
		if($this->cacheDomain){
			$domain=empty($domain)?$_SERVER["HTTP_HOST"]:$domain;
			if(!is_array($domain))$domain=array($domain);
		}else{
			$domain=null;
		}

		//是否使用了分布部署
		$targetServer = empty($servers)?array():explode(',',$servers);

		if(empty($targetServer)){
			## 执行本地清理
			$list=array();
			$this->debug('LocalDisk Cache Deleting .....');
			if($this->cacheDomain){
				foreach($domain as $val){
					$list[]=DOCUROOT.$this->getCacheFile($cacheID,$n,$val);
				}
			}else{
				$list[]=DOCUROOT.$this->getCacheFile($cacheID,$n);
			}

			$this->debug('CacheFile list:');
			if($this->cacheDebug)debug::d($list);

			$result=$this->doDelete($list);
		}else{
			## 使用http触发多服务器的清理脚本
			$domain = $this->cacheDomain?implode(',',$domain):null;
			$result = $this->clearMultiServerCache($domain,$url,$n,$targetServer);
		}

		return $result;
	}

	// 使用CacheID当前服务器上的模板缓存
	public function clearCacheByID($cacheID,$n=0){
		$cacheFile=DOCUROOT.$this->getCacheFile($cacheID,$n);
		$this->debug('ClearCacheFile: '.$cacheFile);

		$server=conf('global','server');
		if(!empty($server['webnode'])){
			$webnode=explode(',',$server['webnode']);
			$this->clearMultiServerCache($_SERVER["HTTP_HOST"],$this->getCacheUrl(),$n,$webnode);
		}

		if(is_file($cacheFile)) return @unlink($cacheFile);
	}

	// 使用http触发,清除多台服务器上的缓存
	private function clearMultiServerCache($domain,$url,$n,$servers){
		$conf = conf( 'global','system' );
		$key = md5($url.$conf['md5key']);
		$type=$this->cacheType;

		//传递过去的URL参数
		$urlparam='system_cache_url='.rawurlencode($url);
		$urlparam.='&system_cache_type='.$type;
		$urlparam.='&system_cache_key='.$key;
		$urlparam.='&system_cache_n='.$n;
		if($this->cacheDomain){
			$urlparam.='&system_cache_domain='.$domain;
		}

		$result=array();
		foreach($servers as $k=>$v){
			$request= 'http://'.$v.'/include/plugins/clearcache.php?'.$urlparam ;
			$msg=@file_get_contents($request);
			$result[]=array('server'=>$v,'request'=>$request,'msg'=>$msg);
		}

		$this->debug('Multi ClearCacheResult: ');
		if($this->cacheDebug)debug::d($result);

		return $result;
	}

	// 遍历删除缓存文件
	public function doDelete($list){
		$result=array();
		foreach($list as $cacheFile){
			if( !strstr($cacheFile,DOCUROOT.'/cache/') ){
				$result[]= $cacheFile.' Access Denied! Can only remove the cache!';
				continue;
			}
			if(is_file($cacheFile)) {
				if(!unlink($cacheFile)) {
					$result[]=$cacheFile.' Permission Denied!';
				}else{
					$result[]=$cacheFile.' File is deleted!';
				}
			}else{
				$result[]=$cacheFile.' File is not exists!';
			}
		}

		return $result;
	}


	/**
	 * 获取缓存文件位置
	 * @param $cache_id: cache md5 ID
	 * @param $n: folder deep num
	 * @param $domain: single or array
	 * @param $partURL: partion of vist url  (i.g.  in[/ad/abc.php,/abc.php]  /abc.php is $partURL)
	 */
	public function getCacheFile($cache_id,$n,$domain=null){
		//基本缓存目录
		$cacheDir="/cache/{$this->cacheType}/";

		//多站环境下,缓存目录中使用域名信息
		if($this->cacheDomain){
			$domain=empty($domain)?$_SERVER["HTTP_HOST"]:$domain;
			$cacheDir.=$domain."/";
		}

		//引入目录深度设置
		if($n>0) $cacheDir.=$this->getDeepFolder($cache_id,$n);

		//完整的缓存文件路径
		$cacheFile= $cacheDir.$cache_id;

		return $cacheFile;
	}

	// 设置模板缓存
	public function setCache($lefttime,$cacheID){
		$this->isCache = true;
		$this->cacheID=$cacheID;

		//设置缓存时间
		if($lefttime>0&&$lefttime<600){
			$this->cacheTime = 600;//缓存时间最小不能小于10分钟
		}else{
			$this->cacheTime=$lefttime;
		}

		//基本缓存目录
		$this->cacheDir = DOCUROOT."/cache/{$this->cacheType}/";

		//多站环境下,缓存目录中使用域名信息
		if($this->cacheDomain)
		$this->cacheDir.=$_SERVER["HTTP_HOST"].'/';

		//为了避免大量缓存文件存在时影响磁盘读写效率，引入目录深度设置
		if($this->cacheDeep>0)
		$this->cacheDir .= $this->getDeepFolder($cacheID,$this->cacheDeep);

		$this->debug('CacheDir: '.$this->cacheDir);
	}

	//写缓存
	private function writeCache($content){
		if(empty($content)){
			return ;
		}
		if(!is_dir($this->cacheDir)){
			files::mkdirs( $this->cacheDir, 0777 );
		}
		file_put_contents($this->cacheDir.$this->cacheID, $content);
	}

	//读缓存
	private function readCache(){
		$content=file_get_contents($this->cacheDir.$this->cacheID);
		return $content;
	}

	// 显示
	public function display($ACT=null){
		if(!$this->isCache){
			if(!empty($ACT)) {
				//输出header
				$this->getContentType();
				$ACT->display();
			}else{
				alert("NoneAction");
			}
		}else{
			if($this->isCached){
				$content=$this->readCache();
			}else{
				//获取缓存数据
				$content=$ACT->fetch();
					
				//创建缓存文件
				$this->writeCache($content);
			}
				
			//输出header
			$this->getContentType();
			$this->getLastModified();
			
			//是否需要语言转换
			if(!empty($_COOKIE['lang_big5'])){
				$obj = new big5();
				if($_COOKIE['lang_big5']==1) $content = $obj->c2t($content);//简转繁
				if($_COOKIE['lang_big5']==2) $content = $obj->t2c($content);//繁转简
			}
			
			//输出
			echo $content;
		}
		exit;
	}
	
	//关闭模板缓存
	public function closeCache(){
		$this->isCache = false;
	}

	public function getContentType(){
		if(empty($_GET['cache_ext'])){
			$pos=strpos($_SERVER['REQUEST_URI'],"?");
			$filename=empty($pos)?$_SERVER['REQUEST_URI']:substr($_SERVER['REQUEST_URI'],0,$pos);
			$ext=strtolower(substr(strrchr($filename, "."), 1));
		}else{
			$ext = $_GET['cache_ext'];
		}
		if($ext=="php") return;

		$pools=array(
			"html"=>"text/html",
			"htm"=>"text/html",
			"css"=>"text/css",
			"js"=>'application/x-javascript',
			"png"=>"image/png",
			"jpg"=>"image/jpeg",
			"jpeg"=>"image/jpeg",
			"gif"=>"image/gif",
			"json"=>"application/json",
		);
		if(!empty($pools[$ext])) header("Content-Type:".$pools[$ext]);
	}

	//取服务器时间为文档最后修改时间
	public function getLastModified(){
		//Wed, 24 Jun 2009 02:11:12 GMT
		$timestamp = filemtime($this->cacheDir.$this->cacheID);
		header("Last-Modified:".date('D, d M Y H:i:s ',$timestamp)."GMT");
	}
	
	//缓存生成判断及log记录
	public function logCache($rs,$filename,$conn=null){
		if(empty($rs)){
			//用于记录缓存更新信息
			file_put_contents(DOCUROOT."/cache/".$filename.".txt", date("y-m-d h:i:s")."/n".serialize($conn)."/n".serialize(self::$tplobj)."/n".serialize($_SERVER) );
		}
	}
	
	private function debug($msg){
		if($this->cacheDebug) echo $msg."<br>";
	}
}
?>