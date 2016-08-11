<?php
namespace admin\controller;
use \base\controller\backend;

class Index extends backend
{
    //后台首页
    public function index()
    {
        return $this->fetch();
    }

    //后台首页顶部
    public function top()
    {
        $indexModel = D("index");
        $admin_info = session("admin_info");
        //一级菜单
        $one_menu_list = $indexModel->getOneLevelMenu($admin_info);
        $this->assign("menu_list",$one_menu_list);
        return $this->fetch();
    }

    //后台首页左侧
    public function left()
    {
        @$one_level_menu_id = $_REQUEST["menu_id"];
        $indexModel = D("index");
        //二级菜单及三级菜单
        $two_menu_list = $indexModel->getTwoLevelMenu($one_level_menu_id);
        $this->assign("menu_list",$two_menu_list);
        return $this->fetch();
    }

    //后台首页内容
    public function main()
    {
        return $this->fetch();
    }

    //后台首页底部
    public function footer()
    {
        return $this->fetch();
    }

    //后台首页拖拽
    public function drag()
    {
        return $this->fetch();
    }

    //待办事项
    public function todo()
    {
        return $this->fetch();
    }
}
