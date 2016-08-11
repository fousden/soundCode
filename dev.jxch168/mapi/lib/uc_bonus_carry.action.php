<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_bonus_carry {
 	//红包提现申请
    public function index(){
    	$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$bonus_id = strim($GLOBALS['request']['bonus_id']);

    	//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id > 0){

	        //如果没有充值或者投资 则不允许提现
	        $payment_notice_num = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "payment_notice where user_id = " . $user_id . " AND is_paid = 1");
	        $deal_load_num      = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "deal_load where user_id = " . $user_id . " AND is_auto = 0");
	        if (!$deal_load_num) {
	            $root['response_code'] = 0;
	        	$root['show_err'] ="您没有任何投资记录，暂时无法提现！";
	        }
	        if (!$payment_notice_num) {
	            $root['response_code'] = 0;
	        	$root['show_err'] ="您没有任何充值记录，暂时无法提现！";
	        }

	        $obj = MO('Bonus');
			$res = $obj->bonusWithdrawals($GLOBALS['user_info']['id'], $bonus_id);
	        if ($res === true) {
	            $root['response_code'] = 1;
	        	$root['show_err'] = "您已成功领取红包，预计3个工作日内到账。";
	        } else {
	        	$root['response_code'] = 0;
	        	$root['show_err'] = $res;
	        }
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
        
        output($root);
    }
}