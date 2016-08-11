<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/14
 * Time: 10:22
 */
class FundAction extends CommonAction{
    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $found = M("user");
        $found_list = $found->field("id,connect_time")->where("fund_flag=1")->order("connect_time desc")->select();
        $data['list'] = $found_list;
        $this->assign("data",$data);
        $this->display();
    }
}