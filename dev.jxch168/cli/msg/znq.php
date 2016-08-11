<?php

/**
 *金享周年短信20150420
 * 发送3.15到现在的所有注册用户
 */
require_once './../init.php';
set_time_limit(0);
$last_id = 1;
//$endId   = 0; //默认  -1
$strId   = '';
while (1) {
    // $sql_str   = "SELECT id,mobile,real_name FROM " . DB_PREFIX . "user where id>$last_id and is_auto=0 and mobile!='' and idno != ''  and user_type = 0 order by id asc limit 10";
    $sql_str   = "SELECT a.id, a.mobile, a.real_name, a.money FROM fanwe_user a where  a.id>" . $last_id . " and create_time > unix_timestamp('20160315') LIMIT 10; ";
    $user_list = $GLOBALS['db']->getAll($sql_str);
    foreach ($user_list as $key => $val) {
//        $msg = "亲爱的（" . $val['mobile'] . "），您有一张100元券还未查收！此外还有机会获得苹果笔记本、iPhone 6s plus、空气炸锅 。。。狂戳链接拼人品吧！  http://dwz.cn/2g9DeK   回复TD拒收";
        $str                     = $val['real_name'] ? $val['real_name'] : $val['mobile'];
        //  $msg = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，详情猛戳 wap.jxch168.com";
        // $msg                     = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，最后一天，错过今天，再等一年，详情猛戳      wap.jxch168.com";
        $msg                     = "金享财行周年加息回馈，短期票号收益直升10.8%，还有iPhone带回家 http://t.cn/Rq9iKOa";
        $msg_data                = array();
        $msg_data['dest']        = $val['mobile'];
        $msg_data['send_type']   = 0;
        $msg_data['title']       = "周年加息回馈";
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
    $last_id = $val['id'];
    if (count($user_list) < 1) {
        echo "操作的用户id为" . $strId;
        exit;
    }
}