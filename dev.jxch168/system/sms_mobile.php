<?php

/**
 * 发短信验证码
 * User: ningcz
 * Date: 15-7-1
 * Time: 下午7:26
 */
//$immediately: true，立即即行发送操作;
function send_verify_sms_new($mobile, $sms_name, $code, $user_info, $immediately, $is_limit = false) {
    $re = array('msg_id' => 0, 'status' => 0, 'msg' => '');
    if ($is_limit == true) {
        if (MO("MobileBlacklist")->getInfoByMobile($mobile) > 0) {
            $data['status'] = 0;
            $data['msg'] = "您之前的操作异常，现已无法发送短信，请联系客服！";
            return $data;
        }
        //当天发送短信条数的限制
        $count = $GLOBALS['sys_config']['SMS_MAX_TODAY_COUNT'];
        if (MO("MsgList")->getCountByMobile($mobile) >= $count) {
            MO("MobileBlacklist")->addMobile($mobile);
            $data['status'] = 0;
            $data['msg'] = "您当天发送的短信已经超过" . $count . "条";
            return $data;
        }
    }

    if (app_conf("SMS_ON") == 1) {
        $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = '" . $sms_name . "'");
        $tmpl_content = $tmpl['content'];

        //短信注册验证码
        if ($sms_name == 'TPL_SMS_VERIFY_CODE') {
            $sms_data['mobile'] = $mobile;
            $sms_data['code'] = $code;
            $sms_data['timeout'] = constant("SMS_EXPIRESPAN") / 60;
        }

        //手机绑定修改
        else if ($sms_name == 'TPL_SMS_BIND_MOBILE_VERIFY_CODE') {
            $sms_data['mobile'] = $mobile;
            $sms_data['code'] = $code;
            $sms_data['timeout'] = constant("SMS_EXPIRESPAN") / 60;
        }

        //支付密码修改验证码
        else if ($sms_name == 'TPL_SMS_SAVE_PWD_VERIFY_CODE') {
            $sms_data['mobile'] = $mobile;
            $sms_data['code'] = $code;
            $sms_data['timeout'] = constant("SMS_EXPIRESPAN") / 60;
        }

        //支付密码修改验证码
        else if ($sms_name == 'TPL_SMS_PAYMENT_PWD_VERIFY_CODE') {
            $sms_data['mobile'] = $mobile;
            $sms_data['code'] = $code;
            $sms_data['timeout'] = constant("SMS_EXPIRESPAN") / 60;
        }

        $GLOBALS['tmpl']->assign("verify", $sms_data);
        $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);
        $msg_data['dest'] = $mobile;
        $msg_data['send_type'] = 0;
        $msg_data['title'] = addslashes($msg);
        $msg_data['content'] = $msg_data['title'];
        $msg_data['send_time'] = 0;

        if ($immediately) {
            $msg_data['is_send'] = 1;
        } else {
            $msg_data['is_send'] = 0;
        }
        $msg_data['create_time'] = TIME_UTC;
        $msg_data['user_id'] = $user_info['id'];
        $msg_data['is_html'] = $tmpl['is_html'];
        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入

        $msg_id = $GLOBALS['db']->insert_id();

        $re['msg_id'] = $msg_id;

        if ($immediately && $msg_id > 0) {

            $re['msg_data'] = $msg_data;

            $result = send_sms_email($msg_data);

            $re['status'] = intval($result['status']);
            $re['msg'] = trim($result['msg']);

            //发送结束，更新当前消息状态
            $GLOBALS['db']->query("update " . DB_PREFIX . "deal_msg_list set is_success = " . intval($result['status']) . ",result='" . $result['msg'] . "',send_time='" . TIME_UTC . "' where id =" . $msg_id);
        } else {
            if ($msg_id == 0) {
                $re['status'] = 0;
            } else {
                $re['status'] = 1;
            }
        }
        return $re;
    } else {
        return $re;
    }
}
