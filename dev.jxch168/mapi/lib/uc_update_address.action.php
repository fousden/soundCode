<?php

/**
 *
 * @api {get} ?act=uc_update_address&r_type=1&email=dch&&pwd=123456&real_name=xxxx&mobile=18651935745&code=123456&address=古北亚繁 修改地址
 * @apiName 客户端崩溃日志
 * @apiGroup jxch
 * @apiVersion 1.0.0
 * @apiDescription 请求url
 *
 * @apiParam {string} act 动作{uc_update_address}
 * @apiParam {string} email 用户名
 * @apiParam {string} pwd 密码
 * @apiParam {string} real_name 收件人
 * @apiParam {string} mobile 手机号
 * @apiParam {string} address 地址
 * @apiParam {string} code 邮政编码
 *
 * @apiSuccess {string} response_code 结果码
 * @apiSuccess {string} show_err 消息说明
 *
 * @apiSuccessExample 返回示范:
{
"response_code": 1,
"show_err": "修改地址成功",
"act": "uc_update_address",
"act_2": ""
}
 */
//require APP_ROOT_PATH . 'app/Lib/deal.php';

class uc_update_address {
    public function index()
    {
        $root = array();
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);
        if ($user_id > 0) {
            $real_name = isset($GLOBALS['request']['real_name']) ? trim($GLOBALS['request']['real_name']) : '';
            $mobile = isset($GLOBALS['request']['mobile']) ? trim($GLOBALS['request']['mobile']) : '';
            $code = isset($GLOBALS['request']['code']) ? trim($GLOBALS['request']['code']) : '';
            $address = isset($GLOBALS['request']['address']) ? trim($GLOBALS['request']['address']) : '';

            // 判断mobile是否为11位数字
            if (!check_mobile($mobile)) {
                output("请填写正确的手机号");
            };

            if (empty($address)) {
                output("收件地址不能为空");
            }
            if (empty($real_name)) {
                output("收件人姓名不能为空");
            }
            // 查出对应的address_id
            $sql = "select * from ".DB_PREFIX."user_receive_price_info where user_id = ".$user_id;
            $info = $GLOBALS['db']->getRow($sql);
            $address_id = $info['address_id'];
            $user_address['user_id'] = $user_id;
            $user_address['address'] = $address;
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_address", $user_address, 'UPDATE', 'id='.$address_id, 'SILENT');
            if($res){
                $user_price['user_id'] = $user_id;
                $user_price['real_name'] = $real_name;
                $user_price['address_id'] = $address_id;
                $user_price['mobile'] = $mobile;
                $user_price['code'] = $code;
                $res1 = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_receive_price_info", $user_price, 'UPDATE', 'address_id='.$address_id, 'SILENT');
                if($res1){
                    $root['response_code'] = 1;
                    $root['show_err'] = "修改地址成功";
                }else{
                    output("修改地址失败");
                }
            }else{
                output("修改地址失败");
            }
        }else{
            $root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }
}

?>
