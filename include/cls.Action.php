<?php
/*
 * Created on 2008-4-3 By Weiqi
 */
abstract class Action {
	// 模板对象
	public static $tplobj=null;
	// 语言包对象
	public $lang;

	// 当前模板设置
	public $tpl=null;
	// 当前action运行时环境
	public $env=null;
	// 用户授权类型
	public $authz="user";//superadmin:超管,admin:授权用户,user:普通用户

	// 当前方法名
	public $method;
	
	// 程序内置缓存时间
	public $cacheTime=0;
	
	// 当前页面的访问位置
	protected $self;
	
	// 初始化
	function __construct(){
		// 获取数据库操作对象		
		if( self::$tplobj == null ){
			//是否使用集中式模板结构，默认采用分项目模板结构
			if( !defined( 'centreModel' ) ) define( 'centreModel', false );

			self::$tplobj = func_getSmarty( AppName , centreModel);
		}
		if(!empty($_SERVER)){
			$this->self=$_SERVER["SCRIPT_NAME"];
		}
		
		//对admin.wxc.com进行处理
		if(strtolower(substr($_SERVER['HTTP_HOST'],0,5))=='admin'||strstr($_SERVER['HTTP_HOST'],'mywxc.com')){
			$returnURL = "/members/passport.php?act=login&redirect=".rawurlencode($_SERVER['REQUEST_URI']);
			
			//必须是管理权限 
			if(!$this->isAdmin()){
				if(strtolower(substr($_SERVER['REQUEST_URI'],0,8))!='/members') go($returnURL);
			}
			
			//如果通过框架管理口登录的用户，强制注销后，再统一登录
			if(empty($_SESSION['UserID'])||empty($_SESSION['UserName'])){
				foreach($_SESSION as $key=>$val) unset($_SESSION[$key]);
				
				$pools = array('uniqueUID','sid','loc');
				foreach($_COOKIE as $key=>$val){
					if(!in_array($key,$pools)){
						setcookie($key, '', null, '/', '.wenxuecity.com');
						if (isset($_COOKIE[$key])) unset($_COOKIE[$key]);			
					}
				}
				if(strtolower(substr($_SERVER['REQUEST_URI'],0,8))!='/members') go($returnURL);
			}
		}
	}
	// 析构
	function __destruct(){
		if(empty($_SESSION['UserLevel']))return;
		if(!empty($_GET['debug'])){
			if($_GET['debug']=='showtpl') {
				$tpl=array();
				$tpl['ScriptName'] = $_SERVER['SCRIPT_NAME'];
				$tpl['TemplateFolder']=str_replace(DOCUROOT,'',self::$tplobj->template_dir[0]);
				$tpl['TemplateFile']=str_replace(DOCUROOT,'',$this->tpl);
				$tpl['includeTpl']=isset(self::$tplobj->tpl_vars['includeTpl'])?str_replace(DOCUROOT,'',self::$tplobj->tpl_vars['includeTpl']):'';
								
				echo '<pre style="clear:both;"><h3>The Script Tpl: </h3></pre><hr>';
				debug::d($tpl);
			}
			if($_GET['debug']=='showfields') {
				$vars=self::$tplobj->tpl_vars;
				if(isset($_GET['field']))
					$vars=empty($vars[$_GET['field']])?$vars:$vars[$_GET['field']];
				
				echo '<pre style="clear:both;"><h3>All Assign Vars: </h3></pre><hr>';
				debug::d($vars);
			}
			
			if($_GET['debug']=='showserver') {
				$lst=array('PATH','DOCUMENT_ROOT','SERVER_SIGNATURE');
				$result=array();
				foreach($_SERVER as $key=>$val){
					if(in_array($key,$lst)) continue;
					$val = str_replace( DOCUROOT,'/DOCUROOT',$val );
					$result[$key]=$val;
				}
				echo '<pre style="clear:both;"><h3>The Server Vars: </h3></pre><hr>';
				debug::d($result);
			}
		}
	}

	// 附值
	public function assign($name, $value){
		return self::$tplobj->assign($name, $value);
	}

	// 注销
	public function unsign($name){
		$arr=explode('.',$name);
		$num=count($arr);
		switch($num){
			case 1:
				unset(self::$tplobj->tpl_vars[$name]);break;
			case 2:
				unset(self::$tplobj->tpl_vars[$arr[0]]->value[$arr[1]]);break;
			case 3:
				unset(self::$tplobj->tpl_vars[$arr[0]]->value[$arr[1]][$arr[2]]);break;
			case 4:
				unset(self::$tplobj->tpl_vars[$arr[0]]->value[$arr[1]][$arr[2]][$arr[3]]);break;
			case 5:
				unset(self::$tplobj->tpl_vars[$arr[0]]->value[$arr[1]][$arr[2]][$arr[3]][$arr[4]]);break;
			default:
				;
		}
	}
	
	/**
	 * 返回原文学城用户系统中的定义
	 * 用户分组定义如下,*****表示常用的项目：
	 * 
		|1|游客/尚未登录用户
		|2|注册会员 *****
		|3|等待Email验证的会员
		|4|等待许可的(COPPA)会员
		|5|超级版主 *****
		|6|管理员 *****
		|7|版主
		|8|广告管理员
		|9|博客管理员
		|10|博客超级管理员
		
	 */
	protected function getUserGroupID(){
		$sessInfo = $this->getUserSession();
		$usergroupid = empty( $sessInfo['bbuserinfo']['usergroupid'] )?2:$sessInfo['bbuserinfo']['usergroupid'];
		$usergroupid = intval($usergroupid);
		
		return $usergroupid;
	}
	
	/**
	 * 判断是否为管理员
	 * @return boolean
	 */
	public function isAdmin() {
		static $isAdmin;
		if( isset($isAdmin) ) return $isAdmin;
		
		//有管理权限的正常登录用户
		if( $this->getUserGroupID()== 6 ) {
			$isAdmin = true;
			return true; 
		}
		
		//在框架机制下检查管理环境
		if( func_checkAdmin() ) {
			$isAdmin = true;
			return true;
		}
		
		$isAdmin = false;
		return false;
	}
	
	/**
	 * 兼容原文学城系统的session机制
	 * 本地调试时需要在本机的hosts中指定cache01,cache02,cache03到127.0.0.1
	 * 
	 * Array
		(
		    [trace] => Array
		        (
		            [currentvisit] => Array
		                (
		                    [cachename] => home
		                    [params] => Array
		                        (
		                            [channel] => 
		                        )
		
		                    [url] => /
		                )
		
		        )
		
		    [bbuserinfo] => Array
		        (
		            [userid] => 730792
		            [blogid] => 57109
		            [usergroupid] => 6
		            [username] => wangweiqi
		            [password] => A0VVMFs9XTsGNQNiATMBZg==:06
		            [email] => weiqi228@gmail.com
		            [pid] => 88356
		            [gender] => 1
		            [birthday] => 1980-02-20
		            [purpose] => 0
		            [height] => 185
		            [weight] => 95
		            [animal] => 9
		            [astrology] => 12
		            [blood] => 3
		            [country] => 2
		            [state] => 11
		            [city] => 13
		            [zipcode] => 94506
		            [degree] => 0
		            [occupation] => 0
		            [principalship] => 0
		            [earning] => 0
		            [marriage] => 0
		            [has_child] => 0
		            [need_child] => 0
		            [interest] => 0
		            [drink] => 0
		            [smoke] => 0
		            [belief] => 0
		            [language] => 0
		            [characters] => 0
		            [skype] => 
		            [posts] => 0
		            [dateline] => 2011-08-10 02:41:19
		            [profile_background] => 
		            [profile_background2] => 
		            [profile_music] => 
		            [profile_titlebgcolor] => #FF6666
		            [profile_bgcolor] => #e5e5e5
		            [profile_bgcolor2] => #FFFFFF
		            [profile_status] => 13
		            [profile_view] => 215
		            [network_added] => 0
		            [network_blocked] => 0
		            [pmpopup] => 1
		            [showgender] => 1
		            [invisible] => 0
		            [ispublic] => 1
		            [skypestatus] => 1
		            [rate] => 1
		            [commentcheck] => 0
		            [commentstatus] => 1
		            [guestcomment] => 1
		            [pcommentcheck] => 0
		            [pcommentstatus] => 1
		            [pguestcomment] => 1
		            [status] => 1
		            [newpassword] => 0
		            [confirmation_code] => 
		            [confirmed] => 1
		            [verified] => Yes
		            [logincheck] => Yes
		            [ipaddress] => 184.72.56.125
		            [ipcountry] => United States
		            [user_info_id] => 730792
		            [user_info_date_of_last_logon] => 2013-05-08 14:41:09
		            [user_info_number_of_logons] => 40
		            [user_info_date_of_last_activity] => 
		            [user_info_date_account_created] => 2010-04-07 19:22:43
		            [user_info_date_account_last_modified] => 2013-04-05 23:50:12
		            [user_info_account_updateprofile] => 0
		            [user_info_ipaddress] => 184.72.56.125
		            [user_info_via_proxy] => 
		            [user_details_id] => 730792
		            [summary] => 
		            [aboutme] => 
		            [hobbies] => 
		            [m_hobbies] => 
		            [buddylist] => 
		            [blacklist] => 
		        )
		
		    [getmodperm] => Array
		        (
		            [usergroupid] => 6
		            [title] => 管理员
		            [usertitle] => 管理员
		            [theme] => 
		            [canread] => 1
		            [canpostnew] => 1
		            [canreply] => 1
		            [candeletepost] => 1
		            [candeleteipposts] => 1
		            [candeleteuserposts] => 1
		            [caneditpost] => 1
		            [canpackage] => 1
		            [canmove] => 1
		            [cancopy] => 1
		            [cansticky] => 1
		            [canbanuser] => 1
		            [canbanip] => 1
		            [canbanwords] => 1
		            [canviewips] => 1
		            [canopenclose] => 1
		            [canshowreply] => 1
		            [canshowallposts] => 1
		            [ismoderator] => 1
		            [issupermod] => 1
		            [isadmod] => 0
		        )
		
		    [favorites] => Array
		        (
		        )
		
		    [theme] => default
		)
	 */
	public function getUserSession(){
		return func_getUserSession();
	}
	
	// 获取当前服务器的缓存对象
	function getMemObj(){
		$cachePools = include DOCUROOT.'/admin/tools/srcSync/svnServerList.php';
		$host = in_array( $_SERVER['SERVER_ADDR'], $cachePools )?$_SERVER['SERVER_ADDR']:"cache01";
		if(debug::check("showserver")) debug::d( "memcache server: ".$host );
		 
		return func_initMemcached($host);
	}
	
	// 设置缓存 
	function setCache($cacheID, $cache, $cacheTime=0){
		$cachePools = array();
		
		//写入多点缓存
		if(defined('PRODUCTION')){
			if(PRODUCTION!='0'){
				$cachePools = include DOCUROOT.'/admin/tools/srcSync/svnServerList.php';
				$cachePools[]="cache01";
			}
		}
		
		//测试环境单点
		if(empty($cachePools))$cachePools = array("cache01");
		
		foreach($cachePools as $host)
			if( $obj=func_initMemcached($host) ) $obj->set($cacheID,$cache,false, $cacheTime);
	}
	
	//检测缓存设置
	function checkCache($cache){
		//没有缓存
		if(empty($cache)) return false;
		
		//超时
		if(isset($cache['datetime'])){
			$now = time();
			$cachePoint = intval( $cache['datetime'] );
			if( $now - $cachePoint > $this->cacheTime ) return false;
		}
		
		//管理员调试
		if(debug::check('hp')) return false;
		
		//管理员手动清除缓存
		if(!empty($_GET['clear'])) {if(!empty($_SESSION['UserLevel'])) return false;}
		
		return true;
	}

	// 显示指定模板
	public function show($tpl){
		self::$tplobj->display( $tpl );
		exit;
	}

	// 显示
	public function display(){
		$html = "";
		
		//是否需要语言转换
		if(!empty($_COOKIE['lang_big5'])){
			$obj = new big5();
			if($_COOKIE['lang_big5']==1) $html = $obj->c2t($this->fetch());//简转繁
			if($_COOKIE['lang_big5']==2) $html = $obj->t2c($this->fetch());//繁转简
		}
		
		if(empty($html)){
			self::$tplobj->display( $this->tpl );
		}else{
			echo $html;
		}
	}
	
	// json输出
	public function json($value,$options=null){
		echo json_encode($value,$options);
		exit;
	}

	// 抓取
	public function fetch($tpl=null){
		$tpl=empty($tpl)?$this->tpl:$tpl;
		$html=self::$tplobj->fetch( $tpl );
		return $html;
	}

	// 启用smarty调试
	public function smartyDebug(){
		self::$tplobj->debugging=true;
	}

	// 注册函数块
	public function register_block($name, $value){
		return self::$tplobj->register_block($name, $value);
	}

	// 注册修饰函数
	public function register_modifier($name, $value){
		return self::$tplobj->register_modifier($name, $value);
	}

	// 加载指定项目的完整路径变量
	public function getTpl($tpl=null,$nav=true,$item='space'){
		if(empty($tpl)){
			$tpl=empty($_GET["act"])?$this->method:$_GET["act"];
			$tpl.=".html";
		}

		$baseTplPath=$this->getItemBaseTpl( $item );
		$objname=get_class($this);
		$this->assign("leftNav",$nav);
		$this->assign("includeTpl",DOCUROOT."/".AppName."/Tpl/{$objname}/{$baseTplPath}/{$tpl}");
	}

	// 加载指定项目的父路径
	public function getItemBaseTpl($item){
		$config=conf();
		
		//返回项目的配置
		if( !empty($config['app'][$item]['baseTplPath']) ) return $config['app'][$item]['baseTplPath'];
		
		//返回全局的标识
		if($config['global']['categorise']=='Portal') return $config['global']['url'];
		
		//返回调用的模板
		return $config['global']['tpl'];
	}

	/**
	 * 加载不同模式下的tpl路径
	 * 返回类似 
	 * 
	 * /template/default/article/view  
	 * 或 
	 * /article/Tpl/view/default
	 * 的模板路径 
	 *
	 * @param 应用 $appname
	 * @param 页面 $act
	 * @param 是否分站 $siteFolder
	 * 
	 * @return string $path  
	 */
	public function getTypeBaseTpl($app, $act='', $siteFolder=false){
		
		//集中模式
		$centrePath = "/template/".conf('global','tpl')."/{$app}";
		if(!empty($act)) $centrePath .= "/{$act}";
		
		//项目模式
		$selfPath= "/{$app}/Tpl";
		if(!empty($act)) $selfPath .= "/{$act}";
		if( $siteFolder ) $selfPath .= "/".conf('global','tpl');
		
		//项目目录下的模板的调用优先级高于集中式的模板
		$path = file_exists(DOCUROOT.'/'.$selfPath)? $selfPath : $centrePath;
		
		return $path;
	}

}
?>