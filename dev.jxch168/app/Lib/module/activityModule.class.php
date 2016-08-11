<?php

class activityModule extends SiteBaseModule {
  
    public function index() {
        if ($_REQUEST['id']) {
            if ($_REQUEST['hf'] == 1) {
                $GLOBALS['tmpl']->assign("hf", true);
            }
            $id = $_REQUEST['id'];
            $article = get_article($id);
            $GLOBALS['tmpl']->assign("page_title", $article['seo_title']);
            $GLOBALS['tmpl']->assign("page_keyword", $article['seo_keyword']);
            $GLOBALS['tmpl']->assign("page_description", $article['seo_description']);
            $GLOBALS['tmpl']->assign("article", $article);
//	    $GLOBALS['tmpl']->display("inc/activity/activity_index.html", $activity_index);
            $GLOBALS['tmpl']->display("page/activity.html");
        }
    }

    //获取获奖榜单的记录列表新的
    public function get_log_info() {
//        $maxLogId = $_REQUEST['maxLogId'];
        $log_list = MO("Lotterylog")->getLogList(3, 10, $maxLogId);
        $new_log_list = array();
        if ($log_list) {
            foreach ($log_list as $key => $val) {
                $new_log_list[$key]['mobile'] = substr_replace($val['mobile'], '*****', 3, 5);
                $new_log_list[$key]['prize_name'] = $val['prize_name'];
                $new_log_list[$key]['cteate_date'] = to_date($val['create_time'], 'm-d H:i');
            }
            $data['data'] = $new_log_list;
            $data['maxLogId'] = $log_list[0]['id'];
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        ajax_return($data);
    }

    //异步获取我的获奖记录
    public function get_user_info() {
        $obj = strim($_REQUEST['obj']);
        $mobile = strim($_REQUEST['mobile']);

        //输出投标列表
        $page = intval($_REQUEST['p']);
        if ($page == 0) {
            $page = 1;
        }
        $page_size = 5;
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        $result = MO("Lotterylog")->get_user_info($mobile, $limit);

        $rs_count = $result['count'];
        $page_all = ceil($rs_count / $page_size);
//        echo '<pre>';var_dump($result,$mobile);echo '</pre>';die;

        $GLOBALS['tmpl']->assign("load_user", $result['item']);
        $GLOBALS['tmpl']->assign("page_all", $page_all);
        $GLOBALS['tmpl']->assign("rs_count", $rs_count);
        $GLOBALS['tmpl']->assign("page", $page);
        $GLOBALS['tmpl']->assign("obj", $obj);
        $GLOBALS['tmpl']->assign("page_prev", $page - 1);
        $GLOBALS['tmpl']->assign("page_next", $page + 1);

        $html = $GLOBALS['tmpl']->fetch("inc/activity/get_user_info.html");

        $return['status'] = 1;
        $return['info'] = $html;
        ajax_return($return);
    }

    //插入抽奖次数
    function insert_user_lottery_number() {
        $mobile = $_REQUEST['mobile'];
        $lottery_number = MO("User")->insert_lottery_number($mobile, 1);
        ajax_return($lottery_number);
    }

    function get_mobile_info() {
        $mobile = $_REQUEST['mobile'];
        $user_mobile = $GLOBALS['db']->getOne("select mobile from " . DB_PREFIX . "user where mobile=$mobile");
        if ($user_mobile) {
            $lottery_number = $GLOBALS['db']->getOne("select lottery_number from " . DB_PREFIX . "user where mobile=$user_mobile");
        } else {
            $mobile = $GLOBALS['db']->getOne("select mobile from " . DB_PREFIX . "user_coupon_middle where mobile=$mobile");
            if (!$mobile) {
                $mobile = $GLOBALS['db']->getOne("select mobile from " . DB_PREFIX . "user_bonus_middel where mobile=$mobile");
            }
            if (!$mobile) {
                $lottery_number = 1;
            } else {
                $lottery_number = 0;
            }
        }
        ajax_return($lottery_number);
    }

    function user_login() {
        if ($_REQUEST['pwd'] && $_REQUEST['email']) {
            $user_info = user_check_web($_REQUEST['email'], $_REQUEST['pwd']);
            es_session::set('user_info', $user_info);
            es_cookie::set('user_name', $user_info['user_name']);
            if (!$user_info) {
                $root['code'] = 0;
                $root['errmsg'] = "账号或者密码错误！";
                ajax_return($root);
            } else {
                $root['code'] = 1;
                $root['errmsg'] = "登录成功！";
                $root['user_pwd_md5'] = md5($user_info['user_pwd'] . date("Y-m-d"));
                $root['user_name'] = $user_info['user_name'];
                ajax_return($root);
            }
        }
    }

    /**
     * 抽奖活动类
     */
    public function lottery() {
        $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
        $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : 4;
        $conf = require_once ROOT_PATH . 'data_conf/activityConf/' . $type . '_conf.php';
        $title = $conf['title'];
        if ($_REQUEST['email'] && $_REQUEST['pwd']) {
            $user_info = user_check_web($_REQUEST['email'], $_REQUEST['pwd']);
        } else {
            $user_info = es_session::get("user_info");
        }
        $user_id = $user_info['id']; //用户id
        $mobile = $user_info['mobile']; //手机号码
        $activity_info = MO("ActivityConf")->getInfoByType($type);
        if ($action) {
            if (!$activity_info) {
                ajax_return(array("code" => 0, "errmsg" => "不在活动期间内，请联系客服！"));
            } else if (!$user_info) {
                ajax_return(array("code" => -1, "errmsg" => "未登录"));
            }
        }
        //增加抽奖次数
        if ($action == 'add_lottery_number') {
            $mobile = (int) $_REQUEST['mobile'];
            $lottery_number_info = MO("User")->insert_lottery_number($mobile, 1);
            $root['code'] = $lottery_number_info['status'];
            $root['lottery_number'] = $lottery_number_info['lottery_number'];
            ajax_return($root); //执行抽奖动作
        } else if ($action == 'do_lottery') {
            if (MO("User")->get_lottery_number($mobile) <= 0) {
                ajax_return(array("code" => 0, "errmsg" => "您的抽奖次数不足！"));
            }
            $param = $conf['param'];
            $where = '';
            $limit = '';
            if ($param['repay_time']) {
                $where.=' and d.repay_time' . $param['repay_time'][0] . $param['repay_time'][1] . " ";
            }
            if ($param['create_time']) {
                $where.=' and d.create_time' . $param['create_time'][0] . $param['create_time'][1] . " ";
            }
            if ($param['agency_id']) {
                $where.=' and d.agency_id' . $param['agency_id'][0] . $param['agency_id'][1] . " ";
            }
            if ($param['where']) {
                $where.=$param['where'];
            }
            $sql_str = "SELECT dl.money,dl.id FROM " . DB_PREFIX . "deal_load as dl left join " . DB_PREFIX . "deal as d on(dl.deal_id=d.id) where dl.user_id=" . $user_info['id'] . " and dl.is_winning=0 " . $where;
            if ($param['compute_type'] == 'once') {
                $sql_str.=" order by money desc limit 1";
            }
            $deal_load = $GLOBALS['db']->getAll($sql_str);
            $money = 0;
            $myriad = 10000;
            $idStr = '';
            if ($deal_load) {
                $ids = array();
                foreach ($deal_load as $val) {
                    $money+=$val['money'];
                    $ids[] = $val['id'];
                }
                $idStr = implode(",", $ids);
            }
            if (!MO('DealLoad')->setWinnig($user_info['id'])) {
                ajax_return(array("code" => 0, "errmsg" => '操作失败！'));
            }

            rand(1, 2) % 2 == 1 ? krsort($conf['prize_conf']) : ksort($conf['prize_conf']);
            foreach ($conf['prize_conf'] as $parameter) {
                if ($money >= $parameter['start'] * $myriad && $money < $parameter['end'] * $myriad) {
                    $child_param = $parameter;
                    break;
                }
            }
            $num = $child_param['prize_id'];
            $prize_type = $child_param['prize_type'];
            $conf_id = $child_param['conf_id'];
            $prize_name = getPriceName($prize_type, $conf_id);

            if ($prize_type == 1 || $prize_type == 2) {
                $lottery_data['remark'] = $title;
                $obj_id = MO("Coupon")->confAdd($user_info, $lottery_data, $conf_id);
                if (!$obj_id) {
                    $root['code'] = 0;
                    $root['errmsg'] = "操作失败！";
                    ajax_return($root);
                }
            } else if ($prize_type == 4) {
                //添加站内信
//          $msg_data['title']="恭喜您中奖了";
                $msg_data['content'] = "您于" . date('Y-m-d H:i:s') . "参与" . $title . "抽奖，抽得奖品" . $prize_name . "。稍后会有客服联系您并确认发放活动奖励！";
                $msg_data['to_user_id'] = $user_info['id'];
                $msg_data['is_notice'] = 1;
                $msg_data['create_time'] = time();
                $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_box", $msg_data, "INSERT");
            }
            $lottery_log_data['lotter_id'] = $type;
            $lottery_log_data['mobile'] = $mobile;
            $lottery_log_data['prize_name'] = $prize_name;
            $lottery_log_data['prize_type'] = isset($prize_type) ? $prize_type : 4;
            $lottery_log_data['prize_desc'] = $title;
            $lottery_log_data['obj_id'] = isset($obj_id) ? $obj_id : 0;
            $lottery_log_data['prize_img'] = $conf_id;

            $lottery_log_data['use_deal_load_id'] = $idStr;
            $lottery_log_data['use_money'] = $money;
            $root['lottery_number'] = MO("User")->update_lottery_number($mobile); //将用户的抽奖次数减一
            $res = MO("Lotterylog")->add($lottery_log_data);
            if (!$res) {
                $root['code'] = 0;
                $root['errmsg'] = "操作失败！";
                ajax_return($root);
            }
            $root['code'] = 1;
            $root['num'] = $num;
            $root['name'] = $prize_name;
            ajax_return($root);
            //页面加载
        } else {
            if ($user_id > 0) {
                if ($_REQUEST['share']) {
                    $lottery_number = MO("User")->insert_lottery_number($mobile, 3)['lottery_number'];
                } else {
                    $lottery_number = MO("User")->get_lottery_number($mobile);
                }
            }
            $GLOBALS['tmpl']->assign("lottery_number", $lottery_number);
            $GLOBALS['tmpl']->assign("user_info", $user_info);
            $GLOBALS['tmpl']->assign("page_title", $title);
            if (isMobile()) {
                $GLOBALS['tmpl']->display("inc/activity/" . $type . "_wap.html");
                die;
            }
            $where = " and create_time>=" . $activity_info['start_time'] . ' ';
            $log_list = MO("Lotterylog")->getLogList($type, 10, 0, $where);
            if (count($log_list) < 10) {
                $log_list = MO("Lotterylog")->getLogList(0, 10, 0, $where);
            }
            if ($log_list) {
                foreach ($log_list as $key => $val) {
                    $log_list[$key]['create_date'] = to_date($val['create_time'], "m-d H:i");
                }
            }
            $show_share_code = share_code(6);
            $GLOBALS['tmpl']->assign("SITE_DOMAIN", SITE_DOMAIN);
            $GLOBALS['tmpl']->assign("log_list", $log_list);
            $GLOBALS['tmpl']->assign("maxLogId", $log_list[0]['id']);
            $GLOBALS['tmpl']->assign("show_share_code", $show_share_code);
            $GLOBALS['tmpl']->display("inc/activity/" . $type . ".html");
        }
    }

    /**
     * 至尊金享旅游回馈
     */
    function jxch_travel() {
        if ($_REQUEST['email'] && $_REQUEST['pwd']) {
            $user_info = user_check_web($_REQUEST['email'], $_REQUEST['pwd']);
        } else {
            $user_info = es_session::get("user_info");
        }
        $user_id = $user_info['id'];

        //判断用户是否登录,登录才计算
        if ($user_id && ($user_info['acct_type'] == null) && ($user_info['is_auto'] == 0) && ($user_info['is_effect'] == 1) && ($user_info['is_delete'] == 0)) {
            $activity_info = MO("ActivityConf")->getInfoByType("jxch_travel");
            $start_time_stamp = $activity_info['start_time'];
            $end_time_stamp = $activity_info['end_time'];

            //计算该用户在活动期间内的投资金额
            $invest_sql = "select sum(money) from " . DB_PREFIX . "deal_load where create_time >= '" . $start_time_stamp . "' and create_time <= '" . $end_time_stamp . "' and user_id =  '" . $user_id . "' and contract_no !=''";
            $invest_money = $GLOBALS['db']->getOne($invest_sql);

            //统计该用户在活动期内推荐的新客户数
            $invite_user_sql = "select count(id) from " . DB_PREFIX . "user where create_time >= '" . $start_time_stamp . "' and create_time <= '" . $end_time_stamp . "' and pid =  '" . $user_id . "' and acct_type is null AND is_auto = 0 and is_effect = 1 AND is_delete = 0";
            $invite_user = $GLOBALS['db']->getOne($invite_user_sql);

            //计算该用户在活动期内推荐新客户的投资记录
            $invite_money_sql = "select sum(dl.money) from " . DB_PREFIX . "deal_load as dl left join " . DB_PREFIX . "user as u on dl.user_id = u.id where u.pid = '" . $user_id . "' and u.create_time >= '" . $start_time_stamp . "' and dl.create_time >= '" . $start_time_stamp . "' and dl.create_time <= '" . $end_time_stamp . "' and u.acct_type is null AND u.is_auto = 0 and u.is_effect = 1 AND u.is_delete = 0 and dl.contract_no !='' ";
            $invite_money = $GLOBALS['db']->getOne($invite_money_sql);

            /*             * 判断用户是否已符合条件* */
            //查询用户在活动开始前是否有投资记录
            $deal_log_sql = "select id from " . DB_PREFIX . "deal_load where create_time <= '" . $start_time_stamp . "' and user_id = '" . $user_id . "'";
            $deal_log = $GLOBALS['db']->getOne($deal_log_sql);
            $res = 0;

            if ($deal_log) {
                # 老用户
                if ($invest_money >= 800000) {
                    $res = 1;
                }
                if ($invite_money >= 1000000) {
                    $res = 3;
                }
            } else {
                if ($invest_money >= 1000000) {
                    $res = 2;
                }
            }
        }
        $invest_money = $invest_money ? $invest_money : 0;
        $invite_user = $invite_user ? $invite_user : 0;
        $invite_money = $invite_money ? $invite_money : 0;

        $GLOBALS['tmpl']->assign("invest_money", $invest_money);
        $GLOBALS['tmpl']->assign("invite_user", $invite_user);
        $GLOBALS['tmpl']->assign("invite_money", $invite_money);
        $GLOBALS['tmpl']->assign("result", $res);
        $GLOBALS['tmpl']->assign("user_id", $user_id);
        if (isMobile()) {
            $GLOBALS['tmpl']->display("inc/activity/jxch_travel_wap.html");
            die;
        }
        $GLOBALS['tmpl']->display("inc/activity/jxch_travel.html");
    }

}

?>