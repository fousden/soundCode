<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/8
 * Time: 9:12
 */
class MapAction extends CommonAction{
    public function index(){
        // 将数据库中的数据查出来按照地域查出来
        $data = M("Statistical_province")->field("*,sum(value)")->where("data_type=1")->group("city")->select();
        $this->display();

    }
}