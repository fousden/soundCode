<?php

return array(
    'title' => "零售供应链金融活动",
    'param' => array(
        'compute_type' => "total", //total表示累计，once表示单次
        'repay_time' => array(">=", "60"), //投资标的的期限
        'create_time' => array(">=", "'2016-03-22'"),
        'where' => " and (d.name like '%CBJR%') ",
    ),
    'prize_conf' => array(
        array("prize_id" => 0, "start" => 0, "end" => 3, "conf_id" => 3, "prize_type" => 2), //10元抵用券
        array("prize_id" => 4, "start" => 3, "end" => 5, "conf_id" => 2, "prize_type" => 2), //35元抵用券
        array("prize_id" => 2, "start" => 5, "end" => 10, "conf_id" => 1, "prize_type" => 2), //100元抵用券
        array("prize_id" => 3, "start" => 10, "end" => 20, "conf_id" => 8, "prize_type" => 4), //东芝移动硬盘2T 2.5寸
        array("prize_id" => 7, "start" => 20, "end" => 30, "conf_id" => 19, "prize_type" => 4), //飞利浦智能扫地机器人
        array("prize_id" => 1, "start" => 30, "end" => 50, "conf_id" => 14, "prize_type" => 4), //松下烤箱微波炉
        array("prize_id" => 6, "start" => 50, "end" => 100, "conf_id" => 21, "prize_type" => 4), //华为mate8 32G移动版
        array("prize_id" => 5, "start" => 100, "end" => 9999, "conf_id" => 3, "prize_type" => 4), //iPad Air 2 64G  
    ),
);
