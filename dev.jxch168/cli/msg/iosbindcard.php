<?php

/**
 *
 */
require_once './../init.php';
set_time_limit(0);
$last_id = 1;
//$endId   = 0; //默认  -1
$strId   = '';
while (1) {
    $sql_str   = "SELECT	a.id,	a.mobile,	a.real_name FROM 	" . DB_PREFIX . "user a LEFT JOIN " . DB_PREFIX . "user_bank b ON a.id = b.user_id WHERE 	a.id > " . $last_id . " AND b.id > 0 AND a.is_auto = 0 AND a.mobile != '' AND a.idno != '' AND a.user_type = 0  and a.terminal != 3 GROUP BY 	a.id ORDER BY 	a.id ASC LIMIT 10 ";

    $user_list = $GLOBALS['db']->getAll($sql_str);

    $title = '金享财行海德商厦15%保理项目';
    $msg = '南京海德商厦联合金享财行推出15%网鱼网咖、名创优品、DFC影城等70家租户保理项目，稳定性好，活动力度大，赶快投起来吧';
    $strId .= sendForeach($user_list, $title, $msg);
    $last_id = $user_list[count($user_list)-1]['id'];
    if (count($user_list) < 1) {
        echo "操作的用户id为" . $strId;
        exit;
    }
}

function sendForeach($user_list, $title, $msg)
{
    $strId = '';
    if ($user_list) {
        foreach ($user_list as $key => $val) {
            $val['name']                     = $val['real_name'] ? $val['real_name'] : $val['mobile'];
            $msg_data                = array();
            $msg_data['dest']        = $val['mobile'];
            $msg_data['send_type']   = 0;
            $msg_data['title']       = $title;
            $msg_data['content']     = addslashes($msg);
            $msg_data['is_send']     = 0;
            $msg_data['create_time'] = time();
            $msg_data['send_time']   = 0;
            $msg_data['result']      = '';
            $msg_data['is_success']  = 0;
            $msg_data['is_html']     = 0;
            $msg_data['is_youhui']   = 0;
            $msg_data['youhui_id']   = 0;

            $msg_data['user_id'] = $val['id'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT");
            $strId.="," . $val['id'];
        }
    }
    return $strId;
}
