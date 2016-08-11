<?php
namespace base\model;
use think\Model;


class EsSms extends Model{

    function sendSmsMsg($mobile,$content){
        $res = array('status'=>0,'info'=>'发送失败');
        $type = M('sms')->where(array('is_effect'=>1))->getField('class_name');
        switch($type){
            case 'lqd':
                $SmsMsg = new sms\Lqdsms();
                break;
            case 'yxt':
                $SmsMsg = new sms\Yxtsms();
                break;
            default:
                $res['status'] = 0;
                $res['info'] = '发送失败,没有可用的短信发送平台';
                return $res;
        }
        //验证信息的有效性,验证成功赋值
        $res = $SmsMsg->setKeyValue($mobile,$content);
        if($res['status']){
            $res = $SmsMsg->send(); 
        }
        return $res;
    }

    function checkSmsMoney($type){
        switch($type){
            case 'lqd':
                $SmsMsg = new sms\Lqdsms();
                break;
            case 'yxt':
                $SmsMsg = new sms\Yxtsms();
                break;
            default:
                $res['status'] = 0;
                $res['info'] = '查询失败,未找到短信发送平台';
                return $res;
        } 
        $res = $SmsMsg->check(); 
        return $res;
    }
    
}
