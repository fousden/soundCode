<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceAction
 *
 * @author panshenglei
 */
class ServiceAction extends CommonAction
{
    public function __construct() {
        parent::__construct();
        require_once APP_ROOT_PATH . "/system/libs/user.php";
    }

    public function index() {

        import("ORG.Util.Page");       //导入分页类
        $field = array('fanwe_user.real_name','fanwe_user.id as user_id1','fanwe_incharge_fail_feedback.login_ip','fanwe_user.mobile','fanwe_incharge_fail_feedback.feedback',
            'fanwe_incharge_fail_feedback.user_name','fanwe_incharge_fail_feedback.id',
            'fanwe_incharge_fail_feedback.order_id','fanwe_incharge_fail_feedback.create_time',
            'fanwe_incharge_fail_feedback.payment_type','fanwe_bank.name',
            'fanwe_incharge_fail_feedback.fail_reason','fanwe_incharge_fail_feedback.user_agent');
        $where = "fanwe_incharge_fail_feedback.id > 0 ";
        if($_REQUEST['real_name']||$_REQUEST['user_name']||$_REQUEST['email']||$_REQUEST['mobile']||$_REQUEST['begin_time']||$_REQUEST['end_time']||$_REQUEST['order_id']){
            if(!empty($_REQUEST['real_name'])){

                $real_name = trim($_REQUEST['real_name']);
                $where .= "and fanwe_user.real_name = '{$real_name}' ";
            }
            if(!empty($_REQUEST['user_name'])){
                $user_name = trim($_REQUEST['user_name']);
                $where .= "and fanwe_user.user_name = '{$user_name}' ";
            }
            if(!empty($_REQUEST['mobile'])){
                $mobile = trim($_REQUEST['mobile']);
                $where .= "and fanwe_user.mobile = '{$mobile}' ";
            }
            if(!empty($_REQUEST['order_id'])){
                $order_id = trim($_REQUEST['order_id']);

                $where .= "and fanwe_incharge_fail_feedback.order_id = {$order_id} ";
            }
            if(!empty($_REQUEST['begin_time'])){
                $begin_time = strtotime($_REQUEST['begin_time']);
                $where .= "and fanwe_incharge_fail_feedback.create_time > '{$begin_time}' ";
            }
            if(!empty($_REQUEST['end_time'])){
                $end_time = strtotime($_REQUEST['end_time']) + 86399;
                $where .= "and fanwe_incharge_fail_feedback.create_time < '{$end_time}' ";
            }
            $count = M("incharge_fail_feedback")->field($field)
                    ->join(array("fanwe_user ON fanwe_incharge_fail_feedback.user_name = fanwe_user.user_name",'fanwe_bank ON fanwe_bank.fuyou_bankid = fanwe_incharge_fail_feedback.bank_id'),'INNER')
                    ->where($where)->count();
            $Page = new Page($count);
            $show       = $Page->show();

            $nowPage = isset($_GET['p'])?$_GET['p']:1;
            $pageStart = ($nowPage - 1) * ($Page->listRows);
            $res = M("incharge_fail_feedback")->field($field)
                    ->join(array("fanwe_user ON fanwe_incharge_fail_feedback.user_name = fanwe_user.user_name",'fanwe_bank ON fanwe_bank.fuyou_bankid = fanwe_incharge_fail_feedback.bank_id'),'INNER')
                    ->where($where)->limit($pageStart.','.$Page->listRows)->select();


        }else{
        $count = M("incharge_fail_feedback")->field($field)
                ->join(array("fanwe_user ON fanwe_incharge_fail_feedback.user_name = fanwe_user.user_name",'fanwe_bank ON fanwe_bank.fuyou_bankid = fanwe_incharge_fail_feedback.bank_id'),'INNER')
                ->count();
        $Page = new Page($count);
        $show       = $Page->show();

        $nowPage = isset($_GET['p'])?$_GET['p']:1;
        $pageStart = ($nowPage - 1) * ($Page->listRows);

        $res = M("incharge_fail_feedback")->field($field)
                ->join(array("fanwe_user ON fanwe_incharge_fail_feedback.user_name = fanwe_user.user_name",'fanwe_bank ON fanwe_bank.fuyou_bankid = fanwe_incharge_fail_feedback.bank_id'),'INNER')
                ->order('create_time desc')->limit($pageStart.','.$Page->listRows)->select();
    }

        $list2 = array();
        foreach ($res as $k=>$v) {


            if($v['payment_type']==1){
                $v['payment_type'] = '借记卡支付';
            }
            if($v['payment_type']==2){
                $v['payment_type'] = '企业网银支付';
            }
            if($v['fail_reason']==1){
                $v['fail_reason'] = '超出充值限额';
            }
            if($v['fail_reason']==2){
                $v['fail_reason'] = '账户信息不符';
            }
            if($v['fail_reason']==3){
                $v['fail_reason'] = '终止操作';
            }
            if($v['fail_reason']==4){
                $v['fail_reason'] = '网络异常';
            }
            if($v['fail_reason']==5){
                $v['fail_reason'] = '其他';
            }
            $v['payment_type'] = $v['payment_type']. '</br>' .$v['name']. '</br>' .$v['fail_reason'];
            array_splice($v,3,1);
            array_splice($v,9,2);


            array_push($list2,$v);

    }

        $this->assign('page',$show);
        $this->assign('list', $list2);
        $this->display();
    }

    public function edit() {
            $id = intval($_REQUEST ['id']);


            $vo = M(incharge_fail_feedback)->join("fanwe_user ON fanwe_incharge_fail_feedback.user_name = fanwe_user.user_name")->where("fanwe_incharge_fail_feedback.id = {$id}")->find();

            $this->assign ( 'vo', $vo );
//                        echo '<pre>';
//            print_r($vo);die;
            $this->display ();
    }
    
    //更换银行卡申请列表
    public function changeBank(){
        $model = D('user_bank_examine');
        //查询条件
        if (trim($_REQUEST['user_name']) != '') {
            $user_id = M("user")->where(array("user_name"=>trim($_REQUEST['user_name']),"is_effect"=>1,"is_delete"=>0))->getField("id");
        }
        if (trim($_REQUEST['mchnt_txn_ssn']) != '') {
            $map["mchnt_txn_ssn"] = trim($_REQUEST['mchnt_txn_ssn']);
        }
        if (trim($_REQUEST['mobile']) != '') {
            $user_id = M("user")->where(array("mobile"=>trim($_REQUEST['mobile']),"is_effect"=>1,"is_delete"=>0))->getField("id");
        }
        if($user_id){
            $map["user_id"] = $user_id;
        }
        $this->_list($model, $map, $sortBy = 'is_effect');  
        $this->display();
    }
    //查看银行卡申请详情
    function show_detail(){
        $id = intval($_REQUEST['id']);
        $user_bank_examine = M("user_bank_examine")->getById($id);
        $user_info = M("user")->getById($user_bank_examine["user_id"]);
        //查询修改银行卡申请审核结果
        require_once APP_ROOT_PATH . "system/payment/fuyou.php";
        $fuyou = new fuyou();
        $bankResult = $fuyou->queryChangeCard($user_info,$user_bank_examine['mchnt_txn_ssn']);
        $bankResultArr = objectToArray($bankResult);
        $this->assign("bankResult", $bankResultArr["plain"]);
        $this->display();
    }
}
