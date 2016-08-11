<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 导航控制器
 *
 * @author jxch
 */

class Nav extends backend{
    //导航列表
    function index(){
        //导航列表
        $return = D("nav")->getNavList($_REQUEST);
        //默认id 倒序
        $sort = $_REQUEST["_sort"] == 1 ? 0 : 1;
        $this->assign("sort",$sort);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list",$return['nav_list']);
        return $this->fetch();
    }

    //新增导航
    function add(){
        $navModel = D("nav");
        if(IS_POST){
            if($navModel->addNav($_REQUEST)){
              return $this->success("导航新增成功");
          }else{
              return $this->error("导航新增失败");
          }
        }else{
           $nav_list = $navModel->select();
           $this->assign("nav_list",$nav_list);
           return $this->fetch();
        }
    }
    
    //编辑导航
    function edit(){
        $navModel = D("nav");
        if(IS_POST){
            if($navModel->editNav($_REQUEST)){
              return $this->success("导航修改成功");
          }else{
              return $this->error("导航修改失败");
          }
        }else{
           $nav_now = $navModel->find($_REQUEST["id"]);
           $nav_list = $navModel->select();
           $this->assign("nav_list",$nav_list);
           $this->assign("nav_now",$nav_now);
           return $this->fetch("add");
        }
    }
}

