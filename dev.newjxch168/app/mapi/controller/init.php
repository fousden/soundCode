<?php

namespace mapi\controller;

class Init extends \think\controller {

    public function choosewebview() {
//        echo '<pre>';var_dump(M("test")->select());echo '</pre>';die;
        $root = array(
            "webview" => "0",
            "url" => "http://ncz.dev.jxch168.com/wap/?_vm=android",
            "act" => "choosewebview",
            "act_2" => ""
        );
        $json='{
    "webview": "0",
    "url": "http://ncz.dev.jxch168.com/wap/?_vm=android",
    "act": "choosewebview",
    "act_2": ""
}';
        echo $json;die;
        output($root);
    }
    
    public function version(){
        $iso_root='{
    "serverVersion": "2.0",
    "ios_down_url": "",
    "ios_upgrade": "更新版本号1.33.1，更新内容：\r\n1、更正数个bug;\r\n2、便捷用户投资体验;",
    "has_upgrade": 1,
    "forced_upgrade": 0,
    "response_code": 1,
    "act": "version",
    "act_2": ""
}';
        $android_root='{
    "serverVersion": "2015101402",
    "filename": "http://dch.dev.jxch168.com/apk/Jxch2.3.2_debug_2015101402.apk",
    "android_upgrade": "更新版本号2.3.2，更新内容：\r\n1、更正数个bug;\r\n2、便捷用户投资体验;",
    "hasfile": 0,
    "filesize": 0,
    "has_upgrade": 0,
    "response_code": 1,
    "act": "version",
    "act_2": ""
}';
        $str=$_REQUEST['_m'].'_root';
        echo $$str;die;
        output($root);
    }

}
