<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

define(MODULE_NAME, "index");
require APP_ROOT_PATH . 'app/Lib/deal.php';

class indexModule extends SiteBaseModule
{

    public function index()
    {
//        $qr_code_url = "http://www.3eyz1688.com";
//        $logo_img_url = APP_ROOT_PATH.'/public/images/1458181460.jpg';
//        $my_grcode = gen_qrcode($qr_code_url,$logo_img_url, 10);
//        
//        echo $my_grcode;die;
//        echo strtotime("2015-12-31 21:44:24");exit;
//        $msg_item['dest'] = "13122905536";
//        $msg_item['content'] = "16554";
//        send_sms_email($msg_item, 5, "EN");
        //合同信息 生成PDF示例
//        $pdf_data["logo_url"] = "";
//        $pdf_data["pdf_title"] = "www.jxch168.com";
//        $pdf_data["title_detail"] = "致力于成为您身边的理财专家";
//        $pdf_data["template_pdf_url"] = APP_ROOT_PATH."public/tcpdf/contract_template/risk.html";
//        $pdf_data["pdf_name"] = "risk_information";
//        $pdf_data["data"] = array();
//        //PHP生成加密PDF
//        generate_pdf($pdf_data);

        //新手引导页弹窗的cookie，时效为一周
        if (es_cookie::get("guide") != "true") {
            $GLOBALS['tmpl']->assign('view', 'true');
            es_cookie::set("guide", "true", 24 * 3600 * 7);
        }

        $GLOBALS['tmpl']->caching        = false;
        $GLOBALS['tmpl']->cache_lifetime = 600;  //首页缓存10分钟
        $cache_id                        = md5(MODULE_NAME . ACTION_NAME);
        if (!$GLOBALS['tmpl']->is_cached("page/index.html", $cache_id)) {

            make_deal_cate_js();
            make_delivery_region_js();

            change_deal_status();

            //借款预告列表
            $advance_deal_list = get_deal_list(5, 0, "publish_wait =0 AND deal_status =1 AND is_advance=1 AND start_time >" . TIME_UTC, " deal_status ASC, is_recommend DESC,sort DESC,id DESC");
            $GLOBALS['tmpl']->assign("advance_deal_list", $advance_deal_list['list']);

            //金享新手标******************************************************************************************************************************************************************************
            $time         = TIME_UTC;
            $deal_xinshou = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "deal WHERE cate_id = 5 AND deal_status = 1 AND is_effect=1 AND  is_delete=0 AND is_hidden = 0 AND (start_time + enddate*24*3600 - " . $time . ") > 0 ORDER BY rate DESC LIMIT 1");
            if (!empty($deal_xinshou)) {
                //标的担保方
                $xs_db_id                       = $deal_xinshou[0]['agency_id'];
                $xs_db                          = $GLOBALS['db']->getRow("SELECT id,is_db_img,user_name FROM " . DB_PREFIX . "user WHERE id = " . $xs_db_id); //var_dump($xs_db);exit;
                $GLOBALS['tmpl']->assign("xs_db", $xs_db);
                //标的百分比
                $deal_xinshou_deal              = $deal_xinshou[0]['id'];
                $xs_tz_sum                      = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id =" . $deal_xinshou_deal);
                $precent                        = $xs_tz_sum / $deal_xinshou[0]['borrow_amount'] * 100;
                $deal_xinshou[0]['precent']     = round($precent, 2);
                //标的倒计时
                //开始时间
                $start_time                     = $deal_xinshou[0]['start_time'];
                //筹标期限
                $enddate                        = $deal_xinshou[0]['enddate'];
                //标的有效时间 是否过期
                $remain_time                    = intval($start_time + $enddate * 24 * 3600 - $time);
                $deal_xinshou[0]['remain_time'] = $remain_time;
                $deal_xinshou[0]['need_money']  = $deal_xinshou[0]['borrow_amount'] - $deal_xinshou[0]['load_money'];
                $GLOBALS['tmpl']->assign("deal_xinshou", $deal_xinshou[0]);
            }


            //金享票号******************************************************************************************************************************************************************************
            $deal_piaohao = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "deal WHERE cate_id = 1 AND is_effect=1 AND  is_delete=0 AND is_hidden = 0 AND start_time+enddate*24*3600-$time>0 AND deal_status=1 ORDER BY rate DESC,last_repay_time asc LIMIT 1");
            if (!empty($deal_piaohao)) {
                //标的担保方
                $ph_db_id                       = $deal_piaohao[0]['agency_id'];
                $ph_db                          = $GLOBALS['db']->getRow("SELECT id,is_db_img,user_name FROM " . DB_PREFIX . "user WHERE id = " . $ph_db_id);
                $GLOBALS['tmpl']->assign("ph_db", $ph_db);
                //标的百分比
                $deal_piaohao_deal              = $deal_piaohao[0]['id'];
                $ph_tz_sum                      = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id =" . $deal_piaohao_deal);
                $precent                        = $ph_tz_sum / $deal_piaohao[0]['borrow_amount'] * 100;
                $deal_piaohao[0]['precent']     = round($precent, 2);
                //标的倒计时
                //开始时间
                $start_time                     = $deal_piaohao[0]['start_time'];
                //筹标期限
                $enddate                        = $deal_piaohao[0]['enddate'];
                //标的有效时间 是否过期
                $remain_time                    = intval($start_time + $enddate * 24 * 3600 - $time);
                $deal_piaohao[0]['remain_time'] = $remain_time;
                $deal_piaohao[0]['need_money']  = $deal_piaohao[0]['borrow_amount'] - $deal_piaohao[0]['load_money'];
                $GLOBALS['tmpl']->assign("deal_piaohao", $deal_piaohao[0]);
            }

//金享保理******************************************************************************************************************************************************************************
            $deal_baoli = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "deal WHERE cate_id = 2 AND is_effect=1 AND  is_delete=0 AND is_hidden = 0 AND deal_status=1 AND start_time+enddate*24*3600-$time>0 ORDER BY rate DESC,last_repay_time asc LIMIT 1");
            if (!empty($deal_baoli)) {
                //标的担保方
                $bl_db_id                     = $deal_baoli[0]['agency_id'];
                $bl_db                        = $GLOBALS['db']->getRow("SELECT id,is_db_img,user_name FROM " . DB_PREFIX . "user WHERE id = " . $bl_db_id); //var_dump($bl_db);exit;
                $GLOBALS['tmpl']->assign("bl_db", $bl_db);
                //标的百分比
                $deal_baoli_deal              = $deal_baoli[0]['id'];
                $bl_tz_sum                    = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id =" . $deal_baoli_deal);
                $precent                      = $bl_tz_sum / $deal_baoli[0]['borrow_amount'] * 100;
                $deal_baoli[0]['precent']     = round($precent, 2);
                //标的倒计时
                //开始时间
                $start_time                   = $deal_baoli[0]['start_time'];
                //筹标期限
                $enddate                      = $deal_baoli[0]['enddate'];
                //标的有效时间 是否过期
                $remain_time                  = intval($start_time + $enddate * 24 * 3600 - $time);
                $deal_baoli[0]['remain_time'] = $remain_time;
                $deal_baoli[0]['need_money']  = $deal_baoli[0]['borrow_amount'] - $deal_baoli[0]['load_money'];
                $GLOBALS['tmpl']->assign("deal_baoli", $deal_baoli[0]);
            }
            //金享租赁******************************************************************************************************************************************************************************
            $deal_zulin = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "deal WHERE cate_id = 3 AND is_effect=1 AND start_time+enddate*24*3600-$time>0 AND  is_delete=0 AND is_hidden = 0 AND deal_status=1 ORDER BY rate DESC,last_repay_time asc LIMIT 1");
            if (!empty($deal_zulin)) {
                //标的担保方
                $zl_db_id                     = $deal_zulin[0]['agency_id'];
                $zl_db                        = $GLOBALS['db']->getRow("SELECT id,is_db_img,user_name FROM " . DB_PREFIX . "user WHERE id = " . $zl_db_id);
                $GLOBALS['tmpl']->assign("zl_db", $zl_db);
                //标的百分比
                $deal_zulin_deal              = $deal_zulin[0]['id'];
                $zl_tz_sum                    = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id =" . $deal_zulin_deal);
                $precent                      = $zl_tz_sum / $deal_zulin[0]['borrow_amount'] * 100;
                $deal_zulin[0]['precent']     = round($precent, 2); //var_dump($deal_zulin[0]['precent']);exit;
                //标的倒计时
                //开始时间
                $start_time                   = $deal_zulin[0]['start_time'];
                //筹标期限
                $enddate                      = $deal_zulin[0]['enddate'];
                //标的有效时间 是否过期
                $remain_time                  = intval($start_time + $enddate * 24 * 3600 - $time);
                $deal_zulin[0]['remain_time'] = $remain_time;
                $deal_zulin[0]['need_money']  = $deal_zulin[0]['borrow_amount'] - $deal_zulin[0]['load_money'];
                $GLOBALS['tmpl']->assign("deal_zulin", $deal_zulin[0]);
            }

            //金享银行******************************************************************************************************************************************************************************
            $sql_str      = "SELECT * FROM " . DB_PREFIX . "deal WHERE cate_id = 4 AND is_effect=1 AND  is_delete=0 AND is_hidden = 0 AND (start_time+enddate*24*3600-$time)>0 AND deal_status=1 ORDER BY rate DESC,last_repay_time asc LIMIT 1";
            $deal_yinhang = $GLOBALS['db']->getAll($sql_str);
            if (!empty($deal_yinhang)) {
                //标的担保方
                $yh_db_id                       = $deal_yinhang[0]['agency_id']; //var_dump($deal_yinhang);exit;
                $yh_db                          = $GLOBALS['db']->getRow("SELECT id,is_db_img,user_name FROM " . DB_PREFIX . "user WHERE id = " . $yh_db_id);
                $GLOBALS['tmpl']->assign("yh_db", $yh_db);
                //标的百分比
                $deal_yinhang_deal              = $deal_yinhang[0]['id'];
                $yh_tz_sum                      = $GLOBALS['db']->getOne("SELECT sum(money) FROM " . DB_PREFIX . "deal_load WHERE deal_id =" . $deal_yinhang_deal);
                $precent                        = $yh_tz_sum / $deal_yinhang[0]['borrow_amount'] * 100;
                $deal_yinhang[0]['precent']     = round($precent, 2);
                //标的倒计时
                $time                           = TIME_UTC;
                //开始时间
                $start_time                     = $deal_yinhang[0]['start_time'];
                //筹标期限
                $enddate                        = $deal_yinhang[0]['enddate'];
                //标的有效时间 是否过期
                $remain_time                    = intval($start_time + $enddate * 24 * 3600 - $time);
                $deal_yinhang[0]['remain_time'] = $remain_time;
                $deal_yinhang[0]['need_money']  = $deal_yinhang[0]['borrow_amount'] - $deal_yinhang[0]['load_money'];
                $GLOBALS['tmpl']->assign("deal_yinhang", $deal_yinhang[0]);
            }


            // 输出首页的公告
            $now_time=time();
            $notice_act_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."notice where is_effect=1 and is_delete=0 and ".$now_time."<end_time and ".$now_time.">begin_time");

            $GLOBALS['tmpl']->assign("notice_act_list",$notice_act_list);
            //输出公司动态
            $art_id    = $GLOBALS['db']->getOne("SELECT id FROM " . DB_PREFIX . "article_cate where title='公司动态'");
            //输出广告信息在有效期以内
//            $adv_info  = $GLOBALS['db']->getAll("SELECT name,code FROM " . DB_PREFIX . "adv WHERE is_effect=1 ORDER BY sort desc");
            $time = time(); // 当前时间
            $adv_info  = $GLOBALS['db']->getAll("SELECT name,img_url,href FROM " . DB_PREFIX . "ad WHERE is_effect=1 and type=0 and begin_time<={$time} and end_time>={$time} ORDER BY sort desc");
            $adv_infos = array();
            foreach ($adv_info as $key => $val) {
                $adv_infos[$key + 1] = $val;
            }
            $GLOBALS['tmpl']->assign("adv_info", $adv_infos);
            if ($art_id > 0) {
                $compnay_active_list = get_article_list(5, $art_id);
                $GLOBALS['tmpl']->assign("art_id", $art_id);
                $GLOBALS['tmpl']->assign("compnay_active_list", $compnay_active_list['list']);
            }

            //平台用户总数 基数22666
            $user_count     = $GLOBALS['db']->getRow("SELECT count(id) as count FROM " . DB_PREFIX . "user");
            $u_count        = intval($user_count['count']);
            /**
             * 22666 + $u_count * 5  20151015 前为
             * 122666 + $u_count * 5;  20151015
             */
            $user_count_all = 122666 + $u_count * 5;
            $user_count_all = (float)($user_count_all);
            $GLOBALS['tmpl']->assign("user_count_all", $user_count_all);
            // 标的期数
            $deal_count = $GLOBALS['db']->getRow("SELECT count(id) as count FROM ".DB_PREFIX."deal");
            $d_count = intval($deal_count['count']);
            $deal_count_all = (float)$d_count; // 暂时用真实的期数
            $GLOBALS['tmpl']->assign("deal_count_all", $deal_count_all);

            //平台投资总数 基数330260000
            $sum_money     = $GLOBALS['db']->getRow("SELECT sum(money)as money FROM " . DB_PREFIX . "deal_load");
            $sum_money     = intval($sum_money['money']);
            // $sum_money_all = 330260000 + $sum_money * 1.5;
            $sum_money_all = (int) (330260000 + $sum_money * 1.5) / 10000;
            //$sum_money_all = (string)number_format($sum_money_all);
            // var_dump($sum_money_all);exit;
            $GLOBALS['tmpl']->assign("sum_money_all", $sum_money_all);
            //平台累计收益 基数48160234
            $sum_interest     = $GLOBALS['db']->getRow("SELECT sum(pure_interests) as interest_money  FROM " . DB_PREFIX . "deal_load where is_has_loans = 1");
            $sum_interest     = intval($sum_interest['interest_money']);
            // $sum_interest_all = 48160234 + $sum_interest * 1.5;
            $sum_interest_all = (int) (78160234 + $sum_interest * 1.5) / 10000;
            //$sum_interest_all = number_format($sum_interest_all);
            $GLOBALS['tmpl']->assign("sum_interest_all", $sum_interest_all);

            //输出公告1(理财资讯)
            $notice_list = get_notice_type(3, 21);
            if (!empty($notice_list)) {
                foreach ($notice_list as &$v) {
                    $v['content'] = trimall(preg_replace('/&[a-zA-Z]*;/', '', $v['content']));
                    $v['content'] = trimall(mb_substr(strip_tags($v['content']), 0, 36, 'UTF-8') . '...');
                }
                $GLOBALS['tmpl']->assign("notice_list", $notice_list);
            }
            //输出公告2(最新公告)
            $notice_list_a = get_notice_type(3, 22);
            if (!empty($notice_list_a)) {
                foreach ($notice_list_a as &$v) {
                    $v['content'] = trimall(preg_replace('/&[a-zA-Z]*;/', '', $v['content']));
                    $v['content'] = trimall(mb_substr(strip_tags($v['content']), 0, 36, 'UTF-8') . '...');
                }
                $GLOBALS['tmpl']->assign("notice_list_a", $notice_list_a);
            }

            //输出公告3(媒体新闻)
            $notice_list_b = get_notice_type(3, 23);
            if (!empty($notice_list_b)) {
                foreach ($notice_list_b as &$v) {
                    $v['content'] = trimall(preg_replace('/&[a-zA-Z]*;/', '', $v['content']));
                    $v['content'] = trimall(mb_substr(strip_tags($v['content'], '$nbsp;'), 0, 36, 'UTF-8') . '...');
                }
                $GLOBALS['tmpl']->assign("notice_list_b", $notice_list_b);
            }

            $GLOBALS['tmpl']->assign("show_site_titile", 1);
        }

        // 将最新的图片输出到前台
        $GLOBALS['tmpl']->assign("zixun_icon",$notice_list['0']['icon']); // 图片地址
        $GLOBALS['tmpl']->assign("zixun_url",$notice_list['0']['url']); // 链接地址
        $GLOBALS['tmpl']->assign("huodong_icon",$notice_list_a['0']['icon']);// 图片地址
        $GLOBALS['tmpl']->assign("huodong_url",$notice_list_a['0']['url']);// 链接地址
        $GLOBALS['tmpl']->assign("gonggao_icon",$notice_list_b['0']['icon']);// 图片地址
        $GLOBALS['tmpl']->assign("gonggao_url",$notice_list_b['0']['url']);//链接地址

        setSearchchannelCookie();
        if (empty($_REQUEST)) {
            $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE']       = $GLOBALS['lang']['INDEX_TITLE'];
            $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD']     = $GLOBALS['lang']['INDEX_KEYWORDS'];
            $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['INDEX_DESCRIPTION'];
            $page_title                                             = $GLOBALS['lang']['INDEX_TITLE'];
        }

        //首页友情链接
        $friend_link_sql     = $GLOBALS['db']->getAll("select name,url,sort from " . DB_PREFIX . "link where is_effect=1 order by sort");
        $friend_link_count   = count($friend_link_sql);
        $friend_link_content = array();

        if ($friend_link_sql) {
            for ($i = 0; $i < 14; $i++) {
                $temp = $friend_link_sql[$i];
                array_push($friend_link_content, $temp);
            }
        }
        $friend_link_more = array();

        if ($friend_link_sql) {
            for ($i = 14; $i < $friend_link_count; $i++) {
                $temp = $friend_link_sql[$i];
                array_push($friend_link_more, $temp);
            }
        }

          //理财资讯，最新活动，官方公告从数据库中取得
        $sql_21 = $GLOBALS['db']->getOne("select ac.title from " . DB_PREFIX . "article_cate as ac where ac.id = 21");
        $sql_22 = $GLOBALS['db']->getOne("select ac.title from " . DB_PREFIX . "article_cate as ac where ac.id = 22");
        $sql_23 = $GLOBALS['db']->getOne("select ac.title from " . DB_PREFIX . "article_cate as ac where ac.id = 23");
        $GLOBALS['tmpl']->assign("sql_21",$sql_21);
        $GLOBALS['tmpl']->assign("sql_22",$sql_22);
        $GLOBALS['tmpl']->assign("sql_23",$sql_23);
        $GLOBALS['tmpl']->assign("page_title", $page_title);

        $GLOBALS['tmpl']->assign('friend_link_sql', $friend_link_sql);
        $GLOBALS['tmpl']->assign('friend_link_content', $friend_link_content);
        $GLOBALS['tmpl']->assign('friend_link_more', $friend_link_more);
        $GLOBALS['tmpl']->assign('friend_link_count', $friend_link_count);
        $GLOBALS['tmpl']->display("page/index.html", $cache_id);
    }

    public function number($k)
    {
        $arr    = explode(".", $k);
        $a      = substr($arr[1], 0, 2);
        $number = empty($a) ? '00' : $a;
        $ok     = $arr[0] . '.' . $number;
        return $ok;
    }

}

?>