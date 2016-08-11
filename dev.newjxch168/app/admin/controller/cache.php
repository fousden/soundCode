<?php

namespace admin\controller;

class cache extends \base\controller\backend {

    public function index() {
        return $this->fetch();
        clear_dir_file(APP_PATH . "runtime/cache/");
        clear_dir_file(APP_PATH . "runtime/template/");
        alert("清除缓存成功","back");
    }

    public function clear_all() {
        clear_dir_file(APP_PATH . "runtime/cache/");
        clear_dir_file(APP_PATH . "runtime/template/");
        header("Content-Type:text/html; charset=utf-8");
        exit("<div style='line-height:50px; text-align:center; color:#f30;'>清除缓存成功</div><div style='text-align:center;'><input type='button' onclick='$.weeboxs.close();' class='button' value='关闭' /></div>");
    }
    
    public function clear_file() {
        $file=ROOT.$_GET['file'].'/';
        clear_dir_file($file);
        echo $file;
    }
    
    public function clear() {
        clear_dir_file(APP_PATH . "runtime/cache/");
        clear_dir_file(APP_PATH . "runtime/template/");
    }

}
