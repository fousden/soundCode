<?php
/**
 * 前台共用model业务逻辑类
 *
 * @author jxch
 */
namespace base\model;
use \base\model\base;

class frontend extends base{
    public function DIY(){
        //添加自己的业务逻辑
        // ...
    }

    // 手机发短信
    public function send_msg($mobile){
        if($mobile){
            return "1"; // 发送成功
        }else{
            return "0"; // 发送失败
        }
    }

    // 手机验证码
    public function verify_msg($mobile,$code){
        if($mobile){
            return "1"; // 验证成功
        }else{
            return "0"; // 验证失败
        }
    }
}