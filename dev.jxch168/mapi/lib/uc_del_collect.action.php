<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_del_collect
{
	public function index(){
		
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		$ids = strim($GLOBALS['request']['id']);
				
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			
			$root['user_login_status'] = 1;									
		
			$sql = "delete from ".DB_PREFIX."deal_collect where deal_id in (".$ids.") and user_id = ".$user_id;
			//$root['sql'] = $sql;
			$GLOBALS['db']->query($sql);
			$root['response_code'] = 1;
			$root['show_err'] = "取消关注";
			/*
			if($GLOBALS['db']->affected_rows()){
				$root['response_code'] = 1;
				$root['show_err'] = $GLOBALS['lang']['DELETE_SUCCESS'];
			}
			else{
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['删除失败'];
			}
			*/
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);		
	}
}
?>
