<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_do_message
{
	public function index(){
		$root = array();
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		$id = intval($GLOBALS['request']['id']);

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$sql = "update ".DB_PREFIX."msg_box set is_read = 1 where id = $id";
			$GLOBALS['db']->query($sql);
			
			$root['show_err'] = '已阅';
			$root['response_code'] = 1;
			$root['user_login_status'] = 1;
		}
		else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);
	}
		
}