<?php

/**
 * 后台导航模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Nav extends backend{
    //表名
    protected $tableName = 'nav';

    function getNavList($request,$condition = [],$order_by = ''){
        //排序
        $_order = $_REQUEST["_order"] ? $_REQUEST["_order"] : "sort";
        $_sort = $_REQUEST["_sort"] == 1 ? "asc" : "desc";

        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');

        if ($count > 0) {
            //创建分页对象
            if (!empty($request ['listRows'])) {
                $listRows = $request ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $nav_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //数据处理
            foreach($nav_list as $key=>$val){
                $nav_list[$key]["is_effect_desc"] = $val["is_effect"] ? "有效" : "无效";
                $nav_list[$key]["status_desc"] = $val["status"] ? "是" : "否";
                $nav_list[$key]["blank_desc"] = $val["blank"] ? "是" : "否";
                $nav_list[$key]["pid_name"] = $this->where(array("id"=>$val["pid"]))->getField("name");
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['nav_list'] = $nav_list;
        }
        return $return;
    }

    //新增导航
    function addNav($request){
        $data = $this->create();
        $data["url"] = "/".$data["module"]."/".$data["controller"]."/".$data["action"];
        //数据处理
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }
    }
    //编辑导航
    function editNav($request){
        $data = $this->create();
        $data["url"] = "/".$data["module"]."/".$data["controller"]."/".$data["action"];
        //数据处理
        if($id = $this->save($data)){
            return $id;
        }else{
            return false;
        }
    }
}
