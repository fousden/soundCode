<?php

/*
 * 每邀请10名实名用户得10元抵现劵
 * 注意事项：
 * 1、邀请的开始日期为2016-04-21
 * 2、邀请人必须要是实名用户
 */
require_once 'init.php';
//设置邀请生效时间
$start_time=  strtotime("2016-04-21");
//设置最低发放奖品邀请人数
define("INVITE_COUNT", 10);
$str_str = "SELECT count(*) as invite_count,pid from fanwe_user where pid!=0 and idno!='' and create_time>$start_time GROUP BY pid HAVING invite_count>=10";
$invite_list = $GLOBALS['db']->getAll($str_str);
$userModel = MO('User');
$UserRewardModel = MO('UserReward');
$CouponModel = MO("Coupon");
foreach ($invite_list as $val) {
    $user_id = $val['pid'];
    $user_info = $userModel->getUserInfoById($user_id, 'idno');
    //邀请人未实名则不发放任何福利
    if (!$user_info['idno']) {
        continue;
    }

    $user_reward_Info = $UserRewardModel->getRewardInfoByUserId($user_id, 'count(*) as reward_count', ' and reward_type=2');

    //该用户应得几个优惠劵
    $prize_count = intval($val['invite_count'] / INVITE_COUNT) - $user_reward_Info['reward_count'];
    //应得的优惠劵少于数量1则没必要执行下去
    if ($prize_count < 1) {
        continue;
    }
    //开始发放奖励
    for ($i = 0; $i < $prize_count; $i++) {
        $res = $CouponModel->confAdd($user_id, ['remark' => '邀请好友获得'], 3);
        if ($res) {
            $reward_data['user_id'] = $user_id;
            $reward_data['reward_name'] = '邀请好友获得';
            $reward_data['reward_type'] = 2;
            $reward_data['generation_time'] = time();
            $GLOBALS['db']->autoExecute(DB_PREFIX . 'user_reward', $reward_data, "INSERT");
        }
    }
}