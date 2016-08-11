<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台首页 公用控制器
 *
 * @author jxch
 */

class Index extends frontend{

    function index(){
        $user_info = $_SESSION['user_info'];
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }
}
