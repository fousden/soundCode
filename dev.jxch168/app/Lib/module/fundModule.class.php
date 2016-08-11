<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class fundModule extends SiteBaseModule
{

    public function index()
    {
        if (empty($GLOBALS['user_info']))
        {
            echo "您还未登录请先登录";
            exit;
        }

        $flag = $GLOBALS['db']->getOne("select fund_flag from " . DB_PREFIX . "user where id = " . $GLOBALS['user_info']['id'] );
        $time = time();
        if (empty($flag))
        {
            $GLOBALS['db']->getOne("update " . DB_PREFIX . "user set fund_flag = 1,connect_time =".$time." where id = " . $GLOBALS['user_info']['id'] );
            echo "您已经提交投资意向，我们的工作人员会跟你进行电话沟通，请耐心等待！";
            // 同时发送邮件
            $date = date("Y-m-d",time());
            $str ="罗总：<br>&nbsp; &nbsp; &nbsp; &nbsp; 您好，用户名:" .$GLOBALS['user_info']['user_name'] . ",用户真实姓名: " .$GLOBALS['user_info']['real_name'] . ",手机号:" .$GLOBALS['user_info']['mobile'] . "，对茗盛项目有投资意向，请安排同事进行跟进。<br>&nbsp; &nbsp; &nbsp; &nbsp; 此致<br>敬礼！<br><span>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 金享财行<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp;  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>$date";
            $contents['msg'] = $str;
            $contents['title'] = "客户反馈";
            adnormal_warning($contents,$contents,'',DEAL_EMAIL_USER);
            exit;
        } else {
            echo "请忽重复提交投资意向，我们的工作人员会跟你进行电话沟通，请耐心等待！";
            exit;
        }
    }
}