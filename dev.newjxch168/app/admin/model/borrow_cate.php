<?php

/**
 * 后台借款分类模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class BorrowCate extends backend{
     //表名
    protected $tableName = 'deal_cate';

    //获取标的列表
    function getDealCateList(){
        //查询条件
        if(@$_REQUEST["name"]){
            $condition["name"] = $_REQUEST["name"];
        }

        //排序
        $_order = $_REQUEST["_order"] ? $_REQUEST["_order"] : "id";
        $_sort = $_REQUEST["_sort"] == 1 ? "asc" : "desc";

        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $deal_cate_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;

            foreach($deal_cate_list as $key=>$val){
                $deal_cate_list[$key]["is_effect_desc"] = $val["is_effect"] ? "有效" : "无效";
                $deal_cate_list[$key]["pid_name"] = M("deal_cate")->where(array("id"=>$val["cate_id"]))->getField("name");
                $deal_cate_list[$key]["pid_name"] = $deal_cate_list[$key]["pid_name"] ? $deal_cate_list[$key]["pid_name"] : "无";
            }
            $return['deal_cate_list'] = $deal_cate_list;
        }
        return $return;
    }

    //添加借款分类
    function addDealCate(){
        $data = $this->create();
        //数据处理
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }
    }

    //修改借款分类信息
    function editDealCate(){
        $data = $this->create();
        //数据处理
        if($id = $this->save($data)){
            return $id;
        }else{
            return false;
        }
    }

    //获取标的分类信息
    function getDealCateInfo($id){
        return $this->find($id);
    }
}