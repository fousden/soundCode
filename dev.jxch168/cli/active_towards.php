<?php

/*
 * 功能：满10000元赠送50元活动 只要是10000元的数倍 赠送也是同倍数赠送
 * 时间：2015年08月24日
 *
 */

require_once 'init.php';
$active_config = require_once APP_ROOT_PATH.'data_conf/over_top_active.php';
//获取活动配置信息
$over_top_act = $active_config['over_top_acting'];
if($over_top_act['ACT_STATUS'] == 1){
    //获取配置信息 计算活动收益 并插入数据库 前提要增加相应数据库字段
    //活动开始时间 结束时间
    $act_start_time = strtotime($over_top_act['ACT_START_TIME']);
    $act_end_time = strtotime($over_top_act['ACT_END_TIME']);

    $endUid = 0;
    while (1) {
        //查询所有有效用户信息
        $users_list = $GLOBALS['db']->getAll('select * from ' . DB_PREFIX . 'user where is_auto = 0 and is_effect = 1 AND is_delete = 0 and acct_type is null and id >' . $endUid . ' limit 100 ');
        foreach($users_list as $key=>$user_info){
            //活动期间投资总额
            $total_money = 0;
            $deal_ids = $deal_load_ids = $user_bonus = array();
            //过滤已经赠送过得
            $before_load_ids = $GLOBALS['db']->getAll('select deal_load_id from ' . DB_PREFIX . 'user_bonus where user_id = '. $user_info['id'] .' AND bonus_type = 0 AND generation_time >= '. $act_start_time .' AND generation_time <= '.$act_end_time);
            //数组最后一个下标
            $last = count($before_load_ids) - 1;
            foreach($before_load_ids as $ke=>$val){
                if($ke == $last){
                    $filter_load_ids .= $val['deal_load_id'];
                }else{
                    $filter_load_ids .= $val['deal_load_id'] . ',';
                }
            }
            //已经参与过红包活动的投资id
            $filter_ids = explode(',', $filter_load_ids);
            $filter_ids = implode(',',array_unique($filter_ids));

            $sql = 'select id,deal_id,money from ' . DB_PREFIX . 'deal_load where user_id = '. $user_info['id'] .' AND is_auto = 0 AND create_time >= '. $act_start_time .' AND create_time <= '.$act_end_time;
            if($filter_ids){
                $sql .= " AND id not in (".$filter_ids.")";
            }
            $deal_loads = $GLOBALS['db']->getAll($sql);
            foreach($deal_loads as $key=>$val){
                $deal_ids[] = $val['deal_id'];
                $deal_load_ids[] = $val['id'];
                $total_money += $val['money'];
            }
            //所投金额是否大于活动起点金额
            if($total_money >= $over_top_act['ACT_TOWARDS_START_MONEY']){
                //是否成倍累计赠送
                if($over_top_act['ACT_IS_MULTIPLE'] == 1){
                    //计算红包大小
                    $bonus_money = floor($total_money / $over_top_act['ACT_TOWARDS_START_MONEY']) * $over_top_act['ACT_TOWARDS_MONEY'];
                }else{
                    $bonus_money = $over_top_act['ACT_TOWARDS_MONEY'];
                }
                //初始化红包数据
                $user_bonus['deal_id'] = implode(',', array_unique($deal_ids));
                $user_bonus['deal_load_id'] = implode(',', array_unique($deal_load_ids));
                $user_bonus['user_id'] = $user_info['id'];
                $user_bonus['reward_name'] = $over_top_act['ACT_NAME'];
                $user_bonus['money'] = $bonus_money;
                $user_bonus['status'] = 1;//默认已提交红包提现申请
                $user_bonus['cash_type'] = 1;//自动提现
                $user_bonus['cash_period'] = 3;//提现周期为3天
                $user_bonus['bonus_type'] = 0;//0代表满就送红包
                $user_bonus['generation_time'] = time();//红包生成时间
                $user_bonus['apply_time'] = time();//默认红包提现申请时间
                $user_bonus['release_time'] = strtotime("+3days",$user_bonus['apply_time']);//预计发放时间
                $user_bonus['release_date'] = strtotime(date('Y-m-d',$user_bonus['release_time']));//预计发放日期
                $user_bonus['remark'] = $over_top_act['ACT_NAME'];
                $GLOBALS['db']->autoExecute(DB_PREFIX."user_bonus",$user_bonus,"INSERT");//插入红包数据
                $user_bonus_id = $GLOBALS['db']->insert_id();
                if($user_bonus_id > 0){
                    //如果成功 则短信通知
                    //                $msg = "尊敬的". app_conf("SHOP_TITLE") ."用户". $user_info['user_name']  . "，您的投标" .$over_top_act['ACT_NAME']. "活动获得红包奖励，红包金额".$bonus_money."，奖励已于".to_date(TIME_UTC,"Y-m-d")."发放。";
                    //                $msg_data['dest'] = $user_info['mobile'];
                    //                $msg_data['send_type'] = 0;
                    //                $msg_data['title'] = "投标红包短信通知";
                    //                $msg_data['content'] = addslashes($msg);;
                    //                $msg_data['send_time'] = 0;
                    //                $msg_data['is_send'] = 0;
                    //                $msg_data['create_time'] = TIME_UTC;
                    //                $msg_data['user_id'] = $user_info['id'];
                    //                $msg_data['is_html'] = 0;
                    //                $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data,"INSERT"); //插入
                    file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_over_bonus.log', "POST:[" .$user_info['user_name']."的红包发放成功". json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }else{
                    file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_over_bonus.log', "POST:[" .$user_info['user_name']."的红包发放失败".json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
            }
            $endUid = $user_info['id'];
        }
        if (count($users_list) < 100) {
            echo "finshed";
            exit;
        }
    }
}