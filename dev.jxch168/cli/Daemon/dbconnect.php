<?php

/*
 * 数据库链接警报------ 建议需要访问数据库的机器都开启这个定时脚本
 */

$db_config = require_once dirname(dirname(dirname(__FILE__))) . '/public/db_config.php';
$link = mysql_connect($db_config['DB_HOST'], $db_config['DB_USER'], $db_config['DB_PWD']);
if (!$link) {
    $users = array(
        '13916497905',
        '13761377369',
        '15618388681',
    );
     $msg  = 'test----'.date("Y-m-d H:i:s") . "数据库链接失败";
    foreach($users as $user)
    {
         $str  = rand(0, 9223372036854775808);
        $url  = "http://61.130.7.220:8023/MWGate/wmgw.asmx/MongateSendSubmit?userId=J50601&password=598712&pszMobis=".$user."&pszMsg={$msg}&iMobiCount=1&pszSubPort=*&MsgId={$str}";
         $data = file_get_contents($url);
    }
}