<?php
namespace base\model\sms;
use think\Model;

class Sms extends Model{
    protected $host       = NULL;
    protected $sendPath   = NULL;
    protected $checkPath  = NULL;
    protected $username   = NULL;
    protected $password   = NULL;
    protected $mobile     = NULL;
    protected $content    = NULL;
    
    function __construct() {
        $sms_conf = M('sms')->where(array('is_effect'=>1))->find();
        if($sms_conf){
            $this->host       = $sms_conf['host'];
            $this->sendPath   = $sms_conf['sendpath'];
            $this->checkPath  = $sms_conf['checkpath'];
            $this->username   = $sms_conf['user_name'];
            $this->password   = $sms_conf['password'];
        }
    }
    
    function setKeyvalue($mobile,$content){
        $result = array('status'=>0,'info'=>'发送失败');
        //增加环境判断，测试环境直接返回
        if(empty($mobile)||empty($content)){
            $result['status'] = 0;
            $result['info'] = '发送失败,手机号或者短信内容为空';
            return $result;
        }
        if(!$this->isMobile($mobile)){
            $result['status'] = 0;
            $result['info'] = '发送失败,手机号不正确';
            return $result;
        }
        
        if(!is_string($content)){
            $result['status'] = 0;
            $result['info'] = '发送失败,短信内容格式不正确';
            return $result;
        }
        $this->mobile  = $mobile;
        $this->content = $content;

        $result['status'] = 1;
        $result['info'] = '正在发送中';
        return $result;
    }

    //验证手机号完整性
    private function isMobile($mobile){
        if (!is_numeric($mobile)){
            return false;
        }
        
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

}

