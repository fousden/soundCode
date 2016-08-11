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
class ActivityConfModel extends BaseModel {

    protected $tableName = 'activity_conf';

    /**
     * 通过活动类型，用户id向用户发放红包
     * @param type 活动类型
     * @param user_id 用户id
     * @return
     */
    public function ActivityConfByType($type = 1, $user_id = 0) {
        $activity_parameter = $this->getParameterByType($type);
        if (!$activity_parameter) {
            $result['status'] = 0;
            $result['user_id'] = $user_id;
            return $result;
        }
        $activity_arr = explode(',', $activity_parameter);
        if ($type == 1) {
            $bonus_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user_bonus where user_id=$user_id and bonus_type=7");
            if ($bonus_id > 0) {
                $result['status'] = 0;
                $result['user_id'] = $user_id;
                return $result;
            }
            $data['user_id'] = $user_id;
            $data['reward_name'] = $activity_arr[0] . "元现金红包";
            $data['money'] = $activity_arr[0];
            $data['bonus_type'] = 7;
            $data['min_limit'] = $activity_arr[0] * 100;
            $data['cash_type'] = 1;
            $data['start_time'] = time();
            $data['generation_time'] = time();
            $data['remark'] = "通过实名获得";
            if (!$activity_arr[2]) {
                $data['end_time'] = $data['start_time'] + 86400 * 90;
            } else if ($activity_arr[2] == 1) {
                $data['end_time'] = $data['start_time'] + 86400 * $activity_arr[3];
            } else if ($activity_arr[2] == 2) {
                $data['end_time'] = strtotime($activity_arr[3]);
            }
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $data, "INSERT");
            if ($res) {
                $result['status'] = 1;
            } else {
                $result['status'] = 0;
            }
        }
        $result['user_id'] = $user_id;
        $result['name'] = $data['reward_name'];
        $result['date'] = (int) ($data['end_time'] - time()) / 86400;
        return $result;
    }

    /**
     * 通过活动类型获取该活动的参数
     * @param type 活动类型
     * @return
     */
    public function getParameterByType($type = 1) {
        $time = time();
        $sql = "select parameter from " . DB_PREFIX . "activity_conf where start_time<$time and end_time>$time and status=1 and type=$type limit 1";
        return $GLOBALS['db']->getOne($sql);
    }

    /**
     * 通过活动类型获取该活动的开始时间，结束时间，活动参数
     * @param type 活动类型
     * @return
     */
    public function getInfoByType($type = 1,$time_stamp = "") {
        
        if($time_stamp){
            $time = $time_stamp;
        }else{
            $time = time();
        }
        $key=activityConf($type);
        if(empty($key) || !$key){
            $key=$type;
        }
        $sql = "select start_time,end_time,parameter,type from " . DB_PREFIX . "activity_conf where start_time<=$time and end_time>=$time and status=1 and `key`='$key' order by start_time asc limit 1";
        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 通过活动类型，用户id向用户发放红包
     * @param type 活动类型
     * @param user_id 用户id
     * @return
     */
    public function GlobalActivity($type = 1, $user_id = 0) {
        $activity_parameter = $this->getParameterByType($type);
        if (!$activity_parameter) {
            $result['code'] = 0;
            $result['errmsg'] = "不在活动期间内";
            return $result;
        }
        $activity_arr = explode(',', $activity_parameter);
        if ($type == 2) {
            $user_name=$GLOBALS['db']->getOne("select user_name from ".DB_PREFIX."user where id=$user_id");
            $data['user_id'] = $user_id;
            $data['user_name'] = $user_name;
            $data['coupon_name'] = $activity_arr[0] . "元抵现券";
            $data['face_value'] = $activity_arr[0];
            $data['coupon_type'] = 1;
            $data['min_limit'] = $activity_arr[0] * 100;
            $data['gain_time'] = time();
            $data['start_time'] = time();
            $data['coupon_desc'] = "注册获得";
            if (!$activity_arr[2]) {
                $data['end_time'] = $data['start_time'] + 86400 * 90;
            } else if ($activity_arr[2] == 1) {
                $data['end_time'] = $data['start_time'] + 86400 * $activity_arr[3];
            } else if ($activity_arr[2] == 2) {
                $data['end_time'] = strtotime($activity_arr[3]);
            }
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_coupon", $data, "INSERT");
            if ($res) {
                $result['code'] = 1;
                $result['errmsg'] = "操作成功";
            } else {
                $result['code'] = 0;
                $result['errmsg'] = "操作失败";
            }
        }
        return $result;
    }

}
