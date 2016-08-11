<?php

/*
 * 功能：预授权撤销
 * 时间：2015年12月01日
 */
require_once 'init.php';
//实现用户平台账户与富友账户余额同步
require_once APP_ROOT_PATH . "system/payment/fuyou.php";
$fuyou = new fuyou();

//配置投资记录ID 即可
$deal_load_id = 7656;

$deal_load = $GLOBALS['db']->getRow("select user_id,contract_no from ".DB_PREFIX."deal_load where id = '".$deal_load_id."'");
$user_info = $GLOBALS['db']->getRow("select user_name,fuiou_account from ".DB_PREFIX."user where id = '".$deal_load["user_id"]."'");

$arr = $fuyou->preAuthCancelAction($user_info['fuiou_account'],$deal_load['contract_no'],time());
if ('0000' == $arr->plain->resp_code) {
    //将该标的置为自动投标记录
    $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", array("user_id"=>281,"is_auto" => 1), "UPDATE", ' `id` ="' . $deal_load_id . '"  ');
    echo "用户名：".$user_info['user_name']."，投资记录ID为".$deal_load_id."的预授权撤销成功！". "\r\n"." return【".json_encode($arr)."】";
} else {
    echo "用户名：".$user_info['user_name']."，投资记录ID为".$deal_load_id."的预授权撤销失败！". "\r\n"." return【".json_encode($arr)."】";
}

