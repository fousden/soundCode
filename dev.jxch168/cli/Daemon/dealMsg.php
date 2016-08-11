<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(dirname(__FILE__)).'/init.php';
while(1)
{
    $list  = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "deal_msg_list where is_send = 0 and (send_type = 0 or send_type = 1) order by id asc limit 10");
    if($list)
    {
        foreach($list as $msg_item)
        {
             $GLOBALS['db']->query("update " . DB_PREFIX . "deal_msg_list set is_send = 1 where id =" . intval($msg_item['id']));
            if ($GLOBALS['db']->affected_rows()) {
                $result = send_sms_email($msg_item);
                //发送结束，更新当前消息状态
                $GLOBALS['db']->query("update " . DB_PREFIX . "deal_msg_list set is_success = " . intval($result['status']) . ",result='" . $result['msg'] . "',send_time='" . time() . "' where id =" . intval($msg_item['id']));
            }
        }
    }
    sleep(1);
}
