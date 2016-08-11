<?php

/*
 * 功能：金享财行多级返佣投资提成奖励计算
 * 时间：2016年04月05日
 * author：chushangming
 */
require_once 'init.php';

//返佣层级 返佣成绩跟利率相对应 没层级都有相对应的利率
$multi = 2;
//提成年化收益率 单位为百分比（%） 以数组的形式相应配置
$rate_arr = array("1"=>1.0,"2"=>0.5);//3、4、5.....等等以此类推

//多级返佣投资提成脚本运行对象的时间范围 近两小时的数据
$start_time = strtotime('-2 hours',time());
$end_time = time();
//fanyongtichengjiangli
//提成奖励活动配置信息 规则 今天的数据明天处理
$activityConf = MO("ActivityConf")->getInfoByType("fanyongtichengjiangli");//,strtotime("-2 hours",time())
//如果在活动周期内
if($activityConf){
    $endUid = 0;
    while (1) {
        //查询当天投资记录 近2小时的数据
        $deal_load_list = $GLOBALS['db']->getAll("select dl.id,dl.money,dl.deal_id,dl.create_time,d.jiexi_time,d.repay_time,u.pid from " . DB_PREFIX . "deal_load dl LEFT JOIN ".DB_PREFIX."deal d on dl.deal_id = d.id LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id where d.is_effect = 1 AND d.is_delete = 0 AND u.is_effect = 1 AND u.is_delete = 0 AND u.pid > 0 AND dl.is_auto = 0 AND dl.contract_no != '' AND dl.create_time >=".$start_time." AND dl.create_time <=".$end_time." AND dl.id > " . $endUid . "  ORDER BY dl.create_time ASC limit 100 ");
        foreach ($deal_load_list as $key => $deal_load) {
            //投资数据必须在活动配置范围以内
            if($deal_load["create_time"] >= $activityConf["start_time"] && $deal_load["create_time"] <= $activityConf["end_time"]){
                //该投资记录是否已经有过提成奖励数据
                $user_reward_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_reward where load_id = '".$deal_load["id"]."' AND is_effect = 1");
                if(!$user_reward_info){
                    //层级初始化
                    $level = 1;
                    //可以多级返佣 提成奖励多成
                    $recommender_id = $deal_load["pid"];
                    while($recommender_id){
                        $recommender_info = $GLOBALS["db"] ->getRow("SELECT id,user_name FROM ".DB_PREFIX."user where id = '".$recommender_id."' AND is_effect = 1 AND is_delete = 0 ");
                        if(1 == $level){
                            //投资一次获得额外奖励
                            //实名送积分
                            MO('UserScore')->add_score($recommender_info["id"],3);
                        }
                        //准备每层级提成数据
                        $user_reward["user_id"] = $recommender_info["id"];
                        $user_reward["deal_id"] = $deal_load["deal_id"];
                        $user_reward["load_id"] = $deal_load["id"];
                        $user_reward["reward_name"] = "投资提成奖励";
                        $user_reward["money"] = num_format(($rate_arr[$level] / 100 / 360) * $deal_load["money"] * $deal_load["repay_time"]);
                        $user_reward["reward_type"] = 1;//投资提成奖励
                        $user_reward["reward_rate"] = $rate_arr[$level];//投资提成利率
                        $user_reward["status"] = 0;//提成奖励发放 未发放已提现
                        $user_reward["verify_status"] = 0;//提成奖励审核 未审核
                        $user_reward["generation_time"] = time();
                        $user_reward["release_date"] = strtotime($deal_load["jiexi_time"]);
                        $user_reward["is_effect"] = 1;//默认有效
                        $user_reward["remark"] = "投资提成奖励";
                        //金额大于零才入库
                        if($user_reward["money"] > 0){
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_reward", $user_reward, "INSERT");
                        }
                        
                        //看是否有多层级邀请会员 多层级返佣提成奖励
                        $recommender_id = $GLOBALS['db']->getOne("select pid from ".DB_PREFIX."user where id = '".$recommender_id."' AND is_effect = 1 AND is_delete = 0 AND acct_type is null AND user_type = 0");
                        //看循环几层级 超出则跳出循环
                        if($level >= $multi){
                            $recommender_id = 0;//跳出循环
                        }
                        $level += 1;
                    }
                }            
            }
            $endUid = $deal_load['id'];
        }
        if (count($deal_load_list) < 100) {
            echo "执行完毕！";exit;
        }
    }
}
