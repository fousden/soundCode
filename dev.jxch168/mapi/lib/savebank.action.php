<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author ningchengzeng
 */
class savebank {
        /**
	 * 保存
	 */
	public function index(){
                $root = array();

                $email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

                $user = user_check($email,$pwd);
		        $user_id  = intval($user['id']);
                if ($user_id >0){
                    $data['bank_id'] = $GLOBALS['request']['bank_id'].'';
                    $data['real_name'] = trim($GLOBALS['request']['real_name']);

                    $data['region_lv1'] = intval($GLOBALS['request']['region_lv1']);
                    $data['region_lv2'] = intval($GLOBALS['request']['region_lv2']);

                    $data['bankzone'] = trim($GLOBALS['request']['bankzone']);
                    $data['bankcard'] = trim($GLOBALS['request']['bankcard']);
                    $data['user_id'] = $GLOBALS['user_info']['id'];

                    //判断通过哪个终端绑定银行卡
                    $bind_from = trim($GLOBALS['request']['_m']);
                    if($bind_from){
                            if($bind_from == "android"){
                                    $data['binding_source'] = 3;
                            }else if($bind_from == "ios"){
                                    $data['binding_source'] = 4;
                            }
                    }else{
                            $data['binding_source'] = 2;
                    }
                    require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
                    $payment_object = new Fuyou_payment;

                    if($data['bank_id'] == 0){
                        $root['response_code'] = 0;
                        $root['show_err'] = $GLOBALS['lang']['PLASE_ENTER_CARRY_BANK'];
                    }
                    else if($data['real_name'] == ""){
                        $root['response_code'] = 0;
                        $root['show_err'] = '请输入开户名';
                    }
                    /*else if($data['bankzone'] == ""){
                        $root['response_code'] = 0;
                        $root['show_err'] = '请输入开户行网点';
                    }*/
                    else if($data['bankcard'] == ""){
                        $root['response_code'] = 0;
                        $root['show_err'] = $GLOBALS['lang']['PLASE_ENTER_CARRY_BANK_CODE'];
                    }
                    else if($GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."user_bank WHERE bankcard='".$data['bankcard']."'  AND user_id=".$GLOBALS['user_info']['id']) > 0){
                        $root['response_code'] = 0;
                        $root['show_err'] = '该银行卡已存在';
                    }

                    $resp_code = $payment_object->fuyouRegAction($data,$GLOBALS['user_info']['id']);
                    if('0000' == $resp_code){
                            //绑卡时间
                            $data['binding_time'] = time();
                            $GLOBALS['db']->autoExecute(DB_PREFIX."user_bank",$data,"INSERT");
                            if($GLOBALS['db']->affected_rows()){
                                $root['response_code'] = 1;
                                $root['show_err'] = '绑卡开户成功';
                            }
                            else{
                                $root['response_code'] = 0;
                                $root['show_err'] = '绑卡开户保存失败';
                            }
                    } else {
                        $remind_codes = require_once APP_ROOT_PATH.'data_conf/remind_code.php';
                        $root['response_code'] = 0;
                        $root['show_err'] = show_fuyou_remind($remind_codes,$resp_code);
                    }
                }else{
                    $root['response_code'] = 0;
                    $root['show_err'] ="未登录";
                    $root['user_login_status'] = 0;
                }
                output($root);
	}
}
