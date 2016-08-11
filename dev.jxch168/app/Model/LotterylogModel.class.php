<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LotterylogModel
 *
 * @author lujun
 */
class LotterylogModel extends BaseModel {

    protected $tableName = 'user_lottery_log';

    /**
     * 插入抽奖日志
     * @param type $lotter_id 为多活动考虑共用日志，默认为活动开始日期
     * @param type $mobile 用户的手机号码
     * @param type $prize_name 奖品的名称
     * @param type $prize_type  奖品的说明
     * @param type $obj_id  '奖品的类型 1 收益券 2 抵现券  3 红包类型 4 实物',
     * @param type $prize_desc  奖励存的ID
     */
    public function add($arr, $prize_desc = '') {
        if (empty($arr['lotter_id']) || empty($arr['mobile']) || empty($arr['prize_name']) || empty($arr['prize_type'])) {
            return false;
        }
        $arr['create_time'] = TIME_UTC;
        $GLOBALS['db']->autoExecute($this->getTableName(), $arr, "INSERT");
        $adId = $GLOBALS['db']->insert_id();
        return $adId;
    }

    /**
     * 获取列表
     * @param type $type 获取类型
     * @param type $limit  限制条数
     * @return type
     */
    function listInfo($type = array(), $limit = 50, $where = '') {
        $contion = '';
        if ($type) {
            $contion = " where prize_type in ('" . implode("','", $type) . "')";
        }
        $sql = "select * from " . $this->getTableName() . $contion . $where . "  order by id desc limit " . $limit;
        return $GLOBALS['db']->getAll($sql);
    }
    
     
    /**
     * 用于获取获奖榜单列表
     * @param lotter_id $lotter_id 区分活动
     * @param type $limit  限制条数
     * @param maxLogId $maxLogId 
     * @return type
     */
    
    function getLogList($lotter_id=0,$limit=10,$maxLogId=0,$condition=''){
        $where='';
        if($lotter_id){
            $where.=" and lotter_id='$lotter_id' ";
        }
        $where.=$condition;
        $sql = "select id,create_time,mobile,prize_name from " . $this->getTableName(). " where id>".$maxLogId.$where."%s and prize_type!=6 and mobile!='' group by mobile order by id desc limit ";
        $list1=$GLOBALS['db']->getAll(sprintf($sql.ceil($limit/2)," "));
        $list2=$GLOBALS['db']->getAll(sprintf($sql.intval($limit/2)," and prize_type='4' "));
        return array_merge($list1,$list2);
    }

    function getInfoByMobile($mobile) {

        $sql = "select * from " . $this->getTableName() . " where mobile=$mobile order by create_time desc limit 5";
        $data['count'] = $GLOBALS['db']->getOne("select count(*) from " . $this->getTableName() . " where mobile=$mobile");
        $data['list'] = $GLOBALS['db']->getAll($sql);
        return $data;
    }

    function get_log_one_info($lotter_id = 1) {
        $time = TIME_UTC - 3;
        $sql = "select * from " . $this->getTableName() . " where create_time>$time and lotter_id=$lotter_id limit 1";
        return $GLOBALS['db']->getRow($sql);
    }

    function get_user_info($mobile, $limit, $lotter_id) {
        $where = " mobile=$mobile ";
        if($lotter_id){
           $where.= " and lotter_id=".$lotter_id;
        }
        
        $sql = "select * from " . $this->getTableName() . " where $where order by create_time desc ";
        if ($limit) {
            $sql .= " limit " . $limit;
        }
        $data['count'] = $GLOBALS['db']->getOne("select count(*) from " . $this->getTableName() . " where $where ");
        $data['item'] = $GLOBALS['db']->getAll($sql);
        return $data;
    }

    /**
     * 获取手机号对应活动的抽奖记录
     * @param type $mobile
     * @param type $lotterId
     */
    function getLogListByMobile($mobile, $lotterId) {
        $sql = "select * from " . $this->getTableName() . " where mobile=$mobile and lotter_id = $lotterId order by create_time desc ";
        return $GLOBALS['db']->getAll($sql);
    }

    /**
     * 获取奖口数量
     * @param type $prizeId
     */
    function getPrizeCntAll($lotter_id) {
        $sql = "select prize_name,count(*) as num from fanwe_user_lottery_log where lotter_id = '$lotter_id' group by prize_name ";
        return $GLOBALS['db']->getAll($sql);
    }

}
