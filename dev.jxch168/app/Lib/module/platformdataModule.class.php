<?php

class platformdataModule extends SiteBaseModule {

    function index() {
	$GLOBALS['tmpl']->assign("page_title", "金融投资理财平台数据分析尽在金享财行官网");
	$GLOBALS['tmpl']->assign("page_keyword", "平台数据总览,金享财行理财数据查看,产品类型分布,投资期限分布,投资人年龄分布,投资人性别分布  金融理财,理财,投资理财,理财平台,金享财行");
	$GLOBALS['tmpl']->assign("page_description", "金享财行投资理财平台按产品类型，投资理财期限，理财年龄分布，理财性别进行了完成的数据分析，了解金融投资理财平台数据尽在金享财行官网。");
	//平台投资总数 基数330260000
	$sum_money = $GLOBALS['db']->getRow("SELECT sum(money)as money FROM " . DB_PREFIX . "deal_load");
	$sum_money = intval($sum_money['money']);
	$sum_money_all = 330260000 + $sum_money * 1.5;
	//平台投资总数转换为以亿为单位的
	$sum_money_all = (int)($sum_money_all/10000000)/10;
	$GLOBALS['tmpl']->assign("sum_money_all", $sum_money_all);

	//平台累计收益 基数48160234
	$sum_interest = $GLOBALS['db']->getRow("SELECT sum(interest_money) as interest_money  FROM " . DB_PREFIX . "deal_load_repay ");
	$sum_interest = intval($sum_interest['interest_money']);
	$sum_interest_all =  (int) (78160234 + $sum_interest * 1.5) ;
	//平台累计收益转换为以千万为单位的
	$sum_interest_all = (int)($sum_interest_all/1000000)/10;
	$GLOBALS['tmpl']->assign("sum_interest_all", $sum_interest_all);

	$GLOBALS['tmpl']->display("page/platformdata.html");
    }

}

?>