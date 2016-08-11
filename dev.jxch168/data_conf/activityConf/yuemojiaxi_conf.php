<?php

return array(
    'title' => "金享票号月末加息活动",
    'param' => array(
        'compute_type' => "once", //total表示累计，once表示单次
//        'repay_time' => array(">=", "180"), //投资标的的期限
        'create_time' => array(">=", "'2016-04-20'"),
//        'agency_id' => array("=", "'26855'"),
        'where' => " and d.name like '%YMJX%'",
        'deal_load_deduct_all'=>'once',//扣除所有标的记录，默认是
    ),
    /**
     * 
     */
    'prize_conf' => array(
        array("prize_id" => 0, "start" => 0, "end" => 1, "conf_id" => 0, "prize_type" => 6), //谢谢参与
        array("prize_id" => 1, "start" => 1, "end" => 10, "conf_id" => 4, "prize_type" => 2), //100元抵用券
        array("prize_id" => 2, "start" => 10, "end" => 20, "conf_id" => 18, "prize_type" => 4), //韩国BD-YS1801养生壶
        array("prize_id" => 3, "start" => 20, "end" => 30, "conf_id" => 9, "prize_type" => 4), //九阳JYZ-V1立式榨汁机
        array("prize_id" => 4, "start" => 20, "end" => 30, "conf_id" => 15, "prize_type" => 4), //美的MF-TN20A空气炸锅
        array("prize_id" => 5, "start" => 20, "end" => 30, "conf_id" => 20, "prize_type" => 4), //飞利浦空气净化器
        array("prize_id" => 6, "start" => 30, "end" => 50, "conf_id" => 19, "prize_type" => 4), //飞利浦智能扫地机器人
        array("prize_id" => 7, "start" => 50, "end" => 100, "conf_id" => 2, "prize_type" => 4), //iPad mini 4 16G
        array("prize_id" => 8, "start" => 100, "end" => 9999, "conf_id" => 6, "prize_type" => 4), //iPhone 6s plus 64G
    ),
);
