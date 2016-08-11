<?php

/**
 * 后台index model业务逻辑类
 *
 * @author jxch
 */

namespace admin\model;

use base\model\backend;

class Index extends backend {

    //获取权限一级菜单
    function getOneLevelMenu($admin_info) {
        $menu_model = M("menu");
        $menu_list = array();
        $menu_where['status'] = 1;
        $menu_where['display'] = 1;
        $menu_where['menu_level'] = 1;
        //超级管理员无视权限
        if ($admin_info['id'] != 0) {
            $where = array("role_id" => $admin_info["role_id"], "status" => 1);
            $menus = M("authority")->field("menu_id")->where($where)->select();

            $menuIdS = array_map('array_shift', $menus);
            unset($menus);
            $menu_where['id'] = array("in", $menuIdS);
        }
        $menu_list = $menu_model->where($menu_where)->select();
//        foreach ($menus as $key => $val) {
//            $menu_list[] = $menu_model->where($menu_where)->find($val["menu_id"]);
//        }

        return arr_sort($menu_list, "sort", "desc");
    }

    //获取权限二级菜单
    function getTwoLevelMenu($one_level_menu_id) {
        $menu_model = M("menu");
        $menus = $menu_model->where(array("pid" => $one_level_menu_id, "status" => 1,"display"=>1))->order("sort desc")->select();
        $menu_list = array();
        foreach ($menus as $key => $val) {
            $three_menu = $menu_model->where(array("pid" => $val["id"], "status" => 1,"display"=>1))->order("sort desc")->select();
            $menus[$key]["three_menu"] = $three_menu;
        }
        unset($three_menu);
        return $menus;
    }

}
