<?php

/*
 * 本类存放所有的统计内容
 * functioname($time)   $time 为指日期
 *
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'IosChannel.class.php';


class staticProvinceRun {
    

    function province($time = '') {
        if(!$time){
            $end_time = strtotime(date('Y-m-d H').":00:00");
            $strat_time =strtotime("-1 hour",$time);
        }else{
            $strat_time =strtotime("-1 hour",$time);
            $end_time = $time;
        }
        $sql_deal_load = "select dl.user_id,sum(dl.money) as money from fanwe_deal_load dl where dl.create_time >= " . $strat_time. " and dl.create_time <= " . $end_time. " and dl.contract_no != '' and dl.is_auto = 0 GROUP BY dl.user_id";
        $deal_load =$GLOBALS['db']->getAll($sql_deal_load);
        
        $data=array();
        foreach($deal_load as $key => $val){
            $sql_data = "select umh.province,umh.city from fanwe_user u LEFT JOIN fanwe_user_mobile_homeaddress umh on u.mobile=umh.mobile where u.id = " .$val['user_id'];
            $data =$GLOBALS['db']->getRow($sql_data);
            if(!($data['province']) || !($data['city'])){
                $data['province']="钓鱼岛";
                $data['city']    ="钓鱼岛";
            }
            $deal_load[$key]['province']=$data['province'];
            $deal_load[$key]['city']=$data['city'];
        }
        
        $province=array();
        foreach($deal_load as $key => $val){
            $sql_province = "select umh.province,umh.city from fanwe_user u LEFT JOIN fanwe_user_mobile_homeaddress umh on u.mobile=umh.mobile where u.id = '" .$val[user_id] ."'";
            $province[$key] =$GLOBALS['db']->getRow($sql_province);
            if(!($province[$key]['province']) || !($province[$key]['city'])){
                $province[$key]['province']="钓鱼岛";
                $province[$key]['city']    ="钓鱼岛";
            }
        }
        foreach($province as $key => $val){
            foreach($province as $k=>$v){
                if($key != $k && $val['province'] == $v['province'] && $val['city'] == $v['city']){
                    unset($province[$key]);
                }
            }
        }

        foreach($province as $key => $val){
            $province[$key]['money'] = 0;
            $province[$key]['people'] = 0;
             foreach($deal_load as $k=>$v){  
                 if($val['province'] == $v['province'] && $val['city'] == $v['city']){
                     $province[$key]['money'] += $v['money'];
                     $province[$key]['people']++;
                 }
             }
        }
 
        $time = to_date($end_time, "Y-m-d H");
        $time = str_replace(" ", '-', $time);
        $time_arr = explode('-', $time);
        $time = $time_arr[0] . $time_arr[1] . $time_arr[2] . $time_arr[3];
        
        $insertdata['create_time'] = $time;
        $insertdata['year'] = $time_arr[0];
        $insertdata['month'] = $time_arr[1];
        $insertdata['day'] = $time_arr[2];
        $insertdata['hour'] = $time_arr[3];
               
        foreach($province as $key => $val){
            $insertdata['province'] = trim($val['province']);
            $insertdata['city'] = trim($val['city']);
            for ($i = 1; $i <= 2; $i++) {
                if ($i == 1) {
                    $insertdata['value'] = $val['money'];
                    $statistical_money = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_province where data_type = 1 and create_time = '".$time."' and province = '" .$val['province'] . "' and city = '" .$val['city'] . "'");        
                    if(!$statistical_money){
                        $insertdata['data_type'] = 1;
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_province", $insertdata, "INSERT");
                    }else{
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_province", $insertdata, "UPDATE", ' `data_type` ="' . $statistical_province['data_type'] . '"  and create_time="'.$time.'"');
                    }

                } else{
                    $insertdata['value'] = $val['people'];
                    $statistical_money = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."statistical_province where data_type = 2 and create_time = '".$time."' and province = '" .$val['province'] . "' and city = '" .$val['city'] . "'");        
                    if(!$statistical_money){
                        $insertdata['data_type'] = 2;
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_province", $insertdata, "INSERT");
                    }else{
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_province", $insertdata, "UPDATE", ' `data_type` ="' . $statistical_province['data_type'] . '"  and create_time="'.$time.'"');
                    }
                } 
            }
        }
    }
}