<?php
class MyPDO extends PDO{
	var $sql=array();//累计执行的sql语句
	var $rowsNum = 0;//记录总数		
	/**
	* Extends php default Function PDO
	* 获取全部记录集
	*
	* @param string or array $condition
	* @param string $table
	* @param array $where
	* @return array or false
	*/
	public function getAll($condition, $where = null, $table = null, $order=null,$limit=25) {
		if ($condition == "") {
			return false;
		}
		$sql=dbtools::getSQL($condition, $table, $where, $order, $limit);

		//记录执行语句
		$this->sql[]=$sql;

		if (!$stmt = $this->query($sql)) {
			return false;
		}
		//记录总数
		$this->rowsNum = $stmt->rowCount();

		if ($rs = $stmt->fetchall(2)) {
			return $rs;
		} else {
			return false;
		}
	}
	//获取单个记录集
	public function getOne($condition, $where = null, $table = null, $order=null ) {
		if ($condition == "") {
			return false;
		}

		$sql=dbtools::getSQL($condition, $table, $where, $order, 1);

		//记录执行语句
		$this->sql[]=$sql;

		if (!$stmt = $this->query($sql)) {
			return false;
		}
		//记录总数
		$this->rowsNum = $stmt->rowCount();
		if ($rs = $stmt->fetch(2)) {
			return $rs;
		} else {
			return false;
		}
	}

	//插入
	public function Insert( $array, $table) {
		if (empty ($table) && !is_array($array)) {
			return false;
		}
		//判断是单行插入，还是多行插入
		if(!empty($array["key"])&&!empty($array["valuelist"])){
			$sql = 'INSERT INTO `' . $table . '` ( ' . implode(', ', $array["key"]) . ') VALUES ';
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
			$sql = 'INSERT INTO `' . $table . '` ( ' . implode(', ', $key) . ') VALUES ( ' . implode(', ', $value) . ' )';

		}
		if ($this->Execute($sql)) {
			return $this->lastInsertId();
		} else {
			return false;
		}
	}

	//替换
	public function Replace( $array, $table) {
		if (empty ($table) && !is_array($array)) {
			return false;
		}
		if(!empty($array["key"])&&!empty($array["valuelist"])){
			$sql = 'REPLACE INTO `' . $table . '` ( ' . implode(', ', $array["key"]) . ') VALUES ';
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

			$sql = 'REPLACE INTO `' . $table . '` ( ' . implode(', ', $key) . ') VALUES ( ' . implode(', ', $value) . ' )';
		}
		if ($this->Execute($sql)) {
			return true;
		} else {
			return false;
		}
	}

	//更新
	public function Update($condition, $where, $table) {
		if (empty ($table) || empty($condition)) return false;		
		$sql = 'UPDATE `'.$table.'` SET ' . dbtools::parseFields($condition) . ' WHERE ' . dbtools::parseWhere( $where );
		return $this->exec($sql);
	}

	//执行
	public function Execute($sql){
		//记录执行语句
		$this->sql[]=$sql;
		return $this->exec($sql);
	}

	//删除
	public function Remove($where,$table){
		$sql = 'DELETE FROM `' . $table .  '` WHERE ' . dbtools::parseWhere( $where );
		if ($this->Execute($sql)) {
			return true;
		} else {
			return false;
		}
	}

}
/**
 * Only for eefocus.com core function func_get_db()
 * it will return a user PDOStatement
 * you extends it with yourself function
 * weiqi at 070709
 */
class MyPDOStatement extends PDOStatement {
	protected $db;
	protected function __construct($db) {
		$this->pdo = $db;
	}

	//获取全部记录集
	public function getAll() {
		$this->execute();
		if ($rs = $this->fetchAll(2)) {
			return $rs;
		} else {
			return false;
		}
	}
	//获取单个记录集
	public function getOne() {
		$this->execute();
		if ($rs = $this->fetch(2)) {
			return $rs;
		} else {
			return false;
		}
	}
}


?>