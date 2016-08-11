<?php

/*
 * 功能：合同业务逻辑类
 * 时间：2015年11月20 11:00
 * author:chushangming
 */

class ContractModel extends Model
{

    protected $tableName = 'contract';
    //自动验证
    protected $_validate = array(
            // array("username","require","用户名必须填写!")
    );
    //自动完成
    protected $_auto     = array(
            // array('reg_time','time',1,'function'), //注册时间
    );

    //获取合同展示列表
    function getContractInfoList()
    {

        //更新最后阅读时间
        $m_user                     = M('user');
        $last_read_time_js          = $m_user->where('role_id = %d', session('role_id'))->getField('last_read_time');
        $last_read_time             = json_decode($last_read_time_js, true);
        $last_read_time['contract'] = time();
        $m_user->where('role_id = %d', session('role_id'))->setField('last_read_time', json_encode($last_read_time));

        $contract  = D('ContractView');
        $below_ids = getSubRoleId(false);
        $all_ids   = getSubRoleId();
        $where     = array();
        //按照指定顺序排序规则
        $where["contract.examine_status"] = array("in",'1,3,4,5,6,0,2');
        $order = "find_in_set(contract.examine_status,'1,3,4,5,6,0,2'),contract.create_time desc";

        if ($_GET['desc_order']) {
            $order = trim($_GET['desc_order']) . ' desc';
        } elseif ($_GET['asc_order']) {
            $order = trim($_GET['asc_order']) . ' asc';
        }
        switch ($_GET['by']) {
            case 'create':
                $where['contract.creator_role_id'] = session('role_id');
                break;
            case 'sub' :
                $where['contract.owner_role_id']   = array('in', implode(',', $below_ids));
                break;
            case 'subcreate' :
                $where['contract.creator_role_id'] = array('in', implode(',', $below_ids));
                break;
            case 'today' :
                $where['contract.due_time']        = array('between', array(strtotime(date('Y-m-d')) - 1, strtotime(date('Y-m-d')) + 86400));
                break;
            case 'week' :
                $week                              = (date('w') == 0) ? 7 : date('w');
                $where['contract.due_time']        = array('between', array(strtotime(date('Y-m-d')) - ($week - 1) * 86400 - 1, strtotime(date('Y-m-d')) + (8 - $week) * 86400));
                break;
            case 'month' :
                $next_year                         = date('Y') + 1;
                $next_month                        = date('m') + 1;
                $month_time                        = date('m') == 12 ? strtotime($next_year . '-01-01') : strtotime(date('Y') . '-' . $next_month . '-01');
                $where['contract.due_time']        = array('between', array(strtotime(date('Y-m-01')) - 1, $month_time));
                break;
            case 'add' :
                $order                             = 'contract.create_time desc';
                break;
            case 'deleted' :
                $where['contract.is_deleted']      = 1;
                break;
            case 'update' :
                $order                             = 'contract.update_time desc';
                break;
            case 'me' :
                $where['contract.owner_role_id']   = session('role_id');
                break;
            default:
                $where['contract.owner_role_id']   = array('in', implode(',', $all_ids));
                break;
        }

        if (!isset($where['contract.is_deleted'])) {
            $where['contract.is_deleted'] = 0;
        }
        if (!isset($where['contract.owner_role_id'])) {
            $where['contract.owner_role_id'] = array('in', implode(',', getSubRoleId()));
        }

        //如果是合同审核员 过滤权限  //总部审核可以看到所有合同
        $position_id   = M("role")->where(array("role_id" => session("role_id")))->getField("position_id");
        $department_id = M("position")->where(array("position_id" => $position_id))->getField("department_id");
        //$department_name = M("role_department")->where(array("department_id"=>session('department_id')))->getField("name");
        $position_name = M("position")->where(array("position_id" => $position_id))->getField("name");
        if ($position_id == 57 || $position_id == 58) {
            unset($where['contract.owner_role_id']);
            $where['contract.examine_status'] = array("egt", 1);
            $order                            = "contract.examine_status asc,contract.create_time desc";
        }
        if ($position_name == '前台') {
            unset($where['contract.owner_role_id']);
            $where['contract.department_id'] = array("eq", $department_id);
        }

        if ($position_name == '会计' || $position_name == "会计经理" || $position_name == '出纳负责人' || $position_name == '出纳') {
            unset($where['contract.owner_role_id']);
            // $where['contract.department_id'] = array("eq",$department_id);
        }

        if ($_REQUEST["field"]) {
            if (trim($_REQUEST['field']) == "all") {
                $field = is_numeric(trim($_REQUEST['search'])) ? 'number|contract.description' : 'number|contract.description';
            } else {
                $field = trim($_REQUEST['field']);
            }
            $search    = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
            $condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);

            if ('create_time' == $field || 'update_time' == $field || 'start_date' == $field || 'end_date' == $field) {
                $search = is_numeric($search) ? $search : strtotime($search);
            }
            switch ($condition) {
                case "is" :
                    if ($field == 'customer_id') {
                        $where['customer.' . $field] = array('eq', $search);
                    } else {
                        $where['contract.' . $field] = array('eq', $search);
                    }break;
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
                default : $where[$field]               = array('eq', $search);
            }
            $params = array('field=' . trim($_REQUEST['field']), 'condition=' . $condition, 'search=' . $_REQUEST["search"]);
        }
        $status = empty($_REQUEST['status']) ? -1 : $_REQUEST['status'] ;
        switch($status){
            case 1:
                $where['contract.examine_status'] = array("eq",0);
                break;
            case 2:
                $where['contract.examine_status'] = array("eq",1);
                break;
            case 3:
                $where['contract.examine_status'] = array("eq",2);
                break;
            case 4:
                $where['contract.examine_status'] = array("eq",3);
                break;
            case 5:
                $where['contract.examine_status'] = array("eq",4);
                break;
            case 6:
                $where['contract.examine_status'] = array("eq",5);
                break;
            case 7:
                $where['contract.examine_status'] = array("eq",6);
                break;
        }
        if ($_GET['listrows']) {
            $contractInfoList['listrows'] = $_GET['listrows'];
            $params[]                     = "listrows=" . trim($_GET['listrows']);
        } else {
            $contractInfoList['listrows'] = 15;
            $params[]                     = "listrows=15";
        }
        $p                        = intval($_GET['p']) ? intval($_GET['p']) : 1;
        //数据集合
        $contractInfoList['list'] = $contract->where($where)->page($p . ',' . $contractInfoList['listrows'])->order($order)->select();

        //数据格式化
        foreach ($contractInfoList['list'] as $key => $val) {
            if ($val["pid_contract_id"]) {
                $contractInfoList['list'][$key]["number_pid"] = M("contract")->where(array("contract_id" => $val["pid_contract_id"]))->getField("number");
            } else {
                $contractInfoList['list'][$key]["number_pid"] = "暂无";
            }
        }
        $count = $contract->where($where)->count();
        import("@.ORG.Page");
        $Page  = new Page($count, $contractInfoList['listrows']);
        if (!empty($_GET['by'])) {
            $params[] = "by=" . trim($_GET['by']);
        }

        if ($_GET['desc_order']) {
            $params[] = "desc_order=" . trim($_GET['desc_order']);
        } elseif ($_GET['asc_order']) {
            $params[] = "asc_order=" . trim($_GET['asc_order']);
        }
        $contractInfoList['params'] = $params;
        foreach ($contractInfoList['list'] as $key => $value) {
            $contractInfoList['list'][$key]['owner']         = getUserByRoleId($value['owner_role_id']);
            $contractInfoList['list'][$key]['creator']       = getUserByRoleId($value['creator_role_id']);
            $contractInfoList['list'][$key]['deletor']       = getUserByRoleId($value['delete_role_id']);
            $contractInfoList['list'][$key]['supplier_name'] = M('supplier')->where('supplier_id = %d', $value['supplier_id'])->getField('name');
            $contacts_id                                     = M('Business')->where('business_id = %d', $value['business_id'])->getField('contacts_id');
            $contractInfoList['list'][$key]['contacts_name'] = M('contacts')->where('contacts_id = %d', $contacts_id)->getField('name');
            $end_date                                        = $contract->where('contract_id = %d', $value['contract_id'])->getField('end_date');
            if ($end_date) {
                $contractInfoList['list'][$key]['days'] = floor(($end_date - time()) / 86400 + 1);
            }
            $contractInfoList['list'][$key]['examine_name'] = $this->getStatusName($value['examine_status']) . $this->getStatusExplain($value['examine_status']);
            // 如果收款成功后的状态说明
            if ($value['examine_status'] == 4) {
                //应收款
                $receivables     = D('ReceivablesView');
                $receivablesinfo = $receivables->where(array('contract_id' => $value['contract_id']))->find();
                if ($receivablesinfo['verify_status'] == 1) {
                    $contractInfoList['list'][$key]['examine_name'] = $this->getStatusName($value['examine_status']) . '<br><span style="color: red;">还款中</span>';
                } else {
                    $contractInfoList['list'][$key]['examine_name'] = $this->getStatusName($value['examine_status']) . '<br><span style="color: blue;">(请会计审核)</span>';
                }
            }
        }
        $Page->parameter          = implode('&', $params);
        $contractInfoList['page'] = $Page->show();
        return $contractInfoList;
    }

    //查看合同详情
    function contractDetail($contract_id)
    {
        $contract = D('ContractView');
        $info     = $contract->where(array('contract_id' => $contract_id))->find();
//        echo $contract->getLastSql();exit;
        if (empty($info)) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('THE_CONTRACT_DOES_NOT_EXIST_OR_HAS_BEEN_DELETED');
            $show_info["url"]    = U('contract/index');
            $info["show_info"]   = $show_info;
            return $info;
        }
        $info['creator_name']  = M('user')->where('role_id = %d', $info['creator_role_id'])->getField('name');
        $info['product']       = M('rContractProduct')->where('contract_id = %d', $contract_id)->select();
        $product_count         = M('rContractProduct')->where('contract_id = %d', $contract_id)->count();
        $info['product_count'] = empty($product_count) ? 0 : $product_count;
        foreach ($info['product'] as $k => $v) {
            $m_product_category                   = M('productCategory');
            $product                              = M('product')->where('product_id = %d', $v['product_id'])->find();
            $info['product'][$k]['info']          = $product;
            $info['product'][$k]['category_name'] = $m_product_category->where('category_id = %d', $product['category_id'])->getField('name');
        }

        $info['receivables'] = D('ReceivablesView')->where('receivables.contract_id = %d and receivables.is_deleted=0', $contract_id)->select();
        foreach ($info['receivables'] as $k => $v) {
            $info['receivables'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
        }

        $receivables_count = D('ReceivablesView')->where('receivables.contract_id = %d and receivables.is_deleted=0', $contract_id)->count();

        //$info['payables'] = D('PayablesView')->where('payables.contract_id = %d and payables.is_deleted=0', $contract_id)->select();
        $info['payables'] = D('PayablesView')->where('payables.contract_id = %d', $contract_id)->order('create_time desc')->select();
        foreach ($info['payables'] as $k => $v) {
            $info['payables'][$k]['owner'] = getUserByRoleId($v['owner_role_id']);
            if($v['redeem_time']){
                $info['payables'][$k]['act_pay_time'] = strtotime("+5 days",$v['redeem_time']);
            } 
        }
        //$payables_count            = count($info['payables']);
        $payables_count            = D('PayablesView')->where('payables.contract_id = %d and payables.is_deleted=0', $contract_id)->count();
        $payables_count            = $payables_count ? $payables_count : 0;
        $info['receivables_count'] = $receivables_count;
        $info['payables_count']    = $payables_count;

        $file_ids     = M('rContractFile')->where('contract_id = %d', $contract_id)->getField('file_id', true);
        $info['file'] = M('file')->where('file_id in (%s)', implode(',', $file_ids))->select();
        $file_count   = 0;
        foreach ($info['file'] as $key => $value) {
            $info['file'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['role_id'])->find();
            $file_count++;
        }
        $info['file_count'] = $file_count;
        foreach ($info['receivables'] as $key => $receivables) {
            $info['receivables'][$key]['price'] = $info['receivables'][$key]['price'] / 100;
        }
        foreach ($info['payables'] as $key => $payables) {
            $info['payables'][$key]['price'] = $info['payables'][$key]['price'] / 100;
        }
        $info['renew_money']  = M("contract")->where(array("pid_contract_id" => $info["contract_id"], "is_deleted" => 0))->getField("sum(investment_money)");
        $info['examine_name'] = $this->getStatusName($info['examine_status']);

        $info['owner'] = getUserByRoleId($info['owner_role_id']);


        return $info;
    }

    //添加合同
    function addContract()
    {
        $contract_custom = M('config')->where('name="contract_custom"')->getField('value');
        if (!$contract_custom) {
            $contract_custom = 'HMT';
        }

        if (!$_POST['number']) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('CONTRACT_NO_EMPTY');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        } else {
            $data['number'] = trim($_POST['number']);
        }
        $data['due_time']    = $_POST['due_time'] ? strtotime($_POST['due_time']) : time();
        $data['business_id'] = 0;
        $data['customer_id'] = $_POST['customer_id'];
        if (empty($data['customer_id'])) {
            $show_info["status"] = "error";
            $show_info["info"]   = "请输入客户名！";
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        //上级合同
        if ($_POST["pid_contract_id"]) {
            $data['pid_contract_id'] = $_POST["pid_contract_id"];
            //该合同下所有的子合同投资金额
            $org_contract_info       = D("Contract")->where(array("contract_id" => $data['pid_contract_id']))->find();
            if ($org_contract_info["renew_status"] == 2) {
                $show_info["info"] = "你已进行过赎回操作，无法续存！";
            }
            //利息付完才能续存 "pay_type"=>1
            $remain_interest = M("payables")->where(array("contract_id" => $data['pid_contract_id'], "pay_type" => 1, "is_deleted" => 0, "status" => array("in", "-1,0,1")))->getField("sum(price)");
            if ($remain_interest) {
                $show_info["info"] = "该合同利息尚未付清，暂不能续存！";
            }
            //判断金额是否足够
            $all_investment_moneys = D("Contract")->where(array("pid_contract_id" => $org_contract_info["contract_id"]))->getField("sum(investment_money)");
            if (($all_investment_moneys + ($_POST['investment_money'] * 100)) > $org_contract_info["investment_money"]) {
                $show_info["info"] = "原始合同余额不足！剩余金额为" . (($org_contract_info["investment_money"] - $all_investment_moneys ) / 100) . "元";
            }
            //如果存在错误 提示
            if ($show_info) {
                $show_info["status"] = "error";
                $show_info["url"]    = $_SERVER['HTTP_REFERER'];
                return $show_info;
            }
        }
        //部门ID
        $position_id                     = D("role")->where(array("role_id" => trim($_POST['owner_role_id'])))->getField("position_id");
        $department_id                   = D("position")->where(array("position_id" => trim($position_id)))->getField("department_id");
        $data['department_id']           = $department_id;
        $data['owner_role_id']           = $_POST['owner_role_id'] ? $_POST['owner_role_id'] : session('role_id');
        //最后操作人
        $data['creator_role_id']         = session('role_id');
        //初始创建者
        $data['original_creater']        = session('role_id');
        //合同经营方式
        //$data['outer_pack']              = trim($_POST['outer_pack']) ? trim($_POST['outer_pack']) : 1; //1 自营 2 外包
        $data['content']                 = trim($_POST['content']);
        $data['description']             = trim($_POST['description']);
        $data['start_date']              = strtotime($_POST['start_date']);
        $data['end_date']                = strtotime($_POST['end_date']);
        $data['create_time']             = time();
        $data['update_time']             = time();
        $data['status']                  = L('HAS_BEEN_CREATED');
        $data['product_id']              = $_POST['product_id'];
        $data['qixi_type']               = $_POST['qixi_type']; //'起息类型 1 代表T+1  2 代表T+2'
        $data['receivables_bank']        = trim($_POST['receivables_bank']);
        $data['receivables_bankzone']    = trim($_POST['receivables_bankzone']);
        $data['receivables_bankcard']    = trim($_POST['receivables_bankcard']);
        $data['receivables_name']        = trim($_POST['receivables_name']);
        $data['receivables_doc_type']    = $_POST['receivables_doc_type'];
        $data['receivables_idno']        = trim($_POST['receivables_idno']);
        $data['investment_money']        = $_POST['investment_money'] * 100;
        $data['month_investment_rate']   = $_POST['month_investment_rate'] * 100;
        $data['active_rate']             = $_POST['active_investment_rate'] * 100;
        $data['investment_rate']         = $_POST['investment_rate'] * 100;
        $data['month_interest']          = $_POST['month_interest'] * 100;
        $data['total_interest']          = $_POST['total_interest'] * 100;
        $data['total_receivables_money'] = $_POST['total_receivables_money'] * 100;
        $data['closure_period']          = $_POST['closure_period'];
        $data['interest_days']           = $_POST['interest_days'];
        //方便统计
        $data['year']                    = date("Y");
        $data['month']                   = date("m");
        $data['day']                     = date("d");

        if ($contractId = $this->add($data)) {
            //保存图片上传路径
            foreach ($_FILES as $ke => $vl) {
                if ($vl['size'][0] <= 0) {
                    unset($_FILES[$ke]);
                }
            }
            $contract_data = array();
            if ($_FILES) {
                $num = 0;
                foreach ($_FILES as $key => $val) {
                    $num += 1;
                    if ($num == 1) {
                        $upload_result = uploading_files($val);
                    }
                    foreach ($upload_result['upload_data'] as $iv) {
                        $contract_data[$iv['key'] . '_pic'] = $iv['savepath'] . $iv['savename'];
                    }
                }
                if ($upload_result['upload_status'] == 1) {
                    $contract_data['contract_id'] = $contractId;
                    $this->save($contract_data);
                } else {
                    //上传失败 则还原 删除目录
                    foreach ($contract_data as $val) {
                        @unlink($val);
                    }
                    //删除合同 添加失败
                    $this->delete($contractId);
                    $show_info["status"] = $upload_result['status'];
                    $show_info["info"]   = $upload_result['info'] . "合同添加失败！";
                    $show_info["url"]    = $upload_result['url'];
                    return $show_info;
                }
            }
            //照片
            if (empty($contract_data)) {
                if ($_POST['id_card_pic']) {
                    $contract_data['id_card_pic'] = $_POST['id_card_pic'];
                }
                if ($_POST['bank_card_pic']) {
                    $contract_data['bank_card_pic'] = $_POST['bank_card_pic'];
                }
                if ($_POST['small_ticket_pic']) {
                    $contract_data['small_ticket_pic'] = $_POST['small_ticket_pic'];
                }
                if ($_POST['other_file_pic']) {
                    $contract_data['other_file_pic'] = $_POST['other_file_pic'];
                }

                if ($contract_data) {
                    $contract_data['contract_id'] = $contractId;
                    //   var_dump($contract_data,$_POST);exit;
                    $this->save($contract_data);
                }
                // var_dump($_POST,$contract_data);exit;
            }


            M('RBusinessContract')->add(array('contract_id' => $contractId, 'business_id' => $data['business_id']));
            if ($_POST['refer_url']) {
                $show_info["status"] = "success";
                $show_info["info"]   = $_POST["pid_contract_id"] ? "合同续存成功，新合同已创建" : L('CREATE_A_CONTRACT_SUCCESSFULLY');
                $show_info["url"]    = $_POST['refer_url'];
            } else {
                $show_info["status"] = "success";
                $show_info["info"]   = $_POST["pid_contract_id"] ? "合同续存成功，新合同已创建" : L('CREATE_A_CONTRACT_SUCCESSFULLY');
                $show_info["url"]    = U('contract/index');
            }
            //合同续存 更新原始合同的付款单
            if ($_POST["pid_contract_id"]) {
                $payables_info = D('Payables')->where(array('contract_id' => $_POST["pid_contract_id"], "pay_type" => array("in", "0,2"), "is_deleted" => 0))->find();
                $new_payables  = array();
                foreach ($payables_info as $key => $val) {
                    if ($key != "payables_id") {
                        if ($key == "price") {
                            $new_payables[$key] = $payables_info['price'] - $data['investment_money'];
                        } else {
                            $new_payables[$key] = $val;
                        }
                    }
                }
                $new_payables['create_time'] = time();
                $new_payables['is_deleted']  = 0;
                //更新付款单
                if ($new_payables["price"] > 0) {
                    $new_id = M("Payables")->add($new_payables);
                }
                M("Payables")->where(array('payables_id' => $payables_info["payables_id"]))->data(array("renew_status" => 1, "status" => 2))->save();

                //更新合同状态
                $update_data['contract_id'] = $_POST["pid_contract_id"];
                if ($payables_info['price'] == $data['investment_money']) {
                    $update_data['examine_status'] = 6; //如果续存金额与原始投资本金付款单相等 则投资完成。
                }
                $update_data['renew_status'] = 1; //0 未进行续存赎回操作 1 已续存 2 已赎回
                $this->save($update_data);
            }
        } else {
            $show_info["status"] = "error";
            $show_info["info"]   = L('FAILED_TO_CREATE_THE_CONTRACT');
            $show_info["url"]    = U('contract/add');
        }
        return $show_info;
    }

    //编辑合同
    function editContract()
    {
        $contract      = D('ContractView');
        $contract_id   = intval($_REQUEST['id']);
        $contract_info = $contract->where('contract.contract_id = %d', $contract_id)->find();
        if (!$contract_info) {
            $show_info["status"] = "error";
            $show_info["info"]   = L('THERE_IS_NO_DATA');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
            return $show_info;
        }
        $data['number'] = $_POST['number'] ? $_POST['number'] : "HMT" . session("user_id") .date('Ymdhis');
        $data['due_time']                = $_POST['due_time'] ? strtotime($_POST['due_time']) : time();
        $data['business_id']             = 0;
        $data['owner_role_id']           = $_POST['owner_role_id'] ? $_POST['owner_role_id'] : session('role_id');
        //最后操作人
        $data['creator_role_id']         = session('role_id');
        $data['content']                 = trim($_POST['content']);
        $data['description']             = trim($_POST['description']);
        $data['start_date']              = strtotime($_POST['start_date']);
        $data['end_date']                = strtotime($_POST['end_date']);
        $data['update_time']             = time();
        $data['status']                  = $_POST['status'];
        $data['qixi_type']               = $_POST['qixi_type']; //'起息类型 1 代表T+1  2 代表T+2'
        //合同经营方式
        //$data['outer_pack']              = trim($_POST['outer_pack']) ? trim($_POST['outer_pack']) : 1; //1 自营 2 外包
        //初始化接收数据
        $data['department_id']           = $_POST['department_id'];
        $data['receivables_bank']        = $_POST['receivables_bank'];
        $data['receivables_bankzone']    = $_POST['receivables_bankzone'];
        $data['receivables_bankcard']    = $_POST['receivables_bankcard'];
        $data['receivables_doc_type']    = $_POST['receivables_doc_type'];
        $data['receivables_name']        = $_POST['receivables_name'];
        $data['receivables_idno']        = $_POST['receivables_idno'];
        $data['investment_money']        = $_POST['investment_money'] * 100;
        $data['month_investment_rate']   = $_POST['month_investment_rate'] * 100;
        $data['active_rate']             = $_POST['active_investment_rate'] * 100;
        $data['investment_rate']         = $_POST['investment_rate'] * 100;
        $data['closure_period']          = $_POST['closure_period'];
        $data['interest_days']           = $_POST['interest_days'];
        $data['month_interest']          = $_POST['month_interest'] * 100;
        $data['total_interest']          = $_POST['total_interest'] * 100;
        $data['total_receivables_money'] = $_POST['total_receivables_money'] * 100;
        if ($this->where(array('contract_id' => $contract_id))->save($data)) {
            $contract_info_old = $this->where(array('contract_id' => $contract_id))->find();
            //保存图片上传路径
            foreach ($_FILES as $ke => $vl) {
                if ($vl['size'][0] <= 0) {
                    unset($_FILES[$ke]);
                }
            }
            if ($_FILES) {
                $num = 0;
                foreach ($_FILES as $key => $val) {
                    $num += 1;
                    if ($num == 1) {
                        $upload_result = uploading_files($val);
                    }
                    foreach ($upload_result['upload_data'] as $iv) {
                        $contract_data[$iv['key'] . '_pic'] = $iv['savepath'] . $iv['savename'];
                    }
                }
                if ($upload_result['upload_status'] == 1) {
                    $contract_data['contract_id'] = $contract_id;
                    $this->save($contract_data);
                    // 清空垃圾数据 删除原有的图片文件
                    unset($contract_data['contract_id']);
                    foreach ($contract_data as $kk => $vv) {
                        @unlink($contract_info_old[$kk]);
                    }
                } else {
                    //上传失败 则还原 删除目录
                    foreach ($contract_data as $val) {
                        @unlink($val);
                    }
                    //删除 添加失败 附件上传失败
                    $show_info["status"] = $upload_result['status'];
                    $show_info["info"]   = $upload_result['info'];
                    $show_info["url"]    = $upload_result['url'];
                    return $show_info;
                }
            }

            M('rBusinessContract')->where(array('contract_id' => $contract_id))->save(array('business_id' => $data['business_id']));
            $show_info["status"] = "success";
            $show_info["info"]   = L('MODIFY_THE_SUCCESS');
            $show_info["url"]    = U('contract/view', 'id=' . $contract_id);
        } else {
            $show_info["status"] = "success";
            $show_info["info"]   = L('THERE_WERE_NO_CHANGES_IN_DATA');
            $show_info["url"]    = $_SERVER['HTTP_REFERER'];
        }
        return $show_info;
    }

    //合同审批
    public function contractExamine($contract_data)
    {

        $contract_info = $this->where(array("contract_id" => $contract_data["contract_id"]))->find();
        if (!$contract_info) {
            $return['status'] = 0;
            $return['info']   = "合同不存在！";
            return $return;
        }
        if ($contract_data['examine_status'] == 3) {
            $remind = "通过";
        } elseif ($contract_data['examine_status'] == 2) {
            $remind = "不通过";
        }
        $res = $this->save($contract_data);
        if ($res) {
            if ($contract_data['examine_status'] == 3) {

                //应收款
                $receivables     = D('ReceivablesView');
                $receivablesinfo = $receivables->where(array('contract_id' => $contract_data['contract_id']))->find();
                if ($receivablesinfo) {
                    //已经收款成功的合同，再次提交后跳过出纳收款
                    if ($receivablesinfo['status'] == 2) {
                        $contract_data['examine_status'] = 4;
                        $res                             = $this->save($contract_data);
                        $return['info']                  = "合同审批" . $remind . "操作成功，已转至会计审核";
                        $return['status']                = 1;
                    } else {
                        $return['info']   = "合同审批" . $remind . "操作成功，已转至出纳处理";
                        $return['status'] = 1;
                    }
                } else {
                    //生成应收款
                    $return = $this->makeReceivable($contract_info, $remind);
                }
            } else {
                $return['status'] = 0;
                $return['info']   = "合同审批" . $remind . ",已退回处理！";
            }
        } else {
            $return['status'] = 0;
            $return['info']   = "合同审批" . $remind . "操作失败！";
        }
        return $return;
    }

    //合同审批完成 生成应收款 及应收款单
    function makeReceivable($contract_info, $remind)
    {
        //准备应收款数据
        $product_info            = M("product")->where(array('product_id' => $contract_info['product_id']))->find();
        $data['name']            = "产品：" . $product_info['name'] . "，合同编号为" . $contract_info['number'] . "的应收款";
        //$data['name'] = $contract_info['number'];
        $data['price']           = $contract_info["investment_money"];
        $data['customer_id']     = $contract_info['customer_id'];
        $data['contract_id']     = $contract_info['contract_id'];
        $data['description']     = $data['name'];
        $data['pay_time']        = time();
        $data['creator_role_id'] = session('role_id');
        $data['owner_role_id']   = $contract_info['owner_role_id'];
        $data['create_time']     = time();
        $data['update_time']     = time();
        $data['status']          = 0;
        $receivables_id          = M('receivables')->add($data);
        if ($receivables_id) {
            $return['info'] = "合同审批" . $remind . "操作成功，生成应收款成功！";
        } else {
            $return['info'] = "合同审批" . $remind . "操作成功，但生成应收款失败！";
        }
        $return['status'] = 1;
        return $return;
    }

    /**
     * 变更订单状态
     * @param type $contract_data
     * @return string
     */
    public function checkStatus($contract_data)
    {
        $contract_info = $this->where(array("contract_id" => $contract_data["contract_id"]))->find();
        if (empty($contract_info)) {
            return '参数异常，请刷新重试！';
        }
        $res = $this->save($contract_data);
        return '操作成功';
    }

    /**
     * 合同状态
     * @param type $status
     * @return string
     */
    public function getStatusName($status)
    {
        $arr = array(
            0 => '未提交审核',
            1 => '审核中',
            2 => '审核失败',
            3 => '审核成功',
            4 => '收款成功',
            5 => '部分付款',
            6 => '全部付款结束'
        );
        return $arr[$status];
    }

    public function getStatusExplain($status)
    {
        $arr = array(
            0 => '', //未提交审核
            1 => '<Br/><span style="color: green;">(请总部审核)<span>', //审核中
            2 => '<Br/><span style="color: magenta;">(请门店跟进)<span>', //审核失败
            3 => '<Br/><span style="color: chocolate;">(请出纳入账)<span>', //审核成功
            4 => '<Br/><span style="color: blue;">(请会计审核)<span>', //收款成功
            5 => '', //部分付款
            6 => '', //全部付款结束
        );
        return $arr[$status];
    }

}
