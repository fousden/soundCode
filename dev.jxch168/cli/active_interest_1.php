<?php

/*
 * 功能：额外加1%年化率客户名单 通过名单进行执行
 *
 */
exit;
require_once 'init.php';
//20150120
//$arr = array(
//    array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'9.16'),
//    array('real_name'=>'李学华','mobile'=>'13040681351','money'=>'18.88'),
//    array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'53.33'),
//    array('real_name'=>'李学华','mobile'=>'13040681351','money'=>'8.88'),
//    array('real_name'=>'钟小新','mobile'=>'13901092123','money'=>'17.77'),
//    array('real_name'=>'李学华','mobile'=>'13040681351','money'=>'9.16'),
//    array('real_name'=>'王恒尧','mobile'=>'13717871410','money'=>'125'),
//    array('real_name'=>'冯成诚','mobile'=>'13510406125','money'=>'385'),
//);
//20150121
//$arr = array(
//    array('real_name'=>'张元林','mobile'=>'13991870425','money'=>'9.44'),
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'0.91'),
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'28.33'),
//    array('real_name'=>'张铁军','mobile'=>'15004055410','money'=>'18.88'),
//    array('real_name'=>'钟小新','mobile'=>'13901092123','money'=>'7.5'),
//    array('real_name'=>'张元林','mobile'=>'13991870425','money'=>'7.22'),
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'14.44'),
//    array('real_name'=>'黄丽红','mobile'=>'18668218212','money'=>'250'),
//    array('real_name'=>'薛宝丽','mobile'=>'13671178064','money'=>'100'),
//    array('real_name'=>'王恒尧','mobile'=>'13717871410','money'=>'700'),
//);
//20150122
//$arr = array(
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'10.55'),
//    array('real_name'=>'林燕飞','mobile'=>'13988961296','money'=>'100'),
//    array('real_name'=>'薛宝丽','mobile'=>'13671178064','money'=>'400'),
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'9.44'),
//);
//20150126
//$arr = array(
//    array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'47.22'),
//     array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'18.88'),
//     array('real_name'=>'薛宝丽','mobile'=>'13671178064','money'=>'300'),
//     array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'33.33'),
//     array('real_name'=>'段夫星','mobile'=>'18651884898','money'=>'1000.25'),
//);

////20150129
//$arr = array(
//    array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'22.22'),
//     array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'43.33'),
//     array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'144.44'),
//     array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'0.11'),
//     array('real_name'=>'陈宝智','mobile'=>'18587251975','money'=>'11.11'),
//    array('real_name'=>'冯成诚','mobile'=>'13510406125','money'=>'75'),
//);

//20150129
//$arr = array(
//    array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'107.5'),
//    array('real_name'=>'冯成诚','mobile'=>'13510406125','money'=>'55.5'),
//);

//20160202-2
$arr = array(
    array('real_name'=>'于海波','mobile'=>'18913867599','money'=>'26.66'),
);

if ($arr)
{
    foreach ($arr as $val)
    {
        $userInfo = $GLOBALS['db']->getRow('select * from ' . DB_PREFIX . 'user where real_name = "'.$val['real_name'].'" and mobile = "'.$val['mobile'].'"   limit 1');
        //初始化红包数据
        $user_bonus['deal_id'] = 0;
        $user_bonus['deal_load_id'] = 0;
        $user_bonus['user_id'] = $userInfo['id'];
        $user_bonus['reward_name'] = getBonusTypeName(9);
        $user_bonus['money'] = $val['money'];
        $user_bonus['status'] = 1;//默认已提交红包提现申请
        $user_bonus['cash_type'] = 1;//自动提现
        $user_bonus['cash_period'] = 3;//提现周期为3天
        $user_bonus['bonus_type'] = 9;//0代表回访老客户1%红包
        $user_bonus['generation_time'] = time();//红包生成时间
        $user_bonus['apply_time'] = time();//默认红包提现申请时间
        $user_bonus['release_time'] = strtotime("+3days",$user_bonus['apply_time']);//预计发放时间
        $user_bonus['release_date'] = strtotime(date('Y-m-d',$user_bonus['release_time']));//预计发放日期
        $user_bonus['remark'] = getBonusTypeName(9);
        $GLOBALS['db']->autoExecute(DB_PREFIX."user_bonus",$user_bonus,"INSERT");//插入红包数据
        $user_bonus_id = $GLOBALS['db']->insert_id();
    }
}
