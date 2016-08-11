<?php
namespace admin\controller;
use base\controller\backend;

/**
 * 我要贷款控制器
 *
 * @author jxch
 */

class Loan extends backend{
    function index(){
        $loan_list = M('loan')->select();
        $this->assign('list',$loan_list);
        return $this->fetch();
    }
}
