<?php

/*
 * 功能：实现划拨转账
 * 时间：2015年09月15日
 */
require_once 'init.php';

$all_users_info = transferBuAction(13917610894,13916497905,1);
if($all_users_info){
    $str = '';
    $str .='出账账户:'.$all_users_info['before_out']['user_id'].'　出账前数据： 账面总余额：'.$all_users_info['before_out']['ct_balance']." 　可用余额:".$all_users_info['before_out']['ca_balance']." 　冻结余额:".$all_users_info['before_out']['cf_balance']."\r\n";
    $str .='出账账户:'.$all_users_info['now_out']['user_id'].'　出账后数据： 账面总余额：'.$all_users_info['now_out']['ct_balance']." 　可用余额:".$all_users_info['now_out']['ca_balance']." 　冻结余额:".$all_users_info['now_out']['cf_balance']."\r\n";
    $str .='入账账户:'.$all_users_info['before_in']['user_id'].'　入账前数据： 账面总余额：'.$all_users_info['before_in']['ct_balance']." 　可用余额:".$all_users_info['before_in']['ca_balance']." 　冻结余额:".$all_users_info['before_in']['cf_balance']."\r\n";
    $str .='入账账户:'.$all_users_info['now_in']['user_id'].'　入账后数据： 账面总余额：'.$all_users_info['now_in']['ct_balance']." 　可用余额:".$all_users_info['now_in']['ca_balance']." 　冻结余额:".$all_users_info['now_in']['cf_balance']."\r\n";

   echo $str;
}else{
    echo '划拨失败';
}

//划拨 （个人与个人之间）
function transferBuAction($out_cust_no,$in_cust_no,$amt)
{
    require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
    $fuyou_payment = new Fuyou_payment();
    $out_user_info =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where fuiou_account = '".$out_cust_no."'");
    $in_user_info =$GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where fuiou_account = '".$in_cust_no."'");
    //检测两账户查询前余额
    $all_users_info['before_out'] = $fuyou_payment->check_balance($out_user_info);
    $all_users_info['before_in'] = $fuyou_payment->check_balance($in_user_info);

    $fun = 'transferBu.action';
    $url = FUYOU_URL . $fun;
    $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
    $setArr['mchnt_txn_ssn'] = FUYOU_DEAL_LOAD_CALLBACK_PREFIX . time(); //流水号
    $setArr['out_cust_no']   = $out_cust_no; //付款登录账户
    $setArr['in_cust_no']    = $in_cust_no; //收款登录账户
    $setArr['amt']           = $amt * 100; //划拨金额   以分为单位 (无小数位)
    $setArr['contract_no'] = '';//预授权合同号
    $setArr['rem']           = ''; //备注

    $str                     = '';
    ksort($setArr);
    foreach ($setArr as $valV) {
        $str .= $valV . '|';
    }

    $setArr['signature'] = $fuyou_payment->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
    $data                = $fuyou_payment->open_url($url, '', '', '', $setArr);

    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBu.log', "划拨日志POST:[" . json_encode($setArr) . "];return:[" . $data . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

    $xml = simplexml_load_string($data);
    if ('0000' == $xml->plain->resp_code) {
        //检测两账户查询前余额
        $all_users_info['now_out'] = $fuyou_payment->check_balance($out_user_info);
        $all_users_info['now_in'] = $fuyou_payment->check_balance($in_user_info);
        $all_users_info['xml'] = $xml;
        return $all_users_info;
    } else {
        return false;
    }
}
