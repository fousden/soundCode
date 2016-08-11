<?php

/**
 * 合同范本模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Contract extends backend{
    //表名
    protected $tableName = 'contract';

    //获取标的列表
    function getContractList($request){
        //未删除的
        //$condition["is_delete"] = 0;
        //查询条件
        if(@$request["title"]){
            $condition["title"] = array('like','%'.$request["title"].'%');
        }   
        if(@$request["content"]){
            $condition["content"] = array('like','%'.$request["content"].'%');
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
            $contract_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //格式化数据
            foreach($contract_list as $key=>$val){
                $contract_list[$key]["content_desc"] = substr(strip_tags($val["content"]),0,24)."......";
            }
            
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['contract_list_new'] = $contract_list;
        }
        return $return;
    }

    //添加数据
    function addContract(){
        $data = $this->create();
        //数据处理
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }
    }

    //获取单个标的信息
    function getContractInfo($id){
        $contract_info = $this->find($id);
        return $contract_info;
    }

    //编辑修改借款
    function editContract(){
        $data = $this->create();
        if($id = $this->save($data)){
            return $id;
        }else{
            return false;
        }
    }
}
