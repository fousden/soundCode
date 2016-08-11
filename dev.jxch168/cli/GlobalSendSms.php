<?php

require_once 'init.php';
set_time_limit(0);
$last_id = 0;
$endId   = 5767; //默认  -1
$strId     = '';
while (1) {
    $sql_str   = "SELECT id,mobile,real_name FROM " . DB_PREFIX . "user where id>$last_id and is_auto=0 and mobile!='' order by id asc limit 10";
    $user_list = $GLOBALS['db']->getAll($sql_str);
    foreach ($user_list as $key => $val) {
        if ($endId > 0 && $val['id'] >= $endId) {
            echo "run fun!!!!!!!!!!!!!!";
            echo "操作的用户id为" . $strId;
            exit;
        }
//        $msg = "亲爱的（" . $val['mobile'] . "），您有一张100元券还未查收！此外还有机会获得苹果笔记本、iPhone 6s plus、空气炸锅 。。。狂戳链接拼人品吧！  http://dwz.cn/2g9DeK   回复TD拒收";
        $str                     = $val['real_name'] ? $val['real_name'] : $val['mobile'];
        //  $msg = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，详情猛戳 wap.jxch168.com";
        $msg                     = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，最后一天，错过今天，再等一年，详情猛戳      wap.jxch168.com";
        $msg_data                = array();
        $msg_data['dest']        = $val['mobile'];
        $msg_data['send_type']   = 0;
        $msg_data['title']       = "金享财行送礼更送福";
        $msg_data['content']     = addslashes($msg);
        $msg_data['is_send']     = 0;
        $msg_data['create_time'] = time();
        $msg_data['user_id']     = $val['id'];
        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT");
        $strId.="," . $val['id'];
    }
    sleep(1);
    $last_id = $val['id'];
    if (count($user_list) < 1) {
        echo "操作的用户id为" . $strId;
        exit;
    }
}