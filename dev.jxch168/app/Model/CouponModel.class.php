<?php

/**
 * Description of Coupon
 *
 * @author lujun
 */
class CouponModel extends BaseModel {

    protected $tableName = 'user_coupon';

//  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属用户ID',
//  `user_name` varchar(100) NOT NULL DEFAULT '' COMMENT '所属用户名',
//  `face_value` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值',
//  `coupon_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '优惠券类型 1 收益券 2 抵现券 3加息券 ',
//  `coupon_flag` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券标识',
//  `coupon_name` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券名称',
//  `coupon_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠券说明',
//  `min_limit` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '最低投资标准限额',
    // `remark` varchar(255) DEFAULT NULL COMMENT '备注',
    public function add($arr, $days = 90, $ctime = TIME_UTC) {
        if (empty($arr['user_id']) || empty($arr['user_name']) || empty($arr['face_value']) || empty($arr['coupon_type']) || empty($arr['coupon_flag']) || empty($arr['coupon_name']) ||
                empty($arr['coupon_desc']) || !isset($arr['min_limit'])) {
            return false;
        }
        $arr['gain_time'] = $ctime;
        if ($days > 0) {
            $arr['start_time'] = $ctime;
            $arr['end_time'] = $ctime + 60 * 60 * 24 * $days;
        }

        $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
        $adId = $GLOBALS['db']->insert_id();
        return $adId;
    }

    /**
     * 以配置文件的形式添加优惠劵
     * @param $user_info  array|int  用户信息或者用户id
     * @param $data       array  优惠劵的信息（备注和标志）
     * @param $conf_id    int  优惠劵配置的id值
     * @return 返回插入id
     * 
     * @example   $this->confAdd(121,array("remark"=>"备注信息","coupon_flag"=>"优惠劵的标识"),1);
     */
    public function confAdd($user_info, $data, $conf_id) {
        $user_coupon_conf = require APP_ROOT_PATH . "data_conf/user_coupon_conf.php";
        $conf_id = trim($conf_id);
        //当$user_info不是一个数组类型时，则该参数传了user_id
        if (!is_array($user_info)) {
            $user_id = $user_info;
            $user_info = array();
            $user_info = $GLOBALS['db']->getRow("select id,user_name from " . DB_PREFIX . "user where id=$user_id");
        }
        $coupon_data = $user_coupon_conf[$conf_id];
        $coupon_data['user_id'] = $user_info['id'];
        $coupon_data['user_name'] = $user_info['user_name'];
        $coupon_data['remark'] = $data['remark'];
        $coupon_data['coupon_flag'] = $data['coupon_flag'];
        $coupon_data['gain_time'] = time();
        $coupon_data['start_time'] = strtotime(date("Y-m-d") . " 00:00:00");
        $coupon_data['end_time'] = $coupon_data['start_time'] + 86400 * 90 - 1;
        $GLOBALS['db']->autoExecute($this->getTableName(), $coupon_data, "INSERT");
        return $GLOBALS['db']->insert_id();
    }

}
