<?php

/*
 * 功能：金享财行投资提成奖励计算
 * 时间：2015年12月07日
 * author：chushangming
 */
require_once 'init.php';

//提成年化收益率 单位为百分比（%）
$reward_rate = 0.5;

//投资提成脚本运行对象的时间范围 昨天数据
$yesterday_date = date("Y-m-d",strtotime('-1 day',strtotime(date('Y-m-d'))));
$start_time = strtotime($yesterday_date." 00:00:00");
$end_time = strtotime($yesterday_date." 23:59:59");

//提成奖励活动配置信息 规则 今天的数据明天处理
$activityConf = MO("ActivityConf")->getInfoByType(4,strtotime('-1 day',time()));
//如果在活动周期内
if($activityConf){
    $endUid = 0;
    while (1) {
        //查询所有用户信息
        $deal_load_list = $GLOBALS['db']->getAll("select dl.id,dl.money,dl.deal_id,dl.create_time,d.jiexi_time,d.repay_time,u.pid from " . DB_PREFIX . "deal_load dl LEFT JOIN ".DB_PREFIX."deal d on dl.deal_id = d.id LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id where d.is_effect = 1 AND d.is_delete = 0 AND u.is_effect = 1 AND u.is_delete = 0 AND u.pid > 0 AND dl.is_auto = 0 AND dl.contract_no != '' AND dl.create_time >=".$start_time." AND dl.create_time <=".$end_time." AND dl.id > " . $endUid . "  ORDER BY dl.create_time ASC limit 100 ");

        foreach ($deal_load_list as $key => $deal_load) {
            //运行昨天的投资数据
            if($deal_load["create_time"] >= $activityConf["start_time"] && $deal_load["create_time"] <= $activityConf["end_time"]){
                //推荐人信息
                $user_reward_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_reward where load_id = '".$deal_load["id"]."' AND is_effect = 1");
                if(!$user_reward_info){
                    //准备提成数据
                    $user_reward["user_id"] = $deal_load["pid"];
                    $user_reward["deal_id"] = $deal_load["deal_id"];
                    $user_reward["load_id"] = $deal_load["id"];
                    $user_reward["reward_name"] = "投资提成奖励";
                    $user_reward["money"] = num_format(($reward_rate / 100 / 360) * $deal_load["money"] * $deal_load["repay_time"]);
                    $user_reward["reward_type"] = 1;//投资提成奖励
                    $user_reward["reward_rate"] = $reward_rate;//投资提成利率
                    $user_reward["status"] = 0;//提成奖励发放 未发放已提现
                    $user_reward["verify_status"] = 0;//提成奖励审核 未审核
                    $user_reward["generation_time"] = time();
                    $user_reward["release_date"] = strtotime($deal_load["jiexi_time"]);
                    $user_reward["is_effect"] = 1;//默认有效
                    $user_reward["remark"] = "投资提成奖励";
                    if($user_reward["money"]){
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_reward", $user_reward, "INSERT");
                    }
                }
            }
            $endUid = $deal_load['id'];
        }
        if (count($deal_load_list) < 100) {
            exit;
        }
    }
}


