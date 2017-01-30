<?php
/**
 * @param $type  关联数据库的字段,如 bbs,article,download
 * @param $id    下载ID,123,456
 */
include '../_inc.php';
func_initSession();

$app=new download();
$app->getFile();

class download{
	
	private $obj;		//文件数据库对象
	private $id;		//文件ID
	private $type='';	//文件存储类型 upload/data

	function __construct(){
		//检测参数
		if( empty($_GET['id'])||empty($_GET['type'])||empty($_GET['app']) ) 
			alert(404);
		
		//检测用户
		$this->checkUserType();
		
		//下载ID
		$this->id=$_GET['id'];
		
		//检测类型
		$pools = array('bbs','download','article','main');
		if( !in_array($_GET['type'],$pools) ) 
			alert(404);
			
		//加载数据对象	
		$this->obj=load("{$_GET['type']}_upload");
	}

	function getFile(){
		$rs=$this->obj->getOne('*',array('id'=>$this->id));
		if(empty($rs['path_info'])) alert(404);
		
		//下载的目标记录计数加1
		$this->obj->addNum($this->id);
		
		$this->checkLocalFile($rs);
		
		$this->checkScore($rs);
		
		$filepath = DOCUROOT.$rs['path_info'];
		
		$this->doDownLoad( $filepath );
	}
	
	private function checkLocalFile($rs){
		if(substr($rs['path_info'],0,6)=='/data/') $this->type='data';
		if(substr($rs['path_info'],0,8)=='/upload/') $this->type='upload';
		if( empty($this->type) ) go($rs['path_info']);//非本地下载直接转向
	}
	
	/**
	 * 下载类型
	 * 1、直接下载 directDown 
	 * 2、验证登录 checkLogin
	 * 3、验证激活 checkActive
	 * 4、后台发送 sendmail
	 * 5、填写信息 infoRequest
	 */
	private function checkUserType(){
		$type=conf('global',"app.home.{$_GET['app']}.howToCheckUser");
		if(empty($type)) $type = "checkLogin";
		
		if($type=='directDown') return;
		
		if( $type=='checkLogin' && empty($_SESSION['UserID']) )
			go('/passport/?redirect='.rawurlencode($_SERVER['REQUEST_URI']));
			
		if( $type=='checkActive' && empty($_SESSION['UserActive']) )
			go('/space/?act=active');
			
		//if( $type=='sendmail' ) go('/service/survey/mailform.php?redirect='.rawurlencode($_SERVER['REQUEST_URI']));
			
		//if( $type=='infoRequest' ) go('/service/survey/commonform.php?redirect='.rawurlencode($_SERVER['REQUEST_URI']));
	}
	
	//积分操作
	private function checkScore($rs){
		//目标积分
		$point = intval($rs['getpoint']);
		
		//如果设置了最低下载积分
		$minscore = conf('global','score.min_down');
		if( !empty( $minscore ) ) {
			if( $point < $minscore ) $point = $minscore;
		}
		
		//没有积分设置
		if(empty($point)) return;
		
		//当前用户ID
		$uid=empty($_SESSION['UserID'])?0:$_SESSION['UserID'];
		
		//跳过自己的操作
		if($uid==$rs['user']) return;
		
		//跳过管理员的操作
		$obj=load('passport_passport');
		$isAdmin = $obj->checkAuth('download','admin');
		if($isAdmin) return;
		
		//如果扣除操作成功，则给作者执行加分操作
		$score = -1 * $point;
		if( func_setScore($uid,$rs['id'],$score,'download','downfile',conf('global','uid'),true) ){
			func_setScore($rs['user'],$rs['id'],$rs['getpoint'],'download','upfile',conf('global','uid'),true);
		}
	}
	
	/**
	 * 下载原理：
	 *
	 * 1，使用重写禁用直接下载;
	 * RewriteRule ^data/application/download/(.*)$ /admin/page/system.php
	 * 
	 * 2，根据当前时间创建临时目录
	 * 3，每小时自动删除两小时前的目录
	 * 
	 * 理论上每个文件最少有1个小时的下载时间，最多有2个小时的下载时间
	 */
	private function doDownLoad($filepath){
		$linkid = substr(md5(date('ymdH').conf('global','system.md5key')),0,6);
		
		//创建data临时目录
		if( !is_dir( DOCUROOT.'/cache/data/' ) ) files::mkdirs(DOCUROOT.'/cache/data/');
		$sourceDataFolder = DOCUROOT."/data";
		$targetDataFolder = DOCUROOT . "/cache/data/{$linkid}";
		if(!file_exists($targetDataFolder))shell_exec("ln -s {$sourceDataFolder} {$targetDataFolder}");
		
		//创建upload临时目录
		if( !is_dir( DOCUROOT.'/cache/upload/' ) ) files::mkdirs(DOCUROOT.'/cache/upload/');
		$sourceUploadFolder = DOCUROOT."/upload";
		$targetUploadFolder = DOCUROOT . "/cache/upload/{$linkid}";
		if(!file_exists($targetUploadFolder))shell_exec("ln -s {$sourceUploadFolder} {$targetUploadFolder}");
		
		//获得临时下载的URL
		if($this->type=='data') $filepath=str_replace($sourceDataFolder, $targetDataFolder, $filepath);
		if($this->type=='upload') $filepath=str_replace($sourceUploadFolder, $targetUploadFolder, $filepath);
		
		$fileUrl=substr($filepath,strlen(DOCUROOT));
		
		if(debug::check('showfile')){echo $fileUrl;exit;}
		go($fileUrl);
	}
}

?>