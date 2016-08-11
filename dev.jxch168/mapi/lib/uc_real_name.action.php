<?php

class uc_real_name
{
    public function index(){
        $email = strim($GLOBALS['request']['email']);//用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']);//密码

        //检查用户,用户密码
        $user = user_check($email,$pwd);
        $user_id  = intval($user['id']);
        if ($user_id >0){
            $root['response_code'] = 1;
            if($user['idno']){
                $root['idno'] = preg_replace('/^(.{4})(.*)(.{4})$/i','${1}**********$3',$user['idno']);
                $root['real_name'] = $user['real_name'];
            }else{
                $root['response_code'] = 0;
		//是否开启《平安保险网银卫士》资金安全担保条款
		$root['insurance_show']=$GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'PINGANPROVISION'");
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }

        $root['program_title'] = "实名认证";
        output($root);
    }
}
?>
