<?php

return array(
    'title' => "金享撒钱活动",
    'param' => array(
        'compute_type' => "total", //total表示累计，once表示单次
        'repay_time' => array(">=", "180"), //投资标的的期限
        'create_time' => array(">=", "'2016-03-01'"),
        'agency_id' => array("=", "'26855'"),
        'where' => " and d.name like '%海德商厦%'",
    ),
    'prize_conf' => array(
        array("prize_id" => 1, "start" => 0, "end" => 0.25, "conf_id" => 1, "prize_type" => 2), //100元抵用券
        array("prize_id" => 2, "start" => 0.25, "end" => 1, "conf_id" => 12, "prize_type" => 4), //小米手环
        array("prize_id" => 3, "start" => 1, "end" => 1.5, "conf_id" => 11, "prize_type" => 4), //小米体重秤
        array("prize_id" => 4, "start" => 1.5, "end" => 2.5, "conf_id" => 18, "prize_type" => 4), //韩国BD-YS1801养生壶
        array("prize_id" => 5, "start" => 2.5, "end" => 3, "conf_id" => 9, "prize_type" => 4), //九阳JYZ-V1立式榨汁机
        array("prize_id" => 6, "start" => 3, "end" => 5, "conf_id" => 15, "prize_type" => 4), //美的MF-TN20A空气炸锅
        array("prize_id" => 7, "start" => 5, "end" => 10, "conf_id" => 10, "prize_type" => 4), //全自动足疗机DE-F18
        array("prize_id" => 8, "start" => 10, "end" => 13, "conf_id" => 1, "prize_type" => 4), //Apple Watch sport 38mm
        array("prize_id" => 9, "start" => 13, "end" => 15, "conf_id" => 2, "prize_type" => 4), //iPad mini 4 16G
        array("prize_id" => 10, "start" => 15, "end" => 25, "conf_id" => 3, "prize_type" => 4), //iPad Air 2 64G
        array("prize_id" => 11, "start" => 25, "end" => 9999, "conf_id" => 6, "prize_type" => 4), //iPhone 6s plus 64G
    ),
);
