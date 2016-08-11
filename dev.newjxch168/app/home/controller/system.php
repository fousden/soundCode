<?php
namespace home\controller;
use base\controller\base;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/18
 * Time: 16:03
 */
class System extends base{

    public function __construct(){
        parent::__construct();
    }

    public function step_one(){
        return $this->fetch();
    }

    public function step_two(){
        // 注册第二步为开户，判断登录
        $user_info = $_SESSION['user_info'];
        if(empty($user_info)){
            $this->redirect('login');
        }
        $user_id = $user_info['user_id'];
        $condition['id'] = $user_id;
        $mobile = D("user")->where($condition)->getField("mobile");
        $bank_list = D("bank")->where("is_rec=1")->order("is_rec desc,sort desc,id asc")->select();
        $region_lv1 = D("district_info")->where("parentcode = 0")->select();
        $this->assign("mobile",$mobile);
        $this->assign("region_lv1",$region_lv1);
        $this->assign("bank_list",$bank_list);
        return $this->fetch();
    }

    public function get_city(){
        $arr = $_REQUEST;
        $city_list = D("system")->do_ajax($arr);
        print_r(json_encode($city_list));
    }

    public function register(){
        // 处理注册
        $arr = $_REQUEST;
        $info = D("system")->do_register($arr);// 返回一个带错误信息的数组
        if($info['response_code']=="1"){
            $this->redirect("step_two");
        }else{
            return $this->error($info["show_err"]); // 如果注册失败则输出具体失败的原因
            $this->redirect("step_one");
        }
    }

    public function step_three(){
        if(empty($_SESSION['user_info'])){
            $this->redirect("login");
        }
        $arr = $_REQUEST;
        $info = D("system")->do_account($arr);
        if($info['response_code']==="0000"){
            // 只有获取到0000才是开户成功
            $checked = intval($_REQUEST["agreement"]); // 判断是否勾选平安保险
            $this->assign('jump', '/');
            $this->assign("stay",0);
            $this->assign("checked", $checked);
            return $this->fetch();
        }else{
            // 如果为0则可以输出具体的错误
            return $this->error($info['show_err']);
        }
    }

    public function ajax(){
        // 处理登录注册用到的ajax请求
        $arr = $_REQUEST;
        $info = D("system")->do_ajax($arr);
        ajax_return($info);
    }
    public function login_out(){
        session_destroy();
        $this->redirect("login");// 跳转至登录页
    }
    public function login(){
        if($_SESSION['user_info']){
            $this->redirect("/"); // 如果session有值自动跳转到首页;
        }
        return $this->fetch();
    }
    public function do_login(){
        $arr = $_REQUEST;
        $info = D("system")->check_login($arr);
        ajax_return($info);
    }
}