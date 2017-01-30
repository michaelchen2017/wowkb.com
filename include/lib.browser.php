<?php
/**
# Browser Emulating file functions v2.0.1
# (c) Kai Blankenhorn
# www.bitfolge.de/browseremulator
# kaib@bitfolge.de

// example code

$be = new browser(); 
$be->addHeaderLine("Referer", "http://previous.server.com/"); 
$be->addHeaderLine("Accept-Encoding", "x-compress; x-zip"); 
$be->addPostData("Submit", "OK"); 
$be->addPostData("item", "42"); 
$be->setAuth("admin", "secretpass"); 
// also possible: 
// $be->setPort(10080); 

$file = $be->fopen("http://restricted.server.com:10080/somepage.html"); 
$response = $be->getLastResponseHeaders(); 

while ($line = fgets($file, 1024)) { 
    // do something with the file 
} 
fclose($file); 

*/

/**
 * browser class. Provides methods for opening urls and emulating 
 * a web browser request.
 **/

class browser {
	
	var $headerLines = Array();
	var $postData = Array();
	var $authUser = "";
	var $authPass = "";
	var $port;
	var $lastResponse = Array();
	var $debug = false;
	
	function browser() {
		$this->resetHeaderLines();
		$this->resetPort();
	}
	
	static function getFile($url,$refer=""){
		if( empty($refer) ){
			$arr = parse_url($url);
			$refer = "{$arr['scheme']}://{$arr['host']}";
		}
		$be = new browser(); 
		$be->addHeaderLine( "Referer", $refer ); 
		$file = $be->fopen( $url ); 
		
		$data='';
		while ($line = fgets($file, 1024)) { 
		    $data .= $line;
		} 
		fclose($file); 
		
		return $data;
	}
	
	/**
	* Adds a single header field to the HTTP request header. The resulting header
	* line will have the format
	* $name: $value\n
	**/
	function addHeaderLine($name, $value) {
		$this->headerLines[$name] = $value;
	}
	
	/**
	* Deletes all custom header lines. This will not remove the User-Agent header field,
	* which is necessary for correct operation.
	**/
	function resetHeaderLines() {
		$this->headerLines = Array();
		
		/*******************************************************************************/
		/**************    YOU MAX SET THE USER AGENT STRING HERE    *******************/
		/*                                                                             */
		/* default is "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",            */
		/* which means Internet Explorer 6.0 on WinXP                                  */
		
		$this->headerLines["User-Agent"] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
		
		/*******************************************************************************/
		
	}
	
	/**
	* Add a post parameter. Post parameters are sent in the body of an HTTP POST request.
	**/
	function addPostData($name, $value) {
		$this->postData[$name] = $value;
	}
	
	/**
	* Deletes all custom post parameters.
	**/
	function resetPostData() {
		$this->postData = Array();
	}
	
	/**
	* Sets an auth user and password to use for the request.
	* Set both as empty strings to disable authentication.
	**/
	function setAuth($user, $pass) {
		$this->authUser = $user;
		$this->authPass = $pass;
	}
	
	/**
	* Selects a custom port to use for the request.
	**/
	function setPort($portNumber) {
		$this->port = $portNumber;
	}
	
	/**
	* Resets the port used for request to the HTTP default (80).
	**/
	function resetPort() {
		$this->port = 80;
	}
	
	/**
	* Make an fopen call to $url with the parameters set by previous member
	* method calls. Send all set headers, post data and user authentication data.
	* Returns a file handle on success, or false on failure.
	**/
	function fopen($url) {
		$this->lastResponse = Array();
		
		preg_match("~([a-z]*://)?([^:^/]*)(:([0-9]{1,5}))?(/.*)?~i", $url, $matches);
		
		$protocol = $matches[1];
		$server = $matches[2];
		$port = empty($matches[4])?'':$matches[4];
		$path = empty($matches[5])?"/":$matches[5];
		if ($port!="") {
			$this->setPort($port);
		}
		
		$socket = false;
		$socket = fsockopen($server, $this->port);
		if ($socket) {
			$this->headerLines["Host"] = $server;
			
			if ($this->authUser!="" && $this->authPass!="") {
				$this->headerLines["Authorization"] = "Basic ".base64_encode($this->authUser.":".$this->authPass);
			}
			
			if (count($this->postData)==0) {
				$request = "GET $path HTTP/1.0\r\n";
			} else {
				$request = "POST $path HTTP/1.0\r\n";
			}
			
			if ($this->debug) echo $request;
		    fputs ($socket, $request);
			
			if (count($this->postData)>0) {
				$PostStringArray = Array();
				foreach ($this->postData AS $key=>$value) {
					$PostStringArray[] = "$key=$value";
				}
				$PostString = join("&", $PostStringArray);
				$this->headerLines["Content-Length"] = strlen($PostString);
			}
			
			foreach ($this->headerLines AS $key=>$value) {
				if ($this->debug) echo "$key: $value\n";
			    fputs($socket, "$key: $value\r\n");
			}
			if ($this->debug) echo "\n";
			fputs($socket, "\r\n");
			if (count($this->postData)>0) {
				if ($this->debug) echo "$PostString";
				fputs($socket, $PostString."\r\n");
			}
		}
		if ($this->debug) echo "\n";
		if ($socket) {
			$line = fgets($socket, 1000);
			if ($this->debug) echo $line;
			$this->lastResponse[] = $line;
			$status = substr($line,9,3);
			while (trim($line = fgets($socket, 1000)) != ""){
				if ($this->debug) echo "$line";
				$this->lastResponse[] = $line;
				if ($status=="401" AND strpos($line,"WWW-Authenticate: Basic realm=\"")===0) {
					fclose($socket);
					return FALSE;
				}
			}
		}
		return $socket;
	}
	
	/**
	* Make an file call to $url with the parameters set by previous member
	* method calls. Send all set headers, post data and user authentication data.
	* Returns the requested file as an array on success, or false on failure.
	**/
	function file($url) {
		$file = Array();
		$socket = $this->fopen($url);
		if ($socket) {
			$file = Array();
			while (!feof($socket)) {
				$file[] = fgets($socket, 10000);
			}
		} else {
			return FALSE;
		}
		return $file;
	}
	
	function getLastResponseHeaders() {
		return $this->lastResponse;
	}
}
?>