<?php
/**
 * 意见反馈
 */
class freeback{
	public function index(){
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$content = strim($GLOBALS['request']['content']);//反馈内容
		$mobile = strim($GLOBALS['request']['_m']);//反馈内容
		$linked = strim($GLOBALS['request']['lined']); //联系方式

		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);

		if($mobile){
			if($mobile == 'ios'){
				$terminal = 4;
			}else if($mobile == 'android'){
				$terminal = 3;
			}
		}else{
			$terminal = 2;
		}

		$data["user_id"] = $user_id;
		$data["content"] = $content;
		$data["create_time"] = time();
		$data["terminal"] = $terminal;
		$data["linked"] = $linked ;

		$GLOBALS['db']->autoExecute(DB_PREFIX."feedback",$data,"INSERT");

		$root['response_code'] = 1;
		$root['show_err'] = "反馈成功";
		output($root);
	}
}
?>