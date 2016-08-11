<?php

namespace mapi\controller;

class Log extends \think\controller {

    public function anomaly_log() {
        $json='{
    "response_code": 1,
    "show_err": "操作成功！",
    "act": "anomaly_log",
    "act_2": ""
}';
        echo $json;die;
    }

}
