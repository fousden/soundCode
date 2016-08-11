<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
require APP_ROOT_PATH . 'app/Lib/deal.php';

class deal_dobid {

    public function index() {
        set_time_limit(0);
        $root = array();

        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        $coupon_id = intval($GLOBALS['request']['coupon_id']);

        $mobile = trim($GLOBALS['request']['_m']);
        if ($mobile) {
            if ($mobile == "android") {
                $terminal = 3;
            } else if ($mobile == "ios") {
                $terminal = 4;
            }
        } else {
            $terminal = 2;
        }

        //检查用户,用户密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);
        if ($user_id > 0) {
            $root['user_login_status'] = 1;

            $id = intval($GLOBALS['request']['id']);
            $deal = get_deal($id);

            $bid_money = floatval($GLOBALS['request']["bid_money"]);
            $buy_number = $GLOBALS['request']["buy_number"];
            if ($deal['uloadtype'] == 1 && $buy_number > 1) {
                $bid_money = $buy_number * $bid_money;
            }

            $bid_paypassword = strim($GLOBALS['request']['bid_paypassword']);

            $status = dobid2($id, $bid_money, $bid_paypassword, 0, $terminal, $coupon_id);

            $root['status'] = $status['status'];
            $root['show_err'] = $status['show_err'];
            if ($status['status'] == 6) {
                //投标成功 如果该用户是被邀请投资 则邀请人将获得随机1-20元随机现金红包
                invite_active($bid_money);
                MO("User")->insert_lottery_number($user['mobile'], 2);
                $root["response_code"] = 1;
            } else {
                $root["response_code"] = 0;
            }
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }

}

?>
