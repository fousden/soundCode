<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class tv_channelModule extends SiteBaseModule{
    public function index(){
        foreach($_GET as $k=>$v){
            $_GET[$k] = htmlspecialchars(addslashes($v));
        }
        $m = $_GET['mobile'];
        $s = $_GET['s'];
        // 渠道参数不存在或者为空都同意赋值为public
        if(!$s){
            $s="public";
        }
        // 手机号码为空或者不是1开头的11为数字则返回-1
        if(!empty($m) && !preg_match("/^1\d{10,}$/",$m)){
            $code=-1;
        }else{
            $code = MO("Channel")->activity($m,$s);
        }
        $msg = MO("Channel")->getMessege($code);
        die(json_encode($msg));
    }
}
