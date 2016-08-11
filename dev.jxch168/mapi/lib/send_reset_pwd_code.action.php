<?php

require APP_ROOT_PATH . 'system/sms_mobile.php';

class send_reset_pwd_code {

    public function index() {
	$mobile = addslashes(htmlspecialchars(trim($GLOBALS['request']['mobile'])));
	$root = array();
	if (app_conf("SMS_ON") == 0) {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['SMS_OFF'];
	    output($root);
	}
	if ($mobile == '') {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
	    output($root);
	}
	if (!check_mobile($mobile)) {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
	    output($root);
	}
	if (!check_ipop_limit(CLIENT_IP, "mobile_verify", 60, 0)) {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST'];
	    output($root);
	}
	$sql = "select id,bind_verify from " . DB_PREFIX . "user where mobile = '" . $mobile . "' and is_delete = 0";
	$user_info = $GLOBALS['db']->getRow($sql);
	$user_id = intval($user_info['id']);
	$GLOBALS['db']->query("DELETE FROM " . DB_PREFIX . "mobile_verify_code WHERE mobile = '" . $mobile . "' and create_time <= (now()-300)");
	if ($user_id == 0) {
	    $root['response_code'] = 0;
	    $root['show_err'] = '手机号码不存在或被禁用';
	    output($root);
	}
	//开始生成手机验证
	if ($code == 0) {
	    //已经生成过了，则使用旧的验证码；反之生成一个新的
	    $code = rand(1111, 9999);
	    $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",array("verify_code"=>$code,"mobile"=>$mobile,"create_time"=>TIME_UTC,"client_ip"=>CLIENT_IP),"INSERT");
	}

	//使用立即发送方式
	$result = send_verify_sms_new($mobile, 'TPL_SMS_SAVE_PWD_VERIFY_CODE', $code, null, false,true); //

	$root['response_code'] = $result['status'];

	if ($root['response_code'] == 1) {
	    $root['show_err'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
	} else {
	    $root['show_err'] = $result['msg'];
	    if ($root['show_err'] == null || $root['show_err'] == '') {
		$root['show_err'] = "验证码发送失败";
	    }
	}
//	$root['post_type'] = empty(trim($GLOBALS['request']['post_type']))?'':trim($GLOBALS['request']['post_type']);
	output($root);
    }

}

?>