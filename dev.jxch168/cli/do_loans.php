<?php

/*
 * 功能：满标放款异常处理
 * 时间：2015年10月23日 09:47
 * autor：chushangming
 */

    require_once 'init.php';

    $id = 400;
    $repay_start_time = date("Y-m-d");
    //更新标的状态 再次放款
    $GLOBALS['db']->query("update ".DB_PREFIX."deal set repay_start_time=0,next_repay_time=0,deal_status=2,is_has_loans=0 where id = '".$id."'");
    $result = do_loans($id, $repay_start_time);

    //投标 收益奖励
    $list = array();

    if($result['status'] == 1){
        $sql_list = "select * from " . DB_PREFIX . "deal_load where id = '17094' AND deal_id = '".$id."' ";
        $list = $GLOBALS['db']->getAll($sql_list);
        foreach ($list as $k => $v) {
            //投标人 VIP升级
            $tuser_id = $v['user_id'];
            $ttype = 1;
            $ttype_info = 2;
            $tresultdate = syn_user_vip($tuser_id, $ttype, $ttype_info);
        }
        //收取管理费
        //$this->get_manage($id);

        //VIP升级 -借款
        $user_id = $GLOBALS['db']->getOne("select user_id from ".DB_PREFIX."deal where id = '".$id."'");
        $type = 1;
        $type_info = 1;
        $resultdate = syn_user_vip($user_id, $type, $type_info);
        echo "标的".$id."放款成功！";
        file_put_contents(dirname(dirname(__FILE__)).'/log/fuyou/' . date('Y-m-d') . '_transferBmu_hands.log', "异常标的放款成功，POST:[标的id:" . $id . "];return:[" .json_encode($result). "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        die;

    }else{
        echo "标的".$id."放款失败！";
        file_put_contents(dirname(dirname(__FILE__)).'/log/fuyou/' . date('Y-m-d') . '_transferBmu_hands.log', "异常标的放款失败，POST:[标的id:" . $id . "];return:[" .json_encode($result). "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        die;
    }