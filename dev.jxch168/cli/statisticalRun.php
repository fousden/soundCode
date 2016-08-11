<?php

/*
 * 本类存放所有的统计内容
 * functioname($date)   $date 为指日期
 *
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'IosChannel.class.php';


class statisticalRun {

    /**
     * [iosChannel IOS渠道分析]
     * @param  [type] $data_star [开始时间]
     * @return [type]            [description]
     */
    function iosChannel($data_star) {
        $iosChannel = new IosChannel();
        $iosChannel->analysis($data_star);
    }

    //运营渠道统计需求的数据导入
    public function importing_date($date = "") {
        if (!$date) {
            $date = to_date(strtotime("-1 day"), "Y-m-d");
        }
        $GLOBALS['db']->query("delete from ".DB_PREFIX . "statistical_data where date='$date'");
        $sql_str = "SELECT
		count(distinct d.user_id) as deal_user_count,
		count(d.id) as deal_count,
		sum(d.money) as deal_month_sum,
		u.terminal as statistical_type,
		u.search_channel as channel_type
		FROM " . DB_PREFIX . "user AS u LEFT JOIN " . DB_PREFIX . "deal_load AS d ON u.id = d.user_id
		where d.is_auto=0 and u.acct_type is null and d.create_date='$date'
		GROUP BY u.search_channel,u.terminal";
        $list['deal'] = $GLOBALS['db']->getAll($sql_str);

        $sql_str = "SELECT
		count(distinct u.id) as user_reg_count,
		u.search_channel as channel_type,
		u.terminal as statistical_type
		FROM " . DB_PREFIX . "user AS u
		where u.is_auto=0 and u.acct_type is null and u.create_date='$date'
		GROUP BY u.search_channel,u.terminal ";
        $list['user'] = $GLOBALS['db']->getAll($sql_str);

        $sql_str = "SELECT
		sum(pn.money) as payment_sum,
		u.terminal as statistical_type,
		u.search_channel as channel_type
		FROM " . DB_PREFIX . "user AS u LEFT JOIN " . DB_PREFIX . "payment_notice AS pn ON u.id = pn.user_id
		where pn.is_paid=1 and u.acct_type is null and pn.create_date='$date'
		GROUP BY u.search_channel,u.terminal ";
        $list['payment'] = $GLOBALS['db']->getAll($sql_str);

        $time = strtotime($date . " 00:00:00");
        $timeb = strtotime($date . " +1 day");
        $sql_str = "select count(*) as activation_count,mobile as statistical_type,source as channel_type  from " . DB_PREFIX . "mobile_extension where state >= 1 and type = 0 and test_time >= " . $time . " AND test_time< " . $timeb . " group by `source`";
        $list['extension'] = $GLOBALS['db']->getAll($sql_str);

        $sql_str = "SELECT count(*) as pingan_count,terminal as statistical_type,search_channel as channel_type FROM `fanwe_user` where is_auto=0 and idno!='' and `status`=2 and  create_date='$date' GROUP BY search_channel,terminal";
        $list['pingan'] = $GLOBALS['db']->getAll($sql_str);

        $arr = array_merge($list['deal'], $list['user'], $list['payment'], $list['extension'], $list['pingan']);
        foreach ($arr as $key => $val) {
            $val['date'] = $date;
            $where = "statistical_type!={$val['statistical_type']} and channel_type!='{$val['channel_type']}' and date!='$date'";
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_data", $val, 'INSERT');
            if (!$res) {
                $where = "statistical_type={$val['statistical_type']} and channel_type='{$val['channel_type']}' and date='$date'";
                $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_data", $val, 'UPDATE', $where);
            }
        }
    }

    //投标统计
    function importing_invest($date = "") {
        if (!$date) {
            $date = to_date(strtotime("-1 day"), "Y-m-d");
        }
        //日期格式
        $date_arr = explode('-', $date);
        $date_fromat = $date_arr[0] . $date_arr[1] . $date_arr[2];
        //公共SQL
        $common_str = "select sum(d.money) as total_money from ".DB_PREFIX."deal_load d left join ".DB_PREFIX."user u on d.user_id = u.id where d.is_auto = 0 and d.contract_no !='' and d.create_date='".$date."' ";
        for($i=0;$i <=6; $i++){
            if(!$i){
                //总共的投资金额统计
                $total_money = $GLOBALS['db']->getOne($common_str);
            }else if($i == 5){
                //线下投资金额统计
                $common_str .=  " and u.admin_id <> 0 ";
                $total_money = $GLOBALS['db']->getOne($common_str);
            }else if($i == 6){
                //线上投资金额统计
                $common_str .= " u.admin_id = 0 ";
                $total_money = $GLOBALS['db']->getOne($common_str);
            }else{
                //查询四端投资统计
                $web_app_str = "select order_source,sum(money) as total_money from ".DB_PREFIX."deal_load where is_auto = 0 and contract_no !='' and order_source = ".$i." and create_date='".$date."' group by order_source";
                $web_app = $GLOBALS['db']->getRow($web_app_str);
                $total_money = $web_app["total_money"] ? $web_app["total_money"] : 0;
            }
            //准备插入数据
            $insert_data["invest_count"] = $total_money ? $total_money : 0;
            $insert_data["invest_date"] = $date_fromat;
            $insert_data["invest_year"] = $date_arr[0];
            $insert_data["invest_month"] = $date_arr[1];
            $insert_data["invest_type"] = $i; //0代表总计 1表示web 2表示wap 3表示android 4表示ios 5表示线下 6表示线上
            $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_invest", $insert_data, "INSERT");
        }
    }

    //充值统计
    function importing_source($date = "") {
        if (!$date) {
            $date = to_date(strtotime("-1 day"), "Y-m-d");
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
            $sql_str = "select
        n.pay_date,
        sum(if(n.is_paid=1,n.money,0)) as money_count,
                n.incharge_source,
                admin_id
        from " . DB_PREFIX . "payment_notice as n left join " . DB_PREFIX . "user as u on n.user_id=u.id $where";
            $sql_str .= " group by n.pay_date ";
            $list[] = $GLOBALS['db']->getAll($sql_str);
        }
        $date_arr = explode('-', $date);
        $date = $date_arr[0] . $date_arr[1] . $date_arr[2];
        foreach ($list as $key => $val) {
            if (empty($val)) {
                $payment_count = '0';
            } else {
                $payment_count = $val[0]['money_count'];
            }

            $res = $GLOBALS['db']->query("insert into " . DB_PREFIX . "statistical_payment_notice (payment_date,payment_count,payment_type,payment_year,payment_month) values ($date,$payment_count,$key,{$date_arr[0]},{$date_arr[1]})");
        }
    }

    // （脚本） 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function statistical_user_reg_jb($data_star) {



        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        //$i==1当天'线上'注册数
        for ($i = 1; $i < 4; $i++) {
            if ($i == 1) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id = 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 6";
                $data['user_reg_type'] = 6;
            }
            //$i==2当天'线下'注册数
            if ($i == 2) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id > 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 5";
                $data['user_reg_type'] = 5;
            }
            //$i==3当天注册总数
            if ($i == 3) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb}";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
            $arr_xs = $GLOBALS['db']->getAll("SELECT count(id) as count FROM " . DB_PREFIX . "user WHERE " . $sql_admin);

            $data['reg_year'] = mb_substr($day, 0, 4);
            $data['reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;

            //$res = M('statistical_user_reg')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_user_reg WHERE " . $sql_type);

            if ($res) {
                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_user_reg')->where($sql_type)->save($data);
                    $res = $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_user_reg SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_user_reg')->add($data);
                $res = $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_user_reg (user_reg_date,user_reg_type,user_reg_count,reg_year,reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['reg_year']},{$data['reg_month']})");
            }
        }
    }

    // （脚本） 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function get_list_pt_jb($data_star) {

        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        //$i==1当天'web'注册数
        for ($i = 1; $i < 6; $i++) {
            if ($i == 1) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 1";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 1";
                $data['user_reg_type'] = 1;
            }
            //$i==2当天'wap'注册数
            if ($i == 2) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 2";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 2";
                $data['user_reg_type'] = 2;
            }
            //$i==3当天'android'注册数
            if ($i == 3) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 3";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 3";
                $data['user_reg_type'] = 3;
            }
            //$i==4当天'IOS'注册数
            if ($i == 4) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 4";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 4";
                $data['user_reg_type'] = 4;
            }
            //$i==5当天注册总数
            if ($i == 5) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb}";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
            $arr_xs = $GLOBALS['db']->getAll("SELECT count(id) as count FROM " . DB_PREFIX . "user WHERE " . $sql_admin);
            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;
            //$res = M('statistical_reg_from')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_reg_from WHERE " . $sql_type);
            //echo "INSERT INTO ".DB_PREFIX."statistical_reg_from (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})";    die;
            if ($res) {
                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_reg_from')->where($sql_type)->save($data);
                    $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_reg_from SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_reg_from')->add($data);
                $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_reg_from (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");
            }
        }
    }

    // (脚本) 渠道统计脚本
    function get_reg_qd_jb($data_star) {

        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        $list = $this->get_reg_from_qd();

        foreach ($list as $key => $v) {

            $data['user_reg_date'] = $day;
            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_type'] = $key;

            //$res = M('user')->field('count(id) as count')->where("create_time >= {$time} create_time < {$timeb} AND search_channel = {$v}")->find();
            $sql_v = " create_time >= {$time} AND create_time < {$timeb} AND search_channel = '{$key}'";
            $res = $GLOBALS['db']->getRow("SELECT count(id) as count FROM " . DB_PREFIX . "user WHERE " . $sql_v);

            if ($res) {
                $data['user_reg_count'] = $res['count'];
            } else {
                $data['user_reg_count'] = 0;
            }

            //$rest = M('statistical_reg_qd')->where("user_reg_date = {$day} AND user_reg_type = {$v}")->find();
            $rest = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_reg_qd WHERE user_reg_date = {$day} AND user_reg_type = '{$key}'");
            if ($rest) {
                if ($rest['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_reg_qd')->save($data);
                    $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_reg_qd SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_reg_qd')->add($data);
                $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_reg_qd (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},'{$data['user_reg_type']}',{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");
            }
        }
    }

    //注册渠道来源数组
    function get_reg_from_qd() {
        include APP_ROOT_PATH . "data_conf/MarketingChannels.php";
    }

    //统计用户注册当天和非注册当天入金的总量和人数
    function get_into_gold($data_star) {
        // 查询当天注册当天入金总量
        $reg_time = strtotime($data_star); //  将传入日期变成unix时间戳
        $reg_day = to_date($reg_time, "Ymd");
        $year = mb_substr($reg_day, 0, 4);
        $month = mb_substr($reg_day, 4, 2);
        $stime = strtotime($reg_day . "00:00:00");
        $etime = strtotime($reg_day . "23:59:59");
        // 查询当天注册用户的ID
        $sql = "select id from " . DB_PREFIX . "user where is_auto=0 and acct_type is null and create_time >'{$stime}' AND create_time<='{$etime}'";
        $res = $GLOBALS['db']->getAll($sql);
        $sum = count($res);
        // 如果有用户则查出总数量
        $id_arr = array();
        foreach ($res as $val) {

            //foreach ($val as $v) {
            $id_arrTmp[] = $val['id'];
            //   }
        }
        $id_arr = implode(',', $id_arrTmp);
        // 查询当天注册用户是否入金
        $sql = "select sum(money) from " . DB_PREFIX . "deal_load where contract_no != '' and  is_auto = 0 and user_id in ({$id_arr}) and create_date = '{$data_star}'";
        $res = $GLOBALS['db']->getALL($sql);
        $sql1 = "select count(distinct(user_id)) from " . DB_PREFIX . "deal_load where contract_no != '' and  is_auto = 0 and user_id in ({$id_arr}) and create_date = '{$data_star}'";
        $res1 = $GLOBALS['db']->getALL($sql1);
        if (!empty($res)) {
            $money = $res['0']['sum(money)'];
        } else {
            $money = 0;
        }
        if (!empty($res1['0'])) {
            $psum = $res1['0']['count(distinct(user_id))'];
        } else {
            $psum = 0;
        }
        // 将当天注册当天入金的人数写入数据库
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 1;
        $data['count'] = $psum;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');
        // 将当天注册当天入金的金额写进数据库
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 3;
        $data['count'] = $money;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');

        //非当天注册所有入金的用户
        $getTodayDealUsersSql = "select user_id from " . DB_PREFIX . "deal_load where contract_no != '' and  is_auto = 0 and user_id not in({$id_arr}) and create_date = '{$data_star}' group by user_id";  // 查出当天入金的所有人
        $getTodayDealUsers = $GLOBALS['db']->getAll($getTodayDealUsersSql);
        foreach ($getTodayDealUsers as $val) {
            $getTodayDealUsersIds[] = $val['user_id'];
        }
        // 当天入金用户之前有投资的用户
        $sql2 = "select user_id from " . DB_PREFIX . "deal_load where contract_no != '' and  is_auto = 0 and user_id  in(" . implode(",", $getTodayDealUsersIds) . ") and create_date < '{$data_star}' group by user_id";  // 查出当天入金的所有人
        $userBeforeDealList = $GLOBALS['db']->getALL($sql2);
        foreach ($userBeforeDealList as $val) {
            $userBeforeDealUids[] = $val['user_id'];
        }
        // 当天入金非当天注册的用户
        $firstDealUserIds = array_diff($getTodayDealUsersIds, $userBeforeDealUids);
        //如果有数据
        if ($firstDealUserIds) {
            $psum1 = count($firstDealUserIds);
        } else {
            $psum1 = 0;
        }
        $sql  = "select sum(money) from " . DB_PREFIX . "deal_load where contract_no != '' and  is_auto = 0 and user_id in(" . implode(",", $firstDealUserIds) . ") and create_date='{$data_star}'";
        $userSumDeal = $GLOBALS['db']->getOne($sql);
        if ($userSumDeal) {
            $money1 = $userSumDeal;
        } else {
            $money1 = 0;
        }
        // 当天入金非当天注册的人数写进数据库
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 2;
        $data['count'] = $psum1;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');

        // 当天入金非当天注册的金额写进数据库
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 4;
        $data['count'] = $money1;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');
        // 查询当天入金的总人数
        $sql = "select count(*) from (select user_id from fanwe_deal_load where contract_no != '' and  is_auto = 0 and create_date = '{$data_star}' group by user_id) as temp";
        $sum_user = $GLOBALS['db']->getOne($sql);
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 5;
        $data['count'] = $sum_user;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');

        // 查询当天入金的总金额
        $sql = "select sum(money) from fanwe_deal_load where contract_no != '' and  is_auto = 0 and create_date = '{$data_star}'";
        $sum_gold = $GLOBALS['db']->getOne($sql);
        $data['user_reg_date'] = $reg_day;
        $data['type'] = 6;
        $data['count'] = $sum_gold;
        $data['gold_year'] = $year;
        $data['gold_month'] = $month;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_gold", $data, 'INSERT', '', 'SILENT');
    }

// （脚本） 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function statistical_reg_yqly_jb($data_star) {

        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        //$i==1当天'线上'注册数
        for ($i = 1; $i < 4; $i++) {

            if ($i == 3) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id = 0 AND pid > 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 5";
                $data['user_reg_type'] = 5;
            }
            //$i==2当天'线下'注册数
            if ($i == 2) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id > 0 AND pid = 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 6";
                $data['user_reg_type'] = 6;
            }
            //$i==3当天注册总数
            if ($i == 1) {
                $sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND (admin_id <> 0 OR pid <> 0)";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
            $arr_xs = $GLOBALS['db']->getAll("SELECT count(id) as count FROM " . DB_PREFIX . "user WHERE " . $sql_admin);

            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;


            //$res = M('statistical_user_reg')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_reg_yqly WHERE " . $sql_type);
//var_dump("SELECT count(id) as count FROM ".DB_PREFIX."user WHERE ".$sql_admin);
            if ($res) {

                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_user_reg')->where($sql_type)->save($data);
                    $res = $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_reg_yqly SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_user_reg')->add($data);
                $res = $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_reg_yqly (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");

//echo "INSERT INTO ".DB_PREFIX."statistical_reg_yqly (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})";
//exit;
            }
        }
    }

    //（脚本） 邀请注册 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function get_list_pt_yqqd_jb($data_star) {

        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        //$i==1当天'web'注册数
        for ($i = 1; $i < 6; $i++) {
            if ($i == 1) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND (admin_id <>0 OR pid <> 0) AND terminal = 1";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 1";
                $data['user_reg_type'] = 1;
            }
            //$i==2当天'wap'注册数
            if ($i == 2) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND (admin_id <>0 OR pid <> 0)  AND terminal = 2";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 2";
                $data['user_reg_type'] = 2;
            }
            //$i==3当天'android'注册数
            if ($i == 3) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND (admin_id <>0 OR pid <> 0)  AND terminal = 3";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 3";
                $data['user_reg_type'] = 3;
            }
            //$i==4当天'IOS'注册数
            if ($i == 4) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND (admin_id <>0 OR pid <> 0)  AND terminal = 4";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 4";
                $data['user_reg_type'] = 4;
            }
            //$i==5当天注册总数
            if ($i == 5) {
                $sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND (admin_id <>0 OR pid <> 0) ";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
            $arr_xs = $GLOBALS['db']->getAll("SELECT count(id) as count FROM " . DB_PREFIX . "user WHERE " . $sql_admin);
            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;
            //$res = M('statistical_reg_from')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_reg_yqqd WHERE " . $sql_type);
            //echo "INSERT INTO ".DB_PREFIX."statistical_reg_from (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})";    die;
            if ($res) {
                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_reg_from')->where($sql_type)->save($data);
                    $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_reg_yqqd SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_reg_from')->add($data);
                $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_reg_yqqd (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");
            }
        }
    }

    // （脚本） 绑卡 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function statistical_bk_reg_jb($data_star) {



        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");






        //$i==1当天'线上'绑卡数
        for ($i = 1; $i < 4; $i++) {
            if ($i == 1) {
                $sql_admin = " AND b.admin_id = 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 6";
                $data['user_reg_type'] = 6;
            }
            //$i==2当天'线下'注册数
            if ($i == 2) {
                $sql_admin = " AND b.admin_id > 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 5";
                $data['user_reg_type'] = 5;
            }
            //$i==3当天注册总数
            if ($i == 3) {
                $sql_admin = "AND b.admin_id >= 0 ";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = $GLOBALS['db']->getAll("SELECT count(a.id) as count FROM ".DB_PREFIX."user a LEFT JOIN ".DB_PREFIX."user_bank b ON a.id = b.user_id WHERE ".$sql_admin);
            $arr_xs = $GLOBALS['db']->getAll("SELECT a.*,b.*,count(a.id) as count FROM " . DB_PREFIX . "user_bank a LEFT JOIN " . DB_PREFIX . "user b ON a.user_id = b.id WHERE a.binding_time >= {$time} AND a.binding_time< {$timeb} " . $sql_admin);
            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;

            //$res = M('statistical_user_reg')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_bk_bkly WHERE " . $sql_type);

            if ($res) {
                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_user_reg')->where($sql_type)->save($data);
                    $res = $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_bk_bkly SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_user_reg')->add($data);
                $res = $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_bk_bkly (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");
            }
        }
    }

    //（脚本） 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    function get_bk_pt_jb($data_star) {

        $date_star = to_date(strtotime($data_star), "Ymd");
        $day = $date_star;

        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime("$data_star 00:00:00");
        $timeb = strtotime("$data_star +1 day");
        //$i==1当天'web'注册数
        for ($i = 1; $i < 6; $i++) {
            if ($i == 1) {
                $sql_admin = "binding_time >= {$time} AND binding_time < {$timeb} AND binding_source = 1";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 1";
                $data['user_reg_type'] = 1;
            }
            //$i==2当天'wap'注册数
            if ($i == 2) {
                $sql_admin = "binding_time >= {$time} AND binding_time < {$timeb} AND binding_source = 2";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 2";
                $data['user_reg_type'] = 2;
            }
            //$i==3当天'android'注册数
            if ($i == 3) {
                $sql_admin = "binding_time >= {$time} AND binding_time < {$timeb} AND binding_source = 3";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 3";
                $data['user_reg_type'] = 3;
            }
            //$i==4当天'IOS'注册数
            if ($i == 4) {
                $sql_admin = "binding_time >= {$time} AND binding_time < {$timeb} AND binding_source = 4";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 4";
                $data['user_reg_type'] = 4;
            }
            //$i==5当天注册总数
            if ($i == 5) {
                $sql_admin = "binding_time >= {$time} AND binding_time < {$timeb} AND binding_source > 0";
                $sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
                $data['user_reg_type'] = 0;
            }
            //$arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
            $arr_xs = $GLOBALS['db']->getAll("SELECT count(id) as count FROM " . DB_PREFIX . "user_bank WHERE " . $sql_admin);
            $data['user_reg_year'] = mb_substr($day, 0, 4);
            $data['user_reg_month'] = mb_substr($day, 4, 2);
            $data['user_reg_count'] = (int) $arr_xs[0]['count'];
            $data['user_reg_date'] = $day;
            //var_dump("SELECT count(id) as count FROM ".DB_PREFIX."user_bank WHERE ".$sql_admin);exit;
            //$res = M('statistical_reg_from')->where($sql_type)->find();
            $res = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "statistical_bk_bkpt WHERE " . $sql_type);
            //echo "INSERT INTO ".DB_PREFIX."statistical_reg_from (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})";    die;
            if ($res) {
                if ($res['user_reg_count'] != $data['user_reg_count']) {
                    //M('statistical_reg_from')->where($sql_type)->save($data);
                    $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "statistical_bk_bkpt SET user_reg_count = {$data['user_reg_count']} WHERE " . $sql_type);
                }
            } else {
                //M('statistical_reg_from')->add($data);
                $GLOBALS['db']->query("INSERT INTO " . DB_PREFIX . "statistical_bk_bkpt (user_reg_date,user_reg_type,user_reg_count,user_reg_year,user_reg_month) VALUE ({$data['user_reg_date']},{$data['user_reg_type']},{$data['user_reg_count']},{$data['user_reg_year']},{$data['user_reg_month']})");
            }
        }
    }

    function importing_login_source($date = "") {
        ;


//        if (!$date) {
//            $date = to_date(strtotime("-1 day"), "Y-m-d");
//        }
        $day = $date;
        $start_time = (int) strtotime($date);

        $end_time = (int) strtotime($date) + 86400;


        $where = '';

        for ($i = 1; $i <= 4; $i++) {
            if ($i == 1) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 1";
            } elseif ($i == 2) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 2";
            } elseif ($i == 3) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 3";
            } elseif ($i == 4) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 4";
            }


            $sql_str = "select
		login_time,
                                   user_name,
                                   terminal,
                                   login_ip
            from " . DB_PREFIX . "user where " . $where;

            $list[] = $GLOBALS['db']->getAll($sql_str);
        }

        for ($i = 0; $i <= 4; $i++) {
            foreach ($list[$i] as $key => $val) {

                $temp_time = (int) $val['login_time'];
                $temp_terminal = (int) $val['terminal'];
                $temp_name = $val['user_name'];
                $temp_ip = $val['login_ip'];

                $res = $GLOBALS['db']->query("insert into " . DB_PREFIX . "user_login_log (login_time,user_name,terminal,ip_address) values ($temp_time,'$temp_name',$temp_terminal,'$temp_ip' )");
            }
        }
    }

    function login_statistical($date = "") {
//        if (!$date) {
//            $date = to_date(strtotime("-1 day"), "Y-m-d");
//        }
        $day = $date;
        $start_time = (int) strtotime($date);

        $end_time = (int) strtotime($date) + 86400;


        $where = '';

        for ($i = 1; $i <= 4; $i++) {
            if ($i == 1) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 1";
                $where_ver = "login_date = '$day' AND login_type = 1";
            } elseif ($i == 2) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 2";
                $where_ver = "login_date = '$day' AND login_type = 2";
            } elseif ($i == 3) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 3";
                $where_ver = "login_date = '$day' AND login_type = 3";
            } elseif ($i == 4) {
                $where = "login_time >= $start_time AND login_time < $end_time AND terminal = 4";
                $where_ver = "login_date = '$day' AND login_type = 4";
            }


            $sql_str = "select
                                   count(*) as count

            from " . DB_PREFIX . "user_login_log where " . $where;
            $res = $GLOBALS['db']->getAll($sql_str);


            if ($res[0]['count'] == 0) {
                $insert['login_time'] = $day;
                $insert['count'] = 0;
                $insert['terminal'] = $i;
                $insert['year'] = mb_substr($day, 0, 4);
                $insert['month'] = mb_substr($day, 5, 2);
                $insert['day'] = mb_substr($day, 8, 2);
            } else {
                $insert['login_time'] = $day;
                $insert['count'] = $res[0]['count'];
                $insert['terminal'] = $i;
                $insert['year'] = mb_substr($day, 0, 4);
                $insert['month'] = mb_substr($day, 5, 2);
                $insert['day'] = mb_substr($day, 8, 2);
            }

            $sql_ver = "SELECT count(*) as count FROM " . DB_PREFIX . "statistical_login_log WHERE " . $where_ver;
            $ver = $GLOBALS['db']->getAll($sql_ver);



            $temp_time = $insert['login_time'];
            $temp_year = $insert['year'];
            $temp_month = $insert['month'];
            $temp_day = $insert['day'];
            $temp_count = (int) $insert['count'];
            $temp_terminal = (int) $insert['terminal'];
            $ins = $GLOBALS['db']->query("insert into " . DB_PREFIX . "statistical_login_log (login_date,login_year,login_month,login_day,login_type,login_count) values ('$temp_time','$temp_year','$temp_month','$temp_day',$temp_terminal,$temp_count)");
        }
    }

    function carry($date = '') {
        if (!$date) {
            $date = to_date(strtotime("-1 day"), "Y-m-d");
        }

        $sql_money = "SELECT sum(z.money) FROM " . DB_PREFIX . "user_carry z where z.create_date='" . $date ."' AND z.status = 1";
        $total_money = $GLOBALS['db']->getOne($sql_money);

        $sql_person = "SELECT count(distinct z.user_id) FROM " . DB_PREFIX . "user_carry z where z.create_date='" . $date ."' AND z.status = 1";
        $total_person = $GLOBALS['db']->getOne($sql_person);

        $sql_times = "SELECT count(z.id) FROM " . DB_PREFIX . "user_carry z where z.create_date='" . $date ."' AND z.status = 1";
        $total_times = $GLOBALS['db']->getOne($sql_times);

        $date_arr = explode('-', $date);
        $date = $date_arr[0] . $date_arr[1] . $date_arr[2];

        $insertdata['carry_date'] = $date;
        $insertdata['carry_year'] = $date_arr[0];
        $insertdata['carry_month'] = $date_arr[1];
        for ($i = 1; $i <= 3; $i++) {
            if ($i == 1) {
                $insertdata['carry_count'] = $total_money;
                $statistical_carry = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_carry where carry_type = 1 and carry_date = '".$date."'");
                if(!$statistical_carry){
                    $insertdata['carry_type'] = 1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "INSERT");
                }else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "UPDATE", ' `carry_type` ="' . $statistical_carry['carry_type'] . '"  and carry_date="'.$date.'"');
                }

            } elseif ($i == 2) {
                $insertdata['carry_count'] = $total_person;
                $statistical_carry = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_carry where carry_type = 2 and carry_date = '".$date."'");
                if(!$statistical_carry){
                    $insertdata['carry_type'] = 2;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "INSERT");
                }else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "UPDATE", ' `carry_type` ="' . $statistical_carry['carry_type'] . '"  and carry_date="'.$date.'"');
                }
            } else {
                $insertdata['carry_count'] = $total_times;
                $statistical_carry = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_carry where carry_type = 3 and carry_date = '".$date."'");
                if(!$statistical_carry){
                    $insertdata['carry_type'] = 3;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "INSERT");
                }else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_carry", $insertdata, "UPDATE", ' `carry_type` ="' . $statistical_carry['carry_type'] . '"  and carry_date="'.$date.'"');
                }
            }
        }
    }

    function repay($date = '') {
        if (!$date) {
            $date = to_date(strtotime("-1 day"), "Y-m-d");
        }

        $sql_total = "SELECT sum(dlr.repay_money) FROM " . DB_PREFIX . "deal_load_repay dlr LEFT JOIN " . DB_PREFIX . "deal d ON dlr.deal_id = d.id where d.jiexi_time = '" . $date ."'";
        $total_repay =$GLOBALS['db']->getOne($sql_total);
        $sql_true = "SELECT sum(dr.repay_money) FROM " . DB_PREFIX . "deal_repay dr WHERE dr.true_repay_date = '" . $date ."'";
        $true_repay =$GLOBALS['db']->getOne($sql_true);

        $date_arr = explode('-', $date);
        $date = $date_arr[0] . $date_arr[1] . $date_arr[2];

        $insertdata['repay_date'] = $date;
        $insertdata['repay_year'] = $date_arr[0];
        $insertdata['repay_month'] = $date_arr[1];
        for ($i = 1; $i <= 2; $i++) {
            if ($i == 1) {
                $insertdata['repay_count'] = $total_repay;
                $statistical_repay = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_repay where repay_type = 1 and repay_date = '".$date."'");
                if(!$statistical_repay){
                    $insertdata['repay_type'] = 1;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_repay", $insertdata, "INSERT");
                }else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_repay", $insertdata, "UPDATE", ' `repay_type` ="' . $statistical_repay['repay_type'] . '"  and repay_date="'.$date.'"');
                }

            } else{
                $insertdata['repay_count'] = $true_repay;
                $statistical_repay = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_repay where repay_type = 2 and repay_date = '".$date."'");
                if(!$statistical_repay){
                    $insertdata['repay_type'] = 2;
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_repay", $insertdata, "INSERT");
                }else{
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_repay", $insertdata, "UPDATE", ' `repay_type` ="' . $statistical_repay['repay_type'] . '"  and repay_date="'.$date.'"');
                }
            }
        }
    }
}
