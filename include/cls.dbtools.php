<?php
class dbtools{
	public static $conn; //mysqli连接，用于字符验证
	
	/**
	 * 获得经过组织的SQL语句
	 *
	 * @param str||arr $condition
	 * @param string $table
	 * @param str||arr $where
	 * @param arr $order
	 * @param int $limit
	 * @return string $sql
	 */
	public static function getSQL( $condition, $table, $where, $order=null, $limit=null ){
		$sql="";
		if( $table!=null ){
			//处理where内部常量
			if( $ualist = isset($where['UA'])?$where['UA']:false ) unset($where['UA']);
			if( $order = isset($where['order'])?$where['order']:$order ) unset($where['order']);
			if( $limit = isset($where['limit'])?$where['limit']:$limit ) unset($where['limit']);
			
			//select & table
			$sql="SELECT ". self::parseSelect($condition) ." FROM `{$table}` ";

			//where
			$whereSQL = self::parseWhere($where);
			if(!empty($whereSQL)) $sql.= "WHERE " . $whereSQL;

			//union all
			if(!empty($ualist)) $sql = self::parseUnionAll($sql, $whereSQL, $ualist);
			
			//order
			$sql.= self::parseOrder($order);

			//limit
			$sql.= self::parseLimit($limit);

		}else{
			$sql=$condition; //直接使用SQL
		}

		return $sql;
	}

	/**
	 * 分析查询字段
	 *
	 * @param string||array $condition
	 * @return string
	 */
	public static function parseSelect($condition){
		if(is_string($condition)) return $condition;
		
		$list=array();
		foreach($condition as $key=>$val) $list[]=self::paresSelectItem($key,$val);
		return implode(', ', $list);
	}
	
	
	public static function paresSelectItem($key,$val){
		if($key==="SQL") return empty($val)?"":$val;
		return "`".$val."`";
	}
	
	public static function parseFields($condition){
		if(is_string($condition)) return $condition;
		foreach ($condition as $k => $v) $value[] = self::parseFieldsItem($k , $v );
		return implode(', ', $value);
	}
	
	/**
	 * 处理复合条件
	 *
	 * @param string $key
	 * @param array $val
	 * @return string
	 */
	public static function parseFieldsItem($key,$val){
		if($key=="SQL") return empty($val)?"":$val;
		return '`'.$key."`=".self::readfields($val);
	}
	

	/**
	 * 分析查询条件
	 *
	 * @param str||arr $condition
	 * @return string where
	 */
	public static function parseWhere($condition){
		if( empty($condition) ) return "";
		if( is_string($condition) ) return $condition;
		
		//以下两项用于直接调用parseWhere的代码时使用
		if( $order = isset($condition['order'])?$condition['order']:false ) unset($condition['order']);
		if( $limit = isset($condition['limit'])?$condition['limit']:false ) unset($condition['limit']);
		
		$sql='';
		$list=array();
		foreach($condition as $key=>$val){
			$tmp=self::paresWhereItem($key,$val);
			if(!empty($tmp)) $list[]=$tmp;
		}

		$sql = implode(' AND ', $list );
		
		//order
		if($order) $sql.= self::parseOrder($order);

		//limit
		if($limit) $sql.= self::parseLimit($limit);
		
		return $sql;
	}

	/**
	 * 处理复合条件
	 *
	 * @param string $key
	 * @param array $val
	 * @return string
	 */
	public static function paresWhereItem($key,$val){
		//处理复合条件SQL
		if($key=="SQL")
			return empty($val)?"":"({$val})";
		
		//处理复合条件OR
		if($key=="OR"){
			$tmpSQL=array();
			foreach($val as $k=>$v){
				foreach($v as $item){
					$tmpSQL[]= "`{$k}`=".self::readfields( $item )."";
				}
				break;//仅取一级
			}
				
			return "(".implode(" OR ",$tmpSQL).")";
		}
			
		//处理其他单一条件
		if(strstr($key,",")){//非等条件,如 !=,>,<,>=,<= 等
			$arr=explode(",",$key);
			$key=$arr[0];
			$delimiter=$arr[1];
		}else{
			$delimiter="=";
		}
		
		return "(`{$key}`{$delimiter}".self::readfields( $val ).")";
	}
	
	public static function parseUnionAll($sql,$whereSQL,$ualist){
		$sql.=(empty($whereSQL))?"WHERE ":" AND ";
		
		$tmpSQL=array();
		foreach($ualist as $key=>$value){
			foreach($value as $val)
				$tmpSQL[]= "({$sql}(`{$key}`=".self::readfields( $val )."))";
				
			break;//仅取一级
		}
		
		//使用UNION ALL重新连接SQL语句
		$sql=implode( " UNION ALL ", $tmpSQL );
		
		return $sql;
	}


	/**
	 * 处理 Order
	 *
	 * @param array $condition
	 * @return string $orderstr
	 */
	public static function parseOrder($condition){
		if(empty($condition)||!is_array($condition)) return "";
		
		$tmp=array();
		foreach($condition as $key=>$val) $tmp[]="`".$key."` ".$val;
		$order=" ORDER BY ". implode(" , ",$tmp);
		
		return $order;
	}

	/**
	 * 处理Limit
	 *
	 * @param int $condition=25  array $condition=array(from,cellnum)
	 * @return str $limit
	 */
	public static function parseLimit( $condition ){
		if(empty($condition)) return "";
		
		$limit="";
		if( is_numeric($condition)) $limit = " LIMIT ".$condition;
		if( is_array($condition)) $limit = " LIMIT ".intval($condition[0]).",".intval($condition[1]);
			
		return $limit;
	}

	/**
	 * 获取数据类型
	 */
	public static function readfields($value) {
		if (is_int($value) || is_float($value)) return $value;
		if (is_string($value)) return "'" . self::escape($value) . "'";
		if (is_array($value) || is_object($value)) return self::readfields(serialize($value));
		if (is_bool($value)) return ($value ? 1 : 0);

		return 'NULL';
	}

	/**
	 * 过滤非法字符
	 */
	public static function escape($input) {
		if(!mysqli_ping(dbtools::$conn)){
			$obj = load("passport_user");	
			dbtools::$conn = $obj->conn;
		}
		
		try{
			$result = mysqli_real_escape_string( dbtools::$conn, self::str_sl($input) );
		} catch (Exception $e) {
			func_throwException($e->getMessage());
		}
		
		return $result;
	}

	/**
	 * 分解字符
	 */
	public static function str_sl($str) {
		static $get;
		if(!isset($get)) $get = get_magic_quotes_gpc();
		
		if (!$get) return $str;
		
		if (is_array($str)) {
			foreach ($str as $k => $v)
				$str[$k] = dbtools::str_sl($v);
		}else{
			$str = stripslashes($str);
		}
		
		return $str;
	}
	
}
?>