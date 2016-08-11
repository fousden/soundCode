<?php
/**
 * 前台公用控制器
 *
 * @author jxch
 */

namespace base\controller;
use \base\controller\base;

class frontend extends base{

    //初始化前台数据
    function _initialize(){
        //网站导航
        $nav_list = M("nav")->where(array("pid"=>0,"is_effect"=>1,"status"=>1))->select();
        foreach($nav_list as $key=>$val){
            $sec_nav_list = M("nav")->where(array("pid"=>$val["id"],"is_effect"=>1,"status"=>1))->select();
            $nav_list[$key]["sec_nav"] = $sec_nav_list;
        }
        $this->assign("nav_list", $nav_list);
         //判断是否登录
        $this->is_login();
    }

    //是否登录
    function is_login() {
//        $user_info = $_SESSION["user_info"];
//        if (!$user_info) {
//            die($this->error("您尚未登录，请您登录后操作！", '', "/home/system/login"));
//        }
    }
}
