<?php

/**
 * 微信相关数据的模型
 *
 * @author dch
 */
class WeixinModel extends BaseModel {

//    protected $tableName = 'weixin_user';
    /**
     * 提供发送模板消息的平台数据
     * @param string $keyword 关键字，可以是简体中文
     * @param string $openid 微信的openid
     * @param array $data 其他的参数
     */
    public function templateMsgData($keyword, $openid, $data = array()) {
        if (is_numeric($openid)) {
            $user_id = $openid;
            $openid = MO('WeixinUser')->getUserOpenidByUserId($user_id);
        }
//        echo '<pre>';var_dump($keyword);echo '</pre>';die;
        switch ($keyword) {
            case "余额":
                $user_info = MO("WeixinUser")->getUserInfoByOpenid($openid);
                if ($user_info) {
                    $data['result'] = ['value' => '用户：' . $user_info['mobile'] . '的资金信息'];
                    $data['money'] = ['value' => $user_info['money']];
                    $data['lock_money'] = ['value' => $user_info['lock_money']];
                    $data['total_money'] = ['value' => $user_info['money'] + $user_info['lock_money']];
                    $data['remark'] = ['value' => '查询时间为' . date('Y-m-d H:i:s')];
                    $template_id = "BMEpsDQPhD_hZR_XkLI5jBP2K3tIaE5yPdEOgE2KZnI";
                    $res = WIN('templateMsg')->send_template($openid, $template_id, $data, "http://www.baidu.com");
                }
                break;
            case '投标成功':
                $deal_load_info = MO("DealLoad")->getDealLoadInfoByLoadId($data['load_id'], "deal_id,money");
                $deal_id = $deal_load_info['deal_id'];
                $deal_info = MO("Deal")->getDealInfoByDealId($deal_load_info['deal_id'], "name,deal_status,qixi_time,jiexi_time,last_mback_time,borrow_amount,load_money");
                $user_info = MO("WeixinUser")->getUserInfoByOpenid($openid);
                if ($user_info && $openid) {
                    $template_data['result'] = ['value' => '用户：' . $user_info['mobile'] . '的投资信息'];
                    $template_data['deal_name'] = ['value' => $deal_info['name']];
                    $template_data['deal_money'] = ['value' => $deal_load_info['money']];
                    $template_data['remark'] = ['value' => '投资时间为' . date('Y-m-d H:i:s')];
                    $template_id = "5soGF0kZ6yh-ACnFgR65gheHlhXebJVTTLsdvCLZlHY";
                    $url = "http://dch.dev.jxch168.com/wap/index.php?ctl=deal&id=" . $deal_id;
                    $res = WIN('templateMsg')->send_template($openid, $template_id, $template_data, $url);
                }

                //满标
                if ($deal_info['borrow_amount'] == $deal_info['load_money']) {
                    $userIds = MO("WeixinUser")->getWeixinUserList("user_id");
                    $user_arr = array_map('array_shift', $userIds);
                    $deal_load_list = MO("DealLoad")->getDealLoadListByDealId($deal_id, 'user_id,sum(money) as money', " and user_id in(" . implode(",", $user_arr) . ") ");
                    unset($userIds, $user_arr);
                    $template_id = 'g295JMlm5Pq36gZFhlPQM3latEBe52WFNd-oehI7MwE';
                    $template_data=array();
                    foreach ($deal_load_list as $key => $val) {
                        $repay_info=MO('DealLoadRepay')->getRepayInfoByUserId($val['user_id'],"sum(repay_money) as repay_money");
                        $template_data['result'] = ['value' => '用户：' . $user_info['mobile'] . '的满标通知'];
                        $template_data['deal_name'] = ['value' => $deal_info['name']];
                        $template_data['deal_money'] = ['value' => $val['money']];
                        $template_data['qixi_time'] = ['value' => $deal_info['qixi_time']];
                        $template_data['jiexi_time'] = ['value' => $deal_info['jiexi_time']];
                        $template_data['repay_money'] = ['value' => $repay_info['repay_money']];
                        $template_data['last_mback_time'] = ['value' => $deal_info['last_mback_time']];
                        $template_data['remark'] = ['value' => '通知时间为' . date('Y-m-d H:i:s')];
                        $url = "http://dch.dev.jxch168.com/wap/index.php?ctl=deal&id=" . $deal_id;
                        $res = WIN('templateMsg')->send_template($openid, $template_id, $template_data, $url);
                    }
                }
                break;
            case '满标':

                break;
            default:
                break;
        }
    }

}
