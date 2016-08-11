<?php

class FinanceAction extends Action
{

    //应收款业务逻辑处理模型
    protected $_receivables_mod;

    //初始化
    public function _initialize()
    {
        $action = array(
            'permission' => array(),
            'allow'      => array('contractdialog','contract_return','changecontent', 'listdialog', 'revert', 'adddialog', 'analytics', 'checkout', 'getmonthlyreceive', 'getyearreceivecomparison', 'getreceivablesmoney',
                'getpayablesmoney','financecheck','calendar','show_pay_details')
        );
        B('Authenticate', $action);

        $this->type = trim($_GET['t']) ? trim($_GET['t']) : 'receivables';
        if (!in_array($this->type, array('receivables', 'payables', 'receivingorder', 'paymentorder','calendar','show_pay_details'))) {
            alert('error', L('PARAMETER_ERROR'), U('index/index'));
        }
        //实例化业务逻辑处理类
        $this->_receivables_mod = D('Finance');
        $this->calendarAuthority();
    }
    public function changecontent()
    {
        $where                                 = array();
        $params                                = array();
        $order                                 = "";
        $p                                     = !$_REQUEST['p'] || $_REQUEST['p'] <= 0 ? 1 : intval($_REQUEST['p']);
        $below_ids                             = getSubRoleId();
        $where[$this->type . '.is_deleted']    = 0;
        $where[$this->type . '.owner_role_id'] = array('in', implode(',', $below_ids));
        $where['receivables.status']           = array('neq', 2);
        if ($_REQUEST["field"]) {
            $field     = trim($_REQUEST['field']) == 'all' ? $this->type . '.name|' . $this->type . '.description' : $this->type . '.' . $_REQUEST['field'];
            $search    = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
            $condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);
            if ('create_time' == $field || 'update_time' == $field) {
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
                default : $where[$field] = array('eq', $search);
            }
        }
        $order = empty($order) ? $this->type . '.update_time desc' : $order;

        switch ($this->type) {
            case 'receivables' :
                $receivables = D('ReceivablesView');
                $list        = $receivables->order($order)->where($where)->page($p . ',10')->select();

                foreach ($list as $k => $v) {
                    $list[$k]['owner']    = getUserByRoleId($v['owner_role_id']);
                    $list[$k]['pay_time'] = date("Y-m-d", $v['pay_time']);
                }

                $count         = $receivables->where($where)->count();
                $data['list']  = $list;
                $data['p']     = $p;
                $data['count'] = $count;
                $data['total'] = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
                $this->ajaxReturn($data, "", 1);
                break;
            case 'payables' :
                $payables      = D('PayablesView');
                $list          = $payables->order($order)->where($where)->page($p . ',10')->select();

                foreach ($list as $k => $v) {
                    $list[$k]['owner']    = getUserByRoleId($v['owner_role_id']);
                    $list[$k]['pay_time'] = date("Y-m-d", $v['pay_time']);
                }
                $count         = $payables->where($where)->count();
                $data['list']  = $list;
                $data['p']     = $p;
                $data['count'] = $count;
                $data['total'] = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
                $this->ajaxReturn($data, "", 1);
                break;
        }
    }

    //首页数据展示
    public function index()
    {

        //获取财务相关数据
        $result         = $this->_receivables_mod->getIndex($this->type);
        $this->listrows = $result['listrows'];
        $this->alert    = parseAlert();
        $this->assign('page', $result['show']);
        $this->assign('money', $result['money']);
        $this->assign('sum_money', $result['sum_money']);
        $this->assign('list', $result['list']);
        $this->display($this->type);
    }
    
    //还款日历
    public function calendar(){
        $dMonth = $_GET['dMonth'];
        if(empty($dMonth)){
            $dMonth = date('Y-m');
        }
        $result = $this->_receivables_mod->getRepayCalendar($dMonth);
        $this->assign("week_info", $result['week_info']);
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("static", $result['static']);
        
        $last_month_stamp = strtotime('-1 month',strtotime($dMonth));
        $next_month_stamp = strtotime('+1 month',strtotime($dMonth));
        $last_month = date('Y-m',$last_month_stamp);
        $next_month = date('Y-m',$next_month_stamp);
        $month = date('Y年m月',strtotime($dMonth));
        $this->assign("month", $month);
        $this->assign("last_month", $last_month);
        $this->assign("next_month", $next_month);
        $this->assign("today", date("Y-m-d"));
        $this->display();
    }
    
    //还款详情
    public function show_pay_details(){
        $start_time = $_REQUEST["start_time"] ? strtotime($_REQUEST["start_time"].' 00:00:00') : strtotime('-1 day');
        $end_time   = $_REQUEST["start_time"] ? strtotime($_REQUEST["end_time"].' 23:59:59')   : time();
        $is_csv     = $_REQUEST["csv"] ? true :false;
        $result = $this->_receivables_mod->getRepayDetail($start_time,$end_time,$is_csv);
        $this->assign("repay_data", $result['data']);
        $this->assign("statis", $result['statis']);
        $this->display();
    }

    //添加财务数据
    public function add()
    {
        $finance_type = $this->type;
        //添加财务相关数据
        if ($_POST['submit']) {
            $result = $this->_receivables_mod->addFinanceData($finance_type);
            alert($result['status'], $result['info'], $result['url']);
        } else {
            $this->alert = parseAlert();
            $this->display($finance_type . 'add');
        }
    }

    //编辑相关财务数据
    public function edit()
    {
        $finance_type = $this->type;
        $result       = $this->_receivables_mod->editFinanceData($finance_type);
        if ($_POST['submit']) {
            alert($result['status'], $result['info'], $result['url']);
        } else {
            if ($result['display_url']) {
                $this->refer_url = $_SERVER['HTTP_REFERER'];
                $this->alert     = parseAlert();
                $this->assign("info", $result['data']);
                $this->display($result['display_url']);
            } else {
                alert($result['status'], $result['info'], $result['url']);
            }
        }
    }

    public function view()
    {
        $id = intval($_GET['id']);
        if ($id == 0)
            alert('error', L('PARAMETER_ERROR'), U('finance/index', 't=' . $this->type));
        switch ($this->type) {

            case 'receivables' :
                $receivables            = D('ReceivablesView');
                $receivingorder         = D('ReceivingorderView');
                $info                   = $receivables->where(array('receivables_id' => $id))->find();
                if (empty($info))
                    alert('error', L('RECORD NOT EXIST', array('')), U('finance/index', 't=' . $this->type));
                $info['receivingorder'] = $receivingorder->where('receivingorder.is_deleted <> 1 and receivingorder.receivables_id = %d', $id)->select();
                $num                    = 0;     //已收款金额
                $num_unCheckOut         = 0;  //未结账状态的金额
                $num_unReceivables      = 0;  //还剩多少金额未收款
                foreach ($info['receivingorder'] as $k => $v) {
                    $info['receivingorder'][$k]['money'] = $info['receivingorder'][$k]['money'] / 100;
                    $v['money']                          = $v['money'] / 100;
                    if ($v['status'] == 1) {
                        //计算已结账状态的金额
                        $info['receivingorder'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                        $num                                 = $num + $v['money'];
                    } else {
                        //未结账状态的金额
                        $info['receivingorder'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                        $num_unCheckOut                      = $num_unCheckOut + $v['money'];
                    }
                }
               $info['role_name'] =  session('role_name');
               $info['admin'] =  session('admin');
                //金额格式化
                $info['price']             = $info['price'] / 100;
                $num_unReceivables         = ($info['price'] - $num) < 0 ? 0 : ($info['price'] - $num);
                $info['num']               = $num;
                $info['num_unReceivables'] = $num_unReceivables;
                $info['num_unCheckOut']    = $num_unCheckOut;
                $info['owner']             = getUserByRoleId($info['owner_role_id']);

                 $info['contract'] = D('Contract')->where(array("contract_id" => $info["contract_id"]))->find();
                 $info['contract']['number_pid'] = D('Contract')->where(array("contract_id" => $info['contract']["pid_contract_id"]))->getField("number");
                $this->assign('info', $info);
                $this->alert               = parseAlert();
                $this->display('receivablesview');
                break;
            case 'payables' :
                $payables                  = D('PayablesView');
                $paymentorder              = D('PaymentorderView');
                $info                      = $payables->where(array('payables_id' => $id))->find();
                if (empty($info))
                    alert('error', L('RECORD NOT EXIST', array('')), U('finance/index', 't=' . $this->type));
                $info['paymentorder']      = $paymentorder->where('paymentorder.is_deleted <> 1 and paymentorder.payables_id = %d', $id)->select();
                $num                       = 0;     //已付款金额
                $num_unCheckOut            = 0;  //未结账状态的金额
                $num_unPayment             = 0;   //还剩多少金额未付款
                foreach ($info['paymentorder'] as $k => $v) {
                    $info['paymentorder'][$k]['money'] = $info['paymentorder'][$k]['money'] / 100;
                    $v['money']                        = $v['money'] / 100;
                    if ($v['status'] == 1) {
                        //计算已结账状态的金额
                        $info['paymentorder'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                        $num += $v['money'];
                    } else {
                        //未结账状态的金额
                        $info['paymentorder'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
                        $num_unCheckOut += $v['money'];
                    }
                }
                //金额格式化
                $info['price']          = $info['price'] / 100;
                $num_unPayment          = ($info['price'] - $num) < 0 ? 0 : ($info['price'] - $num);
                $info['num']            = $num;
                $info['num_unPayment']  = $num_unPayment;
                $info['num_unCheckOut'] = $num_unCheckOut;
                $info['owner']          = getUserByRoleId($info['owner_role_id']);
                 $info['role_name'] =  session('role_name');
               $info['admin'] =  session('admin');
                if($info['redeem_time']){
                    $info['act_pay_time'] = strtotime("+5 days",$info['redeem_time']);
                }  
                $pid_contract_id = D('Contract')->where(array("contract_id" => $info["contract_id"]))->getField("pid_contract_id");
                $info['number_pid'] = D('Contract')->where(array("contract_id" => $pid_contract_id))->getField("number");
                $this->assign('info', $info);
                $this->alert            = parseAlert();
                $this->display('payablesview');
                break;
            case 'receivingorder' :
                $receivingorder         = D('ReceivingorderView');
                $info                   = $receivingorder->where(array('receivingorder_id' => $id))->find();
                if (empty($info))
                    alert('error', L('RECORD NOT EXIST', array('')), U('finance/index', 't=' . $this->type));
                $info['owner']          = getUserByRoleId($info['owner_role_id']);
                $contract_id            = M('receivables')->where(array('receivables_id' => $info['receivables_id']))->getField('contract_id');
                $info['other']          = D('ContractView')->where(array('contract_id' => $contract_id))->find();
                //金额格式化
                $info['money']          = $info['money'] / 100;
                $this->assign('info', $info);
//                echo '<pre>';var_dump($info);echo '</pre>';die;
                $this->alert            = parseAlert();

                if($_GET['act']=='print_view'){
                    if(strpos($info['department_name'], '桐庐')!==false){
                        $this->assign("mark",true);
                    }else{
                        $this->assign("mark",false);
                    }
                    $this->display('print_view');
                }else{
                    $this->display('receivingorderview');
                }
                break;
            case 'paymentorder' :
                $paymentorder           = D('PaymentorderView');
                $info                   = $paymentorder->where(array('paymentorder_id' => $id))->find();
                if (empty($info))
                    alert('error', L('RECORD NOT EXIST', array('')), U('finance/index', 't=' . $this->type));
                $info['owner']          = getUserByRoleId($info['owner_role_id']);
                $contract_id            = M('payables')->where(array('payables_id' => $info['payables_id']))->getField('contract_id');
                $info['other']          = D('ContractView')->where(array('contract_id' => $contract_id))->find();
                //金额格式化
                $info['money']          = $info['money'] / 100;
                $this->assign('info', $info);
                $this->alert            = parseAlert();
                $this->display('paymentorderview');
                break;
        }
    }

    //合同退回窗口
    public function contractdialog()
    {
        $contract_id = $_REQUEST["contract_id"];
        $this->assign("contract_id", $contract_id);
        $this->display();
    }

    //合同退回
    function contract_return(){
        $contract_data['contract_id']    = $_REQUEST['contract_id'];
        $contract_data['examine_status'] = $_REQUEST['examine_status'];
        $contract_data['examine_remark'] = $_REQUEST['examine_remark'];
        $result = $this->_receivables_mod->contract_return($contract_data);
        $this->ajaxReturn($result, $result["info"], $result["status"]);
    }

    public function delete()
    {
        switch ($this->type) {
            case 'receivables' :
                $receivables_ids    = is_array($_REQUEST['receivables_id']) ? implode(',', $_REQUEST['receivables_id']) : $_REQUEST['id'];
                if ($receivables_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));
                $receivables        = M('receivables');
                $receivingorder     = M('Receivingorder');
                //如果应收款下有收款单记录，提示先删除收款单
                $error_tip          = '';
                $receivables_record = $receivables->where('is_deleted <> 1 and receivables_id in (' . $receivables_ids . ')')->select();
                $data               = array('is_deleted' => 1, 'delete_role_id' => session('role_id'), 'delete_time' => time());
                foreach ($receivables_record as $k => $v) {
                    $receivingorder_record = $receivingorder->where('receivables_id = %d', $v['receivables_id'])->count();
                    if ($receivingorder_record == 0) {

                        if (!$receivables->where('receivables_id = %d', $v)->setField($data)) {
                            alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        $error_tip .= $v['name'] . ',';
                        actionLog($v, 't=receivables');
                    }
                }
                if ($error_tip) {
                    alert('error', L('PARTIAL DELETION FAILED', array($error_tip)), $_SERVER['HTTP_REFERER']);
                } else {
                    if ($_GET['refer']) {
                        alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('success', L('DELETED SUCCESSFULLY'), U('finance/index', 't=' . $this->type));
                    }
                }
                break;
            case 'payables' :
                $payables_ids = is_array($_REQUEST['payables_id']) ? implode(',', $_REQUEST['payables_id']) : $_REQUEST['id'];
                if ($payables_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));

                $payables        = M('Payables');
                $paymentorder    = M('Paymentorder');
                //如果应付款下有付款单记录，提示先删除付款单
                $error_tip       = '';
                $payables_record = $payables->where('is_deleted <> 1 and payables_id in (' . $payables_ids . ')')->select();
                $data            = array('is_deleted' => 1, 'delete_role_id' => session('role_id'), 'delete_time' => time());
                foreach ($payables_record as $k => $v) {
                    $paymentorder_record = $paymentorder->where('payables_id = %d', $v['payables_id'])->count();
                    if ($paymentorder_record == 0) {
                        if (!$payables->where('payables_id = %d', $v)->setField($data)) {
                            actionLog($v, 't=payables');
                            alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        $error_tip .= $v['name'] . ',';
                        actionLog($v, 't=payables');
                    }
                }
                if ($error_tip) {
                    alert('error', L('PARTIAL DELETION FAILED', array($error_tip)), $_SERVER['HTTP_REFERER']);
                } else {
                    if ($_GET['refer']) {
                        alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('success', L('DELETED SUCCESSFULLY'), U('finance/index', 't=' . $this->type));
                    }
                }
                break;
            case 'receivingorder' :
                $receivingorder_ids = is_array($_REQUEST['receivingorder_id']) ? implode(',', $_REQUEST['receivingorder_id']) : $_REQUEST['id'];
                if ($receivingorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));
                $receivingorder     = M('receivingorder');
                $data               = array('is_deleted' => 1, 'delete_role_id' => session('role_id'), 'delete_time' => time());
                if ($receivingorder->where('receivingorder_id in (%s)', $receivingorder_ids)->setField($data)) {
                    $receivingorder_idsArr = explode(',', $receivingorder_ids);
                    foreach ($receivingorder_idsArr as $v) {
                        actionLog($v, 't=receivingorder');
                    }
                    if ($_GET['refer']) {
                        alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('success', L('DELETED SUCCESSFULLY'), U('finance/index', 't=' . $this->type));
                    }
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'paymentorder' :
                $paymentorder_ids = is_array($_REQUEST['paymentorder_id']) ? implode(',', $_REQUEST['paymentorder_id']) : $_REQUEST['id'];
                if ($paymentorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));
                $paymentorder     = M('paymentorder');
                $data             = array('is_deleted' => 1, 'delete_role_id' => session('role_id'), 'delete_time' => time());
                if ($paymentorder->where('paymentorder_id in (%s)', $paymentorder_ids)->setField($data)) {
                    $paymentorder_idsArr = explode(',', $paymentorder_ids);
                    foreach ($paymentorder_idsArr as $v) {
                        actionLog($v, 't=paymentorder');
                    }
                    if ($_GET['refer']) {
                        alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('success', L('DELETED SUCCESSFULLY'), U('finance/index', 't=' . $this->type));
                    }
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
        }
    }

    public function revert()
    {
        $id = intval($_GET['id']);
        if ($id == 0)
            alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
        switch ($this->type) {
            case 'receivables' :
                $receivables = M('receivables');
                $info        = $receivables->where('receivables_id = %s', $id)->find();
                if (session('?admin') || $info['delete_role_id'] == session('role_id')) {
                    if ($receivables->where('receivables_id = %s', $id)->setField('is_deleted', 0)) {
                        actionLog($id, 't=receivables');
                        alert('success', L('RESTORE SUCCESSFUL'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('error', L('RESTORE FAILURE'), $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    alert('error', L('HAVE NOT PRIVILEGES'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'payables' :
                $payables = M('payables');
                $info     = $payables->where('payables_id = %s', $id)->find();
                if (session('?admin') || $info['delete_role_id'] == session('role_id')) {
                    if ($payables->where('payables_id = %s', $id)->setField('is_deleted', 0)) {
                        actionLog($id, 't=payables');
                        alert('success', L('RESTORE SUCCESSFUL'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('error', L('RESTORE FAILURE'), $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    alert('error', L('HAVE NOT PRIVILEGES'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'receivingorder' :
                $receivingorder = M('receivingorder');
                $info           = $receivingorder->where('receivingorder_id = %s', $id)->find();
                if (session('?admin') || $info['delete_role_id'] == session('role_id')) {
                    if ($receivingorder->where('receivingorder_id = %s', $id)->setField('is_deleted', 0)) {
                        actionLog($id, 't=receivingorder');
                        alert('success', L('RESTORE SUCCESSFUL'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('error', L('RESTORE FAILURE'), $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    alert('error', L('HAVE NOT PRIVILEGES'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'paymentorder' :
                $paymentorder = M('paymentorder');
                $info         = $paymentorder->where('paymentorder_id = %s', $id)->find();
                if (session('?admin') || $info['delete_role_id'] == session('role_id')) {
                    if ($paymentorder->where('paymentorder_id = %s', $id)->setField('is_deleted', 0)) {
                        actionLog($id, 't=paymentorder');
                        alert('success', L('RESTORE SUCCESSFUL'), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('error', L('RESTORE FAILURE'), $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    alert('error', L('HAVE NOT PRIVILEGES'), $_SERVER['HTTP_REFERER']);
                }
                break;
        }
    }

    public function completedelete()
    {
        if (!session('?admin'))
            alert('error', L('HAVE NOT PRIVILEGES'), $_SERVER['HTTP_REFERER']);
        switch ($this->type) {
            case 'receivables' :
                $receivables_ids = is_array($_REQUEST['receivables_id']) ? implode(',', $_REQUEST['receivables_id']) : $_REQUEST['id'];
                if ($receivables_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
                $receivables     = M('receivables');
                if ($receivables->where('receivables_id in (%s)', $receivables_ids)->delete()) {
                    $receivables_idsArr = explode(',', $receivables_ids);
                    foreach ($receivables_idsArr as $v) {
                        actionLog($v, 't=receivables');
                    }
                    alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'payables' :
                $payables_ids = is_array($_REQUEST['payables_id']) ? implode(',', $_REQUEST['payables_id']) : $_REQUEST['id'];
                if ($payables_ids == '')
                    alert('error', '没有选中任何信息', $_SERVER['HTTP_REFERER']);
                $payables     = M('payables');
                if ($payables->where('payables_id in (%s)', $payables_ids)->delete()) {
                    $payables_idsArr = explode(',', $payables_ids);
                    foreach ($payables_idsArr as $v) {
                        actionLog($v, 't=payables');
                    }
                    alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'receivingorder' :
                $receivingorder_ids = is_array($_REQUEST['receivingorder_id']) ? implode(',', $_REQUEST['receivingorder_id']) : $_REQUEST['id'];
                if ($receivingorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
                $receivingorder     = M('receivingorder');
                if ($receivingorder->where('receivingorder_id in (%s)', $receivingorder_ids)->delete()) {
                    $receivingorder_idsArr = explode(',', $receivingorder_ids);
                    foreach ($receivingorder_idsArr as $v) {
                        actionLog($v, 't=receivingorder');
                    }
                    alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'paymentorder' :
                $paymentorder_ids = is_array($_REQUEST['paymentorder_id']) ? implode(',', $_REQUEST['paymentorder_id']) : $_REQUEST['id'];
                if ($paymentorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
                $paymentorder     = M('paymentorder');
                if ($paymentorder->where('paymentorder_id in (%s)', $paymentorder_ids)->delete()) {
                    $paymentorder_idsArr = explode(',', $paymentorder_ids);
                    foreach ($paymentorder_idsArr as $v) {
                        actionLog($v, 't=paymentorder');
                    }
                    alert('success', L('DELETED SUCCESSFULLY'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('error', L('DELETE FAILED CONTACT THE ADMINISTRATOR'), $_SERVER['HTTP_REFERER']);
                }
                break;
        }
    }

    public function listdialog()
    {
        $receivables = D('ReceivablesView');
        $all_ids     = implode(',', getSubRoleId());
        switch ($this->type) {
            case 'receivables' :
                $list  = $receivables->where("receivables.is_deleted = 0 and receivables.status <> 2 and receivables.owner_role_id in($all_ids)")->order('receivables.update_time desc')->limit(10)->select();
                $count = $receivables->where("receivables.is_deleted = 0 and receivables.status <> 2 and receivables.owner_role_id in($all_ids)")->count();

                $this->total     = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
                $this->count_num = $count;
                $this->assign('receivablesList', $list);
                $this->display('receivableslistdialog');
                break;
            case 'payables' :
                $payables        = D('PayablesView');

                $this->payablesList = $payables->where("payables.is_deleted = 0 and payables.status <> 2 and payables.owner_role_id in($all_ids)")->order('payables.update_time desc')->limit(10)->select();
                $count              = $payables->where("payables.is_deleted = 0 and payables.status <> 2 and payables.owner_role_id in($all_ids)")->count();
                $this->total        = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
                $this->count_num    = $count;
                $this->display('payableslistdialog');
                break;
        }
    }

    public function adddialog()
    {
        $contract_id = $this->_get('contract_id', 'intval', 0);
        if ($contract_id == 0) {
            $id = $this->_get('id', 'intval', 0);
            $this->assign('id', $id);
        } else {
            $contract_id = intval($_GET['contract_id']);
            $this->assign('contract_id', $contract_id);
            $business_id = M('contract')->where(array('contract_id' => $contract_id))->getField('business_id');
            $customer_id = M('business')->where(array('business_id' => $business_id))->getField('customer_id');
            $this->assign('customer_id', $customer_id);
        }
        switch ($this->type) {
            case 'receivables' :
                $this->refer_url  = $_SERVER['HTTP_REFERER'];
                $this->display('receivablesadddialog');
                break;
            case 'payables' :
                $this->refer_url  = $_SERVER['HTTP_REFERER'];
                $this->display('payablesadddialog');
                break;
            case 'receivingorder' :
                $m_receivables    = M('Receivables');
                $m_receivingorder = M('Receivingorder');
                $receivables      = $m_receivables->where('is_deleted <> 1 and receivables_id = %d', $id)->find();
                $receivingorder   = $m_receivingorder->where('is_deleted <> 1 and receivables_id = %d', $receivables['receivables_id'])->select();

                $receivables_money = 0; //已收款总计
                foreach ($receivingorder as $v) {
                    $receivables_money += $v['money'];
                }
                $this->assign('receivables_money', $receivables_money);
                $this->assign('receivables', $receivables);
                $this->display('receivingorderadddialog');
                break;
            case 'paymentorder' :
                $m_payables     = M('Payables');
                $m_paymentorder = M('Paymentorder');
                $payables       = $m_payables->where('is_deleted <> 1 and payables_id = %d', $id)->find();
                $paymentorder   = $m_paymentorder->where('is_deleted <> 1 and payables_id = %d', $payables['payables_id'])->select();

                $payables_money = 0; //已收款总计
                foreach ($paymentorder as $v) {
                    $payables_money += $v['money'];
                }
                $this->assign('payables_money', $payables_money);
                $this->assign('payables', $payables);
                $this->display('paymentorderadddialog');
                break;
        }
    }

    public function editdialog()
    {
        $id = $this->_get('id', 'intval', 0);
        if ($id == 0) {
            alert('参数错误');
        }
        switch ($this->type) {
            case 'receivables' :
                $receivables               = M('receivables')->where('receivables_id=%d', $id)->find();
                $receivables['owner_name'] = M('user')->where('role_id=%d', $receivables['owner_role_id'])->getField('name');
                $this->refer_url           = U('contract/view', 'id=' . $receivables['contract_id']);
                $this->receivables         = $receivables;
                $this->display('receivableseditdialog');
                break;
            case 'payables' :
                $payables                  = M('payables')->where('payables_id=%d', $id)->find();
                $payables['owner_name']    = M('user')->where('role_id=%d', $payables['owner_role_id'])->getField('name');
                $this->refer_url           = U('contract/view', 'id=' . $payables['contract_id']);
                $this->payables            = $payables;
                $this->display('payableseditdialog');
                break;
        }
    }

    public function checkout()
    {
        switch ($this->type) {
            case 'receivingorder' :
                $receivingorder_ids = is_array($_REQUEST['receivingorder_id']) ? implode(',', $_REQUEST['receivingorder_id']) : $_REQUEST['id'];
                if ($receivingorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));
                $receivingorder     = M('receivingorder');
                $data               = array('status' => 1);
                if ($receivingorder->where('receivingorder_id in (%s)', $receivingorder_ids)->setField($data)) {
                    alert('success', L('SUCCESSFUL OPERATION'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('success', L('OPERATION FAILED'), $_SERVER['HTTP_REFERER']);
                }
                break;
            case 'paymentorder' :
                $paymentorder_ids = is_array($_REQUEST['paymentorder_id']) ? implode(',', $_REQUEST['paymentorder_id']) : $_REQUEST['id'];
                if ($paymentorder_ids == '')
                    alert('error', L('NOT CHOOSE ANY'), U('finance/index', 't=' . $this->type));
                $paymentorder     = M('paymentorder');
                $data             = array('status' => 1);
                if ($paymentorder->where('paymentorder_id in (%s)', $paymentorder_ids)->setField($data)) {
                    alert('success', L('OPERATION SUCCESSFUL'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('success', L('OPERATION FAILED'), $_SERVER['HTTP_REFERER']);
                }
                break;
        }
    }

    /**
     * 根据receivables_id获取应收金额
     *
     * */
    public function getreceivablesmoney()
    {
        $id = $_GET['id'];
        if ($id) {
            $m_receivables = M('receivables');
            //应收款总额
            $receivables   = $m_receivables->where('receivables_id = %d', $id)->getField('price');
            if (empty($receivables)) {
                $receivables = 0;
            }
            //已收款金额
            $m_receivingorder = M('receivingorder');
            $receivingorder   = $m_receivingorder->where('receivables_id = %d and status = 1', $id)->sum('money');
            if (empty($receivingorder)) {
                $receivingorder = 0;
            }
            $this->ajaxReturn(array('total' => $receivables, 'receivingorder' => $receivingorder), '', 1);
        }
    }

    /**
     * 根据payables_id获取应付金额
     *
     * */
    public function getpayablesmoney()
    {
        $id = $_GET['id'];
        if ($id) {
            $m_payables = M('payables');
            //应收款总额
            $payables   = $m_payables->where('payables_id = %d', $id)->getField('price');
            if (empty($payables)) {
                $payables = 0;
            }
            //已收款金额
            $m_paymentorder = M('paymentorder');
            $paymentorder   = $m_paymentorder->where('payables_id = %d and status = 1', $id)->sum('money');
            if (empty($paymentorder)) {
                $paymentorder = 0;
            }
            $this->ajaxReturn(array('total' => $payables, 'paymentorder' => $paymentorder), '', 1);
        }
    }

    public function analytics()
    {
        $m_shoukuan    = M('receivables');
        $m_shoukuandan = M('receivingorder');
        $m_fukuan      = M('payables');
        $m_fukuandan   = M('paymentorder');
        if ($_GET['role']) {
            $role_id = intval($_GET['role']);
        } else {
            $role_id = 'all';
        }
        if ($_GET['department'] && $_GET['department'] != 'all') {
            $department_id = intval($_GET['department']);
        } else {
            $department_id = D('RoleView')->where('role.role_id = %d', session('role_id'))->getField('department_id');
        }
        if ($_GET['start_time'])
            $start_time = strtotime(date('Y-m-d', strtotime($_GET['start_time'])));
        $end_time   = $_GET['end_time'] ? strtotime(date('Y-m-d 23:59:59', strtotime($_GET['end_time']))) : strtotime(date('Y-m-d 23:59:59', time()));
        if ($role_id == "all") {
            $roleList      = getRoleByDepartmentId($department_id);
            $role_id_array = array();
            foreach ($roleList as $v2) {
                $role_id_array[] = $v2['role_id'];
            }
            $where_role_id                   = array('in', implode(',', $role_id_array));
            $where_shoukuan['owner_role_id'] = $where_role_id;
        } else {
            $where_shoukuan['owner_role_id'] = $role_id;
        }
        $year                     = date('Y');
        $moon                     = 1;
        $shoukuan_moon_count      = array();
        $fukuan_moon_count        = array();
        $shijishoukuan_moon_count = array();
        $shijifukuan_moon_count   = array();
        while ($moon <= 12) {
            if ($moon == 12) {
                $where_shoukuan['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime(($year + 1) . '-1-1')), 'and');
            } else {
                $where_shoukuan['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime($year . '-' . ($moon + 1) . '-1')), 'and');
            }
            $shoukuanList              = $m_shoukuan->where($where_shoukuan)->select();
            $fukuanList                = $m_fukuan->where($where_shoukuan)->select();
            $total_shoukuan_money      = 0;
            $total_shijishoukuan_money = 0;
            foreach ($shoukuanList as $v) {
                $total_shoukuan_money += $v['price'];
                $shoukuandan_list = $m_shoukuandan->where('receivables_id = %d', $v['receivables_id'])->getField('money', true);
                foreach ($shoukuandan_list as $v2) {
                    $total_shijishoukuan_money += $v2;
                }
            }

            $total_fukuan_money      = 0;
            $total_shijifukuan_money = 0;
            foreach ($fukuanList as $v) {
                $total_fukuan_money += $v['price'];
                $fukuandan_list = $m_fukuandan->where('payables_id = %d', $v['payables_id'])->getField('money', true);
                foreach ($fukuandan_list as $v2) {
                    $total_shijifukuan_money += $v2;
                }
            }

            $shoukuan_moon_count[]      = $total_shoukuan_money;
            $shijishoukuan_moon_count[] = $total_shijishoukuan_money;
            $fukuan_moon_count[]        = $total_fukuan_money;
            $shijifukuan_moon_count[]   = $total_shijifukuan_money;
            $moon ++;
        }
        $moon_count['shoukuan']      = '[' . implode(',', $shoukuan_moon_count) . ']';
        $moon_count['shijishoukuan'] = '[' . implode(',', $shijishoukuan_moon_count) . ']';
        $moon_count['fukuan']        = '[' . implode(',', $fukuan_moon_count) . ']';
        $moon_count['shijifukuan']   = '[' . implode(',', $shijifukuan_moon_count) . ']';
        $this->moon_count            = $moon_count;

        $previous_year               = $year - 1;
        $moon                        = 1;
        $shoukuan_thisyear_count     = array();
        $shoukuan_previousyear_count = array();
        $fukuan_thisyear_count       = array();
        $fukuan_previousyear_count   = array();
        while ($moon <= 12) {
            if ($moon == 12) {
                $where_thisyear_shoukuan['pay_time']     = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime(($year + 1) . '-1-1')), 'and');
                $where_previousyear_shoukuan['pay_time'] = array(array('egt', strtotime($previous_year . '-' . $moon . '-1')), array('lt', strtotime(($previous_year + 1) . '-1-1')), 'and');
            } else {
                $where_thisyear_shoukuan['pay_time']     = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime($year . '-' . ($moon + 1) . '-1')), 'and');
                $where_previousyear_shoukuan['pay_time'] = array(array('egt', strtotime($previous_year . '-' . $moon . '-1')), array('lt', strtotime($previous_year . '-' . ($moon + 1) . '-1')), 'and');
            }

            $thisyear_shoukuanList     = $m_shoukuan->where($where_thisyear_shoukuan)->select();
            $previousyear_shoukuanList = $m_shoukuan->where($where_previousyear_shoukuan)->select();
            $thisyear_fukuanList       = $m_fukuan->where($where_thisyear_shoukuan)->select();
            $previousyear_fukuanList   = $m_fukuan->where($where_previousyear_shoukuan)->select();

            $total_thisyear_shoukuan_count     = 0;
            $total_previousyear_shoukuan_count = 0;
            foreach ($thisyear_shoukuanList as $v) {
                $total_thisyear_shoukuan_count += $v['price'];
            }
            foreach ($previousyear_shoukuanList as $v) {
                $total_previousyear_shoukuan_count += $v['price'];
            }
            $shoukuan_thisyear_count[]     = $total_thisyear_shoukuan_count;
            $shoukuan_previousyear_count[] = $total_previousyear_shoukuan_count;

            $total_thisyear_fukuan_count     = 0;
            $total_previousyear_fukuan_count = 0;
            foreach ($thisyear_fukuanList as $v) {
                $total_thisyear_fukuan_count += $v['price'];
            }
            foreach ($previousyear_fukuanList as $v) {
                $total_previousyear_fukuan_count += $v['price'];
            }
            $fukuan_thisyear_count[]     = $total_thisyear_fukuan_count;
            $fukuan_previousyear_count[] = $total_previousyear_fukuan_count;

            $moon ++;
        }

        $year_count['shoukuan_previousyear'] = '[' . implode(',', $shoukuan_previousyear_count) . ']';
        $year_count['shoukuan_thisyear']     = '[' . implode(',', $shoukuan_thisyear_count) . ']';
        $year_count['fukuan_previousyear']   = '[' . implode(',', $fukuan_previousyear_count) . ']';
        $year_count['fukuan_thisyear']       = '[' . implode(',', $fukuan_thisyear_count) . ']';
        $this->year_count                    = $year_count;

        //统计表内容
        $role_id_array = array();
        if ($role_id == "all") {
            if ($department_id != "all") {
                if (session('?admin')) {
                    $roleList = M('role')->where('user_id <> 0')->getField('role_id', true);
                } else {
                    $roleList = getRoleByDepartmentId($department_id);
                }
                foreach ($roleList as $v) {
                    $role_id_array[] = $v;
                }
            } else {
                $role_id_array = getSubRoleId();
            }
        } else {
            $role_id_array[] = $role_id;
        }

        if ($start_time) {
            $create_time = array(array('elt', $end_time), array('egt', $start_time), 'and');
        } else {
            $create_time = array('elt', $end_time);
        }
        //应收款数 未收款 部分收款 应收金额 实际收款金额 应付款数 未付款 部分付款 应付金额 实际付款金额
        $reportList                = array();
        $shoukuan_count_total      = 0;
        $weishou_count_total       = 0;
        $bufenshoukuan_count_total = 0;
        $shoukuan_money_total      = 0;
        $yishou_money_total        = 0;
        $shoukuandan_count_total   = 0;
        $fukuan_count_total        = 0;
        $weifu_count_total         = 0;
        $bufenfukuan_count_total   = 0;
        $fukuan_money_total        = 0;
        $yifu_money_total          = 0;
        $fukuandan_count_total     = 0;
        foreach ($role_id_array as $v) {
            $user = getUserByRoleId($v);

            $shoukuan_count      = 0;
            $weishou_count       = 0;
            $bufenshoukuan_count = 0;
            $shoukuan_money      = 0;
            $yishou_money        = 0;
            $shoukuandan_count   = 0;
            $fukuan_count        = 0;
            $weifu_count         = 0;
            $bufenfukuan_count   = 0;
            $fukuan_money        = 0;
            $yifu_money          = 0;
            $fukuandan_count     = 0;

            $shoukuan_count = $m_shoukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();

            $weishou_count       = $m_shoukuan->where(array('is_deleted' => 0, 'status' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $bufenshoukuan_count = $m_shoukuan->where(array('is_deleted' => 0, 'status' => 1, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();

            $shoukuandan_count   = $m_shoukuandan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $shoukuan_money      = round($m_shoukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->sum('price'), 2);
            $shoukuan_id_array   = $m_shoukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->getField('receivables_id', true);
            $shijishoukuan_money = 0;
            foreach ($shoukuan_id_array as $v2) {
                $shoukuandan_list = $m_shoukuandan->where('status = 1 and is_deleted=0 and receivables_id = %d', $v2)->getField('money', true);
                foreach ($shoukuandan_list as $v3) {
                    $shijishoukuan_money += $v3;
                }
            }
            $yishou_money = round($shijishoukuan_money, 2);

            $fukuan_count      = $m_fukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $weifu_count       = $m_fukuan->where(array('is_deleted' => 0, 'status' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $bufenfukuan_count = $m_fukuan->where(array('is_deleted' => 0, 'status' => 1, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $fukuandan_count   = $m_fukuandan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->count();
            $fukuan_money      = $n                 = round($m_fukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->sum('price'), 2);
            $fukuan_id_array   = $m_fukuan->where(array('is_deleted' => 0, 'owner_role_id' => $v, 'pay_time' => $create_time))->getField('payables_id', true);
            $shijifukuan_money = 0;
            foreach ($fukuan_id_array as $v4) {
                $fukuandan_list = $m_fukuandan->where('status = 1 and is_deleted=0 and payables_id = %d', $v4)->getField('money', true);
                foreach ($fukuandan_list as $v5) {
                    $shijifukuan_money += $v5;
                }
            }
            $yifu_money = round($shijifukuan_money, 2);

            $reportList[] = array("user"                => $user, "shoukuan_count"      => $shoukuan_count, "shoukuan_money"      => $shoukuan_money, "weishou_count"       => $weishou_count, "bufenshoukuan_count" => $bufenshoukuan_count, "yishou_money"        => $yishou_money, "shoukuandan_count"   => $shoukuandan_count,
                "fukuan_count"        => $fukuan_count, 'weifu_count'         => $weifu_count, "bufenfukuan_count"   => $bufenfukuan_count, "fukuan_money"        => $fukuan_money, "yifu_money"          => $yifu_money, "fukuandan_count"     => $fukuandan_count);

            $shoukuan_count_total += $shoukuan_count;
            $weishou_count_total += $weishou_count;
            $bufenshoukuan_count_total += $bufenshoukuan_count;
            $shoukuan_money_total += $shoukuan_money;
            $yishou_money_total += $yishou_money;
            $shoukuandan_count_total += $shoukuandan_count;
            $fukuan_count_total += $fukuan_count;
            $weifu_count_total += $weifu_count;
            $bufenfukuan_count_total += $bufenfukuan_count;
            $fukuan_money_total += $fukuan_money;
            $yifu_money_total += $yifu_money;
            $fukuandan_count_total += $fukuandan_count;
        }

        $total_report       = array("shoukuan_count" => $shoukuan_count_total, "weishou_count" => $weishou_count_total, "bufenshoukuan_count" => $bufenshoukuan_count_total, "shoukuan_money" => $shoukuan_money_total, "yishou_money" => $yishou_money_total, "shoukuandan_count" => $shoukuandan_count_total, "fukuan_count" => $fukuan_count_total, "weifu_count" => $weifu_count_total, "bufenfukuan_count" => $bufenfukuan_count_total, "fukuan_money" => $fukuan_money_total, "yifu_money" => $yifu_money_total, "fukuandan_count" => $fukuandan_count_total);
        $this->reportList   = $reportList;
        $this->total_report = $total_report;
        if (session('?admin')) {
            $idArray = M('role')->where('user_id <> 0')->getField('role_id', true);
        } else {
            $idArray = getSubRoleId();
        }
        $roleList = array();
        foreach ($idArray as $roleId) {
            $roleList[$roleId] = getUserByRoleId($roleId);
        }
        $this->roleList = $roleList;

        $departments      = M('roleDepartment')->select();
        $departmentList[] = M('roleDepartment')->where('department_id = %d', session('department_id'))->find();
        $departmentList   = array_merge($departmentList, getSubDepartment(session('department_id'), $departments, ''));
        $this->assign('departmentList', $departmentList);
        $this->display();
    }

    /**
     * 首页应收款月度统计
     * @ level 0:自己的数据  1:自己和下属的数据
     * */
    public function getmonthlyreceive()
    {
        $m_receivables            = M('receivables');
        $m_payables               = M('payables');
        $dashboard                = M('user')->where('user_id = %d', session('user_id'))->getField('dashboard');
        $widget                   = unserialize($dashboard);
        $where['creator_role_id'] = array('in', getSubRoleId());

        $year                = date('Y');
        $moon                = 1;
        $not_receive         = array(); //应收款
        $have_received       = array(); //实际收款
        $not_pay             = array(); //应付款
        $have_paid           = array(); //实际付款
        $where['is_deleted'] = array('eq', 0);
        while ($moon <= 12) {
            if ($moon == 12) {
                $where['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime(($year + 1) . '-1-1')), 'and');
            } else {
                $where['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime($year . '-' . ($moon + 1) . '-1')), 'and');
            }

            $not_receiveList     = $m_receivables->where($where)->select(); //应收款数组
            $monthly_not_receive = 0;
            foreach ($not_receiveList as $v) {
                $monthly_not_receive = floatval(bcadd($monthly_not_receive, $v['price'], 2)); //单月应收款总额
            }
            $not_receive[] = $monthly_not_receive;

            $condition             = $where;
            $condition['status']   = array('neq', 0);
            $have_receivedList     = $m_receivables->where($condition)->select(); //(部分)已收款数组
            $monthly_have_received = 0;
            foreach ($have_receivedList as $v) {
                $monthly_have_received += M('receivingorder')->where('receivables_id = %d and is_deleted = 0', $v['receivables_id'])->sum('money'); //单月实收款总额
            }
            $have_received[] = $monthly_have_received;

            $not_payList     = $m_payables->where($where)->select(); //应付款数组
            $monthly_not_pay = 0;
            foreach ($not_payList as $v) {
                $monthly_not_pay = floatval(bcadd($monthly_not_pay, $v['price'], 2)); //单月实收款总额
            }
            $not_pay[] = $monthly_not_pay;

            $have_paidList     = $m_payables->where($condition)->select(); //(部分)已收款数组
            $monthly_have_paid = 0;
            foreach ($have_paidList as $v) {
                $monthly_have_paid += M('paymentorder')->where('payables_id = %d and is_deleted = 0', $v['payables_id'])->sum('money'); //单月实收款总额
            }
            $have_paid[] = $monthly_have_paid;

            $moon ++;
        }
        $financeDate['not_receive']   = $not_receive;
        $financeDate['have_received'] = $have_received;
        $financeDate['not_pay']       = $not_pay;
        $financeDate['have_paid']     = $have_paid;
        $this->ajaxReturn($financeDate, 'success', 1);
    }

    /**
     * 首页应收款年度对比统计
     * @ level 0:自己的数据  1:自己和下属的数据
     * */
    public function getYearReceiveComparison()
    {
        $m_receivables            = M('receivables');
        $dashboard                = M('user')->where('user_id = %d', session('user_id'))->getField('dashboard');
        $widget                   = unserialize($dashboard);
        $where['creator_role_id'] = array('in', getSubRoleId());

        $year                    = date('Y');
        $prev_year               = $year - 1;
        $moon                    = 1;
        $receive_this_year_money = array();
        $receive_prev_year_money = array();
        $where['is_deleted']     = array('eq', 0);
        $where_this_year         = $where;
        $where_prev_year         = $where;
        while ($moon <= 12) {
            if ($moon == 12) {
                $where_this_year['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime(($year + 1) . '-1-1')), 'and');
                $where_prev_year['pay_time'] = array(array('egt', strtotime($prev_year . '-' . $moon . '-1')), array('lt', strtotime(($year) . '-1-1')), 'and');
            } else {
                $where_this_year['pay_time'] = array(array('egt', strtotime($year . '-' . $moon . '-1')), array('lt', strtotime($year . '-' . ($moon + 1) . '-1')), 'and');
                $where_prev_year['pay_time'] = array(array('egt', strtotime($prev_year . '-' . $moon . '-1')), array('lt', strtotime($prev_year . '-' . ($moon + 1) . '-1')), 'and');
            }

            $receive_this_year_price   = $m_receivables->where($where_this_year)->sum('price'); //今年月度收款金额总和
            $receive_prev_year_price   = $m_receivables->where($where_prev_year)->sum('price'); //去年月度收款金额总和
            $receive_this_year_money[] = empty($receive_this_year_price) ? 0 : round($receive_this_year_price, 2);
            $receive_prev_year_money[] = empty($receive_prev_year_price) ? 0 : round($receive_prev_year_price, 2);
            $moon ++;
        }

        $total_money = array('this_year' => $receive_this_year_money, 'prev_year' => $receive_prev_year_money);
        $this->ajaxReturn($total_money, 'success', 1);
    }


    //应该收应付审核
    public function financecheck()
    {
        $id = (int) $_GET['id'];
        if ('receivables' == $_GET['t']) {
            $receivables_info = D('receivables')->where(array('receivables_id' => $id))->find();
            if ($receivables_info['status'] == 2) {
                D('receivables')->where(array('receivables_id' => $id))->save(array('verify_status' => 1));
                //生成应付款 以及付款单（还款计划）
                $res = $this->_receivables_mod->makeMeet($receivables_info);
                if (!$res) {
                    $this->ajaxReturn(array('message' => '审核成功，但生成应付款记录失败'), 'success', 1);
                } else {
                    $this->ajaxReturn(array('message' => '审核成功，生成应付款记录成功'), 'success', 1);
                }
            } else {
                $this->ajaxReturn(array('message' => '操作失败请刷新重试'), 'error', 1);
            }
        }
        if ('payables' == $_GET['t']) {
            $payables_info = D('payables')->where(array('payables_id' => $id))->find();
            if ($payables_info['status'] == -1) {
                D('payables')->where(array('payables_id' => $id))->save(array('status' => 0));
                $this->ajaxReturn(array('message' => '审核成功'), 'success', 1);
            } else {
                $this->ajaxReturn(array('message' => '操作失败请刷新重试'), 'error', 1);
            }
        }
        $this->ajaxReturn(array('message' => '数据错误，请刷新后重试'), 'error', 1);
    }

    //打印预览
    public function print_view(){
       $id=  isset($_REQUEST['id'])?$_REQUEST['id']:0;
//       $contract=D("contract");
       echo '<meta charset="utf-8"><pre>';var_dump(D('Contract')->getContractPrintInfo($id));echo '</pre>';die;

    }

    public function get_funds_gangway(){
        $fundsGangway = fundsGangway();
        $this->ajaxReturn($fundsGangway);
    }
    
    private function calendarAuthority(){
        $allow = false;
        $role_name = D('RoleView')->where('role.role_id = %d', session('role_id'))->getField('role_name');
        $arr = array('总经理','财务总监','会计经理','会计','出纳负责人','出纳','出纳','出纳','综合内勤');
        if(in_array($role_name,$arr)){
            $allow = true;
        }
        $this->assign('authority',$allow);
    }
    
}
