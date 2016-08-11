<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class IndexAction extends AuthAction
{

    //首页
    public function index()
    {
        $this->display();
    }

    //框架头
    public function top()
    {
        $navs        = require_once APP_ROOT_PATH . "system/admnav_cfg.php";
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_id      = intval($adm_session['adm_id']);
        $adm_name    = strim($adm_session['adm_name']);
        //根据用户的id来获取该用户属于哪个分组
        $sql         = "select role.id from " . DB_PREFIX . "role as role left join " .
                DB_PREFIX . "admin as admin on admin.role_id = role.id where admin.id=$adm_id";
        $rid         = $GLOBALS['db']->getOne($sql);
        //判断用户是否是admin管理员，如果是则不进行判断，admin是拥有所有的权限
        if ($adm_name != 'admin') {
            // 用户不是ADMIN则显示其角色
            $sql        = "select name from " . DB_PREFIX . "role where id={$rid}";
            $role_name  = $GLOBALS['db']->getOne($sql);
            //根据分组id来获取所有的module以及node
            $node       = $GLOBALS['db']->getAll("select module,node from " . DB_PREFIX . "role_access where role_id=$rid and node !=''");
            $nodes_list = array();
            foreach ($node as $ke => $vl) {
                $unforbid_model_action_key[] = trim($vl['module']) . '#' . trim($vl['node']);
            }
            foreach ($navs as $key => $val) {
                foreach ($val['groups'] as $ke => $va) {
                    foreach ($va['nodes'] as $k => $v) {
                        $tmpModelActionKey = trim($v['module']) . '#' . trim($v['action']);
                        //判断是否有权限，如果没有权限就删除对应的值
                        if (!in_array($tmpModelActionKey, $unforbid_model_action_key)) {
                            unset($navs[$key]['groups'][$ke]['nodes'][$k]);
                        }
                    }
                    if (!$navs[$key]['groups'][$ke]['nodes']) {
                        unset($navs[$key]['groups'][$ke]);
                    }
                    if (!$navs[$key]['groups']) {
                        unset($navs[$key]);
                    }
                }
            }
        }
        $this->assign("role_name", $role_name);
        $this->assign("navs", $navs);
        $this->assign("adm_session", $adm_session);
        $this->display();
    }

    //框架左侧
    public function left()
    {
        $navs        = require_once APP_ROOT_PATH . "system/admnav_cfg.php";
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_id      = intval($adm_session['adm_id']);
        $adm_name    = strim($adm_session['adm_name']);
        $nav_key     = strim($_REQUEST['key']);
        $nav_group   = $navs[$nav_key]['groups'];
        //根据用户的id来获取该用户属于哪个分组
        $sql         = "select role.id from " . DB_PREFIX . "role as role left join " .
                DB_PREFIX . "admin as admin on admin.role_id = role.id where admin.id=$adm_id";
        $rid         = $GLOBALS['db']->getOne($sql);
        //判断用户是否是admin管理员，如果是则不进行判断，admin是拥有所有的权限
        if ($adm_name != 'admin') {
            //根据分组id来获取所有的module以及node
            $node       = $GLOBALS['db']->getAll("select module,node from " . DB_PREFIX . "role_access where role_id=$rid and node !=''");
            $nodes_list = array();
            foreach ($node as $ke => $vl) {
                $unforbid_model_action_key[] = trim($vl['module']) . '#' . trim($vl['node']);
            }
            foreach ($nav_group as $key => $val) {
                $nodes_list = $val['nodes'];
                foreach ($nodes_list as $k => $v) {
                    $tmpModelActionKey = trim($v['module']) . '#' . trim($v['action']);
                    //判断是否有权限，如果没有权限就删除对应的值
                    if (!in_array($tmpModelActionKey, $unforbid_model_action_key)) {
                        unset($nav_group[$key]['nodes'][$k]);
                    }
                }
                if (!$nav_group[$key]['nodes']) {
                    unset($nav_group[$key]);
                }
            }
        }

        $this->assign("menus", $nav_group);
        $this->display();
    }

    //默认框架主区域
    public function main()
    {
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_id      = intval($adm_session['adm_id']);
        $adm_name    = strim($adm_session['adm_name']);
        //根据用户的id来获取该用户属于哪个分组
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $sql         = "select role.id from " . DB_PREFIX . "role as role left join " .
                DB_PREFIX . "admin as admin on admin.role_id = role.id where admin.id=$adm_id";
        $rid         = $GLOBALS['db']->getOne($sql);
        //判断用户是否是admin管理员，如果是则不进行判断，admin是拥有所有的权限
        if ($adm_name != 'admin') {
            //根据分组id来获取所有的module以及node
            $node       = $GLOBALS['db']->getAll("select module,node from " . DB_PREFIX . "role_access where role_id=$rid and node !=''");
            $nodes_list = array();
            foreach ($node as $ke => $vl) {
                $unforbid_model_action_key[] = trim($vl['module']) . '#' . trim($vl['node']);
            }
            $tmpModelActionKey = 'Index#main';
            //判断是否有代办事务的权限，如果没有权限就停止执行下面的方法
            if (!in_array($tmpModelActionKey, $unforbid_model_action_key)) {
                die;
            }
        }
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_id      = intval($adm_session['adm_id']);
        $adm_name    = intval($adm_session['adm_name']);
        $sql         = "select role.id from " . DB_PREFIX . "role as role left join " .
                DB_PREFIX . "admin as admin on admin.role_id = role.id where admin.id=$adm_id";
        $navs        = require_once APP_ROOT_PATH . "system/admnav_cfg.php";
        $this->assign("navs", $navs);


        //会员数
        $total_user        = M("User")->count();
        $total_verify_user = M("User")->where("is_effect=1")->count();
        $this->assign("total_user", $total_user);
        $this->assign("total_verify_user", $total_verify_user);

        //满标的借款
        $suc_deal_count  = M("Deal")->where("is_effect=1 AND publish_wait = 0 AND is_delete = 0 AND deal_status = 2")->count();
        //待审核的借款
        $wait_deal_count = M("Deal")->where("publish_wait = 1 AND is_delete = 0 ")->count();
        //等待材料的借款
        $info_deal_count = M("Deal")->where("is_effect=1 AND publish_wait = 0 AND is_delete = 0 AND deal_status=0")->count();
        //等待审核的申请额度
        $quota_count     = M("QuotaSubmit")->where("status=0")->count();

        //等待审核的授信额度
        $deal_quota_count = M("DealQuotaSubmit")->where("status=0")->count();

        //提现申请
        $carry_count = D("UserCarry")->where("status = 0")->count();

        //三日要还款的借款
        $threeday_repay_count = M("DealRepay")->where("((repay_time - " . TIME_UTC . " +  24*3600 -1)/24/3600 between 0 AND 3) and has_repay=0 ")->count();

        //逾期未还款的
        $yq_repay_count = M("DealRepay")->where(" (" . TIME_UTC . " - repay_time  -  24*3600 +1 )/24/3600 > 0 and has_repay=0 ")->count();

        //未处理举报
        $reportguy_count = M("Reportguy")->where("status = 0")->count();

        //注册待审核
        $register_count         = M("User")->where("login_time = 0 and login_ip='' and is_effect=0 and is_delete=0 and user_type=0 and is_black=0")->count();
        $company_register_count = M("User")->where("login_time = 0 and login_ip='' and is_effect=0 and is_delete=0 and user_type=1 and is_black=0")->count();

        //未处理续约申请
        $generation_repay_submit = M("GenerationRepaySubmit")->where("status = 0")->count();

        $this->assign("register_count", $register_count);
        $this->assign("company_register_count", $company_register_count);
        $this->assign("suc_deal_count", $suc_deal_count);
        $this->assign("wait_deal_count", $wait_deal_count);
        $this->assign("info_deal_count", $info_deal_count);
        $this->assign("deal_quota_count", $deal_quota_count);
        $this->assign("quota_count", $quota_count);
        $this->assign("carry_count", $carry_count);
        $this->assign("threeday_repay_count", $threeday_repay_count);
        $this->assign("yq_repay_count", $yq_repay_count);
        $this->assign("reportguy_count", $reportguy_count);
        $this->assign("generation_repay_submit", $generation_repay_submit);

        $topic_count   = M("Topic")->where("is_effect = 1 and fav_id = 0")->count();
        $msg_count     = M("Message")->where("is_buy = 0")->count();
        $buy_msg_count = M("Message")->count();



        $this->assign("topic_count", $topic_count);
        $this->assign("msg_count", $msg_count);
        $this->assign("buy_msg_count", $buy_msg_count);


        //充值单数
        $incharge_order_buy_count = M("PaymentNotice")->where("is_paid=1")->count();
        $this->assign("incharge_order_buy_count", $incharge_order_buy_count);


        $reminder                   = M("RemindCount")->find();
        $reminder['topic_count']    = intval(M("Topic")->where("is_effect = 1 and relay_id = 0 and fav_id = 0 and create_time >" . $reminder['topic_count_time'])->count());
        $reminder['msg_count']      = intval(M("Message")->where("create_time >" . $reminder['msg_count_time'])->count());
        /* $reminder['buy_msg_count'] = intval(M("Message")->where("create_time >".$reminder['buy_msg_count_time'])->count());
          $reminder['order_count'] = intval(M("DealOrder")->where("is_delete = 0 and type = 0 and pay_status = 2 and create_time >".$reminder['order_count_time'])->count());
          $reminder['refund_count'] = intval(M("DealOrder")->where("is_delete = 0 and refund_status = 1 and create_time >".$reminder['refund_count_time'])->count());
          $reminder['retake_count'] = intval(M("DealOrder")->where("is_delete = 0 and retake_status = 1 and create_time >".$reminder['retake_count_time'])->count()); */
        $reminder['incharge_count'] = intval(M("PaymentNotice")->where("is_paid = 1 and pay_date=" . to_date(TIME_UTC, "Y-m-d") . " ")->count());

        M("RemindCount")->save($reminder);
        $this->assign("reminder", $reminder);

        //所有待审核认证资料 包括实名认证的材料
        $auth_count = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "user_credit_file where passed = 0 ");
        $this->assign("auth_count", $auth_count);

        //待补还项目
        $after_repay_count = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal as d where publish_wait=0 and is_delete =0 AND deal_status in(4,5) AND (repay_money > round((SELECT sum(repay_money) FROM " . DB_PREFIX . "deal_load_repay WHERE has_repay=1 and deal_id = d.id),2) + 1 or (repay_money >0  and (SELECT count(*) FROM " . DB_PREFIX . "deal_load_repay WHERE has_repay =1 and deal_id = d.id) = 0))");
        $this->assign("after_repay_count", $after_repay_count);

        $this->display();
    }

    public function map()
    {
        $navs = require_once APP_ROOT_PATH . "system/admnav_cfg.php";
        $this->assign("navs", $navs);
        $this->display();
    }

    //底部
    public function footer()
    {
        $this->display();
    }

    //修改管理员密码
    public function change_password()
    {
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $this->assign("adm_data", $adm_session);
        $this->display();
    }

    public function do_change_password()
    {
        $adm_id = intval($_REQUEST['adm_id']);

        if (!check_empty($_REQUEST['adm_password'])) {
            $this->error(L("ADM_PASSWORD_EMPTY_TIP"));
        }
        if (!check_empty($_REQUEST['adm_new_password'])) {
            $this->error(L("ADM_NEW_PASSWORD_EMPTY_TIP"));
        }
        if ($_REQUEST['adm_confirm_password'] != $_REQUEST['adm_new_password']) {
            $this->error(L("ADM_NEW_PASSWORD_NOT_MATCH_TIP"));
        }

        if (M("Admin")->where("id=" . $adm_id)->getField("adm_password") != md5($_REQUEST['adm_password'])) {
            $this->error(L("ADM_PASSWORD_ERROR"));
        }
        $data['adm_password'] = md5($_REQUEST['adm_new_password']);
        M("Admin")->where("id=" . $adm_id)->save($data);
        save_log(M("Admin")->where("id=" . $adm_id)->getField("adm_name") . L("CHANGE_SUCCESS"), 1);
        $this->success(L("CHANGE_SUCCESS"));
    }

    public function reset_sending()
    {
        $field = trim($_REQUEST['field']);
        if ($field == 'DEAL_MSG_LOCK' || $field == 'PROMOTE_MSG_LOCK' || $field == 'APNS_MSG_LOCK') {
            M("Conf")->where("name='" . $field . "'")->setField("value", '0');
            $this->success(L("RESET_SUCCESS"), 1);
        } else {
            $this->error(L("INVALID_OPERATION"), 1);
        }
    }

    function manage_carry()
    {

        $id                = intval($_REQUEST['id']);
        $manage_carry_list = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "admin_carry");
        $this->assign("manage_carry_list", $manage_carry_list);
        $this->display();
    }

    public function de_manage_carry()
    {
        $id = intval($_REQUEST['id']);

        $list = M("AdminCarry")->where('id=' . $id)->delete(); // 删除

        if (!$list) {
            $this->error("删除失败");
        } else {
            $this->success("删除成功");
        }
    }

    public function add_manage_carry()
    {
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $this->assign("adm_session", $adm_session);
        $this->display();
    }

    public function insert_carry()
    {
        $admin_id   = intval($_REQUEST['admin_id']);
        $admin_name = $_REQUEST['admin_name'];
        $money      = floatval($_REQUEST['money']);
        $memo       = $_REQUEST['memo'];

        //$creat_time = to_date($_REQUEST['creat_time']);
        $creat_time                 = TIME_UTC;
        $admin_carry                = array();
        $admin_carry['admin_id']    = $admin_id;
        $admin_carry['admin_name']  = $admin_name;
        $admin_carry['money']       = $money;
        $admin_carry['memo']        = $memo;
        $admin_carry['create_time'] = $creat_time;

        M("AdminCarry")->add($admin_carry);

        $this->assign("jumpUrl", u(MODULE_NAME . "/manage_carry"));
        $this->success(L("INSERT_SUCCESS"));
    }

    //统计信息
    function statistics()
    {
        //投资用户数
        $payUserCnt = $GLOBALS['db']->getOne("select count(distinct user_id) from fanwe_deal_load where is_auto = 0 and contract_no != '' ");
        $this->assign("payUserCnt", $payUserCnt);

        //总的用户
        $user_count       = M("User")->where('  is_delete = 0 and user_type = 0')->count();
        $this->assign("user_count", $user_count);
        //回收站用户
        $trash_user_count = M("User")->where("is_delete=1 and user_type = 0")->count();
        $this->assign("trash_user_count", $trash_user_count);

        //会员信息统计
        $user_card['user_passed']       = M("User")->where("idno != '' AND idcardpassed = 1")->count(); //已实名认证
        $user_card['user_passed_error'] = M("User")->where("idno != '' AND idcardpassed = 0")->count(); //身份证已存在，实名未成功的！
        $user_card['user_bank_cnt'] = M("user_bank")->count(); //身份证已存在，实名未成功的！
        $this->assign("user_card", $user_card);

        //所有用户信息统计
        $all_user_infos = $GLOBALS['db']->getRow("SELECT sum(money) as remain_money,sum(lock_money) as all_lock_moneys FROM " . DB_PREFIX . "user where is_auto = 0 and acct_type is null and is_effect = 1 AND user_type = 0 AND is_delete = 0 and id > 1;");
        $this->assign("all_user_infos", $all_user_infos);
        //所有标的冻结金额统计信息
        $deal_lock_data = $GLOBALS['db']->getAll("select dl.deal_id,d.name,d.deal_status,sum(dl.money - dl.coupon_cash) as all_lock_money from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d on dl.deal_id = d.id where dl.is_auto = 0 and dl.contract_no != '' and d.is_has_loans = 0 and d.is_has_loans = 0 GROUP BY dl.deal_id ORDER BY dl.deal_id desc");
        $lock_money_all = $GLOBALS['db']->getOne("select sum(dl.money - dl.coupon_cash) as lock_money_all from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d on dl.deal_id = d.id where dl.is_auto = 0 and dl.contract_no != '' and d.is_has_loans = 0 and d.is_has_loans = 0");
        foreach($deal_lock_data as $key=>$val){
            //0待等材料，1进行中，2满标，3流标，4还款中，5已还清
            if($val["deal_status"] == 1){
                $deal_lock_data[$key]['deal_status_desc'] = "进行中";
            }else if($val["deal_status"] == 2){
                $deal_lock_data[$key]['deal_status_desc'] = "满标";
            }else if($val["deal_status"] == 3){
                $deal_lock_data[$key]['deal_status_desc'] = "流标";
            }else if($val["deal_status"] == 4){
                $deal_lock_data[$key]['deal_status_desc'] = "还款中";
            }else if($val["deal_status"] == 5){
                $deal_lock_data[$key]['deal_status_desc'] = "已还清";
            }else{
                $deal_lock_data[$key]['deal_status_desc'] = "待等材料";
            }            
        }
        $this->assign("deal_lock_data", $deal_lock_data);
        //总计
        $this->assign("lock_money_all", $lock_money_all);

        //累计投标总额
        $all_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) as all_bid_money FROM " . DB_PREFIX . "deal_load where is_auto = 0 and contract_no != '' ");
        $this->assign("all_bid_money", $all_bid_money);
        //线上充值
        $online_pay    = floatval($GLOBALS['db']->getOne("SELECT	sum(a.money) FROM 	" . DB_PREFIX . "payment_notice a LEFT JOIN " . DB_PREFIX . "payment b ON a.payment_id = b.id WHERE 	a.is_paid = 1 and b.class_name != 'Otherpay'"));
        $this->assign("online_pay", $online_pay);

        //成功提现
        $carry_amount = M("UserCarry")->where("status=1")->sum("money");
        $this->assign("carry_amount", $carry_amount);

        //标种类型统计
        // $_sql           = "select dc.name as type_name,sum(c.money) as all_money,count(*) as num from (select d.name,d.cate_id,dl.deal_id,dl.money from fanwe_deal_load dl left join fanwe_deal d on dl.deal_id = d.id and dl.is_auto = 0 and dl.deal_id > 32) as c right join fanwe_deal_cate as dc on c.cate_id = dc.id GROUP BY dc.name";
        $_sql           = "select sum(dl.money)as all_money,count(dl.id)as num ,dc.name as type_name from fanwe_deal_load as dl left JOIN fanwe_deal as d on(dl.deal_id=d.id) LEFT JOIN fanwe_deal_cate as dc on(dc.id=d.cate_id) where is_auto=0 and dl.deal_id>32 GROUP BY d.cate_id";
        $deal_type_info = $GLOBALS['db']->getAll($_sql);
        $this->assign("deal_type_info", $deal_type_info);

        //已还款总额
        $has_repay_info  = $GLOBALS['db']->getRow("SELECT sum(dlr.repay_money) as h_repay_money,sum(dlr.self_money - dl.coupon_cash) as h_self_money,sum(dlr.pure_interests) as h_pure_interests,sum(dlr.coupon_interests) as h_coupon_interests,sum(dlr.active_interest_money) as h_act_interests,sum(dl.coupon_cash) as h_coupon_cash,sum(dlr.interest_money) as h_all_interests FROM " . DB_PREFIX . "deal_load_repay dlr left join " . DB_PREFIX . "deal_load dl on dlr.load_id = dl.id where has_repay = 1 ");
        $this->assign("has_repay_info", $has_repay_info);
        //未还总额
        $need_repay_info = $GLOBALS['db']->getRow("SELECT sum(dlr.repay_money) as h_repay_money,sum(dlr.self_money - dl.coupon_cash) as h_self_money,sum(dlr.pure_interests) as h_pure_interests,sum(dlr.coupon_interests) as h_coupon_interests,sum(dlr.active_interest_money) as h_act_interests,sum(dl.coupon_cash) as h_coupon_cash,sum(dlr.interest_money) as h_all_interests FROM " . DB_PREFIX . "deal_load_repay dlr left join " . DB_PREFIX . "deal_load dl on dlr.load_id = dl.id where has_repay = 0 ");
        $this->assign("need_repay_info", $need_repay_info);
        //泉龙达短信条数预警
        $ress = send_sms_email($msg_item, 3,"EN");
        if ($ress['status'] == 1) {
            $result['number'] = $ress['return'];
        } else {
            $result['number'] = 0;
        }
        $result['description'] = "剩余条数(包括套内和可超发条数)";
        $this->assign("result", $result);
        //一信通短信条数预警
        require_once APP_ROOT_PATH . "system/sms/YY_sms.php";
        $yy_sms                = new YY_sms();
        $yy_arr                = $yy_sms->get_count_msg();
        $this->assign("yy_arr", $yy_arr);
        //过期标的预警
        $time            = time();
        //查找所有正在进行中的借款标
        $deals_abate_num = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "deal where deal_status = 1 AND is_has_loans = 0 AND is_effect = 1 AND is_delete = 0 AND (start_time + (enddate * 24 * 3600) - " . $time . ") < 0");
        $deals_abate_num = $deals_abate_num ? $deals_abate_num : 0;
        $this->assign("deals_abate_num", $deals_abate_num);
        //红包
        $bonusList = $GLOBALS['db']->getAll("select status,sum(money) as allmoney from fanwe_user_bonus group by status");
        $this->assign("bonusList", $bonusList);


        $bonusTypeList = $GLOBALS['db']->getAll("select status,bonus_type,sum(money)  as allmoney from fanwe_user_bonus  group by status,bonus_type order by bonus_type,status");
        $this->assign("bonusTypeList", $bonusTypeList);
        $this->display();
    }

}

?>