<?php

namespace admin\controller;
use \base\controller\base;

/**
 * 后台公用控制器
 *
 * @author jxch
 */

class Admin extends base{


    //管理员登录
    public function login(){
        //验证是否已登录
        //管理员的SESSION
        $admin_info = session("admin_info");
        if($admin_info)
        {
            //已登录
            $this->redirect("/admin/index/index");
        }
        else
        {
            return $this->fetch();
        }
    }

    //实现管理员登录操作
    public function doLogin(){
        $adminModel = D("admin");
        if(!$adminModel->check_verify($_REQUEST["verify_code"], $id = '')){
            die(json_encode(array("status"=>0,"info"=>"验证码有误")));
        }
        if(!$admin_info = $adminModel->checkAdmin($_REQUEST)){
            die(json_encode(array("status"=>0,"info"=>"用户名或密码有误")));
        }
        session('admin_info',$admin_info);
        die(json_encode(array("status"=>1,"info"=>"登录成功","url"=>"/admin/index/index")));
    }

    //管理员登出
    public function logout(){
        $admin_info = session("admin_info");
        unset($admin_info);
        session(null);
        session_destroy();
        $this->redirect("/admin/admin/login");
    }
}
