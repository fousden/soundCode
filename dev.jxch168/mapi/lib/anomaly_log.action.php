<?php

/**
 *  
 * @api {get} ?act=anomaly_log&r_type=1&mobile=18565826594&udid=aasdas&_m=Android&agent=xiaomi3(mimu)&content=为什么崩溃了 客户端崩溃日志
 * @apiName 客户端崩溃日志
 * @apiGroup jxch
 * @apiVersion 1.0.0 
 * @apiDescription 请求url 
 *  
 * @apiParam {string} act 动作{anomaly_log}
 * @apiParam {string} mobile 手机号
 * @apiParam {string} udid {必传}设备id
 * @apiParam {string} _m {必传}客户端名称（如Android，ios）
 * @apiParam {string} agent {必传}设备类型附带设备使用的系统 如:xiaomi4(mimu5)
 * @apiParam {string} content {必传}崩溃日志
 * 
 * @apiSuccess {string} response_code 结果码 
 * @apiSuccess {string} show_err 消息说明 
 * 
 * @apiSuccessExample 返回示范: 
{
    "response_code": 1,
    "show_err": "操作成功！",
    "act": "anomaly_log",
    "act_2": ""
}
 */
class anomaly_log {

    public function index() {
        $_m = isset($_REQUEST['_m']) ? strtolower($_REQUEST['_m']) : '';
        if($_m=='android'){
            $data['terminal']=3;
        }else if($_m=='ios'){
            $data['terminal']=4;
        }
        $data['mobile'] = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $data['udid'] = isset($_REQUEST['udid']) ? strtoupper(trim($_REQUEST['udid'])) : '';
        $data['agent'] = isset($_REQUEST['agent']) ? trim($_REQUEST['agent']) : '';
        $data['content'] = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
        $data['app_version'] = isset($_REQUEST['version']) ? $_REQUEST['version'] : '';
        $data['create_time'] = time();
//        echo '<pre>';var_dump();echo '</pre>';die;
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "mobile_anomaly_log", $data, "INSERT");
        if (!$res) {
            output("操作失败！");
        } else {
            $root['response_code'] = 1;
            $root['show_err'] = "操作成功！";
            output($root);
        }
    }

}
