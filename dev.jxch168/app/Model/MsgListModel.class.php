<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MsgListModel extends BaseModel {
    
    //根据手机来获取今天发送短信的条数
    public function getCountByMobile($mobile,$send_type=0,$start_time="",$end_time=""){
        $date=date("Y-m-d");
        $remove_time=MO("MobileBlacklist")->getInfo($mobile)['remove_time'];
        if(date("Y-m-d",$remove_time)==$date){
            $start_time=$remove_time;
        }
        if(empty($start_time))$start_time= strtotime($date." 00:00:00");
        if(empty($end_time))$end_time=strtotime($date." 23:59:59");
        $sql_str="select count(*) from ".DB_PREFIX."deal_msg_list where dest='$mobile' and create_time>$start_time and create_time<$end_time and send_type=$send_type";
        return $GLOBALS['db']->getOne($sql_str);
    }
    
    
    
    
}

