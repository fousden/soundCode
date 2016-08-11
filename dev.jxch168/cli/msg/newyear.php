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
    // $sql_str   = "SELECT id,mobile,real_name FROM " . DB_PREFIX . "user where id>$last_id and is_auto=0 and mobile!='' and idno != ''  and user_type = 0 order by id asc limit 10";
    $sql_str   = "SELECT	a.id,	a.mobile,	a.real_name FROM 	" . DB_PREFIX . "user a LEFT JOIN " . DB_PREFIX . "user_bank b ON a.id = b.user_id WHERE 	a.id > " . $last_id . " AND b.id > 0 AND a.is_auto = 0 AND a.mobile != '' AND a.idno != '' AND a.user_type = 0 GROUP BY 	a.id ORDER BY 	a.id ASC LIMIT 10 ";
    $user_list = $GLOBALS['db']->getAll($sql_str);
    foreach ($user_list as $key => $val) {
//        $msg = "亲爱的（" . $val['mobile'] . "），您有一张100元券还未查收！此外还有机会获得苹果笔记本、iPhone 6s plus、空气炸锅 。。。狂戳链接拼人品吧！  http://dwz.cn/2g9DeK   回复TD拒收";
        $str                     = $val['real_name'] ? $val['real_name'] : $val['mobile'];
        //  $msg = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，详情猛戳 wap.jxch168.com";
        // $msg                     = "亲爱的" . $str . "，2016开年利是，金享财行送礼更送福，1月1-4日期间购买产品，金享财行全线产品收益上调1%，新春一点红，满年好运来，最后一天，错过今天，再等一年，详情猛戳      wap.jxch168.com";
        $msg                     = "亲爱的" . $str . "，感谢您在过去的一年中对我们金享的大力支持，祝您在新的一年中吉祥如意，财源广进！金享财行春节期间业务受理及各大活动安排已出，包括申请提现、喜上加喜活动标的、QQ群242675907随时撒钱等，详见官网公告www.jxch168.com！
";
        $msg_data                = array();
        $msg_data['dest']        = $val['mobile'];
        $msg_data['send_type']   = 0;
        $msg_data['title']       = "新春佳节";
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