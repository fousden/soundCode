<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MobileBlacklistModel extends BaseModel {

    //根据手机来获取今天发送短信的条数
    public function getInfoByMobile($mobile) {
        $sql_str = "select id from " . DB_PREFIX . "mobile_blacklist where mobile='$mobile' and is_delete=0 limit 1";
        return $GLOBALS['db']->getOne($sql_str);
    }

    public function addMobile($mobile,$ip=CLIENT_IP) {
        $res = $this->getInfo($mobile);
        $date['mobile'] = $mobile;
        if($ip)$date['mobile_ip']=$ip;
        if (!$res['black_count']) {
            $date['add_time'] = time();
            $date['update_time'] = time();
            $date['black_count'] = 1;
            $GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_blacklist", $date, "INSERT");
        } else {
            $date['id'] = $res['id'];
            $date['black_count'] = $res['black_count'] + 1;
            $date['is_delete'] = 0;
            $date['update_time'] = time();
            $where = " id=" . $res['id'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_blacklist", $date, "UPDATE", $where);
        }
    }

    public function getInfo($mobile) {
        $sql_str = "select id,black_count,remove_time from " . DB_PREFIX . "mobile_blacklist where mobile='$mobile' limit 1";
        return $GLOBALS['db']->getRow($sql_str);
    }

}
