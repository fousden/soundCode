<?php
namespace mapi\controller;
class user extends \think\controller{
    public function register(){
        $home_stytem_model=D('home/system');
        //http://dch.newdev.jxch168.com/mapi/index.php?_m=ios&act=register&channel=ios&jxstr=423b22bd-9d8a-4808-9da3-dac01c1f13d5&mobile=18565826594&mobile_code=0000&referer=&user_name=4111&user_pwd=123456&user_pwd_confirm=123456&version=2.5.1&i_type=1&r_type=1
//        $data['user_type'] = $user_type = isset($arr['user_type'])? trim($arr['user_type']) : '';
//        $data['user_name'] = $user_name = isset($arr['user_name'])? trim($arr['user_name']) : '';
//        $data['mobile'] = $mobile = isset($arr['mobile']) ? trim($arr['mobile']): '';
//        $sms_code = isset($arr['sms_code'])? trim($arr['sms_code']) : '';
//        $data['user_pwd'] = $user_pwd = isset($arr['user_pwd'])? md5(trim($arr['user_pwd'])) : '';
//        $user_pwd_confirm = isset($arr['user_pwd_confirm'])? md5(trim($arr['user_pwd_confirm'])) : '';
//        $data['referer'] = $referer = isset($arr['referer'])? trim($arr['referer']) : '';
//        $agreement = isset($arr['agreement'])? trim($arr['agreement']) : '';
        $data['user_name']=$_REQUEST['user_name'];
        $data['mobile']=$_REQUEST['mobile'];
        $data['sms_code']=$_REQUEST['mobile_code'];
        $data['user_pwd']=$_REQUEST['user_pwd'];
        $data['user_pwd_confirm']=$_REQUEST['user_pwd_confirm'];
        $data['agreement']=1;
        $res=$home_stytem_model->do_register($data);
        
        echo '<pre>';var_dump($res);echo '</pre>';die;
    }
}
