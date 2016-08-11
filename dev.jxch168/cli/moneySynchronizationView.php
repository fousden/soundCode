<?php

/*
 * 功能：实现富有与金享财行平台用户账户资金同步
 * 时间：2015年07月24日
 */
require_once 'init.php';
//实现用户平台账户与富友账户余额同步
require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
$fuyou_payment = new Fuyou_payment();

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
                $str                = '';
                $str .= "[用户" . $user_info['user_name'] . "的金享财行账面总余额为" . $jxch_user_account . "对应富友账面总余额为" . $account_total . "]";
                $str .= "[其中金享财行账面可用余额为" . $user_info['money'] . "，对应富友账面可用余额为" . $money . "]";
                $str .= "[金享财行账面冻结余额为" . $user_info['lock_money'] . "，对应富友账面冻结金额为" . $lock_money . "]";
                $str .= "[资金同步以富友对应账户余额数据为准进行同步][" . date('Y-m-d H:i:s') . "][uid:" .$user_info['id']  . ";mobile:" .$user_info['mobile'] . ";rename:" . $user_info['real_name'] . ";]";
                echo $str . "\r\n";
            }
        } elseif ($fuyou_user_data['status'] == 2) {
            //服务器忙 响应失败，请稍后重试！
            $str = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
            echo $str . "\r\n";
        }
        $endUid = $user_info['id'];
    }
    if (count($users_list) < 100) {
        echo "finshed \r\n";
        exit;
    }
}

