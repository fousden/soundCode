<?php

// +----------------------------------------------------------------------
// ｜jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

$payment_lang = array(
    'name'        => '富友支付',
    'Mer_code'    => '商户号',
    'Mer_key'     => '证书',
    'VALID_ERROR' => '支付验证失败',
    'PAY_FAILED'  => '支付失败',
    'GO_TO_PAY'   => '前往富友支付',
);
$config       = array(
    'Mer_code' => array(
        'INPUT_TYPE' => '0',
    ), //商户号
    'Mer_key'  => array(
        'INPUT_TYPE' => '0'
    ), //证书
);
/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true) {
    $module['class_name'] = 'Fuyou';

    /* 名称 */
    $module['name'] = $payment_lang['name'];

    /* 支付方式：1：在线支付；0：线下支付 */
    $module['online_pay'] = '1';

    /* 配送 */
    $module['config'] = $config;

    $module['lang'] = $payment_lang;

    $module['reg_url'] = FUYOU_URL . 'webLogin.action';

    return $module;
}

// 环讯支付模型
require_once(APP_ROOT_PATH . 'system/libs/payment.php');

class Fuyou_payment implements payment
{

    private $payment_lang = array(
        'GO_TO_PAY' => '前往%s支付',
    );

    function get_payment_code($payment_notice_id, $isApp = 0)
    {
        $payment_notice         = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment_notice where id = " . $payment_notice_id);
        $payment_info           = $GLOBALS['db']->getRow("select id,config,logo from " . DB_PREFIX . "payment where id=" . intval($payment_notice['payment_id']));
        $payment_info['config'] = unserialize($payment_info['config']);

        /* 获得订单的流水号，补零到10位 */
        $sp_billno                  = $payment_notice['notice_sn'];
        $user_info                  = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where `id` = '{$payment_notice['user_id']}'");
        $parameter['mchnt_cd']      = FUYOU_MCHNT_CD; // 商户代码
        $parameter['mchnt_txn_ssn'] = $sp_billno; // 流水号
        //$parameter['login_id']      = $user_info['mobile'];//用户账户 为手机号
        $parameter['login_id']      = $user_info['fuiou_account'];//富友账号 为手机号

        $parameter['amt']           = floatval($payment_notice['money'] * 100); //跳转充值、提现页面锁定金额
        if ($isApp) {
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'wap/member.php?ctl=uc_incharge_log';
            if (isset($GLOBALS['request']['_m']) && $GLOBALS['request']['_m']) {
                $parameter['page_notify_url'] =
                $parameter['page_notify_url']
                . '&_m=' . $GLOBALS['request']['_m']
                . '&version='.$GLOBALS['request']['version']
                . '&email='.strim($GLOBALS['request']['email'])
                . '&pwd='.strim($GLOBALS['request']['pwd']);
            }
        } else {
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'member.php?ctl=uc_money&act=incharge_log&is_paid=1';
        }
        $parameter['back_notify_url'] = FUYOU_CALLBACK_URL . 'index.php?ctl=payment&act=notify&class_name=Fuyou';

        ksort($parameter);
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str                    = substr($str, 0, -1);
        $signature              = $this->rsaSign($str, APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据

        //判断是手机端还是Wap端
        if ($isApp) {
            $isAppAtr  = 'app/';
            $payLinks = '<form id="incharge_now" style="text-align:center;" action="' . FUYOU_URL . $isAppAtr . '500002.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $payLinks .= "<input type='hidden' name='$key' value='$val' />";
            }
            if (!empty($payment_info['logo'])) {
                $payLinks .= "<input type='image' src='" . $payment_info['logo'] . "' style='border:solid 1px #ccc;'><div class='blank'></div>";
            }
            $payLinks .= "<input type='submit' id='pay_skip' class='paybutton' value='前往富友支付'></form>";
            $code = '<div style="text-align:center">' . $payLinks . '</div>';
            $code.="<br /><div style='text-align:center' class='red'>" . $GLOBALS['lang']['PAY_TOTAL_PRICE'] . ":" . format_price($payment_notice['money']) . "</div>";
            return $code;
        }else{
            $payLinks = '<form id="incharge_now" style="text-align:center;" action="' . FUYOU_URL . '500002.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $payLinks .= "<input type='hidden' name='$key' value='$val' />";
            }
            if (!empty($payment_info['logo'])) {
                //$payLinks .= "<input type='image' src='" . APP_ROOT . $payment_info['logo'] . "' style='border:solid 1px #ccc;'><div class='blank'></div>";
            }
            $payLinks .= "<input type='hidden' id='pay_skip' class='paybutton' value=''></form>";
            $code = '<div style="text-align:center">' . $payLinks . '</div>';
            //$code.="<br /><div style='text-align:center' class='red'>" . $GLOBALS['lang']['PAY_TOTAL_PRICE'] . ":" . format_price($payment_notice['money']) . "</div>";
            return $code;
        }
    }

    //用户提现
    function get_user_carry_code($user_carry_id, $isApp = 0)
    {
        $user_carry_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user_carry where id = " . $user_carry_id);

        $user_info                  = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where `id` = '" . $user_carry_info['user_id'] . "'");
        $parameter['mchnt_cd']      = FUYOU_MCHNT_CD; // 商户代码
        $parameter['mchnt_txn_ssn'] = FUYOU_CASH_SERIAL_NUMBER_PREFIX . $user_carry_id; // 获得订单的流水号，补零到10位
        //$parameter['login_id']      = $user_info['mobile'];
        $parameter['login_id']      = $user_info['fuiou_account'];//富友账号 提现
        $parameter['amt']           = floatval($user_carry_info['money'] * 100); //跳转提现页面锁定金额
        if ($isApp) {
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'wap/member.php?ctl=uc_incharge_log';
            if (isset($GLOBALS['request']['_m']) && $GLOBALS['request']['_m']) {
                $parameter['page_notify_url'] =
                $parameter['page_notify_url']
                . '&_m=' . $GLOBALS['request']['_m']
		. '&withdrawals=1'
                . '&version='.$GLOBALS['request']['version']
                . '&email='.strim($GLOBALS['request']['email'])
                . '&pwd='.strim($GLOBALS['request']['pwd']);
            }
        } else {
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'member.php?ctl=uc_money&act=carry_log&status=0';
        }
        $parameter['back_notify_url'] = FUYOU_CALLBACK_URL . 'index.php?ctl=payment&act=cash_notify';

        ksort($parameter);
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str                    = substr($str, 0, -1);
        $signature              = $this->rsaSign($str, APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据

        if ($isApp) {
            $isAppAtr = 'app/';
            $payLinks = '<form id="carry_now" style="text-align:center;" action="' . FUYOU_URL . $isAppAtr . '500003.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $payLinks .= "<input type='hidden' name='$key' value='$val' />";
            }
            $payLinks .= "<input type='submit' class='paybutton' value='前往富友提现'></form>";
            $code                    = '<div style="text-align:center">' . $payLinks . '</div>';
            $code.="<br /><div style='text-align:center' class='red'>" . $user_info['user_name'] . "正在进行提现操作，" . $GLOBALS['lang']['CARRY_TOTAL_PRICE'] . "为:" . format_price($user_carry_info['money']) . "</div>";
            $result['code']          = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
            return $result;
        }else{
            $payLinks = '<form id="carry_now" style="text-align:center;" action="' . FUYOU_URL . '500003.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $payLinks .= "<input type='hidden' name='$key' value='$val' />";
            }
            $payLinks .= "<input type='hidden' class='paybutton' ></form>";
            $code                    = '<div style="text-align:center">' . $payLinks . '</div>';
            //$code.="<br /><div style='text-align:center' class='red'>提现请求发送中,请耐心等待......</div>";
            $result['code']          = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
            return $result;
        }

    }

    //下单
    function response($request)
    {

    }
    //充值回调
    function notify($request)
    {
        $reArr = $request;
        unset($request['ctl']);
        unset($request['act']);
        unset($request['class_name']);
        $sign  = $request['signature'];
        unset($request['signature']);
        unset($request['resp_desc']);
        //验证返回数据是否被篡改
        $this->checkDate($reArr);

        //订单号
        $billno    = str_replace(FUYOU_ORDER_PREFIX, '', $reArr['mchnt_txn_ssn']);
        //订单状态描述信息
        if($_REQUEST['resp_desc']){
            $back_data['resp_describle'] = $_REQUEST['resp_desc'];
        }else{
            $remind_codes = require APP_ROOT_PATH.'data_conf/remind_code.php';
            $resp_code = $_REQUEST['resp_code'];
            $resp_describle = $remind_codes[$resp_code];
            $back_data['resp_describle'] = $resp_describle ? $resp_describle : $_REQUEST['resp_code'];
        }
        $GLOBALS['db']->autoExecute(DB_PREFIX . "payment_notice", $back_data, "UPDATE", "notice_sn=" . $billno);

        if ($request['resp_code'] != '0000') {
            $a = $this->getXml('0003', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_ordercallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }

        ksort($request);
        $str = '';
        foreach ($request as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);

        $ipsbillno = '';

        $payment_notice = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment_notice where notice_sn = '" . $billno . "'");
        if (empty($payment_notice)) {
            $a = $this->getXml('0002', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_ordercallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }

        //验证签名
        if ($this->rsaVerify($str, 'fuyou_key/php_pbkey.pem', $reArr['signature'])) {
            $order_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "deal_order where id = " . $payment_notice['order_id']);
            require_once APP_ROOT_PATH . "system/libs/cart.php";
            //更改充值状态 更新充值数据
            $rs = payment_paid($payment_notice['id'], $ipsbillno);
            if ($rs) {
                $rs = order_paid($payment_notice['order_id']);
                $a = $this->getXml('0000', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
                echo $a;
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_ordercallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                die();
            } else {
                //如果失败 将失败信息更新到充值订单状态
                $GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set resp_describle = '充值订单数据更新失败' where id = '".$payment_notice['id']."' and is_paid = 0 ");

                $a = $this->getXml('0000', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
                echo $a;
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_ordercallback.log', "POST:[" . json_encode($reArr) . "];[富友充值成功，但ID为".$payment_notice['id']."充值记录状态修改失败！];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                die();
            }
        } else {
            //如果失败 将失败信息更新到充值订单状态
            //$GLOBALS['db']->query("update ".DB_PREFIX."payment_notice set resp_describle = '".$reArr["resp_desc"]."' where id = '".$payment_notice['id']."' and is_paid = 0 ");

            $a = $this->getXml('0001', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_ordercallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
    }

    function cash_notify($request)
    {
        //用户提现ID
        $reArr         = $_REQUEST;
        $user_carry_id = intval(str_replace(FUYOU_CASH_SERIAL_NUMBER_PREFIX, '', $reArr['mchnt_txn_ssn']));
        unset($_REQUEST['ctl']);
        unset($_REQUEST['act']);
        unset($_REQUEST['signature']);
        unset($_REQUEST['resp_desc']);

        //订单状态描述信息
        $back_data['resp_desc'] = $_REQUEST['resp_desc'];
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $back_data, "UPDATE", "id=" . $user_carry_id);

        if ($_REQUEST['resp_code'] != '0000') {
            $a = $this->getXml('0003', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_carrycashcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
        ksort($_REQUEST);
        $str = '';
        foreach ($_REQUEST as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);
        //验证签名是否被篡改
        if ($this->rsaVerify($str, './fuyou_key/php_pbkey.pem', $reArr['signature'])) {
            //更新提现状态
            $carry_data['status'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_carry", $carry_data, "UPDATE", "id=" . $user_carry_id);
            $user_carry_info      = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "user_carry where id=" . $user_carry_id);
            //先注释掉 提现成功后才进行资金修改同步
            require APP_ROOT_PATH . 'system/libs/user.php';
            modify_account(array('money' => -$user_carry_info['money']), $user_carry_info['user_id'], "提现申请", 8);
            //短信通知提现
            $notice['time']       = to_date($user_carry_info['create_time'], "Y年m月d日 H:i:s");
            $notice['money']      = format_price($user_carry_info['money']);
            $tmpl_content         = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_WITHDRAWS_CASH'", false);
            $GLOBALS['tmpl']->assign("notice", $notice);
            $content              = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content['content']);
            send_user_msg("", $content, 0, $user_carry_info['user_id'], TIME_UTC, 0, true, 5);
            $a                    = $this->getXml('0000', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_carrycashcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        } else {
            $a = $this->getXml('0001', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_carrycashcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
    }

    //判断充值流水号、金额、手机是否被篡改 是否一致
    function checkDate($arr)
    {
        //查看原交易是否存在
        $old_payment_notice = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment_notice where notice_sn = '" . $arr['mchnt_txn_ssn'] . "'");
        if (!$old_payment_notice) {
            $a = $this->getXml('3101', FUYOU_MCHNT_CD, $arr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_checkMoney.log', "POST:[" . json_encode($arr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
        if (intval($old_payment_notice['money'] * 100) != $arr['amt']) {
            $a = $this->getXml('3108', FUYOU_MCHNT_CD, $arr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_checkMoney.log', "POST:[" . json_encode($arr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
        $user_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where `id` = '" . $old_payment_notice['user_id'] . "'");
        //if ($user_info['mobile'] != $arr['login_id']) {
        if ($user_info['fuiou_account'] != $arr['login_id']) {
            $a = $this->getXml('3109', FUYOU_MCHNT_CD, $arr['mchnt_txn_ssn']);
            echo $a;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_checkMoney.log', "POST:[" . json_encode($arr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
    }

    function getXml($resp_code, $mchnt_cd, $mchnt_txn_ssn)
    {

        $strArr = array(
            'resp_code'     => '',
            'mchnt_cd'      => '',
            'mchnt_txn_ssn' => '',
        );
        ksort($strArr);
        $str    = '';
        foreach ($strArr as $valV) {
            $str .= $valV . '|';
        }
        $str       = substr($str, 0, -1);
        $signature = $this->rsaSign($str, ROOT_PATH . 'fuyou_key/php_prkey.pem');

        $str = '<?xml version="1.0" encoding="UTF-8"?>';
        $str .= '<ap>';
        $str .= '<plain>';
        $str .= '<resp_code>' . $resp_code . '</resp_code>';
        $str .= '<mchnt_cd>' . $mchnt_cd . '</mchnt_cd>';
        $str .= '<mchnt_txn_ssn>' . $mchnt_txn_ssn . '</mchnt_txn_ssn>';
        $str .= '</plain>';
        $str .= '<signature>' . $signature . '</signature>';
        $str .= '</ap>';
        return $str;
    }

    function get_display_code()
    {
        $payment_item = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment where class_name='Fuyou'");
        if ($payment_item) {
            $html = "<label  checked='checked' class='f_l ui-radiobox' id='pay_now' rel='common_payment' style='background:url(" . APP_ROOT . $payment_item['logo'] . ")' title='" . $payment_item['name'] . "'>" .
                    "<input type='radio' name='payment' value='" . $payment_item['id'] . "' checked='checked' />&nbsp;";
            if ($payment_item['logo'] == "") {
                $html .=$payment_item['name'];
            }
            $html .= "</label>";
            return $html;
        } else {
            return '';
        }
    }

    //绑卡时注册
    function fuyouRegAction($data, $userId)
    {

        $user_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where `id` = '{$userId}'");

        $setArr['mchnt_cd']       = FUYOU_MCHNT_CD; //商户代码  M
        $setArr['mchnt_txn_ssn']  = FUYOU_BINDCARD_PREFIX . $userId . time(); //流水号 M
        $setArr['cust_nm']        = $user_info['real_name']; //客户姓名 M
        $setArr['certif_id']      = strtoupper($user_info['idno']); //身份证号码 M  如果身份证号码中有字母则转换成大写
        //$setArr['mobile_no']      = $user_info['mobile']; //手机号码 M
        $setArr['mobile_no']      = $user_info['fuiou_account']; //富友账号 M
        $setArr['email']          = ''; //邮箱地址
        $setArr['city_id']        = $data['region_lv2']; //开户行地区代码 M
        $setArr['parent_bank_id'] = $data['bank_id']; //开户行行别 M
        $setArr['bank_nm']        = ''; //开户行支行名称
        $setArr['capAcntNm']      = ''; //户名
        $setArr['capAcntNo']      = str_replace(' ', '', $data['bankcard']); //帐号 M
        $setArr['password']       = ''; //提现密码
        $setArr['lpassword']      = ''; //登录密码
        $setArr['rem']            = ''; //备注
        $str                      = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }

        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        ; //签名信息 M
        $data                = $this->open_url(FUYOU_URL . 'reg.action', '', '', '', $setArr);

        // file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '.log', "ERR:【" .   $data ."】;【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_regAction.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".FUYOU_URL . "reg.action];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        $xml = simplexml_load_string($data);
        $resp_code = $xml->plain->resp_code;
        $resp_code .= '';
        return $resp_code;
    }

    //投标时检测富友余额 如果余额足够返回TRUE 余额不足返回FALSE
    function check_balance($user_info)
    {
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码  M
        $setArr['mchnt_txn_ssn'] = FUYOU_BINDCARD_PREFIX . $user_info['id']; //流水号 M
        $setArr['mchnt_txn_dt']  = date('Ymd', time()); //交易日期 M
        //$setArr['cust_no']       = $user_info['mobile']; //待查询的登录帐户 M
        $setArr['cust_no']       = $user_info['fuiou_account']; //待查询的富友账户 M
        $str = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        ; //签名信息 M
        $data                = $this->open_url(FUYOU_URL . 'BalanceAction.action', '', '', '', $setArr);
        //写入文件日志信息
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_BalanceAction.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求url地址".FUYOU_URL ."BalanceAction.action];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        //载入数据
        $xml                 = simplexml_load_string($data);
        $arr                 = array();
        if ('0000' == $xml->plain->resp_code) {
            $arr['status']        = 1;
            $arr['mchnt_cd']      = '' . $xml->plain->mchnt_cd; //商户代码
            $arr['mchnt_txn_ssn'] = '' . $xml->plain->mchnt_txn_ssn; //请求流水号
            $arr['user_id']       = '' . $xml->plain->results->result->user_id; //用户名
            $arr['ct_balance']    = intval($xml->plain->results->result->ct_balance) / 100; //账面总余额
            $arr['ca_balance']    = intval($xml->plain->results->result->ca_balance) / 100; //可用余额
            $arr['cf_balance']    = intval($xml->plain->results->result->cf_balance) / 100; //冻结余额
            $arr['cu_balance']    = intval($xml->plain->results->result->cu_balance) / 100; //未转结余额
        } else {
            $arr['status']     = 2;
            $arr['show_error'] = '对不起，服务器忙，请稍后再试！';
        }
        return $arr;
    }

    //富友用户信息查询
    function getFuYouUserInfo($user_info)
    {
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码  M
        $setArr['mchnt_txn_ssn'] = FUYOU_BINDCARD_PREFIX . $user_info['id']; //流水号 M
        $setArr['mchnt_txn_dt']  = date('Ymd', time()); //交易日期 M
        //$setArr['user_ids']       = $user_info['mobile']; //待查询的登录帐户 M
        $setArr['user_ids']       = $user_info['fuiou_account']; //待查询的富友账户 M

        $str = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        ; //签名信息 M
        $data                = $this->open_url(FUYOU_URL . 'queryUserInfs.action', '', '', '', $setArr);
        //写入文件日志信息
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_FuYouUserInfo.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求url地址".FUYOU_URL ."queryUserInfs.action];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        //载入数据
        $xml                 = simplexml_load_string($data);
        $arr                 = array();
        if ('0000' == $xml->plain->resp_code) {
            $arr['status']        = 1;
            $arr['data']      = json_decode(json_encode($xml->plain),TRUE); //返回的用户信息数据 转换成数组
        } else {
            $arr['status']     = 2;
            $arr['show_error'] = '对不起，服务器忙，请稍后再试！';
        }
        return $arr;
    }

    //发送URL请求
    function open_url($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "120")
    {//,$ctimeout="60
        //echo $URL . "\r\n<BR>";
        if ($cks && $cksfile) {
            $logstr = "[[cookie]]: There is a NULL bettwn cks and cksfile at one time! \r\n";
            echo $logstr;
            return 0;
        }
        $ch = curl_init(); //初始化一个curl资源(resource)
        curl_setopt($ch, CURLOPT_URL, $URL); //初始化一个url
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $tmout); //设置连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $tmout); //设置执行时间
        if ($ip) { //设置代理服务器
            curl_setopt($ch, CURLOPT_PROXY, $ip);
        }
        if ($cksfile) { //设置保存cookie 的文件路径
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cksfile); //读上次cookie
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cksfile); //写本次cookie
        }
        if ($cks) { //设置cookies字符串，不要与cookie文件同时设置
            curl_setopt($ch, CURLOPT_COOKIE, $cks);
        }
        if ($ref) { //url reference
            curl_setopt($ch, CURLOPT_REFERER, $ref);
        }

        if ($post) { //设置post 字符串
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $fl); //设置是否允许页面跳转 1 跳转，0 不跳转
        curl_setopt($ch, CURLOPT_HEADER, $hder); //设置是否返回头文件 1返回，0 不返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //
        curl_setopt($ch, CURLOPT_NOBODY, $nbd); //设置是否返回body信息，1 不返回，0 返回
        //设置用户浏览器信息
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)');

        //执行
        $re = curl_exec($ch); //
        if ($re === false) { //检错
            $logstr = "[[curl]]: " . curl_error($ch);
            echo $logstr;
        }
        curl_close($ch); //关闭curl资源
        return $re; //返回得到的结果
    }

    function rsaSign($data, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res    = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign   = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign)
    {
        $pubKey = file_get_contents($ali_public_key_path);
        $res    = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

}

?>
