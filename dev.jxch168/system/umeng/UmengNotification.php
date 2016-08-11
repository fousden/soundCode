<?php

abstract class UmengNotification {
	// The host
	protected $host = "http://msg.umeng.com";
	// 上传路径
	protected $uploadPath = "/upload";
	// 消息发送地址
	protected $postPath = "/api/send";
        //消息查询地址
	protected $checkPath = "/api/status"; 
	// 服务器秘钥
	protected $appMasterSecret = NULL;

	/*
	 * $data is designed to construct the json string for POST request. Note:
	 * 1)The key/value pairs in comments are optional.  
	 * 2)The value for key 'payload' is set in the subclass(AndroidNotification or IOSNotification), as their payload structures are different.
	 */ 
	protected $data = array(
			"appkey"           => NULL,
			"timestamp"        => NULL,
			"type"             => NULL,
			//"device_tokens"  => "xx",
			"production_mode"  => NULL,
			"description"      => NULL,
			"thirdparty_id"    => NULL,
	);
        
        protected $checkdata = array(
			"appkey"           => NULL,
			"timestamp"        => NULL,
                        "task_id"          => NULL,
	);

	protected $DATA_KEYS    = array("appkey", "timestamp", "type", "device_tokens", "alias", "alias_type", "file_id", "filter", "production_mode",
								    "feedback", "description", "thirdparty_id");
	protected $POLICY_KEYS  = array("start_time", "expire_time", "max_send_num");

	function __construct() {

	}

	function setAppMasterSecret($secret) {
		$this->appMasterSecret = $secret;
	}
        function setCheckMsgData($key,$valus) {
		$this->checkdata[$key] = $valus;
	}
	
	//return TRUE if it's complete, otherwise throw exception with details
	function isComplete() {
		if ($this->appMasterSecret == ''){
                    $result['status'] = 0;
                    $result['info'] = "Please set your app master secret for generating the signature!";
                    return $result;
                }
		$this->checkArrayValues($this->data);
		return TRUE;
	}

	private function checkArrayValues($arr) {
		foreach ($arr as $key => $value) {
			if (is_null($value)){
                            $result['status'] = 0;
                            $result['info'] = $key . " is NULL!";
                            return $result;
                        }else if (is_array($value)) {
				$this->checkArrayValues($value);
			}
		}
	}

	// Set key/value for $data array, for the keys which can be set please see $DATA_KEYS, $PAYLOAD_KEYS, $BODY_KEYS, $POLICY_KEYS
	abstract function setPredefinedKeyValue($key, $value);

	//send the notification to umeng, return response data if SUCCESS , otherwise throw Exception with details.
	function send() {
            //check the fields to make sure that they are not NULL
            $inf = $this->isComplete();
            if(!$inf) return $inf;
            $url = $this->host . $this->postPath; 
            $postBody = json_encode($this->data);
            $sign = md5("POST" . $url . $postBody . $this->appMasterSecret);
            $url = $url . "?sign=" . $sign;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody );
            $result['info'] = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErrNo = curl_errno($ch);
            $curlErr = curl_error($ch);
            curl_close($ch);
            $code['info']      = $result['info'];
            $code['httpCode']  = $httpCode;
            $code['curlErrNo'] = $curlErrNo;
            $code['curlErr']   = $curlErr;
            $result['code'] = json_encode($code);
            if ($httpCode == "0") {
                     // Time out
                    $result['status'] = 0;
                    return $result;
            } else if ($httpCode != "200") {
                    // We did send the notifition out and got a non-200 response
                    $result['status'] = 0;
                    return $result;
            } else {
                    $result['status'] = 1;
                    return $result;
            }
    }
    
    function check() {
        $url = $this->host . $this->checkPath; 
        $postBody = json_encode($this->checkdata);
        $sign = md5("POST" . $url . $postBody . $this->appMasterSecret);
        $url = $url . "?sign=" . $sign;
  	$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody );
        $result['info'] = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);
        $code['info']      = $result['info'];
        $code['httpCode']  = $httpCode;
        $code['curlErrNo'] = $curlErrNo;
        $code['curlErr']   = $curlErr;
        $result['code'] = json_encode($code);
        return $result;
    }
	
}