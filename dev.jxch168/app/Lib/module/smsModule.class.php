<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
require APP_ROOT_PATH.'system/sms_mobile.php';

class smsModule extends SiteBaseModule
{
	public function subscribe()
	{
		$GLOBALS['tmpl']->display("sms_subscribe.html");
	}
	public function do_subscribe()
	{
		global $deal_city;
		//开始发送验证码
		if(check_ipop_limit(CLIENT_IP,"sms_send_code",intval(app_conf("SUBMIT_DELAY"))))
		{
			$mobile = addslashes(trim($_REQUEST['mobile']));
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{
				$result['type'] = 0;
				$result['message'] = $GLOBALS['lang']['VERIFY_CODE_ERROR'];
				ajax_return($result);
			}

			$mobile_subscribe = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_list where mobile='".$mobile."'");
			if(!$mobile_subscribe)
			{
				$mobile_item['mobile'] = $mobile;
				$mobile_item['city_id'] = $deal_city['id'];
				$mobile_item['is_effect'] = 0;
				$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile_item,'INSERT','','SILENT');
				$id = $GLOBALS['db']->insert_id();
				if($id)
				{
					$mobile_subscribe = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_list where id='".$id."'");
				}
				else
				{
					$result['type'] = 0;
					$result['message'] = $GLOBALS['lang']['SUBSCRIBE_FAILED'];
					ajax_return($result);
				}
			}

			if($mobile_subscribe['is_effect']==1)
			{
				//已经订阅
				$result['type'] = 2;
				ajax_return($result);
			}
			else
			{
				$code = rand(1111,9999);
				$GLOBALS['db']->query("update ".DB_PREFIX."mobile_list set verify_code = '".$code."' where id = ".$mobile_subscribe['id']);
				send_verify_sms_new($mobile_subscribe['mobile'],'TPL_SMS_VERIFY_CODE',$code);
				$result['type'] = 1;
				ajax_return($result);
			}
		}
		else
		{
			$result['type'] = 0;
			$result['message'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
			ajax_return($result);
		}

	}

	public function do_subscribe_verify()
	{
		$code = addslashes(trim($_REQUEST['code']));
		$mobile = addslashes(trim($_REQUEST['mobile']));
		$mobile_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_list where mobile = '".$mobile."' and verify_code = '".$code."'");
		if($mobile_item)
		{
			$mobile_item['is_effect'] = 1;
			$mobile_item['verify_code'] = '';
			$GLOBALS['db']->autoExecute(DB_PREFIX."mobile_list",$mobile_item,'UPDATE','id='.$mobile_item['id'],'SILENT');
			$result['type'] = 1;
			ajax_return($result);
		}
		else
		{
			$result['type'] = 0;
			$result['message'] = $GLOBALS['lang']['VERIFY_CODE_ERROR'];
			ajax_return($result);
		}
	}

	public function unsubscribe()
	{
		$GLOBALS['tmpl']->display("sms_unsubscribe.html");
	}

	public function do_unsubscribe()
	{
		//开始发送验证码
		if(check_ipop_limit(CLIENT_IP,"sms_send_code_un",intval(app_conf("SUBMIT_DELAY"))))
		{
			$mobile = addslashes(trim($_REQUEST['mobile']));
			$verify = md5(trim($_REQUEST['verify']));
			$session_verify = es_session::get('verify');
			if($verify!=$session_verify)
			{
				$result['type'] = 0;
				$result['message'] = $GLOBALS['lang']['VERIFY_CODE_ERROR'];
				ajax_return($result);
			}

			$mobile_subscribe = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_list where mobile='".$mobile."' and is_effect = 1");
			if(!$mobile_subscribe)
			{
				$result['type'] = 0;
				$result['message'] = $GLOBALS['lang']['MOBILE_NOT_SUBSCRIBE'];
				ajax_return($result);
			}



			$code = rand(1111,9999);
			$GLOBALS['db']->query("update ".DB_PREFIX."mobile_list set verify_code = '".$code."' where id = ".$mobile_subscribe['id']);
			send_verify_sms_new($mobile_subscribe['mobile'],'TPL_SMS_VERIFY_CODE',$code);
			$result['type'] = 1;
			ajax_return($result);

		}
		else
		{
			$result['type'] = 0;
			$result['message'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
			ajax_return($result);
		}
	}

	public function do_unsubscribe_verify()
	{
		$code = addslashes(trim($_REQUEST['code']));
		$mobile = addslashes(trim($_REQUEST['mobile']));
		$mobile_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."mobile_list where mobile = '".$mobile."' and verify_code = '".$code."'");
		if($mobile_item)
		{
			$GLOBALS['db']->query("delete from ".DB_PREFIX."mobile_list where id = ".$mobile_item['id']);
			$result['type'] = 1;
			ajax_return($result);
		}
		else
		{
			$result['type'] = 0;
			$result['message'] = $GLOBALS['lang']['VERIFY_CODE_ERROR'];
			ajax_return($result);
		}
	}
}
?>