<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 借款人控制器
 *
 * @author jxch
 */

class Borrower extends backend{

    //所有的借款人列表
    public function index(){
        $borrowModel = D("borrower");
        $return = $borrowModel->getBorrowerList();
        //默认id 倒序
        $sort = $_REQUEST["_sort"] == 1 ? 0 : 1;

        $this->assign("sort",$sort);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list",$return['borrower_list']);
        return $this->fetch();
    }

    //新增借款人
    function add(){
        $borrowModel = D("borrower");
        if(IS_POST){
          if($borrowModel->addBorrower()){
              return $this->success("借款人新增成功");
              die;
          }else{
              return $this->error("借款人新增失败");
              die;
          }
        }else{
            return $this->fetch();
        }
    }

    //编辑借款人
    function edit($id){
        $borrowerModel = D("borrower");
        if(IS_POST){
          if($borrowerModel->editBorrower()){
              return $this->success("借款人修改成功");
              die;
          }else{
              return $this->error("借款人修改失败");
              die;
          }
        }else{
            //准备展示数据
            $borrower = $borrowerModel->getBorrowerInfo($id);
            $this->assign("borrower", $borrower);
            return $this->fetch();
        }
    }
}
