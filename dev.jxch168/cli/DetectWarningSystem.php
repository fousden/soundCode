<?php

/*
 * 功能：预警检测系统
 * 时间：2015年12月14日
 * author：chushangming
 */

class DetectWarningSystem {

    //当一个手机号码累计三天都发送了注册验证码加入黑名单
    function addMobileBlacklist(){
        $sql_str="SELECT mobile FROM `fanwe_mobile_blacklist` where is_delete=0";
        $mobile_blacklist_list=$GLOBALS['db']->getAll($sql_str);
        $mobiles = array_map('array_shift', $mobile_blacklist_list);

        $sql_str="SELECT dest,FROM_UNIXTIME(create_time,'%Y-%m-%d') as date,count(DISTINCT FROM_UNIXTIME(create_time,'%Y-%m-%d')) as c FROM `fanwe_deal_msg_list` where user_id=0 and send_type=0 and dest not in(" . implode(",",$mobiles) . ") GROUP BY dest having c>=3";
        $list=$GLOBALS['db']->getAll($sql_str);
        if(!is_dir(APP_ROOT_PATH . 'log/cli/'))mkdir(APP_ROOT_PATH . 'log/cli/addMobileBlacklist/',0777,true);
        foreach($list as $key=>$val){
            MO("MobileBlacklist")->addMobile($val['dest'],false);
            file_put_contents(APP_ROOT_PATH . 'log/cli/addMobileBlacklist/' . date('Y-m-d') . '_addMobileBlacklist.log', "[" . date("Y-m-d H:i:s") . "] ".$val['dest']."\r\n", FILE_APPEND);
        }
    }

    //短信余量预警
    function smsWarning(){
        //泉龙达短信条数预警
        $ress = send_sms_email($msg_item, 3,"EN");

        //一信通短信条数预警
        require_once APP_ROOT_PATH . "system/sms/YY_sms.php";
        $yy_sms = new YY_sms();
        $yy_arr = $yy_sms->get_count_msg();

        //余量小于5000则预警
        if($ress && $ress["status"] === 1){
            if($ress['return'] <= SMS_MIN){
                $contents[0]['title'] = "泉龙达短信余量预警";
                $contents[0]['msg'] = "泉龙达短信剩余量为".($ress['return'] ? $ress['return'] : 0)."条，为防止余量不足，请及时充值！";
            }
            //记录泉龙达短信余量
            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_quanlongda_remain.log', "泉龙达短信余量为【".($ress['return'] ? $ress['return'] : 0)."】【" . date("Y-m-d H:i:s") . "】\r\n", FILE_APPEND);
        }
        if($yy_arr && $yy_arr['result']){
            if(intval($yy_arr["number"]) <= SMS_MIN){
                $contents[1]['title'] = "一信通短信余量预警";
                $contents[1]['msg'] = "一信通短信剩余量为".($yy_arr["number"] ? intval($yy_arr["number"]) : 0)."条，为防止余量不足，请及时充值！";
            }
            //记录一信通短信余量
            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_yixintong_remain.log', "一信通短信余量为【".($yy_arr["number"] ? intval($yy_arr["number"]) : 0)."】【" . date("Y-m-d H:i:s") . "】\r\n", FILE_APPEND);
        }
        if($contents){
            foreach($contents as $key=>$val){
                //运营人员
                adnormal_warning($val,$val,OPERATE_USER,OPERATE_EMAIL_USER);
                //系统管理员
                adnormal_warning($val,$val,INFO_USER,INFO_EMAIL_USER);
            }
        }
    }

    //途虎活动兑换码余量预警
    function tuActiveWarning(){
        //尚未使用的途虎优惠券数量
        $car_coupon_num = $GLOBALS['db']->getOne("select count(*) as car_coupon_num from " .DB_PREFIX. "car_coupon where status = 0");
        if($car_coupon_num <= TU_NUM){
            $contents['title'] = "途虎活动兑换码余量预警";
            $contents['msg'] = "途虎活动兑换码余量为".$car_coupon_num."条，为防止余量不足，请及时增加途虎活动兑换码！";
            //系统管理员
            adnormal_warning($contents,$contents,INFO_USER,INFO_EMAIL_USER);
        }
    }

    //过期标的预警
    function  expireDeal(){
        $time = time();
	//查找所有过期标的总数
        $ids= $GLOBALS["db"]->getAll("select id from ".DB_PREFIX."deal where deal_status = 1 AND publish_wait = 0  AND is_has_loans = 0 AND start_time <= ".$time." AND is_effect = 1 AND is_delete = 0 AND (start_time + (enddate * 24 * 3600) - ".$time.") < 0");
        $expireDealNum = count($ids);
        if($expireDealNum > 0){
            $msg = "金享财行过期标的预警，总共过期".$expireDealNum."个标的，标的id：【". json_encode($ids)."】，请及时处理！";
            $mobilesContents['title'] = $emailsContents['title'] = "金享财行过期标的预警";
            $mobilesContents['msg'] = "金享财行存在过期标的，请及时处理！";
            $emailsContents['msg'] = $msg;
            //系统管理员
            adnormal_warning($mobilesContents,$emailsContents,INFO_USER,INFO_EMAIL_USER);
        }
    }

    //订单异常 合同号为空 但实际预授权成功
    function abnormalOrders(){
        //查询近2小时内所有投标信息
        $end_time = time() - (10 * 60);//当前十分钟之前的数据
        $start_time = strtotime(" -2 hours ",$end_time);//近两小时的数据

        $error_num = 1;
        $deal_load_list = $GLOBALS['db']->getAll("select id,user_id,deal_id,money,is_has_loans,contract_no,contract_no_flag from " . DB_PREFIX . "deal_load where create_time >= ".$start_time." and create_time <= ".$end_time." and is_auto = 0 ORDER BY create_time DESC");
        foreach($deal_load_list as $key=>$deal_load){
            if(!$deal_load["contract_no"]){
                if($error_num == 1){
                    $str .= "【投标订单信息存在异常，异常信息如下：】<br/>";
                }
                $user_info = $GLOBALS['db']->getRow("select user_name,mobile,real_name from ".DB_PREFIX."user where id = '".$deal_load["user_id"]."'");
                $str .= "　　用户信息:【用户名：" . $user_info['user_name'] . "手机号：".$user_info['mobile'] .";真实姓名：".$user_info['real_name']."】";
                $str .= "　　标的ID&nbsp;：【" . $deal_load['deal_id'] . "】";
                $str .= "　　投资记录ID：【" . $deal_load['id'] . "】";
                $str .= "　　投资金额：【money：" . $deal_load['money'] . "】【".date('Y-m-d H:i:s')."】";
                $str .= "<br/>********************************************<br/>";

                $error_num +=1;
            }
        }
        if($str){
            $mobilesContents['title'] = $emailsContents['title'] = "金享财行投标订单异常预警";
            $mobilesContents['msg'] = "金享财行投标订单信息存在异常，请及时处理！";
            $emailsContents['msg'] = $str;
            //系统管理员
            adnormal_warning($mobilesContents,$emailsContents,INFO_USER,INFO_EMAIL_USER);
        }
    }

    //起息时间异常预警
    function qixi_timeAbnormal(){
        $endUid = 0;
        $error_num = 1;
        while (1) {
            //查询所有用户信息
            //接受参数cli
            $deal_list = $GLOBALS['db']->getAll('select id,name,success_time,qixi_time,jiexi_time,last_mback_time,repay_time from ' . DB_PREFIX . 'deal where is_effect = 1 AND is_delete = 0 AND deal_status in (2,4,5) and id >' . $endUid . ' ORDER BY id DESC limit 100 ');
            foreach ($deal_list as $key => $deal) {
                //更新标的起息时间 结息时间
                $old_qixi_time = date("Y-m-d", strtotime("+1 day", $deal['success_time']));
                $old_jiexi_time = date("Y-m-d", strtotime("+" . $deal['repay_time'] . " days", strtotime($old_qixi_time)));
                $old_last_mback_time = date("Y-m-d", strtotime("+2 days", strtotime($old_jiexi_time))); //默认是结息日后3天

                if($old_qixi_time != $deal['qixi_time'] || $old_jiexi_time != $deal['jiexi_time']){
                    if($error_num == 1){
                        $str .= "【标的满标起息结息时间存在异常，异常信息如下：】<br/>";
                    }
                    $str .= "　　标的信息:【标的ID：" . $deal['id'] . "】【标的名称：".$deal['name']."】";
                    $str .= "　　【满标时间：".date("Y-m-d", $deal['success_time'])."】【起息时间：".$deal["qixi_time"]."】【结息时间：".$deal["jiexi_time"]."】【最迟还款日：".$deal["last_mback_time"]."】";
                    $str .= "<br/>********************************************<br/>";

                    $error_num +=1;
                }
                $endUid = $deal['id'];
            }
            if (count($deal) < 100) {
                if($str){
                    $mobilesContents['title'] = $emailsContents['title'] = "金享财行标的满标起息结息时间异常预警";
                    $mobilesContents['msg'] = "金享财行满标起息结息时间存在异常，请及时处理！";
                    $emailsContents['msg'] = $str;
                    //系统管理员
                    adnormal_warning($mobilesContents,$emailsContents,INFO_USER,INFO_EMAIL_USER);
                }
                break;
            }
        }
    }
}