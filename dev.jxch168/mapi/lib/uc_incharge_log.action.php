<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_incharge_log{
	public function index(){
		$root = array();

		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$root['email'] = $email;
		$root['pwd'] = $pwd;
		$page = intval($GLOBALS['request']['page']);
		$withdrawals = intval($GLOBALS["request"]["withdrawals"]);
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$bank_num = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_bank where user_id=$user_id");
			if($GLOBALS['user_info']['real_name']==""){
				$root['response_code'] = 2;
				$root['show_err'] ="没有进行实名认证,请认证后重试!";
			}else if($bank_num == 0){
				$root['response_code'] = 3;
				$root['show_err'] ="没有绑定银行卡请绑定后重试!";
			}else{
				$root['response_code'] = 1;

				require APP_ROOT_PATH.'app/Lib/uc_func.php';

				$root['user_login_status'] = 1;
				$root['response_code'] = 1;

				if($page==0)
					$page = 1;
				$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");

                                sleep(1);
				if($withdrawals == 1){
					$result = get_user_carry($limit,$user_id);
                                        //如果是数字 则更新描述信息
                                        $old_resp_desc = $GLOBALS['db']->getOne("select resp_desc from ".DB_PREFIX."user_carry where mchnt_txn_ssn = '".$_REQUEST['mchnt_txn_ssn']."'");
                                }else{
					$result = get_user_incharge_log($limit,$user_id,'');
                                        //如果是数字 则更新描述信息
                                        $old_resp_desc = $GLOBALS['db']->getOne("select resp_describle from ".DB_PREFIX."payment_notice where notice_sn = '".$_REQUEST['mchnt_txn_ssn']."'");
                                }
				$root['withdrawals'] = $withdrawals;
				$root['item'] = $result['list'];
				$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
			}
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}

                $root['withdrawals'] = $withdrawals;
		$root['program_title'] = "充值日志";
		$root['resp_code'] = $_REQUEST['resp_code'];
		$root['mchnt_txn_ssn'] = $_REQUEST['mchnt_txn_ssn'];

		$payment=MO("Payment");
		$root['resp_desc'] = $payment->getFuyouRemind($_REQUEST['resp_code']);

                if(is_numeric($old_resp_desc)){
                    if($withdrawals == 1){
                        //更新提现记录提示信息状态
                        $paymentModel->updateCarrys($_REQUEST['mchnt_txn_ssn'],$_REQUEST['resp_code']);
                    }else{
                        //更新充值记录提示信息状态
                        $payment->updatePayments($_REQUEST['mchnt_txn_ssn'],$_REQUEST['resp_code']);
                    }
                }else{
                    $root['resp_desc'] = $old_resp_desc;
                }

		output($root);
	}
}
?>
