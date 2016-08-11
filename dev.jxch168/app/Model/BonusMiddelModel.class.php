<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BonusMiddelModel
 *
 * @author lujun
 */
class BonusMiddelModel extends BaseModel
{

    protected $tableName = 'user_bonus_middel';

    /**
     *
     * @param type $arr
     * `mobile` varchar(11) NOT NULL DEFAULT '0' COMMENT '手机号',
      `reward_name` varchar(255) NOT NULL COMMENT '奖励名称',
      `money` decimal(15,2) NOT NULL DEFAULT '0.00',
      `bonus_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '以fanwe_user_bonus 中bonus_type相应说明为准',
      `cash_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '以fanwe_user_bonus 中cash_type相应说明为准',
      `min_limit` decimal(15,2) DEFAULT NULL COMMENT '以fanwe_user_bonus 中min_limit相应说明为准', --  可填
      `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用 0未提取 1已提取', --  可填
      `remark` varchar(255) DEFAULT NULL COMMENT '备注',
     * @param type $ctime
     * @param type $days  周期 0 为无限
     * @return boolean
     */
    public function add($arr, $ctime = TIME_UTC, $days = 90)
    {
        if (empty($arr['mobile']) || empty($arr['reward_name']) || empty($arr['money']) || !isset($arr['bonus_type']) || !isset($arr['cash_type'])) {
            return false;
        }
        $arr['generation_time'] = $ctime; // `` int(11) DEFAULT '0' COMMENT '红包生成时间',
        $arr['use_days']        = $days;
        $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
        $adId                   = $GLOBALS['db']->insert_id();
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

        $obj = MO('Bonus');
        foreach ($list as $row) {
            $setArr = array('user_id'     => $userInfo['id']
                , 'reward_name' => $row['reward_name'],
                'money'       => $row['money'],
                'bonus_type'  => $row['bonus_type'],
                'cash_type'   => $row['cash_type'],
                'min_limit'   => $row['min_limit'],
                'remark'      => $row['remark'],
            );
            if ($obj->add($setArr, $row['use_days'])) {
                $this->extractionById($row['id'], 3, $row['generation_time']);
            } else {
                //TODO log
            }
        }
        return true;
    }

}
