<?php

require APP_ROOT_PATH.'system/sms_mobile.php';

class send_reset_pay_code{
	public function index()
	{
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码		
		
		
		if(app_conf("SMS_ON")==0)
		{
			$root['response_code'] = 0;
			$root['show_err'] = $GLOBALS['lang']['SMS_OFF'];//短信未开启
			output($root);
		}
				
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		$root['user_id'] = $user_id;
		if ($user_id >0){
			$mobile = $user['mobile'];
			$code = intval($user['bind_verify']);
			
			if($mobile == '')
			{
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['MOBILE_EMPTY_TIP'];
				output($root);
			}
			
			if(!check_mobile($mobile))
			{
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'];
				output($root);
			}
			
			if(!check_ipop_limit(CLIENT_IP,"mobile_verify",60,0))
			{
				$root['response_code'] = 0;
				$root['show_err'] = $GLOBALS['lang']['MOBILE_SMS_SEND_FAST']; //短信发送太快
				output($root);
			}
	
			
			//删除超过5分钟的验证码
                        $GLOBALS['db']->query("DELETE FROM ".DB_PREFIX."mobile_verify_code WHERE mobile = '".$mobile."' and create_time <=".TIME_UTC-300);

                        $verify_code = $GLOBALS['db']->getOne("select verify_code from ".DB_PREFIX."mobile_verify_code where mobile = '".$mobile."' and create_time>=".(TIME_UTC-300)." ORDER BY id DESC");
                        if(intval($verify_code) == 0)
                        {
                                //如果数据库中存在验证码，则取数据库中的（上次的 ）；确保连接发送时，前后2条的验证码是一至的.==为了防止延时
                                //开始生成手机验证
                                $verify_code = rand(1111,9999);
                                $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_verify_code",array("verify_code"=>$verify_code,"mobile"=>$mobile,"create_time"=>TIME_UTC,"client_ip"=>CLIENT_IP),"INSERT");
                        }

			$result = send_verify_sms_new($mobile,'TPL_SMS_PAYMENT_PWD_VERIFY_CODE',$verify_code,null,false,true);//
			$root['response_code'] = $result['status'];			
			
			if ($root['response_code'] == 1){
				$root['show_err'] = $GLOBALS['lang']['MOBILE_VERIFY_SEND_OK'];
			}else{
				$root['show_err'] = $result['msg'];
				if ($root['show_err'] == null || $root['show_err'] == ''){
					$root['show_err'] = "验证码发送失败";
				}
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