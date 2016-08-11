<?php

/*
 * 功能：金享财行修复自动投标数据（主要修复自动投标时间）
 * 时间：2016年04月11日
 * author：chushangming
 */
require_once 'init.php';

$endUid = 0;
while (1) {
    //查询所有的标的数据
    $deal_list = $GLOBALS['db']->getAll("SELECT id,create_time,start_time,success_time FROM ".DB_PREFIX."deal WHERE is_delete = 0 AND is_effect = 1 AND success_time > 0 AND deal_status in (1,2,4,5) AND id > '".$endUid."' ORDER BY id ASC limit 100");
    //外层循环 遍历所有标的下的投资数据
    foreach($deal_list as $key=>$deal){
        //标的开始时间
        $deal_start_time = $deal["start_time"];
        $last_update_time = 0;
        //标的下所有自动投标记录
        $deal_load_list = $GLOBALS['db']->getAll("SELECT id,deal_id,user_id,user_name,create_time FROM ".DB_PREFIX."deal_load WHERE deal_id = '".$deal['id']."' AND is_auto = 1 ORDER BY id ASC");
        //内层循环 更新所有自动投标数据的时间信息
        foreach($deal_load_list as $k=>$deal_load){
            //必须满足条件 在筹标期间内 过滤掉最后一个自动投标数据
            if($deal_load["create_time"] >= $deal_start_time && $deal_load["create_time"] <= $deal["success_time"] && $k != (count($deal_load_list) - 1)){
                if(0 == $k){
                    $update_data["create_time"] = mt_rand($deal_start_time, $deal_load["create_time"]);
                }else{
                    $update_data["create_time"] = mt_rand($last_update_time, $deal_load["create_time"]);
                }
                $update_data["create_date"] = date("Y-m-d",$update_data["create_time"]);
                
                //更新自动投标数据
                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", $update_data, "UPDATE", ' `id` = "' . $deal_load['id'] . '" ');
                //记录上次更新时间
                $last_update_time = $update_data["create_time"];
            }
        }
        $endUid = $deal['id'];
    }
    //循环结束终止
    if (count($deal_list) < 100) {
        echo "自动投标数据更新完成！（修复自动投标时间）";exit;
    }
}


