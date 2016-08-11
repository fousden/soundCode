<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserModel
 *
 * @author lujun
 */
class devicePushModel extends BaseModel {

    protected $tableName = 'device_push';

    /**
     * 通过活动类型，用户id向用户发放红包
     * @param type 活动类型
     * @param user_id 用户id
     * @return
     */
    public function add($data) {
        $data['update_time']=  time();
        $data['device_type']= $data['device_type']=='ios'?2:1;
        $data['ip']= get_client_ip();
        $data['city']= getIpAddr($data['ip']);
        if ($this->getdDeviceInfoByUserId($data['user_id'])) {
            $GLOBALS['db']->autoExecute($this->getTableName(), $data, "UPDATE","user_id=".$data['user_id']);
        } else {
            $data['create_time']= time();
            $GLOBALS['db']->autoExecute($this->getTableName(), $data, "INSERT");
        }
    }

    public function getdDeviceInfoByUserId($user_id) {
        $sql_str = "select * from " .$this->getTableName(). " where user_id=" . $user_id . " order by update_time desc limit 1";
        return $GLOBALS['db']->getRow($sql_str);
    }

}
