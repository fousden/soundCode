<?php

namespace home\controller;

class DealLoad extends \base\controller\frontend {

    /**
     * 投标生成订单
     * @param $deal_id    int       标的id
     * @param $bid_money  int       投资金额
     * @param $pay_pwd    string   支付密码
     * @return   response_code：0=失败，1=成功，show_err：提示语
     * 
     */
    public function to_load($data=array()) {
        $data['user_id'] = $_SESSION['user_info']['id'];
        $data['pay_pwd'] = isset($_REQUEST['pay_pwd']) ? trim($_REQUEST['pay_pwd']) : ajax_return("请输入支付密码！"); //获取传递过来的支付密码
        $data['bid_money'] = isset($_REQUEST['bid_money']) ? trim($_REQUEST['bid_money']) : ajax_return("请输入投资金额！"); //获取传递过来的投资金额
        $data['deal_id'] = isset($_REQUEST['deal_id']) ? trim($_REQUEST['deal_id']) :ajax_return("系统错误，请重试！"); //获取传递过来的标的id
        $data['coupon_id'] = isset($_REQUEST['coupon_id']) ? trim($_REQUEST['coupon_id']) :0; //获取传递过来的标的id
        ajax_return(D('deal_load')->insert($data));
    }

}
