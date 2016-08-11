<?php
/**
 * 基础model业务逻辑类
 *
 * @author jxch
 */
namespace base\model;
use think\Model;

class base extends Model{

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}