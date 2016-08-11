<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class LogAction extends Action{
    public function __construct(){
    parent::__construct();
    $action = array();

    if (!in_array(ACTION_NAME, $action)) {
            $this->user_info=checkLogin();
        }
    }
    public function workingLog(){
        $user_info = $this->user_info;
        $user_id = $user_info['user_id'];
        $user_name = $user_info['user_name']; //  用户名
        $role_name = $user_info['role_name']; // 职位名称
        $department_name = $user_info['department_name']; // 部门名字
        $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : '1'; // 当前页数
        $page_size = 10;
        $offset = $page_size*($page-1);
        $field = "customer_id,customer_name,create_time,sign_address,agent,information,log_type,report_type";
        $log_info = M("workingLog")->field($field)->where(array("user_id"=>$user_id))->limit($offset,$page_size)->order('create_time desc')->select();
        $info = M("workingLog")->where(array("user_id"=>$user_id))->select();
        $count_info = count($info);
        $page_total = ceil($count_info/$page_size);
        foreach($log_info as $key=>$val){
            $log_info[$key]['status'] = time_tran($val['create_time']);
            $log_info[$key]['create_date'] = date("Y-m-d H:i",$val['create_time']);
        }
        $root['code'] = '1';
        $root['errmsg'] = '请求成功';
        if($log_info){
            $root['log_info'] = $log_info;
        }else{
            $root['log_info'] = array();
        }
        $root['page'] = (string)$page; // 当前页
        $root['page_size'] = (string)$page_size; // 每页显示的页数
        $root['page_total'] = (string)$page_total; // 总页数
        output($root);
    }
}
