<?php

class uc_bound_bank_test{
    public function index(){
        $root = array();

        $email = strim($GLOBALS['request']['email']);//用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']);//密码

        $user = user_check($email,$pwd);
        $user_id  = intval($user['id']);
        if ($user_id >0){
            if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_bank WHERE user_id=".$user_id) > 0){
                $root['response_code'] = 0;
                $root['show_err'] = '只能绑定一张银行卡！';
            }else{
                $root['response_code'] = 1;
                $root['show_err'] = '未绑卡';
            }
        }
        else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }
}
?>