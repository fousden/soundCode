<?php
/**
 * 后台共用model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Admin extends backend{

    //验证管理员登录信息
    function checkAdmin($data){
        $admin_info = $this->where(array("admin_name"=>$data["admin_name"],"admin_pwd"=>md5($data["admin_pwd"])))->find();
        return $admin_info;
    }

}