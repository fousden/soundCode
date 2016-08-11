<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 借款分类控制器
 *
 * @author jxch
 */

class BorrowCate extends backend{

    //所有的借款分类列表
    public function index(){
        $borrowCateModel = D("borrow_cate");
        $return = $borrowCateModel->getDealCateList();
        //默认id 倒序
        $sort = $_REQUEST["_sort"] == 1 ? 0 : 1;

        $this->assign("sort",$sort);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list",$return['deal_cate_list']);
        return $this->fetch();
    }

    //新增借款分类
    function add(){
        $borrowModel = D("borrow_cate");
        if(IS_POST){
          if($borrowModel->addDealCate()){
              return $this->success("借款分类新增成功");
              die;
          }else{
              return $this->error("借款分类新增失败");
              die;
          }
        }else{
            return $this->fetch();
        }
    }

    //编辑贷款
    function edit($id){
        $borrowModel = D("borrow_cate");
        if(IS_POST){
          if($borrowModel->editDealCate()){
              return $this->success("借款分类修改成功");
              die;
          }else{
              return $this->error("借款分类修改失败");
              die;
          }
        }else{
            //准备展示数据
            $deal_cate = $borrowModel->getDealCateInfo($id);
            $this->assign("deal_cate", $deal_cate);
            return $this->fetch();
        }
    }
}
