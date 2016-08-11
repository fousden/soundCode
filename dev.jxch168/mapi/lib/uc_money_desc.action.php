<?php

/**
 *  
 * @api {get} ?act=uc_money_desc&r_type=1&email=dch&&pwd=123456&year=2016 我的资产
 * @apiName 我的资产
 * @apiGroup jxch
 * @apiVersion 1.0.0 
 * @apiDescription 请求url 
 *  
 * @apiParam {string} act 动作{uc_money_desc}
 * @apiParam {string} email {必传}手机号
 * @apiParam {string} pwd {必传}设备id
 * @apiParam {string} year {必传}年份
 * 
 * @apiSuccess {string} response_code 结果码 
 * @apiSuccess {string} show_err 消息说明 
 * @apiSuccess {float} money_format 可用资金 
 * @apiSuccess {float} lock_money_format 冻结金额 
 * @apiSuccess {float} total_money_format 资产金额 
 * @apiSuccess {float} total_invest_money_format 代收本息 
 * @apiSuccess {float} invest_money_format 代收本金
 * @apiSuccess {float} interest_money_format 代收利息
 * @apiSuccess {json} total_month_money 每个月投资的金额信息
 * @apiSuccess {string} total_month_money.date_ym 日期（年-月）
 * @apiSuccess {float} total_month_money.money 每月的金额
 * 
 * 
 * @apiSuccessExample 返回示范: 
  {
  "money": "9144.25",
  "money_format": "9144.25",
  "lock_money": "10033984.00",
  "lock_money_format": "10033984.00",
  "total_money": 10043128.25,
  "total_money_format": "10043128.25",
  "total_invest_money": 35190637.49,
  "total_invest_money_format": "35190637.49",
  "invest_money": 34632492,
  "invest_money_format": "34632492.00",
  "interest_money": 558145.53,
  "interest_money_format": "558145.53",
  "all_income": 7800.85,
  "all_income_format": "7800.85",
  "all_redbag": 51755,
  "all_redbag_format": "51755.00",
  "pt_money_title": "当前月平台投资",
  "pt_money": "142235875080",
  "total_month_money": [
  {
  "date_ym": "2015-01",
  "money": "0"
  },
  {
  "date_ym": "2015-02",
  "money": "0"
  },
  {
  "date_ym": "2015-03",
  "money": "0"
  },
  {
  "date_ym": "2015-04",
  "money": "0"
  },
  {
  "date_ym": "2015-05",
  "money": "0"
  },
  {
  "date_ym": "2015-06",
  "money": "0"
  },
  {
  "date_ym": "2015-07",
  "money": "0"
  },
  {
  "date_ym": "2015-08",
  "money": "0"
  },
  {
  "date_ym": "2015-09",
  "money": "4961073"
  },
  {
  "date_ym": "2015-10",
  "money": "1398847"
  },
  {
  "date_ym": "2015-11",
  "money": "680673"
  },
  {
  "date_ym": "2015-12",
  "money": "800191"
  }
  ],
  "response_code": 1,
  "user_login_status": 1,
  "act": "uc_money_desc",
  "act_2": ""
  }
 */
class uc_money_desc {

    public function index() {
        $root = array();

        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        //检查用户,用户密码
        $user = user_check($email, $pwd);

        $user_id = intval($user['id']);
        if ($user_id > 0) {

            $user['money_format'] = format_price($user['money']); //可用资金
            $user['lock_money_format'] = format_price($user['lock_money']); //冻结金额
            //资金金额
            $user['total_money'] = $user['money'] + $user['lock_money'];
            $user['total_money_format'] = format_price($user['total_money']);

            //可用资金
            $root['money'] = $user['money'];
            $root['money_format'] = $user['money_format'];

            //冻结金额
            $root['lock_money'] = $user['lock_money'];
            $root['lock_money_format'] = $user['lock_money_format'];

            //资金金额
            $root['total_money'] = $user['total_money'];
            $root['total_money_format'] = $user['total_money_format'];

            $all_wait_deals = $this->get_loadlist($user_id);

            $total_invest_money = 0.00;
            $total_self_money = 0.00;
            $total_interest_money = 0.00;
            foreach ($all_wait_deals as $k => $v) {
                $total_invest_money += $v["repay_money"];
                $total_self_money += $v["self_money"];
                $total_interest_money += $v["interest_money"];
            }

            //待还本息
            $root['total_invest_money'] = $total_invest_money;
            $root['total_invest_money_format'] = num_format($root['total_invest_money']);

            //待还本金
            $root['invest_money'] = $total_self_money;
            $root['invest_money_format'] = num_format($root['invest_money']);

            //待还利息
            $root['interest_money'] = $total_interest_money;
            $root['interest_money_format'] = num_format($root['interest_money']);

            $all_repay_deals = $this->get_loadlist($user_id, 1);
            $totle_all_income = 0.00;
            foreach ($all_repay_deals as $k => $v) {
                $totle_all_income += $v["interest_money"];
            }

            //累计收益利息
            $root['all_income'] = $totle_all_income;
            $root['all_income_format'] = num_format($root['all_income']);

            $sql_bouns = "select sum(money) from " . DB_PREFIX . "user_bonus where user_id =$user_id AND status = 2";
            //累计受益红包
            $root['all_redbag'] = floatval($GLOBALS['db']->getOne($sql_bouns));
            $root['all_redbag_format'] = num_format($root['all_redbag']);

            //平台当前月度数据
            $year_month = date("Y-m");
            $sql_now_month_all_money = "select sum(money) from " . DB_PREFIX . "deal_load where FROM_UNIXTIME(create_time,'%Y-%m') = '" . $year_month . "'";
            $root['pt_money_title'] = "当前月平台投资";
            $root['pt_money'] = $GLOBALS['db']->getOne($sql_now_month_all_money);
            if (isset($_REQUEST['year']) && $_REQUEST['year']) {
                $year = trim($_REQUEST['year']);
            } else {
                $year = date("Y");
            }
            //月投资信息
            $start_time = strtotime($year . '-01-01');
            $end_time = strtotime($year . "-12-" . date("t", strtotime($year . '-' . $i)));
            $sql_str = "SELECT FROM_UNIXTIME(create_time,'%Y-%m') as date_ym,SUM(money) as money FROM " . DB_PREFIX . "deal_load where user_id=" . $user_id . " and contract_no!='' and create_time>" . $start_time . " and create_time<" . $end_time . " GROUP BY date_ym";
            $user_deal_load_list = $GLOBALS['db']->getAll($sql_str);
            $shift_data = array_map('array_shift', $user_deal_load_list);

            $year_month = "";
            $arr = array(
                'date_ym' => '',
                'money' => "0",
            );
            for ($i = 1; $i <= 12; $i++) {
                if ($i < 10) {
                    $i = "0" . $i;
                }
                $year_month = $year . '-' . $i;
                if (!in_array($year_month, $shift_data)) {
                    $arr['date_ym'] = $year_month;
                    $user_deal_load_list[] = $arr;
                }
            }
            $root['total_month_money'] = arr_sort($user_deal_load_list, 'date_ym', 'asc', true);
            $root['response_code'] = 1;
            $root['user_login_status'] = 1;
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
        }

        output($root);
    }

    private function get_loadlist($user_id, $repay = 0) {
        $condtion = "   AND dlr.has_repay = " . $repay . " ";
        $sql = "select dlr.*,u.user_name,u.level_id,u.province_id,u.city_id from " . DB_PREFIX . "deal_load_repay dlr LEFT JOIN " . DB_PREFIX . "user u ON u.id=dlr.user_id  where ((dlr.user_id = " . $user_id . " and dlr.t_user_id = 0) or dlr.t_user_id = " . $user_id . ") $condtion order by dlr.repay_time desc ";
        $list = $GLOBALS['db']->getAll($sql);
        return $list;
    }

}
