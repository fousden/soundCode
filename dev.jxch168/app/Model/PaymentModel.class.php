<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of PaymentModel
 *
 * @author chushangming
 */
class PaymentModel extends BaseModel
{

    protected $tableName = 'payment_notice';

    //获取富友返回信息
    public function getFuyouRemind($resp_code,$is_carry = 1){
        if($resp_code){
            if("0000" == $resp_code){
                if($is_carry){
                    $resp_describle = "充值成功";
                }else{
                    $resp_describle = "提现成功";
                }
            }else{
                $remind_codes = require APP_ROOT_PATH.'data_conf/remind_code.php';
                $resp_describle = $remind_codes[$resp_code];
                if(!$resp_describle){
                    $resp_describle = $resp_code;
                }
            }
        }else{
            $resp_describle = "";
        }
        return $resp_describle;
    }

    //更新充值记录信息
    public function updatePayments($notice_sn,$resp_code){
        $payment_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."payment_notice where notice_sn = '".$notice_sn."'");
        //将错误信息更新到数据库
        $resp_describle = $this->getFuyouRemind($resp_code);
        $GLOBALS['db']->query("update " . DB_PREFIX . "payment_notice set resp_describle = '" . $resp_describle . "' where id = " . $payment_id);
    }

    //更新提现记录信息
    public function updateCarrys($notice_sn,$resp_code){
        $carry_id = $GLOBALS['db']->getOne("select id from ".DB_PREFIX."user_carry where mchnt_txn_ssn = '".$notice_sn."'");
        //将错误信息更新到数据库
        $resp_describle = $this->getFuyouRemind($resp_code,0);
        $GLOBALS['db']->query("update " . DB_PREFIX . "user_carry set resp_desc = '" . $resp_describle . "' where id = " . $carry_id);
    }
}