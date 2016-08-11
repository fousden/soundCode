<?php

class noviceModule  extends SiteBaseModule {
     //Novice新手模块  guidelines指引
     function guidelines(){
        $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['NOVICE_GUIDELINES_TITLE'];
        //$GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['GOLD_SAFE_KEYWORD'];
        //$GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['GOLD_SAFE_DESCRIPTION'];
        $page_title = $GLOBALS['lang']['NOVICE_GUIDELINES_TITLE'];
        $GLOBALS['tmpl']->assign("page_title",$page_title);

         //平台用户总数 基数22666
        $user_count = $GLOBALS['db']->getRow("SELECT count(id) as count FROM " . DB_PREFIX . "user");
        $u_count = intval($user_count['count']);
        $user_count_all = 22666+$u_count*5;
        $user_count_all = number_format($user_count_all);
        $GLOBALS['tmpl']->assign("user_count_all",$user_count_all);

        //平台投资总数 基数330260000
        $sum_money = $GLOBALS['db']->getRow("SELECT sum(money)as money FROM " . DB_PREFIX . "deal_load");
        $sum_money = intval($sum_money['money']);
        $sum_money_all = 330260000+$sum_money*1.5;
        $sum_money_all = number_format($sum_money_all);
        $GLOBALS['tmpl']->assign("sum_money_all",$sum_money_all);

        //平台累计收益 基数48160234
        $sum_interest = $GLOBALS['db']->getRow("SELECT sum(interest_money) as interest_money  FROM " . DB_PREFIX . "deal_load_repay ");
        $sum_interest = intval($sum_interest['interest_money']);
        $sum_interest_all =  (int) (78160234 + $sum_interest * 1.5) ;
        $sum_interest_all = number_format($sum_interest_all);
        $GLOBALS['tmpl']->assign("sum_interest_all",$sum_interest_all);

        $GLOBALS['tmpl']->display("page/guidelines.html");
     }

}