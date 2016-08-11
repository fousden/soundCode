<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
class uc_do_withdrawals
{

    public function index()
    {
        $root = array();

        $email       = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd         = strim($GLOBALS['request']['pwd']); //密码
        $paypassword = strim($GLOBALS['request']['paypassword']); //密码
        $amount      = strim($GLOBALS['request']['amount']);
        $bid         = strim($GLOBALS['request']['bid']);
        $mobile      = trim($GLOBALS['request']['_m']);
        if ($mobile) {
            if ($mobile == "android") {
                $terminal = 3;
            } else if ($mobile == "ios") {
                $terminal = 4;
            }
        } else {
            $terminal = 2;
        }

        $root["email"] = $email;
        $root["pwd"]   = $pwd;
        
        //检查用户,用户密码
        $user                 = user_check($email, $pwd);
        $GLOBALS['user_info'] = $user;
        $user_id              = intval($user['id']);
        if ($user_id > 0) {
            $root['user_login_status'] = 1;

            $payment_notice_num = $GLOBALS['db']->getOne("select count(1) from " . DB_PREFIX . "payment_notice where user_id = " .$user_id. " AND is_paid = 1");
            $deal_load_num = $GLOBALS['db']->getOne("select count(1) from " . DB_PREFIX . "deal_load where user_id = ".$user_id. " AND is_auto = 0");
            if(!$deal_load_num){
                $root['response_code']     = 0;
                $root['show_err']          = "您没有任何投资记录，暂时无法提现！";
                output($root);
            }

            if(!$payment_notice_num){
                $root['response_code']     = 0;
                $root['show_err']          = "您没有任何充值记录，暂时无法提现！";
                output($root);
            }
            
            require_once APP_ROOT_PATH . 'app/Lib/uc_func.php';

            $status = getUcSaveCarry($amount, $paypassword, $bid, $terminal);
            if ($status['status'] == 1) {
                //新增提现ID
                $user_carry_id = $status['user_carry_id'];
                if ($user_carry_id) {
                    //富友提现申请
                    require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
                    $fuyou_payment = new Fuyou_payment();
                    $result        = $fuyou_payment->get_user_carry_code($user_carry_id, $terminal);
                    if ($result) {
                        $root['response_code']   = 1;
                        $root['user_carry_code'] = $result['code'];
                        $root['user_carry_id']   = $user_carry_id;
                        $root['mchnt_txn_ssn']   = $result['mchnt_txn_ssn'];

                        $root['page_title']    = $GLOBALS['lang']['CARRY_NOW'];
                        $root['txn_snn_title'] = $GLOBALS['lang']['CARRY_NOTICE_SN'];
                    } else {
                        $root['response_code'] = 0;
                        $root['show_err']      = '支付失败';
                    }
                } else {
                    $root['response_code'] = 0;
                    $root['show_err']      = '支付失败';
                }
            } else {
                $root['response_code'] = 0;
                $root['show_err']      = $status['show_err'];
            }
        } else {
            $root['response_code']     = 0;
            $root['show_err']          = "未登录";
            $root['user_login_status'] = 0;
        }

        output($root);
    }

}

?>