<?php

/**
 * Description of CouponMiddle
 *
 * @author lujun
 */
class CouponMiddleModel extends BaseModel
{

    protected $tableName = 'user_coupon_middle';

    /**
     * 生成中间数据  生成时间为当前时间
     * @param type $arr
     * //     `mobile` varchar(100) NOT NULL DEFAULT '' COMMENT '手机号',
      //  `face_value` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '优惠券面值',
      //  `coupon_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '优惠券类型 1 收益券 2 抵现券 3加息券 ',
      //  `coupon_flag` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券标识',
      //  `coupon_name` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券名称',
      //  `coupon_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠券说明',
      //  `min_limit` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT '最低投资标准限额',
     * //  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用 0未提取 1已提取',  可填 可不填
     * `remark` varchar(255) DEFAULT NULL COMMENT '备注',
     *
     *
     * @return boolean
     */
    public function add($arr, $ctime = TIME_UTC, $days = 90)
    {
        if (empty($arr['mobile']) || empty($arr['face_value']) || empty($arr['coupon_type']) || empty($arr['coupon_flag']) || empty($arr['coupon_name']) || empty($arr['coupon_desc']) ||
                empty($arr['min_limit'])) {
            return false;
        }
        $arr['gain_time'] = $ctime;
        $arr['use_days']  = $days;
        $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
        $adId             = $GLOBALS['db']->insert_id();
        return $adId;
    }

    /**
     * 提取更新 by id
     * @param type $id
     */
    public function extractionById($id)
    {
        return $GLOBALS['db']->autoExecute($this->getTableName(), array('status' => 1), 'update', " id = '{$id}' ");
    }

    /**
     * 提取更新 by id
     * @param type $id
     */
    public function extractionByMobile($mobile)
    {
        return $GLOBALS['db']->autoExecute($this->getTableName(), array('status' => 1), 'update', " mobile = '{$mobile}' ");
    }

    /**
     * 根据ID获取数据
     * @param type $id
     * @return type
     */
    public function getById($id)
    {
        return $GLOBALS['db']->getRow("select * from " . $this->getTableName() . " where id = '{$id}' limit 1");
    }

    /**
     * 根据手机号获取
     * @param type $mobile
     * @return type
     */
    public function getByMobile($mobile, $status = -1)
    {
        $sql = "select * from " . $this->getTableName() . " where mobile = '{$mobile}'";
        if ($status >= 0) {
            $sql .= " and status = '" . $status . "'";
        }
        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * 从中间表到正式表
     * @param type $userInfo
     * array('id'=> 1,'user_name'=> 'user_name','mobile'=> '13869632565')
     */
    function middel2formal($userInfo)
    {
        if (empty($userInfo['id']) || empty($userInfo['user_name']) || empty($userInfo['mobile'])) {
            return false;
        }

        $list = $this->getByMobile($userInfo['mobile'], '0');
        if (empty($list)) {
            return true;
        }

        $obj = MO('Coupon');
        foreach ($list as $row) {
            echo "<BR>1<BR>";
            $setArr = array(
                'user_id'     => $userInfo['id']
                , 'user_name' => $userInfo['user_name'],
                'face_value'       => $row['face_value'],
                'coupon_type'  => $row['coupon_type'],
                'coupon_flag'   => $row['coupon_flag'],
                'coupon_name'   => $row['coupon_name'],
                'coupon_desc'      => $row['coupon_desc'],
                'min_limit'      => $row['min_limit'],
                'remark'      => $row['remark'],
            );
            if ($obj->add($setArr, $row['use_days'])) {
                $this->extractionById($row['id'],$row['gain_time']);
            } else {
                //TODO log
            }
        }
        return true;
    }

}
