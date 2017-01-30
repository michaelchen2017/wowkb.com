<?php
/**
 *  分页显示类
 *  使用pdo作为数据连接类， 提供url重写支持， 显示结果使用模板表现样式
 */

class page{

	public $smarty;
	public $conn;
	public $static=false; 		// 是否生成静态链接

	public $pagesign="page";	// 页码参数名
	public $pageinfo=true;	// 页码参数名
	public $page=0;				// 页码

	public $total;				// 记录总数
	public $result;				// 记录集	
	public $pagenav;			// 翻页导航使用的模板
	public $tablePK = null; //数据库表主键信息
	public $dbtype = 'MySQL'; //数据库类型，用于区别记录总数算法

	private $DHpagetarget=null;
	private $DHpageOtherCs=null;

	public function __construct($conn,$smarty){
		$this->smarty=$smarty;
		$this->conn=$conn;
	}

	/**
	 * get result array, 兼容仍使用SQL语句查询分页的程序
	 * @deprecated
	 */
	public function GetList($sql,$maxRows_Rs=25,$DHpagenum=5)
	{
		if($this->DHpagetarget==''){$this->_getURL();}

		$maxRows_Rs=intval($maxRows_Rs);
		if(empty($maxRows_Rs))$maxRows_Rs=25;

		$DHpagenum=intval($DHpagenum);
		if(empty($DHpagenum))$DHpagenum=5;
		
		$startRow_Rs=0;
		$startRow_Rs = $this->page * $maxRows_Rs;

		$this->total=$this->_getRecordCount($sql);

		if(!empty($maxRows_Rs)){
			$sql=$sql." LIMIT $startRow_Rs ,$maxRows_Rs";
		}
		$this->result=$this->conn->getAll($sql);

		$this->_getNav($startRow_Rs,$maxRows_Rs,$DHpagenum);
		if(!empty($this->smarty))$this->smarty->assign("basepagenum",$maxRows_Rs*$this->page);

		return $this->result;
	}
	
	/**
	 * 符合标准查询的列表返回方式，兼容MongoDB/MySQL
	 * 
	 * @param $fields
	 * @param $where
	 * @param $table
	 * @param $maxRows_Rs
	 * @param $DHpagenum
	 * 
	 * @return $result
	 */
	public function readList($fields, $where, $table, $maxRows_Rs=25,$DHpagenum=5){
		if($this->DHpagetarget==''){$this->_getURL();}
		
		$startRow_Rs=0;
		$maxRows_Rs=intval($maxRows_Rs);
		$startRow_Rs = $this->page * $maxRows_Rs;
		
		if(empty($maxRows_Rs))$maxRows_Rs=25;
		if(isset($where['limit'])) unset($where['limit']);//在未计算总数前，不要执行limit计算
		$where['limit']=array($startRow_Rs, $maxRows_Rs);
		
		$DHpagenum=intval($DHpagenum);
		if(empty($DHpagenum))$DHpagenum=5;
		
		if($this->dbtype =='MySQL'){
			if(!is_array($fields) && trim($fields)!="*"){
				//兼容SQL语句
				$this->total = $this->_getRecordCount( dbtools::getSQL($fields,null,null) );
				$this->result = $this->conn->getAll($fields);
			}else{
				$this->total = $this->_getRecordCount( dbtools::getSQL($fields, $table, $where));
				$this->result = $this->conn->getAll($fields, $where, $table);
			}
		}else{
			$this->total = $this->conn->Count($where, $table);
			$this->result = $this->conn->getAll($fields, $where, $table);
		}
		
		$this->_getNav($startRow_Rs,$maxRows_Rs,$DHpagenum);
		if(!empty($this->smarty))$this->smarty->assign("basepagenum",$maxRows_Rs*$this->page);
		
		return $this->result;
	}
	
	static function getIDs($type="I",$name='id'){
		if(!empty($_GET[$name])) return intval($_GET[$name]);
		
		$result=array();
		if(!empty($_POST[$name])){
			$ids=is_array($_POST[$name])?$_POST[$name]:array($_POST[$name]);
			if(empty($ids)) $ids=array(0);
			
			foreach($ids as $k=>$v){
				$ids[$k]=intval($v);
			}
			
			$result=($type == "I")?$ids[0]:$ids;
		}
		
		return $result;
	}

	private function _getNav($startRow_Rs,$maxRows_Rs,$DHpagenum){

		$totalPages_Rs = ceil($this->total/$maxRows_Rs);
		$list=array();

		if ( $totalPages_Rs> 0 && $this->page<=$totalPages_Rs ){
			if($this->page<0){$this->page=0;}
			$DHpagecs=ceil(($this->page+1)/$DHpagenum-1);

			$list["current"]=$this->page+1;
			$list["totalpage"]=$totalPages_Rs;
			$list["per"]=$maxRows_Rs;

			$list["first"] = $startRow_Rs + 1;
			$list["last"] = min($startRow_Rs+$maxRows_Rs, $this->total);

			$list["group"]=$DHpagenum;
			$list["total"]=$this->total;


			//prev group
			if ($DHpagecs>=1){
				$list["prevgroup"]=$this->_getLink($DHpagecs*$DHpagenum-1);
			}else{
				$list["prevgroup"]="";
			}

			//prev page
			if($this->page+1>1){
				$list["prevpage"]=$this->_getLink($this->page-1);
			}else{
				$list["prevpage"]="";
			}

			//each page
			$listpage=array();
			for($i_page=($DHpagecs*$DHpagenum+1);$i_page<=(($DHpagecs+1)*$DHpagenum);$i_page++){
				if ($i_page>($totalPages_Rs)) {
					break;
				}
				if ($i_page==($this->page+1)){
					$listpage[]=array($i_page,"");
				}else{
					$listpage[]=array($i_page,$this->_getLink($i_page-1));
				}
			}
			$list["eachpage"]=$listpage;

			//next page
			if( $this->page+1<$totalPages_Rs ){
				$list["nextpage"]=$this->_getLink($this->page+1);
			}else{
				$list["nextpage"]="";
			}
			//next group
			if ($i_page <= ($totalPages_Rs)){
				$list["nextgroup"]=$this->_getLink(($DHpagecs+1)*$DHpagenum);
			}else{
				$list["nextgroup"]="";
			}

			//for page index
			if ($this->page != 0){
				$list["firstpage"]=$this->_getLink(0);
			}else{
				$list["firstpage"]="";
			}

			if ($this->page < $totalPages_Rs-1){
				$list["lastpage"]=$this->_getLink($totalPages_Rs-1) ;
			}else{
				$list["lastpage"]="";
			}
		}
		$list['pageinfo']=$this->pageinfo;
		
		if(!empty($this->smarty))$this->smarty->assign("pagenav",$list);
		$this->_getNavTpl();
	}
	
	private function _getNavTpl(){
		if(!empty($this->pagenav)){
			$navtpl = $this->pagenav;
		}else{
			$navtpl=DOCUROOT.'/include/template/pagenav/'.conf('global','system.multiPageTpl').'.html';
			if(!is_file($navtpl)){
				$navtpl=DOCUROOT.'/include/template/pagenav/cn.html';
			}
		}
		if(!empty($this->smarty))$this->smarty->assign("pagenavtpl",$navtpl);
	} 
	
	/**
	 * get records count num
	 * @param SQL;
	 * @return int
	 */
	private function _getRecordCount($sql){
		$sqlstr = strtolower($sql);
		$pos = strpos($sqlstr, 'order by');
		if(!empty($pos)) $sql = substr($sql,0,$pos);
		
		$pos = strpos($sqlstr, 'limit');
		if(!empty($pos)) $sql = substr($sql,0,$pos);
		
		$start = strpos( $sqlstr, "select") + 6;
		$end = strpos( $sqlstr, "from ");
		$sql1 = substr( $sql, 0, $start );
		$sql2 = substr( $sql, $end);
		
		$tbpk = empty($this->tablePK)?"*":$this->tablePK;//表主键
		$newsql = $sql1." count({$tbpk})as num ".$sql2;
		
		$rs = $this->conn->getOne($newsql);
		$num = empty($rs)?0:$rs['num'];
		
		return $num;
	}
	
	/**
	 * format the url param
	 * static  rule
	 *    param_param_..._pagenum
	 *    i.g: list_ad_mm_9.html
	 *    the num 9 is page num
	 * @return nothing
	 */
	private function _getURL(){
		global $_SERVER;
		$arrurl=array();
		$this->DHpageOtherCs="";

		if($this->static){
			$orginurl=$_SERVER["REQUEST_URI"];//请求地址
			$ext=strrchr($orginurl, '.');//后缀

			$baseurl=substr($orginurl,0,strlen($orginurl)-strlen($ext));

			$pagenum=str_replace('_','',strrchr($baseurl, '_'));//获取页码
			$this->page=intval($pagenum);

			$baseurl=substr($baseurl,0,strlen($baseurl)-strlen($pagenum));
			$baseurl=strings::endstr($baseurl,'_');
			
			$this->DHpagetarget=$baseurl;//生成访问前缀
			$this->DHpageOtherCs=$ext;//生成访问后缀

		}else{
			$this->DHpagetarget=$_SERVER['SCRIPT_NAME'];

			if($_SERVER['QUERY_STRING']!=""){
				$arrcs=explode("&",$_SERVER['QUERY_STRING']);
				foreach($arrcs as $key=>$value){
					$arrtempcs=explode("=",$value);
					if(count($arrtempcs)==2){
						if($arrtempcs[0]==$this->pagesign){
							$this->page=intval($arrtempcs[1]);
						}else{
							$arrurl[$arrtempcs[0]]=$arrtempcs[1];
						}
					}
				}

			}
			if(!empty($arrurl)){
				foreach($arrurl as $key=>$value){
					$this->DHpageOtherCs=$this->DHpageOtherCs."&".$key."=".$value;
				}
			}

		}
	}

	//根据页码返回目标链接
	private function _getLink($pagenum){
		$link="";
		if($this->static){
			$link = $this->DHpagetarget.$pagenum.$this->DHpageOtherCs;
		}else{
			$link = $this->DHpagetarget."?".$this->pagesign."=" . $pagenum. $this->DHpageOtherCs;
		}
		return $link;
	}
		
}
?>