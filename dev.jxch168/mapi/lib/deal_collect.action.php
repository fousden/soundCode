<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
require APP_ROOT_PATH . 'app/Lib/deal.php';

class deal_collect {

    public function index() {
        $root = array();

        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        $id = intval($GLOBALS['request']['id']);
        $root['deal_load_id'] = isset($_REQUEST['deal_load_id']) ? intval($_REQUEST['deal_load_id']) : 0;

        //检查用户,用户密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);
        if ($user_id > 0) {
            $root['user_login_status'] = 1;

            $root['response_code'] = 1;
            $root['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal_collect WHERE deal_id = " . $id . " AND user_id=" . $user_id);

            $root['ips_bill_no'] = $GLOBALS['db']->getOne("SELECT ips_bill_no FROM " . DB_PREFIX . "deal WHERE id = " . $id);

            if (!empty($root['ips_bill_no'])) {
                //第三方托管标
                if (!empty($user['ips_acct_no'])) {
                    $result = GetIpsUserMoney($user_id, 0);

                    $root['user_money'] = $result['pBalance'];
                } else {
                    $root['user_money'] = 0;
                }
            } else {
                $root['user_money'] = $user['money'];
            }

            $root['user_money_format'] = format_price($user['user_money']); //用户金额

            $root['open_ips'] = intval(app_conf("OPEN_IPS"));
            $root['ips_acct_no'] = $user['ips_acct_no']; //当前用户是否有申请，第三方托管帐户
        } else {
            $root['show_err'] = "未登录";
            $root['user_money_format'] = 0;
            $root['user_money'] = 0;
            $root['response_code'] = 1;
            $root['is_faved'] = 0;
            $root['ips_bill_no'] = 0;
        }


        $result = get_deal_list_mobile('0,1', 0, ' id = ' . $id);
        $rdata = array();
        //删除过期的标
        $time = TIME_UTC;
        foreach ($result['list'] as $value) {
            if (($value["start_time"] - TIME_UTC) > 0) {
                $value["bfinish_time"] = 0;
            } else {
                $value["bfinish_time"] = 1;
            }

            if ($value['deal_status'] == 4 || $value['deal_status'] == 2) {
                $value['progress_point'] = '100.00';
            }

            //开始时间
            $start_time = $value['start_time'];
            //筹标期限
            $enddate = $value['enddate'];
            //标的有效时间 是否过期
            $remain_time = intval($start_time + $enddate * 24 * 3600 - $time);
            //两天时间时间戳表示
            if ($value['deal_status'] == 1 && $remain_time <= 0) {
                
            } else {
                array_push($rdata, $value);
            }
        }
        $root['item'] = $rdata[0];

        output($root);
    }

}

?>
