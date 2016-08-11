<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'init.php';
// 员工编号
$file = file("jbh2015.txt");
$p    = array();
// 搜索该员工编号在admin表中是否有记录
foreach ($file as $v) {
    $work_id = trim($v);
    $sql     = "select * from " . DB_PREFIX . "admin where work_id = '{$work_id}' ";
    $res     = $GLOBALS['db']->getRow($sql);
    if ($res) {
        $name                = $res['adm_name'];
        $mobile              = $res['mobile'];

        //生成二维码
        //二维码URL
        $filename            = $work_id . "_" . $mobile;
        $qr_code_url         = CLI_DOMAIN . "front/jbh2015/jbh2015.html?referer=" . $work_id . "&aname=" . urlencode($name);
        $logo_img_url        = dirname(dirname(__FILE__)) . '/public/images/logo.png';
        $my_grcode           = gen_qrcode($qr_code_url, $logo_img_url, 10, $filename);
        // 调用短信接口
        $content = "尊敬的理财顾问" . $name."，点击此链接" . trim(CLI_DOMAIN,'/') . $my_grcode."  生成您的专属二维码，打开保存至本地，推广时至用户扫码注册即可。";
        $tmp                 = send_msg_to_db($mobile, $content);
    } else {
        echo $work_id . "\r\n";
    }
}

