<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

/*
 *
 * 用户还款日历数据api
 *
 */

class uc_repay {

    public function index() {
        $root = array();
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码

        $root['email'] = $email;
        $root['pwd'] = $pwd;

        //检查用户,用户密码
        $user = user_check($email, $pwd);

        $user_id = intval($user['id']);
        if ($user_id <= 0) {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
            output($root);
        }
        $month = strim($GLOBALS['request']['month']); //接收从ios Android传过来的年月日
        $sql1 = '';
        $sql2 = '';
        if ($month) {
            $month_arr = explode('-', $month);
            $year_money = $month_arr[0] . '-' . $month_arr[1];
            $sql1 = " AND dlr.true_repay_date like '%{$year_money}%'";
            $sql2 = " AND repay_date like '%{$year_money}%'";
        }
        //从deal_load_repay表中查询未还款的数据
        $sql_str = "SELECT SUM(repay_money) AS repay_money
                ,true_repay_date AS date,dlr.has_repay
                ,count(DISTINCT deal_id) AS deal_count
                FROM " . DB_PREFIX . "deal_load_repay AS dlr
                WHERE dlr.has_repay = 1 and user_id=$user_id $sql1 
                GROUP BY true_repay_date";
//        echo 11111;die;
        $root['repay_list'] = $GLOBALS['db']->getAll($sql_str);

        $sql_str = "SELECT
            SUM(repay_money) AS repay_money,
            repay_date AS date,
            has_repay,
            count(DISTINCT deal_id) AS deal_count
                FROM " . DB_PREFIX . "deal_load_repay
                WHERE has_repay = 0 and user_id=$user_id $sql2 
                GROUP BY repay_date";
        $repay_date_list = $GLOBALS['db']->getAll($sql_str);
        //将已还款的数据（$true_repay_date_list）跟未还款的数据（$repay_date_list）合并
        if ($repay_date_list) {
            foreach ($repay_date_list as $key => $val) {
                foreach ($root['repay_list'] as $k => $v) {
                    if ($val['date'] == $v['date']) {
                        unset($val);
                    }
                }
                if ($val) {
                    $root['repay_list'][] = $val;
                }
            }
        }
        $root['repay_money_sum'] = 0;
        $sql_str = "SELECT
                            dlr.has_repay as is_repay,
                            dlr.deal_id,
                            SUM(dlr.repay_money) AS repay_money,
                            d.name,
                            d.rate
                            FROM
                                fanwe_deal_load_repay AS dlr
                            LEFT JOIN fanwe_deal as d on(dlr.deal_id=d.id)
                            WHERE dlr.user_id = $user_id";
        foreach ($root['repay_list'] as $key => $val) {
            $root['repay_money_sum']+=$val['repay_money'];
            for ($i = 1; $i <= 2; $i++) {
                if ($i == 1) {
                    $where = " AND dlr.has_repay = 1 AND dlr.true_repay_date='{$val['date']}'";
                    $deal_list1 = $GLOBALS['db']->getAll($sql_str . " $where GROUP BY dlr.deal_id");
                } else {
                    $where = "AND dlr.has_repay = 0 AND dlr.repay_date='{$val['date']}'";
                    $deal_list2 = $GLOBALS['db']->getAll($sql_str . " $where GROUP BY dlr.deal_id");
                }
            }
            $root['repay_list'][$key]['deal_list'] = array_merge($deal_list1, $deal_list2);
        }
        $root['true_repay_money'] = (int) $GLOBALS['db']->getOne("select sum(true_repay_money) as true_repay_money from " . DB_PREFIX . "deal_load_repay where user_id=$user_id");
        $root['program_title'] = "我的还款";
        output($root);
    }

}
