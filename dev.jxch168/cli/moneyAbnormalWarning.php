<?php

/*
 * 功能：实现富有与金享财行平台用户账户资金 异常预警
 * 时间：2015年07月24日
 */
require_once 'init.php';
//实现用户平台账户与富友账户余额同步
require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
$fuyou_payment = new Fuyou_payment();

$str = '';
$error_num = 1;
$endUid = 0;
while (1) {
    //查询所有用户信息
    //接受参数cli
    if($argv[1] > 0){
        $users_list = $GLOBALS['db']->getAll('select u.* from ' . DB_PREFIX . 'user u LEFT JOIN '.DB_PREFIX.'user_bank ub on u.id = ub.user_id where ub.id > 0 AND u.acct_type is null AND u.is_auto = 0 and u.is_effect = 1 AND u.is_delete = 0 and u.id =' . $argv[1] . ' limit 1 ');
    }else{
        $users_list = $GLOBALS['db']->getAll('select u.* from ' . DB_PREFIX . 'user u LEFT JOIN '.DB_PREFIX.'user_bank ub on u.id = ub.user_id where ub.id > 0 AND u.acct_type is null AND u.is_auto = 0 and u.is_effect = 1 AND u.is_delete = 0 and u.id >' . $endUid . ' limit 100 ');
    }

    foreach ($users_list as $key => $user_info) {
        $fuyou_user_data = $fuyou_payment->check_balance($user_info);
        if ($fuyou_user_data['status'] == 1) {
            $money             = ($fuyou_user_data['ca_balance']); //富友账户可用余额
            $lock_money        = ($fuyou_user_data['cf_balance']); //富友账户冻结余额
            //$account_total = ($fuyou_user_data['ct_balance']);//富友账户总余额
            $account_total     = ($money + $lock_money); //富友账户总余额
            //金享财行平台用户账户总余额
            $jxch_user_account = ($user_info['money'] + $user_info['lock_money']);
            if ($jxch_user_account != $account_total || $user_info['money'] != $money || $user_info['lock_money'] != $lock_money) {
                //资金不一致 以富友数据为准进行同步
                $data['money']      = $money;
                $data['lock_money'] = $lock_money;

                if($error_num == 1){
                    $str .= "【资金同步异常信息如下：】<br/>";
                }
                $error_num +=1;
                $str .= "　　[用户" . $user_info['user_name'] . "的金享财行账面总余额为" . $jxch_user_account . "对应富友账面总余额为" . $account_total . "]<br/>";
                $str .= "　　[其中金享财行账面可用余额为" . $user_info['money'] . "，对应富友账面可用余额为" . $money . "]<br/>";
                $str .= "　　[金享财行账面冻结余额为" . $user_info['lock_money'] . "，对应富友账面冻结金额为" . $lock_money . "]<br/>";
                $str .= "　　[资金同步以富友对应账户余额数据为准进行同步][" . date('Y-m-d H:i:s') . "][user_id:" .$user_info['id']  . ";mobile:" .$user_info['mobile'] . ";rename:" . $user_info['real_name'] . ";]<br/>";
                $str .= "<br/>********************************************<br/>";
            }
        } elseif ($fuyou_user_data['status'] == 2) {
            //服务器忙 响应失败，请稍后重试！
            $str = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_money_abnormal_warning.log', "RETURN:[" .$str.",];DATA:[".json_encode($fuyou_user_data) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        }
        $endUid = $user_info['id'];
    }
    if (count($users_list) < 100) {
        //存在异常才发短信 邮件预警
        if($str){
            //短信预警 短信通知管理员组成员处理
            $msg = "资金同步操作时会员金享账户与富友账户账面金额存在异常，请查看日志确认！【时间：".date('Y-m-d H:i:s')."】。";
            info_admin($msg,"资金同步操作");
            $email_title = date("Y-m-d")."会员资金同步异常邮件通知";
        }else{
            $email_title = date("Y-m-d")."会员资金同步正常邮件通知";
            $str = date("Y-m-d")."所有会员资金同步正常！";
        }

        //邮件通知 管理员组成员处理
        $info_email_users = explode(',', INFO_EMAIL_USER);
        foreach($info_email_users as $email_user){
            $msg_data['dest'] = $email_user;
            $msg_data['send_type'] = 1;//0短信 1 邮件
            $msg_data['title'] = $email_title;
            $msg_data['content'] = addslashes($str);
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = 1;//$tmpl['is_html']  1超文本 0纯文本
            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
        }
        exit;
    }
}

