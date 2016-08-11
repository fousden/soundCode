<?php

/*
 * 功能：还款计划异常预警 （思路：判断所有有效还款计划对应的投资订单是否正常有效，主要指预授权等一些信息）
 * 时间：2015年12月02日
 * author:楚尚明
 */

require_once 'init.php';

$str = '';
$error_num = 1;
$endUid = 0;
while (1) {
    //查询所有用户信息
    //接受参数cli
    if($argv[1] > 0){
        $deal_load_repay_list = $GLOBALS['db']->getAll('select dlr.*,dl.contract_no,dl.is_auto,dl.is_has_loans from ' . DB_PREFIX . 'deal_load_repay dlr LEFT JOIN '.DB_PREFIX.'deal_load dl on dlr.load_id = dl.id where dlr.has_repay = 0 AND dlr.deal_id = ' . $argv[1]." ORDER BY dlr.id asc");
    }else{
        $deal_load_repay_list = $GLOBALS['db']->getAll('select dlr.*,dl.contract_no,dl.is_auto,dl.is_has_loans from ' . DB_PREFIX . 'deal_load_repay dlr LEFT JOIN '.DB_PREFIX.'deal_load dl on dlr.load_id = dl.id where dlr.has_repay = 0 AND dlr.id > ' . $endUid . ' ORDER BY dlr.id asc limit 100 ');
    }

    foreach ($deal_load_repay_list as $key => $deal_load_repay) {
        if(!$deal_load_repay["contract_no"] || $deal_load_repay["is_has_loans"] == 0 || $deal_load_repay["is_auto"] == 1){
            if($error_num == 1){
                $str .= "【网站还款记录存在异常，异常信息如下：】<br/>";
            }
            $error_num +=1;
            $user_info = $GLOBALS['db']->getRow("select user_name,mobile,real_name from ".DB_PREFIX."user where id = '".$deal_load_repay["user_id"]."'");
            $str .= "　　用户信息:【用户名：" . $user_info['user_name'] . "手机号：".$user_info['mobile'] .";真实姓名：".$user_info['real_name']."】";
            $str .= "　　标的ID&nbsp;：【" . $deal_load_repay['deal_id'] . "】";
            $str .= "　　投资记录ID：【" . $deal_load_repay['load_id'] . "】";
            $str .= "　　还款计划部分信息：【ID：" . $deal_load_repay['id'] . ";还款金额：".$deal_load_repay['repay_money']."】【".date('Y-m-d H:i:s')."】";
            $str .= "<br/>********************************************<br/>";
        }

        $endUid = $deal_load_repay['id'];
    }
    if (count($deal_load_repay_list) < 100) {
        //存在异常才发短信 邮件预警
        if($str){
            //短信预警 短信通知管理员组成员处理
            $msg = "网站代还款中的还款计划存在异常，请查看日志确认！【时间：".date('Y-m-d H:i:s')."】。";
            info_admin($msg,"网站代还款");
            //邮件通知 管理员组成员处理
            $info_email_users = explode(',', INFO_EMAIL_USER);
            foreach($info_email_users as $email_user){
                $msg_data['dest'] = $email_user;
                $msg_data['send_type'] = 1;//0短信 1 邮件
                $msg_data['title'] = date("Y-m-d")."网站代还款还款计划异常邮件通知";
                $msg_data['content'] = addslashes($str);
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = TIME_UTC;
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = 1;//$tmpl['is_html']  1超文本 0纯文本
                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
            }
        }
		echo "run ok!";
        exit;
    }
}

