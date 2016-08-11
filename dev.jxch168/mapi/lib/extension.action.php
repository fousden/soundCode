<?php

/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 15/7/20
 * Time: 下午1:26
 */

/**
 * 
 * 点入广告平台接入
 * 手机端数据应答
 *
 */
class extension {

    public function index() {

	$data['udid'] = strtoupper($GLOBALS['request']['udid']);    //我们平台唯一
	$data['source'] = $GLOBALS['request']['source'];    //来源
	$data['app'] = $GLOBALS['request']['app'];
	$data['mobile'] = $GLOBALS['request']['_m'];
	$data['ip'] = $GLOBALS['request']['__ip__'];

	$data['pburl'] = $GLOBALS['request']['pburl'];
	$data['drkey'] = $GLOBALS['request']['drkey'];  //点入唯一
        //点入
	if (!$data['pburl'] && $data['drkey']) {
	    $data['pburl'] = 'http://api.mobile.dianru.com/callback/index.do?drkey=' . $data['drkey'];
         //安沃
	}else if (!$data['pburl'] && $data['source'] == 'adwo') {
	    $data['pburl'] = "http://offer.adwo.com/iofferwallcharge/ia?adalias=adwojxch&idfa={$data['udid']}";
	}
	$extension_data = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "mobile_extension where udid='" . $data['udid'] . "'");
	if($data['source'] && $extension_data){
	    $root['response_code'] = 0;
	    $root['err_msg'] = "The udid already exists";
	    output($root);
	}
	if ($data['mobile'] == 'android') {
	    unset($data['pburl']);
	    if ($extension_data && $extension_data['source'] == $data['source']) {
                $data = $extension_data;
		$data['hit'] = $extension_data["hit"] + 1;
		$data['test_time'] = time();
		$root['response_code'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_extension", $data, "UPDATE", "udid='" . $extension_data["udid"] . "'");
	    } else {
		$data['mobile'] = 3;
		$data['create_time'] = time();
		$data['create_date'] = date("Y-m-d");
		$data['year'] = date("Y");
		$data['month'] = date("m");
		$data['day'] = date("d");
		$data['week'] = date("W");
		$data['state'] = 1;
		$data['type'] = 1;
		$data['hit'] = 0;

		$root['response_code'] = 1;
		$GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_extension", $data);
	    }
	} else {
	    if ($extension_data) {
                $data = $extension_data;
		$data['test_time'] = time();
		if($extension_data['state']==0){
		     $data['state']= 1;     //软件被打开
		}
		$data['type'] = 0;
		$data['hit'] = $extension["hit"] + 1;

		$root['response_code'] = 1;

		$GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_extension", $data, "UPDATE", "udid='" . $extension_data["udid"] . "'");

		file_put_contents(APP_ROOT_PATH . 'log/extension/' . date('Y-m-d') . '_outinput_callback.log', "POST:[" . json_encode($data) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
	    } else {
		if (!$data['source']) {
		    $data['source'] = 'app_store';
		}
		$data['mobile'] = 4;
		$data['create_time'] = time();
		$data['create_date'] = date("Y-m-d");
		$data['year'] = date("Y");
		$data['month'] = date("m");
		$data['day'] = date("d");
		$data['week'] = date("W");
		$data['type'] = 0;

		$data['state'] = 0;

		$GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_extension", $data);

		$root['response_code'] = 1;

		file_put_contents(APP_ROOT_PATH . 'log/extension/' . date('Y-m-d') . '_appinput_callback.log', "POST:[" . json_encode($data) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
	    }
	}
	$root['err_msg'] = "ok";
	output($root);
    }

}

?>