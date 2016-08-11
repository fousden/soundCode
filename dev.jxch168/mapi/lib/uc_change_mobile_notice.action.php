<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_change_mobile_notice{
	public function index(){
		$root = array();

		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$root['email'] = $email;
		$root['pwd'] = $pwd;
		
                //检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
                    require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                    $fuyou = new fuyou();
                    $request = $GLOBALS['request'];
                    //更换手机号回调
                    $return = $fuyou->changeMobileBack($request);
                    if($return["status"] == 1){
                        $root['show_err'] ="更换手机号成功";
                        $root['response_code'] = 1;
                        $root['user_login_status'] = 1;
                    }else{
                        $root['show_err'] ="更换手机号失败，失败原因：".$return["resp_desc"];
                        $root['response_code'] = 0;
                        $root['user_login_status'] = 1;
                    }                 
		}else{
                    $root['response_code'] = 0;
                    $root['show_err'] ="未登录";
                    $root['user_login_status'] = 0;
		}               

		output($root);
	}
}
?>
