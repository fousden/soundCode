<?php

namespace admin\controller;

/**
 * 菜单管理
 *
 * @author jxch
 */
class Menu extends \base\controller\backend {

    protected $tree_list;
    protected $field = "id,name,menu_level";
    protected $menu_model = "";

    public function _initialize() {
        //判断是否登录
        $this->menu_model = D('menu');
    }

    public function index() {
        $result = $this->menu_model->order(array("sort" => "desc"))->select(); //这里查询得到结果集，注意结果集为数组
        $field = "sort,id,name,menu_level,status,display";
        $menu_list = $this->get_menu_list($result, $field);
        $this->assign('list', $menu_list);
        return $this->fetch();
    }

    public function add() {
        if ($_REQUEST['submit']) {
            $data = $this->menu_model->create();
            $menu_level = (int) $this->menu_model->where(array("id" => $data['pid']))->getField("menu_level");
            $data['menu_level'] = $menu_level + 1;
            if ($this->menu_model->add($data)) {
                return $this->success("添加成功！", "", "/admin/menu/index");
            } else {
                return $this->error("添加失败，请重试！", "", "/admin/menu/add");
            }
        }
        $result = $this->menu_model->get_menu_list(); //这里查询得到结果集，注意结果集为数组
        $field = "id,name,menu_level";
        $menu_list = $this->get_menu_list($result, $field);
        $menu_info['pid'] = M("menu")->where(array("id" => $_GET['id']))->getField("pid");
        $menu_info['sort'] = $this->menu_model->max_sort() + 1;        
        $this->assign('menu_list', $menu_list);
        $this->assign('menu_info', $menu_info);
        return $this->fetch();
    }

    public function delete() {
        $ids = $_REQUEST['id'];
        if ($name = $this->menu_model->get_child_by_ids($ids)) {
            $data['status'] = 0;
            $data['info'] = "你删除的菜单{" . $name . "}下还有子菜单,请先删除子菜单后操作！";
        } else {
            if ($this->menu_model->delete($ids)) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
                $date['info'] = "操作失败，请重试！";
            }
        }
        echo json_encode($data);
    }

    public function edit() {
        if ($_REQUEST['submit']) {
            $data = $this->menu_model->create();
//            unset($data['submit']);
            $menu_level = (int) $this->menu_model->where(array("id" => $data['pid']))->getField("menu_level");
            $data['menu_level'] = $menu_level + 1;
            M("menu")->save($data);
            return $this->success("编辑成功！", "", "/admin/menu/index");
        }
        $result = $this->menu_model->get_menu_list(); //这里查询得到结果集，注意结果集为数组
        $field = "id,name,menu_level";
        $menu_list = $this->get_menu_list($result, $field);
        $menu_info = M("menu")->where(array("id" => $_GET['id']))->find();
        $this->assign('menu_list', $menu_list);
        $this->assign('menu_info', $menu_info);
        return $this->fetch('add');
    }

    public function map_show() {
        $field = "id,name,menu_level,pid";
        $where['status'] = 1;
        $where['display'] = 1;
        $result = $this->menu_model->where($where)->field($field)->order("sort desc")->select(); //这里查询得到结果集，注意结果集为数组
        $menu_list = $this->get_menu_list($result, $field);
        $this->assign('menu_list', $menu_list);
        return $this->fetch();
    }

    protected function get_menu_list($result, $field) {
        import("ORG.Tree");
        $tree = new \ORG\Tree($result);
        $arr = $tree->leaf();
        $this->field = $field;
        $this->get_tree_list($arr);
        return $this->tree_list;
    }

    public function test() {
        $list = $this->_Sql_list(D("menu"), "select * from jxch_menu");
//        $this->assign("default_map", $where='');
//        parent::index();
        return $this->fetch();
//        D("menu")->get_test_list();
    }

    protected function get_tree_list($data) {
        if ($this->field && !is_array($this->field)) {
            $this->field = explode(",", $this->field);
        }
        $tree_list = array();
        foreach ($data as $entry) {
            foreach ($this->field as $field) {
                $tree[$field] = $entry[$field];
            }
            $this->tree_list[] = $tree;
            if (isset($entry['child'])) {
                $this->get_tree_list($entry['child']);
            }
        }
    }

}
