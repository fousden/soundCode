<?php

/*
 * 功能：财务业务逻辑类
 * 时间：2015年11月20 11:00
 * author:chushangming
 */

class FinanceModel extends Model
{

    protected $tableName = 'receivables';
    //自动验证
    protected $_validate = array(
            // array("username","require","用户名必须填写!")
    );
    //自动完成
    protected $_auto = array(
            // array('reg_time','time',1,'function'), //注册时间
    );

    //生成应付款数据
    function makeMeet($receivables)
    {
        $ids = array();
        $payables                = M('payables');
        //准备应付款数据
        $contract_info           = M("contract")->where(array('contract_id' => $receivables['contract_id']))->find();
        $product_info            = M("product")->where(array('product_id' => $contract_info['product_id']))->find();
        //产品类型分类 通类产品生产五个应付款 分别为四个季度的利息和一个原始还本金
        if($product_info["product_flag"] == "SJT" || $product_info["product_flag"] == "NFT"){//双季通 年富通
            //外包 通类产品结息时间 已每月八号为准 然后付息
            /*if($contract_info["outer_pack"] == 1){//自营团队合同
                //第一季度支付时间
                $pay_time[1]  = strtotime("+3 day",$contract_info['start_date']);
                $month_num = 3;
            }else if($contract_info["outer_pack"] == 2){//外包团队合同
                //当前日期 当月八号
                $now_date = strtotime(date("Y-m-08"));
                //第一季度支付时间
                if($contract_info['start_date'] >= $now_date){
                    $pay_time[1]  = strtotime("+1 month",strtotime(date("Y-m-08",$contract_info['start_date'])));
                }else{
                    $pay_time[1]  = strtotime(date("Y-m-08",$contract_info['start_date']));
                }
                $month_num = 1;
             }
           */
            
            //按季度付息 第一季度支付时间
            $pay_time[1]  = strtotime("+3 day",time());
            $month_num = 3;
            
            //第二季度及其以后付息时间
            $i = 1;
            while (strtotime("+".$month_num." month",$pay_time[$i]) < $contract_info['end_date']) {
               $pay_time[$i+1]  = strtotime("+".$month_num." month",$pay_time[$i]);
               $i++;
            }
            
            //到期还本时间 根据付息时间生成应付款
            $pay_time[]  = $contract_info['end_date'];
            foreach($pay_time as $key=>$val){
                if($key != count($pay_time)){
                    $data['name']  = "产品：" . $product_info['name'] . "，合同编号为" . $contract_info['number'] . "的第".$key."季度应付款";
                    if(($contract_info['total_interest']%2) == 0){
                        $data['price'] = intval($contract_info['total_interest'] / (count($pay_time)-1));
                    }else{
                        $data['price'] = intval(($contract_info['total_interest']-1) / (count($pay_time)-1));
                        if($key == 1){
                            $data['price'] += 1;
                        }
                    }
                    $data['pay_type'] = 1;//1代表利息
                }else{
                    $data['name']  = "产品：" . $product_info['name'] . "，合同编号为" . $contract_info['number'] . "的到期还本应付款";
                    $data['price'] = $contract_info['investment_money'];
                    $data['pay_type'] = 2;//2代表本金 0代表本息
                }
                $data['receivables_id']  = $receivables['receivables_id'];
                $data['customer_id']     = $contract_info["customer_id"];
                $data['contract_id']     = $contract_info['contract_id'];
                $data['description']     = $data['name'];
                $data['pay_time']        = $val;
                $data['creator_role_id'] = session('role_id');
                $data['owner_role_id']   = $contract_info['owner_role_id'];
                $data['create_time']     = time();
                $data['update_time']     = time();
                $data['status']          = -1;
                $ids[] = $payables->add($data);
            }
        }else{//盈类产品 只需要一个应付款记录 到期还本息
            $data['name']            = "产品：" . $product_info['name'] . "，合同编号为" . $contract_info['number'] . "的到期还本付息应付款";
            $data['price']           = $contract_info['total_receivables_money'];
            $data['receivables_id']  = $receivables['receivables_id'];
            $data['customer_id']     = $contract_info["customer_id"];
            $data['contract_id']     = $contract_info['contract_id'];
            $data['description']     = $data['name'];
            $data['pay_time']        = $contract_info['end_date'];
            $data['creator_role_id'] = session('role_id');
            $data['owner_role_id']   = $contract_info['owner_role_id'];
            $data['create_time']     = time();
            $data['update_time']     = time();
            $data['status']          = ($data['price'] == 0) ? 2 : -1;
            $ids[] = $payables->add($data);
        }
        return $ids;
    }

    //生成收款单数据记录
    public function addReceivingOrder($finance_type)
    {
        $receivables_info  = $this->where(array("receivables_id" => $_POST['receivables_id']))->find();
        $receivingorder    = M('receivingorder');
        $receivables_money = $receivingorder->where(array("receivables_id" => $_POST['receivables_id'], "is_deleted" => 0, 'status' => 1))->getField("sum(money)");
        if ($receivables_money == $receivables_info['price'] || $receivables_info['status'] == 2) {
            $show_info["status"] = "error";
            $show_info["info"]   = "应收账款已收完，无法增加新的应收款！";
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }

        $data['name']  = (trim($_POST['name']) && (trim($_POST['name']) != L('AUTOMATIC_GENERATION'))) ? trim($_POST['name']) : 'HMT' . date('Ymd') . mt_rand(1000, 9999);
        //收款总额
        $data['money'] = $_POST['money'] ? $_POST['money'] * 100 : 0;
        //收款手续费
        $data['receive_fee'] = $_POST['receive_fee'] ? $_POST['receive_fee'] * 100 : 0;
        //实收款项
        $data['real_money'] = $_POST['real_money'] * 100;
        if (($data['money'] + $receivables_money) > $receivables_info['price']) {
            $show_info["status"] = "error";
            $show_info["info"]   = "新增收款单金额超过剩余应收金额，无法增加！";
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        if (!$_POST['receivables_id']) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('PLEASE_SELECT_RECEIVABLES');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        $data['receivables_id']  = intval($_POST['receivables_id']);
        $data['description']     = trim($_POST['description']);
        $data['pay_time']        = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();
        $data['bank_in_time']        = strtotime($_POST['bank_in_time']) ? strtotime($_POST['bank_in_time']) : time();
        $data['creator_role_id'] = session('role_id');
        if (!$_POST['owner_role_id']) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        $data['owner_role_id'] = intval($_POST['owner_role_id']);
        $data['create_time']   = time();

        $data['status']        = 1;
        $data['update_time']   = time();
        $data['funds_gangway'] = $_POST['funds_gangway'];

        if ($receivingorder->add($data)) {
            actionLog($id, 't=receivingorder');

            $receivables = $this->where(array('receivables_id' => $data['receivables_id']))->find();
            $moneys      = $receivingorder->where(array('receivables_id' => $data['receivables_id'], 'status' => 1))->getField("sum(money)");
            if ($moneys == $receivables['price']) {
                $this->where(array('receivables_id' => $data['receivables_id']))->save(array('status' => 2));
                M('contract')->where(array('contract_id' => $receivables['contract_id']))->save(array('examine_status' => 4));

                $show_info["status"] = "success";
                $show_info["info"]   = "该应收款收款完毕，请会计审核 ！";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;

            } elseif ($moneys > 0 && $moneys < $receivables['price']) {
                $ad_id               = $this->where(array('receivables_id' => $data['receivables_id']))->save(array('status' => 1));
                $show_info["status"] = "success";
                $show_info["info"]   = "收款单添加成功！";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            } elseif ($money_sum > $receivables['price']) {
                $show_info["status"] = "error";
                $show_info["info"]   = "所有已收款记录金额超过了该应收账款账面金额，请查询确认应收款及收款单数据！";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            }

            if ($_POST['submit'] == L('SAVE')) {
                $show_info["status"] = "success";
                $show_info["info"]   = "该应收款收款完毕，对应应付款记录已生成！";
                $show_info["url"]    = U('finance/index', 't=' . $finance_type);
                return $show_info;
            } else {
                $show_info["status"] = "success";
                $show_info["info"]   = "该应收款收款完毕，对应应付款记录已生成！";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            }
        } else {
            $show_info["status"] = "success";
            $show_info["info"]   = L('ADDING FAILS CONTACT THE ADMINISTRATOR', array(''));
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
    }

    //生成付款单数据记录
    function addRepayOrder($finance_type)
    {
        $payables_info  = M("payables")->where(array("payables_id" => $_POST['payables_id']))->find();
        $paymentorder   = M('paymentorder');
        $payables_money = $paymentorder->where(array("payables_id" => $_POST['payables_id'], "is_deleted" => 0, "status" => 1))->getField("sum(money)");
        if ($payables_money == $payables_info['price']) {// || $payables_info['status'] == 2
            $show_info["status"] = "error";
            $show_info["info"]   = "应付账款已还清，无法增加新的收款单！";
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }

        $data['name']  = (trim($_POST['name']) && (trim($_POST['name']) != L('AUTOMATIC_GENERATION'))) ? trim($_POST['name']) : 'HMT' . date('Ymd') . mt_rand(1000, 9999);
        $data['money'] = $_POST['money'] * 100;
        if ($_POST['status'] == 1 && ($data['money'] + $payables_money) > $payables_info['price']) {
            $show_info["status"] = "error";
            $show_info["info"]   = "新增付款单金额超过剩余应付款金额，无法增加！";
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        if (!$_POST['payables_id']) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('PLEASE_SELECT_PAYABLES');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        $data['payables_id']     = intval($_POST['payables_id']);
        $data['description']     = trim($_POST['description']);
        $data['pay_time']        = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();


        $data['creator_role_id'] = session('role_id');
        if (!$_POST['owner_role_id']) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        if (!isset($_FILES['pay_img']) || empty($_FILES['pay_img']) || empty($_FILES['pay_img']['name'])) {
            $show_info["status"] = "error";
            $show_info["info"]   = '请上传付款凭证截图';
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }

        $data['owner_role_id'] = intval($_POST['owner_role_id']);
        $data['create_time']   = time();

        $data['status']        = 1;
        $data['update_time']   = time();
        $data['funds_gangway'] = $_POST['funds_gangway'];

        $upload_result   = uploading_files($_FILES['pay_img']);
        $data['pay_img'] = $upload_result['upload_data'][0]['savepath'] . $upload_result['upload_data'][0]['savename'];

        if ($paymentorder->add($data)) {
            actionLog($id, 't=paymentorder');
            $payables  = M('payables')->where(array('payables_id' => $data['payables_id']))->find();
            $money_sum = $paymentorder->where(array('payables_id' => $data['payables_id'], 'status' => 1))->getField("sum(money)");
            //合同信息
            $contract_info = M("contract")->where(array("contract_id"=>$payables["contract_id"]))->find();
            if ($money_sum == $payables['price']) {
                //更新合同为部分付款
                M('contract')->where(array('contract_id' => $payables['contract_id']))->save(array('examine_status' => 5));
                $up_id = M('payables')->where(array('payables_id' => $data['payables_id']))->save(array('status' => 2));
                //如果合同是已赎回的
                if($contract_info['renew_status'] == 2){
                    //该合同下所有的应付款总额（已还）
                    $payables_price = M('payables')->where(array('contract_id' => $contract_info['contract_id'],"status"=>array("in","0,1"),"renew_status"=>0))->getField("sum(price)");
                    if(!$payables_price){
                        $contract_flag = true;
                    }
                }else{
                    //该合同下所有的应付款总额（已还）
                    $payables_price = M('payables')->where(array('contract_id' => $contract_info['contract_id'],"status"=>2,"renew_status"=>0))->getField("sum(price)");
                    //如果存在赎回子合同 合并金额
                    $pid_investment_money = M("Contract")->where(array('pid_contract_id' => $contract_info['contract_id'],"is_deleted"=>0))->getField("sum(investment_money)");
                    if(($payables_price+$pid_investment_money) == $contract_info["total_receivables_money"]){
                        $contract_flag = true;
                    }
                }
                if($contract_flag){
                    //如果所有还清则合同已还完
                    M('contract')->where(array('contract_id' => $payables['contract_id']))->save(array('examine_status' => 6));
                    $return_all = true;
                }
                if ($up_id) {
                    $show_info["status"] = "success";
                    $show_info["info"]   = $return_all ? "该合同下所有应付款都已还清，合同结束！" : "该应付款付款完毕，该应付款已还清！";
                } else {
                    $show_info["status"] = "error";
                    $show_info["info"]   = "该应付款付款完毕，但更新该应付款数据失败！";
                }
                $show_info["url"] = $_SERVER['HTTP_REFERER'];
                return $show_info;
            } elseif ($money_sum > 0 && $money_sum < $payables['price']) {
                M('payables')->where(array('payables_id' => $data['payables_id']))->save(array('status' => 1));
            } else if ($money_sum > $payables['price']) {
                $show_info["status"] = "error";
                $show_info["info"]   = "所有已付款记录金额超过了该应付账款账面金额，请查询确认应付款及付款单数据！";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            }
            if ($_POST['submit'] == L('SAVE')) {
                $show_info["status"] = "success";
                $show_info["info"]   = L('ADD SUCCESS', array(''));
                $show_info["url"]    = U('finance/index', 't=' . $finance_type);
                return $show_info;
            } else {
                $show_info["status"] = "success";
                $show_info["info"]   = L('ADD SUCCESS', array(''));
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            }
        } else {
            $show_info["status"] = "error";
            $show_info["info"]   = L('ADDING FAILS CONTACT THE ADMINISTRATOR', array(''));
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
    }

    //合同退回
    function contract_return($contract_data){
        $contract_info = M("Contract")->where(array("contract_id" => $contract_data["contract_id"]))->find();
        if (!$contract_info) {
            $return['status'] = 0;
            $return['info']   = "合同不存在！";
            return $return;
        }
        $res = M("Contract")->save($contract_data);
        if ($res) {
            M('receivables')->where(array("contract_id" => $contract_data["contract_id"],'status'=> 0))->delete();
            $return['status'] = 1;
            $return['info']   = "合同退回操作成功！";
        } else {
            $return['status'] = 0;
            $return['info']   = "合同退回操作失败！";
        }
        return $return;
    }

    //首页数据展示
    function getIndex($finance_type)
    {
        $where  = array();
        $params = array();
        $order  = "";

        if ($_GET['desc_order']) {
            $order = trim($_GET['desc_order']) . ' desc';
        } elseif ($_GET['asc_order']) {
            $order = trim($_GET['asc_order']) . ' asc';
        }

        $p         = isset($_GET['p']) ? intval($_GET['p']) : 1;
        $by        = isset($_GET['by']) ? trim($_GET['by']) : '';
        $below_ids = getSubRoleId(false);
        $all_ids   = getSubRoleId();
        switch ($by) {
            case 'create' : $where[$finance_type . '.creator_role_id'] = session('role_id');
                break;
            case 'sub' : $where[$finance_type . '.owner_role_id']   = array('in', implode(',', $below_ids));
                break;
            case 'subcreate' : $where[$finance_type . '.creator_role_id'] = array('in', implode(',', $below_ids));
                break;
            case 'none' : $where[$finance_type . '.status']          = array('eq', 0);
                break;
            case 'part' : $where[$finance_type . '.status']          = array('eq', 1);
                break;
            case 'all' : $where[$finance_type . '.status']          = array('eq', 2);
                break;
            case 'today' :
                $where[$finance_type . '.pay_time']        = array(array('lt', strtotime(date('Y-m-d', time())) + 86400), array('gt', 0), 'and');
                $where[$finance_type . '.status']          = array('neq', 2);
                break;
            case 'week' :
                $where[$finance_type . '.pay_time']        = array(array('lt', strtotime(date('Y-m-d', time())) + (date('N', time()) - 1) * 86400), array('gt', 0), 'and');
                $where[$finance_type . '.status']          = array('neq', 2);
                break;
            case 'month' :
                $where[$finance_type . '.pay_time']        = array(array('lt', strtotime(date('Y-m-01', strtotime('+1 month')))), array('gt', 0), 'and');
                $where[$finance_type . '.status']          = array('neq', 2);
                break;
            case 'deleted' : $where[$finance_type . '.is_deleted']      = 1;
                break;
            case 'add' : $order                                     = $finance_type . '.create_time desc';
                break;
            case 'update' : $order                                     = $finance_type . '.update_time desc';
                break;
            case 'me' : $where[$finance_type . '.owner_role_id']   = session('role_id');
                break;
        }
        if (!isset($where[$finance_type . '.is_deleted'])) {
            $where[$finance_type . '.is_deleted'] = 0;
        }
        if ($_REQUEST["field"]) {
            $field     = trim($_REQUEST['field']) == 'all' ? $finance_type . '.name|' . $finance_type . '.description' : $finance_type . '.' . $_REQUEST['field'];
            $search    = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
            $condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);
            if ('receivables.create_time' == $field || 'receivables.update_time' == $field) {
                $search = is_numeric($search) ? $search : strtotime($search);
            }
            switch ($_REQUEST['condition']) {
                case "is" : $where[$field] = array('eq', $search);
                    break;
                case "isnot" : $where[$field] = array('neq', $search);
                    break;
                case "contains" : $where[$field] = array('like', '%' . $search . '%');
                    break;
                case "not_contain" : $where[$field] = array('notlike', '%' . $search . '%');
                    break;
                case "start_with" : $where[$field] = array('like', $search . '%');
                    break;
                case "end_with" : $where[$field] = array('like', '%' . $search);
                    break;
                case "is_empty" : $where[$field] = array('eq', '');
                    break;
                case "is_not_empty" : $where[$field] = array('neq', '');
                    break;
                case "gt" : $where[$field] = array('gt', $search);
                    break;
                case "egt" : $where[$field] = array('egt', $search);
                    break;
                case "lt" : $where[$field] = array('lt', $search);
                    break;
                case "elt" : $where[$field] = array('elt', $search);
                    break;
                case "eq" : $where[$field] = array('eq', $search);
                    break;
                case "neq" : $where[$field] = array('neq', $search);
                    break;
                case "between" : $where[$field] = array('between', array($search - 1, $search + 86400));
                    break;
                case "nbetween" : $where[$field] = array('not between', array($search, $search + 86399));
                    break;
                case "tgt" : $where[$field] = array('gt', $search + 86400);
                    break;
                default :
                    if($_REQUEST['field'] == "department_id"){
                        $where["contract.department_id"] = array('eq', $search);
                    }else{
                        $where[$field] = array('eq', $search);
                    }
            }
            $params = array('field=' . trim($_REQUEST['field']), 'condition=' . $condition, 'search=' . trim($_REQUEST["search"]));
        }
        $order = empty($order) ? $finance_type . '.create_time desc' : $order;
        if ($_GET['listrows']) {
            $listrows = $_GET['listrows'];
            $params[] = "listrows=" . trim($_GET['listrows']);
        } else {
            $listrows = 15;
            $params[] = "listrows=15";
        }
        switch ($finance_type) {
            case 'receivables' :
                $status = $_REQUEST['status'];
                switch($status){
                    case 1:
                        $where[$finance_type.'.status'] = array("eq",0);
                        break;
                    case 2:
                        $where[$finance_type.'.status'] = array("eq",1);
                        break;
                    case 3:
                        $where[$finance_type.'.status'] = array("eq",2);
                        break;
                    case 4:
                        $where[$finance_type.'.verify_status'] = array("eq",0);
                        break;
                    case 5:
                        $where[$finance_type.'.verify_status'] = array("eq",1);
                        break;
                }
//                echo "<pre>";
//                print_r($where);
                $receivables    = D('ReceivablesView');
                $result['list'] = $receivables->order($order)->where($where)->page($p . ',' . $listrows)->select();
//                echo $receivables->getLastSql();exit;
                $sum_money      = $receivables->where($where)->sum('(receivables.price/100)');
                foreach ($result['list'] as $k => $v) {
                    $v['price']                  = $v['price'] / 100;
                    $result['list'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                    $money += $v['price'];
                    if ($by == 'deleted') {
                        $result['list'][$k]['deleted'] = getUserByRoleId($v['delete_role_id']);
                    }
                    $num                              = D('ReceivingorderView')->where('receivingorder.is_deleted <> 1 and receivingorder.receivables_id = %d and receivingorder.status = 1', $v['receivables_id'])->sum('(money / 100)');
                    $result['list'][$k]['un_payable'] = $v['price'] - $num;
                    $result['list'][$k]['price']      = $v['price'];
                }
                $result['money']     = number_format($money, 2);
                $result['sum_money'] = number_format($sum_money, 2);
                $count               = $receivables->where($where)->count();
                import("@.ORG.Page");
                $Page                = new Page($count, $listrows);
                $params[]            = 'by=' . trim($_GET['by']);
                $params[]            = 't=' . $finance_type;
                $result['parameter'] = implode('&', $params);
                if ($_GET['desc_order']) {
                    $params[] = "desc_order=" . trim($_GET['desc_order']);
                } elseif ($_GET['asc_order']) {
                    $params[] = "asc_order=" . trim($_GET['asc_order']);
                }

                $Page->parameter    = implode('&', $params);
                $result['show']     = $Page->show();
                $result['listrows'] = $listrows;
                return $result;
                break;
            case 'payables' :
                $status = $_REQUEST['status'];
                switch($status){
                    case -1:
                        $where[$finance_type.".status"] = array("eq",-1);
                        break;
                    case 1:
                        $where[$finance_type.".status"] = array("eq",0);
                        break;
                    case 2:
                        $where[$finance_type.".status"] = array("eq",1);
                        break;
                    case 3:
                        $where[$finance_type.".status"] = array("eq",2);
                        break;
                }
                $payables           = D('PayablesView');
                $result['list']     = $payables->order($order)->where($where)->page($p . ',' . $listrows)->select();
//                echo "<pre>";
//                print_r($result['list']);exit;
                $sum_money          = $payables->where($where)->sum('(payables.price/100)');
                foreach ($result['list'] as $k => $v) {
                    $result['list'][$k]['price']            = $result['list'][$k]['price'] / 100;
                    $v['price']                             = $v['price'] / 100;
                    $result['list'][$k]['owner']            = getUserByRoleId($v['owner_role_id']);
                    $money += $v['price'];
                    $result['list'][$k]['purchase_sn_code'] = M('purchase')->where('purchase_id = %d', $v['purchase_id'])->getField('sn_code');
                    if ($by == 'deleted') {
                        $result['list'][$k]['deleted'] = getUserByRoleId($v['delete_role_id']);
                    }
                }
                $result['money']     = number_format($money, 2);
                $result['sum_money'] = number_format($sum_money, 2);
                $count               = $payables->where($where)->count();
                import("@.ORG.Page");
                $Page                = new Page($count, $listrows);
                $params[]            = 'by=' . trim($_GET['by']);
                $params[]            = 't=' . $finance_type;

                $result['parameter'] = implode('&', $params);
                if ($_GET['desc_order']) {
                    $params[] = "desc_order=" . trim($_GET['desc_order']);
                } elseif ($_GET['asc_order']) {
                    $params[] = "asc_order=" . trim($_GET['asc_order']);
                }
                $Page->parameter    = implode('&', $params);
                $result['show']     = $Page->show();
                $result['listrows'] = $listrows;
//                echo "<pre>";
//                print_r($result['list']);exit;
                return $result;
                break;
            case 'receivingorder' :
                $receivingorder     = D('ReceivingorderView');
                $result['list']     = $receivingorder->order($order)->where($where)->page($p . ',' . $listrows)->select();
                $sum_money          = $receivingorder->where($where)->sum('(money/100)');
                foreach ($result['list'] as $k => $v) {
                    $result['list'][$k]['money'] = $result['list'][$k]['money'] / 100;
                    $v['money']                  = $v['money'] / 100;
                    $money += $v['money'];
                    $result['list'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                    if ($by == 'deleted') {
                        $result['list'][$k]['deleted'] = getUserByRoleId($v['delete_role_id']);
                    }
                }
                $result['money']     = number_format($money, 2);
                $result['sum_money'] = number_format($sum_money, 2);
                $count               = $receivingorder->where($where)->count();
                import("@.ORG.Page");
                $Page                = new Page($count, $listrows);
                $params[]            = 'by=' . trim($_GET['by']);
                $params[]            = 't=' . $finance_type;

                $result['parameter'] = implode('&', $params);
                if ($_GET['desc_order']) {
                    $params[] = "desc_order=" . trim($_GET['desc_order']);
                } elseif ($_GET['asc_order']) {
                    $params[] = "asc_order=" . trim($_GET['asc_order']);
                }

                $Page->parameter    = implode('&', $params);
                $result['show']     = $Page->show();
                $result['listrows'] = $listrows;
                return $result;
                break;
            case 'paymentorder' :
                $paymentorder       = D('PaymentorderView');
                $result['list']     = $paymentorder->order($order)->where($where)->page($p . ',' . $listrows)->select();
                $sum_money          = $paymentorder->where($where)->sum('money');
                foreach ($result['list'] as $k => $v) {
                    $result['list'][$k]['money'] = $result['list'][$k]['money'] / 100;
                    $v['money']                  = $v['money'] / 100;
                    $money +=$v['money'];
                    $result['list'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                    if ($by == 'deleted') {
                        $result['list'][$k]['deleted'] = getUserByRoleId($v['delete_role_id']);
                    }
                }
                $result['money']     = number_format($money, 2);
                $result['sum_money'] = number_format($sum_money, 2);
                $count               = $paymentorder->where($where)->count();
                import("@.ORG.Page");
                $Page                = new Page($count, $listrows);
                $params[]            = 'by=' . trim($_GET['by']);
                $params[]            = 't=' . $finance_type;

                $$result['parameter'] = implode('&', $params);
                if ($_GET['desc_order']) {
                    $params[] = "desc_order=" . trim($_GET['desc_order']);
                } elseif ($_GET['asc_order']) {
                    $params[] = "asc_order=" . trim($_GET['asc_order']);
                }

                $Page->parameter    = implode('&', $params);
                $result['show']     = $Page->show();
                $result['listrows'] = $listrows;
                return $result;
                break;
        }
    }

    //添加财务数据
    function addFinanceData($finance_type)
    {
        switch ($finance_type) {
            case 'receivables' :
                $receivables = M('receivables');
                if (!trim($_POST['name'])) {
                    $show['status'] = "error";
                    $show['info']   = L('PLEASE_FILL_IN_THE_NAME');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['name']  = trim($_POST['name']);
                $data['price'] = (int) ($_POST['price'] * 100);
                if (empty($data['price'])) {
                    $show['status'] = "error";
                    $show['info']   = '请填写金额';
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                if (!intval($_POST['customer_id'])) {
                    $show['status'] = "error";
                    $show['info']   = L('PLEASE_SELECT_CUSTOMERS');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['customer_id']     = intval($_POST['customer_id']);
                $data['contract_id']     = intval($_POST['contract_id']);
                $data['description']     = trim($_POST['description']);
                $data['pay_time']        = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();
                $data['creator_role_id'] = session('role_id');
                $data['owner_role_id']   = intval($_POST['owner_role_id']) ? intval($_POST['owner_role_id']) : session('role_id');
                $data['create_time']     = time();
                $data['update_time']     = time();
                $data['status']          = 0;
                if ($id                      = $receivables->add($data)) {
                    if (intval($_POST['check_add_order']) == 1) {
                        $data_order['name'] = (trim($_POST['order_name']) && (trim($_POST['order_name']) != L('AUTOMATIC_GENERATION'))) ? trim($_POST['name']) : 'HMT' . date('Ymd') . mt_rand(1000, 9999);
                        if (!trim($_POST['order_money'])) {
                            $show['status'] = "error";
                            $show['info']   = L('PLEASE_FILL_IN_THE_AMOUNT');
                            $show['url']    = $_SERVER['HTTP_REFERER'];
                            return $show;
                        }
                        $data_order['money']           = trim($_POST['order_money']);
                        $data_order['status']          = $_POST['order_status'];
                        $data_order['description']     = trim($_POST['order_description']);
                        $data_order['pay_time']        = strtotime($_POST['order_pay_time']) ? strtotime($_POST['order_pay_time']) : time();
                        $data_order['creator_role_id'] = session('role_id');
                        $data_order['owner_role_id']   = $data['owner_role_id'];
                        $data_order['create_time']     = time();
                        $data_order['receivables_id']  = $id;
                        $data_order['money']           = $data_order['money'] * 100;
                        $ro_id                         = M('receivingorder')->add($data_order);
                        actionLog($ro_id, 't=receivingorder');
                        if ($_POST['order_status'] == 1) {
                            $receivables = M('receivables')->where(array('receivables_id' => $id))->find();

                            if ($data_order['money'] >= $receivables['price']) {
                                M('receivables')->where(array('receivables_id' => $id))->setField('status', '2');
                            } elseif ($data_order['money'] > 0) {
                                M('receivables')->where(array('receivables_id' => $id))->setField('status', '1');
                            }
                        }
                    }
                    if ($_POST['submit'] == L('SAVE')) {
                        actionLog($id, 't=receivables');
                        if ($_POST['refer_url']) {
                            $show['status'] = "success";
                            $show['info']   = L('ADD SUCCESS', array(''));
                            $show['url']    = $_POST['refer_url'];
                        } else {
                            $show['status'] = "success";
                            $show['info']   = L('ADD SUCCESS', array(''));
                            $show['url']    = U('finance/index', 't=receivables');
                        }
                    } else {
                        $show['status'] = "success";
                        $show['info']   = L('ADD SUCCESS', array(''));
                        $show['url']    = $_SERVER['HTTP_REFERER'];
                    }
                    return $show;
                } else {
                    $show['status'] = "error";
                    $show['info']   = L('ADDING FAILS CONTACT THE ADMINISTRATOR', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                break;
            case 'payables' :
                $payables = M('payables');
                if (!trim($_POST['name'])) {
                    $show['status'] = "error";
                    $show['info']   = L('PLEASE_FILL_IN_THE_NAME');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['name']  = trim($_POST['name']);
                $data['price'] = $_POST['price'] * 100;
                if (empty($data['price'])) {
                    alert('error', '请填写金额', $_SERVER['HTTP_REFERER']);
                }
                if (!intval($_POST['customer_id'])) {
                    $show['status'] = "error";
                    $show['info']   = L('PLEASE_SELECT_CUSTOMERS');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['customer_id']     = intval($_POST['customer_id']);
                $data['contract_id']     = intval($_POST['contract_id']);
                $data['description']     = trim($_POST['description']);
                $data['pay_time']        = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();
                $data['creator_role_id'] = session('role_id');
                $data['owner_role_id']   = intval($_POST['owner_role_id']) ? intval($_POST['owner_role_id']) : session('role_id');
                $data['create_time']     = time();
                $data['update_time']     = time();
                $data['status']          = 0;

                if ($id = $payables->add($data)) {
                    if (intval($_POST['check_add_order']) == 1) {
                        $data_order['name']            = (trim($_POST['order_name']) && (trim($_POST['order_name']) != L('AUTOMATIC_GENERATION'))) ? trim($_POST['name']) : '5kcrm' . date('Ymd') . mt_rand(1000, 9999);
                        $data_order['money']           = trim($_POST['order_money']) ? trim($_POST['order_money']) : alert('error', L('PLEASE_FILL_IN_THE_AMOUNT'), $_SERVER['HTTP_REFERER']);
                        $data_order['status']          = $_POST['order_status'];
                        $data_order['description']     = trim($_POST['order_description']);
                        $data_order['pay_time']        = strtotime($_POST['order_pay_time']) ? strtotime($_POST['order_pay_time']) : time();
                        $data_order['creator_role_id'] = session('role_id');
                        $data_order['owner_role_id']   = $data['owner_role_id'];
                        $data_order['create_time']     = time();
                        $data_order['payables_id']     = $id;
                        $data_order['money']           = $data_order['money'] * 100;
                        $po_id                         = M('paymentorder')->add($data_order);
                        actionLog($po_id, 't=paymentorder');

                        if ($_POST['order_status'] == 1) {
                            $payables = M('payables')->where(array('payables_id' => $id))->find();

                            if ($data_order['money'] >= $payables['price']) {
                                M('payables')->where(array('payables_id' => $id))->setField('status', '2');
                            } elseif ($data_order['money'] > 0) {
                                M('payables')->where(array('payables_id' => $id))->setField('status', '1');
                            }
                        }
                    }

                    if ($_POST['submit'] == L('SAVE')) {
                        actionLog($id, 't=payables');
                        if ($_POST['refer_url']) {
                            $show['status'] = "success";
                            $show['info']   = L('ADD SUCCESS', array(''));
                            $show['url']    = $_POST['refer_url'];
                        } else {
                            $show['status'] = "success";
                            $show['info']   = L('ADD SUCCESS', array(''));
                            $show['url']    = U('finance/index', 't=payables');
                        }
                    } else {
                        $show['status'] = "success";
                        $show['info']   = L('ADD SUCCESS', array(''));
                        $show['url']    = $_SERVER['HTTP_REFERER'];
                    }
                    return $show;
                } else {
                    $show['status'] = "error";
                    $show['info']   = L('ADDING FAILS CONTACT THE ADMINISTRATOR', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                break;
            case 'receivingorder' :
                //新增收款单数据记录
                $show_info      = $this->addReceivingOrder($finance_type);
                $show['status'] = $show_info['status'];
                $show['info']   = $show_info['info'];
                $show['url']    = $show_info["url"];
                return $show;
                break;
            case 'paymentorder' :
                //新增付款单数据记录
                $show_info      = $this->addRepayOrder($finance_type);
                $show['status'] = $show_info['status'];
                $show['info']   = $show_info['info'];
                $show['url']    = $show_info["url"];
                return $show;
                break;
        }
    }

    //编辑财务数据
    function editFinanceData($finance_type)
    {
        $id = intval($_REQUEST['id']);
        if ($id == 0) {
            $show['status'] = 'error';
            $show['info']   = L('PARAMETER_ERROR');
            $show['url']    = U('finance/index', 't=' . $finance_type);
            return $show;
        }
        switch ($finance_type) {
            case 'receivables' :
                $receivables = D('ReceivablesView');
                $info        = $receivables->where(array('receivables_id' => $id))->find();
                if (empty($info)) {
                    $show['status'] = 'error';
                    $show['info']   = L('RECORD NOT EXIST', array(''));
                    $show['url']    = U('finance/index', 't=' . $finance_type);
                    return $show;
                }
                $info['owner'] = getUserByRoleId($info['owner_role_id']);
                if (!$_POST['submit']) {
                    $info['price']       = $info['price'] / 100;
                    $show["data"]        = $info;
                    $show["display_url"] = "receivablesedit";
                    return $show;
                }
                if (!$_POST['name']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_FILL_IN_THE_NAME');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['name']  = trim($_POST['name']);
                $data['price'] = (int) ($_POST['price'] * 100);
                if (!$_POST['customer_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_CUSTOMERS');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['customer_id'] = intval($_POST['customer_id']);
                $data['contract_id'] = intval($_POST['contract_id']);
                if (!$_POST['owner_role_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['owner_role_id'] = intval($_POST['owner_role_id']);
                $data['description']   = trim($_POST['description']);
                $data['pay_time']      = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();

                if (M('receivables')->where(array('receivables_id' => $id))->save($data)) {
                    actionLog($id, 't=receivables');
                    if ($_POST['refer_url']) {
                        $show['status'] = 'success';
                        $show['info']   = L('EDIT SUCCESS', array(''));
                        $show['url']    = $_POST['refer_url'];
                    } else {
                        $show['status'] = 'success';
                        $show['info']   = L('EDIT SUCCESS', array(''));
                        $show['url']    = U('finance/view', 'id=' . $id . '&t=' . $finance_type);
                    }
                } else {
                    $show['status'] = 'error';
                    $show['info']   = L('EDIT FAILED', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                }
                return $show;
                break;
            case 'payables' :
                $payables = D('PayablesView');
                $info     = $payables->where(array('payables_id' => $id))->find();
                if (empty($info)) {
                    $show['status'] = 'error';
                    $show['info']   = L('RECORD NOT EXIST', array(''));
                    $show['url']    = U('finance/index', 't=' . $finance_type);
                    return $show;
                }
                $info['owner'] = getUserByRoleId($info['owner_role_id']);
                if (!$_POST['submit']) {
                    $info['price']       = $info['price'] / 100;
                    $show["data"]        = $info;
                    $show["display_url"] = "payablesedit";
                    return $show;
                }
                if (!$_POST['name']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_FILL_IN_THE_NAME');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['name']  = trim($_POST['name']);
                $data['price'] = $_POST['price'] * 100;
                if (!$_POST['customer_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_CUSTOMERS');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['customer_id'] = intval($_POST['customer_id']);
                $data['contract_id'] = intval($_POST['contract_id']);
                if (!$_POST['owner_role_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['owner_role_id'] = intval($_POST['owner_role_id']);
                $data['description']   = trim($_POST['description']);
                $data['pay_time']      = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();

                if (M('payables')->where(array('payables_id' => $id))->save($data)) {
                    actionLog($id, 't=payables');
                    if ($_POST['refer_url']) {
                        $show['status'] = 'success';
                        $show['info']   = L('EDIT SUCCESS', array(''));
                        $show['url']    = $_POST['refer_url'];
                    } else {
                        $show['status'] = 'success';
                        $show['info']   = L('EDIT SUCCESS', array(''));
                        $show['url']    = U('finance/view', 'id=' . $id . '&t=' . $finance_type);
                    }
                } else {
                    $show['status'] = 'error';
                    $show['info']   = L('EDIT FAILED', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                }
                return $show;
                break;
            case 'receivingorder' :
                $receivingorder  = D('ReceivingorderView');
                $info            = $receivingorder->where(array('receivingorder_id' => $id))->find();
                $receivables     = M("receivables")->where(array("receivables_id" => $info['receivables_id'], "is_deleted" => 0))->find();
                $receiving_money = $receivingorder->where(array("receivables_id" => $info['receivables_id'], "is_deleted" => 0, "status" => 1))->getField("sum(money)");
//                if ($receivables['status'] == 2 || ($info['status'] == 0 && ($info['money'] + $receiving_money) > $receivables['price'])) {
//                    $show["status"] = "error";
//                    $show["info"]   = "收款单金额超过剩余应付款金额，无法修改！";
//                    $show["url"]    = $_SERVER['HTTP_REFERER'];
//                    return $show;
//                }
                if (empty($info)) {
                    $show['status'] = 'error';
                    $show['info']   = L('RECORD NOT EXIST', array(''));
                    $show['url']    = U('finance/index', 't=' . $finance_type);
                    return $show;
                }
//                if ($info['status'] == 1) {
//                    $show['status'] = 'error';
//                    $show['info']   = L('THE RECEIVABLES ORDER HAS BEEN CLOSING');
//                    $show['url']    = U('finance/index', 't=' . $finance_type);
//                    return $show;
//                }
                $info['owner'] = getUserByRoleId($info['owner_role_id']);
                if (!$_POST['submit']) {
                    $info['money']       = $info['money'] / 100;
                    $show["data"]        = $info;
//                    echo '<pre>';var_dump($info);echo '</pre>';die;
                    $show["display_url"] = "receivingorderedit";
//                    $show["display_url"] = "receivablesadddialog";
                    return $show;
                }
//                $data['name']  = trim($_POST['name']);
//                $data['money'] = $_POST['money'] * 100;
                $data['receive_fee'] = $_POST['receive_fee'] * 100;
                $data['funds_gangway'] =(int) $_POST['funds_gangway'];
                $data['description'] =$_POST['description'];
                
//                if (!$_POST['receivables_id']) {
//                    $show['status'] = 'error';
//                    $show['info']   = L('PLEASE_SELECT_PAYABLES');
//                    $show['url']    = $_SERVER['HTTP_REFERER'];
//                    return $show;
//                }
//                $data['receivables_id'] = intval($_POST['receivables_id']);
//                $data['description']    = trim($_POST['description']);
//                if (!$_POST['owner_role_id']) {
//                    $show['status'] = 'error';
//                    $show['info']   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
//                    $show['url']    = $_SERVER['HTTP_REFERER'];
//                    return $show;
//                }
                $data['owner_role_id'] = intval($_POST['owner_role_id']);
                if ($info['owner_role_id'] == session('role_id')) {
                    $data['status'] = intval($_POST['status']);
                }
                $data['pay_time'] = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();
                $data['bank_in_time'] = strtotime($_POST['bank_in_time']) ? strtotime($_POST['bank_in_time']) : time();
                if ($data['status'] == 1) {
                    $data['update_time'] = time();
                }

                if (M('receivingorder')->where(array('receivingorder_id' => $id))->save($data)) {
                    actionLog($id, 't=receivingorder');
                    $receivables = M('receivables')->where(array('receivables_id' => $data['receivables_id']))->find();
                    $moneys      = $receivingorder->where(array('receivables_id' => $data['receivables_id']))->select();
                    foreach ($moneys as $money) {
                        $money_sum += $money['money'];
                    }
                    if ($money_sum >= $receivables['price']) {
                        M('receivables')->where(array('receivables_id' => $data['receivables_id']))->save(array('status' => 2));
                    } elseif ($money > 0) {
                        M('receivables')->where(array('receivables_id' => $data['receivables_id']))->save(array('status' => 1));
                    }
                    if ($_POST['refer_url']) {
                        $show['status'] = 'success';
                        $show['info']   = L('EDIT SUCCESS', array(''));
                        $show['url']    = $_POST['refer_url'];
                        return $show;
                    }
                    $show['status'] = 'success';
                    $show['info']   = L('EDIT SUCCESS', array(''));
                    $show['url']    = U('finance/view', 'id=' . $id . '&t=' . $finance_type);
                    return $show;
                } else {
                    $show['status'] = 'error';
                    $show['info']   = L('EDIT FAILED', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                break;
            case 'paymentorder' :
                $paymentorder   = D('PaymentorderView');
                $info           = $paymentorder->where(array('paymentorder_id' => $id))->find();
                $payables       = M("payables")->where(array("payables_id" => $info['payables_id'], "is_deleted" => 0))->find();
                $payables_money = $paymentorder->where(array("payables_id" => $info['payables_id'], "is_deleted" => 0, "status" => 1))->getField("sum(money)");
                if ($payables["status"] == 2 || ($info['status'] == 0 && ($info['money'] + $payables_money) > $payables['price'])) {
                    $show["status"] = "error";
                    $show["info"]   = "付款单金额超过剩余应付款金额，无法修改！";
                    $show["url"]    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }

                if (empty($info)) {
                    $show['status'] = 'error';
                    $show['info']   = L('RECORD NOT EXIST', array(''));
                    $show['url']    = U('finance/index', 't=' . $finance_type);
                    return $show;
                }
                if ($info['status'] == 1) {
                    $show['status'] = 'error';
                    $show['info']   = L('THE PAYMENT ORDER HAS BEEN CLOSING');
                    $show['url']    = U('finance/index', 't=' . $finance_type);
                    return $show;
                }
                $info['owner'] = getUserByRoleId($info['owner_role_id']);
                if (!$_POST['submit']) {
                    $info['money']       = $info['money'] / 100;
                    $show["data"]        = $info;
                    $show["display_url"] = "paymentorderedit";
                    return $show;
                }
                $data['name']  = trim($_POST['name']);
                $data['money'] = $_POST['money'] * 100;
                if (!$_POST['payables_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_PAYABLES');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['payables_id'] = intval($_POST['payables_id']);
                $data['description'] = trim($_POST['description']);
                if (!$_POST['owner_role_id']) {
                    $show['status'] = 'error';
                    $show['info']   = L('PLEASE_SELECT_THE_PERSON_IN_CHARGE');
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                    return $show;
                }
                $data['owner_role_id'] = intval($_POST['owner_role_id']);
                if ($info['owner_role_id'] == session('role_id')) {
                    $data['status'] = intval($_POST['status']);
                }
                $data['pay_time'] = strtotime($_POST['pay_time']) ? strtotime($_POST['pay_time']) : time();
                if ($data['status'] == 1) {
                    $data['update_time'] = time();
                }

                if (M('paymentorder')->where(array('paymentorder_id' => $id))->save($data)) {
                    actionLog($id, 't=paymentorder');
                    $payables = M('payables')->where(array('payables_id' => $data['payables_id']))->find();
                    $moneys   = $paymentorder->where(array('payables_id' => $data['payables_id']))->select();
                    foreach ($moneys as $money) {
                        $money_sum += $money['money'];
                    }
                    if ($money_sum >= $payables['price']) {
                        M('payables')->where(array('payables_id' => $data['payables_id']))->save(array('status' => 2));
                    } elseif ($money > 0) {
                        M('payables')->where(array('payables_id' => $data['payables_id']))->save(array('status' => 1));
                    }
                    $show['status'] = 'success';
                    $show['info']   = L('EDIT SUCCESS', array(''));
                    $show['url']    = U('finance/view', 'id=' . $id . '&t=' . $finance_type);
                } else {
                    $show['status'] = 'error';
                    $show['info']   = L('EDIT FAILED', array(''));
                    $show['url']    = $_SERVER['HTTP_REFERER'];
                }
                return $show;
                break;
        }
    }
    
    function getRepayCalendar($dMonth){
       
        $cInfo = D('Calendar')->calendarMonth($dMonth);
        //周信息
        $week_info = $cInfo[0];
        $result['week_info'] = $week_info;
        //去除数组的第一个元素
        array_shift($cInfo);
        foreach($cInfo as $key=>$val){
           foreach($val as $k=>$v){
               if(strtotime($v) <  strtotime($dMonth.'-01')){
                   $cInfo[$key][$k] = '';
               }
            }
        }
        //初始化信息  //根据应付时间
        $new_calendar = array();
        $static = array();
        foreach($cInfo as $key => $val){
            foreach($val as $k => $v){
                $new_calendar[$key][$k]['week_day'] = $v;
                if(empty($v)){
                    continue;
                }
                $repay_data = $this->getRepayList($v);
                $new_calendar[$key][$k]['confirm_num'] = $repay_data['confirm_num'];
                $new_calendar[$key][$k]['repay_num']   = $repay_data['repay_num'];
                $new_calendar[$key][$k]['has_num']   = $repay_data['has_num'];
                $new_calendar[$key][$k]['money']       = number_format($repay_data['money']/100, 2);
                
                $static['confirm_num'] += $repay_data['confirm_num'];
                $static['repay_num']   += $repay_data['repay_num'];
                $static['has_num']   += $repay_data['has_num'];
                $static['num']         += $repay_data['num'];
                $static['money']       += $repay_data['money'];
            }
        }
        $static['money'] = number_format($static['money']/100, 2);
        
        $result['new_calendar'] = $new_calendar;
        $result['static']       = $static;
        return $result;
    }
    
    private function getRepayList($time){
        $payables = D('PayablesView');
        $start_time = strtotime($time.' 00:00:00');
        $end_time = strtotime($time.' 23:59:59');
        $map['pay_time'] = array('between',array($start_time,$end_time));
        $repay_data = $payables->where($map)->order($order)->select();
        foreach($repay_data as $key=>$val){
            switch($val['status']){
                case '-1':
                    $repay_data['confirm_num'] += 1;
                    break;
                case '0':
                    $repay_data['repay_num'] += 1;
                    break;
                case '1':
                    $repay_data['repay_num'] += 1;
                    break;
                case '2':
                    $repay_data['has_num'] += 1;
                    break;
            }
            $repay_data['num'] += 1;
            $repay_data['money'] += $val['price'];
        }
        return $repay_data;
    }
    
    //获取某天应还款详情
    function getRepayDetail($start_time,$end_time,$is_csv = false){
        $payables = D('PayablesView');
        $order = 'pay_time';
        $map['pay_time'] = array('between',array($start_time,$end_time));
        $repay_data = $payables->where($map)->order($order)->select();
        
        $statis = array('num'=>0,'money'=>0);
        foreach($repay_data as $key=>$val){
            $repay_data[$key]['id'] = $key+1;
            $repay_data[$key]['price'] = number_format($val['price']/100, 2);
            $repay_data[$key]['pay_time'] = date('Y-m-d',$val['pay_time']);
            $repay_data[$key]['create_time'] = date('Y-m-d',$val['create_time']);
            $repay_data[$key]['owner_name'] =  M('user')->where(array('role_id'=>$val['owner_role_id']))->getField('name');
            $repay_data[$key]['creator_name'] =  M('user')->where(array('role_id'=>$val['creator_role_id']))->getField('name');
            switch($val['status']){
                case '-1':
                    $repay_data[$key]['status'] = '未审核';
                    break;
                case '0':
                    $repay_data[$key]['status'] = '未付';
                    break;
                case '1':
                    $repay_data[$key]['status'] = '部分付';
                    break;
                case '2':
                    $repay_data[$key]['status'] = '已付';
                    break;
                default :
                    $repay_data[$key]['status'] = '无';
            }
            $statis['num'] += 1;
            $statis['money'] += $val['price'];
        }
        $statis['money'] = number_format($statis['money']/100, 2);
        $result =array('data'=>$repay_data,'statis'=>$statis);
        if($is_csv){
            //导出记录到excel表
            $this->export_csv($result);
        }
        return $result; 
    }
    
    //将数据导出为excel表的方法
    private function export_csv($result){
        if($result['data']){
            $account_value = array('id'=>'""','customer_name'=>'""','contract_name'=>'""','name'=>'""','price'=>'""','pay_time'=>'""','owner_name'=>'""','creator_name'=>'""','create_time'=>'""','status'=>'""');
            $content = iconv("utf-8","gbk","编号,客户,合同编号,应付款名,应付金额,应付时间,负责人,创建人,创建时间,状态");
            $content = $content . "\n";
            foreach($result['data'] as $k=>$v){	
                $account_value = array();
                $account_value['id']      = iconv('utf-8','gbk','"' . $v['id'] . '"');
                $account_value['customer_name'] = iconv('utf-8','gbk','"' . $v['customer_name'] . '"');
                $account_value['contract_name']   = iconv('utf-8','gbk','"' . $v['contract_name'] . '"');
                $account_value['name']   = iconv('utf-8','gbk','"' . $v['name'] . '"');
                $account_value['price']   = iconv('utf-8','gbk','"' . $v['price'] . '"');
                $account_value['pay_time']  = iconv('utf-8','gbk','"' . $v['pay_time'] . '"');
                $account_value['owner_name']  = iconv('utf-8','gbk','"' . $v['owner_name'] . '"');
                $account_value['creator_name']  = iconv('utf-8','gbk','"' . $v['creator_name'] . '"');
                $account_value['create_time']  = iconv('utf-8','gbk','"' . $v['create_time'] . '"');
                $account_value['status']  = iconv('utf-8','gbk','"' . $v['status'] . '"');
                $content .= implode(",", $account_value) . "\n";
            }
            $content .= iconv('utf-8','gbk','合计, , , ,"' . $result['statis']['money'] . '"');
            header("Content-Disposition: attachment; filename=jxch_travel_invite_list.csv");
	    echo $content; 
            exit;
        }
    }
    

}
