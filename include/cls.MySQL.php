<?php
/**
 * MySQL防止预期外注入的措施：
 *
 * 简单说就是不要给用户传入特殊操作符的机会。
 * 1，所有使用框架中数组定义的方式进行的查询都是安全的
 * 2，使用用户输入的数据拼接SQL时，对数字类型的参数要使用Type Casting:(int)||(float)进行类型验证，对字符类型的参数要使用dbtools::escape进行过滤，并在SQL中使用引号包括用户输入的数据
 * 3，使用框架或自己接拼接SQL尽量不要引用用户数据生成表名，如一定需要这样做，程序员要严格限定输入的参数格式
 *
 * @author weiqiwang
 */
class MySQL{
	var $conn;//当前链接
	var $silent = false;//是否不抛出异常信息
	var $mysqlMasterSlave=false; //是否使用mysql数据库主从结构

	var $master;//主库,可读写
	var $slave;//从库，只读

	var $config=array();//当前链接的配置
	var $sql=array();//累计执行的sql语句
	var $querynum=0;//累计查询的数量

	function init(){
		if(!$this->mysqlMasterSlave){
			//独立库
			$this->conn = $this->connect( $this->config );
		}else{
			//主从库，自动打开到从库链接
			$this->conn = $this->slave = $this->connect( $this->config['slave'] );
		}
	}
	
	/**
	 * 使用mysqli进行连接
	 * @return mysqli connection
	 */
	function connect($config){
		$conn = new mysqli($config['server'], $config['user'], $config['password'], $config['database']);
		
		if ( $conn->connect_errno ) func_throwException("Failed to connect to MySQL: (" . $conn->connect_errno . ") " . $conn->connect_error);
		if ( !mysqli_set_charset ( $conn , $config['charset'] ) ) func_throwException("Error loading character set {$config['charset']}: {$conn->error}");
		if ( empty(dbtools::$conn)||!mysqli_ping(dbtools::$conn) ) dbtools::$conn = $conn;
		
		return $conn;
	}

	/**
	 * 执行sql语句
	 * @param string sql语句
	 * @param string 默认为空，可选值为 CACHE UNBUFFERED
	 * @param int Cache以秒为单位的生命周期
	 * @return resource
	 */
	public function query($sql){
		if(!$this->mysqlMasterSlave){
			//对于使用mysql-proxy实现主从结构或没有主从结构的情况直接调用主库即可
			if(empty($this->conn)){
				$this->conn=$this->connect( $this->config );
			}
		}else{
			//根据查询类型选择使用主库还是从库
			if(strtolower(substr(trim($sql),0,6))=='select'){
				$this->conn = $this->slave;
			}else{
				if(empty($this->master)){
					if($this->config['master']['server'] == $this->config['slave']['server']){
						$this->master = $this->slave;
					}else{
						$this->master = $this->connect( $this->config['master'] );
					}
				}
				$this->conn = $this->master;
			}
		}

		//执行数据内容操作
		if(substr($sql,-1)!=';') $sql = $sql.';';
		if( !($result = mysqli_query($this->conn,$sql)) && !$this->silent){
			$mes = 'MySQL Query Error:'.$sql;
			func_throwException($mes);
		}else{
			$this->sql[]=$sql;//记录执行语句
		}

		$this->querynum++;
		return $result;
	}

	/**
	 * 执行sql语句，快捷方法
	 * @return int or bool
	 */
	public function exec( $sql ){
		$this->query( $sql );
		return $this->conn->affected_rows;
	}
	
	/**
	 * 
	 * 执行sql语句，返回记录集行数
	 * @param string $sql
	 * @return number
	 */
	public function rows( $sql ){
		$result = $this->query( $sql );
		return mysqli_num_rows ( $result );
	}

	/**
	 * 执行sql语句，只得到一条记录
	 * @return array
	 */
	public function getOne( $fields, $where = null, $table=null, $order=null) {
		$result=$this->getAll( $fields, $where, $table, $order, 1);
		if(!empty($result)){
			foreach($result as $rs){
				return $rs;
				break;
			}
		}
	}

	/**
	 * 执行sql语句，得到所有的记录
	 * @return array
	 */
	public function getAll( $fields, $where = null, $table=null, $order=null, $limit=null) {
		if (empty($fields)) return false;
		
		$sql=dbtools::getSQL($fields, $table, $where, $order, $limit);
		$query = $this->query($sql);
		
		$list = array();
		if(!empty($query)){
			$i=0;
			while( $row = mysqli_fetch_array( $query,MYSQLI_ASSOC ) ) {
				$i++;
				$list[] = $row;
				
				//为防止取出过多的查询结果导致内存溢出，此处对超过100条输出的结果做内存监控
				if($i>100){
					//默认查询数据库的内存上限为16M，可以通过设置SqlMaxMemorySize调整
					static $maxMemory;
					if( empty($maxMemory) ) $maxMemory = defined("SqlMaxMemorySize")?SqlMaxMemorySize:16777216;
					
					$memory_usage=memory_get_usage();
					if( $memory_usage>$maxMemory ) func_throwException("数据库存查询结果过大，导致内存溢出!");
				}
			}
			
			mysqli_free_result($query);
		}
		return $list;

	}

	//插入
	public function Insert( $array, $table) {
		if (empty ($table) && !is_array($array)) {
			return false;
		}
		
		if(!strstr($table,"`")) $table = "`{$table}`";
		
		//判断是单行插入，还是多行插入
		if(!empty($array["key"])&&!empty($array["valuelist"])){
			$sql = 'INSERT INTO '.$table.' ( `' . implode('`, `', $array["key"]) . '` ) VALUES ';
			$valuearr=array();
			foreach($array["valuelist"] as $value){
				foreach ($value as $k => $v) {
					$value[$k] = dbtools::readfields($v);
				}
				$valuearr[]='( ' . implode(', ', $value) . ' )';
			}
			$sql.= implode(',',$valuearr);
		}else{
			foreach ($array as $k => $v) {
				$key[] = '`' . $k . "`";
				$value[] = dbtools::readfields($v);
			}
			$sql = 'INSERT INTO '.$table.' ( ' . implode(', ', $key) . ') VALUES ( ' . implode(', ', $value) . ' )';

		}
		if ($this->query($sql)) {
			return mysqli_insert_id($this->conn);
		} else {
			return false;
		}
	}

	//替换
	public function Replace($array,$table) {
		if (empty ($table) && !is_array($array)) {
			return false;
		}
		
		if(!strstr($table,"`")) $table = "`{$table}`";
		
		if(!empty($array["key"])&&!empty($array["valuelist"])){
			$sql = 'REPLACE INTO '.$table.' ( ' . implode(', ', $array["key"]) . ') VALUES ';
			$valuearr=array();
			foreach($array["valuelist"] as $value){
				foreach ($value as $k => $v) {
					$value[$k] = dbtools::readfields($v);
				}
				$valuearr[]='( ' . implode(', ', $value) . ' )';
			}
			$sql.= implode(',',$valuearr);
		}else{
			foreach ($array as $k => $v) {
				$key[] = '`' . $k . "`";
				$value[] = dbtools::readfields($v);
			}

			$sql = 'REPLACE INTO '.$table.' ( ' . implode(', ', $key) . ') VALUES ( ' . implode(', ', $value) . ' )';
		}
		return $this->exec($sql);
	}

	//更新
	public function Update($fields, $where, $table) {
		if (empty ($table) || empty($fields)) return false;
		if(!strstr($table,"`")) $table = "`{$table}`";
		$sql = 'UPDATE '.$table.' SET ' . dbtools::parseFields($fields) . ' WHERE ' . dbtools::parseWhere( $where );
		return $this->exec($sql);
	}

	//执行
	public function Execute($sql){
		return $this->exec($sql);
	}

	//删除
	public function Remove($where,$table){
		if(!strstr($table,"`")) $table = "`{$table}`";
		$sql = 'DELETE FROM '.$table.' WHERE ' . dbtools::parseWhere( $where );
		return $this->exec($sql);
	}
	
	//记数
	public function Count($where, $table){
		$fields='count(*) as count';
		if(is_array($where)){
				
			//去重查询
			if(isset($where['distinct'])){
				$fields="count( DISTINCT {$where['distinct']} ) as count";
				unset($where['distinct']);
			}
		}
		
		$wherestr=empty($where)?"":" WHERE ".dbtools::parseWhere($where);
		$count=$this->getOne("SELECT {$fields} FROM {$table}{$wherestr}");
		return $count["count"];
	}
	
	//对单表进行优化
	public function OptimizeTB($tbn){
		if(!empty($tbn)) $this->exec("OPTIMIZE TABLE `{$tbn}`");
	}
	
	//对数据库中的全部表进行优化
	public function OptimizeDB($dbconf){
		$tblist = func_getAllTbList($dbconf);
		if(empty($tblist)) return false;
		
		$conn = $this->connect( func_getDbSetting($dbconf) );
		$sql='OPTIMIZE TABLE `'.implode("`,`", $tblist).'`';
		
		if($query = mysqli_query($this->conn,$sql )){
			$this->sql[]=$sql;//记录执行语句
		}else{
			$mes = 'MySQL Query Error:'.$sql;
			func_throwException($mes);
		}
	}

	//挂起
	private function halt($message = '', $sql = ''){
		$out ="MySQL Query:$sql <br>";
		$out.="MySQL Error:".$this->conn->error." <br>";
		$out.="MySQL Error No:".$this->conn->errno." <br>";
		$out.="Message:$message";
		exit($out);
	}

	//日志
	private function log( $mes, $n ){
		$path = DOCUROOT."/data/logs/mysql";
		if(!is_dir($path)) files::mkdirs($path);
		file_put_contents( $path."/error_".$n.".log",$mes);
	}
}

?>