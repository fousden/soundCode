<?php

class save_reset_pwd {

    public function index() {
	$mobile = addslashes(htmlspecialchars(trim($GLOBALS['request']['mobile'])));
	$verify = addslashes(htmlspecialchars(trim($GLOBALS['request']['mobile_code'])));
	$user_pwd = addslashes(htmlspecialchars(trim($GLOBALS['request']['user_pwd'])));
	$user_pwd_confirm = addslashes(htmlspecialchars(trim($GLOBALS['request']['user_pwd_confirm'])));

	$root = array();

	if ($user_pwd != $user_pwd_confirm) {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['USER_PWD_CONFIRM_ERROR'];
	    output($root);
	}

	if ($user_pwd == null || $user_pwd == '') {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['USER_PWD_ERROR'];
	    output($root);
	}


	if ($verify == "") {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'];
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

	$sql = "select id from ".DB_PREFIX."user where mobile = '".$mobile."' and is_delete = 0";
	$user_id = $GLOBALS['db']->getOne($sql);
	$code = $GLOBALS['db']->getOne("select verify_code from " . DB_PREFIX . "mobile_verify_code where mobile = '" . $mobile . "' and create_time>=" . (TIME_UTC - 300) . " ORDER BY id DESC");
	if ($code != $verify) {
	    $root['response_code'] = 0;
	    $root['show_err'] = $GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'];
	    output($root);
	} else {
	    $GLOBALS['db']->query("update " . DB_PREFIX . "user set user_pwd='" . md5($user_pwd) . "' where id = " . $user_id);
	    $root['response_code'] = 1;
	    $root['show_err'] = "密码更新成功!";
	    $root['sql'] = $sql;
	    output($root);
	}
	$root['program_title'] = "修改密码";
	output($root);
    }

}

?>