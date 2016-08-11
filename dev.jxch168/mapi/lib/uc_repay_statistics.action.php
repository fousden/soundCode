<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

/*
 *
 * 还款统计
 *
 */

class uc_repay_statistics {

    public function index() {
        $root = array();
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码

        $root['email'] = $email;
        $root['pwd'] = $pwd;

        //检查用户,用户密码
        $user = user_check($email, $pwd);

        $user_id = intval($user['id']);
        if ($user_id > 0) {
            $root['response_code'] = 1;
            $root['show_err'] = "数据请求成功！";
            $year_month='';
            if($_REQUEST['month']){
                $year_month=date('Y-m',strtotime($_REQUEST['month']));
            }
            //查询当年每个月的已还款的金额
            $sql_str = "SELECT SUM(repay_money) AS repay_money
                ,true_repay_time
                ,true_repay_date
                FROM " . DB_PREFIX . "deal_load_repay AS dlr
                WHERE dlr.has_repay = 1 and user_id=$user_id ";
            $year = date("Y",strtotime($_REQUEST['month']));//获取年份
            //通过循环来查询每个月的还款金额
            for ($i = 1; $i <= 12; $i++) {
                $start_time = strtotime($year . '-' . $i . "-1");
                $end_time = strtotime($year . "-" . $i . '-' . date("t", strtotime($year . '-' . $i)));
                $month = date("Y-m", $start_time);
                $where = " and true_repay_time>$start_time and true_repay_time<=$end_time";
                $root['repay_statistics'][$month] = $GLOBALS['db']->getRow($sql_str . $where);
            }
            $num = 0;
            foreach ($root['repay_statistics'] as $key => $val) {
                $root['repay_statistics'][$num]['year_money'] = (float) $val['repay_money'];
                $root['repay_statistics'][$num]['date'] = $key;
                unset($root['repay_statistics'][$key]);
                $num++;
            }

            $sql_str = "SELECT
            SUM(repay_money) AS repay_money, 
            SUM(self_money) AS self_money,
            SUM(pure_interests) AS pure_interests 
                FROM " . DB_PREFIX . "deal_load_repay
                WHERE has_repay = 0 and user_id=$user_id and repay_date like '%{$year_month}%'";

            $repay_money_info = $GLOBALS['db']->getRow($sql_str);
            $root['repay_money'] = (float) $repay_money_info['repay_money'];
            $root['self_money'] = (float) $repay_money_info['self_money'];
            $root['pure_interests'] = (float) $repay_money_info['pure_interests'];
            unset($repay_money_info);

            //平台所有的金额
            $all_money_info = $GLOBALS['db']->getRow("select sum(true_repay_money) as all_true_repay_money,sum(true_self_money) as all_true_self_money,sum(interest_money) as all_interest_money from " . DB_PREFIX . "deal_load_repay where user_id=$user_id");
            $root['all_true_repay_money'] = (float) $all_money_info['all_true_repay_money'];
            $root['all_true_self_money'] = (float) $all_money_info['all_true_self_money'];
            $root['all_interest_money'] = (float) $all_money_info['all_interest_money'];
            //投资的等级
            $sql_str = "select sum(money) FROM fanwe_deal_load where is_auto=0";
            $all_money = $GLOBALS['db']->getOne($sql_str);
            $user_all_money = (int) $GLOBALS['db']->getOne($sql_str . " and user_id=$user_id");
//            echo $user_all_money;die;
            $float_money = $user_all_money / $all_money;
            if ($user_all_money <= 0) {
                $root['title_status'] = 1;
            } else if ($float_money <= 50) {
                $root['title_status'] = 2;
            } else if ($float_money > 50 && $float_money <= 90) {
                $root['title_status'] = 3;
            } else if ($float_money > 90) {
                $root['title_status'] = 4;
            }
            
            //计算用户的投资金额占所有人投资人的比例
            $time=strtotime("2015-12-08 15:46:00");
            if($user_all_money>0){
            //投资最多的用户的金额
            $max_money=$GLOBALS['db']->getOne("SELECT sum(money) as money FROM `fanwe_deal_load` where is_auto=0 GROUP BY user_id order by money desc limit 1");
            //该用户投资多少钱
            $user_money=$GLOBALS['db']->getOne("select sum(money) from fanwe_deal_load where is_auto=0 and create_time<$time and user_id=$user_id");        
            $root['percent']=round($user_money/($max_money/50),2);
            $new_user_money=(int)$GLOBALS['db']->getOne("select sum(money) from fanwe_deal_load where user_id=$user_id and create_time>$time");
            if($new_user_money>10000){
                $root['percent']=$root['percent']+round($user_money/1000000,2);
            }
            }else{
                $root['percent']=0;
            }
            
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
        }
        $root['program_title'] = "我的还款";
        output($root);
    }

}
