<?php

namespace wap\controller;


class Index extends \mapi\controller\Index{

    public function index() {
//        echo 111;die;
//        echo '<pre>';var_dump($_REQUEST);echo '</pre>';die;
//        $this->assign("data",parent::index());
        return $this->fetch();
    }
    
    public function test(){
        echo '<pre>';var_dump($_SERVER,$_REQUEST);echo '</pre>';die;
    }

}
