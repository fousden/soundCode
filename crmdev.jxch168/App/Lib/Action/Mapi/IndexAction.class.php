<?php

/**
 * User Related
 * 用户相关模块
 *
 * */

class IndexAction extends Action {

    function __construct() {
        parent::__construct();
        $action = array("index");
        if (!in_array(ACTION_NAME, $action)) {
            $this->user_info = checkLogin();
        }
    }


    //测试连接接口
    public function index() {
        $root['code']=1;
        $root['errmsg']="连接成功！";
        output($root);
    }



}
