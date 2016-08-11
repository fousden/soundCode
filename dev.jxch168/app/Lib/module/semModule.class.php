<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of semModule
 *
 * @author Administrator
 */
class semModule
{
    public function index(){

        //平台累计收益 基数48160234
        $sum_interest     = $GLOBALS['db']->getRow("SELECT sum(interest_money) as interest_money  FROM " . DB_PREFIX . "deal_load_repay WHERE has_repay = 1");
        $sum_interest     = intval($sum_interest['interest_money']);
        $sum_interest_all = 48160234 + $sum_interest * 1.5;
        //平台投资总数 基数330260000
        $sum_money     = $GLOBALS['db']->getRow("SELECT sum(money)as money FROM " . DB_PREFIX . "deal_load");
        $sum_money     = intval($sum_money['money']);
        $sum_money_all = 330260000 + $sum_money * 1.5;
        //分配投资总数数据到页面
        $all_money = (string)$sum_money_all;

        $all = explode('.',num_format($all_money / 100000000));


        $GLOBALS['tmpl']->assign("all", $all);

        //分配收益总数数据到页面
        $all_interest = explode('.',num_format($sum_interest_all / 10000000));

        $GLOBALS['tmpl']->assign("all_interest", $all_interest);

        $count = strlen($sum_interest_all);
        $num_array = array_reverse(explode('.',substr(chunk_split($sum_interest_all,1,'.'),0,2*$count-1)));

        for($i=12;$i>$count;$i--){
            array_push($num_array,'0');
        }

        $sum_interest_all = number_format($sum_interest_all);

        $GLOBALS['tmpl']->assign("num_array", $num_array);
        $GLOBALS['tmpl']->assign('sem',true);
        $GLOBALS['tmpl']->display("page/sem.html");


    }




}
