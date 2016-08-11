<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/uc_func.php';

class uc_do_incharge{

	public function index(){
		$payment_id = intval($_REQUEST['payment_id']);
		$money = floatval($_REQUEST['money']);
                $mobile      = trim($GLOBALS['request']['_m']);
                if ($mobile) {
                    if ($mobile == "android") {
                        $terminal = 3;
                    } else if ($mobile == "ios") {
                        $terminal = 4;
                    }
                } else {
                    $terminal = 2;
                }

		$bank_id = addslashes(htmlspecialchars(trim($_REQUEST['bank_id'])));
		$memo = addslashes(htmlspecialchars(trim($_REQUEST['memo'])));
		$pingzheng = replace_public(trim($_REQUEST['pingzheng']));

		$email = strim($_REQUEST['email']);//用户名或邮箱
		$pwd = strim($_REQUEST['pwd']);//密码

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$GLOBALS['user_info'] = $user;
		$status = getInchargeDone($payment_id,$money,$bank_id,$memo,$pingzheng,$terminal);
		output($status);
	}
}
?>
