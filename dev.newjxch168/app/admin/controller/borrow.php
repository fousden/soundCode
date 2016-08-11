<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 借款控制器
 *
 * @author jxch
 */

class Borrow extends backend{

    //所有的借款列表
    public function index(){
        $borrowModel = D("borrow");
        $return = $borrowModel->getDealList();
        //默认id 倒序
        $sort = $_REQUEST["_sort"] == 1 ? 0 : 1;

        //准备展示数据
        //借款分类
        $deal_cate = M("deal_cate")->field("id,name")->where(array("is_effect"=>1))->select();
        $this->assign("deal_cate", $deal_cate);
        $this->assign("sort",$sort);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list",$return['deal_list_new']);
        return $this->fetch();
    }

    //新增借款
    function add(){
        $borrowModel = D("borrow");
        if(IS_POST){
          if($borrowModel->addDeal()){
              return $this->success("借款新增成功");
              die;
          }else{
              return $this->error("借款新增失败");
              die;
          }
        }else{
            //准备展示数据
            $data = $borrowModel->initAddDeal();
            $this->assign("data", $data);
            return $this->fetch();
        }
    }

    //编辑贷款
    function edit($id){
        $borrowModel = D("borrow");
        if(IS_POST){
          if($borrowModel->editDeal()){
              return $this->success("借款修改成功");
              die;
          }else{
              return $this->error("借款修改失败");
              die;
          }
        }else{
            //准备展示数据
            $data = $borrowModel->initAddDeal();
            $this->assign("data", $data);
            //准备展示数据
            $deal_info = $borrowModel->getDealInfo($id);
            $this->assign("deal_info", $deal_info);
            return $this->fetch();
        }
    }
}
