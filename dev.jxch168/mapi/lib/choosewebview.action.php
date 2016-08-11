<?php

/**
 * 判断是否跳转到 webview 界面
 * 
 */
class choosewebview {

    /**
     * 判断是否跳转到 webview 界面
     * @return [type] []
     */
    public function index() {
        $_m = $GLOBALS['request']["_m"];
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        $root = array();

        if ($_m == 'ios') {
            $root['webview'] = $GLOBALS['m_config']['ios_source_webview'];  //0= 源生界面 1= webview界面
            $root['url'] = $GLOBALS['m_config']['ios_webview_url']; //跳转地址
        } else if ($_m == 'android') {
            $root['webview'] = $GLOBALS['m_config']['android_source_webview'];  //0= 源生界面 1= webview界面
            $root['url'] = $GLOBALS['m_config']['android_webview_url']; //跳转地址
        }
        if ($email && $pwd) {
            user_login($email, $pwd);
        }
        output($root);
    }

}
