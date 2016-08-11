<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_del_message
{
	public function index(){
		$email = strim($GLOBALS['request']['email']); //用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']); //密码
		$id = intval($GLOBALS['request']['id']); //唯一主建
		
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0 ){

			$sql_id = "";
			if($id > 0)
				$sql_id = ' and id = '.$id;
			
			$sql = 'update '.DB_PREFIX.'msg_box set is_delete = 1 where to_user_id = '.$user_id . $sql_id;
			$GLOBALS['db']->query($sql);

			$root['show_err'] = '删除成功';
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