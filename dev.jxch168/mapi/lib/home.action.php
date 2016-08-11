<?php

/**
 * 网站首页2.0
 * 1、广告
 * 2、显示单个移动端置顶标记
 * 3、显示公共数据
 *
 */
class home {

    public function index() {
        $root = array();
        $root['response_code'] = 1;

        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        //print_r($GLOBALS['db_conf']); exit;

        $root['kf_phone'] = $GLOBALS['m_config']['kf_phone']; //客服电话
        $root['kf_email'] = $GLOBALS['m_config']['kf_email']; //客服邮箱
        //$pattern = "/<img([^>]*)\/>/i";
        //$replacement = "<img width=300 $1 />";
        //$goods['goods_desc'] = preg_replace($pattern, $replacement, get_abs_img_root($goods['goods_desc']));
        //关于我们(填文章ID)
        $root['about_info'] = intval($GLOBALS['m_config']['about_info']);

        $root['version'] = VERSION; //接口版本号int
        $root['page_size'] = PAGE_SIZE; //默认分页大小
        $root['program_title'] = $GLOBALS['m_config']['program_title'];
        $root['site_domain'] = str_replace("/mapi", "", SITE_DOMAIN . APP_ROOT); //站点域名;
        $root['site_domain'] = str_replace("http://", "", $root['site_domain']); //站点域名;
        $root['site_domain'] = str_replace("https://", "", $root['site_domain']); //站点域名;
        $root['red_bonus'] = intval($GLOBALS['m_config']['red_bonus']); //红包信息
        $root['share_msg'] = $GLOBALS['m_config']['share_msg']; //分享默认信息
        //累计投资金额
        $stats['total_load'] = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load where is_repay= 0 ");
        $stats['total_load_format'] = format_conf_count(number_format($stats['total_load'], 2));
        //成交笔数
        $stats['deal_total_count'] = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "deal where  deal_status >=4 ");
        //累计创造收益
        $stats['total_rate'] = $GLOBALS['db']->getOne("SELECT sum(true_interest_money + impose_money + true_reward_money - true_manage_money - true_manage_interest_money) FROM " . DB_PREFIX . "deal_load_repay where  has_repay = 1 ");
        $stats['total_rate'] += $GLOBALS['db']->getOne("SELECT sum(rebate_money) FROM " . DB_PREFIX . "deal_load where  is_has_loans = 1 "); //加上返利
        $stats['total_rate'] -= $GLOBALS['db']->getOne("SELECT sum(fee_amount) FROM " . DB_PREFIX . "payment_notice WHERE  is_paid =1  "); //减去充值手续费
        $stats['total_rate'] -= $GLOBALS['db']->getOne("SELECT sum(fee) FROM " . DB_PREFIX . "user_carry WHERE status =1  "); //减去提现手续费
        $stats['total_rate'] += $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "referrals WHERE pay_time >0  "); //加上邀请返利
        $stats['total_rate_format'] = format_conf_count(number_format($stats['total_rate'], 2));
        //本息保证金（元）
        $stats['total_bzh'] = $GLOBALS['db']->getOne("SELECT sum(guarantor_real_freezen_amt+real_freezen_amt) FROM " . DB_PREFIX . "deal where deal_status= 4 ");
        $stats['total_bzh_format'] = format_conf_count(number_format($stats['total_bzh'], 2));
        //待收资金（元）
        $stats['total_repay'] = $GLOBALS['db']->getOne("SELECT sum(repay_money) FROM " . DB_PREFIX . "deal_load_repay where has_repay = 1 ");
        $stats['total_repay_format'] = format_conf_count(number_format($stats['total_repay'], 2));
        //待投资金（元）
        $statsU = $GLOBALS['db']->getRow("SELECT sum(money) as total_usermoney ,count(*) total_user FROM " . DB_PREFIX . "user where is_effect = 1 and is_delete=0 ");
        $stats['total_usermoney'] = $statsU['total_usermoney'];
        $stats['total_usermoney_format'] = format_conf_count(number_format($stats['total_usermoney'], 2));
        $stats['total_user'] = $statsU['total_user'];
        $GLOBALS['tmpl']->assign("stats", $stats);

        $root['virtual_money_1'] = strip_tags(num_format(($GLOBALS['db_conf']['VIRTUAL_MONEY_1']) / 10000)); //虚拟的累计成交额;
        $root['virtual_money_2'] = strip_tags(num_format(($GLOBALS['db_conf']['VIRTUAL_MONEY_2']) / 10000)); //虚拟的累计创造收益;
        $root['virtual_money_3'] = strip_tags(num_format(($GLOBALS['db_conf']['VIRTUAL_MONEY_3']) / 10000)); //虚拟的本息保障金;

        $index_list = $GLOBALS['cache']->get("MOBILE_INDEX_ADVS");
        if (true || $index_list === false) {
            $where = " and id!=16 ";
            if ($_REQUEST['channel'] == 'baidu') {
                $activity_parameter = MO("ActivityConf")->getParameterByType(2);
                if ($activity_parameter) {
                    $where = "";
                }
            }
            if ((strpos(getIpAddr(get_client_ip()), "香港") !== false || strpos(getIpAddr(get_client_ip()), "美国") !== false || strpos(getIpAddr(get_client_ip()), "南美") !== false || strpos(getIpAddr(get_client_ip()), "北美") !== false) && $_REQUEST['_m'] == 'ios') {
                $where.=" and id in(13,14) ";
            }
//            $advs = $GLOBALS['db']->getAll(" select * from " . DB_PREFIX . "m_adv where status = 1 $where order by sort desc ");
            $time = time();
            $sql_str = "select id,name,img_url as img,href as data from " . DB_PREFIX . "ad where is_effect = 1 and type=1 and begin_time<'$time' and end_time>'$time' $where order by sort desc ";
            $advs = $GLOBALS['db']->getAll($sql_str);

            $adv_list = array();
            $deal_list = array();
            $condition = "-1";
            $tmpEmail = $_POST['email'] ? $_POST['email'] : $_GET['email'];
            $tmpPwd = $_POST['pwd'] ? $_POST['pwd'] : $_GET['pwd'];
            foreach ($advs as $k => $v) {
                if (strpos($v['data'], "http") === false) {
                    $v['data'] = SITE_DOMAIN . $v['data'];
                }
                if ($v['data'] && $tmpEmail && $tmpPwd) {
                    $v['data'].= '&email=' . $tmpEmail . '&pwd=' . $tmpPwd;
                }
                if ($v['img'] != '') {
                    $v['img'] = get_abs_img_root(get_spec_image($v['img'], 640, 240, 1));
                }
                if ($_REQUEST['_m']) {
                    $v['data'].="&_m=" . $_REQUEST['_m'];
                }
                if ($v['data']) {
                    $v['type'] = '2';
                } else {
                    $v['type'] = '1';
                }
                $v['open_url_type'] = '0';
                $adv_list[] = $v;
            }

            //$condition = " id in (".$condition.")";
            //publish_wait 0:已审核 1:等待审核;
            //deal_status 0待等材料，1进行中，2满标，3流标，4还款中，5已还清
            //is_effect 1有效 0无效
            //is_delete 1删除 0未删除
            //is_advance 是否预告
            //is_recommend 是否推荐
            //rate 年化利率
            //repay_time 借款期限
//			$condition = " (start_time + enddate*24*3600 - " . TIME_UTC . ") >0 and is_moving = 1 and is_recommend = 1 and publish_wait = 0 and deal_status =1";
            $condition = " (start_time + enddate*24*3600 - " . TIME_UTC . ") >0 and publish_wait = 0 and deal_status =1";

            require APP_ROOT_PATH . 'app/Lib/deal.php';
            $limit = "0,1";
            $orderby = " is_moving desc,is_recommend desc,rate desc,repay_time asc ";

            $result = get_deal_list_mobile($limit, 0, $condition, $orderby);

            if (count($result['list']) == 0) {
                $condition = " deal_status=2 and publish_wait = 0 ";
                $result = get_deal_list_mobile($limit, 0, $condition, $orderby);
            }

            $rdata = array();
            $time = TIME_UTC;
            foreach ($result['list'] as $value) {
                if (($value["start_time"] - TIME_UTC) > 0) {
                    $value["bfinish_time"] = 0;
                } else {
                    $value["bfinish_time"] = 1;
                }

                if ($value['deal_status'] == 4) {
                    $value['name'].='(还款中)';
                    $value['progress_point'] = '100.00';
                } else if ($value['deal_status'] == 2) {
                    $value['name'].='(满标)';
                    $value['progress_point'] = '100.00';
                }

                //开始时间
                $start_time = $value['start_time'];
                //筹标期限
                $enddate = $value['enddate'];
                //标的有效时间 是否过期
                array_push($rdata, $value);
            }


            $index_list['adv_list'] = $adv_list;
            $index_list['deal_list'] = $rdata;
            $GLOBALS['cache']->set("MOBILE_INDEX_ADVS", $index_list);
        }

        $root['index_list'] = $index_list;
        $root['deal_cate_list'] = getDealCateArray(); //分类

        /* if(strim($GLOBALS['m_config']['sina_app_key'])!=""&&strim($GLOBALS['m_config']['sina_app_secret'])!="")
          {
          $root['api_sina'] = 1;
          $root['sina_app_key'] = $GLOBALS['m_config']['sina_app_key'];
          $root['sina_app_secret'] = $GLOBALS['m_config']['sina_app_secret'];
          $root['sina_bind_url'] = $GLOBALS['m_config']['sina_bind_url'];
          }
          if(strim($GLOBALS['m_config']['tencent_app_key'])!=""&&strim($GLOBALS['m_config']['tencent_app_secret'])!="")
          {
          $root['api_tencent'] = 1;
          $root['tencent_app_key'] = $GLOBALS['m_config']['tencent_app_key'];
          $root['tencent_app_secret'] = $GLOBALS['m_config']['tencent_app_secret'];
          $root['tencent_bind_url'] = $GLOBALS['m_config']['tencent_bind_url'];
          } */
        //友盟推送
        if ($email = $_REQUEST['email'] && $user_id = MO("User")->getUserInfoByEmail($email, 'id')['id']) {
            $device_data['user_id'] = $user_id;
            $device_data['device_token'] = trim($_REQUEST['device_token']);
            $device_data['device_type'] = trim($_REQUEST['_m']);
            MO("devicePush")->add($device_data);
        }
        output($root);
    }

}

function getDealCateArray() {
    //$land_list = FanweService::instance()->cache->loadCache("land_list");

    $sql = "select id, pid, name, icon from " . DB_PREFIX . "deal_cate where pid = 0 and is_effect = 1 and is_delete = 0 order by sort desc ";
    //echo $sql; exit;
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

?>
