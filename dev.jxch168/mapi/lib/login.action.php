<?php

class login {

    public function index() {
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
//	file_put_contents('1.txt', json_encode($pwd));
        $result = user_login($email, $pwd);
        if ($result['status']) {
            $user_data = $GLOBALS['user_info']; //$result['user'];
            $root['response_code'] = 1;
            //$root['user_login_status'] = 1;//用户登录状态：1:成功登录;0：未成功登录
            $root['show_err'] = "用户登录成功";
            $root['id'] = $user_data['id'];
            $root['user_name'] = $user_data['user_name'];
            $root['user_pwd'] = $user_data['user_pwd'];
            $root['user_money'] = $user_data['money'];
            $root['user_money_format'] = format_price($user_data['money']); //用户金额
            $root['real_name'] = $user_data['real_name'];
            $root['idcard'] = $user_data['idno'];
            $root['idcardpressed'] = $user_data['idcardpressed'];
        } else {
            $root['response_code'] = 0;
            //$root['user_login_status'] = 0;//用户登录状态：1:成功登录;0：未成功登录
            $root['show_err'] = $result['msg'];
            $root['id'] = 0;
            $root['user_name'] = $email;
            $root['user_email'] = $email;
        }
//        $root['act'] = "login";
        output($root);
    }

}

?>