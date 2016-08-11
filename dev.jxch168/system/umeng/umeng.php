<?php
require_once(dirname(__FILE__) . '/' . 'android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'ios/checkIOSMsg.php');

class UMeng {
	protected $appkey           = NULL; 
	protected $appMasterSecret  = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($key,$secret) {
		$this->appkey = $key;
		$this->appMasterSecret = $secret;
		$this->timestamp = strval(time());
	}
	function sendIOSBroadcast($msg,$insert_id) {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("alert", $msg['content']);
            $brocast->setPredefinedKeyValue("badge", 1);
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", UMENG_MODE);
            $brocast->setPredefinedKeyValue("description", $msg['id'].'-'.$insert_id);
            $brocast->setPredefinedKeyValue("thirdparty_id", $msg['id']);
            // Set customized fields
            if($msg['type']){
                $brocast->setCustomizedField("type", $msg['type']);
                $brocast->setCustomizedField("data", $msg['data']); 
            }
            $data = $brocast->send();
            return $data;
	}
        function sendIOSUnicast($device_tokens,$msg,$insert_id) {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens); 
            $unicast->setPredefinedKeyValue("alert", $msg['content']);
            $unicast->setPredefinedKeyValue("badge", 1);
            $unicast->setPredefinedKeyValue("production_mode", UMENG_MODE);
            $unicast->setPredefinedKeyValue("description", $msg['id'].'-'.$insert_id);
            $unicast->setPredefinedKeyValue("thirdparty_id", $msg['id']);
            // Set customized fields
            if($msg['type']){
                $unicast->setCustomizedField("type", $msg['type']);
                $unicast->setCustomizedField("data", $msg['data']);
            }
            $data = $unicast->send();
            return $data;
	}
        
	function checkMsg($msg_id) {
            $checkmsg = new checkIOSMsg();
            $checkmsg->setAppMasterSecret($this->appMasterSecret);
            $checkmsg->setCheckMsgData("appkey",           $this->appkey);
            $checkmsg->setCheckMsgData("timestamp",        $this->timestamp);
            $checkmsg->setCheckMsgData("task_id" ,         $msg_id);
            $data=$checkmsg->check();
            return $data;
	}
	function sendAndroidBroadcast($msg=array(),$insert_id) {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           $msg['title']);
            $brocast->setPredefinedKeyValue("title",            $msg['title']);
            $brocast->setPredefinedKeyValue("text",             $msg['content']);

            $brocast->setPredefinedKeyValue("after_open",       'go_app');
            $brocast->setPredefinedKeyValue("custom",       '0000');
            $brocast->setPredefinedKeyValue("production_mode", UMENG_MODE);
            $brocast->setPredefinedKeyValue("description", $msg['id'].'-'.$insert_id);
            $brocast->setPredefinedKeyValue("thirdparty_id", $msg['id']);
            $brocast->setExtraField("type", $msg['type']);
            $brocast->setExtraField("data", $msg['data']);

            $data = $brocast->send();
            return $data;
	}

	function sendAndroidUnicast($msg=array(),$insert_id) {//单播
            $unicast = new AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $msg['device_token']);
            $unicast->setPredefinedKeyValue("ticker",           $msg["title"]);
            $unicast->setPredefinedKeyValue("title",            $msg["title"]);
            $unicast->setPredefinedKeyValue("text",             $msg["content"]);
            $unicast->setPredefinedKeyValue("after_open",       "go_app");
            $unicast->setPredefinedKeyValue("production_mode", UMENG_MODE);
            $unicast->setPredefinedKeyValue("description", $msg['id'].'-'.$insert_id);
            $unicast->setPredefinedKeyValue("thirdparty_id", $msg['id']);
            $unicast->setExtraField("type",       $msg['type']);
            $unicast->setExtraField("data",       $msg['data']);
            $data = $unicast->send();
            return $data;
	}
}

