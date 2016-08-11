<?php

/**
 * 后台借款人模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Borrower extends backend{
    //表名
    protected $tableName = 'user';

    //获取借款人列表
    function getBorrowerList(){
        //查询条件
        if(@$_REQUEST["user_name"]){
            $condition["user_name"] = $_REQUEST["user_name"];
        }

        //排序
        $_order = $_REQUEST["_order"] ? $_REQUEST["_order"] : "id";
        $_sort = $_REQUEST["_sort"] == 1 ? "asc" : "desc";

        //借款人类型user_type = 1
        $condition["user_type"] = 1;
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
            $borrower_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            foreach($borrower_list as $key=>$val){
                $borrower_list[$key]['status_desc'] = $val['status'] ? "是" : "否";
                $borrower_list[$key]['is_effect_desc'] = $val['is_effect'] ? "有效" : "无效";
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['borrower_list'] = $borrower_list;
        }
        return $return;
    }

    //添加数据
    function addBorrower(){
        $data = $this->create();
        //数据处理
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }

    }


    //获取借款人信息
    function getBorrowerInfo($id){
       return $this->find($id);
    }

    //编辑修改借款
    function editBorrower(){
        $data = $this->create();

        if($id = $this->save($update_data)){
            return $id;
        }else{
            return false;
        }
    }
}
