<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台 安全保障 safety控制器
 *
 * @author jxch
 */

class Safety extends frontend{
    function index(){
        return $this->fetch();
    }
}