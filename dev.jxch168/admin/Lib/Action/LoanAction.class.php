<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/30
 * Time: 10:19
 */
class LoanAction extends CommonAction{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
//        echo get_loan_name();
        import("ORG.Util.Page");
        $loan = M('loan');
        $name       = isset($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
        $mobile     = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time   = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        $where = array();
        if (!empty($name)) {
            $where['loan_name'] = array('eq',$name);
        }
        if (!empty($mobile)) {
            $where['mobile'] = array('eq',$mobile);
        }
        if (!empty($begin_time) && !empty($endtime)) {
            $begin_time = strtotime($begin_time);
            $end_time   = strtotime($end_time);
            $where['apply_time'] = array('gt',$begin_time);
            $where['apply_time'] = array('lt',$end_time);
        }
        $count     = $loan->where($where)->getField('count(*)');
        $Page      = new Page($count);
        $show      = $Page->show();
        $nowPage   = isset($_GET['p']) ? $_GET['p'] : 1;
        $pageStart = ($nowPage - 1) * ($Page->listRows);
//        $Page->listRows=3;
        $loan_info = $loan->where($where)->order("apply_time desc")->limit($pageStart . ',' . $Page->listRows)->select();
//        $data['list'] = $loan_info;
        $this->assign("data",$loan_info);
        $this->assign('page', $show);
        $this->display();
    }
}