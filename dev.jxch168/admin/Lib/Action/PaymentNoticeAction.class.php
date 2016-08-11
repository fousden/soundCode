<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class PaymentNoticeAction extends CommonAction
{

    public $_mod;

    //初始化函数
    public function _initialize()
    {
        parent::_initialize();
        $this->_mod = D('PaymentNotice');
    }

    public function com_search()
    {
        $map = array();

        if (!isset($_REQUEST['end_time']) || $_REQUEST['end_time'] == '') {
            $_REQUEST['end_time'] = to_date(get_gmtime(), 'Y-m-d');
        }


        if (!isset($_REQUEST['start_time']) || $_REQUEST['start_time'] == '') {
            $_REQUEST['start_time'] = dec_date($_REQUEST['end_time'], 7); // $_SESSION['q_start_time_7'];
        }

        $map['start_time'] = trim($_REQUEST['start_time']);
        $map['end_time']   = trim($_REQUEST['end_time']);

        $this->assign("start_time", $map['start_time']);
        $this->assign("end_time", $map['end_time']);


        if ($map['start_time'] == '') {
            $this->error('开始时间 不能为空');
            exit;
        }

        if ($map['end_time'] == '') {
            $this->error('结束时间 不能为空');
            exit;
        }

        $d = explode('-', $map['start_time']);
        if (checkdate($d[1], $d[2], $d[0]) == false) {
            $this->error("开始时间不是有效的时间格式:{$map['start_time']}(yyyy-mm-dd)");
            exit;
        }

        $d = explode('-', $map['end_time']);
        if (checkdate($d[1], $d[2], $d[0]) == false) {
            $this->error("结束时间不是有效的时间格式:{$map['end_time']}(yyyy-mm-dd)");
            exit;
        }

        if (to_timespan($map['start_time']) > to_timespan($map['end_time'])) {
            $this->error('开始时间不能大于结束时间:' . $map['start_time'] . '至' . $map['end_time']);
            exit;
        }

        $q_date_diff = 31;
        $this->assign("q_date_diff", $q_date_diff);
        //echo abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400 + 1;
        if ($q_date_diff > 0 && (abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400 + 1 > $q_date_diff)) {
            $this->error("查询时间间隔不能大于  {$q_date_diff} 天");
            exit;
        }

        return $map;
    }

    //充值人工审核
    function manual_audit()
    {
        $payment_id   = $_REQUEST['payment_id'];
        $audit_status = $_REQUEST['is_paid'];
        if (!$payment_id) {
            $this->ajaxReturn('', '充值单不存在！', 0);
            die();
        }
        $condition['id'] = $payment_id;
        $payment_notice  = M("payment_notice")->where($condition)->find();
        if ($payment_notice['is_paid'] == 0) {
            $data['id'] = $payment_id;
            if ($audit_status == 0) {
                $this->ajaxReturn('', '该充值单人工审核未通过成功！', 0);
                die();
            } elseif ($audit_status == 1) {
                require_once APP_ROOT_PATH . "system/libs/cart.php";
                //更改充值状态 更新充值数据
                $rs = payment_paid($payment_notice['id'], '');

                if ($rs) {
                    M("payment_notice")->save(array('id' => $payment_id, 'resp_describle' => '充值成功，人工！'));
                    $this->ajaxReturn('', '该充值单人工审核通过成功，数据更新成功！', 1);
                    die();
                } else {
                    M("payment_notice")->save(array('id' => $payment_id, 'is_paid' => 0));
                    $this->ajaxReturn('', '更新充值单状态失败！', 0);
                    die();
                }
            }
        } else if ($payment_notice['resp_describle'] != "充值成功" && $payment_notice['is_paid'] == 0) {
            $this->ajaxReturn('', '该充值单已中断，无需审核！', 0);
            die();
        } else if ($payment_notice['is_paid'] == 1) {
            $this->ajaxReturn('', '该充值单已充值成功，无需审核！', 0);
            die();
        }
    }

    public function index()
    {

        $map = $this->com_search();
        //增加会员名查找
        if (trim($_REQUEST['user_name']) != '') {
            $condition['user_id'] = M("User")->where("user_name='" . trim($_REQUEST['user_name']) . "'")->getField("id");
            $this->assign("user_name", $_REQUEST['user_name']);
        }
        //增加手机号查找
        if (trim($_REQUEST['mobile']) != '') {
            $condition['user_id'] = M("User")->where("mobile='" . trim($_REQUEST['mobile']) . "'")->getField("id");
            $this->assign("mobile", $_REQUEST['mobile']);
        }
        if (trim($_REQUEST['order_sn']) != '') {
            $condition['order_id'] = M("DealOrder")->where("order_sn='" . trim($_REQUEST['order_sn']) . "'")->getField("id");
        }
        if (intval($_REQUEST['no_payment_id']) > 0) {
            $condition['payment_id'] = array("neq", intval($_REQUEST['no_payment_id']));
        }
        if (trim($_REQUEST['notice_sn']) != '') {
            $condition['notice_sn'] = $_REQUEST['notice_sn'];
        }

        if ($map['start_time'] != '' && $map['end_time'] && (!isset($_REQUEST['is_paid']) || intval($_REQUEST['is_paid']) == -1 || intval($_REQUEST['is_paid']) == 1 )) {
            if (intval($_REQUEST['is_paid']) == 1)
                $condition['pay_date']    = array("between", array($map['start_time'], $map['end_time']));
            else
                $condition['create_date'] = array("between", array($map['start_time'], $map['end_time']));
        }

        if (intval($_REQUEST['is_paid']) == 0) {
            //$condition['create_time']= array("between",array(to_timespan($map['start_time'],"Y-m-d"),to_timespan(dec_date($map['end_time'],-1),"Y-m-d")));
            $condition['create_date'] = array("between", array($map['start_time'], $map['end_time']));
        }


        $payment_id              = M("Payment")->where("class_name = 'Otherpay' ")->getField("id");
        $condition['payment_id'] = array("neq", intval($payment_id));

        if (intval($_REQUEST['payment_id']) == 0) {
            unset($_REQUEST['payment_id']);
        } else {
            $condition['payment_id'] = array("eq", intval($_REQUEST['payment_id']));
        }
        if (intval($_REQUEST['is_paid']) == -1 || !isset($_REQUEST['is_paid']))
            unset($_REQUEST['is_paid']);

        //dump($condition);

        $this->assign("default_map", $condition);
        $this->assign("now_action", "index");
        $this->assign("payment_list", M("Payment")->findAll());
        parent::index();
    }

    //财务充值单错误处理
    public function incharge_manage()
    {
        set_time_limit(0);
        if (($_REQUEST['start_time'] && $_REQUEST['end_time']) || $_REQUEST['user_name'] || $_REQUEST['mobile'] || $_REQUEST['notice_sn']) {
            //$pre_map   = $this->com_search();
            $condition = $this->_mod->getCondition();

            //列表过滤器，生成查询Map对象
            $map       = $this->_search();
            //追加默认参数
            if ($condition) {
                $map = array_merge($map, $condition);
            }
            if (method_exists($this, '_filter')) {
                $this->_filter($map);
            }
            //默认 时间筛选
            $begin_time = $_REQUEST['start_time'] ? $_REQUEST['start_time'] : date("Y-m-d", strtotime("-7 day", time()));
            $end_time   = $_REQUEST['end_time'] ? $_REQUEST['end_time'] : date("Y-m-d");
            $start_time = $begin_time." 00:00:00";
            $final_time = $end_time . " 23:59:59";

            $result = $this->_mod->getInchargeList ($map,$start_time, $final_time);//所有充值记录 $result['list']
            $this->assign('list', $result['list']);
            $this->assign('sort', $result['sort']);
            $this->assign('order', $result['order']);
            $this->assign('sortImg', $result['sortImg']);
            $this->assign('sortType', $result['sortType']);
            $this->assign("page", $result['page']);
            $this->assign("nowPage", $result['nowPage']);

            $this->assign("now_action", "incharge_manage");
            $this->assign("payment_list", M("Payment")->findAll());
        }
        $this->display();
        return;
    }

    public function online()
    {
        $map = $this->com_search();
        if (trim($_REQUEST['order_sn']) != '') {
            $condition['order_id'] = M("DealOrder")->where("order_sn='" . trim($_REQUEST['order_sn']) . "'")->getField("id");
        }
        if (intval($_REQUEST['no_payment_id']) > 0) {
            $condition['payment_id'] = array("neq", intval($_REQUEST['no_payment_id']));
        }
        if (trim($_REQUEST['notice_sn']) != '') {
            $condition['notice_sn'] = $_REQUEST['notice_sn'];
        }
        $payment_id              = M("Payment")->where("class_name = 'Otherpay'")->getField("id");
        $condition['payment_id'] = $payment_id;

        if ($map['start_time'] != '' && $map['end_time']) {
            $condition['create_time'] = array("between", array(to_timespan($map['start_time'], "Y-m-d"), to_timespan(dec_date($map['end_time'], -1), "Y-m-d")));
        }

        if (intval($_REQUEST['is_paid']) == -1 || !isset($_REQUEST['is_paid']))
            unset($_REQUEST['is_paid']);
        $this->assign("default_map", $condition);
        parent::index();
    }

    //管理员收款
    public function update()
    {

        $notice_id       = intval($_REQUEST['id']);
        $outer_notice_sn = strim($_REQUEST['outer_notice_sn']);
        $bank_id         = strim($_REQUEST['bank_id']);

        //开始由管理员手动收款
        require_once APP_ROOT_PATH . "system/libs/cart.php";
        $payment_notice = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment_notice where id = " . $notice_id);

        if ($payment_notice['is_paid'] == 0) {
            if ($bank_id) {
                $GLOBALS['db']->query("update " . DB_PREFIX . "payment_notice set  bank_id = " . $bank_id . " where id = " . $notice_id . " and is_paid = 0");
            } else {
                $this->error("请输入直联银行编号");
            }
            payment_paid($notice_id, "银行流水号 " . ':' . $outer_notice_sn); //对其中一条款支付的付款单付款
            $msg = sprintf(l("ADMIN_PAYMENT_PAID"), $payment_notice['notice_sn']);
            save_log($msg, 1);
            $this->success(l("ORDER_PAID_SUCCESS"));
        } else {
            $this->error(l("INVALID_OPERATION"));
        }
    }

    public function gathering()
    {

        $id = intval($_REQUEST['id']);
        $this->assign("id", $id);
        $this->display();
    }

}

?>