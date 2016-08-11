<?php
/**
 *  
 * @api {get} ?act=deal_details&r_type=1&email=dch&&pwd=123456&deal_load_id=2016 投资详情页 
 * @apiName 投资详情页 
 * @apiGroup jxch
 * @apiVersion 1.0.0 
 * @apiDescription 请求url 
 *  
 * @apiParam {string} act 动作{deal_details}
 * @apiParam {string} email {必传}账号
 * @apiParam {string} pwd {必传}密码
 * @apiParam {string} id {必传}标的
 * @apiParam {string} deal_load_id {选传}投资id（在我的投资中投资详情中使用，在我的投资列表中有该字段）
 * 
 * @apiSuccess {string} response_code 请求状态
 * @apiSuccess {int} user_login_status 登录状态（1=已登录，0=未登录） 
 * @apiSuccess {string} min_loan_money_format 最低起投金额 
 * @apiSuccess {string} name 标的名称 
 * @apiSuccess {string} rate_format_w 格式化的年化利率(带'%'符号的)
 * @apiSuccess {string} repay_time 项目期限 
 * @apiSuccess {string} progress_point 投资进度百分比 
 * @apiSuccess {string} d_status 标的状态（0=还款中，1=已还款）
 * @apiSuccess {string} last_jiexi_time 预计还款日（年-月-日）
 * @apiSuccess {string} real_money 实际收益
 * @apiSuccess {string} self_money 实际投资金额
 * @apiSuccess {string} active_interest_money 活动收益
 * @apiSuccess {string} month_repay_money 本息合计
 * @apiSuccess {string} month_has_repay_money 已收回成本
 * @apiSuccess {string} true_repay_date 实际还款日
 * 
 */

require APP_ROOT_PATH . 'app/Lib/deal.php';

class deal_details {

    function index() {
        $root = array();

        $id = intval($GLOBALS['request']['id']);
        $deal_load_id = isset($_REQUEST['deal_load_id'])?intval($_REQUEST['deal_load_id']):0;
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        //检查用户,用户密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);
        if ($user_id > 0) {
            $root['response_code'] = 1;
            $root['user_login_status'] = 1;
            //获取详情中需要的数据            
            $result = $GLOBALS['db']->getRow("select true_repay_date,u_key from " . DB_PREFIX . "deal_load_repay where deal_id = $id and (user_id=$user_id or t_user_id=$user_id)");
            $root['true_repay_date'] = "0";
            if ($result['true_repay_date'] != '0000-00-00') {
                $root['true_repay_date'] = $result['true_repay_date'];
            }
            $deal = get_deal($id);
            $root['min_loan_money_format'] = num_format($deal['min_loan_money']);
            $root['name'] = $deal['name'];
            $root['rate_format_w'] = num_format($deal['rate'], '2') . '%';
            $root['repay_time'] = empty($deal['repay_time']) ? '0' : $deal['repay_time'];
            $root['progress_point'] = (int) ($deal['load_money'] / $deal['borrow_amount'] * 100);
            if (!$deal || $deal['deal_status'] < 4) {// 此处要改为4在写完的时候
                $root['show_err'] = "无法查看，可能有以下原因！<br>1。借款不存在<br>2。借款被删除<br>3。借款未成功";
                $root['response_code'] = 0;
                output($root);
                exit;
            }
            if ($deal['deal_status'] == 4) {
                $root['d_status'] = 0; // 待还
            }
            if ($deal['deal_status'] == 5) {
                $root['d_status'] = 1; // 已还
            }

            $u_key = $result['u_key'];
            $root['last_jiexi_time'] = date("Y-m-d", strtotime("+2 day", strtotime($deal['jiexi_time'])));
            $deal['deal_load_id']=$deal_load_id;
            $temp_user_load['load'] = get_deal_user_load_list($deal, $user_id, -1);
            $root['month_repay_money'] = 0;
            $root['month_has_repay_money'] = 0;
            $root['real_money'] = 0;
            $root['active_interest_money'] = 0;
            $root['self_money'] = 0;
            foreach ($temp_user_load['load'] as $key => $val) {
                $vv=$val[0];
                $root['month_repay_money']+=$vv['month_repay_money'];
                $root['month_has_repay_money']+=$vv['month_has_repay_money'];
                $root['real_money']+=$vv['real_money'];
                //实际扣款金额
                $root['self_money']+= $vv['self_money'] - $vv['coupon_cash'];
                //活动收益（指的是所有的活动收益，包括优惠劵，抵现劵，加息活动）
                $root['active_interest_money'] = $vv['coupon_cash'] + $vv['act_interests'] + $vv['coupon_interests'];
                //实际收益（2015-10-20 更改为除活动收益以外的收益）
                if ($vv['has_repay'] == 1) {
                    $root['real_money'] = format_price($vv['month_repay_money'] - $vv['self_money'] - $vv['act_interests'] - $vv['coupon_interests']);
                } else {
                    $root['real_money'] = '0';
                }
            }
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
        }
        output($root);
    }
}
