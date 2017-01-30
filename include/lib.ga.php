<?php
/**
 * 使用google analytics api 协议获取统计信息
 * http://code.google.com/intl/zh-CN/apis/analytics/docs/gdata/2.0/gdataProtocol.html
 *
 * @author weiqi
 *
 */
class ga {

	protected $auth;
	 
	public function login($email, $password) {
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		 
		$data = array(
            'accountType' => 'GOOGLE',
            'Email' => $email,
            'Passwd' => $password,
            'service' => 'analytics',
            'source' => 'curl-dataFeed-v2'
            );
             
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            //debug::d($info);debug::d($output);
            
            $this->auth = '';
            if($info['http_code'] == 200) {
            	preg_match('/Auth=(.*)/', $output, $matches);
            	if(isset($matches[1])) {
            		$this->auth = $matches[1];
            	}
            }
             
            return $this->auth != '';
             
	}
	 
	/**
	 * 使用url直接获取统计数据
	 * 具体查询协议参见:
	 * http://code.google.com/intl/zh-CN/apis/analytics/docs/gdata/gdataReferenceDataFeed.html#dataRequest
	 *
	 "https://www.google.com/analytics/feeds/data
		?ids=ga:12345
		&dimensions=ga:source,ga:medium
		&metrics=ga:visits,ga:bounces
		&sort=-ga:visits
		&filters=ga:medium%3D%3Dreferral
		&segment=gaid::10 OR dynamic::ga:medium%3D%3Dreferral
		&start-date=2008-10-01
		&end-date=2008-10-31
		&start-index=10
		&max-results=100
		&v=2
		&prettyprint=true"

	 *
	 * @param string $url
	 * @return xml data
	 */
	public function call($url) {

		$headers = array("Authorization: GoogleLogin auth=$this->auth");
	         
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
		$output=xml::xml2array($output);
		
        if($info['http_code'] == 200) {
        	return $output;
        }
        else {
        	return false;
        }
	         
	}

	public function get($config){
		$api = new ga();
		if($api->login($config['user'],$config['pass'])) {
				
			$url=array();
			$url[]="https://www.google.com/analytics/feeds/data";
			$url[]="?ids=ga:".$config['id'];
			$url[]="&start-date=2006-07-15";
			$url[]="&end-date=".$config['date'];
			//$url[]="&dimensions=ga:source,ga:medium";
			$url[]="&metrics=ga:pageviews,ga:visits,ga:visitors";
			//$url[]="&sort=-ga:visits";
			//$url[]="&filters=ga:medium%3D%3Dreferral";
			//$url[]="&max-results=5";
			$url[]="&prettyprint=true";
		
			$feedUri=implode('',$url);
			$xml = $api->call( $feedUri );
			
			return $xml;
		}
		return false;
	}
	
	/**
	 * TODO
	 * 使用　https://www.google.com/analytics/feeds/accounts/default　得到全部Table ID
	 */	 
}
