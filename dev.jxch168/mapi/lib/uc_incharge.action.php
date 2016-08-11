<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc_func.php';

class uc_incharge{

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
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;

                        $result = check_mapi_user_info($user,0);
                        if( $result['response_code'] == 0){
                            $root['response_code'] = $result['response_code'];
                            $root['show_err'] = $result['show_err'];
                            $root['program_title'] = "充值";
                            output($root);
                        }


			//输出支付方式
			$payment_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name <> 'Account' and class_name <> 'Voucher' and class_name <> 'tenpayc2c' and online_pay = 1 order by sort desc");
			foreach($payment_list as $k=>$v)
			{
				if($v['class_name']=='Alipay')
				{
					$cfg = unserialize($v['config']);
					if($cfg['alipay_service']!=2)
					{
						unset($payment_list[$k]);
						continue;
					}
				}
				$directory = APP_ROOT_PATH."system/payment/";
				$file = $directory. $v['class_name']."_payment.php";
				if(file_exists($file))
				{
					require_once($file);
					$payment_class = $v['class_name']."_payment";
					$payment_object = new $payment_class();
					$payment_list[$k]['display_code'] = $payment_object->get_display_code();
					$payment_list[$k]['logo'] = substr($payment_list[$k]['logo'],1);
				}
				else
				{
					unset($payment_list[$k]);
				}
			}

			$root["payment_list"] = $payment_list;

			//判断是否有线下支付
			$below_payment = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."payment where is_effect = 1 and class_name = 'Otherpay'");
			if($below_payment){
				$directory = APP_ROOT_PATH."system/payment/";
				$file = $directory. $below_payment['class_name']."_payment.php";
				if(file_exists($file)) {
					require_once($file);
					$payment_class = $below_payment['class_name']."_payment";

					$payment_object = new $payment_class();
					$below_payment['display_code'] = $payment_object->get_display_code();
				}

				$root["below_payment"] = $below_payment;
			}
                        //充值说明
                        $root['incharge_desc']=  str_replace("\r\n", "<br>", $GLOBALS['m_config']['incharge_desc']);

		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}

		$root['program_title'] = "充值";
		output($root);
	}
}
?>
