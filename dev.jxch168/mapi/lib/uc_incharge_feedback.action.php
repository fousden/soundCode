<?php

class uc_incharge_feedback {

    public function index() {
	$root = array();
	
	$root['ajax'] = intval($GLOBALS['request']['ajax']);
	if ($root['ajax'] == 1) {
	    
	    $email = strim($GLOBALS['request']['email']); //用户名或邮箱
	    $pwd = strim($GLOBALS['request']['pwd']); //密码
	    $user = user_check($email, $pwd);
	    $user_id = intval($user['id']);
	    if ($user_id > 0) {
		$data['bank_id'] = strim($GLOBALS['request']['bank_id']);
		$data['fail_reason'] = intval($GLOBALS['request']['cause_id']);
		$data['feedback'] = strim($GLOBALS['request']['feedback']);
		if ($data['bank_id'] == 0) {
		    $root['response_code'] = 0;
		    $root['show_err'] = "请选择银行";
		    output($root);
		} else if ($data['fail_reason'] == 0) {
		    $root['response_code'] = 0;
		    $root['show_err'] = "请选择反馈原因";
		    output($root);
		} else {
		    $data['create_time'] = get_gmtime();
		    $data['order_id'] = strim($GLOBALS['request']['mchnt_txn_ssn']);
		    $data['user_agent'] = $GLOBALS['request']['HTTP_USER_AGENT'];
		    $data['user_name'] = $user['user_name'];
		    $data['payment_type'] = 1;
		    $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "incharge_fail_feedback", $data, "INSERT");
		    if ($res) {
			$root['response_code'] = 1;
			$root['show_err'] = "反馈成功";
		    } else {
			$root['response_code'] = 0;
			$root['show_err'] = "操作失败";
		    }
		    output($root);
		}
	    } else {
		$root['response_code'] = 0;
		$root['show_err'] = "未登录";
		$root['user_login_status'] = 0;
	    }
	} else {
	    
	    $root['email'] = strim($GLOBALS['request']['email']); //用户名或邮箱
	    $root['pwd'] = strim($GLOBALS['request']['pwd']); //密码
	    
	    $root['mchnt_txn_ssn'] = strim($GLOBALS['request']['mchnt_txn_ssn']);
	    $root['program_title'] = "充值失败反馈";
	    output($root);
	}
    }

}

?>
