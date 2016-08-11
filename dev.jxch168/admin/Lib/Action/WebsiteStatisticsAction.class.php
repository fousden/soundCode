<?php

class WebsiteStatisticsAction extends CommonAction
{

    public function com_search()
    {
        $map = array();


        if (!isset($_REQUEST['end_time']) || $_REQUEST['end_time'] == '') {
            $_REQUEST['end_time'] = to_date(get_gmtime(), 'Y-m-d');
        }


        if (!isset($_REQUEST['start_time']) || $_REQUEST['start_time'] == '') {
            $_REQUEST['start_time'] = dec_date($_REQUEST['end_time'], 7); // $_SESSION['q_start_time_7'];
        }


        $map['start_time'] = trim($_REQUEST['start_time']);
        $map['end_time']   = trim($_REQUEST['end_time']);


        $this->assign("start_time", $map['start_time']);
        $this->assign("end_time", $map['end_time']);


        $d = explode('-', $map['start_time']);
        if (checkdate($d[1], $d[2], $d[0]) == false) {
            $this->error("开始时间不是有效的时间格式:{$map['start_time']}(yyyy-mm-dd)");
            exit;
        }

        $d = explode('-', $map['end_time']);
        if (checkdate($d[1], $d[2], $d[0]) == false) {
            $this->error("结束时间不是有效的时间格式:{$map['end_time']}(yyyy-mm-dd)");
            exit;
        }

        if (to_timespan($map['start_time']) > to_timespan($map['end_time'])) {
            $this->error('开始时间不能大于结束时间');
            exit;
        }

        $q_date_diff = 70;
        $this->assign("q_date_diff", $q_date_diff);

        if ($q_date_diff > 0 && (abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400 + 1 > $q_date_diff)) {
            $this->error("查询时间间隔不能大于  {$q_date_diff} 天");
            exit;
        }

        return $map;
    }

    //提现统计
    public function website_extraction_cash()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        if ($start_time == $end_time) {
            if($start_time == $time){
                //应还记录
                $sql_repay_total = "SELECT sum(dlr.repay_money) FROM " . DB_PREFIX . "deal_load_repay dlr LEFT JOIN " . DB_PREFIX . "deal d ON dlr.deal_id = d.id where d.jiexi_time = '" . $start_time ."'";
                $statistics_repay_total =$GLOBALS['db']->getOne($sql_repay_total);
                //实还记录
                $sql_repay_true = "SELECT sum(dr.repay_money) FROM " . DB_PREFIX . "deal_repay dr WHERE dr.true_repay_date = '" . $start_time ."'";
                $statistics_repay_true =$GLOBALS['db']->getOne($sql_repay_true);
                //充值记录
                $sql_payment = "SELECT sum(pn.money) FROM " . DB_PREFIX . "payment_notice pn WHERE pn.pay_date = '" .$start_time . "' and pn.is_paid=1 GROUP BY pn.pay_date";
                $statistics_payment = $GLOBALS['db']->getOne($sql_payment);
                //提现记录
                $sql_carry = "SELECT sum(z.money) FROM " . DB_PREFIX . "user_carry z where z.create_date='" . $start_time ."' AND z.status = 1";
                $statistics_carry = $GLOBALS['db']->getOne($sql_carry);
                //投标记录
                $sql_invest = "SELECT sum(dl.money) FROM " . DB_PREFIX . "deal_load dl WHERE dl.create_date = '" .$start_time . "' and dl.is_auto = 0  GROUP BY dl.create_date";
                $statistics_invest = $GLOBALS['db']->getOne($sql_invest);
            }else{
                //应还记录
                $sql_repay_total = "SELECT sr.repay_count FROM " . DB_PREFIX . "statistical_repay sr WHERE sr.repay_date = '" .$start_time_int . "' and sr.repay_type = 1";
                $statistics_repay_total = $GLOBALS['db']->getOne($sql_repay_total);
                //实还记录
                $sql_repay_true = "SELECT sr.repay_count FROM " . DB_PREFIX . "statistical_repay sr WHERE sr.repay_date = '" .$start_time_int . "' and sr.repay_type = 2";
                $statistics_repay_true = $GLOBALS['db']->getOne($sql_repay_true);
                //充值记录
                $sql_payment = "SELECT sum(spn.payment_count) as payment_count FROM " . DB_PREFIX . "statistical_payment_notice spn WHERE spn.payment_date = '" .$start_time_int . "' and spn.payment_type = 0";
                $statistics_payment = $GLOBALS['db']->getOne($sql_payment);
                //提现记录
                $sql_carry        = "SELECT sc.carry_count FROM " . DB_PREFIX . "statistical_carry sc WHERE sc.carry_date = '" .$start_time_int . "' and sc.carry_type = 1";
                $statistics_carry = $GLOBALS['db']->getOne($sql_carry);
                //投标记录
                $sql_invest = "SELECT sum(si.invest_count) as invest_count FROM " . DB_PREFIX . "statistical_invest si WHERE si.invest_date = '" .$start_time_int . "'  and si.invest_type = 0";
                $statistics_invest = $GLOBALS['db']->getOne($sql_invest);
            }
            $list = array();
            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($statistics_repay_total)&&!empty($statistics_repay_true)&&!empty($statistics_payment)&&!empty($statistics_carry)&&!empty($statistics_invest)) {
                $sum = $statistics_repay_total + $statistics_repay_true + $statistics_payment + $statistics_carry + $statistics_invest ;
                $list[] = round($statistics_repay_total / ($sum) * 100, 2);
                $list[] = round($statistics_repay_true / ($sum) * 100, 2);
                $list[] = round($statistics_payment / ($sum) * 100, 2);
                $list[] = round($statistics_carry / ($sum) * 100, 2);
                $list[] = round($statistics_invest / ($sum) * 100, 2);
            }
            $data_name_raw   = ['应还金额'."(".$statistics_repay_total."元)", '实还金额'."(".$statistics_repay_true."元)", '充值金额'."(".$statistics_payment."元)",'提现金额'."(".$statistics_carry."元)",'投标金额'."(".$statistics_invest."元)"];
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $date_list[]   = $start_time_int;

            $client_list = array();
            $client_list[0]['date']              = $start_time_int;
            $client_list[0]['repay_total_count'] = $statistics_repay_total;
            $client_list[0]['repay_true_count']  = $statistics_repay_true;
            $client_list[0]['payment_count']     = $statistics_payment;
            $client_list[0]['carry_count']       = $statistics_carry;
            $client_list[0]['invest_count']      = $statistics_invest;

        } else {
            //应还记录
            $sql_repay_total = "SELECT sr.repay_date,sr.repay_count FROM " . DB_PREFIX . "statistical_repay sr WHERE sr.repay_date >= '" .$start_time_int . "' and sr.repay_date <= '" .$end_time_int . "' and sr.repay_type = 1";
            $statistics_repay_total = $GLOBALS['db']->getAll($sql_repay_total);
            //实还记录
            $sql_repay_true = "SELECT sr.repay_date,sr.repay_count FROM " . DB_PREFIX . "statistical_repay sr WHERE sr.repay_date >= '" .$start_time_int . "' and sr.repay_date <= '" .$end_time_int . "' and sr.repay_type = 2";
            $statistics_repay_true = $GLOBALS['db']->getAll($sql_repay_true);
            //充值记录
            $sql_payment = "SELECT spn.payment_date,spn.payment_count FROM " . DB_PREFIX . "statistical_payment_notice spn WHERE spn.payment_date >= '" .$start_time_int . "' and spn.payment_date <= '" .$end_time_int . "' and spn.payment_type = 0";
            $statistics_payment = $GLOBALS['db']->getAll($sql_payment);
            //提现记录
            $statistics_carry = M("statistical_carry")->order("carry_date asc")->where("carry_date>=$start_time_int and carry_date<=$end_time_int and carry_type = 1")->findAll();
            //投标记录
            $sql_invest = "SELECT si.invest_date,si.invest_count FROM " . DB_PREFIX . "statistical_invest si WHERE si.invest_date >= '" .$start_time_int . "' and si.invest_date <= '" .$end_time_int . "' and si.invest_type = 0";
            $statistics_invest = $GLOBALS['db']->getAll($sql_invest);

            $date_lists = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<= $end_time_int")->field("payment_date")->group("payment_date")->findAll();
            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['payment_date'];
                }
            }
            $list_repay_total = array();
            $list_repay_true  = array();
            $list_payment     = array();
            $list_carry       = array();
            $list_invest      = array();
            foreach($date_list as $k => $v){
                if($statistics_repay_total){
                    foreach($statistics_repay_total as $key => $val){
                        if($val['repay_date'] == $v){
                            $list_repay_total[0][$k] = (float)$val['repay_count'];
                        }
                        if(!$list_repay_total[0][$k]){
                            $list_repay_total[0][$k] = (float)0;
                        }
                     }
                }else{
                    $list_repay_total[0][$k] = (float)0;
                }

                if($statistics_repay_true){
                    foreach($statistics_repay_true as $key => $val){
                        if($val['repay_date'] == $v){
                            $list_repay_true[1][$k] = (float)$val['repay_count'];
                        }
                        if(!$list_repay_true[1][$k]){
                            $list_repay_true[1][$k] = (float)0;
                        }
                     }
                }else{
                    $list_repay_true[1][$k] = (float)0;
                }

                if($statistics_payment){
                    foreach($statistics_payment as $key => $val){
                        if($val['payment_date'] == $v){
                            $list_payment[2][$k] = (float)$val['payment_count'];
                        }
                        if(!$list_payment[2][$k]){
                            $list_payment[2][$k] = (float)0;
                        }
                     }
                }else{
                    $list_payment[2][$k] = (float)0;
                }

                if($statistics_carry){
                    foreach($statistics_carry as $key => $val){

                        if($val['carry_date'] == $v){
                            $list_carry[3][$k] = (float)$val['carry_count'];
                        }
                        if(!$list_carry[3][$k]){
                            $list_carry[3][$k] = (float)0;
                        }
                     }
                }else{
                    $list_carry[3][$k] = (float)0;
                }

                if($statistics_invest){
                    foreach($statistics_invest as $key => $val){
                        if($val['invest_date'] == $v){
                            $list_invest[4][$k] = (float)$val['invest_count'];
                        }
                        if(!$list_invest[4][$k]){
                            $list_invest[4][$k] = (float)0;
                        }
                     }
                }else{
                    $list_invest[4][$k] = (float)0;
                }
            }
            $list = array_merge($list_repay_total,$list_repay_true,$list_payment,$list_carry,$list_invest);
            $data_name_raw   = ['应还金额', '实还金额', '充值金额','提现金额','投标金额'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图

            $client_list = array();
            $client_list['gross']['date'] = "总计";
            foreach($date_list as $k => $v){
                $client_list[$v]['date']  = $v;
                if($statistics_repay_total){
                    foreach($statistics_repay_total as $key => $val){
                        if($val['repay_date'] == $v){
                            $client_list[$v]['repay_total_count'] = $val['repay_count'];
                        }
                        if(!$client_list[$v]['repay_total_count']){
                            $client_list[$v]['repay_total_count'] = 0;
                        }
                     }
                }else{
                    $client_list[$v]['repay_total_count'] = 0;
                }

                if($statistics_repay_true){
                    foreach($statistics_repay_true as $key => $val){
                        if($val['repay_date'] == $v){
                            $client_list[$v]['repay_true_count'] = $val['repay_count'];
                        }
                        if(!$client_list[$v]['repay_true_count']){
                            $client_list[$v]['repay_true_count'] = 0;
                        }
                    }
                }else{
                    $client_list[$v]['repay_true_count'] = 0;
                }

                if($statistics_payment){
                    foreach($statistics_payment as $key => $val){

                        if($val['payment_date'] == $v){

                            $client_list[$v]['payment_count'] = $val['payment_count'];
                        }
                        if(!$client_list[$v]['payment_count']){
                            $client_list[$v]['payment_count'] = 0;
                        }
                    }
                }else{
                    $client_list[$v]['payment_count'] = 0;
                }

                if($statistics_carry){
                    foreach($statistics_carry as $key => $val){
                        if($val['carry_date'] == $v){
                            $client_list[$v]['carry_count'] = $val['carry_count'];
                        }
                        if(!$client_list[$v]['carry_count']){
                            $client_list[$v]['carry_count'] = 0;
                        }
                    }
                }else{
                    $client_list[$v]['carry_count'] = 0;
                }

                if($statistics_invest){
                    foreach($statistics_invest as $key => $val){
                        if($val['invest_date'] == $v){
                            $client_list[$v]['invest_count'] = $val['invest_count'];
                        }
                        if(!$client_list[$v]['invest_count']){
                            $client_list[$v]['invest_count'] = 0;
                        }
                    }
                }else{
                    $client_list[$v]['invest_count'] = 0;
                }
                $client_list['gross']['repay_total_count'] += $client_list[$v]['repay_total_count'];
                $client_list['gross']['repay_true_count']  += $client_list[$v]['repay_true_count'];
                $client_list['gross']['payment_count']     += $client_list[$v]['payment_count'];
                $client_list['gross']['carry_count']       += $client_list[$v]['carry_count'];
                $client_list['gross']['invest_count']      += $client_list[$v]['invest_count'];

                }
            krsort($client_list);
        }

        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));
        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);
        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode($list));
        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));
        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));
        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));
        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($list));
        $this->assign('client_list', $client_list); //表格中的数据
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    //用户统计
    public function website_users_total()
    {

        $map = $this->com_search();

        foreach ($map as $key => $val) {
            if ((!is_array($val)) && ($val <> '')) {
                $parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        $sql_str = "select
		u.create_date as 时间,
		count(*) as 用户注册人数
		from " . DB_PREFIX . "user as u ";

        //日期期间使用in形式，以确保能正常使用到索引
        if (isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> '') {
            $sql_str .= " where u.create_date in (" . date_in($map['start_time'], $map['end_time']) . ")";
        }

        $sql_str .= " group by u.create_date ";
        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, "&" . $parameter, '时间', false);

        require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');

        $total_array = array(
            array(
                array('用户注册人数', '时间', '用户注册人数'),
            ),
        );


        krsort($voList);
        $chart_list = $this->get_jx_json_all($voList, $total_array);
        $this->assign("chart_list", $chart_list);
        $this->display();
    }

    //网站垫付
    public function website_advance_total()
    {

        $map = $this->com_search();

        foreach ($map as $key => $val) {
            if ((!is_array($val)) && ($val <> '')) {
                $parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        $sql_str = "select
		g.create_date as 时间,
		sum(repay_money) as 代还本息总额,
		sum(manage_money) as 代还管理费总额,
		sum(impose_money) as 代还罚息总额,
		sum(manage_impose_money) as 代还逾期管理费总额
		from " . DB_PREFIX . "generation_repay as g ";

        //日期期间使用in形式，以确保能正常使用到索引
        if (isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> '') {
            $sql_str .= " where g.create_date in (" . date_in($map['start_time'], $map['end_time']) . ")";
        }

        $sql_str .= " group by g.create_date ";
        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, "&" . $parameter, '时间', false);

        require('./admin/Tpl/default/Common/js/flash/php-ofc-library/open-flash-chart.php');

        $total_array = array(
            array(
                array('代还本息总额', '时间', '代还本息总额'),
                array('代还管理费总额', '时间', '代还管理费总额'),
                array('代还罚息总额', '时间', '代还罚息总额'),
                array('代还逾期管理费总额', '时间', '代还逾期管理费总额'),
            ),
        );


        krsort($voList);
        $chart_list = $this->get_jx_json_all($voList, $total_array);
        $this->assign("chart_list", $chart_list);
        $this->display();
    }

    //网站费用统计
    public function website_cost_total()
    {

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);

        $sql_str = "select
		count(DISTINCT user_id) as 关联用户数,
		sum(if(type = 1,money,0)) as 充值手续费,
		sum(if(type = 9,money,0)) as 提现手续费,
		sum(if(type = 10,money,0)) as 借款管理费,
		sum(if(type = 12,money,0)) as 逾期管理费,
		sum(if(type = 13,money,0)) as 人工操作,
		sum(if(type = 14,money,0)) as 借款服务费,
		sum(if(type = 17,money,0)) as 债权转让管理费,
		sum(if(type = 18,money,0)) as 开户奖励,
		sum(if(type = 20,money,0)) as 投标管理费,
		sum(if(type = 22,money,0)) as 兑换,
		sum(if(type = 23,money,0)) as 邀请返利,
		sum(if(type = 24,money,0)) as 投标返利,
		sum(if(type = 25,money,0)) as 签到成功,
		sum(if(type = 26,money,0)) as 逾期罚金（垫付后）,
		sum(if(type = 27,money,0)) as 其他费用,
		sum(if(type = 28,money,0)) as 投资奖励,
		sum(if(type = 29,money,0)) as 红包奖励
		from " . DB_PREFIX . "site_money_log where 1 = 1  ";

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (create_time between $begin_time and $end_time )";
            }
        }

        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, '时间', false);

        $this->display();
    }

    //充值明细
    public function website_recharge_info()
    {

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (n.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['notice_sn']) != '') {
            $notice_sn = trim($_REQUEST['notice_sn']);
        }
        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['is_paid']) != '') {
            $is_paid = trim($_REQUEST['is_paid']);
        }
        if (trim($_REQUEST['memo']) != '') {
            $memo = trim($_REQUEST['memo']);
        }



        $sql_str = "select
		n.create_date as 时间,
		n.notice_sn as 支付单号,
		n.user_id,
		u.user_name as 会员名称,
		n.money as 应付金额,
		p.name as 支付方式,
		if(n.is_paid = 1,'已支付','未支付') as 支付状态,
		n.memo as 支付备注
		from " . DB_PREFIX . "payment_notice as n LEFT JOIN " . DB_PREFIX . "user as u on u.id=n.user_id LEFT JOIN " . DB_PREFIX . "payment as p on  p.id=n.payment_id $condtion ";

        if ($notice_sn) {
            $sql_str = "$sql_str  and n.notice_sn = '$notice_sn'";
        }
        if ($user_name) {
            $sql_str = "$sql_str and u.user_name like '%$user_name%'";
        }
        if ($memo) {
            $sql_str = "$sql_str and n.memo like '%$memo%'";
        }


        if (isset($_REQUEST['is_paid'])) {
            if ($is_paid == 4) {
                $sql_str = "$sql_str";
            } elseif ($is_paid == 1) {
                $sql_str = "$sql_str and n.is_paid = 1 ";
            } elseif ($is_paid == 2) {
                $sql_str = "$sql_str and n.is_paid = 0 ";
            }
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str = "$sql_str and (n.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str = "$sql_str and (n.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str = "$sql_str and (n.create_time between $begin_time and $end_time )";
            }
        }

        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, '时间', false);

        $this->display();
    }

    //提现明细
    public function website_extraction_cash_info()
    {

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (c.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }


        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['status']) != '') {
            $status = trim($_REQUEST['status']);
        }

        $sql_str = "select
		c.create_date as 时间,
		u.id as 会员名称,
		c.money as 提现金额,
		c.fee as 手续费,
		case c.status
		when 0 then '待审核'
		when 1 then '已付款'
		when 2 then '未通过'
		when 3 then '待付款'
		else
		 '撤销'
		end as 提现状态,
		FROM_UNIXTIME(c.update_time + 28800, '%Y-%m-%d') as 处理时间
		from " . DB_PREFIX . "user_carry as c left join " . DB_PREFIX . "user as u on u.id=c.user_id  $condtion ";

        if ($user_name) {
            $sql_str = "$sql_str and u.user_name like '%$user_name%'";
        }

        if (isset($_REQUEST['status'])) {
            if ($status == 5) {
                $sql_str = "$sql_str";
            } elseif ($status == 1) {
                $sql_str = "$sql_str and c.status = 0 ";
            } elseif ($status == 2) {
                $sql_str = "$sql_str and c.status = 1 ";
            } elseif ($status == 3) {
                $sql_str = "$sql_str and c.status = 2 ";
            } elseif ($status == 4) {
                $sql_str = "$sql_str and c.status = 4 ";
            }
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str = "$sql_str and (c.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str = "$sql_str and (c.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str = "$sql_str and (c.create_time between $begin_time and $end_time )";
            }
        }

        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, '时间', false);

        $this->display();
    }

    //用户明细
    public function website_users_info()
    {

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (u.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['email']) != '') {
            $email = trim($_REQUEST['email']);
        }
        if (trim($_REQUEST['mobile']) != '') {
            $mobile = trim($_REQUEST['mobile']);
        }

        if (trim($_REQUEST['level_id']) != '') {
            $level_id = trim($_REQUEST['level_id']);
        }

        $this->assign("level_list", M("UserLevel")->findAll());


        $sql_str = "select
		u.create_date as 注册时间,
		u.id as 会员名称,
		u.email as 会员邮件,
		u.mobile as 手机号,
		u.money as 会员余额,
		u.lock_money as 冻结资金,
		l.name as 会员等级
		from " . DB_PREFIX . "user as u left join " . DB_PREFIX . "user_level as l on l.id=u.level_id  $condtion ";

        if ($user_name) {
            $sql_str = "$sql_str and u.user_name like '%$user_name%'";
        }
        if ($email) {
            $sql_str = "$sql_str and u.email like '%$email%'";
        }
        if ($mobile) {
            $sql_str = "$sql_str and u.mobile like '%$mobile%'";
        }

        if ($level_id) {
            $sql_str = "$sql_str and l.id = '$level_id'";
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str = "$sql_str and (u.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str = "$sql_str and (u.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str = "$sql_str and (u.create_time between $begin_time and $end_time )";
            }
        }

        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, '时间', false);

        $this->display();
    }

    //充值反馈统计
    public function incharge_feed_back()
    {
        //从url中获取开始时间，结束时间
        $start_time = $_REQUEST["start_time"];
        $end_time   = $_REQUEST["end_time"];
        $incharge_type = $_REQUEST["incharge_type"];

        $_sql       = "select count(id) as error_num,resp_describle from ".DB_PREFIX."payment_notice where 1 = 1 ";
        $_count_sql = "select count(id) as count_total from fanwe_payment_notice where 1 = 1 ";

        $begin_time = $start_time ? $start_time . " 00:00:00" : "2015-10-13 00:00:00";
        $_sql .= " AND create_time >= " . strtotime($begin_time);
        $_count_sql .= " AND create_time >= " . strtotime($begin_time);
        if ($end_time) {
            $_sql .= " AND create_time <= " . strtotime($end_time . " 23:59:59");
            $_count_sql .= " AND create_time <= " . strtotime($end_time . " 23:59:59");
        }
        //充值类型
        if($incharge_type == "official"){
            $_sql .= " AND incharge_source = 1 ";
            $_count_sql .= " AND incharge_source = 1 ";
        }else if($incharge_type == "app"){
            $_sql .= " AND incharge_source != 1 ";
            $_count_sql .= " AND incharge_source != 1 ";
        }else if($incharge_type == "wap"){
            $_sql .= " AND incharge_source = 2 ";
            $_count_sql .= " AND incharge_source = 2 ";
        }else if($incharge_type == "ios"){
            $_sql .= " AND incharge_source = 4 ";
            $_count_sql .= " AND incharge_source = 4 ";
        }else if($incharge_type == "android"){
            $_sql .= " AND incharge_source = 3 ";
            $_count_sql .= " AND incharge_source = 3 ";
        }

        $_sql .= " GROUP BY resp_describle";

        $model             = D();
        $inchage_feed_list = $this->_Sql_list($model, $_sql, '', 'error_num', false);
        //充值反馈错误总数
        $count_total       = D("payment_notice")->query($_count_sql);
        $count_num         = $count_total[0]['count_total'];

        //数据格式化
        foreach ($inchage_feed_list as $key => $val) {
            $inchage_feed_list[$key]['id']         = $key + 1;
            $inchage_feed_list[$key]['error_rate'] = round($val['error_num'] / $count_num, 4);
            if (!$val['resp_describle']) {
                $inchage_feed_list[$key]['resp_describle'] = "充值中断";
            }
            $data_name_raw[]  = $inchage_feed_list[$key]['resp_describle'] . '(' . $val['error_num'] . '次)';
            $pie_data_array[] = $inchage_feed_list[$key]['error_rate'];
        }

        //导出充值返回错误统计信息
        if ($_REQUEST['type'] == 'export_now') {
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num         = 1;
            foreach ($inchage_feed_list as $key => $val) {
                if ($num == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '编号')
                            ->setCellValue('B' . $num, "充值错误类型")
                            ->setCellValue('C' . $num, "错误次数")
                            ->setCellValue('D' . $num, "错误率（%）");
                    $num = 2;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $val['id'])
                        ->setCellValue('B' . $num, $val['resp_describle'])
                        ->setCellValue('C' . $num, $val['error_num'])
                        ->setCellValue('D' . $num, $val['error_rate'] * 100);
                $num++;
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
            if ($start_time && $end_time) {
                $filename = $start_time . '~' . $end_time . "的充值返回错误统计表";
            } else {
                $filename = "所有充值返回错误统计表";
            }
            php_export_excel($objPHPExcel, $filename);
            die();
        }
        $this->assign('data_name', json_encode($data_name_raw));
        $this->assign('series_name', json_encode("百分比"));
        $this->assign('pie_data_array', json_encode($pie_data_array));
        $this->assign('count_num', $count_num);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('feed_list', $inchage_feed_list);
        $this->assign('type', 'pie');
        $this->display();
    }

    //垫付明细
    public function website_advance_info()
    {

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (r.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['name']) != '') {
            $name = trim($_REQUEST['name']);
        }
        if (trim($_REQUEST['adm_name']) != '') {
            $adm_name = trim($_REQUEST['adm_name']);
        }
        if (trim($_REQUEST['agency_id']) != '') {
            $agency_id = trim($_REQUEST['agency_id']);
        }

        $this->assign("agency_list", M("User")->where('user_type = 2')->findAll());

        $sql_str = "select
		r.create_date as 代还时间,
		d.sub_name as 贷款名称,
		CONCAT('第',lr.l_key + 1,'期') as 第几期,
		a.adm_name as 管理员,
		da.name as 担保机构,
		r.repay_money as 代还本息,
		r.manage_money as 代还管理费,
		r.impose_money as 代还罚息,
		r.manage_impose_money 代还多少逾期管理费,
		r.deal_id
		from " . DB_PREFIX . "generation_repay as r
		left join " . DB_PREFIX . "deal as d on d.id=r.deal_id
		left join " . DB_PREFIX . "deal_load_repay as lr on lr.repay_id=r.repay_id
		left join " . DB_PREFIX . "admin as a on a.id=r.admin_id
		left join " . DB_PREFIX . "deal_agency as da on da.id=r.agency_id
		$condtion ";

        if ($name) {
            $sql_str = "$sql_str and d.name like '%$name%'";
        }
        if ($adm_name) {
            $sql_str = "$sql_str and a.adm_name like '%$adm_name%'";
        }

        if ($agency_id) {
            $sql_str = "$sql_str and da.id = '$agency_id'";
        }


        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str = "$sql_str and (r.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str = "$sql_str and (r.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str = "$sql_str and (r.create_time between $begin_time and $end_time )";
            }
        }

        $model  = D();
        $voList = $this->_Sql_list($model, $sql_str, '时间', false);

        $this->display();
    }

    // 入金统计
    public function website_gold_info()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个周前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间,默认为今天
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        $gold_list = array();
        //所选日期内的数据
        if ($start_time != $end_time) {
            //将日期数据存为图表规定的数据格式
            $interval = (to_timespan($end_time, $format   = 'Y-m-d H:i:s') - to_timespan($start_time, $format   = 'Y-m-d H:i:s')) / 86400;
            $date_all = array();
            for ($i = 0; $i <=$interval; $i++) {
                $date_temp                              = to_date(to_timespan($start_time, $format                                 = 'Y-m-d H:i:s') + ($i * 86400), "Y-m-d");
                array_push($date_all, $date_temp);
                $gold_list[$date_temp]['user_reg_date'] = $date_temp;
            }
            //设置图表为折线图
            $type       = "line";
            $this->assign('type', $type);
            // 循环将数据保存为图标规定的数据格式
            $model = D();
            $count_all = array();// 存放第一张数据表需要的数据
            // 当天注册当天入金的人数
            $count_int = array();// 存放当天入金当天注册的人数，日期为搜索的日期
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=1 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $count_int[] = (int)$val['count'];
            }

            array_push($count_all,$count_int); // 将当天入金当天注册的人数加入到数组
            // 当天入金非当天注册的人数
            $count_int = array(); // 存放当天入金非当天注册的人数
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=2 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $count_int[] = (int)$val['count'];
            }
            array_push($count_all,$count_int);// 将当天入金非当天注册的人数加入到数组
            // 当天入金的总人数
            $count_int = array();// 存放当天入金的总人数
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=5 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $count_int[] = (int)$val['count'];
            }
            array_push($count_all,$count_int);// 将当天入金的总人数加入到数组
            $sql = "select * from " . DB_PREFIX . "statistical_gold where user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc "; // 查询到表中的数据
            $info = $model->query($sql);
            $day_count = 0;
            $noday_count = 0;
            $sumday_count = 0;
            foreach($info as $key=>$val){
                $date = date("Y-m-d",strtotime($val['user_reg_date']));
                $user_info[$val['user_reg_date']]['user_reg_date'] = $date;
                if($val['type']==1){
                    $user_info[$val['user_reg_date']]['day'] = $val['count'];
                    $day_count = $day_count+$val['count'];
                }
                if($val['type']==2){
                    $user_info[$val['user_reg_date']]['noday'] = $val['count'];
                    $noday_count = $noday_count+$val['count'];
                }
                if($val['type']==5){
                    $user_info[$val['user_reg_date']]['sumday'] = $val['count'];
                    $sumday_count = $sumday_count+$val['count'];
                }
            }
            $user_info['sum']['user_reg_date'] = "总计";
            $user_info['sum']['day'] = $day_count;
            $user_info['sum']['noday'] = $noday_count;
            $user_info['sum']['sumday'] = $sumday_count;
            $this->assign('list',$user_info); // 人数图下面的表
            $this->assign('xAxis_pot', json_encode($date_all));
            $sum = count($count_all[0]);
            foreach($count_all as $key=>$val){
                foreach($val as $k=>$v){
                    $val1[$k]=$val[$sum-1-$k];
                }
                $count_all1[]=$val1;
            }
            $this->assign('data_array', json_encode($count_all1));
            $this->assign('yAxis_title', json_encode("人数"));
            $this->assign('data_name', json_encode(["当天注册当天入金", "当天入金非当天注册", "当天实际入金"]));
            $this->assign('unit', json_encode("人"));
            $this->assign('title_name', json_encode("入金人数统计表"));


            $gcount_all = array();// 存放第二张数据表需要的数据
            // 当天注册当天入金的金额
            $gcount_int = array();// 存放当天入金当天注册的金额，日期为搜索的日期
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=3 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $gcount_int[] = (int)$val['count'];
            }
            array_push($gcount_all,$gcount_int); // 将当天入金当天注册的金额加入到数组

            // 当天入金非当天注册的金额
            $gcount_int = array(); // 存放当天入金非当天注册的金额
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=4 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $gcount_int[] = (int)$val['count'];
            }
            array_push($gcount_all,$gcount_int);// 将当天入金非当天注册的人数加入到数组

            // 当天入金的总金额
            $gcount_int = array();// 存放当天入金的总金额
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=6 and user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc";
            $info = $model->query($sql);
            foreach($info as $key=>$val){
                  $gcount_int[] = (int)$val['count'];
            }
            array_push($gcount_all,$gcount_int);// 将当天入金的总金额加入到数组
            $sql = "select * from " . DB_PREFIX . "statistical_gold where user_reg_date between '$start_time_int' and '$end_time_int' order by user_reg_date desc "; // 查询到表中的数据
            $info = $model->query($sql);
            $day_count = 0;
            $noday_count = 0;
            $sumday_count = 0;
            foreach($info as $key=>$val){
                $date = date("Y-m-d",strtotime($val['user_reg_date']));
                $gold_info[$val['user_reg_date']]['user_reg_date'] = $date;
                if($val['type']==3){
                    $gold_info[$val['user_reg_date']]['day'] = $val['count'];
                    $day_count = $day_count + $val['count'];
                }
                if($val['type']==4){
                    $gold_info[$val['user_reg_date']]['noday'] = $val['count'];
                    $noday_count = $noday_count + $val['count'];
                }
                if($val['type']==6){
                    $gold_info[$val['user_reg_date']]['sumday'] = $val['count'];
                    $sumday_count = $sumday_count + $val['count'];
                }
            }
            $gold_info['sum']['user_reg_date'] = "总计";
            $gold_info['sum']['day'] = $day_count;
            $gold_info['sum']['noday'] = $noday_count;
            $gold_info['sum']['sumday'] = $sumday_count;
            $this->assign('glist',$gold_info); // 金额图下面的表
            $this->assign('xAxis_gpot', json_encode($date_all));
            foreach($gcount_all as $key=>$val){
                foreach($val as $k=>$v){
                    $val1[$k]=$val[$sum-1-$k];
                }
                $gcount_all1[]=$val1;
            }
            $this->assign('data_garray', json_encode($gcount_all1));
            $this->assign('yAxis_gtitle', json_encode("金额"));
            $this->assign('data_gname', json_encode(["当天注册当天日入金", "首次入金非当天注册", "当天实际入金"]));
            $this->assign('gunit', json_encode("元"));
            $this->assign('title_gname', json_encode("入金金额统计表"));
        }

         //昨天的数据，饼图
        if($start_time==$end_time && $start_time != to_date(time(), $format = 'Y-m-d')){
            $model = D();
            $type       = "pie";
            $this->assign('type', $type);
            $count_all = array();
            // 当天入金当天注册的人数
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=1 and user_reg_date = '{$start_time_int}'";
            $res = $model->query($sql);
            array_push($count_all,(int)$res['0']['count']);

            // 当天入金非当天注册的人数
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=2 and user_reg_date = '{$start_time_int}'";
            $res = $model->query($sql);
            array_push($count_all,(int)$res['0']['count']);

            // 当天入金的总人数
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=5 and user_reg_date ='{$start_time_int}' ";
            $res = M("Deal_load")->query($sql);
            $sum_user = $res['0']['count'];
            $pie_data_array  = array();
            $gcount = 0;// 算出两种情况的总数
            foreach($count_all as $key=>$val){
                $gcount += (int)($val);
                $temp     = $val / $sum_user * 100;
                $fraction = round($temp, 2);
                array_push($pie_data_array,$fraction);
            }
            $other = $sum_user-$gcount; // 其他占据的总数
            $temp1 = $other / $sum_user * 100;
            $fraction1 = round($temp1,2);
            array_push($pie_data_array,$fraction1);
            // 搜索出昨天的所有数据
            $sql = "select * from " . DB_PREFIX . "statistical_gold where user_reg_date = $start_time_int"; // 查询到表中的数据
            $info = $model->query($sql);
            $day_count = 0;
            $noday_count = 0;
            $sumday_count = 0;
            foreach($info as $key=>$val){
                $date = date("Y-m-d",strtotime($val['user_reg_date']));
                $user_info[$val['user_reg_date']]['user_reg_date'] = $date;
                if($val['type']==1){
                    $user_info[$val['user_reg_date']]['day'] = $val['count'];
                    $day_count = $day_count + $val['count'];
                }
                if($val['type']==2){
                    $user_info[$val['user_reg_date']]['noday'] = $val['count'];
                    $noday_count = $noday_count + $val['count'];
                }
                if($val['type']==5){
                    $user_info[$val['user_reg_date']]['sumday'] = $val['count'];
                    $sumday_count = $sumday_count + $val['count'];
                }
            }
            $user_info['sum']['user_reg_date'] = "总计";
            $user_info['sum']['day'] = $day_count;
            $user_info['sum']['noday'] = $noday_count;
            $user_info['sum']['sumday'] = $sumday_count;
            $this->assign('list',$user_info);
            $this->assign('series_name', json_encode("人数比例"));
            $this->assign('pie_data_array', json_encode($pie_data_array));
            $this->assign('pie_data_name', json_encode(["当天注册当天入金", "首次入金非当天注册","其他"]));
            $pie_title_name  = $date . "入金人数统计图";
            $this->assign('pie_title_name', json_encode($pie_title_name));

            // 当天入金当天注册的金额
            $gcount_all = array();
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=3 and user_reg_date = '{$start_time_int}'";
            $res = $model->query($sql);
            array_push($gcount_all,(int)$res['0']['count']);
            // 当天入金非当天注册的金额
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=4 and user_reg_date = '{$start_time_int}'";
            $res = $model->query($sql);
            array_push($gcount_all,(int)$res['0']['count']);
            // 当天入金的总金额
            $sql = "select * from " . DB_PREFIX . "statistical_gold where type=6 and user_reg_date ='{$start_time_int}' ";
            $res = M("Deal_load")->query($sql);
            $sum_money = $res['0']['count'];
            $pie_data_garray  = array();
            $gcount = 0;// 算出两种情况的总数
            foreach($gcount_all as $key=>$val){
                $gcount += (int)($val);
                $temp     = $val / $sum_money * 100;
                $fraction = round($temp, 2);
                array_push($pie_data_garray,$fraction);
            }
            $other = $sum_money-$gcount; // 其他占据的总数
            $temp1 = $other / $sum_money * 100;
            $fraction1 = round($temp1,2);
            array_push($pie_data_garray,$fraction1);
            $sql = "select * from " . DB_PREFIX . "statistical_gold where user_reg_date = $start_time_int"; // 查询到表中的数据
            $info = $model->query($sql);
            $day_count = 0;
            $noday_count = 0;
            $sumday_count = 0;
            foreach($info as $key=>$val){
                $date = date("Y-m-d",strtotime($val['user_reg_date']));
                $gold_info[$val['user_reg_date']]['user_reg_date'] = $date;
                if($val['type']==3){
                    $gold_info[$val['user_reg_date']]['day'] = $val['count'];
                    $day_count = $day_count + $val['count'];
                }
                if($val['type']==4){
                    $gold_info[$val['user_reg_date']]['noday'] = $val['count'];
                    $noday_count = $noday_count + $val['count'];
                }
                if($val['type']==6){
                    $gold_info[$val['user_reg_date']]['sumday'] = $val['count'];
                    $sumday_count = $sumday_count + $val['count'];
                }
            }
            $gold_info['sum']['user_reg_date'] = "总计";
            $gold_info['sum']['day'] = $day_count;
            $gold_info['sum']['noday'] = $noday_count;
            $gold_info['sum']['sumday'] = $sumday_count;
            $this->assign("glist",$gold_info);
            $this->assign('series_gname', json_encode("金额比例"));
            $this->assign('pie_data_garray', json_encode($pie_data_garray));
            $this->assign('pie_data_gname', json_encode(["当天注册当天入金", "首次入金非当天注册","其他"]));
            $pie_title_name  = $date . "入金金额统计图";
            $this->assign('pie_title_gname', json_encode($pie_title_name));
        }

        // 今天的数据
        if($start_time ==to_date(time(), $format = 'Y-m-d')){
            // 查询出当天注册的人数
            $type = "pie";
            $this->assign('type',$type);
            $count_all = array();
            $gcount_all = array();
            $stime = strtotime($start_time . "00:00:00");
            $etime = strtotime($strat_time . "23:59:59");
            $sql = "select id from " . DB_PREFIX . "user where create_time >'{$stime}' AND create_time<='{$etime}'";
            $res = $GLOBALS['db']->getAll($sql);
            $id_arr = array();
            foreach ($res as $val) {
                foreach ($val as $v) {
                    $id_arrTmp[] = $v;
                }
            }
            $id_arr = implode(',', $id_arrTmp);
            // 查询当天注册用户是否入金
            $sql    = "select count(distinct(user_id)) from " . DB_PREFIX . "deal_load where user_id in ({$id_arr}) and create_date = '{$start_time}'";
            $res    = $GLOBALS['db']->getALL($sql);
             if ($res) {
                $psum = $res['0']['count(distinct(user_id))'];
            }else{
                $psum = 0;
            }
            // 当天注册当天入金的人数
            array_push($count_all,$psum);
            $sql = "select sum(money) from " . DB_PREFIX . "deal_load where user_id in ({$id_arr}) and create_date = '{$start_time}'";
            $res = $GLOBALS['db']->getOne($sql);
            if ($res) {
                $money = $res;
            } else {
                $money = 0;
            }
            // 当天入金非当天注册的金额
            array_push($gcount_all,$money);
            // 首次入金非当天注册
            $getTodayDealUsersSql = "select user_id from " . DB_PREFIX . "deal_load where user_id not in({$id_arr}) and create_date = '{$start_time}' group by user_id";  // 查出当天入金的所有人
            $getTodayDealUsers    = $GLOBALS['db']->getAll($getTodayDealUsersSql);
            foreach ($getTodayDealUsers as $val) {
                $getTodayDealUsersIds[] = $val['user_id'];
            }
            // 当天入金用户之前有投资的用户
            $sql2               = "select user_id from " . DB_PREFIX . "deal_load where user_id  in(" . implode(",", $getTodayDealUsersIds) . ") and create_date < '{$start_time}' group by user_id";  // 查出当天入金的所有人
            $userBeforeDealList = $GLOBALS['db']->getALL($sql2);
            foreach ($userBeforeDealList as $val) {
                $userBeforeDealUids[] = $val['user_id'];
            }
            $firstDealUserIds = array_diff($getTodayDealUsersIds, $userBeforeDealUids);
            //如果有数据
            if ($firstDealUserIds) {
                $psum1 = count($firstDealUserIds);
                $sql         = "select sum(money) " . DB_PREFIX . "deal_load where user_id in(" . implode(",", $firstDealUserIds) . ")";
                $userSumDeal = $GLOBALS['db']->getOne($sql);
                if ($userSumDeal) {
                    $money1 = $userSumDeal;
                } else {
                    $money1 = 0;
                }
            } else {
                $psum1 = 0;
                $money1 = 0;
            }

        array_push($count_all,$psum1);// 当日入金非当天注册的人数
        array_push($gcount_all,$money1);// 当日入金非当天注册的金额
        // 查出总人数
        $sql    = "select count(distinct(user_id)) from " . DB_PREFIX . "deal_load where create_date = '{$start_time}'";
        $res = M("Deal_load")->query($sql);
        $sum_user = (int)$res['0']['count(distinct(user_id))'];
        $pie_data_array  = array();
        $gcount = 0;// 算出两种情况的总数
        foreach($count_all as $key=>$val){
            $gcount += (int)($val);
            $temp     = $val / $sum_user * 100;
            $fraction = round($temp, 2);
            array_push($pie_data_array,$fraction);
        }
        $other = $sum_user-$gcount; // 其他占据的总数
        $temp1 = $other / $sum_user * 100;
        $fraction1 = round($temp1,2);
        array_push($pie_data_array,$fraction1);
        // 查出当天的人数
        $user_info[$start_time] = array();
        $user_info[$start_time]['user_reg_date'] = date("Y-m-d",strtotime($start_time));
        $user_info[$start_time]['day'] = $count_all['0'];
        $user_info[$start_time]['noday'] = $count_all['1'];
        $user_info[$start_time]['sumday'] = $sum_user;
        $user_info['sum'] = array();
        $user_info['sum']['user_reg_date'] = "总计";
        $user_info['sum']['day'] = $count_all['0'];
        $user_info['sum']['noday'] = $count_all['1'];
        $user_info['sum']['sumday'] = $sum_user;
        $this->assign('list',$user_info);
        $this->assign('series_name', json_encode("人数比例"));
        $this->assign('pie_data_array', json_encode($pie_data_array));
        $this->assign('pie_data_name', json_encode(["当天注册当天入金", "当天首次入金非当天注册","其他"]));
        $pie_title_name  = $date . "入金人数统计图";
        $this->assign('pie_title_name', json_encode($pie_title_name));
        // 查出总金额
        $sql = "select sum(money) from fanwe_deal_load where contract_no != ''  and  is_auto = 0 and create_date = '{$start_time}'";
        $res = M("Deal_load")->query($sql);
        $sum_money = (int)$res['0']['sum(money)'];
        $pie_data_garray  = array();
        $gcount = 0;// 算出两种情况的总数
        foreach($gcount_all as $key=>$val){
            $gcount += (int)($val);
            $temp     = $val / $sum_money * 100;
            $fraction = round($temp, 2);
            array_push($pie_data_garray,$fraction);
        }
        $other = $sum_money-$gcount; // 其他占据的总数
        $temp1 = $other / $sum_money * 100;
        $fraction1 = round($temp1,2);
        array_push($pie_data_garray,$fraction1);
        $gold_info[$start_time] = array();
        $gold_info[$start_time]['user_reg_date'] = date("Y-m-d",strtotime($start_time));
        $gold_info[$start_time]['day'] = $gcount_all['0'];
        $gold_info[$start_time]['noday'] = $gcount_all['1'];
        $gold_info[$start_time]['sumday'] = $sum_money;
        $gold_info['sum'] = array();
        $gold_info['sum']['user_reg_date'] = "总计";
        $gold_info['sum']['day'] = $gcount_all['0'];
        $gold_info['sum']['noday'] = $gcount_all['1'];
        $gold_info['sum']['sumday'] = $sum_money;
        $this->assign("glist",$gold_info);
        $this->assign('series_gname', json_encode("金额比例"));
        $this->assign('pie_data_garray', json_encode($pie_data_garray));
        $this->assign('pie_data_gname', json_encode(["当天注册当天入金", "首次入金非当天注册","其他"]));
        $pie_title_name  = $date . "入金金额统计图";
        $this->assign('pie_title_gname', json_encode($pie_title_name));
        }
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    //充值统计导出
    public function export_csv_recharge_total($page = 1)
    {
        //从url中获取开始时间，
        $start_time     = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time       = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $payment_list = $this->get_payment_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $lists        = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($payment_list as $key => $val) {
                if (empty($val)) {
                    $lists[$key]['payment_count'] = '0';
                } else {
                    $lists[$key]['payment_count'] = $val[0]['money_count'];
                }
                $lists[$key]['payment_date'] = $start_time_int;
                $lists[$key]['payment_type'] = (string) $key;
            }
        } else {
            $lists = M("statistical_payment_notice")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($lists as $key => $val) {
            $client_list['gross'][$val['payment_type']]+=$val['payment_count'];
            $client_list[$val['payment_date']]['payment_date'] = $val['payment_date'];
            if ($val['payment_type'] == $lists[$key]['payment_type']) {
                $client_list[$val['payment_date']][$val['payment_type']] = $val['payment_count'];
            }
        }
        $client_list['gross']['payment_date'] = "总计";
        krsort($client_list);
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel                          = new PHPExcel();
        $num                                  = 1;
        foreach ($client_list as $key => $val) {
            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('B' . $num, "web(元)")
                        ->setCellValue('C' . $num, "wap(元)")
                        ->setCellValue('D' . $num, "Android(元)")
                        ->setCellValue('E' . $num, "ios(元)")
                        ->setCellValue('F' . $num, "合计(元)");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['payment_date'])
                    ->setCellValue('B' . $num, $val['1'])
                    ->setCellValue('C' . $num, $val['2'])
                    ->setCellValue('D' . $num, $val['3'])
                    ->setCellValue('E' . $num, $val['4'])
                    ->setCellValue('F' . $num, $val['0']);


            $num++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $start_time . '~' . $end_time . "的充值统计表";
        php_export_excel($objPHPExcel, $filename);
    }

    //充值统计导出
    public function export_csv_recharge_oto($page = 1)
    {
        //从url中获取开始时间，
        $start_time     = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time       = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $payment_list = $this->get_payment_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $lists        = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($payment_list as $key => $val) {
                if (empty($val)) {
                    $lists[$key]['payment_count'] = '0';
                } else {
                    $lists[$key]['payment_count'] = $val[0]['money_count'];
                }
                $lists[$key]['payment_date'] = $start_time_int;
                $lists[$key]['payment_type'] = (string) $key;
            }
        } else {
            $lists = M("statistical_payment_notice")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($lists as $key => $val) {
            $client_list['gross'][$val['payment_type']]+=$val['payment_count'];
            $client_list[$val['payment_date']]['payment_date'] = $val['payment_date'];
            if ($val['payment_type'] == $lists[$key]['payment_type']) {
                $client_list[$val['payment_date']][$val['payment_type']] = $val['payment_count'];
            }
        }
        $client_list['gross']['payment_date'] = "总计";
        krsort($client_list);
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel                          = new PHPExcel();
        $num                                  = 1;
        foreach ($client_list as $key => $val) {
            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('B' . $num, "线下(元)")
                        ->setCellValue('C' . $num, "线上(元)")
                        ->setCellValue('D' . $num, "合计(元)");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['payment_date'])
                    ->setCellValue('B' . $num, $val['5'])
                    ->setCellValue('C' . $num, $val['6'])
                    ->setCellValue('D' . $num, $val['0']);
            $num++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $start_time . '~' . $end_time . "的充值统计表";
        php_export_excel($objPHPExcel, $filename);
    }

    //提现统计导出
    public function export_csv_extraction_cash($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $map = $this->com_search();

        foreach ($map as $key => $val) {
            if ((!is_array($val)) && ($val <> '')) {
                $parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        $sql_str = "select
		c.create_date as time,
		sum(if(status=1,money,0)) as cgtxze,
		count(*) as rc
		from " . DB_PREFIX . "user_carry as c ";

        //日期期间使用in形式，以确保能正常使用到索引
        if (isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> '') {
            $sql_str .= " where c.create_date in (" . date_in($map['start_time'], $map['end_time']) . ")";
        }

        $sql_str .= " group by c.create_date limit $limit ";
        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);

        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_extraction_cash'), $page + 1);

            $extraction_cash_value   = array(
                'time'   => '""',
                'cgtxze' => '""',
                'rc'     => '""'
            );
            if ($page == 1)
                $content_extraction_cash = iconv("utf-8", "gbk", "时间,成功提现总额,人次");

            if ($page == 1)
                $content_extraction_cash = $content_extraction_cash . "\n";

            foreach ($list as $k => $v) {
                $extraction_cash_value           = array();
                $extraction_cash_value['time']   = iconv('utf-8', 'gbk', '"' . $v['time'] . '"');
                $extraction_cash_value['cgtxze'] = iconv('utf-8', 'gbk', '"' . number_format($v['cgtxze'], 2) . '"');
                $extraction_cash_value['rc']     = iconv('utf-8', 'gbk', '"' . $v['rc'] . '"');

                $content_extraction_cash .= implode(",", $extraction_cash_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=extraction_cash_list.csv");
            echo $content_extraction_cash;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    // 入金统计导出
    public function export_gold($page = 1)
    {
        //从url中获取开始时间，
        $start_time = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time   = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if($start_time==$end_time){
                $sql = "select * from ".DB_PREFIX."statistical_gold where user_reg_date='{$start_time_int}'";
                $res = M("Statistical_gold")->query($sql);
        }else{
                $sql = "select * from ".DB_PREFIX."statistical_gold where user_reg_date between '{$start_time_int}' and '{$end_time_int}' ";
                $res = M("Statistical_gold")->query($sql);
        }
        $list = array();
        foreach($res as $val){
            $date = date("Y-m-d",strtotime($val['user_reg-date']));
            $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
            if($val['type']==1){
                $list[$val['user_reg_date']]['user_day'] = $val['count']; // 当日入金当天注册人
            }
            if($val['type']==2){
                $list[$val['user_reg_date']]['user_noday'] = $val['count'];// 当日入金非当天注册人
            }
            if($val['type']==3){
                $list[$val['user_reg_date']]['money_day'] = $val['count'];// 当日入金当天注册金额
            }
            if($val['type']==4){
                $list[$val['user_reg_date']]['money_noday'] = $val['count'];// 当日入金非当天注册
            }
            if($val['type']==5){
                $list[$val['user_reg_date']]['user_sum'] = $val['count'];// 总人数
            }
           if($val['type']==6){
                $list[$val['user_reg_date']]['money_sum'] = $val['count'];// 总金额
            }
        }

        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $num         = 1;
        $count = count($list)+4;
        $cnt = $count;
        foreach ($list as $key => $val) {
            if ($num == 1 && $count==count($list)+4) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('A' . $count, '时间')
                        ->setCellValue('B' . $num, "当天注册当天入金(人)")
                        ->setCellValue('B' . $count, "当天注册当天入金(元)")
                        ->setCellValue('C' . $num, "当天入金非当天注册(人)")
                        ->setCellValue('C' . $count, "当天入金非当天注册(元)")
                        ->setCellValue('D' . $num, "当天实际入金(人)")
                        ->setCellValue('D' . $count, "当天实际入金(元)");
                $count=count($list)+5;
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['user_reg_date'])
                    ->setCellValue('A' . $count, $val['user_reg_date'])
                    ->setCellValue('B' . $num, $val['user_day'])
                    ->setCellValue('B' . $count, $val['money_day'])
                    ->setCellValue('C' . $num, $val['user_noday'])
                    ->setCellValue('C' . $count, $val['money_noday'])
                    ->setCellValue('D' . $num, $val['user_sum'])
                    ->setCellValue('D' . $count, $val['money_sum']);


            $num++;
            $count++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':D'.$cnt)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':D'.$cnt)->getFill()->getStartColor()->setARGB('FFFFD700');
         $objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':D'.$cnt)->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        if ($start_time = $end_time) {
            $filename = $start_time . "的入金统计表";
        } else {
            $filename = $start_time . '~' . $end_time . "的入金统计表";
        }
        php_export_excel($objPHPExcel, $filename);
    }

    //登录统计导出
    public function export_csv_login_total($page = 1)
    {
        //从url中获取开始时间，

        $start_time = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time   = $_REQUEST["end_time"];

        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {

            $sql_str = "select
		login_count,
                                   login_date,
                                   login_type
                        from " . DB_PREFIX . "statistical_login_log where login_date= '$start_time' ";

            $model = D();
            $info  = $model->query($sql_str);



            //将数组重整，此数据是放到数据中的
            $lists = $info;
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
//            foreach ($info as $key => $val) {
//
//                $lists[$key]['login_type'] = $val[0]['login_type'];
//
//                $lists[$key]['login_date'] = $start_time_int;
//                $lists[$key]['login_count'] = $val[0]['login_count'];
//            }
        } else {
            $lists = M("statistical_login_log")->where("login_date>='$start_time' and login_date<='$end_time'")->findAll();
        }

        $list = array();
        foreach ($lists as $key => $val) {
            $list[$val['login_date']]['login_date'] = $val['login_date'];
            if ($val['login_type'] == 1) {
                $list[$val['login_date']]['web'] = $val['login_count'];
            }
            if ($val['login_type'] == 2) {
                $list[$val['login_date']]['wap'] = $val['login_count'];
            }
            if ($val['login_type'] == 3) {
                $list[$val['login_date']]['Android'] = $val['login_count'];
            }
            if ($val['login_type'] == 4) {
                $list[$val['login_date']]['ios'] = $val['login_count'];
            }
        }
        $interval = (strtotime($end_time) - strtotime($start_time)) / 86400;

        for ($i = 0; $i <= $interval; $i++) {
            $temp_count = 0;
            $date_temp  = to_date(to_timespan($start_time, $format     = 'Y-m-d H:i:s') + ($i * 86400), "Y-m-d");
            foreach ($list as $key => $value) {

                if ($value['login_date'] == $date_temp) {
                    $list[$key]['all'] = $value['web'] + $value['wap'] + $value['Android'] + $value['ios'];
                }
            }
        }
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $num         = 1;
        foreach ($list as $key => $val) {
            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('B' . $num, "web")
                        ->setCellValue('C' . $num, "wap")
                        ->setCellValue('D' . $num, "Android")
                        ->setCellValue('E' . $num, "ios")
                        ->setCellValue('F' . $num, "总共");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['login_date'])
                    ->setCellValue('B' . $num, $val['web'])
                    ->setCellValue('C' . $num, $val['wap'])
                    ->setCellValue('D' . $num, $val['Android'])
                    ->setCellValue('E' . $num, $val['ios'])
                    ->setCellValue('F' . $num, $val['all']);


            $num++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $start_time . '~' . $end_time . "的登录统计表";
        php_export_excel($objPHPExcel, $filename);
    }

    //用户统计导出
    public function export_csv_users_total($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $map = $this->com_search();

        foreach ($map as $key => $val) {
            if ((!is_array($val)) && ($val <> '')) {
                $parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        $sql_str = "select
		u.create_date as time,
		count(*) as yhzcrs
		from " . DB_PREFIX . "user as u ";

        //日期期间使用in形式，以确保能正常使用到索引
        if (isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> '') {
            $sql_str .= " where u.create_date in (" . date_in($map['start_time'], $map['end_time']) . ")";
        }

        $sql_str .= " group by u.create_date limit $limit ";
        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);



        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_users_total'), $page + 1);

            $users_total_value   = array(
                'time'   => '""',
                'yhzcrs' => '""'
            );
            if ($page == 1)
                $content_users_total = iconv("utf-8", "gbk", "时间,用户注册人数");

            if ($page == 1)
                $content_users_total = $content_users_total . "\n";

            foreach ($list as $k => $v) {
                $users_total_value           = array();
                $users_total_value['time']   = iconv('utf-8', 'gbk', '"' . $v['time'] . '"');
                $users_total_value['yhzcrs'] = iconv('utf-8', 'gbk', '"' . $v['yhzcrs'] . '"');

                $content_users_total .= implode(",", $users_total_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=users_total_list.csv");
            echo $content_users_total;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //网站垫付导出
    public function export_csv_advance_total($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));
        $map   = $this->com_search();

        foreach ($map as $key => $val) {
            if ((!is_array($val)) && ($val <> '')) {
                $parameter .= "$key=" . urlencode($val) . "&";
            }
        }

        $sql_str = "select
		g.create_date as time,
		sum(repay_money) as dhbxze,
		sum(manage_money) as dhglfze,
		sum(impose_money) as dhfxze,
		sum(manage_impose_money) as dhyqglfze
		from " . DB_PREFIX . "generation_repay as g ";

        //日期期间使用in形式，以确保能正常使用到索引
        if (isset($map['start_time']) && $map['start_time'] <> '' && isset($map['end_time']) && $map['end_time'] <> '') {
            $sql_str .= " where g.create_date in (" . date_in($map['start_time'], $map['end_time']) . ")";
        }

        $sql_str .= " group by g.create_date limit $limit ";
        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);

        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_advance_total'), $page + 1);

            $advance_total_value   = array(
                'time'      => '""',
                'dhbxze'    => '""',
                'dhglfze'   => '""',
                'dhfxze'    => '""',
                'dhyqglfze' => '""'
            );
            if ($page == 1)
                $content_advance_total = iconv("utf-8", "gbk", "时间,代还本息总额,代还管理费总额,代还罚息总额,代还逾期管理费总额");

            if ($page == 1)
                $content_advance_total = $content_advance_total . "\n";

            foreach ($list as $k => $v) {
                $advance_total_value              = array();
                $advance_total_value['time']      = iconv('utf-8', 'gbk', '"' . $v['time'] . '"');
                $advance_total_value['dhbxze']    = iconv('utf-8', 'gbk', '"' . number_format($v['dhbxze'], 2) . '"');
                $advance_total_value['dhglfze']   = iconv('utf-8', 'gbk', '"' . number_format($v['dhglfze'], 2) . '"');
                $advance_total_value['dhfxze']    = iconv('utf-8', 'gbk', '"' . number_format($v['dhfxze'], 2) . '"');
                $advance_total_value['dhyqglfze'] = iconv('utf-8', 'gbk', '"' . number_format($v['dhyqglfze'], 2) . '"');

                $content_advance_total .= implode(",", $advance_total_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=advance_total_list.csv");
            echo $content_advance_total;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //网站费用统计导出
    public function export_csv_cost_total($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);

        $sql_str = "select
		count(DISTINCT user_id) as glyhs,
		sum(if(type = 1,money,0)) as czsxf,
		sum(if(type = 9,money,0)) as txsxf,
		sum(if(type = 10,money,0)) as jkglf,
		sum(if(type = 12,money,0)) as yqglf,
		sum(if(type = 13,money,0)) as rgcz,
		sum(if(type = 14,money,0)) as jkfwf,
		sum(if(type = 17,money,0)) as zqzrglf,
		sum(if(type = 18,money,0)) as kfjl,
		sum(if(type = 20,money,0)) as tbglf,
		sum(if(type = 22,money,0)) as dh,
		sum(if(type = 23,money,0)) as yqfl,
		sum(if(type = 24,money,0)) as tbfl,
		sum(if(type = 25,money,0)) as qdcg,
		sum(if(type = 26,money,0)) as yqfj,
		sum(if(type = 27,money,0)) as qtfy,
		sum(if(type = 28,money,0)) as tzjl,
		sum(if(type = 29,money,0)) as hbjl
		from " . DB_PREFIX . "site_money_log where 1 = 1  ";

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (create_time between $begin_time and $end_time )";
            }
        }

        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);

        if ($list) {
            //register_shutdown_function(array(&$this, 'export_csv_cost_total'), $page+1);

            $cost_total_value   = array(
                'glyhs'   => '""',
                'czsxf'   => '""',
                'txsxf'   => '""',
                'jkglf'   => '""',
                'yqglf'   => '""',
                'rgcz'    => '""',
                'jkfwf'   => '""',
                'zqzrglf' => '""',
                'kfjl'    => '""',
                'tbglf'   => '""',
                'dh'      => '""',
                'yqfl'    => '""',
                'tbfl'    => '""',
                'qdcg'    => '""',
                'yqfj'    => '""',
                'qtfy'    => '""',
                'tzjl'    => '""',
                'hbjl'    => '""'
            );
            if ($page == 1)
                $content_cost_total = iconv("utf-8", "gbk", "关联用户数,充值手续费,提现手续费,借款管理费,逾期管理费,人工操作,借款服务费,债权转让管理费,开户奖励,投标管理费,兑换,邀请返利,投标返利,签到成功,逾期罚金（垫付后）,其他费用,投资奖励,红包奖励");

            if ($page == 1)
                $content_cost_total = $content_cost_total . "\n";

            foreach ($list as $k => $v) {
                $cost_total_value            = array();
                $cost_total_value['glyhs']   = iconv('utf-8', 'gbk', '"' . $v['glyhs'] . '"');
                $cost_total_value['czsxf']   = iconv('utf-8', 'gbk', '"' . number_format($v['czsxf'], 2) . '"');
                $cost_total_value['txsxf']   = iconv('utf-8', 'gbk', '"' . number_format($v['txsxf'], 2) . '"');
                $cost_total_value['jkglf']   = iconv('utf-8', 'gbk', '"' . number_format($v['jkglf'], 2) . '"');
                $cost_total_value['yqglf']   = iconv('utf-8', 'gbk', '"' . number_format($v['yqglf'], 2) . '"');
                $cost_total_value['rgcz']    = iconv('utf-8', 'gbk', '"' . number_format($v['rgcz'], 2) . '"');
                $cost_total_value['jkfwf']   = iconv('utf-8', 'gbk', '"' . number_format($v['jkfwf'], 2) . '"');
                $cost_total_value['zqzrglf'] = iconv('utf-8', 'gbk', '"' . number_format($v['zqzrglf'], 2) . '"');
                $cost_total_value['kfjl']    = iconv('utf-8', 'gbk', '"' . number_format($v['kfjl'], 2) . '"');
                $cost_total_value['tbglf']   = iconv('utf-8', 'gbk', '"' . number_format($v['tbglf'], 2) . '"');
                $cost_total_value['dh']      = iconv('utf-8', 'gbk', '"' . number_format($v['dh'], 2) . '"');
                $cost_total_value['yqfl']    = iconv('utf-8', 'gbk', '"' . number_format($v['yqfl'], 2) . '"');
                $cost_total_value['tbfl']    = iconv('utf-8', 'gbk', '"' . number_format($v['tbfl'], 2) . '"');
                $cost_total_value['qdcg']    = iconv('utf-8', 'gbk', '"' . number_format($v['qdcg'], 2) . '"');
                $cost_total_value['yqfj']    = iconv('utf-8', 'gbk', '"' . number_format($v['yqfj'], 2) . '"');
                $cost_total_value['qtfy']    = iconv('utf-8', 'gbk', '"' . number_format($v['qtfy'], 2) . '"');
                $cost_total_value['tzjl']    = iconv('utf-8', 'gbk', '"' . number_format($v['tzjl'], 2) . '"');
                $cost_total_value['hbjl']    = iconv('utf-8', 'gbk', '"' . number_format($v['hbjl'], 2) . '"');

                $content_cost_total .= implode(",", $cost_total_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=cost_total_list.csv");
            echo $content_cost_total;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //充值明细导出
    public function export_csv_recharge_info($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (n.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['notice_sn']) != '') {
            $notice_sn = trim($_REQUEST['notice_sn']);
        }
        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['is_paid']) != '') {
            $is_paid = trim($_REQUEST['is_paid']);
        }
        if (trim($_REQUEST['memo']) != '') {
            $memo = trim($_REQUEST['memo']);
        }



        $sql_str = "select
		n.create_date as time,
		n.notice_sn as zfdh,
		n.user_id,
		u.user_name as hymc,
		n.money as yfje,
		p.name as zffs,
		if(n.is_paid = 1,'已支付','未支付') as zfzt,
		n.memo as zfbz
		from " . DB_PREFIX . "payment_notice as n LEFT JOIN " . DB_PREFIX . "user as u on u.id=n.user_id LEFT JOIN " . DB_PREFIX . "payment as p on  p.id=n.payment_id $condtion ";

        if ($notice_sn) {
            $sql_str .=" and n.notice_sn = '$notice_sn'";
        }
        if ($user_name) {
            $sql_str .=" and u.user_name like '%$user_name%'";
        }
        if ($memo) {
            $sql_str .=" and n.memo like '%$memo%'";
        }


        if (isset($_REQUEST['is_paid'])) {
            if ($is_paid == 4) {
                //$sql_str .="";
            } elseif ($is_paid == 1) {
                $sql_str .=" and n.is_paid = 1 ";
            } elseif ($is_paid == 2) {
                $sql_str .=" and n.is_paid = 0 ";
            }
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (n.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (n.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (n.create_time between $begin_time and $end_time )";
            }
        }
        $sql_str .= " limit $limit ";

        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);


        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_recharge_info'), $page + 1);

            $recharge_info_value   = array(
                'time' => '""',
                'zfdh' => '""',
                'hymc' => '""',
                'yfje' => '""',
                'zffs' => '""',
                'zfzt' => '""',
                'zfbz' => '""'
            );
            if ($page == 1)
                $content_recharge_info = iconv("utf-8", "gbk", "时间,支付单号,会员名称,应付金额,支付方式,支付状态,支付备注");

            if ($page == 1)
                $content_recharge_info = $content_recharge_info . "\n";

            foreach ($list as $k => $v) {
                $recharge_info_value         = array();
                $recharge_info_value['time'] = iconv('utf-8', 'gbk', '"' . $v['time'] . '"');
                $recharge_info_value['zfdh'] = iconv('utf-8', 'gbk', '"' . $v['zfdh'] . '"');
                $recharge_info_value['hymc'] = iconv('utf-8', 'gbk', '"' . $v['hymc'] . '"');
                $recharge_info_value['yfje'] = iconv('utf-8', 'gbk', '"' . number_format($v['yfje'], 2) . '"');
                $recharge_info_value['zffs'] = iconv('utf-8', 'gbk', '"' . $v['zffs'] . '"');
                $recharge_info_value['zfzt'] = iconv('utf-8', 'gbk', '"' . $v['zfzt'] . '"');
                $recharge_info_value['zfbz'] = iconv('utf-8', 'gbk', '"' . $v['zfbz'] . '"');

                $content_recharge_info .= implode(",", $recharge_info_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=recharge_info_list.csv");
            echo $content_recharge_info;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //提现明细导出
    public function export_csv_extraction_cash_info($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (c.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }


        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['status']) != '') {
            $status = trim($_REQUEST['status']);
        }

        $sql_str = "select
		c.create_date as time,
		u.id as hymc,
		c.money as txje,
		c.fee as sxf,
		case c.status
		when 0 then '待审核'
		when 1 then '已付款'
		when 2 then '未通过'
		when 3 then '待付款'
		else
		 '撤销'
		end as txzt,
		FROM_UNIXTIME(c.update_time + 28800, '%Y-%m-%d') as clsj
		from " . DB_PREFIX . "user_carry as c left join " . DB_PREFIX . "user as u on u.id=c.user_id  $condtion ";

        if ($user_name) {
            $sql_str .=" and u.user_name like '%$user_name%'";
        }

        if (isset($_REQUEST['status'])) {
            if ($status == 5) {
                //$sql_str .="";
            } elseif ($status == 1) {
                $sql_str .=" and c.status = 0 ";
            } elseif ($status == 2) {
                $sql_str .=" and c.status = 1 ";
            } elseif ($status == 3) {
                $sql_str .=" and c.status = 2 ";
            } elseif ($status == 4) {
                $sql_str .=" and c.status = 4 ";
            }
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (c.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (c.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (c.create_time between $begin_time and $end_time )";
            }
        }

        $sql_str .= " limit $limit ";

        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);

        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_extraction_cash_info'), $page + 1);

            $extraction_cash_info_value   = array(
                'time' => '""',
                'hymc' => '""',
                'txje' => '""',
                'sxf'  => '""',
                'txzt' => '""',
                'clsj' => '""'
            );
            if ($page == 1)
                $content_extraction_cash_info = iconv("utf-8", "gbk", "时间,会员名称,提现金额,手续费,提现状态,处理时间");

            if ($page == 1)
                $content_extraction_cash_info = $content_extraction_cash_info . "\n";

            foreach ($list as $k => $v) {
                $extraction_cash_info_value         = array();
                $extraction_cash_info_value['time'] = iconv('utf-8', 'gbk', '"' . $v['time'] . '"');
                $extraction_cash_info_value['hymc'] = iconv('utf-8', 'gbk', '"' . $v['hymc'] . '"');
                $extraction_cash_info_value['txje'] = iconv('utf-8', 'gbk', '"' . number_format($v['txje'], 2) . '"');
                $extraction_cash_info_value['sxf']  = iconv('utf-8', 'gbk', '"' . number_format($v['sxf'], 2) . '"');
                $extraction_cash_info_value['txzt'] = iconv('utf-8', 'gbk', '"' . $v['txzt'] . '"');
                $extraction_cash_info_value['clsj'] = iconv('utf-8', 'gbk', '"' . $v['clsj'] . '"');
                $content_extraction_cash_info .= implode(",", $extraction_cash_info_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=extraction_cash_info_list.csv");
            echo $content_extraction_cash_info;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //用户明细导出
    public function export_csv_users_info($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (u.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['user_name']) != '') {
            $user_name = trim($_REQUEST['user_name']);
        }
        if (trim($_REQUEST['email']) != '') {
            $email = trim($_REQUEST['email']);
        }
        if (trim($_REQUEST['mobile']) != '') {
            $mobile = trim($_REQUEST['mobile']);
        }

        if (trim($_REQUEST['level_id']) != '') {
            $level_id = trim($_REQUEST['level_id']);
        }

        $this->assign("level_list", M("UserLevel")->findAll());


        $sql_str = "select
		u.create_date as zcsj,
		u.id as yhmc,
		u.email as hyyj,
		u.mobile as sjh,
		u.money as yhye,
		u.lock_money as djzj,
		l.name as yhdj
		from " . DB_PREFIX . "user as u left join " . DB_PREFIX . "user_level as l on l.id=u.level_id  $condtion ";

        if ($user_name) {
            $sql_str .=" and u.user_name like '%$user_name%'";
        }
        if ($email) {
            $sql_str .=" and u.email like '%$email%'";
        }
        if ($mobile) {
            $sql_str .=" and u.mobile like '%$mobile%'";
        }

        if ($level_id) {
            $sql_str .=" and l.id = '$level_id'";
        }

        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (u.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (u.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (u.create_time between $begin_time and $end_time )";
            }
        }

        $sql_str .= " limit $limit ";

        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);

        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_users_info'), $page + 1);

            $users_info_value   = array(
                'zcsj' => '""',
                'yhmc' => '""',
                'hyyj' => '""',
                'sjh'  => '""',
                'yhye' => '""',
                'djzj' => '""',
                'yhdj' => '""'
            );
            if ($page == 1)
                $content_users_info = iconv("utf-8", "gbk", "注册时间,会员名称,会员邮件,手机号,会员余额,冻结资金,会员等级");

            if ($page == 1)
                $content_users_info = $content_users_info . "\n";

            foreach ($list as $k => $v) {
                $users_info_value         = array();
                $users_info_value['zcsj'] = iconv('utf-8', 'gbk', '"' . $v['zcsj'] . '"');
                $users_info_value['yhmc'] = iconv('utf-8', 'gbk', '"' . $v['yhmc'] . '"');
                $users_info_value['hyyj'] = iconv('utf-8', 'gbk', '"' . $v['hyyj'] . '"');
                $users_info_value['sjh']  = iconv('utf-8', 'gbk', '"' . $v['sjh'] . '"');
                $users_info_value['yhye'] = iconv('utf-8', 'gbk', '"' . number_format($v['yhye'], 2) . '"');
                $users_info_value['djzj'] = iconv('utf-8', 'gbk', '"' . number_format($v['djzj'], 2) . '"');
                $users_info_value['yhdj'] = iconv('utf-8', 'gbk', '"' . $v['yhdj'] . '"');


                $content_users_info .= implode(",", $users_info_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=users_info_list.csv");
            echo $content_users_info;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //垫付明细导出
    public function export_csv_advance_info($page = 1)
    {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time   = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        $time       = trim($_REQUEST['time']);
        if (trim($_REQUEST['time'])) {
            $condtion = " where  (r.create_date = '$time')";
        } else {
            $condtion = " where 1=1 ";
        }

        if (trim($_REQUEST['name']) != '') {
            $name = trim($_REQUEST['name']);
        }
        if (trim($_REQUEST['adm_name']) != '') {
            $adm_name = trim($_REQUEST['adm_name']);
        }
        if (trim($_REQUEST['agency_id']) != '') {
            $agency_id = trim($_REQUEST['agency_id']);
        }

        $this->assign("agency_list", M("User")->where('user_type = 2')->findAll());

        $sql_str = "select
		r.create_date as dhsj,
		d.sub_name as dkmc,
		CONCAT('第',lr.l_key + 1,'期') as djq,
		a.adm_name as gly,
		da.name as dbjg,
		r.repay_money as dhbx,
		r.manage_money as dhglf,
		r.impose_money as dhfx,
		r.manage_impose_money dhdsyqglf,
		r.deal_id
		from " . DB_PREFIX . "generation_repay as r
		left join " . DB_PREFIX . "deal as d on d.id=r.deal_id
		left join " . DB_PREFIX . "deal_load_repay as lr on lr.repay_id=r.repay_id
		left join " . DB_PREFIX . "admin as a on a.id=r.admin_id
		left join " . DB_PREFIX . "deal_agency as da on da.id=r.agency_id
		$condtion ";

        if ($name) {
            $sql_str .=" and d.name like '%$name%'";
        }
        if ($adm_name) {
            $sql_str .=" and a.adm_name like '%$adm_name%'";
        }

        if ($agency_id) {
            $sql_str .=" and da.id = '$agency_id'";
        }


        if ($begin_time > 0 || $end_time > 0) {
            if ($begin_time > 0 && $end_time == 0) {
                $sql_str .= " and (r.create_time > $begin_time)";
            } elseif ($begin_time == 0 && $end_time > 0) {
                $sql_str .= " and (r.create_time < $end_time )";
            } elseif ($begin_time > 0 && $end_time > 0) {
                $sql_str .= " and (r.create_time between $begin_time and $end_time )";
            }
        }

        $sql_str .= " limit $limit ";

        $list = array();
        $list = $GLOBALS['db']->getAll($sql_str);



        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv_advance_info'), $page + 1);

            $advance_info_value   = array(
                'dhsj'      => '""',
                'dkmc'      => '""',
                'djq'       => '""',
                'gly'       => '""',
                'dbjg'      => '""',
                'dhbx'      => '""',
                'dhglf'     => '""',
                'dhfx'      => '""',
                'dhdsyqglf' => '""'
            );
            if ($page == 1)
                $content_advance_info = iconv("utf-8", "gbk", "代还时间,贷款名称,第几期,管理员,担保机构,代还本息,代还管理费,代还罚息,代还多少逾期管理费");

            if ($page == 1)
                $content_advance_info = $content_advance_info . "\n";

            foreach ($list as $k => $v) {
                $advance_info_value              = array();
                $advance_info_value['dhsj']      = iconv('utf-8', 'gbk', '"' . $v['dhsj'] . '"');
                $advance_info_value['dkmc']      = iconv('utf-8', 'gbk', '"' . $v['dkmc'] . '"');
                $advance_info_value['djq']       = iconv('utf-8', 'gbk', '"' . $v['djq'] . '"');
                $advance_info_value['gly']       = iconv('utf-8', 'gbk', '"' . $v['gly'] . '"');
                $advance_info_value['dbjg']      = iconv('utf-8', 'gbk', '"' . $v['dbjg'] . '"');
                $advance_info_value['dhbx']      = iconv('utf-8', 'gbk', '"' . number_format($v['dhbx'], 2) . '"');
                $advance_info_value['dhglf']     = iconv('utf-8', 'gbk', '"' . number_format($v['dhglf'], 2) . '"');
                $advance_info_value['dhfx']      = iconv('utf-8', 'gbk', '"' . number_format($v['dhfx'], 2) . '"');
                $advance_info_value['dhdsyqglf'] = iconv('utf-8', 'gbk', '"' . number_format($v['dhdsyqglf'], 2) . '"');


                $content_advance_info .= implode(",", $advance_info_value) . "\n";
            }

            header("Content-Disposition: attachment; filename=advance_info_list.csv");
            echo $content_advance_info;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    /**
      +----------------------------------------------------------
     * 通过for循环来依次导入各种类型的数据 1表示web 2表示wap 3表示android 4表示ios 5表示线下 6表示线上
      +----------------------------------------------------------
     * @access private
      +----------------------------------------------------------
     * @param string $date  $date默认为空，如果是空则是今天的时间
      +----------------------------------------------------------
     * @return array三维数组
      +----------------------------------------------------------
     */
    private function get_payment_arr($date)
    {
        if (!$date) {
            $date = to_date(time(), "Y-m-d");
        }
        $where = '';
        for ($i = 0; $i <= 6; $i++) {
            if ($i == 0) {
                $where = "where n.pay_date='$date'";
            } elseif ($i == 5) {
                $where = "where u.admin_id<>0 and n.pay_date='$date'";
            } elseif ($i == 6) {
                $where = "where u.admin_id=0 and n.pay_date='$date'";
            } else {
                $where = "where n.incharge_source=$i and n.pay_date='$date'";
            }
            $list[] = $this->get_chart_list($where);
        }
        return $list;
    }

    /**
      +----------------------------------------------------------
     * 根据where查询出充值记录
      +----------------------------------------------------------
     * @access private
      +----------------------------------------------------------
     * @param string $where  where条件
      +----------------------------------------------------------
     * @return array二维数组
      +----------------------------------------------------------
     */
    private function get_chart_list($where = '')
    {
        $sql_str = "select
		n.pay_date,
		sum(if(n.is_paid=1,n.money,0)) as money_count,
                n.incharge_source,
                admin_id
		from " . DB_PREFIX . "payment_notice as n left join " . DB_PREFIX . "user as u on n.user_id=u.id $where";
        $sql_str .= " group by n.pay_date ";
        $model   = D();
        $info    = $model->query($sql_str);
        return $info;
    }

    //充值统计
    public function website_recharge_total()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $lists = $this->get_payment_arr($start_time);

            //将数组重整，此数据是放到数据中的
            $statistics_data = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($lists as $key => $val) {
                if (empty($val)) {
                    $statistics_data[$key]['payment_count'] = '0';
                } else {
                    $statistics_data[$key]['payment_count'] = $val[0]['money_count'];
                }
                $statistics_data[$key]['payment_date'] = $start_time_int;
                $statistics_data[$key]['payment_type'] = (string) $key;
            }
            $list = array();
            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    if (empty($val)) {
                        $list[$key] = 0;
                    } else {
                        $list[$key] = round($val[0]['money_count'] / ($lists[0][0]['money_count']) * 100, 2);
                    }
                    if ($key > 0 && $key <= 4) {
                        $client_side_list[] = $list[$key];
                    } else if ($key == 5 || $key == 6) {
                        $pie_data_array_oto[] = $list[$key];
                    }
                }
            }
            //将数据传递到模块中
            $data_name_raw = [ 'web', 'wap', 'Android', 'ios'];
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            //$this->assign("list", $statistics_data);//表格里的数据
            $date_list[]   = $start_time_int;
        } else {

            $date_lists = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->field("payment_date")->group("payment_date")->findAll();
            //将数组转换成图形所需要的数据
            for ($i = 0; $i <= 4; $i++) {
                $lists[] = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int and payment_type=$i")->findAll();
            }
            $list = array();

            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    foreach ($val as $k => $v) {
                        $list[$key][] = (float) $v['payment_count'];
                    }
                }
            }
            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['payment_date'];
                }
            }
            $data_name_raw   = ['全部', 'web', 'wap', 'Android', 'ios'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $statistics_data = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($statistics_data as $key => $val) {
            $client_list['gross'][$val['payment_type']]+=$val['payment_count'];
            $client_list[$val['payment_date']]['payment_date'] = $val['payment_date'];
            if ($val['payment_type'] == $statistics_data[$key]['payment_type']) {
                $client_list[$val['payment_date']][$val['payment_type']] = $val['payment_count'];
            }
        }
        $client_list['gross']['payment_date'] = "总计";
        krsort($client_list);
        $this->assign('client_list', $client_list); //表格中的数据
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));

        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);
        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode($list));

        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));

        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));
        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));

        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($client_side_list));

        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    public function website_recharge_oto()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //一天的数据
        if ($start_time == $end_time) {
            $lists           = $this->get_payment_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $statistics_data = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($lists as $key => $val) {
                if ($key == 0 || $key == 5 || $key == 6) {
                    if (empty($val)) {
                        $statistics_data[$key]['payment_count'] = '0';
                    } else {
                        $statistics_data[$key]['payment_count'] = $val[0]['money_count'];
                    }
                    $statistics_data[$key]['payment_date'] = $start_time_int;
                    $statistics_data[$key]['payment_type'] = (string) $key;
                }
            }

            $list = array();
            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    if (empty($val)) {
                        $list[$key] = 0;
                    } else {
                        $list[$key] = round($val[0]['money_count'] / ($lists[0][0]['money_count']) * 100, 2);
                    }
                    if ($key > 0 && $key <= 4) {
                        $client_side_list[] = $list[$key];
                    } else if ($key == 5 || $key == 6) {
                        $pie_data_array_oto[] = $list[$key];
                    }
                }
            }
            //将数据传递到模块中
            $data_name_raw = [ '线上', '线下'];
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $date_list[]   = $start_time_int;
        } else {
            $date_lists = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->field("payment_date")->group("payment_date")->findAll();
            //将数组转换成图形所需要的数据
            for ($i = 0; $i <= 6; $i++) {
                $lists[] = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int and payment_type=$i")->findAll();
            }
            $list = array();
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($key == 0 || $key == 5 || $key == 6) {
                            $list[$key][] = (float) $v['payment_count'];
                        }
                    }
                }
            }
            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['payment_date'];
                }
            }
            $data_name_raw   = ['全部', '线上', '线下'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $this->assign('list', $lists); //表格中的数据
            $statistics_data = M("statistical_payment_notice")->order("payment_date asc")->where("payment_date>=$start_time_int and payment_date<=$end_time_int")->findAll();
        }

        $client_list = array();
        foreach ($statistics_data as $key => $val) {
            $client_list['gross'][$val['payment_type']]+=$val['payment_count'];
            $client_list[$val['payment_date']]['payment_date'] = $val['payment_date'];
            if ($val['payment_type'] == $statistics_data[$key]['payment_type']) {
                $client_list[$val['payment_date']][$val['payment_type']] = $val['payment_count'];
            }
        }
        $client_list['gross']['payment_date'] = "总计";
        krsort($client_list);


        $this->assign('client_list', $client_list); //表格中的数据
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));

        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);

        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode(array_values($list)));

        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));

        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));

        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));

        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($pie_data_array_oto));
        $this->assign("start_time", $start_time); //开始时间
        $this->assign("end_time", $end_time); //结束时间
        $this->display();
    }

    public function website_login_info()
    {

        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型


        $login_list = array();
        //所选日期内的数据
        if ($start_time != $end_time) {

            //将日期数据存为图表规定的数据格式
            $interval = (to_timespan($end_time, $format   = 'Y-m-d H:i:s') - to_timespan($start_time, $format   = 'Y-m-d H:i:s')) / 86400;


            $date_all = array();
            for ($i = 0; $i <= $interval; $i++) {
                $date_temp                            = to_date(to_timespan($start_time, $format                               = 'Y-m-d H:i:s') + ($i * 86400), "Y-m-d");
                array_push($date_all, $date_temp);
                $login_list[$date_temp]['login_date'] = $date_temp;
            }
            //设置图表为折线图
            $type = "line";
            $this->assign('type', $type);


            //循环将数据保存为图表规定的数据格式
            $count_all = array();
            for ($i = 1; $i <= 4; $i++) {


                $sql_str = "select
		login_count,
                                   login_date,
                                   login_type
                        from " . DB_PREFIX . "statistical_login_log where login_type=$i and login_date between '$start_time' and '$end_time' ";

                $model = D();
                $info  = $model->query($sql_str);
                $count_int = array();
                foreach ($info as $key => $value) {

                    $count_int[] = (int) $value['login_count'];
                }

                array_push($count_all, $count_int);
            }





            $sql_str = "select
		login_count,
                                   login_date,
                                   login_type
                        from " . DB_PREFIX . "statistical_login_log where login_date between '$start_time' and '$end_time' ";
            $model   = D();
            $info    = $model->query($sql_str);

            $login_list = array();

            foreach ($info as $key => $value) {
                $login_list[$value['login_date']]['login_date'] = $value['login_date'];
                if ($value['login_type'] == '1') {

                    $login_list[$value['login_date']]['web'] = $value['login_count'];
                }
                if ($value['login_type'] == '2') {

                    $login_list[$value['login_date']]['wap'] = $value['login_count'];
                }
                if ($value['login_type'] == '3') {

                    $login_list[$value['login_date']]['Android'] = $value['login_count'];
                }
                if ($value['login_type'] == '4') {

                    $login_list[$value['login_date']]['ios'] = $value['login_count'];
                }
            }
            $interval = count($info) / 4;
            for ($i = 0; $i < $interval; $i++) {
                $temp_count = 0;
                $date_temp  = to_date(to_timespan($start_time, $format     = 'Y-m-d H:i:s') + ($i * 86400), "Y-m-d");
                foreach ($info as $key => $value) {
                    if ($value['login_date'] == $date_temp) {
                        $temp_count += $value['login_count'];
                    }
                }
                $login_list[$date_temp]['all'] = $temp_count;
            }
            $this->assign('login_list', $login_list);
            $this->assign('xAxis_pot', json_encode($date_all));
            $this->assign('data_array', json_encode($count_all));
            $this->assign('yAxis_title', json_encode("登录人数"));
            $this->assign('data_name', json_encode(["web", "wap", "android", "ios"]));
            $this->assign('unit', json_encode("人"));
            $this->assign('title_name', json_encode("登录人数统计表"));
        }

        if ($start_time == $end_time) {

            //将日期数据存为图表规定的数据格式
            $date = $start_time;

            //设置图表为饼图
            $type = "pie";

            if ($start_time != to_date(time(), $format = 'Y-m-d')) {
                //循环将数据保存为图表规定的数据格式
                $count_all = array();
                for ($i = 1; $i <= 4; $i++) {


                    $sql_str = "select
                    login_count
                            from " . DB_PREFIX . "statistical_login_log where login_type=$i and login_date='$start_time' ";

                    $model = D();
                    $info  = $model->query($sql_str);


                    array_push($count_all, (int) $info[0]['login_count']);
                }

                $sum = 0;
                echo "<pre>";
                foreach ($count_all as $value) {
                    $sum += $value;
                }
                if ($sum == 0) {
                    $type = 'error';
                    $this->assign('type', $type);
                } else {
                    $pie_data_array = array();

                    foreach ($count_all as $value) {
                        $temp     = $value / $sum * 100;
                        $fraction = round($temp, 2);
                        array_push($pie_data_array, $fraction);
                    }

                    $sql_str = "select
                    login_count,
                                       login_date,
                                       login_type
                            from " . DB_PREFIX . "statistical_login_log where login_date='$start_time' ";
                    $model   = D();
                    $info    = $model->query($sql_str);

                    $login_list = array();

                    foreach ($info as $key => $value) {
                        $login_list[$value['login_date']]['login_date'] = $value['login_date'];
                        if ($value['login_type'] == '1') {
                            $login_list[$value['login_date']]['web'] = $value['login_count'];
                        }
                        if ($value['login_type'] == '2') {
                            $login_list[$value['login_date']]['wap'] = $value['login_count'];
                        }
                        if ($value['login_type'] == '3') {
                            $login_list[$value['login_date']]['Android'] = $value['login_count'];
                        }
                        if ($value['login_type'] == '4') {
                            $login_list[$value['login_date']]['ios'] = $value['login_count'];
                        }
                    }
                    $temp_count = 0;
                    foreach ($info as $key => $value) {
                        $temp_count += $value['login_count'];
                    }
                    $login_list[$start_time]['all'] = $temp_count;

                    $this->assign('login_list', $login_list);

                    $this->assign('type', $type);
                    $this->assign('series_name', json_encode("登录比例"));
                    $this->assign('pie_data_array', json_encode($pie_data_array));
                    $this->assign('pie_data_name', json_encode(["web", "wap", "android", "ios"]));
                    $pie_title_name = $date . "登录人数统计图";
                    $this->assign('pie_title_name', json_encode($pie_title_name));
                }
            }

            if ($start_time == to_date(time(), $format = 'Y-m-d')) {
                //循环将数据保存为图表规定的数据格式
                $count_all = array();

                $login_list                            = array();
                $login_list[$start_time]['login_date'] = $start_time;
                $login_list[$start_time]['all']        = 0;
                for ($i = 1; $i <= 4; $i++) {

                    $start_stamp = to_timespan($start_time, $format      = 'Y-m-d');
                    $sql_str     = "select
                    terminal,
                    count(*) as login_count
                            from " . DB_PREFIX . "user_login_log where terminal=$i and login_time>$start_stamp ";

                    $model = D();
                    $info  = $model->query($sql_str);


                    $login_list[$start_time]['all'] += $info[0]['login_count'];
                    array_push($count_all, (int) $info[0]['login_count']);
                    foreach ($info as $key => $value) {

                        if ($i == 1) {
                            $login_list[$start_time]['web'] = $value['login_count'];
                        }
                        if ($i == 2) {
                            $login_list[$start_time]['wap'] = $value['login_count'];
                        }
                        if ($i == 3) {
                            $login_list[$start_time]['Android'] = $value['login_count'];
                        }
                        if ($i == 4) {
                            $login_list[$start_time]['ios'] = $value['login_count'];
                        }
                    }
                }

                $sum = 0;
                foreach ($count_all as $value) {
                    $sum += $value;
                }
                if ($sum == 0) {
                    $type = 'error';
                    $this->assign('type', $type);
                } else {
                    $pie_data_array = array();

                    foreach ($count_all as $value) {
                        $temp     = $value / $sum * 100;
                        $fraction = round($temp, 2);
                        array_push($pie_data_array, $fraction);
                    }
                    $this->assign('login_list', $login_list);
                    $this->assign('type', $type);
                    $this->assign('series_name', json_encode("登录比例"));
                    $this->assign('pie_data_array', json_encode($pie_data_array));
                    $this->assign('pie_data_name', json_encode(["web", "wap", "android", "ios"]));
                    $pie_title_name = $date . "登录人数统计图";
                    $this->assign('pie_title_name', json_encode($pie_title_name));
                }
            }
        }



        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);

        $this->display();
    }

    public function website_invest_oto()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $lists           = $this->get_invest_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $statistics_data = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($lists as $key => $val) {
                if (empty($val)) {
                    $statistics_data[$key]['invest_count'] = '0';
                } else {
                    $statistics_data[$key]['invest_count'] = $val[0]['money_count'];
                }
                $statistics_data[$key]['invest_date'] = $start_time_int;
                $statistics_data[$key]['invest_type'] = (string) $key;
            }
            $list = array();

            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    if (empty($val)) {
                        $list[$key] = 0;
                    } else {
                        $list[$key] = round($val[0]['money_count'] / ($lists[0][0]['money_count']) * 100, 2);
                    }
                    if ($key > 0 && $key <= 4) {
                        $client_side_list[] = $list[$key];
                    } else if ($key == 5 || $key == 6) {
                        $pie_data_array_oto[] = $list[$key];
                    }
                }
            }
            //将数据传递到模块中
            $data_name_raw = [ '线下', '线上'];
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            //$this->assign("list", $statistics_data);//表格里的数据
            $date_list[]   = $start_time_int;
        } else {
            $date_lists = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->field("invest_date")->group("invest_date")->findAll();
            //将数组转换成图形所需要的数据
            for ($i = 0; $i <= 6; $i++) {
                $lists[] = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int and invest_type=$i")->findAll();
            }
            $list = array();
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($key == 0 || $key == 5 || $key == 6) {
                            $list[$key][] = (float) $v['invest_count'];
                        }
                    }
                }
            }

            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['invest_date'];
                }
            }
            $data_name_raw   = ['全部', '线下', '线上'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $statistics_data = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->findAll();
        }
        $client_list = array();

        foreach ($statistics_data as $key => $val) {
            $client_list['gross'][$val['invest_type']]+=$val['invest_count'];
            $client_list[$val['invest_date']]['invest_date'] = $val['invest_date'];
            if ($val['invest_type'] == $statistics_data[$key]['invest_type']) {
                $client_list[$val['invest_date']][$val['invest_type']] = $val['invest_count'];
            }
        }
        $client_list['gross']['invest_date'] = "总计";
        krsort($client_list);
        $this->assign('client_list', $client_list); //表格中的数据
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));

        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);
        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode(array_values($list)));

        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));

        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));

        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));

        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($pie_data_array_oto));

        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    public function website_invest_total()
    {
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $lists           = $this->get_invest_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $statistics_data = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($lists as $key => $val) {
                if (empty($val)) {
                    $statistics_data[$key]['invest_count'] = '0';
                } else {
                    $statistics_data[$key]['invest_count'] = $val[0]['money_count'];
                }
                $statistics_data[$key]['invest_date'] = $start_time_int;
                $statistics_data[$key]['invest_type'] = (string) $key;
            }
            $list = array();

            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    if (empty($val)) {
                        $list[$key] = 0;
                    } else {
                        $list[$key] = round($val[0]['money_count'] / ($lists[0][0]['money_count']) * 100, 2);
                    }
                    if ($key > 0 && $key <= 4) {
                        $client_side_list[] = $list[$key];
                    } else if ($key == 5 || $key == 6) {
                        $pie_data_array_oto[] = $list[$key];
                    }
                }
            }
            //将数据传递到模块中
            $data_name_raw = [ 'web', 'wap', 'Android', 'ios'];
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            //$this->assign("list", $statistics_data);//表格里的数据
            $date_list[]   = $start_time_int;
        } else {
            $date_lists = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->field("invest_date")->group("invest_date")->findAll();
            //将数组转换成图形所需要的数据
            for ($i = 0; $i <= 4; $i++) {
                $lists[] = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int and invest_type=$i")->findAll();
            }

            $list = array();

            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    foreach ($val as $k => $v) {
                        $list[$key][] = (float) $v['invest_count'];
                    }
                }

            }

            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['invest_date'];
                }
            }

            $data_name_raw   = ['全部', 'web', 'wap', 'Android', 'ios'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $statistics_data = M("statistical_invest")->order("invest_date asc")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($statistics_data as $key => $val) {
            $client_list['gross'][$val['invest_type']]+=$val['invest_count'];
            $client_list[$val['invest_date']]['invest_date'] = $val['invest_date'];
            if ($val['invest_type'] == $statistics_data[$key]['invest_type']) {
                $client_list[$val['invest_date']][$val['invest_type']] = $val['invest_count'];
            }
        }

        $client_list['gross']['invest_date'] = "总计";
        krsort($client_list);
        $this->assign('client_list', $client_list); //表格中的数据
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));

        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);

        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode(array_values($list)));

        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));

        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));

        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));

        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100


        $this->assign('pie_data_array', json_encode($client_side_list));

        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    private function get_invest_arr($date = "")
    {
        if (!$date) {
            $date = to_date(time(), "Y-m-d");
        }
        $where = '';
        for ($i = 0; $i <= 6; $i++) {
            if ($i == 0) {
                $where = "where n.create_date='$date'";
            } elseif ($i == 5) {
                $where = "where u.admin_id<>0 and n.create_date='$date'";
            } elseif ($i == 6) {
                $where = "where u.admin_id=0 and n.create_date='$date'";
            } else {
                $where = "where n.order_source=$i and n.create_date='$date'";
            }
            $sql_str = "select
        n.create_date,
        sum(if(n.is_auto=0,n.money,0)) as money_count,
                n.order_source,
                admin_id
        from " . DB_PREFIX . "deal_load as n left join " . DB_PREFIX . "user as u on n.user_id=u.id $where";
            $sql_str .= " group by n.create_date ";
            $list[]  = D()->query($sql_str);
        }
        return $list;
    }

    public function export_csv_invest_total($page = 1)
    {
        //从url中获取开始时间，
        $start_time     = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time       = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $payment_list = $this->get_invest_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $lists        = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($payment_list as $key => $val) {
                if (empty($val)) {
                    $lists[$key]['invest_count'] = '0';
                } else {
                    $lists[$key]['invest_count'] = $val[0]['money_count'];
                }
                $lists[$key]['invest_date'] = $start_time_int;
                $lists[$key]['invest_type'] = (string) $key;
            }
        } else {
            $lists = M("statistical_invest")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($lists as $key => $val) {
            $client_list['gross'][$val['invest_type']]+=$val['invest_count'];
            $client_list[$val['invest_date']]['invest_date'] = $val['invest_date'];
            if ($val['invest_type'] == $lists[$key]['invest_type']) {
                $client_list[$val['invest_date']][$val['invest_type']] = $val['invest_count'];
            }
        }
        $client_list['gross']['invest_date'] = "总计";
        krsort($client_list);
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel                         = new PHPExcel();
        $num                                 = 1;
        foreach ($client_list as $key => $val) {
            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('B' . $num, "web(元)")
                        ->setCellValue('C' . $num, "wap(元)")
                        ->setCellValue('D' . $num, "Android(元)")
                        ->setCellValue('E' . $num, "ios(元)")
                        ->setCellValue('F' . $num, "合计(元)");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['invest_date'])
                    ->setCellValue('B' . $num, $val['1'])
                    ->setCellValue('C' . $num, $val['2'])
                    ->setCellValue('D' . $num, $val['3'])
                    ->setCellValue('E' . $num, $val['4'])
                    ->setCellValue('F' . $num, $val['0']);


            $num++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $start_time . '~' . $end_time . "的投标统计表";
        php_export_excel($objPHPExcel, $filename);
    }

    public function export_csv_invest_oto($page = 1)
    {
        //从url中获取开始时间，
        $start_time     = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time       = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
            $payment_list = $this->get_invest_arr($start_time);
            //将数组重整，此数据是放到数据中的
            $lists        = array();
            //将获取的数据数组转换成统计的中的类型，这样有利于数据的显示
            foreach ($payment_list as $key => $val) {
                if (empty($val)) {
                    $lists[$key]['invest_count'] = '0';
                } else {
                    $lists[$key]['invest_count'] = $val[0]['money_count'];
                }
                $lists[$key]['invest_date'] = $start_time_int;
                $lists[$key]['invest_type'] = (string) $key;
            }
        } else {
            $lists = M("statistical_invest")->where("invest_date>=$start_time_int and invest_date<=$end_time_int")->findAll();
        }
        $client_list = array();
        foreach ($lists as $key => $val) {
            $client_list['gross'][$val['invest_type']]+=$val['invest_count'];
            $client_list[$val['invest_date']]['invest_date'] = $val['invest_date'];
            if ($val['invest_type'] == $lists[$key]['invest_type']) {
                $client_list[$val['invest_date']][$val['invest_type']] = $val['invest_count'];
            }
        }
        $client_list['gross']['invest_date'] = "总计";
        krsort($client_list);
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel                         = new PHPExcel();
        $num                                 = 1;
        foreach ($client_list as $key => $val) {
            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '时间')
                        ->setCellValue('B' . $num, "线下(元)")
                        ->setCellValue('C' . $num, "线上(元)")
                        ->setCellValue('D' . $num, "合计(元)");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $val['invest_date'])
                    ->setCellValue('B' . $num, $val['5'])
                    ->setCellValue('C' . $num, $val['6'])
                    ->setCellValue('D' . $num, $val['0']);


            $num++;
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $start_time . '~' . $end_time . "的投标统计表";
        php_export_excel($objPHPExcel, $filename);
    }

    public function website_recharge_rank(){
        //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (30 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        $conditon = "";
        //时间限制
        $conditon .= " pn.pay_date >= '".$start_time_int ."'";
        $conditon .= " AND pn.pay_date <= '".$end_time_int ."'";
        $sql_count = "SELECT sum(pn.money) from ".DB_PREFIX."payment_notice pn WHERE ".$conditon ." and pn.is_paid = 1 GROUP BY pn.user_id";
        $count = $GLOBALS['db']->getAll($sql_count);
        $count = count($count);
        if (! empty ( $_REQUEST ['listRows'] )) {
                $listRows = $_REQUEST ['listRows'];
        } else {
                $listRows = '';
        }
        $p = new Page ( $count, $listRows );
        if($count > 0 ){
                $sql = "SELECT sum(pn.money) as total_money,u.* from ".DB_PREFIX."payment_notice pn LEFT JOIN ".DB_PREFIX."user u on pn.user_id = u.id WHERE ".$conditon ." and pn.is_paid = 1 GROUP BY pn.user_id ORDER BY total_money desc limit ".$p->firstRow . ',' . $p->listRows;
                $list = $GLOBALS['db']->getAll($sql);
                foreach($list as $key=>$val){
                    $list[$key]['key'] = $key + 1;
                }
                $this->assign("list",$list);
        }
        $page = $p->show();
        $this->assign ( "page", $page );
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);;
        $this->display();
    }

    public function website_extraction_rank(){
         //获取当前的时间
        $time           = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (30 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int   = str_replace("-", '', $end_time); //将结束时间转换成int类型
        $conditon = "";
        //时间限制
        $conditon .= " z.create_date >= '".$start_time_int ."'";
        $conditon .= " AND z.create_date <= '".$end_time_int ."'";
        $sql_count = "SELECT sum(z.money) FROM " . DB_PREFIX . "user_carry z WHERE ".$conditon ." AND z.status = 1 GROUP BY z.user_id";
        $count = $GLOBALS['db']->getAll($sql_count);
        $count = count($count);
        if (! empty ( $_REQUEST ['listRows'] )) {
                $listRows = $_REQUEST ['listRows'];
        } else {
                $listRows = '';
        }
        $p = new Page ( $count, $listRows );
        if($count > 0 ){
                $sql = "SELECT sum(z.money) as total_money,u.id,u.user_name from ".DB_PREFIX."user_carry z LEFT JOIN ".DB_PREFIX."user u on z.user_id = u.id WHERE ".$conditon ." AND z.status = 1 GROUP BY z.user_id ORDER BY total_money desc limit ".$p->firstRow . ',' . $p->listRows;
                $list = $GLOBALS['db']->getAll($sql);
                foreach($list as $key=>$val){
                    $list[$key]['key'] = $key + 1;
                }
                $this->assign("list",$list);
        }
        $page = $p->show();
        $this->assign ( "page", $page );
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);;
        $this->display();
    }

}

?>