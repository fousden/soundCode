<?php

/*
 * 短信存量报警
 * 通过守护进程，方式预警  执行完成后结束
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(dirname(__FILE__)) . '/init.php';

$list = $GLOBALS['db']->getAll("select  count(*) as num from " . DB_PREFIX . "deal_msg_list where is_send = 0 and (send_type = 0 or send_type = 1)");

if ($list[0]['num'] > 20) {
    $msg   = '' . date("Y-m-d H:i:s") . "短信未发送超过[" . $list[0]['num'] . ']条';
    $users = array(
        '13916497905',
        '13761377369',
        '15618388681',
    );
    foreach ($users as $user) {
        $str  = rand(0, 9223372036854775808);
        $url  = "http://61.130.7.220:8023/MWGate/wmgw.asmx/MongateSendSubmit?userId=J50601&password=598712&pszMobis=" . $user . "&pszMsg={$msg}&iMobiCount=1&pszSubPort=*&MsgId={$str}";
        $data = file_get_contents($url);
    }
}
