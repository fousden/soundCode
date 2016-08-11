<?php

return array(
    'title' => "春满金享好运来活动",
    'param' => array(
        'compute_type' => "total", //total表示累计，once表示单次
        'repay_time' => array(">=", "90"), //投资标的的期限
        'create_time' => array(">=", "'2016-03-01'"),
    ),
    'prize_conf' => array(
        array('prize_id' => 1, "start" => 0, "end" => 10, "conf_id" => 1, "prize_type" => 2), //100元抵用券
        array('prize_id' => 2, "start" => 10, "end" => 20, "conf_id" => 1, "prize_type" => 8), //东芝移动硬盘2T 2.5寸
        array('prize_id' => 3, "start" => 20, "end" => 30, "conf_id" => 1, "prize_type" => 19), //飞利浦智能扫地机器人
        array('prize_id' => 4, "start" => 30, "end" => 50, "conf_id" => 1, "prize_type" => 14), //松下烤箱微波炉
        array('prize_id' => 5, "start" => 50, "end" => 100, "conf_id" => 1, "prize_type" => 21), //华为mate8 32G移动版
        array('prize_id' => 6, "start" => 100, "end" => 9999, "conf_id" => 1, "prize_type" => 3), //iPad Air 2 64G
    ),
);
