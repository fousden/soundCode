<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台loan 我要贷款控制器
 *
 * @author jxch
 */

class Loan extends frontend{
    
    function index(){
        if(IS_POST){
            $mobile = trim($_POST['mobile']);
            $res = M('Loan')->where(array("mobile"=>$mobile))->find();
            if($res){
                $data['response_code'] = 1;
                $data['show_err'] = "手机号已存在";
                ajax_return($data);
            }
            $loan_data['loan_name'] = trim($_POST['name']);
            $loan_data['mobile'] = $mobile;
            $loan_data['sex'] = $_POST['sex'];
            session('loan',$loan_data);
            ajax_return(0);
        }else{
            return $this->fetch();
        }   
    }
    function details(){
        if(IS_POST){
            $user_id = session('user_info.id');
            $loan_data = session('loan');
            $loan_data['user_id'] = $user_id ? $user_id:'';
            $loan_data['age'] = trim($_POST['age']);
            $loan_data['city'] = trim($_POST['city']);
            $loan_data['live_date'] = $_POST['live_time'];
            $loan_data['salary'] = $_POST['salary'];
            $loan_data['salary_way'] = $_POST['salary_way'];
            $loan_data['is_house'] = $_POST['fangdai_status'];
            $loan_data['is_house_repay'] = $_POST['fangdai_status_time'];
            $loan_data['house_periods'] = $_POST['fangdai_status_num'];
            $loan_data['is_car'] = $_POST['chedai_status'];
            $loan_data['is_car_repay'] = $_POST['chedai_status_time'];
            $loan_data['car_periods'] = $_POST['chedai_status_num'];
            $loan_data['is_car_insurance'] = $_POST['chexian'];
            $loan_data['is_life_insurance'] = $_POST['shouxian'];
            $loan_data['is_credit_card'] = $_POST['xinyongka'];
            $loan_data['money'] = $_POST['money'];
            $loan_data['apply_time'] = time();
            $res = M('loan')->add($loan_data);
            if($res){
                die(json_encode(1));
            }
        }else{
            return $this->fetch();
        }   
    }
    function success(){
        return $this->fetch();
    }
}
