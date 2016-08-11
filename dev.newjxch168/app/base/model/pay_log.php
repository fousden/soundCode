<?php

/**
 * 基础model业务逻辑类
 *
 * @author jxch
 */

namespace base\model;

class payLog extends \think\Model {

    //支付请求的数据以及返回数据入库
    function addLog($in_data, $put_str, $start_time, $end_time) {
        $dataObj = simplexml_load_string($put_str);
        $data_arr = object_to_linear_array($dataObj);
        $resp_code = $data_arr['resp_code'];
        $mchnt_txn_ssn = $data_arr['mchnt_txn_ssn'];
        $funName = debug_backtrace()[2]['function'];
        $data['user_id'] = (int)$_SESSION['user_info']['id'];
        $data['resp_code'] = $resp_code;
        $data['resp_code_zh'] = get_remind_code_zh($resp_code);
        $data['mchnt_txn_ssn'] = $mchnt_txn_ssn;
        $data['act'] = $funName;
        $data['act_zh'] = getPayFunName($funName);
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['employ_time'] = $end_time - $start_time;
        $data['create_date'] = date("Y-m-d");
        $data['in_data'] = json_encode($in_data);
        $data['put_data'] = json_encode($data_arr);
        M("pay_log")->add($data);
    }

}
