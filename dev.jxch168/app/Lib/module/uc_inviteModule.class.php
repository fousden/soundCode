<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/uc.php';

class uc_inviteModule extends SiteBaseModule {

    public function index() {
        //推荐好友总数
        $data['referral_user'] = intval($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "user where pid = " . $GLOBALS['user_info']['id'] . " and is_effect=1 and is_delete=0 AND user_type in(0,1) "));

        $sql_str = "select sum(money) from " . DB_PREFIX . "user_reward where user_id = " . $GLOBALS['user_info']['id'] . " ";
        //返利总金额
        $data['total_rebate_money'] = floatval($GLOBALS['db']->getOne($sql_str));
        
        //到账总金额
        $data['total_referral_money'] = floatval($GLOBALS['db']->getOne($sql_str . ' and status=1 '));
        
        //昨天返利
        $yesterday_start_time=  strtotime(date('Y-m-d',  strtotime("-1day")).'00:00:00');
        $yesterday_end_time=  strtotime(date('Y-m-d',  strtotime("-1day")).'23:59:59');
        $data['total_yesterday_money'] = floatval($GLOBALS['db']->getOne($sql_str . " and status=1 and act_release_time>$yesterday_start_time and act_release_time<$yesterday_end_time"));
        
        $GLOBALS['tmpl']->assign("data", $data);
        $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['UC_INVITE']);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_invite_index.html");

        if (intval(app_conf("URL_MODEL")) == 0)
            $depart = "&";
        else
            $depart = "?";
        $share_url = SITE_DOMAIN . url("index", "user#register");
        if ($GLOBALS['user_info']) {
            $share_url_mobile = $share_url . $depart . "r=" . base64_encode($GLOBALS['user_info']['mobile']);
            $share_url_username = $share_url . $depart . "r=" . base64_encode($GLOBALS['user_info']['user_name']);
        }

        //生成二维码
        //二维码URL
        $qr_code_url = SITE_DOMAIN . '/wap/index.php?ctl=register_red&r=' . base64_encode($GLOBALS['user_info']['mobile']);
        $logo_img_url = APP_ROOT_PATH . 'public/images/logo.png';

        $my_grcode = gen_qrcode($qr_code_url, $logo_img_url, 3);

        //分享代码
        $show_share_code = share_code();

        $GLOBALS['tmpl']->assign("share_url_mobile", $share_url_mobile);
        $GLOBALS['tmpl']->assign("my_grcode", $my_grcode);
        $GLOBALS['tmpl']->assign("show_share_code", $show_share_code);
        $GLOBALS['tmpl']->assign("qr_code_url", $qr_code_url);
        $GLOBALS['tmpl']->assign("share_url_username", $share_url_username);

        $GLOBALS['tmpl']->display("page/uc.html");
    }

    function invite() {
        $type = intval($_REQUEST['type']);
        $page = intval($_REQUEST['p']);
        if ($page == 0)
            $page = 1;

        $limit = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");
        $result = get_invite_list($limit, $GLOBALS['user_info']['id'], $type);


        $GLOBALS['tmpl']->assign("list", $result['list']);
        $page = new Page($result['count'], app_conf("PAGE_SIZE"));   //初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);
        $GLOBALS['tmpl']->assign("type", $type);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_invite_list.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    function reward() {
        if (!$_REQUEST['type']) {
            $_REQUEST['type'] = 1;
        }

        //获取红包信息
        $result = getRewardList($GLOBALS['user_info']['id'], intval($_REQUEST['p']), '', '', $_REQUEST['type']);
        $list = $result['list'];
        foreach ($list as $key => $val) {
            $name = $GLOBALS['db']->getOne("select user_name from " . DB_PREFIX . "deal_load where id=" . $val['load_id']);
            $list[$key]['name'] = $name;
        }
        $count = $result['count'];
        $reward_count = $result['reward_count'];
        $repay_count = $result['repay_count'];
        $GLOBALS['tmpl']->assign("list", $list);
        $page = new Page($count, app_conf("PAGE_SIZE"));   //初始化分页对象
        $p = $page->show();
        $GLOBALS['tmpl']->assign('reward_count', $reward_count);
        $GLOBALS['tmpl']->assign('repay_count', $repay_count);
        $GLOBALS['tmpl']->assign("page_title", "我的红包");
        $GLOBALS['tmpl']->assign('pages', $p);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_invite_reward.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

}

?>