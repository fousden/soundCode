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
class UserModel extends BaseModel {

    protected $tableName = 'user';

    /**
     * 通过用户id获取抽奖次数
     * @param type $user_id 用户id
     * @return $lottery_number 抽奖次数
     */
    public function getLotteryNumberByUserId($user_id) {
        $lottery_number = $GLOBALS['db']->getOne("select lottery_number from " . DB_PREFIX . "user where id=$user_id");
        return $lottery_number;
    }

    /**
     * 通过手机号获取用信息
     * @param type $mobile
     * @return type
     */
    public function userInfoByMobile($mobile) {
        $sql = "select * from " . $this->getTableName() . " where mobile ='{$mobile}'";
        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 通过账号获取用信息
     * @param type $email
     * @return type
     */
    public function getUserInfoByEmail($email, $field = "*") {
        $sql = "select $field from " . $this->getTableName() . " where user_name ='$email' or mobile='$email' or email='$email'";
        return $GLOBALS['db']->getRow($sql);
    }
    /**
     * 通过用户id获取用户信息
     * @param type $id
     * @return user_info
     */
    public function getUserInfoById($user_id, $field = "*") {
        $sql = "select $field from " . $this->getTableName() . " where id=$user_id";
        return $GLOBALS['db']->getRow($sql);
    }

    //将抽奖次数插入到数据库
    function insert_lottery_number($mobile, $type = 0) {
        if (!$mobile) {
            return false;
        }
        $date = to_date(get_gmtime(), "Y-m-d");
        if ($type == 1) {
            $count = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "lottery_number_log where type=$type and create_date='$date' and mobile='$mobile'");
            if ($count >= 5) {
                $result['status'] = 0;
                $result['lottery_number'] = $this->get_lottery_number($mobile);
                return $result;
            }
        } else if ($type == 3) {
            $count = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "lottery_number_log where type=$type and mobile='$mobile'");
            if ($count >= 1) {
                $result['status'] = 0;
                $result['lottery_number'] = $this->get_lottery_number($mobile);
                return $result;
            }
        }
        $data['mobile'] = $mobile;
        $data['create_time'] = get_gmtime();
        $data['create_date'] = $date;
        $data['type'] = $type;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "lottery_number_log", $data, "INSERT");
        $sql = "update " . DB_PREFIX . "user set lottery_number=lottery_number+1 where mobile=$mobile";
        $GLOBALS['db']->query($sql);
        $result['status'] = 1;
        $result['lottery_number'] = $this->get_lottery_number($mobile);
        return $result;
    }

//当转盘使用后，将抽奖次数减一
//update_lottery_number(861);
    function update_lottery_number($mobile) {
        $sql = "update " . DB_PREFIX . "user set lottery_number=lottery_number-1 where mobile=$mobile";
        $GLOBALS['db']->query($sql);
        return $this->get_lottery_number($mobile);
    }

    function get_lottery_number($mobile) {
        $lottery_number = $GLOBALS['db']->getOne("select lottery_number from " . DB_PREFIX . "user where mobile=$mobile");
        return $lottery_number;
    }

    function send_msg_register($mobile, $type = 1) {
        $activity_parameter = MO("ActivityConf")->getParameterByType($type);
        if ($activity_parameter) {
            $user_info = $this->userInfoByMobile($mobile);
            $activity_arr = explode(',', $activity_parameter);
            //如果有理财顾问
            if ($user_info['admin_id']) {
                $admin_info = $GLOBALS['db']->getRow("select adm_name,work_id from " . DB_PREFIX . "admin where id = '" . $user_info['admin_id'] . "'");
                $msg = "尊敬的" . $user_info['mobile'] . "，恭喜您成功注册金享财行。您的理财顾问是" . $admin_info['adm_name'] . "，工号：" . $admin_info['work_id'] . "。在完成实名认证后送您" . $activity_arr[0] . "元现金红包哦。回复TD拒收。";
            } else {
                $msg = MO("MsgTemplate")->getContentByName("TPL_SMS_REGISTER");
                $msg = sprintf($msg, $mobile, $activity_arr[0]);
            }
            $msg_data['dest'] = $mobile;
            $msg_data['title'] = "注册通知";
            $msg_data['content'] = addslashes($msg);
            $msg_data['create_time'] = get_gmtime();
            $msg_data['user_id'] = $user_info['id'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT");
        }
    }

    function send_msg_register_idno($mobile) {
        $user_info = $this->userInfoByMobile($mobile);
        $msg = MO("MsgTemplate")->getContentByName("TPL_SMS_REGISTER_idno");
        $msg = sprintf($msg, $mobile);
        $msg_data['dest'] = $mobile;
        $msg_data['title'] = "绑卡成功通知";
        $msg_data['content'] = addslashes($msg);
        $msg_data['create_time'] = get_gmtime();
        $msg_data['user_id'] = $user_info['id'];
        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT");
    }

}