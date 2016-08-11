<?php

class ContractAction extends Action
{

    //合同业务逻辑模型类
    protected $_contract_mod;

    //初始化
    public function _initialize()
    {
         $action              = array(
            'permission' => array(),
            'allow'      => array('get_role_department_name','changecontent', 'submitcheck','listdialog', 'getcontractlist','getcustomerinfo','getproductinfo','getinvestincome','getproductjiexitime')
        );

        B('Authenticate', $action);
        $this->_contract_mod = D('Contract');
    }

    //合同首页展示列表
    public function index()
    {
        $contractInfoList = $this->_contract_mod->getContractInfoList();
        $this->listrows   = $contractInfoList['listrows'];
        $Page->parameter  = implode('&', $contractInfoList['params']);
        $this->assign('page', $contractInfoList['page']);
        $this->assign('list', $contractInfoList['list']);
        $this->alert      = parseAlert();
        $this->display();
    }

    //查看合同详情
    public function view()
    {
        $contract_id = intval($_REQUEST['id']);
        //权限判断
        if (!check_permission($contract_id, 'contract')) {
            $this->error(L('HAVE NOT PRIVILEGES'));
        }
        //合同是否存在
        if (0 == $contract_id) {
            alert('error', L('NOT CHOOSE ANY'), U('contract/index'));
        }
        $info = $this->_contract_mod->contractDetail($contract_id);
        if ($info["show_info"]) {
            alert($show_info["status"], $show_info["info"], $show_info["url"]);
        }
        $info['number_pid'] = M("Contract")->where(array("contract_id"=>$info["pid_contract_id"]))->getField("number");
        $this->assign('info', $info);
//        echo "<pre>";
//        print_r($info);exit;
        $this->alert = parseAlert();
        $this->display();
    }

    //添加合同
    public function add()
    {
        if ($this->isPost()) {
            $show_info = $this->_contract_mod->addContract();
            alert($show_info["status"], $show_info["info"], $show_info["url"]);
        } else {
            if (intval($_GET['business_id'])) {
                $this->assign('business_id', intval($_GET['business_id']));
                $this->assign('contract_custom', "HMT" . session("user_id") .date('Ymdhis'));
                $this->alert     = parseAlert();
                $this->refer_url = $_SERVER['HTTP_REFERER'];
                $this->display('adddialog');
            } else {
                //默认初始合同创建者
                $user_id         = D("role")->where(array("role_id" => session("role_id")))->getField("user_id");
                $user_name       = D("user")->where(array("user_id" => trim($user_id)))->getField("name");
                $this->assign('user_name', $user_name);
                $product_info    = M("product")->select();
                $this->assign('product_info', $product_info);
                $doc_type        = array('', "身份证", "居住证", "签证", "护照", "军人证", "驾驶证");
                $this->assign('doc_type', $doc_type);
                $this->assign('contract_custom', "HMT" . session("user_id") .date('Ymdhis'));
                $this->refer_url = $_SERVER['HTTP_REFERER'];
                $this->alert     = parseAlert();
                $this->display();
            }
        }
    }

    //编辑合同
    public function edit()
    {
        if (!$_REQUEST['id']) {
            alert("error", L('PARAMETER_ERROR'), $_SERVER['HTTP_REFERER']);
        }
        if (!check_permission($_REQUEST['id'], 'contract')) {
            $this->error(L('HAVE NOT PRIVILEGES'));
        }
        if ($_POST['submit']) {
            $show_info = $this->_contract_mod->editContract();
            alert($show_info["status"], $show_info["info"], $show_info["url"]);
        } else {

            $contract_info = D("ContractView")->where('contract.contract_id = %d', $_REQUEST['id'])->find();

            $contract_info = format_contract_info($contract_info);
            //部门ID
            $department_id = D("role")->where(array("user_id" => $contract_info['owner_role_id']))->getField("position_id");
            $product_name  = D("product")->where(array("product_id" => $contract_info['product_id']))->getField("name");

            //默认初始合同创建者
            $user_id   = D("role")->where(array("role_id" => $contract_info['original_creater']))->getField("user_id");
            $user_name = D("user")->where(array("user_id" => trim($user_id)))->getField("name");
            $this->assign('user_name', $user_name);

            $this->assign('product_name', $product_name);
            $this->assign('department_id', $department_id);
            $doc_type    = array('', "身份证", "居住证", "签证", "护照", "军人证", "驾驶证");
            $this->assign('doc_type', $doc_type);
            $this->assign('info', $contract_info);
            $this->alert = parseAlert();
            $this->display();
        }
    }

    //续存/转存
    function renew(){
        if ($this->isPost()) {
            $show_info = $this->_contract_mod->addContract();
            alert($show_info["status"], $show_info["info"], $show_info["url"]);
        } else {
            //判断合同剩余金额 还能不能续存
            $contract_info = D("ContractView")->where('contract.contract_id = %d', $_REQUEST['contract_id'])->find();
            //该合同下所有的子合同投资金额
            $all_investment_moneys = D("Contract")->where(array("pid_contract_id"=>$contract_info["contract_id"]))->getField("sum(investment_money)");
            if($all_investment_moneys >= $contract_info["investment_money"]){
                alert("error", "原始合同余额不足,剩余".(($contract_info["investment_money"] - $all_investment_moneys) / 100).'元', U('contract/index'));
            }
            //利息付完才能续存 "pay_type"=>1
            $remain_interest = M("payables")->where(array("contract_id"=>$_REQUEST['contract_id'],"pay_type"=>1,"is_deleted"=>0,"status"=>array("in","-1,0,1")))->getField("sum(price)");
            if($remain_interest){
                alert("error", "该合同利息尚未付清，暂不能续存", U('contract/index'));
            }
            $contract_info = format_contract_info($contract_info);
            //部门ID
            $department_id = D("role")->where(array("user_id" => $contract_info['owner_role_id']))->getField("position_id");
            //默认初始合同创建者
            $user_id   = D("role")->where(array("role_id" => $contract_info['original_creater']))->getField("user_id");
            $user_name = D("user")->where(array("user_id" => trim($user_id)))->getField("name");
            $this->assign('user_name', $user_name);
            $this->assign('department_id', $department_id);
            $this->assign('info', $contract_info);
            //新合同生效时间
            $start_date = date("Y-m-d",strtotime("+1 day",$contract_info['end_date']));
            $this->assign('start_date', $start_date);
            $str_month = M("product")->where(array("product_id"=>$contract_info["product_id"]))->getField("str_month");
            $end_date = date("Y-m-d",strtotime("+".$str_month." months",strtotime($start_date)));
            $this->assign('end_date', $end_date);
            //产品信息
            $product_info    = M("product")->select();
            $this->assign('product_info', $product_info);
            $doc_type        = array('', "身份证", "居住证", "签证", "护照", "军人证", "驾驶证");
            $this->assign('doc_type', $doc_type);
            $this->assign('contract_custom', "HMT" . session("user_id") .date('Ymdhis'));
            $this->refer_url = $_SERVER['HTTP_REFERER'];
            $this->alert     = parseAlert();
            $this->display();
        }
    }

    //提前赎回申请
    function redeem_apply(){
        $contract_id = $_REQUEST["contract_id"];
        if($contract_id){
            M("Contract")->where(array("contract_id"=>$contract_id))->save(array("redeem_status"=>1));
            alert('success', "提前赎回申请成功",$_SERVER['HTTP_REFERER']);
        }else{
            alert('error', "提前赎回申请失败，请稍后重试！",$_SERVER['HTTP_REFERER']);
        }
    }
    
    //提前赎回
    function redeem(){
        if($this->isPost()){
            $contract_id = $_POST["contract_id"];
            $end_date = $_POST["end_date"];
            $total_receivables_money = $_POST["total_receivables_money"] * 100;
            if($total_receivables_money <= 0 ){
                alert('error', "剩余本息不足以抵扣赎回费用，无法赎回", U('contract/redeem',array("contract_id"=>$contract_id)));
            }
            //合同信息
            $contract_info = M("contract")->where(array("contract_id"=>$contract_id))->find();
            if($contract_info["renew_status"] == 1){
                alert('error', "你已进行过续存操作，无法赎回！", U('contract/redeem',array("contract_id"=>$contract_id)));
            }
            if($total_receivables_money > $contract_info["total_receivables_money"]){
                alert('error', "您输入的赎回金额超出合同本息金额，无法赎回", U('contract/redeem',array("contract_id"=>$contract_id)));
            }
            //原始应付款数据
            $payables_list = M("Payables")->where(array("contract_id"=>$contract_info['contract_id'],'is_deleted' => 0))->select();
            //生成赎回应付款
            $ids = $this->makeRedeemPayables($contract_info,$total_receivables_money,$end_date);
            if($ids){
                foreach($payables_list as $payables){
                    M("Payables")->where(array("payables_id"=>$payables["payables_id"],"status"=>array("in","-1,0,1")))->data(array("is_deleted"=>1,"renew_status"=>2))->save();
                }
                //更新合同状态 数据
                //renew_status 0 未进行续存赎回操作 1 已续存 2 已赎回
                M("Contract")->where(array("contract_id"=>$contract_id))->data(array("renew_status"=>2,"redeem_status"=>2,"redeem_time"=>strtotime($end_date)))->save();
                alert('success', "赎回操作成功", U('contract/index'));
            }else{
                alert('error', "赎回操作失败", U('contract/index'));
            }
        }else{
            $contract_id = intval($_REQUEST['contract_id']);
            //权限判断
            if (!check_permission($contract_id, 'contract')) {
                $this->error(L('HAVE NOT PRIVILEGES'));
            }
            //合同是否存在
            if (0 == $contract_id) {
                alert('error', L('NOT CHOOSE ANY'), U('contract/index'));
            }
            $info = $this->_contract_mod->contractDetail($contract_id);
            if ($info["show_info"]) {
                alert($show_info["status"], $show_info["info"], $show_info["url"]);
            }
            $this->assign('info', $info);
            $this->alert = parseAlert();
            $this->display();
        }
    }

    //生成赎回应付款
    function makeRedeemPayables($contract_info,$total_receivables_money,$end_date){
        //准备应付款数据
        $product_info  = M("product")->where(array('product_id' => $contract_info['product_id']))->find();
        $receivables = M("receivables")->where(array("contract_id"=>$contract_info['contract_id'],"is_deleted"=>0))->find();
        //产品类型分类 通类产品生产五个应付款 分别为四个季度的利息和一个原始还本金
        $data['name']  = "产品：" . $product_info['name'] . "，合同编号为" . $contract_info['number'] . "的到期还本（赎回）应付款";
        $data['price']           = $total_receivables_money ? $total_receivables_money : 0;
        $data['receivables_id']  = $receivables['receivables_id'];
        $data['customer_id']     = $contract_info["customer_id"];
        $data['contract_id']     = $contract_info['contract_id'];
        $data['description']     = $data['name'];
        $data['pay_time']        = strtotime($end_date);
        $data['creator_role_id'] = session('role_id');
        $data['owner_role_id']   = $contract_info['owner_role_id'];
        $data['create_time']     = time();
        $data['update_time']     = time();
        $data['status']          = -1;
        if($data['price'] <=0 ){
            $data['price'] = 0;
        }
        return M('payables')->add($data);
    }

    //回收站删除合同
    public function completeDelete()
    {
        $contract_id = is_array($_REQUEST['contract_id']) ? implode(',', $_REQUEST['contract_id']) : $_REQUEST['contract_id'];
        if ('' == $contract_id) {
            alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
        } else {

            if (M('contract')->where('contract_id in (%s)', $contract_id)->delete()) {
                M('rBusinessContract')->where(array('contract_id' => $contract_id))->delete();
                M('rContractProduct')->where(array('contract_id' => $contract_id))->delete();
                alert('success', L('DELETED SUCCESSFULLY'), U('contract/index', 'by=deleted'));
            } else {
                alert('error', L('DELETE FAILED'), $_SERVER['HTTP_REFERER']);
            }
        }
    }

    //回收站恢复合同
    public function revert()
    {
        $contract_id = isset($_GET['id']) ? intval(trim($_GET['id'])) : 0;
        if ($contract_id > 0) {
            $m_contract = M('contract');
            $contract   = $m_contract->where('contract_id = %d', $contract_id)->find();
//            if (session('?admin') || $contract['delete_role_id'] == session('role_id')) {
                if ($m_contract->where('contract_id = %d', $contract_id)->setField('is_deleted', 0)) {
                    alert('success', L('REDUCTION OF SUCCESS'), $_SERVER['HTTP_REFERER']);
                } else {
                    alert('error', L('REDUCTION OF FAILED'), $_SERVER['HTTP_REFERER']);
                }
//            } else {
//                alert('error', L('YOU HAVE NO PERMISSION TO RESTORE'), $_SERVER['HTTP_REFERER']);
//            }
        } else {
            alert('error', L('PARAMETER_ERROR'), $_SERVER['HTTP_REFERER']);
        }
    }

    //软删除合同
    public function delete()
    {
        $contract_ids = is_array($_REQUEST['contract_id']) ? $_REQUEST['contract_id'] : array($_REQUEST['contract_id']);
        if ('' == $contract_ids) {
            alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
        } else {
            $m_contract           = M('Contract');
            $m_receivables        = M('Receivables');
            $m_payables           = M('Payables');
            $m_r_contract_product = M('rContractProduct');
            $m_r_contract_file    = M('rContractFile');
            //如果合同下有产品，财务和文件信息，提示先删除产品，财务和文件数据。
            $data                 = array('is_deleted' => 1, 'delete_role_id' => session('role_id'), 'delete_time' => time());
            foreach ($contract_ids as $k => $v) {
                $contract             = $m_contract->where('contract_id = %d', $v)->find();
                $contract_product     = $m_r_contract_product->where('contract_id = %d', $v)->select(); //合同关联的产品记录
                $contract_file        = $m_r_contract_file->where('contract_id = %d', $v)->select(); //合同关联的文件
                $contract_receivables = $m_receivables->where('is_deleted <> 1 and contract_id = %d', $v)->select(); //合同关联的应收款
                $contract_payables    = $m_payables->where('is_deleted <> 1 and contract_id = %d', $v)->select(); //合同关联的应付款

                if (empty($contract_product) && empty($contract_file) && empty($contract_receivables) && empty($contract_payables)) {
                    if (!$m_contract->where('contract_id = %d', $v)->save($data)) {
                        alert('error', L('NOT CHOOSE ANY'), $_SERVER['HTTP_REFERER']);
                    }
                } else {
                    if (!empty($contract_product)) {
                        alert('error', L('DELETE_FAILED_PLEASE_DELETE_UNDER_THE_CONTRACT_OF_PRODUCT_INFORMATION', array($contract['number'])), $_SERVER['HTTP_REFERER']);
                    } elseif (!empty($contract_file)) {
                        alert('error', L('DELETE_FAILED_PLEASE_DELETE_UNDER_THE_CONTRACT_OF_PRODUCT_INFORMATION', array($contract['number'])), $_SERVER['HTTP_REFERER']);
                    } elseif (!empty($contract_receivables)) {
                        alert('error', L('DELETE_FAILED_PLEASE_DELETE_RECEIVABLES_UNDER_THE_FINANCIAL_INFORMATION_IN_THE_CONTRACT', array($contract['number'])), $_SERVER['HTTP_REFERER']);
                    } else {
                        alert('error', L('DELETE_FAILED_PLEASE_DELETE_RECEIVABLES_UNDER_THE_FINANCIAL_INFORMATION_IN_THE_CONTRACT', array($contract['number'])), $_SERVER['HTTP_REFERER']);
                    }
                }
            }
            alert('success', L('DELETED SUCCESSFULLY'), U('contract/index'));
        }
    }

    public function changeContent()
    {
        if ($this->isAjax()) {
            $contract = D('ContractView');
            $where    = array();

            $where['contract.is_deleted']    = 0;
            $where['contract.owner_role_id'] = array('in', implode(',', getSubRoleId()));

            if ($_REQUEST["field"]) {
                if (trim($_REQUEST['field']) == "all") {
                    $field = is_numeric(trim($_REQUEST['search'])) ? 'number|price|contract.description' : 'number|contract.description';
                } else {
                    $field = trim($_REQUEST['field']);
                }
                $search    = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
                $condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);

                if ('create_time' == $field || 'update_time' == $field || 'due_date' == $field) {
                    $search = is_numeric($search) ? $search : strtotime($search);
                }
                switch ($condition) {
                    case "is" : $where['contract.' . $field] = array('eq', $search);
                        break;
                    case "isnot" : $where['contract.' . $field] = array('neq', $search);
                        break;
                    case "contains" : $where['contract.' . $field] = array('like', '%' . $search . '%');
                        break;
                    case "not_contain" : $where['contract.' . $field] = array('notlike', '%' . $search . '%');
                        break;
                    case "start_with" : $where['contract.' . $field] = array('like', $search . '%');
                        break;
                    case "end_with" : $where['contract.' . $field] = array('like', '%' . $search);
                        break;
                    case "is_empty" : $where['contract.' . $field] = array('eq', '');
                        break;
                    case "is_not_empty" : $where['contract.' . $field] = array('neq', '');
                        break;
                    case "gt" : $where['contract.' . $field] = array('gt', $search);
                        break;
                    case "egt" : $where['contract.' . $field] = array('egt', $search);
                        break;
                    case "lt" : $where['contract.' . $field] = array('lt', $search);
                        break;
                    case "elt" : $where['contract.' . $field] = array('elt', $search);
                        break;
                    case "eq" : $where['contract.' . $field] = array('eq', $search);
                        break;
                    case "neq" : $where['contract.' . $field] = array('neq', $search);
                        break;
                    case "between" : $where['contract.' . $field] = array('between', array($search - 1, $search + 86400));
                        break;
                    case "nbetween" : $where['contract.' . $field] = array('not between', array($search, $search + 86399));
                        break;
                    case "tgt" : $where['contract.' . $field] = array('gt', $search + 86400);
                        break;
                    default : $where[$field]             = array('eq', $search);
                }
            }

            $p     = !$_REQUEST['p'] || $_REQUEST['p'] <= 0 ? 1 : intval($_REQUEST['p']);
            $list  = $contract->where($where)->page($p . ',10')->order('contract.create_time desc')->select();
            $count = $contract->where($where)->count();
            foreach ($list as $key => $value) {
                $list[$key]['owner']   = getUserByRoleId($value['owner_role_id']);
                $list[$key]['creator'] = getUserByRoleId($value['creator_role_id']);
                $list[$key]['deletor'] = getUserByRoleId($value['delete_role_id']);
            }
            $data['list']  = $list;
            $data['p']     = $p;
            $data['count'] = $count;
            $data['total'] = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
            $this->ajaxReturn($data, "", 1);
        }
    }

    //通用弹出框
    public function listDialog()
    {
        $below_ids = getSubRoleId(true);
        $contract  = D('ContractView');

        $business_id = intval($_GET['bid']);
        if (!empty($business_id)) {
            $where['business_id'] = array('eq', $business_id);
        }
        $where['contract.owner_role_id'] = array('in', implode(',', $below_ids));
        $where['contract.is_deleted']    = 0;
        $list                            = $contract->where($where)->page('0,10')->order('create_time desc')->select();
        $count                           = $contract->where($where)->count();
        $this->total                     = $count % 10 > 0 ? ceil($count / 10) : $count / 10;
        $this->count_num                 = $count;
        $this->assign('contractList', $list);
        $this->display();
    }

    //ajax获取合同列表信息
    public function getcontractlist()
    {
        $contract = D('ContractView');
        $list     = $contract->where(array('contract.is_deleted' => 0))->select();
        $this->ajaxReturn($list, '', 1);
    }

    //获取产品信息
    public function getProductInfo()
    {
        $product_id   = $_REQUEST["product_id"];
        $invest_money = $_REQUEST["investment_money"];
        $start_date = $_REQUEST["start_date"];
        $qixi_type = $_REQUEST["qixi_type"];
        if (!$product_id) {
            $this->ajaxReturn("", '该产品信息不存在', 0);
            die();
        }
        $product_info                = D("product")->where(array("product_id" => $product_id))->find();
        $product_info['active_rate'] = $product_info['active_rate'] ? $product_info['active_rate'] : 0;
        if ($invest_money && $product_info) {
            $str_month                               = $product_info['str_month']; //封闭期
            $year_rate                               = $product_info['year_rate'] + $product_info['active_rate'];
            $year_rate                               = $year_rate / 100; //年利率
            $month_rate                              = (int) (($year_rate / 12) * 10000); //月利率
            $month_rate                              = $month_rate / 10000; //月利率
            $product_info["month_interest"]          = $invest_money * $month_rate;
            $product_info["total_interest"]          = $product_info["month_interest"] * $str_month;
            $product_info["total_receivables_money"] = $invest_money + $data["total_interest"];
        }
        if($start_date){
            if($product_id == 1){
                //3个月（季满盈） 按90天计息（按一年360天计）
                $product_info["end_date"] = date("Y-m-d",strtotime("+30 days",strtotime($start_date)));
            }else if($product_id == 2){
                //3个月（季满盈） 按90天计息（按一年360天计）
                $product_info["end_date"] = date("Y-m-d",strtotime("+90 days",strtotime($start_date)));
            }else if($product_id == 3 || $product_id == 4){
                //6个月（双季通，双季盈）按180天计息（按一年360天计）
                $product_info["end_date"] = date("Y-m-d",strtotime("+180 days",strtotime($start_date)));
            }else if($product_id == 5 || $product_id == 6){
                //12个月（年富通，年富盈）按365天计息（按一年365天计）
                $product_info["end_date"] = date("Y-m-d",strtotime("+365 days",strtotime($start_date)));
            }
            //起息类型
            if($qixi_type == 1){
                $product_info["end_date"] = $product_info["end_date"];//T+0;
            }else if($qixi_type == 2){
                $product_info["end_date"] = date("Y-m-d",strtotime("+1 days",strtotime($product_info["end_date"])));//T+1;
            }
        }
        $this->ajaxReturn($product_info, '', 1);
    }

    //计算产品到期时间
    public function getProductJiexiTime(){
        $product_id   = $_REQUEST["product_id"];
        $start_date = $_REQUEST["start_date"];
        $qixi_type = $_REQUEST["qixi_type"];
        if (!$product_id) {
            $this->ajaxReturn("", '该产品信息不存在', 0);
            die();
        }
        if($product_id == 1){
            //3个月（季满盈） 按90天计息（按一年360天计）
            $product_data["end_date"] = date("Y-m-d",strtotime("+30 days",strtotime($start_date)));
        }else if($product_id == 2){
            //3个月（季满盈） 按90天计息（按一年360天计）
            $product_data["end_date"] = date("Y-m-d",strtotime("+90 days",strtotime($start_date)));
        }else if($product_id == 3 || $product_id == 4){
            //6个月（双季通，双季盈）按180天计息（按一年360天计）
            $product_data["end_date"] = date("Y-m-d",strtotime("+180 days",strtotime($start_date)));
        }else if($product_id == 5 || $product_id == 6){
            //12个月（年富通，年富盈）按365天计息（按一年365天计）
            $product_data["end_date"] = date("Y-m-d",strtotime("+365 days",strtotime($start_date)));
        }
        //起息类型
        if($qixi_type == 1){
            $product_data["end_date"] = $product_data["end_date"];//T+0;
        }else if($qixi_type == 2){
            $product_data["end_date"] = date("Y-m-d",strtotime("+1 days",strtotime($product_data["end_date"])));//T+1;
        }
        $this->ajaxReturn($product_data, '', 1);
    }

    //获取客户信息
    public function getCustomerInfo()
    {
        $customer_name = $_REQUEST["customer_name"];
        if (!$customer_name) {
            $this->ajaxReturn("", '客户名不存在！', 0);
            die();
        }
        $customer_info = D("customer")->where(array("name" => $customer_name))->find();
        $this->ajaxReturn($customer_info, '', 1);
    }

    //获取相应收益
    public function getInvestIncome()
    {
        $product_id             = $_REQUEST["product_id"];
        $start_date             = $_REQUEST["start_date"];
        $pid_contract_id             = $_REQUEST["pid_contract_id"];
        $end_date             = $_REQUEST["end_date"];
        $invest_money           = $_REQUEST["invest_money"];
        $active_investment_rate = $_REQUEST["active_investment_rate"];
        if (!$product_id) {
            $this->ajaxReturn("", '请先选择产品信息！', 0);
            die();
        }
        if (!$invest_money) {
            $this->ajaxReturn("", '您输入金额不正确！', 0);
            die();
        }
        $product_info = D("product")->where(array("product_id" => $product_id))->find();

        $str_month                       = $product_info['str_month']; //封闭期
        $active_investment_rate          = $active_investment_rate ? $active_investment_rate : 0;
        $year_rate                       = $product_info['year_rate'] + $active_investment_rate;
        $year_rate                       = $year_rate / 100; //年利率
        //$month_rate                      = intval(($year_rate / 12) * 1000000); //月利率
        //$month_rate                      = $month_rate / 1000000; //月利率
        //$data["month_interest"]          = intval(($invest_money * $month_rate)*100)/100;
        $data["month_interest"]          = num_format($year_rate / 12 * $invest_money);
        if($end_date){
            //计算两个日期天数
            $gap_days = intval((strtotime($end_date) - $start_date)/3600/24);
            //赎回费用
            $redeem_fee = $invest_money * 0.05;
        }
        if($str_month == 3){//按90天计息（按一年360天计）
            $interest_days = $gap_days ? $gap_days : 90;
            $data["total_interest"] = num_format(($year_rate / 360) * $invest_money * $interest_days);
        }else if($str_month == 6){//按180天计息（按一年360天计）
            $interest_days = $gap_days ? $gap_days : 180;
            $data["total_interest"] = num_format(($year_rate / 360) * $invest_money * $interest_days);
        }else if($str_month == 12){//按365天计息（按一年365天计）
            $interest_days = $gap_days ? $gap_days : 365;
            $data["total_interest"] = num_format(($year_rate / 365) * $invest_money * $interest_days);
        }else{
            $interest_days = $gap_days ? $gap_days : 30;
            $data["total_interest"] = num_format(($year_rate / 360) * $invest_money * $interest_days);
        }
        $data["total_receivables_money"] = $invest_money + $data["total_interest"];
        ////赎回费用
        if($redeem_fee){
            $data["total_receivables_money"] = $data["total_receivables_money"] - $redeem_fee;
        }
        //如果存在赎回金额
        $redeem_price = M("Payables")->where(array("contract_id"=>$pid_contract_id,"is_deleted"=>0,"status"=>2))->getField("sum(price)");
        $data["total_receivables_money"] = $data["total_receivables_money"] - ($redeem_price / 100);
        if($data["total_receivables_money"] < 0){
            $this->ajaxReturn($data, '剩余金额不足以抵扣赎回费用，无法赎回', 0);
        }else{
            $this->ajaxReturn($data, '', 1);
        }
    }

    //合同审批
    public function examine()
    {
        $contract_data['contract_id']    = $_REQUEST['contract_id'];
        $contract_data['examine_status'] = $_REQUEST['examine_status'];
        $contract_data['examine_remark'] = $_REQUEST['examine_remark'];
        $result                          = $this->_contract_mod->contractExamine($contract_data);
        $this->ajaxReturn($result, $result["info"], $result["status"]);
    }

    //弹出窗口 合同审批
    public function contractdialog()
    {
        $contract_id = $_REQUEST["contract_id"];
        $this->assign("contract_id", $contract_id);
        $this->display();
    }

    public function submitcheck()
    {
        $contract_data['contract_id']    = $_REQUEST['contract_id'];
        $contract_data['examine_status'] = $_REQUEST['examine_status'];
        $result                          = $this->_contract_mod->checkStatus($contract_data);
        $this->ajaxReturn($result, '', '');
    }

    public function getdate()
    {
        $product_info = D("product")->where(array("product_id" => $_REQUEST['product_id']))->find();
        $beginTime    = date('Y-m-d', strtotime('+1 day', strtotime($_REQUEST['setdate'])));
        $endTime      = date('Y-m-d', strtotime('+' . $product_info['str_month'] . ' months', strtotime($_REQUEST['setdate'])));
        $this->ajaxReturn(array('beginTime' => $beginTime, 'endTime' => $endTime), '', '');
    }

    public function get_role_department_name(){
        $product_info = D("role_department")->where(array("parent_id" => 1))->select();
        $this->ajaxReturn($product_info);
    }

}
