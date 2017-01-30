<?php
final class Exceptions extends Exception{
	/**
	 * 异常类型
	 *
	 * @var string
	 * @access private
	 */
	private $type;

	/**
	 * 是否存在多余调试信息
	 *
	 * @var string
	 * @access private
	 */
	private $extra;
	
	/**
	 * 自定义异常处理
	 *
	 * @access public
	 * @param mixed $e 异常对象
	 */
	public static function appException($Exception){
		
		//有管理权限或启用调试的用户，或在命令行模式下，可以查看错误信息
		//if( !empty($_SESSION ['UserLevel']) || defined( 'CliEenvironment' )) {
			$errors = $Exception->toString();
			if(!is_array($errors)) {
				$trace = debug_backtrace();
				$e['file'] = $trace[0]['file'];
				$e['class'] = $trace[0]['class'];
				$e['function'] = $trace[0]['function'];
				$e['line'] = $trace[0]['line'];
				$traceInfo='';
				$time = date("y-m-d H:i:m",time()+8*3600);
				foreach($trace as $t)
				{
					$traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') ';
					$traceInfo .= empty($t['class'])?"":$t['class'];
					$traceInfo .= empty($t['type'])?"":$t['type'];
					$traceInfo .= (empty($t['function'])?"":$t['function']).'(';
					$traceInfo .= implode(', ', $t['args']);
					$traceInfo .=")<br/>";
				}
				$e['trace']  = $traceInfo;
			}else {
				$e = $errors;
			}
	
			//命令行模式
			if(defined( 'CliEenvironment' )){
				print_r( $e );
				exit;
			}
			
			//网页模式
			if( !file_exists( DOCUROOT.'/include/template/exception.php' ) ){
				echo '<pre>';
				print_r( $e );
				echo '</pre>';
			}else{
				include DOCUROOT.'/include/template/exception.php';
			}
		//}else{
			//其它用户
			/*echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<h2>非常抱歉，服务器内部发生错误！<br>';
			echo '您可以';
			if(!empty($_SERVER['HTTP_REFERER'])) echo '<a href="'.$_SERVER['HTTP_REFERER'].'">返回上一页</a>&nbsp;&nbsp;';
			echo '<a href="/feedback/">向我们反馈这个错误</a></h2>';
			exit;*/
		//}
		exit();
	}
	
	/**
	 * 异常处理
	 *
	 * @param string $msg 错误信息
	 * @param int $code
	 * @return void
	 */
	function __construct( $message,$code=0,$extra=false ){
		parent::__construct($message,$code);
		$this->type = get_class($this);
		$this->extra = $extra;
	}

	/**
	 * 异常输出 所有异常处理类均通过toString方法输出错误
	 * 每次异常都会写入系统日志
	 * 该方法可以被子类重载
	 *
	 * @access public
	 * @return array
	 */
	function toString(){
		$error = '';
		$trace = $this->getTrace();
		if($this->extra) {
			// 通过throw_exception抛出的异常要去掉多余的调试信息
			array_shift($trace);
		}

		$this->class = empty($trace[0]['class'])?"":$trace[0]['class'];
		$this->function = empty($trace[0]['function'])?"":$trace[0]['function'];
		$this->file = empty($trace[0]['file'])?"":$trace[0]['file'];
		$this->line = empty($trace[0]['line'])?"":$trace[0]['line'];
		$file   =   file($this->file);
		$traceInfo='';
		$time = date("y-m-d H:i:m",time()+8*3600);
		foreach($trace as $t) {
			$traceInfo .= '['.$time.'] ';
			$traceInfo .= empty($t['file'])?"":$this->formatFile($t['file']);
			$traceInfo .= empty($t['line'])?"":' ('.$t['line'].') ';
			$traceInfo .= empty($t['class'])?"":$t['class'];
			$traceInfo .= empty($t['type'])?"":$t['type'];
			$traceInfo .= (empty($t['function'])?"":$t['function']).'(';
			$traceInfo .= $this->formatArgs($t['args']);
			$traceInfo .=")\n";
		}
		$error['message']   = $this->message;
		$error['type']      = $this->type;
		$error['detail']   =   ($this->line-2).': '.$file[$this->line-3];
		$error['detail']   .=   ($this->line-1).': '.$file[$this->line-2];
		$error['detail']   .=   '<font color="#FF6600" >'.($this->line).': <b>'.$file[$this->line-1].'</b></font>';
		$error['detail']   .=   ($this->line+1).': '.$file[$this->line];
		$tmp=empty($file[$this->line+1])?'':$file[$this->line+1];
		$error['detail']   .=   ($this->line+2).': '.$tmp;
		$error['class']     =   $this->class;
		$error['function']  =   $this->function;
		$error['file']      = $this->formatFile($this->file);
		$error['line']      = $this->line;
		$error['trace']     = $traceInfo;

		return $error;
	}

	function formatArgs($args){
		if(empty($args)) return;
		if(!is_array($args)) return;
		
		$tmp=array();
		foreach($args as $key=>$val){
			if(is_string($val))	$tmp[]=$val;
			if(is_array($val))	{
				if(!empty($val['server']))$val['server']='DbServer';
				if(!empty($val['user']))$val['user']='DbManager';
				if(!empty($val['password']))$val['password']='******';
				$tmp[]=$val;
			}
		}
		return $this->makeVal($tmp);
	}
	
	function makeVal($val){
		if(!is_array($val)) return @strval($val);
		
		$tmp=array();
		foreach($val as $v){
			$tmp[] = (is_array($v)) ? $this->makeVal($v) : $v;
		}
		
		$result="[".implode(", ", $tmp)."]";
		return $result;
	}

	function formatFile($file){
		$file= str_replace(DOCUROOT,"",$file);
		return $file;
	}
}
?>