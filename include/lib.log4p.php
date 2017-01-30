<?php
/**
 * 框架内置日志记录，提供基本的日志服务
 *
 * <code>
 * <?php
 * $log=new log4p();
 * $log->type='file';
 * $log->file=DOCUROOT.'/test.txt';
 * $log->appendLog('some msg',1);
 * $log->appendLog('some msg',1);
 * $log->appendLog('some msg',1);
 * ……
 * 
 * ==========================================
 * 
 * $log=new log4p();
 * $log->type='html';
 * $log->appendLog('some msg',1);
 * ……
 * 
 * ==========================================
 * 
 * $log=new log4p();
 * $log->type='screen';
 * $log->appendLog('some msg',1);
 * ……
 * ?>
 * </code>
 * */
class log4p{
	public $type='file';

	public $file='';

	/**
	 * 保存运行期间的日志，在脚本结束时将日志内容写入到文件
	 *
	 * @var string
	 * @access protected
	 */
	protected $log = '';

	/**
	 * 指示哪些级别的错误要保存到日志中
	 *
	 * @var array
	 * @access protected
	 * @static
	 */
	protected $errorLevel = array( 
		0=>'default', 
		1=>'notice', 
		2=>'debug', 
		3=>'warning', 
		4=>'error', 
		5=>'exception',
		6=>'log' 
	);
	
	/**
	 * 
	 */
	protected $dateTimeFormat='Y-m-d H:i:s';

	/**
	 * 指示当日志文件超过多少 KB 时，自动创建新的日志文件，单位是 K
	 *
	 * @var int
	 * @access protected
	 */
	protected $maxSize = 4096;

	function __construct(){
		list($usec, $sec) = explode( " ", microtime() );

		$this->log = sprintf( "Start Logging [%s %s]==============\n",
		    date( $this->dateTimeFormat, times::getTimeStamp($sec) ), $usec );

		if (isset($_SERVER['REQUEST_URI'])) {
			$this->log .= sprintf("[%s] REQUEST_URI: %s\n",
			date($this->dateTimeFormat,times::getTimeStamp()), $_SERVER['REQUEST_URI'] );
		}else{
			$this->log .= sprintf("[%s] PHP SCRIPT: %s\n",
			date($this->dateTimeFormat), $_SERVER['SCRIPT_FILENAME'] );
		}

		// 注册脚本结束时要运行的方法，将缓存的日志内容写入文件
		register_shutdown_function( array( &$this, 'writeLog' ) );
	}

	/**
	 * 追加日志信息
	 *
	 * @param string $msg
	 * @param int $level
	 * @access public
	 */
	function appendLog( $msg,$level=0 ){
		$level = intval( $level );
		if ( !isset( $this->errorLevel[$level] ) ) { return; }
		$msg = sprintf( "[%s] [%s] %s\n", date( $this->dateTimeFormat ,times::getTimeStamp()), $this->errorLevel[$level], $msg );
		$this->log .= $msg;
	}

	/**
	 * 输出日志信息
	 *
	 * @access public
	 */
	function writeLog(){
		switch( strtolower( $this->type ) ){
			case "screen": //直接打出到屏幕
				echo $this->log;
				break;
			case "html": //打出到浏览器
				echo "<pre>";
				echo nl2br( $this->log );
				echo "</pre>";
				break;
			case "file": //记录到文件

				//如果日志存放目录不存，尝试创建
				$basedir=dirname($this->file);
				if(!is_dir($basedir)) files::mkdirs($basedir);

				//如果文件存在记录文件大小
				if ( file_exists( $this->file ) ) {
					$filesize = filesize( $this->file );
				} else { //文件不存在
					$dir = dirname( $this->file );
					$dir = realpath( $dir );
					//文件目录不存在或者不可写
					if( !is_dir( $dir ) && !is_writable($dir) )
					$this->file = $_ENV["TEMP"]."/".basename( $this->file );
					$filesize = 0;
				}
				$maxsize = $this->maxSize;
				if ($maxsize >= 512) {
					$maxsize = $maxsize * 1024;
					if ( $filesize >= $maxsize ) {
						// 使用新的日志文件名
						$pathinfo = pathinfo($this->file);
						$newFilename = $pathinfo['dirname'] . "/" .
						basename($pathinfo['basename'], '.' . $pathinfo['extension']) .
						date('-Ymd-His') . '.' . $pathinfo['extension'];
						rename( $this->file, $newFilename );
					}
				}
				$fp = fopen($this->file, 'a');
				if (!$fp) { return; }
				flock( $fp, LOCK_EX );
				fwrite( $fp, str_replace("\r", '', $this->log ) );
				flock($fp, LOCK_UN);
				fclose($fp);
				break;
		}
	}
}
?>