<?php

/**
 * 外部请求api的入口页
 */
require dirname(dirname(__FILE__)) . '/../system/common.php';
$keyword = isset($_REQUEST['keyword']) ? urldecode($_REQUEST['keyword']) : '';
$openid = isset($_REQUEST['openid']) ? trim($_REQUEST['openid']) : '';
if(!empty($keyword)){
    MO('Weixin')->templateMsgData($keyword,$openid);
//    MO('Weixin')->templateMsgData("投标成功",861,['load_id'=>11002]);
}