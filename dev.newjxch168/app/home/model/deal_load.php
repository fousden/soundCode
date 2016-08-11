<?php

namespace home\model;

class DealLoad extends \base\model\frontend {

    //表名
    protected $tableName = 'deal_load';
    private $user_info = array();
    private $deal_info = array();
    private $fuyouModel;
    private $userModel;
    private $dealModel;
    private $couponModel;
    protected $data = array();
    private $is_use_coupon = 0;
    private $face_value = 0;
    private $coupon_interest = 0;
    private $capital = 0;

    private function check() {
        $this->fuyouModel = D('base/fuyou');
        $this->userModel = D("user");
        $this->user_info = $this->userModel->getUserInfoById($this->data['user_id']); //从user表中取出该用户详细的信息
        if (!$this->user_info) {
            return array_return("用户信息不正确！");
        } else if (!$this->user_info['idno']) {
            return array_return("您未实名，请先去实名！");
        } else if (!$this->user_info['pay_pwd']) {
            return array_return("您未设置支付密码，请先去个人中心设置！");
        } else if (!$this->user_info['fuiou_account']) {
            return array_return("您未正常开户！");
        }
        //验证是否输入的支付密码是否正确
        if (md5($this->data['pay_pwd']) != $this->user_info['pay_pwd']) {
            return array_return("输入支付密码不正确，请检查后输入！");
        }
        $this->dealModel = D('deal');
        $this->deal_info = $this->dealModel->getDealInfoById($this->data['deal_id']); //从deal表中取出该标的的信息
        if (!$this->deal_info) {
            return array_return("系统错误，请刷新后重试！");
        } else if ($this->deal_info['end_time'] < time()) {
            return array_return("该标的已经结束，请选择其他标的");
        } else if ($this->deal_info['deal_status'] > 1) {
            return array_return("该标的状态为" . getDealStatusName($this->deal_info['deal_status']) . "状态，请选择其他标的");
        }

        if ($this->data['coupon_id']) {
            $this->couponModel = D('coupon');
            $this->coupon_info = $this->couponModel->getCouponInfoById($this->data['coupon_id']);
            if (!$this->coupon_info || $this->coupon_info['user_id'] != $this->user_info['id']) {
                return array_return("所选的优惠劵不存在或者已经失效，请重试！");
            } else if ($this->coupon_info['min_limit'] > $this->data['bid_money']) {
                return array_return("投资的金额必须要大于" . $this->coupon_info['min_limit'] . '才能使用' . getCouponTypeName($this->coupon_info['coupon_type']));
            } else {
                //抵现券面值
                $this->face_value = $this->coupon_info['face_value'];
                $coupon_type = $this->coupon_info['coupon_type'];
                if ($coupon_type == 1) {
                    $this->coupon_interest = $deal_deal_data['pure_interest_e2'] * $this->face_value;
                }
                $this->is_use_coupon = 1;
            }
        }
        //计算实际投资金额（投资金额-抵现券面值）
        $this->capital = $this->data['bid_money'] - $this->face_value;
        //验证账户中的金额是否足够
        if ($this->capital > $this->user_info['money_e2'] / 100) {
            return array_return("账户可用金额不足，请充值后重试！");
        }
        //验证投资金额是否足够
        if ($this->data['bid_money'] > ($this->deal_info['borrow_amount_e2'] - $this->deal_info['load_money_e2']) / 100) {
            return array_return("输入的投资金额大于可投金额！");
        }
        //验证投资金额是否低于最低投资金额
        if ($this->data['bid_money'] < $this->deal_info['min_loan_money_e2'] / 100) {
            return array_return("输入的投资金额必须大于最低投资金额！");
        } else if ($this->data['bid_money'] < $this->deal_info['max_loan_money_e2'] / 100 && $this->deal_info['max_loan_money_e2'] != 0) {
            return array_return("输入的投资金额必须小于于最高投资金额！");
        }

        //验证第三方支付账户中的金额是否足够
        $fuyou_data['id'] = $this->data['user_id'];
        $fuyou_data['fuiou_account'] = $this->user_info['fuiou_account'];
        $res = $this->fuyouModel->check_balance($fuyou_data);
        if ($res['status'] == 2) {
            return array_return($res['show_error']);
        } else if ($this->capital > $res['ca_balance']) {
            return array_return("账户可用金额不足，请充值后重试！");
        }
        return true;
    }

    public function insert($data) {
//        echo '<pre>';var_dump($data);echo '</pre>';die;
        $this->data = $data;
        if (is_array($arr = $this->check())) {
            return array_return($arr);
        }
        //将deal表投资的金额统计
        $deal_data['id'] = $this->deal_info['id'];
        $deal_data['load_money_e2'] = $this->deal_info['load_money_e2'] + $this->data['bid_money'] * 100;
        $deal_data['buy_count'] = $this->deal_info['buy_count'] + 1;
        if ($deal_data['load_money_e2'] == $this->deal_info['borrow_amount_e2']) {
            $deal_data['deal_status'] = 2;
        }
        $deal_id = $this->dealModel->save($deal_data);
        if (!$deal_id) {
            return array_return("操作失败,请重试！");
        }
        //扣除jxch账户金额
        $user_data['id'] = $this->user_info['id'];
        $user_data['money_e2'] = $this->user_info['money_e2'] - $this->capital * 100;
        $user_data['lock_money_e2'] = $this->user_info['lock_money_e2'] + $this->capital * 100;
        $user_id = $this->userModel->save($user_data);
        if (!$user_id) {
            $this->recoverDeal(); //deal表投资的金额恢复掉
            return array_return("操作失败,请重试！");
        }
        //扣除优惠劵
        if ($this->is_use_coupon) {
            $coupon_data['id'] = $this->coupon_info['id'];
            $coupon_data['status'] = 1;
            $coupon_data['load_id'] = $this->data['deal_id'];
            $coupon_data['lose_time'] = time();
            $coupon_id = $this->couponModel->save($coupon_data);
            if (!$coupon_id) {
                $this->recoverUserData(); //将账户中的钱还原
                $this->recoverDeal(); //deal表投资的金额恢复掉
                return array_return("操作失败,请重试！");
            }
        }

        //生成订单所准备的数据
        //实际投资本金（投资金额-抵现券面值）
        $deal_deal_data['capital_e2'] = (int) $this->capital * 100;
        //借款ID
        $deal_deal_data['deal_id'] = (int) $this->data['deal_id'];
        //借款人ID
        $deal_deal_data['borrower_id'] = (int) $this->deal_info['borrower_id'];
        //投标人ID
        $deal_deal_data['user_id'] = (int) $this->user_info['id'];
        //用户名
        $deal_deal_data['user_name'] = (string) $this->user_info['user_name'];
        //投标金额
        $deal_deal_data['money_e2'] = (int) $this->data['bid_money'] * 100;
        //实际纯利息(投资金额*年化利率/360天*借款期限(天))
        $deal_deal_data['pure_interest_e2'] = (int) ($this->data['bid_money'] * ($this->deal_info['rate_e2']+$this->deal_info['increase_rate_e2']) / 360 * $this->deal_info['repay_time']/100);
        //年化收益率
        $deal_deal_data['rate_e2'] = (int) $this->deal_info['rate_e2'];
        //加息年化收益率(这个功能暂时没有，先设为0)
        $deal_deal_data['increase_rate_e2'] = (int)$this->deal_info['increase_rate_e2'];
        //投资期限
        $deal_deal_data['repay_time'] = (int) $this->deal_info['repay_time'];
        //活动收益(这个功能暂时没有，先设为0)
        $deal_deal_data['active_interest_e2'] = 0;
        //收益券收益(收益劵表暂无，暂时为0)
        $deal_deal_data['coupon_interest_e2'] = (int) $this->coupon_interest * 100;
        //总收益（实际纯利息+活动收益+收益券收益）
        $deal_deal_data['all_interest_e2'] = (int) $deal_deal_data['pure_interest_e2'] + $deal_deal_data['active_interest_e2'] + $deal_deal_data['coupon_interest_e2'];
        //实际应还总额(总收益+总本金)
        $deal_deal_data['all_repay_money_e2'] = $deal_deal_data['all_interest_e2'] + $deal_deal_data['money_e2'];
        //抵现券面值
        $deal_deal_data['coupon_cash_e2'] = $this->face_value * 100;
        //富友预授权合同号
        $deal_deal_data['contract_no'] = 0;
        //是否为自动投标 0手动 1:自动(暂时从用户中取)
        $deal_deal_data['is_auto'] = (string) $this->user_info['is_auto'];
        //订单来源 1=web 2=wap 3=android 4=ios(暂时为注册时的渠道信息)
        $deal_deal_data['order_source'] = (string) $this->user_info['register_terminal'];
        //投标时间
        $deal_deal_data['create_time'] = time();
        //记录投资日期,方便统计使用
        $deal_deal_data['create_date'] = date("Y-m-d");
        $dealLoadModel = M('deal_load');
        if ($deal_load_id = $dealLoadModel->add($deal_deal_data)) {
            $result = $this->fuyouModel->preAuthAction($this->user_info['fuiou_account'], $this->capital, $deal_load_id, $this->deal_info['name']);
//            echo '<pre>';var_dump($result);echo '</pre>';die;
            $resp_code = $result->plain->resp_code;
            $contract_no = (string) $result->plain->contract_no;
            if ($resp_code == '0000') {
                $deal_load_data['id'] = $deal_load_id;
                $deal_load_data['status'] = 1;
                $deal_load_data['contract_no'] = $contract_no;
                if (!M('deal_load')->save($deal_load_data)) {
                    //记录错误日志
                    echo 111;
                    die;
                }
//                echo '<pre>';print_r($deal_load_data);echo '</pre>';die;
//                echo M('deal_load')->getLastSql();die;
                $root['response_code'] = 1;
                $root['show_err'] = "投资成功！";
                return array_return($root);
            } else {
                $deal_load_data['id'] = $deal_load_id;
                $deal_load_data['status'] = 2;
                $deal_load_data['update_time'] = time();
//                echo '<pre>';var_dump($deal_load_data);echo '</pre>';die;
                if (!$dealLoadModel->save($deal_load_data)) {
                    //记录错误日志
                }
                $this->recoverCouponData(); //将扣除的优惠劵还原
                $this->recoverUserData(); //将账户中的钱恢复掉
                $this->recoverDealData(); //deal表投资的金额恢复掉
//                return array_return("操作失败,请重试！");
                return array_return(get_remind_code_zh($resp_code));
            }
        } else {
            $this->recoverCouponData(); //将扣除的优惠劵还原
            $this->recoverUserData(); //将账户中的钱恢复掉
            $this->recoverDealData(); //deal表投资的金额恢复掉
            return array_return("操作失败！");
        }
    }

    //deal表投资的金额恢复掉
    private function recoverDealData() {
        //deal表投资的金额恢复掉
        $deal_data['id'] = $this->deal_info['id'];
        $deal_data['load_money_e2'] = $this->deal_info['load_money_e2'];
        $deal_data['buy_count'] = $this->deal_info['buy_count'];
        $deal_data['deal_status'] = 1;
        $deal_id = $this->dealModel->save($deal_data);
        if (!$deal_id) {
            //记录错误日志
        }
    }

    //将账户中的钱恢复掉
    private function recoverUserData() {
        $user_data['id'] = $this->user_info['id'];
        $user_data['money_e2'] = $this->user_info['money_e2'];
        $user_data['lock_money_e2'] = $this->user_info['lock_money_e2'];
        $user_id = $this->userModel->save($user_data);
        if (!$user_id) {
            //记录错误日志
        }
    }

    //将扣除的优惠劵还原
    private function recoverCouponData() {
        if ($this->is_use_coupon) {
            $coupon_data['id'] = $this->coupon_info['id'];
            $coupon_data['status'] = 0;
            $coupon_data['load_id'] = 0;
            $coupon_data['lose_time'] = 0;
            $coupon_id = $this->couponModel->save($coupon_data);
            if (!$coupon_id) {
                //记入到错误日志中
            }
        }
    }

    //获取用户投资列表
    function getUserDealLoad($condition, $listRows = 20, $order_by = 'create_time desc') {
        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            $p = new \think\Page($count, $listRows);
            $carry_list = $this->where($condition)->order($order_by)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            //数据处理
            foreach ($carry_list as $key => $val) {
                $this->deal_info = M("deal")->find($val["deal_id"]);
                $this->deal_info["progress_point"] = ($this->deal_info["load_money_e2"] / $this->deal_info["borrow_amount_e2"] * 100);
                $carry_list[$key]["deal"] = $this->deal_info;
            }
            $return['load_list'] = $carry_list;
        }
        return $return;
    }

}
