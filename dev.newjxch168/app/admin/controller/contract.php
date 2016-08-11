<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 合同范本控制器
 *
 * @author jxch
 */

class Contract extends backend{

    //所有的合同范本列表
    public function index(){
        $contractModel = D("contract");
        $return = $contractModel->getContractList($_REQUEST);
        //默认id 倒序
        $sort = $_REQUEST["_sort"] == 1 ? 0 : 1;

        //准备展示数据
        $this->assign("sort",$sort);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list",$return['contract_list_new']);
        return $this->fetch();
    }

    //新增合同范本
    function add(){
        $contractModel = D("contract");
        if(IS_POST){
          if($contractModel->addContract()){
              return $this->success("借款合同范本成功");
              die;
          }else{
              return $this->error("借款合同范本失败");
              die;
          }
        }else{
            //准备展示数据
            return $this->fetch();
        }
    }

    //编辑贷款
    function edit($id){
        $contractModel = D("contract");
        if(IS_POST){
          if($contractModel->editContract()){
              return $this->success("合同范本修改成功");
              die;
          }else{
              return $this->error("合同范本修改失败");
              die;
          }
        }else{
            //准备展示数据
            $contract_info = $contractModel->getContractInfo($id);
            $this->assign("contract", $contract_info);
            return $this->fetch("add");
        }
    }
    
    //显示合同范本详情
    function show_contract(){
        $contractModel = D("contract");
        $contract_id = $_REQUEST["id"];
        //准备展示数据
        $contract_info = $contractModel->getContractInfo($id);
        $this->assign("contract", $contract_info);
        return $this->fetch();
    }
}
