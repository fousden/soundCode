<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BonusModel
 *
 * @author lujun
 */
class BonusModel extends BaseModel {

    protected $tableName = 'user_bonus';

    /**
     *
     * @param type $arr
     * `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户id',
      `reward_name` varchar(255) NOT NULL COMMENT '奖励名称',
      `money` decimal(15,2) NOT NULL DEFAULT '0.00',
      `bonus_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '红包类型 0满就送 1邀请注册 2邀请投资 3大转盘 4刮刮卡',
      `cash_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '提现方式 0手动提现 1自动提现',
      `min_limit` decimal(15,2) DEFAULT NULL COMMENT '金额限制，实际根据类型操作',
      `remark` varchar(255) DEFAULT NULL COMMENT '备注',
     * @param type $ctime
     * @param type $days  有效周期 0 为无限
     * @param type $cash_period 提现周期 默认为3天
     * @return boolean
     */
    public function add($arr, $days = 90, $cash_period = 3, $ctime = TIME_UTC) {
        if (empty($arr['user_id']) || empty($arr['reward_name']) || empty($arr['money']) || !isset($arr['bonus_type']) || !isset($arr['min_limit'])) {
            return false;
        }
        $arr['generation_time'] = $ctime;
        //如果是1自动提现的处理
        if ($arr['cash_type'] == 1) {
            $arr['cash_period'] = $cash_period; // 提现周期
            $arr['apply_time'] = $ctime; //'申请提现时间',
            $arr['release_date'] = strtotime(date('Y-m-d', $ctime)) + 60 * 60 * 24 * $arr['cash_period']; // '发放日期',
            $arr['status'] = 1;
        }

        //所有券默认有效期为90 0为不限
        if ($days > 0 && $arr['cash_type'] != 1) {
            $arr['start_time'] = $ctime;
            $arr['end_time'] = $ctime + 60 * 60 * 24 * $days;
        }
        if ($arr['bonus_type'] == 8) {
            $time_info = $GLOBALS['db']->getRow("select start_time,end_time from " . DB_PREFIX . "activity_conf where type=6 and status=1");
            $arr['end_time'] = $time_info['end_time'];
            $arr['start_time'] = $time_info['start_time'];
        }
        $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
        $adId = $GLOBALS['db']->insert_id();
        return $adId;
    }

    /**
     * 提取红包
     * @param type $uid
     * @param type $bonus_id
     * @return string
     */
    public function bonusWithdrawals($uid, $bonus_id) {

        $sql_bonus = "SELECT * FROM " . DB_PREFIX . "user_bonus WHERE id={$bonus_id}  and status = 0 and is_effect = 1";
        $bonus_info = $GLOBALS['db']->getRow($sql_bonus);

        if (empty($bonus_info)) {
            return "操作错误请刷新页面重试！";
        }
        if ($bonus_info['start_time'] > TIME_UTC || $bonus_info['end_time'] < TIME_UTC) {
            return "此红包已过期请刷新页面重试！";
        }
        if ($bonus_info['bonus_type'] == 8) {
            $sql_deal = "select * from " . DB_PREFIX . "deal_load where is_auto = 0 and contract_no != '' and user_id={$uid} and create_time >= " .
                    $bonus_info['start_time'] . " and create_time <= " . $bonus_info['end_time'];
            $dealInfo = $GLOBALS['db']->getAll($sql_deal);
            if (!$dealInfo) {
                return "在活动期间内，任意投资一笔，可领取红包，红包3个工作日内到账。红包不与其他抵用券、现金券冲突，可同时叠加使用。";
            }
        } else {
            //TODO test 2015-09-25
            $sql_deal = "SELECT *  FROM " . DB_PREFIX . "deal_load WHERE is_auto = 0 and contract_no != '' AND user_id={$uid} and create_time >= " .
                    strtotime(date('2015-09-25')) . " and bonus_withdrawals = 0 and  coupon_id in (0,-1) order by id  ";
            $dealInfo = $GLOBALS['db']->getAll($sql_deal);
            $min_limit = (int) $bonus_info['min_limit'];
            if (empty($dealInfo)) {
                return "未使用收益券或抵现券，累积投资{$min_limit}元以上(已领过红包的投资不算)，可激活此红包，红包激活后三个工作日内到账";
            }
            $ac = false;
            $ids = array();
            if ($bonus_info['min_limit'] > 0) {
                $ids = array();
                $allmoney = 0;
                foreach ($dealInfo as $row) {
                    $allmoney += $row['money'];
                    $ids[] = $row['id'];
                    if ($allmoney >= $bonus_info['min_limit']) {
                        $ac = true;
                        break;
                    }
                }
            } else {
                $ac = true;
            }

            if (true != $ac) {
                return "累积投资{$min_limit}元以上(已领过红包的投资不算)，激活此红包，红包激活后三个工作日内到账";
            }
            if ($ids) {
                $updateDealSql = " update " . DB_PREFIX . "deal_load set bonus_withdrawals = '" . $bonus_id . "' WHERE user_id={$uid}  and  id in (' " . implode("','", $ids) . "') ";
                $GLOBALS['db']->query($updateDealSql);
            }
        }


        // 更新优惠券状态
        $data['status'] = 1; //提现处理中.
        $data['apply_time'] = time(); //申请提现时间
        $data['release_time'] = strtotime("+3days", $data['apply_time']); //预计发放时间
        $data['release_date'] = strtotime(date('Y-m-d', $data['release_time'])); //预计发放日期
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $data, "UPDATE", "id=" . $bonus_id);
        return true;
    }

    /**
     * 以配置文件的形式添加红包
     * @param $user_info  array|int  用户信息或者用户id
     * @param $data       array  红包的信息(提现方式,备注信息,红包类型)
     * @param $conf_id    int  红包配置的id值
     * @return 返回最新的插入id
     * 
     * @example   $this->confAdd(121,array("cash_type"=>"提现方式 0手动提现 1自动提现","remark"=>"备注信息","bonus_type"=>"红包类型"),1);
     */
    public function confAdd($user_id, $data, $conf_id) {
        $user_bonus_conf = require_once APP_ROOT_PATH . "data_conf/user_bonus_conf.php";
        $conf_id = trim($conf_id);
        $bonus_data = $user_bonus_conf[$conf_id];
        $bonus_data['user_id'] = $user_id;
        $bonus_data['cash_type'] = $data['cash_type'];
        $bonus_data['remark'] = $data['remark'];
        $bonus_data['bonus_type'] = $data['bonus_type'];
        $bonus_data['generation_time'] = time();
        $bonus_data['start_time'] = strtotime(date("Y-m-d") . " 00:00:00");
        $bonus_data['end_time'] = $bonus_data['start_time'] + 86400 * 90 - 1;
        $GLOBALS['db']->autoExecute($this->getTableName(), $bonus_data, "INSERT");
        return $GLOBALS['db']->insert_id();
    }

}
