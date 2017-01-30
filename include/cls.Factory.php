<?php
/*
 * Created on 2008-4-3 By Weiqi
 * 
 * URL 参数 clear 功能列表：
 * true=>不为空时执行缓存清除
 * cacheDebug=>显示缓存调试信息
 * 
 * URL 参数 debug 功能列表：
 * true=>不为空时强制执行php并显示错误报告
 * showsql=>显示php执行时间及全部sql查询语句
 * showfields=>显示page的全部字段
 */

final class Factory {
	// 是否启用SESSION
	public $sess=false;

	// 是否启用缓存及缓存时间,单位秒
	public $cache=0;
	// 启用缓存的页面动作
	public $cacheAct="all";
	// 是否启用缓存目录深度控制,用于大量重复的页面缓存,最大深度为4
	public $cacheDeep=0;
	// 缓存类型
	public $cacheType='html';
	// 强制删除缓存时的验证标识
	public $cacheLabel=null;
	// 缓存调试
	public $cacheDebug=false;
	// 是否启用域名目录存储缓存
	public $cacheDomain=true;
	
	// 指定模板调用父路径
	public $baseTplPath=null;
	// 指定模板调用名称
	public $baseTplName=null;

	// 是否为管理后台
	public $admin=false;
	// 是否为用户空间
	public $space=false;
	// 在Action中加载的外围环境变量
	public $env=null;
	// Action运行时变量
	public $spaceNav=true;
	// 给Action初始化时读取的全局环境变量
	static $actEnv=null;

	// 根路径
	public $rootpath=DOCUROOT;
	// 设置语言包
	public $lang=array();
	
	// 是否启用错误处理
	public $errorDetail=false;

	// 是否显示本地调试信息
	public $debug=false;
	// 是否启用smarty调试
	public $smartydebug=false;

	// 动作
	protected $action=null;
	// 动作调用路径
	protected $actionPath=null;
	// 方法名
	protected $method=null;

	// 调试信息
	protected $starttime;
	// 验证信息
	protected $authinfo;

	// 缓存对象
	protected $cacheObj=null;
	// 运行的对象
	private $ACT=null;

	function __construct(){
		//判断是转向到唯一域名
		$uniqueDomain=conf('global','uniqueDomain');
		if(!empty($uniqueDomain)){
			if($uniqueDomain!=$_SERVER['HTTP_HOST']) go("http://".trim($uniqueDomain).$_SERVER['REQUEST_URI']);
		}
		
		//获取当前服务器
		if(debug::check("serverip")) echo $_SERVER['SERVER_ADDR'];
		
		//记录运行时间
		$this->starttime=times::getMicrotime();
		
		//全局调试模式
		if( defined( 'AppDebug' ) ) $this->debug=true;
		if( defined( 'SmartyDebug' ) ) $this->smartydebug=true;
	}
	// 输出调试信息
	function __destruct(){
		//运行时调试模式
		if(isset($_GET['debug'])&&isset($_SESSION['UserLevel'])){
			if( $_GET['debug']=='showsql' && $_SESSION['UserLevel']==6 )$this->debug=true;
			if( $_GET['debug']=='showget' && $_SESSION['UserLevel']==6 )debug::d($_GET);
			if( $_GET['debug']=='showpost' && $_SESSION['UserLevel']==6 )debug::d($_POST);
		}
		
		//输出调试信息
		if($this->debug){
			//显示全部调试错误
			debug::g();
			//查询执行次数			
			$debugInfo["processTimes"]=0;
			//加载所有数据库静态连接池里的对象
			$pool=func_getDB(null,null,array('debug'=>true));
			if(!empty($pool)){
				foreach($pool as $key=>$val){
					$temp=$val->sql;
					$debugInfo["sql"][$key] = $temp;
					$debugInfo["processTimes"]+=count($temp);
				}
			}
			//记录运行时间
			$debugInfo["processTime"]=times::getMicrotime()-$this->starttime;

			//输出运行时间、执行的SQL语句、执行查询的数量
			echo '<pre style="clear:both;"><h3>Debug Message: </h3></pre><hr>';
			debug::d($debugInfo);
		}
	}

	// 执行动作
	public function run($action=null,$method="index"){
		if($action==null) alert("NoneAction");

		//设置错误处理机制
		if($this->errorDetail){
			set_error_handler( empty($this->errorHandler)?array($this,"appError"):$this->errorHandler );
		}

		//启用session
		if( $this->sess || $this->admin || $this->space || strtolower(substr($_SERVER['HTTP_HOST'],0,5))=='admin' || strstr($_SERVER['HTTP_HOST'],'mywxc.com')) $this->startSession();
		
		//收到调试请求时判断是否启用SESSION
		if( isset($_GET["debug"]) || isset($_GET["clear"]) ) {
			$ip = http::getIP();
			if( substr($ip,0,strlen(DEBUGIP)) == DEBUGIP ) $this->startSession();
		}
		
		//设置action及method的路径 
		$this->setInfo($action,$method);

		//获取权限信息，false为未通过权限验证	
		if($this->sess)			
			if(!$this->authinfo=$this->checkAuth_1()) $this->authGoTo(1);

		//根据使用缓存使用设置调用显示逻辑
		if( $this->checkCacheAct()){
			$this->loadCache();
		}else{
			$this->loadAction();
		}
	}
	
	//必须在run后运行，输出当前页面的html源码
	public function getHTML(){
		if(empty($this->ACT)) return 'You must Excute run befor this method!';
		$html=$this->ACT->fetch();
		return $html;
	}

	//根据条件检测是否满足使用缓存的条件
	private function checkCacheAct(){
		//不是定时缓存，$cache 为0的情况返回false;
		if($this->cache == 0) return false;
		
		//使用Action程序内缓存
		if($this->cacheType=='memcache') return false;
		
		//检查act
		if($this->cacheAct=="all"){return true;}
		if($this->cacheAct==$this->method){return true;}
		if(is_array($this->cacheAct)){
			if(@in_array($this->method,$this->cacheAct)){return true;}
		}
		return false;
	}

	/**
	 * 处理使用缓存时的页面显示
	 *
	 *
	 */
	private function loadCache(){
		$this->cacheObj=$Cache=new Cache();
		$Cache->cacheDebug=$this->cacheDebug;
		$Cache->cacheDeep=$this->cacheDeep;
		$Cache->cacheType=$this->cacheType;
		$Cache->cacheLabel=$this->cacheLabel;
		$Cache->cacheDomain=$this->cacheDomain;
		
		//获得缓存ID
		$cache_id = $Cache->getCacheID();
		
		//设置缓存
		$Cache->setCache($this->cache,$cache_id);
		
		//拒绝的普通用户的调试请求
		if( !empty($_SESSION['UserLevel']) ){
			//预览
			if(isset($_GET["debug"])) $Cache->closeCache();
			
			//清除缓存
			if(isset($_GET["clear"])){
				if($_GET["clear"]=='cacheDebug') $Cache->cacheDebug=true;
				$Cache->clearCacheByID($cache_id,$this->cacheDeep);
			}
		}
		
		//加载要执行的Action
		if(!$Cache->isCached()) $this->loadAction();
		
		$Cache->display();
	}

	/**
	 * 加载动作，并处理显示逻辑
	 *
	 */
	private function loadAction(){
		$actionfile = $this->rootpath."/".AppName."/Action/".$this->actionPath.".php";
		if(file_exists($actionfile)){
			//加载类文件
			require( $actionfile );
			$actname = $this->action;
			$metname = "ACT_".$this->method;
				
			//加载环境变量
			$this->initActEnv();
				
			//实例化
			$ACT = new $actname();
				
			//传递方法名
			$ACT->method=$this->method;
				
			//传递缓存时间
			if($this->cacheType=='memcache') $ACT->cacheTime = $this->cache;
			
			//加载运行时的环境
			$this->loadEnv($ACT);

			if(method_exists($ACT,$metname)){
				$this->doAction($ACT);
			}else{
				func_throwException('没有找到执行方法!');
			}
		}else{
			func_throwException('没有找到执行方法!');
		}
	}

	/**
	 * 加载  Action 环境
	 *
	 * @param object $ACT
	 */
	private function loadEnv($ACT){
		if( empty($this->env) ){
			$Env=new Env($ACT);
			if($this->admin) $Env->admin();
			if($this->space) $Env->space();
		}else{
			//用户函数中使用，init函数与主Action共享模板对象
			$env=$this->env;
			if(is_array($env)){
				if(!empty($env["mod"])&&!empty($env["app"])){
					$ACT->env=$env["app"]."_".$env["mod"];
					$obj=$this->loadModel($env["mod"],$env["app"]);
					$obj->init($ACT);
				}
			}
		}
	}

	//初始化运行环境的全局变量，供Action类初始化时调用
	private function initActEnv(){
		if( empty($this->env) ){
			if($this->admin) {
				Factory::$actEnv='admin';
			}
			if($this->space) {
				Factory::$actEnv='space';
			}
		}else{
			$env=$this->env;
			if(is_array($env)){
				if(!empty($env["mod"])&&!empty($env["app"])){
					Factory::$actEnv=$env["app"]."_".$env["mod"];
				}
			}
		}
	}

	/**
	 * 执行当前主类方法
	 *
	 * @param Object $ACT
	 */
	private function doAction($ACT){
		//当前页面
		$metname="ACT_".$this->method;

		//数据所有权检测				
		$this->checkAuth_2($ACT);

		//设置调用的模板
		$ACT->tpl = empty($ACT->tpl)?$this->getTplInfo():$ACT->tpl;

		//是否启用smarty的调试
		if($this->smartydebug){
			$ACT->smartyDebug();
		}

		//得到完整的前台配置信息
		$conf=$this->getConf();
		
		$ACT->assign("root",DOCUROOT);//加载站点根目录
		$ACT->assign("site",$conf);//加载站点配置文件	
		$ACT->assign("authz",$ACT->authz);//加载用户授权类型
		$ACT->assign("sitetpl",$conf['tpl']);//加载站点模板路径
		
		//加载模板语言包文件
		if(!empty($this->lang)) {
			$ACT->lang = lang($this->lang);
			$ACT->assign( "lang", $ACT->lang );
		}
		
		$ACT->$metname();

		//设置缓存显示或直接显示
		if(!empty($this->cacheObj)){
			$this->cacheObj->display($ACT);
		}else{
			$ACT->display();
		}
		
		//执行完页面后，加载动作对象，用于Factory后续功能调用
		$this->ACT=$ACT;
	}

	/**
	 * 第一级权限检查
	 * 调用外面的passport类中的定义，判断用户是否有模块操作权限
	 * @return false || superadmin,admin,user,visit,none
	 *
	 * 返回权限状态或权限代码解释如下：
	 * superadmin,仅定义为超级管理员的用户
	 * admin,所有授权的用户
	 * user,所有登录的访问者
	 * visit,所有访问者
	 * 未定义权限文件或权限文件中无当前访问页面定义 none,
	 *
	 * false 未通过权限检查
	 */
	private function checkAuth_1(){
		$authzLevel=$this->authFiter();

		//不存在权限文件或没有设置权限检查
		if(!$authzLevel){return "none";}

		//加载权限验证基类
		$passort=load("passport_passport");
		if(!$passort){
			//使用简单验证方式
			if(empty($_SESSION["UserID"])){
				return false;
			}else{
				return true;
			}
		}

		$result=$passort->checkAuth(basename(AppName)."_".$this->action,$authzLevel);
			
		return $result;
	}

	/**
	 * 第二级权限检查
	 * 如当前执行的主类中定义数据所有权验证函数，则执行此函数将验证逻辑转移到主类
	 * 主类验证函数检查用户对要操作的数据是否有所有权
	 *
	 * @param object $ACT
	 * @param string $metname
	 * @param string $authinfo
	 *
	 * @return void
	 */
	private function checkAuth_2($ACT){
		//初始化用户授权类型
		$ACT->authz=$this->authinfo;

		//超管和未定义权限直接返回
		if($this->authinfo=="superadmin"||$this->authinfo=="none")	return;

		if(method_exists($ACT,"checkAuth")){
			$authLevel_2=$ACT->checkAuth($this->method,$this->authinfo);
			if(!$authLevel_2){
				$this->authGoTo(2);
			}
		}
	}

	/**
	 * 过滤权限定义文件，返回与当前访问页面相关的权限定义
	 *
	 * @return string "superadmin","admin","user"
	 */
	private function authFiter(){
		$authfile=$this->rootpath."/".AppName."/Config/auth/".$this->action.".php";
		if(!file_exists($authfile)){
			if($this->admin){
				//如果当前程序是系统后台，在没有定义权限验证方式时默认为admin权限
				return 'admin';
			}
			
			if($this->space){
				//如果当前程序是用户空间，在没有定义权限验证方式时默认为user权限
				return 'user';
			}
			
			//默认返回，表示不验证
			return false;
		}

		//加载权限文件
		$list=include( $authfile );
		foreach($list as $key=>$val){
			if(is_bool($val)){
				if($val)return $key;
			}
			if(in_array($this->method,$val)){
				return $key;
			}
		}
		
		//管理程序，在没有定义时，启用admin级的验证
		if($this->admin) return 'admin';
		
		//默认返回，表示不验证
		return false;
	}

	/**
	 * 验证未通过时跳转
	 *
	 * @param string $level 验证未通过的环节等级
	 *
	 * @return void
	 */
	private function authGoTo($level){
		//授权调试
		if(isset($_SESSION['CheckMod'])) exit;
		
		$tailstr="redirect=".rawurlencode( $_SERVER["REQUEST_URI"] )."&level=".$level;
		if($this->admin){
			go( "/passport/admin.php?act=admin&errmsg=accessdeny&checkAuth=true&".$tailstr);
		}
		go( "/passport/?".$tailstr);
	}

	/**
	 * 模板路径
	 *
	 * @param string $tplpath
	 * @return string $tpl
	 */
	private function getTplInfo(){
		$defaultFolder = $this->actionPath;
		
		if(is_string($this->baseTplPath)) {
			$defineFolder = $this->actionPath."/".$this->baseTplPath;
		}else{
			$defineFolder = $this->actionPath."/".conf('global','tpl');
		}
		
		$filename = empty($this->baseTplName)?$this->method:$this->baseTplName;
		$filename = $filename.".html";
	
		if( file_exists( DOCUROOT."/".AppName."/Tpl/{$defineFolder}/{$filename}" ) ){
			$tpl = "{$defineFolder}/{$filename}";
		}else{
			$tpl = "{$defaultFolder}/{$filename}";
		}
		
		return $tpl;
	}
	
	//得到用于前台输出的配置信息
	private function getConf(){
		$config = conf();
		$filter=array('uid','tpl','app','navlist','linklist','global');
		$result=array();
		foreach($config as $key=>$val){
			if(in_array($key,$filter)) $result[$key]=$val;
		}
		return $result;
	}

	/**
	 * 设置执行类及显示页面
	 *
	 * @param string $action
	 * @param string $method
	 */
	private function setInfo($action,$method){
		if(strstr($action,"/")){
			$this->action = basename($action);
		}else{
			//设置动作
			$this->action = $action;
		}
		$this->actionPath = $action;
		//格式化act参数
		$this->method = $_GET["act"] = strtolower(empty($_GET["act"])?$method:$_GET["act"]);
	}

	/**
	 * 加载session
	 * 默认的SESSION为 UserID,UserName,NickName,UserLevel,UserPower
	 */
	private function startSession(){
		func_initSession();
		if(!$this->sess) $this->sess = true;
	}

	/**
	 * 自定义错误处理
	 *
	 * @access public
	 * @param int $errno 错误类型
	 * @param string $errstr 错误信息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行数
	 * @return void
	 */
	public static function appError($errno, $errstr, $errfile, $errline){
		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
				$errorStr = "Error:[$errno] $errstr ".basename($errfile)." at $errline row.\n";
				func_logs( $errorStr,4 );
				exit( $errorStr );
				break;
			case E_STRICT:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			default:
				$errorStr = "Notice:[$errno] $errstr ".basename($errfile)." at $errline row.\n";
				func_logs( $errorStr,1 );
				break;
		}
	}

	/** 当设置了调试用户时，启动debug */
	public function userDebug( $userID=0 ){
		if(!empty($userID)){
			$this->startSession();
			if( $_SESSION['UserID']==$userID ) $this->debug=true;
		}
	}

}
?>