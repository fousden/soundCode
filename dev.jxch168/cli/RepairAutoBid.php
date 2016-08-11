<?php

/*
 * 功能：金享财行修复自动投标数据（李明明用户）
 * 时间：2016年04月05日
 * author：chushangming
 */
require_once 'init.php';

$endUid = 0;
$user_info_arr = $GLOBALS['db']->getAll("select id,user_name,real_name,mobile,money,paypassword from ".DB_PREFIX."user where is_auto = 1 AND id != 281 AND user_name != '李明明' limit 300");

while (1) {
    //查询所有李明明的数据 自动投标数据
    $deal_load_list = $GLOBALS['db']->getAll("select dl.id,dl.money,dl.deal_id,dl.create_time,dl.user_id,u.user_name from " . DB_PREFIX . "deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id where u.is_effect = 1 AND u.is_delete = 0 AND u.is_auto = 1 AND u.user_name = '李明明' AND dl.is_auto = 1 AND dl.id > " . $endUid . "  ORDER BY dl.id ASC limit 100 ");
    foreach ($deal_load_list as $key => $deal_load) {
        //自动更新李明明数据 
        if(281 == $deal_load["user_id"] && "李明明" == $deal_load["user_name"]){
            //除李明明外的其他99个自动投标用户随机产生
            $user_info = $user_info_arr[mt_rand(0,count($user_info_arr) - 1)];
            $update_data['user_id'] = $user_info["id"];
            $update_data["user_name"] = $user_info["user_name"];
            //更新自动投标数据
            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", $update_data, "UPDATE", ' `id` = "' . $deal_load['id'] . '" ');
        }     
        $endUid = $deal_load['id'];
    }
    if (count($deal_load_list) < 100) {
        echo "自动投标数据更新完成！";exit;
    }
}


