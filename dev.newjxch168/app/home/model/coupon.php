<?php

namespace home\model;

/*
 * 用户的优惠劵模型
 * 
 */

class Coupon extends \base\model\frontend {

    protected $tableName = "user_coupon";
    private $where = array();

    public function _initialize() {
        parent::_initialize();
        $time=  time();
        $this->where = array(
            'status' => 0,
            'load_id' => 0,
            'start_time' => array("lt", $time),
            'end_time' => array("gt", $time),
        );
    }

    public function getCouponInfoById($id) {
        $time = time();
//        $where['status'] = 0;
//        $where['load_id'] = 0;
//        $where['start_time'] = array("lt", $time);
//        $where['end_time'] = array("gt", $time);
        $where=  $this->where;
        $where['id'] = $id;
        return $this->where($where)->find();
    }

    public function getCouponList($where = '',$id=0) {
        if (!$where) {
            $where=  $this->where;
        }
        if($id){
            $where['user_id']=$id;
        }
        return $this->where($where)->group("coupon_type")->select();
        
        
    }

}
