<?php

namespace mapi\model;

class deal extends \think\model {

    protected $options = array(
        'where' => array(
            'is_delete' => 0,
            'is_effect' => 1,
            'is_hidden' => 0,
        ),
    );

    public function getDealList($where = '', $order = '', $limit = '') {
        $field = "id,name,borrow_amount_e2,rate_e2,repay_time,load_money_e2,deal_status,start_time";
        $data = $this->field($field)->where($where)->order($order)->limit($limit)->select();
        return $this->getDataFormat($data);
    }

    public function getDealStatusName($deal_status) {
//        0待发布，1进行中，2满标，3还款中，4已还清',
        $deal_status_conf = array("待发布", "进行中", "满标", "还款中", "已还清");
        return '(' . $deal_status_conf[$deal_status] . ')';
    }

    public function getDataFormat($data, $is_array = false) {
        foreach ($data as $key => $val) {
            $data[$key]['progress_point'] = (int) $val['load_money_e2'] / $val['borrow_amount_e2'] * 100;
            $data[$key]['borrow_amount_format'] = ($val['borrow_amount_e2'] / 1000000) . '万';
            $data[$key]['rate_foramt_w'] = ($val['rate_e2'] / 100) . '%';
            $data[$key]['rate_foramt'] = ($val['rate_e2'] / 100);
            $data[$key]['repay_time_type'] = 0;
            if ($val['deal_status'] == 0 || $val['start_time'] < $time) {
                $data[$key]['bfinish_time'] = "0";
            } else {
                $data[$key]['bfinish_time'] = "1";
            }
            unset($data[$key]['rate_e2'], $data[$key]['borrow_amount_e2'], $data[$key]['load_money_e2']);
        }
        if ($is_array) {
            return $data[0];
        } else {
            return $data;
        }
    }

    public function getDealInfoById($deal_id, $field = '') {
        if (!$field) {
            $field = "id,name,borrow_amount_e2,rate_e2,repay_time,load_money_e2,deal_status,min_loan_money_e2,end_time,agency_id";
        }
        $where['id'] = $deal_id;
        $data_info = M("deal")->field($field)->where($where)->find();
        $data_info['rate'] = num_format($data_info['rate_e2'] / 100);
        $data_info['rate_foramt_w'] = $data_info['rate'] . '%';
        $data_info['min_loan_money'] = num_format($data_info['min_loan_money_e2'] / 100);
        $data_info['jiexi_time'] = date("Y-m-d", $data_info['jiexi_date']);
        $data_info['qixi_time'] = date("Y-m-d", $data_info['qixi_date']);
        $data_info['last_mback_time'] = date("Y-m-d", $data_info['last_repay_date']);
        $data_info['progress_point'] = num_format(($data_info['load_money_e2'] / $data_info['borrow_amount_e2']) * 100);
        $data_info['borrow_amount_format'] = ($data_info['borrow_amount_e2'] / 1000000) . '万';
        $data_info['need_money'] = num_format(($data_info['borrow_amount_e2'] - $data_info['load_money_e2']) / 100);
        $data_info['bfinish_time'] = $data_info['deal_status'] == 1 ? 1 : 1;
        $remain_time = time() - $data_info['end_time'];
        $data_info['remain_time_format'] = $data_info['deal_status'] != 1 || $remain_time <= 0 ? "0天0时0分" : remain_time($data_info['end_time']);
        $data_info['loantype'] = "2";
        $data_info['guarantee'] = D("home/agency")->getAgencyNameById($data_info['agency_id']);
        unset($data_info['rate_e2'], $data_info['min_loan_money_e2'], $data_info['jiexi_date'], $data_info['qixi_date'], $data_info['last_repay_date'], $data_info['load_money_e2'], $data_info['borrow_amount_e2'], $data_info['end_time'], $data_info['agency_id']);
        return $data_info;
    }

}
