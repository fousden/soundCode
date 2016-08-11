<?php
class uc_bound_bank
{
    public function index(){
        $email = strim($GLOBALS['request']['email']);//用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']);//密码

        //检查用户,用户密码
        $user = user_check($email,$pwd);
        $user_id  = intval($user['id']);

        if ($user_id >0){
            $root['response_code'] = 1;
            $bank_num = $GLOBALS['db']->getOne("select count(id) from ".DB_PREFIX."user_bank where user_id=$user_id");

            //判断是否验证过身份证
            if($GLOBALS['user_info']['real_name']==""){
                $root['response_code'] = 0;
                $root['response_code_e'] = 1;
                $root['show_err'] ="您的实名信息尚未填写！\n为保护您的账户安全，请先填写实名信息。";
            }else if($bank_num >= 1){
                $root['response_code'] = 0;
                $root['response_code_e'] = 2;
                $root['real_name'] = $user['real_name'];
                $root['show_err'] ="只能绑定一张银行卡！";
            }else{
                $root['response_code'] = 1;
                $root['response_code_e'] = 0;

                $bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank  where fuyou_bankid != '' ORDER BY is_rec DESC,sort DESC,id ASC");
                $region_lv1 = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."district_info where parentcode = 0");

                $root['region_lv1'] = $region_lv1;
                $root['bank_list'] = $bank_list;
                $root['real_name'] = $user['real_name'];
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }

        $root['program_title'] = "绑定银行卡";
        output($root);
    }
}
?>
