<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_withdrawals{
	public function index(){
		$root = array();

		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);

		if ($user_id >0){
			$root['user_login_status'] = 1;
			$result = check_mapi_user_info($user,0);
                        if( $result['response_code'] == 0){
                            $root['response_code'] = $result['response_code'];
                            $root['show_err'] = $result['show_err'];
                            output($root);
                        }
                        //您更换银行卡申请正在审核中，暂时无法体现！
                        $bank_examine_num = 0;
                        $bank_examine_list = $GLOBALS['db']->getAll("select id,user_id,mchnt_txn_ssn from " . DB_PREFIX . "user_bank_examine where user_id = ".$user_id. " AND change_status = 0 AND is_effect = 1");
                        require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                        $fuyou = new fuyou();
                        foreach($bank_examine_list as $key=>$bank_examine){
                            //查询修改银行卡申请审核结果
                            $bankResult = $fuyou->queryChangeCard($user,$bank_examine['mchnt_txn_ssn']);
                            $bankResultArr = objectToArray($bankResult);
                            if("0000" != $bankResultArr["plain"]["resp_code"]){
                                //更新审核记录
                                $ub_examine["is_effect"] = 0;
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $ub_examine, "UPDATE", "id = '" . $bank_examine['id'] . "'");
                            }else if("0000" == $bankResultArr["plain"]["resp_code"] && $bankResultArr["plain"]["examine_st"] == 0){
                                $bank_examine_num += 1;
                            }
                        }
                        if($bank_examine_num){
                            $root['response_code'] = $result['response_code'];
                            $root['show_err'] = "您更换银行卡申请正在审核中，暂时无法体现！";
                            output($root);
                        }                        
			$sql_bank = "SELECT ubank.id,ubank.bank_id,ubank.bankcard,ubank.real_name,bank.name,bank.icon FROM ".DB_PREFIX."user_bank ubank, ".DB_PREFIX."bank bank
						where ubank.bank_id = bank.fuyou_bankid and user_id = " .$user_id;

			$user_bank = $GLOBALS['db']->getRow($sql_bank);

			$root['bank'] = preg_replace("/(\d{3})\d{13}/", "$1*************", str_replace(" ","", $user_bank["bankcard"]));
			$root['user_bank']= $user_bank;
			$root['money'] = $user['money'];
			$root['money_format'] = format_price($user['money']);

			$carry_total_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM ".DB_PREFIX."user_carry WHERE user_id=".$user_id." AND status=1");
			$root['carry_total_money'] = $carry_total_money;

			$root['email'] = $email;
			$root['pwd'] = $pwd;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}

		output($root);
	}
}
?>