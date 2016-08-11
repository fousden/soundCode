<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InchargefeedbackModel
 *
 * @author panshenglei
 */
class InchargefeedbackModel extends BaseModel
{

    protected $tableName = 'incharge_fail_feedback';

    public function add($arr)
    {
        if(empty($arr['bank_id'])||empty($arr['user_name'])||empty($arr['create_time'])||empty($arr['payment_type'])||empty($arr['order_id'])||empty($arr['fail_reason'])){
            return false;
        }else{
            $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
            $adId = $GLOBALS['db']->insert_id();
            return $adId;
        }

    }

}
