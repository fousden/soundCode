<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
require APP_ROOT_PATH . 'app/Lib/deal.php';

class dealModule extends SiteBaseModule {

    public function index() {

        /* if(!$GLOBALS['user_info']){
          set_gopreview();
          app_redirect(url("index","user#login"));
          } */
        //担保凭证图片操作
        $id = intval($_REQUEST['id']);

        $deal = get_deal($id);

        if ($deal['cate_id'] == 4) {
            $tmp = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal_load WHERE deal_id = " . $id . "  and user_id = '" . $GLOBALS['user_info']['id'] . "'  ");
            if ($tmp < 1) {
                header("Location: /");
                exit;
            }
        }

        $icon_img = $GLOBALS['db']->getAll("SELECT icon_url,deal_id FROM " . DB_PREFIX . "deal_gallery WHERE deal_id = " . $id . " order by deal_id ASC ");
        foreach ($icon_img as $k => $v) {
            $icon_img_url[] = $v['icon_url'];
        }

        $icon_img_count = count($icon_img_url);
        $icon_img_1 = array_slice($icon_img_url, 0, 6);
        $icon_img_2 = array_slice($icon_img_url, 6, 6);
        $icon_img_3 = array_slice($icon_img_url, 12, 6);

        $GLOBALS['tmpl']->assign('icon_img_count', $icon_img_count);
        $GLOBALS['tmpl']->assign('icon_img_url', $icon_img_url);
        $GLOBALS['tmpl']->assign('icon_img_1', $icon_img_1);
        $GLOBALS['tmpl']->assign('icon_img_2', $icon_img_2);
        $GLOBALS['tmpl']->assign('icon_img_3', $icon_img_3);


        send_deal_contract_email($id, $deal, $deal['user_id']);

        if (!$deal)
            app_redirect(url("index"));

        //借款列表
        $load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,is_auto,create_time FROM " . DB_PREFIX . "deal_load WHERE deal_id = " . $id . " order by id ASC ");

        $u_info = get_user("*", $deal['user_id']);

        if ($deal['view_info'] != "") {
            $view_info_list = unserialize($deal['view_info']);
            $GLOBALS['tmpl']->assign('view_info_list', $view_info_list);
        }


        //可用额度
        $can_use_quota = get_can_use_quota($deal['user_id']);
        $GLOBALS['tmpl']->assign('can_use_quota', $can_use_quota);

        $credit_file = get_user_credit_file($deal['user_id'], $u_info);
        $deal['is_faved'] = 0;
        if ($GLOBALS['user_info']) {
            if ($u_info['user_type'] == 1)
                $company = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "user_company WHERE user_id=" . $u_info['id']);

            $deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal_collect WHERE deal_id = " . $id . " AND user_id=" . intval($GLOBALS['user_info']['id']));

            if ($deal['deal_status'] >= 4) {
                //还款列表
                $loan_repay_list = get_deal_load_list($deal);
                $GLOBALS['tmpl']->assign("loan_repay_list", $loan_repay_list);

                if ($loan_repay_list) {
                    $temp_self_money_list = $GLOBALS['db']->getAll("SELECT sum(self_money) as total_money,u_key FROM " . DB_PREFIX . "deal_load_repay WHERE has_repay=1 AND deal_id=" . $id . " group by u_key ");
                    $self_money_list = array();
                    foreach ($temp_self_money_list as $k => $v) {
                        $self_money_list[$v['u_key']] = $v['total_money'];
                    }

                    foreach ($load_list as $k => $v) {
                        $load_list[$k]['remain_money'] = $v['money'] - $self_money_list[$k];
                        if ($load_list[$k]['remain_money'] <= 0) {
                            $load_list[$k]['remain_money'] = 0;
                            $load_list[$k]['status'] = 1;
                        }
                    }
                }
            }
            $user_statics = sys_user_status($deal['user_id'], true);
            $GLOBALS['tmpl']->assign("user_statics", $user_statics);
            $GLOBALS['tmpl']->assign("company", $company);


            if ($deal['uloadtype'] == 1) {
                $has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id=" . $id);
                $GLOBALS['tmpl']->assign("has_bid_money", $has_bid_money);
                $GLOBALS['tmpl']->assign("has_bid_portion", intval($has_bid_money) / ($deal['borrow_amount'] / $deal['portion']));
            }
        }

        $GLOBALS['tmpl']->assign("load_list", $load_list);
        $GLOBALS['tmpl']->assign("credit_file", $credit_file);
        $GLOBALS['tmpl']->assign("u_info", $u_info);

        if ($deal['type_match_row'])
            $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $GLOBALS['lang']['GOLD_FINAN_DETAIL_TITLE']; /* $deal['type_match_row'] . " - " . $deal['name'] */
        else
            $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $deal['name'];

        $GLOBALS['tmpl']->assign("page_title", $seo_title);
        $seo_keyword = $deal['seo_keyword'] != '' ? $deal['seo_keyword'] : $deal['type_match_row'] . "," . $deal['name'];
        $GLOBALS['tmpl']->assign("page_keyword", $seo_keyword . ",");
        $seo_description = $deal['seo_description'] != '' ? $deal['seo_description'] : $deal['name'];
        $GLOBALS['tmpl']->assign("seo_description", $seo_description . ",");

        //留言
        require APP_ROOT_PATH . 'app/Lib/message.php';
        require APP_ROOT_PATH . 'app/Lib/page.php';

        $rel_table = 'deal';

        $message_type = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "message_type where type_name='" . $rel_table . "'");
        $condition = "rel_table = '" . $rel_table . "' and rel_id = " . $id;

        if (app_conf("USER_MESSAGE_AUTO_EFFECT") == 0) {
            $condition.= " and user_id = " . intval($GLOBALS['user_info']['id']);
        } else {
            if ($message_type['is_effect'] == 0) {
                $condition.= " and user_id = " . intval($GLOBALS['user_info']['id']);
            }
        }

        //message_form 变量输出
        $GLOBALS['tmpl']->assign('rel_id', $id);
        $GLOBALS['tmpl']->assign('rel_table', $rel_table);

        //分页
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");
        $msg_condition = $condition . " AND is_effect = 1 ";
        $message = get_message_list($limit, $msg_condition);

        $page = new Page($message['count'], app_conf("PAGE_SIZE"));   //初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);

        foreach ($message['list'] as $k => $v) {
            $msg_sub = get_message_list("", "pid=" . $v['id'], false);
            $message['list'][$k]["sub"] = $msg_sub["list"];
        }

        $GLOBALS['tmpl']->assign("message_list", $message['list']);
        if (!$GLOBALS['user_info']) {
            $GLOBALS['tmpl']->assign("message_login_tip", sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'], url("shop", "user#login"), url("shop", "user#register")));
        }
        //结息日期格式转换
        if ($deal['qixi_time'] == '0000-00-00') {
            $deal['qixi_time'] = '暂无';
        }
        if ($deal['jiexi_time'] == '0000-00-00') {
            $deal['jiexi_time'] = '暂无';
        }
        //最迟还款日格式转换
        if ($deal['last_mback_time'] == '0000-00-00') {
            $deal['last_mback_time'] = '暂无';
        }
        //起息日期格式转换
        if ($deal['repay_start_date'] == '0000-00-00') {
            $deal['repay_start_date'] = '暂无';
        }
        //到期利息（元）{$deal.month_repay_money_format}   原为剩余可投金额的利息
        $deal['month_repay_money_format'] = num_format($deal['borrow_amount'] * $deal['rate'] / 360 * $deal['repay_time'] / 100);
        //将年利率的小数点分割整数，小数点
        $min_loan_money_arr = explode('.', $deal['min_loan_money']);
        $deal['min_loan_money_num'] = $min_loan_money_arr[0];

        //时间重组
        $deal['start_date'] = to_date($deal['start_time'], "Y-m-d");
        $deal['start_dates'] = to_date($deal['start_time'], "H:i:s");

        if (!$deal['success_time']) {
            $deal['end_time'] = to_date($deal['start_time'] - 1, "H:i:s");
            $deal['end_times'] = $deal['start_time'] + 3600 * 24 * $deal['enddate']; //结束时间
            $deal['end_date'] = to_date($deal['end_times'], "Y-m-d");

            $deal['qixi_time'] = to_date($deal['end_times'] + 3600 * 24 * 1, "Y-m-d"); //起息时间
            $deal['jiexi_time'] = to_date(strtotime($deal['qixi_time']) + 3600 * 24 * $deal['repay_time'], "Y-m-d"); //结息时间
            $deal['last_mback_time'] = to_date(strtotime($deal['jiexi_time']) + 3600 * 24 * 2, "Y-m-d"); //默认是结息日后3天
        } else {
            $deal['end_time'] = to_date($deal['success_time'], "H:i:s");
            $deal['end_times'] = $deal['success_time']; //结束时间
            $deal['end_date'] = to_date($deal['end_times'], "Y-m-d");
        }



        //
        //计算时间的进度百分率
        $deal['jieshu_course'] = round((get_gmtime() - $deal['start_time']) / ($deal['end_times'] - $deal['start_time']) * 100, 2);
        $deal['qixi_course'] = round((get_gmtime() - $deal['end_times']) / (strtotime($deal['qixi_time']) - $deal['end_times']) * 100, 2);
        $deal['jiexi_course'] = round((get_gmtime() - strtotime($deal['qixi_time'])) / (strtotime($deal['jiexi_time']) - strtotime($deal['qixi_time'])) * 100, 2);
        $deal['end_course'] = round((get_gmtime() - strtotime($deal['jiexi_time'])) / ($deal['enddate'] * 3600 * 24) * 100, 2);
        $deal['jieshu_course'] = $deal['jieshu_course'] > 100 ? 100 : $deal['jieshu_course'];
        $deal['qixi_course'] = $deal['qixi_course'] > 100 ? 100 : $deal['qixi_course'];
        $deal['jiexi_course'] = $deal['jiexi_course'] > 100 ? 100 : $deal['jiexi_course'];
        $deal['end_course'] = $deal['end_course'] > 100 ? 100 : $deal['end_course'];

        $GLOBALS['tmpl']->assign("page_title", $deal['seo_title']);
        $GLOBALS['tmpl']->assign("page_keyword", $deal['seo_keyword']);
        $GLOBALS['tmpl']->assign("page_description", $deal['seo_description']);
        $GLOBALS['tmpl']->assign("deal", $deal);
        //取出贷款中的多张图片
        $icon_url = $GLOBALS['db']->getAll("select icon_url from fanwe_deal_gallery where deal_id={$deal['id']}");
        $GLOBALS['tmpl']->assign("icon_url", $icon_url);

        //var_dump($icon_url);


        $qr_code_url = SITE_DOMAIN . '/wap/index.php?ctl=register_red&r=' . base64_encode($GLOBALS['user_info']['mobile']);
        $GLOBALS['tmpl']->assign("qr_code_url", $qr_code_url);

        //分享代码
        $show_share_code = share_code(6);
        $GLOBALS['tmpl']->assign("show_share_code", $show_share_code);

        if ($GLOBALS['user_info']['id']) {
            //获取优惠券列表信息
            $coupon_time = time();
            $coupon_lists = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "user_coupon where status = 0 AND " . $coupon_time . " >= start_time AND " . $coupon_time . " <= end_time AND user_id = " . $GLOBALS['user_info']['id'] . " group by face_value,min_limit ");
            $GLOBALS['tmpl']->assign("coupon_lists", $coupon_lists);
        }

        $GLOBALS['tmpl']->display("page/deal.html");
    }

    public function deal_record(){
        $id = $_REQUEST['id'];
        $sql = "select count(*) from ".DB_PREFIX."deal_load where deal_id={$id}";
        $count = $GLOBALS['db']->getOne($sql); // 一共几条数据
        $page = intval($_REQUEST['p']);
        $page_size = 10; // 后台每页几条数据
        $page_count = ceil($count/$page_size); //几页
        if($page==0)
            $page = 1; // 第几页
        $limit = (($page-1)*$page_size).",".$page_size;
        $sql = "select dl.money,dl.create_time,u.mobile from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."user as u on dl.user_id=u.id where dl.deal_id={$id} order by dl.create_time desc limit ".$limit;
        $deal_info = $GLOBALS['db']->getAll($sql);
        foreach($deal_info as $key=>$val){
            $deal_info[$key]['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
            // 处理下号码
            $tmobile = substr($val['mobile'],0,3);// 取出前三位号码如果是114则做处理
            if($tmobile=="114"){
                $mobile = "188".substr($val['mobile'],3);// 取出除了前三位的手机号码
                $deal_info[$key]['mobile'] = hideMobile($mobile);// 拼接上188字符串;
            }else{
                $deal_info[$key]['mobile'] = hideMobile($val['mobile']);
            }
        }
        $list['page'] = $page;// 当前第几页
        $list['page_count'] = $page_count; // 一共几页
        $list['page_size'] = $page_size; //每页显示的条数
        $list['count'] = $count;
        $list['deal_info'] = $deal_info;
        ajax_return($list);

    }

    public function mobile() {
        /* if(!$GLOBALS['user_info']){
          set_gopreview();
          app_redirect(url("index","user#login"));
          } */

        $id = intval($_REQUEST['id']);

        $deal = get_deal($id, 0);

        send_deal_contract_email($id, $deal, $deal['user_id']);

        if (!$deal)
            app_redirect(url("index"));

        //借款列表
        $load_list = $GLOBALS['db']->getAll("SELECT deal_id,user_id,user_name,money,is_auto,create_time FROM " . DB_PREFIX . "deal_load WHERE deal_id = " . $id);

        $u_info = get_user("*", $deal['user_id']);
        $GLOBALS['tmpl']->assign('risk_security', $deal['risk_security']);
        //可用额度
        $can_use_quota = get_can_use_quota($deal['user_id']);
        $GLOBALS['tmpl']->assign('can_use_quota', $can_use_quota);

        $credit_file = get_user_credit_file($deal['user_id']);
        $deal['is_faved'] = 0;
        if ($GLOBALS['user_info']) {
            $deal['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal_collect WHERE deal_id = " . $id . " AND user_id=" . intval($GLOBALS['user_info']['id']));

            if ($deal['deal_status'] >= 4) {
                //还款列表
                $loan_repay_list = get_deal_load_list($deal);
                $GLOBALS['tmpl']->assign("loan_repay_list", $loan_repay_list);
                foreach ($load_list as $k => $v) {
                    $load_list[$k]['remain_money'] = $v['money'] - $GLOBALS['db']->getOne("SELECT sum(self_money) FROM " . DB_PREFIX . "deal_load_repay WHERE user_id=" . $v['user_id'] . " AND deal_id=" . $id);
                    if ($load_list[$k]['remain_money'] <= 0) {
                        $load_list[$k]['remain_money'] = 0;
                        $load_list[$k]['status'] = 1;
                    }
                }
            }
            $user_statics = sys_user_status($deal['user_id'], true);
            $GLOBALS['tmpl']->assign("user_statics", $user_statics);
        }

        $GLOBALS['tmpl']->assign("load_list", $load_list);
        $GLOBALS['tmpl']->assign("credit_file", $credit_file);
        $GLOBALS['tmpl']->assign("u_info", $u_info);

        //工作认证是否过期
        //$GLOBALS['tmpl']->assign('expire',user_info_expire($u_info));

        if ($deal['type_match_row'])
            $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $deal['type_match_row'] . " - " . $deal['name'];
        else
            $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $deal['name'];

        $GLOBALS['tmpl']->assign("page_title", $seo_title);
        $seo_keyword = $deal['seo_keyword'] != '' ? $deal['seo_keyword'] : $deal['type_match_row'] . "," . $deal['name'];
        $GLOBALS['tmpl']->assign("page_keyword", $seo_keyword . ",");
        $seo_description = $deal['seo_description'] != '' ? $deal['seo_description'] : $deal['name'];
        $GLOBALS['tmpl']->assign("seo_description", $seo_description . ",");

        //留言
        require APP_ROOT_PATH . 'app/Lib/message.php';
        require APP_ROOT_PATH . 'app/Lib/page.php';
        $rel_table = 'deal';
        $message_type = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "message_type where type_name='" . $rel_table . "'");
        $condition = "rel_table = '" . $rel_table . "' and rel_id = " . $id;

        if (app_conf("USER_MESSAGE_AUTO_EFFECT") == 0) {
            $condition.= " and user_id = " . intval($GLOBALS['user_info']['id']);
        } else {
            if ($message_type['is_effect'] == 0) {
                $condition.= " and user_id = " . intval($GLOBALS['user_info']['id']);
            }
        }

        //message_form 变量输出
        $GLOBALS['tmpl']->assign('rel_id', $id);
        $GLOBALS['tmpl']->assign('rel_table', "deal");

        //分页
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;
        $limit = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");
        $msg_condition = $condition . " AND is_effect = 1 ";
        $message = get_message_list($limit, $msg_condition);

        $page = new Page($message['count'], app_conf("PAGE_SIZE"));   //初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);

        foreach ($message['list'] as $k => $v) {
            $msg_sub = get_message_list("", "pid=" . $v['id'], false);
            $message['list'][$k]["sub"] = $msg_sub["list"];
        }

        $GLOBALS['tmpl']->assign("message_list", $message['list']);
        if (!$GLOBALS['user_info']) {
            $GLOBALS['tmpl']->assign("message_login_tip", sprintf($GLOBALS['lang']['MESSAGE_LOGIN_TIP'], url("shop", "user#login"), url("shop", "user#register")));
        }

        $GLOBALS['tmpl']->assign("deal", $deal);
        $GLOBALS['tmpl']->display("deal_mobile.html");
    }

    function preview() {
        $deal['id'] = 'XXX';

        $deal_loan_type_list = load_auto_cache("deal_loan_type_list");
        if (intval($_REQUEST['quota']) == 1) {
            $deal = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "deal_quota_submit WHERE status=1 and user_id = " . $GLOBALS['user_info']['id'] . " ORDER BY id DESC");
            $type_id = intval($deal['type_id']);
            $data['view_info'] = unserialize($deal['view_info']);
            if ($deal['cate_id'] > 0) {
                $deal['cate_info'] = $GLOBALS['db']->getRow("select id,name,brief,uname,icon from " . DB_PREFIX . "deal_cate where id = " . $deal['cate_id'] . " and is_effect = 1 and is_delete = 0");
            }
        } else {
            $deal['name'] = strim($_REQUEST['borrowtitle']);
            $type_id = intval($_REQUEST['borrowtype']);

            $icon_type = strim($_REQUEST['imgtype']);

            $icon_type_arr = array(
                'upload' => 1,
                'userImg' => 2,
                'systemImg' => 3,
            );
            $data['icon_type'] = $icon_type_arr[$icon_type];

            switch ($data['icon_type']) {
                case 1 :
                    $deal['icon'] = replace_public(strim($_REQUEST['icon']));
                    break;
                case 2 :
                    $deal['icon'] = replace_public(get_user_avatar($GLOBALS['user_info']['id'], 'big'));
                    break;
                case 3 :
                    $deal['icon'] = $GLOBALS['db']->getOne("SELECT icon FROM " . DB_PREFIX . "deal_loan_type WHERE id=" . intval($_REQUEST['systemimgpath']));
            }


            $deal['description'] = replace_public(valid_str(btrim($_REQUEST['borrowdesc'])));


            $user_view_info = $GLOBALS['user_info']['view_info'];
            $user_view_info = unserialize($user_view_info);

            $new_view_info_arr = array();
            for ($i = 1; $i <= intval($_REQUEST['file_upload_count']); $i++) {
                $img_info = array();
                $img = replace_public(strim($_REQUEST['file_' . $i]));
                if ($img != "") {
                    $img_info['name'] = strim($_REQUEST['file_name_' . $i]);
                    $img_info['img'] = $img;
                    $img_info['is_user'] = 1;

                    $user_view_info[] = $img_info;
                    $ss = $user_view_info;
                    end($ss);
                    $key = key($ss);
                    $new_view_info_arr[$key] = $img_info;
                }
            }


            $data['view_info'] = array();
            foreach ($_REQUEST['file_key'] as $k => $v) {
                if (isset($user_view_info[$v])) {
                    $data['view_info'][$v] = $user_view_info[$v];
                }
            }

            foreach ($new_view_info_arr as $k => $v) {
                $data['view_info'][$k] = $v;
            }

            if ($deal['cate_id'] > 0) {
                $deal['cate_info']['name'] = "借款预览标";
            }
        }


        $deal['rate_foramt'] = number_format(strim($_REQUEST['apr']), 2);
        $deal['repay_time'] = strim($_REQUEST['repaytime']);
        $deal['repay_time_type'] = intval($_REQUEST['repaytime_type']);
        $deal['loantype'] = intval($_REQUEST['loantype']);

        $deal['borrow_amount'] = strim($_REQUEST['borrowamount']);
        $deal['borrow_amount_format'] = format_price($deal['borrow_amount'] / 10000) . "万";

        $GLOBALS['tmpl']->assign('view_info_list', $data['view_info']);
        unset($data['view_info']);

        foreach ($deal_loan_type_list as $k => $v) {
            if ($v['id'] == $type_id) {
                $deal['type_info'] = $v;
            }
        }


        $deal['min_loan_money'] = 50;
        $deal['need_money'] = $deal['borrow_amount_format'];



        //本息还款金额
        $deal['month_repay_money'] = format_price(pl_it_formula($deal['borrow_amount'], strim($deal['rate']) / 12 / 100, $deal['repay_time']));


        if ($deal['agency_id'] > 0) {
            $deal['agency_info'] = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where id = " . $deal['agency_id'] . " and is_effect = 1");
        }

        $deal['progress_point'] = 0;
        $deal['buy_count'] = 0;
        $deal['voffice'] = 1;
        $deal['vjobtype'] = 1;


        $deal['is_delete'] = 2;

        $u_info = get_user("*", $GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign("u_info", $u_info);

        $can_use_quota = get_can_use_quota($GLOBALS['user_info']['id']);
        $GLOBALS['tmpl']->assign('can_use_quota', $can_use_quota);

        $credit_file = get_user_credit_file($GLOBALS['user_info']['id'], $u_info);
        $GLOBALS['tmpl']->assign("credit_file", $credit_file);
        $user_statics = sys_user_status($GLOBALS['user_info']['id'], true);
        $GLOBALS['tmpl']->assign("user_statics", $user_statics);


        $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $deal['type_match_row'] . " - " . $deal['name'];
        $GLOBALS['tmpl']->assign("page_title", $seo_title);
        $seo_keyword = $deal['seo_keyword'] != '' ? $deal['seo_keyword'] : $deal['type_match_row'] . "," . $deal['name'];
        $GLOBALS['tmpl']->assign("page_keyword", $seo_keyword . ",");
        $seo_description = $deal['seo_description'] != '' ? $deal['seo_description'] : $deal['name'];

        $GLOBALS['tmpl']->assign("seo_description", $seo_description . ",");

        $GLOBALS['tmpl']->assign("deal", $deal);

        $GLOBALS['tmpl']->display("page/deal.html");
    }

    function bid() {
        if (!$GLOBALS['user_info']) {
            set_gopreview();
            app_redirect(url("index", "user#login"));
        }

        //如果未绑定手机
        if (intval($GLOBALS['user_info']['mobilepassed']) == 0) {
            $GLOBALS['tmpl']->assign("page_title", "成为借出者");
            $GLOBALS['tmpl']->display("page/deal_mobilepaseed.html");
            exit();
        }



        $id = intval($_REQUEST['id']);
        $deal = get_deal($id);
        if (!$deal)
            app_redirect(url("index"));

        if ($deal['user_id'] == $GLOBALS['user_info']['id']) {
            showErr($GLOBALS['lang']['CANT_BID_BY_YOURSELF']);
        }

        if ($deal['ips_bill_no'] != "" && $GLOBALS['user_info']['ips_acct_no'] == "") {
            showErr("此标为第三方托管标，请先绑定第三方托管账户", 0, url("index", "uc_center"));
        }


        $has_bid_money = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id=" . $id);
        $GLOBALS['tmpl']->assign("has_bid_money", $has_bid_money);
        if ($deal['uloadtype'] == 1) {
            $GLOBALS['tmpl']->assign("has_bid_portion", intval($has_bid_money) / ($deal['borrow_amount'] / $deal['portion']));
        }

        $seo_title = $deal['seo_title'] != '' ? $deal['seo_title'] : $deal['type_match_row'] . " - " . $deal['name'];
        $GLOBALS['tmpl']->assign("page_title", $seo_title);
        $seo_keyword = $deal['seo_keyword'] != '' ? $deal['seo_keyword'] : $deal['type_match_row'] . "," . $deal['name'];
        $GLOBALS['tmpl']->assign("page_keyword", $seo_keyword . ",");
        $seo_description = $deal['seo_description'] != '' ? $deal['seo_description'] : $deal['name'];

        $GLOBALS['tmpl']->assign("deal", $deal);
        $GLOBALS['tmpl']->display("page/deal_bid.html");
    }

    function dobidstepone() {
        if (!$GLOBALS['user_info'])
            showErr($GLOBALS['lang']['PLEASE_LOGIN_FIRST'], 1);

        if (strim($_REQUEST['name']) == "") {
            showErr($GLOBALS['lang']['PLEASE_INPUT'] . $GLOBALS['lang']['URGENTCONTACT'], 1);
        }
        $data['real_name'] = strim($_REQUEST['name']);
        if ($GLOBALS['user_info']['idcardpassed'] == 0) {
            if (strim($_REQUEST['idno']) == "") {
                showErr($GLOBALS['lang']['PLEASE_INPUT'] . $GLOBALS['lang']['IDNO'], 1);
            }

            if (getIDCardInfo(strim($_REQUEST['idno'])) == 0) {  //身份证正则表达式
                showErr($GLOBALS['lang']['FILL_CORRECT_IDNO'], 1);
            }

            if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "user where idno = '" . strim($_REQUEST['idno']) . "' and id <> " . intval($GLOBALS['user_info']['id'])) > 0) {
                showErr(sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'], $GLOBALS['lang']['IDNO']), 1);
            }
            if (strim($_REQUEST['idno']) != strim($_REQUEST['idno_re'])) {
                showErr($GLOBALS['lang']['TWO_ENTER_IDNO_ERROR'], 1);
            }
            $data['idno'] = strim($_REQUEST['idno']);
            $data['idcardpassed'] = 0;
        }

        /* 手机 */
        if ($GLOBALS['user_info']['mobilepassed'] == 0) {
            if (strim($_REQUEST['phone']) == "") {
                showErr($GLOBALS['lang']['MOBILE_EMPTY_TIP'], 1);
            }
            if (!check_mobile(strim($_REQUEST['phone']))) {
                showErr($GLOBALS['lang']['FILL_CORRECT_MOBILE_PHONE'], 1);
            }
            if (strim($_REQUEST['validateCode']) == "") {
                showErr($GLOBALS['lang']['PLEASE_INPUT'] . $GLOBALS['lang']['VERIFY_CODE'], 1);
            }
            if (strim($_REQUEST['validateCode']) != $GLOBALS['user_info']['bind_verify']) {
                showErr($GLOBALS['lang']['BIND_MOBILE_VERIFY_ERROR'], 1);
            }
            $data['mobile'] = strim($_REQUEST['phone']);
            $data['mobilepassed'] = 1;
        }

        $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, "UPDATE", "id=" . $GLOBALS['user_info']['id']);

        showSuccess($GLOBALS['lang']['SUCCESS_TITLE'], 1);
    }

    function dobid() {
        set_time_limit(0);
        $ajax = intval($_REQUEST["ajax"]);
        $id = intval($_REQUEST["id"]);
        $bid_money = floatval($_REQUEST["bid_money"]);
        $coupon_id = intval($_REQUEST["coupon_id"]); //优惠券ID
        $bid_paypassword = strim(FW_DESPWD($_REQUEST['bid_paypassword']));

        //检测优惠券是否能已被使用
        $coupon_status = $GLOBALS['db']->getRow("select status,face_value,coupon_type from " . DB_PREFIX . "user_coupon where id = '" . $coupon_id . "'");
        if ($coupon_status['status'] == 1) {
            $status['show_err'] = "该优惠券已被使用，不能再次使用！";
            showErr($status['show_err'], $ajax);
        }
        if ($coupon_status['coupon_type'] == 2 && $coupon_status['face_value'] > 0) {
            $coupon_money = $bid_money - $coupon_status['face_value'];
        }
        //检测用户信息真实性
        //check_user_info($GLOBALS['user_info'],$ajax);
        //投标时检测富友余额是否足够
        if ($GLOBALS["user_info"]["user_name"] != "李明明" && $GLOBALS["user_info"]['is_auto'] != 1) {
            require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
            $fuyou_payment = new Fuyou_payment();
            $check_data = $fuyou_payment->check_balance($GLOBALS['user_info']);
            if ($check_data['status'] == 1) {
                if ($coupon_money > $check_data['ca_balance']) {
                    showErr("<div style='font-size:16px;color:#C40000'>富友账户可用余额不足，请先充值！</div>", $ajax);
                }
            } elseif ($check_data['status'] == 2) {
                //服务器忙 响应失败，请稍后重试！
                showErr($check_data['show_error'], $ajax);
            }
        }

        //开始投标
        $status = dobid2($id, $bid_money, $bid_paypassword, 1, 1, $coupon_id);
        if ($status['status'] == 6) {
            //投标成功 如果该用户是被邀请投资 则邀请人将获得随机1-20元随机现金红包
            invite_active($bid_money);
            $user_info = es_session::get("user_info");
            MO("User")->insert_lottery_number($user_info['mobile'], 2);

            //TODO根据标的类型替换URL
            $deal = get_deal($id);
            $time=date("Y-m-d");
            if ((strpos($deal['name'], "上海旭弘") !== false || strpos($deal['name'], "商超货款") !== false) && ($time>'2016-03-01' && $time<'2016-04-30')) {
                $status['url'] = '/index.php?ctl=activity&act=lottery&type=market_activities&is_pc=1';
            } else if (strpos($deal['name'], "海德商厦") !== false  && ($time>'2016-03-01' && $time<'2016-05-15')) {
                $status['url'] = '/index.php?ctl=activity&act=lottery&type=hfbl&is_pc=1';
            } else if (strpos($deal['name'], "CBJR")!==false  && ($time>'2016-03-22' && $time<'2016-07-21')) {
                $status['url'] = '/index.php?ctl=activity&act=lottery&type=retail_financial&is_pc=1';
            }else if(strpos($deal['name'], "YMJX")!==false  && ($time>'2016-04-19' && $time<'2016-04-30')){
                $status['url'] = '/index.php?ctl=lottery&type=yuemojiaxi&is_pc=1';
            } else if($time>'2016-03-01' && $time<'2016-03-31'){
                $status['url'] = '/index.php?ctl=activity&act=lottery&type=springLuck&is_pc=1';
            }else{
                $status['url']='/index.php?ctl=activity&act=lottery&type=hfbl&is_pc=1';
            }
            //成功提示
            showSuccess($status, $ajax, url("index", "uc_invest"));
        } elseif ($status['status'] == 0 || $status['status'] == 5 || $status['status'] == 4) {
            showErr($status['show_err'], $ajax);
        } elseif ($status['status'] == 2) {
            ajax_return($status);
        } elseif ($status['status'] == 3) {
            showSuccess("余额不足，请先去充值", $ajax, url("index", "uc_money#incharge"));
        }
    }

}

?>
