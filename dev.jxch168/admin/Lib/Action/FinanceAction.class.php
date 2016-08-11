<?php

/**
 * 财务工具
 */
class FinanceAction extends CommonAction
{
    //初始化函数
    public function _initialize()
    {
        parent::_initialize();
    }

    //入金操作列表
    public function index()
    {
        //满标
        $condition['deal_status'] = 2;
        //审核状态
        $condition['verify_status'] = 0;
        $condition['publish_wait'] = 0;
        $type = 'qixi_time';
        $this->commonShow($condition,$type,"index");
    }

    //还款审核列表
    public function verify()
    {
        //还款审核
        $condition['deal_status'] = 4;
        //待审核
        $condition['verify_status'] = 1;
        $condition['publish_wait'] = 0;
        //$map['verify_status'] = array("in","1,2");
        $type = 'jiexi_time';
        $this->commonShow($condition,$type,"verify");
    }

    //网站还款列表
    public function operate(){
        //还款中
        $condition['deal_status'] = 4;
        $condition['publish_wait'] = 0;
        //已审核
        $condition['verify_status'] = 2;
        $type = 'jiexi_time';
        $dMonth = $_REQUEST['dmonth'];
        if (empty($dMonth)) {
            $dMonth = date('Y-m');
        }
        //本月还款情况
        $repay_communication = $GLOBALS["db"]->getAll("select dlr.has_repay,sum(dlr.repay_money) as repay_money from " . DB_PREFIX . "deal_load_repay dlr left join " . DB_PREFIX . "deal d on dlr.deal_id = d.id where DATE_FORMAT(d.jiexi_time, '%Y-%m') = '" . $dMonth . "' GROUP BY dlr.has_repay  ORDER BY dlr.has_repay DESC");
        $this->assign("repay_all_money", ($repay_communication[0]["repay_money"] + $repay_communication[1]["repay_money"]));
        $this->assign("repay_yet_money", $repay_communication[0]["repay_money"]);
        $this->assign("repay_remain_money", $repay_communication[1]["repay_money"]);

        $this->commonShow($condition,$type,"operate");
    }

    //按照日期还款统计
    function repay_static(){
        if (isset ( $_REQUEST ['_order'] )) {
                $order = $_REQUEST ['_order'];
        } else {
                $order = ! empty ( $sortBy ) ? $sortBy : "total_pricipal";
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset ( $_REQUEST ['_sort'] )) {
                $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
                $sort = $asc ? 'asc' : 'desc';

        }
        //查询条件
        $user_name = $_REQUEST["user_name"] ? $_REQUEST["user_name"] : '';
        $real_name = $_REQUEST["real_name"] ? $_REQUEST["real_name"] : '';
        $mobile = $_REQUEST["mobile"] ? $_REQUEST["mobile"] : '';
        $start_time = $_REQUEST["start_time"] ? $_REQUEST["start_time"] : '';
        $end_time = $_REQUEST["end_time"] ? $_REQUEST["end_time"] : '';
        if($user_name){
            $user_id = M("User")->where(array("user_name"=>$user_name,"is_effect"=>1,"is_delete"=>0,"is_auto"=>0))->getField("id");
        }
        if($real_name){
            $user_id = M("User")->where(array("real_name"=>$real_name,"is_effect"=>1,"is_delete"=>0,"is_auto"=>0))->getField("id");
        }
        if($mobile){
            $user_id = M("User")->where(array("mobile"=>$mobile,"is_effect"=>1,"is_delete"=>0,"is_auto"=>0))->getField("id");
        }
        if($user_id){
            $map['dlr.user_id'] = $user_id;
        }
        if($start_time && $end_time){
            $map['d.jiexi_time'] = array(array('egt',$start_time),array('elt',$end_time),'and');
        }else{
            if($start_time){
                $map['d.jiexi_time'] = array('egt',$start_time);
            }
            if($end_time){
                $map['d.jiexi_time'] = array('elt',$end_time);
            }
        }
        $map['dlr.has_repay'] = 0;//未还款数据
        //日期条件
        //$map['is_effect'] = 1;
        //实例化
        $financeModel = D("Finance");
        $return = $financeModel->getRemainRepayList($map,$order,$sort);
        $this->assign("page", $return['page']);
        $sort = $sort == 'desc' ? 1 : 0; //排序方式

        $users = $GLOBALS["db"]->getOne("select count(distinct user_id) from fanwe_deal_load_repay where has_repay = 0");
        $this->assign ('users', $users);
        $this->assign ('sort', $sort);
        $this->assign("list",$return['remain_repay_list']);
        $this->display("repay_static");
    }

    //公共显示方法
    function commonShow($condition,$type,$show_page){
        $dMonth = $_REQUEST['dmonth'];
        if (empty($dMonth)) {
            $dMonth = date('Y-m');
        }
        $map['is_delete'] = 0;
        $map['is_effect'] = 1;
        //条件合并
        $smap = array_merge($map,$condition);
        //实例化
        $financeModel = D("Finance");
        $result = $financeModel->init_deals($smap,$dMonth,$type);

        //计算本月应还本息总额
        $now_month = explode("-",$dMonth);
        foreach($result['new_calendar'] as $key=>$val){
            foreach($val as $k=>$v){
                $old_month = explode("-",$v['week_day']);
                if($old_month[1] == $now_month[1]){
                    $envir['date_all_capital'] += $v['date_all_capital'];
                    $envir['date_all_coupon_cash'] += $v['date_all_coupon_cash'];
                    $envir['date_all_pure_interest'] += $v['date_all_pure_interest'];
                    $envir['date_all_coupon_interest'] += $v['date_all_coupon_interest'];
                    $envir['date_all_active_interest'] += $v['date_all_active_interest'];
                    $envir['date_all_interest'] += $v['date_all_interest'];
                    $envir['date_all_repay_money'] += $v['date_all_repay_money'];
                    $envir['date_all_has_repay_money'] += $v['date_all_has_repay_money'];
                    $envir['date_all_remain_repay_money'] += $v['date_all_remain_repay_money'];
                }
            }
        }
        $this->assign("envir", $envir);
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("week_info", $result['week_info']);
        $this->assign("dMonth", $dMonth);
        $this->assign("Month", $now_month[0]."年".$now_month[1]."月");
        $this->assign("onMonth", D('calendar')->onMonth($dMonth));
        $this->assign("lastMonth", D('calendar')->lastMonth($dMonth));
        $this->assign("today", date("Y-m-d"));
        $this->display($show_page);
    }

    //入金详情列表  显 示某个日期入金审核 详细
    function show_loads(){
       //满标
        $map['deal_status'] = array("in","2,4,5");
        //审核状态
        //$map['verify_status'] = array("in","0,1,2");
        $this->repayCommon($map,"show_loads","qixi_time");
    }

    //还款审核详细列表 显示某个日期入还款审核 详细
    public function show_verify_loads()
    {
        $jiexi_time = trim($_REQUEST['jiexi_time']);
        //还款中
        $map['deal_status'] = array("in", "4,5");
        //还款审核完成
        $map['verify_status'] = array("in", "1,2");
        $this->repayCommon($map,"show_verify_loads","jiexi_time");
    }

    //网站还款详细列表 显示某个日期入还款操作 详细
    public function show_operate_loads()
    {
        //还款中
        $map['deal_status'] = array("in", "4,5");
        //还款审核完成
        $map['verify_status'] = 2;
        $this->repayCommon($map,"show_operate_loads","jiexi_time");
    }
    
    //单个标的网站还款详情
    public function repay_detail(){
        $deal_id = $_REQUEST["deal_id"];
        $map["id"] = $deal_id;
        //实例化
        $financeModel = D("Finance");
        $return = $financeModel->getDealList($map);
        $deal_info = $return["deal_lists"][0];
        
        //富友资金池信息数据
        $cash_data = $financeModel->getAccount();
        //剩余总额
        if($oper_type == "qixi_time"){
            $remain_money = ($cash_data['ca_balance'] + $return['remain_capital']);
            $this->assign("all_loads_money",($return['remain_capital']));
        }else{
            $remain_money = ($cash_data['ca_balance'] - $return['date_all_remain_repay_money']);
            $this->assign("all_loads_money",($return['date_all_remain_repay_money']));
        }
        $this->assign("account_all_money",($remain_money ? $remain_money : 0));
        $this->assign("ca_balance",($cash_data['ca_balance'] ? $cash_data['ca_balance'] : 0));
        //实际还款总额
        $act_repay_money = M("deal_load_repay")->where(array("deal_id"=>$deal_id))->getField("sum(repay_money+impose_money)");
        //罚息
        $act_impose_money = M("deal_load_repay")->where(array("deal_id"=>$deal_id,"has_repay"=>1))->getField("sum(impose_money)");
        //实际还款日期
        $true_repay_date = M("deal_load_repay")->where(array("deal_id"=>$deal_id,"has_repay"=>1))->getField("true_repay_date");
        //管理员信息
        $this->assign("adminInfo", $financeModel->getAdminInfo());
        $this->assign("act_repay_money", $act_repay_money);
        $this->assign("act_impose_money", $act_impose_money);
        $this->assign("true_repay_date", $true_repay_date);
        //借款者账户
        $this->assign("FUYOU_MCHNT_FR",FUYOU_MCHNT_FR);
        $this->assign("deal", $deal_info);
        $this->display();        
    }

    //计算预计还款收益
    function calculate_income(){
            $deal_id = $_REQUEST["deal_id"];//标的ID
            $rate = $_REQUEST["rate"];//利率;
            $qixi_time = $_REQUEST["qixi_time"];//起息时间
            $jiexi_time = $_REQUEST["jiexi_time"];//结息时间
            $act_jiexi_time = $_REQUEST["act_jiexi_time"];//实际结息时间
            $repay_type = $_REQUEST["repay_type"];//还款类型            
            $ca_balance = $_REQUEST["ca_balance"];//账户余额    
            
            if($repay_type == 2){//提前
                if( strtotime($act_jiexi_time) > strtotime($jiexi_time)){
                    die(json_encode(array("status"=>0,"info"=>"提前还款结息日期必须小与正常结息时间")));
                }
            }else if($repay_type == 3){//逾期
                if( strtotime($act_jiexi_time) < strtotime($jiexi_time)){
                    die(json_encode(array("status"=>0,"info"=>"逾期还款结息日期必须大与正常结息时间")));
                }
            }
            //获取该标的下所有还款计划
            $deal_load_repay_list = M("deal_load_repay")->field("repay_money,self_money,coupon_interests,active_interest_money")->where(array("deal_id"=>$deal_id))->select();
            //实际期限
            $qixi_time = date_create($qixi_time);
            $act_jiexi_time = date_create($act_jiexi_time);
            $diff = date_diff($qixi_time,$act_jiexi_time);
            $act_repay_time = $diff->format("%a");
            //预计回款总额
            $total_money = 0;
            //罚息
            $act_impose_money = 0;
            //循环计算利息
            foreach($deal_load_repay_list as $key=>$val){
                $new_repay_money = $val["self_money"] + num_format(($rate / 100 / 360) * $val["self_money"] * intval($act_repay_time)) + $val["coupon_interests"] + $val["active_interest_money"];
                $act_impose_money += $new_repay_money - $val["repay_money"]; 
                $total_money += $new_repay_money;                             
            }
            //预计还款后账户剩余金额
            $remain_money = $ca_balance - $total_money;
            die(json_encode(array("status"=>1,"data"=>number_format($total_money,2),"impose_money"=>number_format($act_impose_money,2),"remain_money"=>number_format($remain_money,2))));
    }
    
    //公共显示某个日期下标的详细数据的方法
    function repayCommon($condition = [],$show_page,$oper_type="qixi_time"){
        $oper_date = trim($_REQUEST[$oper_type]);
        $orderBy ="create_time desc";
        //查询条件
        $map[$oper_type] = $oper_date;
        $map['is_delete'] = 0;
        $map['is_effect'] = 1;
        $map['publish_wait'] = 0;
        if($condition){
            $map = array_merge($map,$condition);
        }
        //输出投标列表 分页参数
        $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
        $page_size = 10;
        //实例化
        $financeModel = D("Finance");
        $limit = $financeModel->getLimit($page,$page_size);
        $return = $financeModel->getDealList($map,$orderBy,$limit);

        //富友资金池信息数据
        $cash_data = $financeModel->getAccount();
        //剩余总额
        if($oper_type == "qixi_time"){
            $remain_money = ($cash_data['ca_balance'] + $return['remain_capital']);
            $this->assign("all_loads_money",($return['remain_capital']));
        }else{
            $remain_money = ($cash_data['ca_balance'] - $return['date_all_remain_repay_money']);
            $this->assign("all_loads_money",($return['date_all_remain_repay_money']));
        }
        $this->assign("account_all_money",($remain_money ? $remain_money : 0));
        $this->assign("ca_balance",($cash_data['ca_balance'] ? $cash_data['ca_balance'] : 0));
        //管理员信息
        $this->assign("adminInfo", $financeModel->getAdminInfo());
        //分页数据
        $rs_count = $return['deal_count'];
        $page_all = ceil($rs_count / $page_size);
        $this->assign("page_all", $page_all);
        $this->assign("rs_count", $rs_count);
        $this->assign("page", $page);
        $this->assign("page_prev", $page - 1);
        $this->assign("page_next", $page + 1);
        $this->assign("result",$return);
        $this->assign($oper_type,$oper_date);
        //前一天
        $this->assign("pre_".$oper_type,date("Y-m-d",strtotime("-1 day",strtotime($oper_date))));
        //后一天
        $this->assign("next_".$oper_type,date("Y-m-d",strtotime("+1 day",strtotime($oper_date))));
        //借款者账户
        $this->assign("FUYOU_MCHNT_FR",FUYOU_MCHNT_FR);
        $this->display($show_page);
    }

    //显示某个标的投标记录
    public function get_deal_loads()
    {
        $deal_id = intval($_REQUEST['deal_id']);
        $l_key = intval($_REQUEST['l_key']);
        //document 对象
        $obj = strim($_REQUEST['obj']);
        $type = $_REQUEST['type'];

        if ($deal_id == 0) {
            $this->error("数据错误");
        }
        //输出投标列表 分页参数
        $page = intval($_REQUEST['p']) ? intval($_REQUEST['p']) : 1;
        $page_size = 10;
        //实例化
        $financeModel = D("Finance");
        $limit = $financeModel->getLimit($page, $page_size);
        $result = $financeModel->get_deal_load_list($deal_id, '', $limit);

        //分页数据
        $rs_count = $result['load_count'];
        $page_all = ceil($rs_count / $page_size);
        $this->assign("load_user", $result['load_list']);
        $this->assign("l_key", $l_key);
        $this->assign("page_all", $page_all);
        $this->assign("rs_count", $rs_count);
        $this->assign("page", $page);
        $this->assign("deal_id", $deal_id);
        $this->assign("obj", $obj);
        $this->assign("page_prev", $page - 1);
        $this->assign("page_next", $page + 1);
        $html = $this->fetch();
        $this->success($html);
    }

    //导出所有export_all_load
    function export_all_load()
    {
        $data['deal_time'] = $_REQUEST['deal_time'];
        $data['id'] = $_REQUEST['deal_id'];
        $data['type'] = $_REQUEST['type'];
        //导出入金表
        if ($data) {
            D("Finance")->do_export_load($data);
        } else {
            die();
        }
    }

    //满标放款
    public function do_loans()
    {
        set_time_limit(0);
        $id = intval($_REQUEST['id']);
        $oper_pwd = md5(trim($_REQUEST['oper_password']));
        $loans_pic = $_REQUEST["loans_pic"];
        //实例化
        $financeModel = D("Finance");
        //验证管理员操作密码是否正确
        if (!$financeModel->checkAdminPwd($oper_pwd, 'oper_password')) {
            $result['status'] = 9;
            $result['info'] = "入金操作密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 9);
        }
        //放款时间
        $repay_start_time = date('Y-m-d');
        $result = $financeModel->doLoans($id, $repay_start_time, 0, $loans_pic);

        //检测总账
        $financeModel->checkJxchAccount($_REQUEST["ca_balance"], $result['admin_log']["money"], $id, "do_loans");
        //管理员信息
        $adminInfo = $financeModel->getAdminInfo();
        //管理员日志
        $result['admin_log']['admin_name'] = $adminInfo['adm_name'];
        $result['admin_log']['admin_id'] = $adminInfo['id'];
        $result['admin_log']['module_name'] = MODULE_NAME;
        $result['admin_log']['action_name'] = ACTION_NAME;
        $admin_log_id = M("admin_log")->add($result['admin_log']);

        //放款状态
        if ($result['status'] == 2) {
            ajax_return($result);
        } elseif ($result['status'] == 1) {
            $this->success($result['info']);
        } else {
            $this->error($result['info']);
        }
    }

    //还款审核
    public function verify_deal()
    {
        $id = intval($_REQUEST['id']);
        $verify_pwd = md5(trim($_REQUEST['verify_pwd']));
        //实例化
        $financeModel = D("Finance");
        //验证管理员操作密码是否正确
        if (!$financeModel->checkAdminPwd($verify_pwd, 'verify_password')) {
            $result['status'] = 0;
            $result['info'] = "还款审核密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
        }
        //更新标的审核状态
        $data['id'] = $id;
        $data['verify_status'] = 2;
        if ($financeModel->updateModel('deal', $data)) {
            $result['status'] = 1;
            $result['info'] = "还款审核成功！";
        } else {
            $result['status'] = 0;
            $result['info'] = "还款审核失败！";
        }
        //管理员信息
        $adminInfo = $financeModel->getAdminInfo();
        $this->assign("adminInfo", $adminInfo);
        //管理员日志
        $admin_log['admin_name'] = $adminInfo['adm_name'];
        $result['admin_log']['admin_id'] = $adminInfo['id'];
        $admin_log['module_name'] = MODULE_NAME;
        $admin_log['action_name'] = ACTION_NAME;
        $admin_log['deal_id'] = $id;
        $admin_log['operate_type'] = 2; //2代表还款审核
        $admin_log['operate_desc'] = "网站还款审核";
        $load_ids = M("deal_load")->field("id")->where(array("deal_id" => $id, "is_auto" => 0))->findAll();
        foreach ($load_ids as $key => $val) {
            $str_lid[] = $val['id'];
        }
        $admin_log['load_repay_id'] = $str_lid ? implode(",", $str_lid) : '';
        $admin_log_money = M("deal_load_repay")->field("sum(repay_money) as repay_moneys")->where(array("deal_id" => $id, "has_repay" => 0))->find();
        $admin_log['money'] = $admin_log_money["repay_moneys"];
        $admin_log['status'] = 1;
        $admin_log['operate_time'] = time();
        $admin_log['operate_date'] = date("Y-m-d");
        $admin_log['operate_ip'] = get_client_ip();
        $admin_log['remark'] = $result['info'];
        $admin_log_id = M("admin_log")->add($admin_log); //添加日历

        $this->ajaxReturn($result, $result['info'], $result['status']);
    }

    //还款操作 代还款
    function do_site_repay()
    {
        set_time_limit(0);
        $id = intval($_REQUEST['id']);//投资记录id
        $repay_type = $_REQUEST['repay_type'];//网站还款类型 1准时 2提前 3逾期
        $act_jiexi_time = $_REQUEST['act_jiexi_time'];//实际结息日期
        
        $return_pwd = md5(trim($_REQUEST['return_pwd']));
        //实例化
        $financeModel = D("Finance");
        //验证管理员操作密码是否正确
        if (!$financeModel->checkAdminPwd($return_pwd, 'return_password')) {
            $result['status'] = 0;
            $result['info'] = "还款操作密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
        }
        //操作类型
        $number_type = trim($_REQUEST['number_type']);
        $colum_name = $number_type == "all" ? "deal_id" :"load_id";
         //验证资金池账户余额
        if (!$financeModel->checkFuyouBalance($colum_name,$id)) {
            $result['status'] = 0;
            $result['info'] = "富友资金池账户资金不足，暂时无法还款，请及时充值！";
            $this->ajaxReturn($result, $result['info'], 0);
        }
        if($number_type == "all"){//单个标的放款
            //打开文件 执行文件锁 进行排它型锁定
            $file_dir = APP_ROOT_PATH . "log/do_site_repay.lock";
            $fp = fopen($file_dir, "w+");
            if (flock($fp, LOCK_EX)) {
                $result = $financeModel->do_site_repay($id, $l_key,$repay_type,$act_jiexi_time);
                flock($fp, LOCK_UN); // 释放锁定
                //关闭文件流
                fclose($fp);
            } else {
                fclose($fp);
                $result['info'] = "网站代还款进程已被占用，请稍后再试...";
                $this->ajaxReturn($result, $result['info'], 1);
            }
        }else if( $number_type== "sigle"){//单个投资记录放款
            //打开文件 执行文件锁 进行排它型锁定
            $file_dir = APP_ROOT_PATH . "log/do_site_repay.lock";
            $fp = fopen($file_dir, "w+");
            if (flock($fp, LOCK_EX)) {
                $result = $financeModel->do_load_repay($id);
                flock($fp, LOCK_UN); // 释放锁定
                //关闭文件流
                fclose($fp);
            } else {
                fclose($fp);
                $result['info'] = "网站代还款进程已被占用，请稍后再试...";
                $this->ajaxReturn($result, $result['info'], 1);
            }
        }

        //检测总账
        $financeModel->checkJxchAccount($_REQUEST["ca_balance"], $result['admin_log']["money"], $id, "repay");

        ///管理员信息
        $adminInfo = $financeModel->getAdminInfo();
        $this->assign("adminInfo", $adminInfo);
        //管理员日志
        $result['admin_log']['admin_name'] = $adminInfo['adm_name'];
        $result['admin_log']['admin_id'] = $adminInfo['id'];
        $result['admin_log']['module_name'] = MODULE_NAME;
        $result['admin_log']['action_name'] = ACTION_NAME;
        $admin_log_id = M("admin_log")->add($result['admin_log']);

        if ($result['status'] == 1) {
            $this->ajaxReturn($result, $result['info'], 1);
        } else {
            $this->ajaxReturn($result, $result['info'], 0);
        }
    }

    //保存管理员操作密码
    public function saveOperPwd()
    {
        $type = strim($_REQUEST['type']);
        $oper_name = '';
        //设置还款审核密码
        if ($type == "verify") {
            $verify_password = strim($_REQUEST['verify_password']);
            $confirm_verify_password = strim($_REQUEST['confirm_verify_password']);
            $oper_name = '还款审核';
            if ($verify_password != $confirm_verify_password) {
                $result['status'] = 0;
                $result['info'] = "两次输入的密码不一致，请重新输入！";
            }
            $data['verify_password'] = md5(trim($verify_password));
        } else if ($type == "return") {
            $return_password = strim($_REQUEST['return_password']);
            $confirm_return_password = strim($_REQUEST['confirm_return_password']);
            $oper_name = '还款操作';
            if ($return_password != $confirm_return_password) {
                $result['status'] = 0;
                $result['info'] = "两次输入的密码不一致，请重新输入！";
            }
            $data['return_password'] = md5(trim($return_password));
        } else if ($type == "income") {
            $oper_password = strim($_REQUEST['oper_password']);
            $confirm_oper_password = strim($_REQUEST['confirm_oper_password']);
            $oper_name = '入金操作';
            if ($oper_password != $confirm_oper_password) {
                $result['status'] = 0;
                $result['info'] = "两次输入的密码不一致，请重新输入！";
            }
            $data['oper_password'] = md5(trim($oper_password));
        } else {
            $result['status'] = 0;
            $result['info'] = "没有该密码类型！";
        }

        //错误提示信息
        if ($result) {
            if ($_REQUEST['ajax_off']) {
                $this->error($result['info']);
            } else {
                $this->ajaxReturn($result, $result['info'], 0);
            }
        }
        $res = D("Finance")->saveAdmin($data);

        if ($res) {
            $result['status'] = 1;
            $result['info'] = $oper_name . "密码设置成功！";
            if ($_REQUEST['ajax_off']) {
                $this->success($result['info']);
            } else {
                $this->ajaxReturn($result, $result['info'], 1);
            }
        } else {
            $result['status'] = 0;
            $result['info'] = $oper_name . "密码设置失败！";
            if ($_REQUEST['ajax_off']) {
                $this->error($result['info']);
            } else {
                $this->ajaxReturn($result, $result['info'], 0);
            }
        }
    }

    //检测原始密码
    public function checkOldPwd()
    {
        $type = strim($_REQUEST['type']);
        $info = strim($_REQUEST['info']);
        $old_password = md5(strim($_REQUEST['old_password']));
        //实例化
        $financeModel = D("Finance");
        //验证管理员操作密码是否正确
        if (!$financeModel->checkAdminPwd($old_password, $type . '_password')) {
            $result['status'] = 0;
            $result['info'] = $info . "原始密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
            die();
        }
    }

    //付款流水号信息
    public function flowingwater()
    {
        $whereSql = '';
        $dealName = trim($_REQUEST['deal_name']);
        $ids = array();
        if ($dealName) {
            $sql = "select id from " . DB_PREFIX . "deal where name like '%" . $dealName . "%'";
            $dealIdTmp = $GLOBALS['db']->getAll($sql);
            if ($dealIdTmp) {
                foreach ($dealIdTmp as $key => $val) {
                    $ids[] = $val['id'];
                }
            }
        }

        if ($_REQUEST['has_repay']) {
            if ($_REQUEST['has_repay'] == 2) {
                $has_repay = 0;
            } else if ($_REQUEST['has_repay'] == 1) {
                $has_repay = 1;
            }
            $whereSql .= " a.has_repay = '" . $has_repay . "' ";
        }

        if ($ids) {
            $whereSql .= "AND a.deal_id  in (" . implode(",", $ids) . ")";
        }
        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : trim($_REQUEST['begin_time']);
        $end_time = trim($_REQUEST['end_time']) == '' ? 0 : trim($_REQUEST['end_time']);
        if ($begin_time) {
            $begin_time = strtotime($begin_time);
            $whereSql .= "AND d.create_time >= '$begin_time'  ";
        }
        if ($end_time) {
            $end_time = strtotime($end_time);
            $whereSql .= "AND d.create_time <= '$end_time'  ";
        }
        if ($whereSql) {
            $whereSql = " WHERE " . trim($whereSql, 'AND') . '';
        }
        $conditon = " from " . DB_PREFIX . "deal_load_repay a left join " . DB_PREFIX . "deal b on a.deal_id = b.id left join " . DB_PREFIX . "user c on a.user_id = c.id  left join fanwe_deal_load d on a.load_id = d.id $whereSql ";

        if ($_GET['xls'] == 'true') {
            $sql = "select a.id,a.has_repay,a.user_id,b.id as deal_id , d.coupon_cash,b.cate_id,b.rate,(a.self_money - d.coupon_cash) as real_self_money,a.pure_interests as  interest_moneys, (a.pure_interests + a.active_interest_money + a.coupon_interests)  as total_interest_money,a.active_interest_money,a.coupon_interests,(a.self_money + a.pure_interests + a.active_interest_money + a.coupon_interests) as repay_moneys,date_sub(b.jiexi_time,interval -1 day) as rdate,b.name,b.qixi_time,b.jiexi_time,d.create_time,d.coupon_cash " . $conditon . " order by d.id desc,b.id desc ";
            $list = $GLOBALS['db']->getAll($sql);
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num = 1;
            $self_money = 0;
            $interest_money = 0;
            $active_interest_money = 0;
            $repay_money = 0;
            foreach ($list as $Tkey => $Tval) {
                $self_money += $Tval['self_money'];
                $interest_money += $Tval['interest_money'];
                $active_interest_money += $Tval['active_interest_money'];
                $repay_money += $Tval['repay_money'];

                if ($num == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '编号 ')
                        ->setCellValue('B' . $num, "会员 ")
                        ->setCellValue('C' . $num, "姓名 ")
                        ->setCellValue('D' . $num, "标的名  ")
                        ->setCellValue('E' . $num, "投标类型")
                        ->setCellValue('F' . $num, "利率(%)")
                        ->setCellValue('G' . $num, "投资本金")
                        ->setCellValue('H' . $num, "抵现券")
                        ->setCellValue('I' . $num, "利息")
                        ->setCellValue('J' . $num, "收益券收益")
                        ->setCellValue('K' . $num, "活动收益")
                        ->setCellValue('L' . $num, "应还总收益")
                        ->setCellValue('M' . $num, "还款金额 ")
                        ->setCellValue('N' . $num, "投标时间 ")
                        ->setCellValue('O' . $num, "起息日 ")
                        ->setCellValue('P' . $num, "结息日 ");
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $Tval['id'])
                    ->setCellValue('B' . $num, get_deal_user($Tval['user_id']))
                    ->setCellValue('C' . $num, getUserRealName($Tval['user_id']))
                    ->setCellValue('D' . $num, $Tval['name'])
                    ->setCellValue('E' . $num, get_deal_cate_name($Tval['cate_id']))
                    ->setCellValue('F' . $num, $Tval['rate'])
                    ->setCellValue('G' . $num, $Tval['real_self_money'])
                    ->setCellValue('H' . $num, $Tval['coupon_cash'])
                    ->setCellValue('I' . $num, $Tval['interest_moneys'])
                    ->setCellValue('J' . $num, $Tval['coupon_interests'])
                    ->setCellValue('K' . $num, $Tval['active_interest_money'])
                    ->setCellValue('L' . $num, $Tval['total_interest_money'])
                    ->setCellValue('M' . $num, $Tval['repay_moneys'])
                    ->setCellValue('N' . $num, to_date($Tval['create_time']))
                    ->setCellValue('O' . $num, $Tval['qixi_time'])
                    ->setCellValue('P' . $num, $Tval['jiexi_time']);
                $num++;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, '总计')
                ->setCellValue('B' . $num, ' ')
                ->setCellValue('C' . $num, ' ')
                ->setCellValue('D' . $num, ' ')
                ->setCellValue('E' . $num, ' ')
                ->setCellValue('F' . $num, ' ')
                ->setCellValue('G' . $num, $self_money)
                ->setCellValue('H' . $num, $interest_money)
                ->setCellValue('I' . $num, $active_interest_money)
                ->setCellValue('J' . $num, $repay_money)
                ->setCellValue('K' . $num, ' ')
                ->setCellValue('L' . $num, ' ')
                ->setCellValue('M' . $num, ' ')
                ->setCellValue('N' . $num, ' ')
                ->setCellValue('O' . $num, ' ')
                ->setCellValue('P' . $num, ' ');
            $filename = $begin_time . '~' . $end_time . "付款流水";
            php_export_excel($objPHPExcel, $filename);
            exit;
        }
        $count = $GLOBALS['db']->getOne("SELECT count(*) " . $conditon);
        if (!empty($_REQUEST ['listRows'])) {
            $listRows = $_REQUEST ['listRows'];
        } else {
            $listRows = '';
        }

        $p = new Page($count, $listRows);
        if ($count > 0) {
            $sql = "select a.id,a.has_repay,a.user_id,b.id as deal_id  ,b.cate_id,b.rate,(a.self_money - d.coupon_cash) as real_self_money,a.pure_interests as  interest_moneys,(a.pure_interests + a.active_interest_money + a.coupon_interests)  as total_interest_money,a.active_interest_money,a.coupon_interests,(a.self_money + a.pure_interests + a.active_interest_money + a.coupon_interests) as repay_moneys,date_sub(b.jiexi_time,interval -1 day) as rdate,b.name,b.jiexi_time,b.qixi_time,d.create_time,d.coupon_cash " . $conditon . " order by d.id desc,b.id desc limit  " . $p->firstRow . ',' . $p->listRows;

            $list = $GLOBALS['db']->getAll($sql);
            $page = (int)$_GET['p'] < 1 ? 1 : (int)$_GET['p'];
            if ($p->totalPages == $page) {
                $sql = "select '总计' as id,'' as user_id ,'' as deal_id , '' as cate_id ,'' as rate ,'' as rdate, '' as name, '' as jiexi_time, '' as create_time,sum(a.self_money - d.coupon_cash) as real_self_money,sum(a.pure_interests) as  interest_moneys,sum(a.pure_interests + a.active_interest_money + a.coupon_interests)  as total_interest_money,sum(a.active_interest_money) as active_interest_money,sum(a.coupon_interests) as coupon_interests,sum(a.self_money + a.pure_interests + a.active_interest_money + a.coupon_interests) as repay_moneys,sum(d.coupon_cash) as coupon_cash,'2' as has_repay" . $conditon . " order by jiexi_time desc,b.id desc   ";
                $totalInfo = $GLOBALS['db']->getRow($sql);
                $list[] = $totalInfo;
            }
            $this->assign("list", $list);
        }

        $page = $p->show();
        $this->assign("page", $page);
        $this->display();
    }

    public function admin_invite()
    {
        $admin_name = isset($_REQUEST['admin_id']) ? trim($_REQUEST['admin_id']) : '';
        $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $rate = isset($_REQUEST['rate']) ? trim($_REQUEST['rate']) : 0.005;
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        // 将admin_name转换成admin_id
        $where = array(); // 搜索user表用的条件
        $condition = ""; //搜索deal_load表用的条件
        $arr = array();
        $condition .= "dl.is_auto=0 ";
        $condition .= "and dl.contract_no != ' ' ";
        $arr['is_auto'] = array("eq", "0");
        $arr['contract_no'] = array("neq", '');
        // 此处
        if ($admin_name) {
            $admin_id_info = M("admin")->field('id')->where("adm_name like '%" . $admin_name . "%'")->select();
            foreach ($admin_id_info as $val) {
                $admin_id_arr[] = $val['id'];
            }
            if ($admin_id_arr) {
                $admin_id = implode(",", $admin_id_arr); //  拼接成需要的id
                $where['admin_id'] = array('in', $admin_id);
                $user_id_info = M("user")->field('id')->where($where)->select();
                foreach ($user_id_info as $val) {
                    $user_id_arr[] = $val['id'];
                }
                if ($user_id_arr) {
                    $user_id = implode(",", $user_id_arr); //  拼接成需要的id
                    $arr['user_id'] = array('in', $user_id);
                    $condition .= "and dl.user_id in (".$user_id.") ";
                }
            }
        }
        if ($user_name) {
            $arr['user_name'] = array('like', '%' . $user_name . '%');
            $condition .= "and dl.user_name like '%".$user_name."%' ";
        }
        if ($begin_time && $end_time=='') {
            $begin_time = strtotime($begin_time);
            $arr['create_time'] = array('gt', $begin_time);
            $condition .= "and dl.create_time >=".$begin_time." ";
        }
        if ($end_time && $begin_time=='') {
            $end_time = strtotime($end_time."23:59:59");;
            $arr['create_time'] = array('lt', $end_time);
            $condition .= "and dl.create_time <=".$end_time." ";
        }
        if($begin_time && $end_time){
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time."23:59:59");
            $condition.="and dl.create_time >=".$begin_time." and dl.create_time <=".$end_time." ";
            $arr['create_time'] = array(array('gt', $begin_time),array('lt', $end_time));
        }
        // 在deal_load表中搜索出相关数据
        if ($arr['user_id'] || $arr['user_name']) {
            $count = M("deal_load")->where($arr)->order("create_time desc")->count();
            import("ORG.Util.Page");
            $Page = new Page($count, 18);
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$condition."
                    order by dl.create_time desc
                    limit {$Page->firstRow},{$Page->listRows}
                    ";
            $deal_info = D()->query($sql);
        foreach ($deal_info as $key => $val) {
            $deal_reapy = M("deal_load_repay")->field("has_repay")->where("id=" . $val['deal_id'])->find();
            $deal_info[$key]['real_self_money'] = $val['money'] - $val['coupon_cash'];
            $deal_info[$key]['has_repay'] = $deal_reapy['has_repay'];
        }
            $sql = "select sum(truncate((d.repay_time*dl.money*{$rate}/360),2))as sum_commission from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$condition;
            $res = D()->query($sql);
            $sum_commission = $res['0']['sum_commission'];
            $Page->parameter = 'search=key&name=thinkphp';
            // 设置分页显示
            $Page->setConfig('header', '条数据');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $page = $Page->show();
            $this->assign("page", $page);
            $this->assign("sum_commission", $sum_commission);
            $this->assign("list", $deal_info);
        }

        $this->display();
    }

    public function export_csv_admin_invite_total()
    {
        $admin_name = isset($_REQUEST['admin_id']) ? trim($_REQUEST['admin_id']) : '';
        $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $rate = isset($_REQUEST['rate']) ? trim($_REQUEST['rate']) : 0.005;
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        // 将admin_name转换成admin_id
        $where = array(); // 搜索user表用的条件
        $condition = ""; //搜索deal_load表用的条件
        $arr = array();
        $condition .= "dl.is_auto=0 ";
        $condition .= "and dl.contract_no != ' ' ";
        $arr['is_auto'] = array("eq", "0");
        $arr['contract_no'] = array("neq", '');
        if ($admin_name) {
            $admin_id_info = M("admin")->field('id')->where("adm_name like '%" . $admin_name . "%'")->select();
            foreach ($admin_id_info as $val) {
                $admin_id_arr[] = $val['id'];
            }
            if ($admin_id_arr) {
                $admin_id = implode(",", $admin_id_arr); //  拼接成需要的id
                $where['admin_id'] = array('in', $admin_id);
                $user_id_info = M("user")->field('id')->where($where)->select();
                foreach ($user_id_info as $val) {
                    $user_id_arr[] = $val['id'];
                }
                if ($user_id_arr) {
                    $user_id = implode(",", $user_id_arr); //  拼接成需要的id
                    $arr['user_id'] = array('in', $user_id);
                    $condition .= "and dl.user_id in (".$user_id.") ";
                }
            }
        }
        if ($user_name) {
            $arr['user_name'] = array('like', '%' . $user_name . '%');
            $condition .= "and dl.user_name like '%".$user_name."%' ";
        }
        if ($begin_time && $end_time=='') {
            $begin_time = strtotime($begin_time);
            $arr['create_time'] = array('gt', $begin_time);
            $condition .= "and dl.create_time >=".$begin_time." ";
        }
        if ($end_time && $begin_time=='') {
            $end_time = strtotime($end_time."23:59:59");;
            $arr['create_time'] = array('lt', $end_time);
            $condition .= "and dl.create_time <=".$end_time." ";
        }
        if($begin_time && $end_time){
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time."23:59:59");
            $condition.="and dl.create_time >=".$begin_time." and dl.create_time <=".$end_time." ";
            $arr['create_time'] = array(array('gt', $begin_time),array('lt', $end_time));
        }
        // 在deal_load表中搜索出相关数据
        if ($arr['user_id'] || $arr['user_name']) {
            $count = M("deal_load")->where($arr)->order("create_time desc")->count();
            import("ORG.Util.Page");
            $Page = new Page($count, 18);
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from " . DB_PREFIX . "deal_load as dl
                    left join " . DB_PREFIX . "deal as d on dl.deal_id = d.id
                    where " . $condition . "
                    order by dl.create_time desc
                    ";
            $deal_info = D()->query($sql);
            foreach ($deal_info as $key => $val) {
                $deal_reapy = M("deal_load_repay")->field("has_repay")->where("id=" . $val['deal_id'])->find();
                $deal_info[$key]['user_name'] = get_user_name_reals($val['user_id']); //会员名
                $deal_info[$key]['cate_id'] = get_deal_cate_name($val['cate_id']); // 获取标的类别
                $deal_info[$key]['create_date'] = to_date($val['create_time']); // 获取标的类别
                $deal_info[$key]['real_self_money'] = $val['money'] - $val['coupon_cash'];
                $deal_info[$key]['has_repay'] = $deal_reapy['has_repay']==0 ? "未还款" : "已还款" ;
            }
        }
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $num = 1;
        foreach ($deal_info as $key => $val) {

            if ($num == 1) {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, '编号')
                    ->setCellValue('B' . $num, '会员')
                    ->setCellValue('C' . $num, "标的名")
                    ->setCellValue('D' . $num, "投标类型")
                    ->setCellValue('E' . $num, "利率")
                    ->setCellValue('F' . $num, "投资本金(%)")
                    ->setCellValue('G' . $num, "期限(元)")
                    ->setCellValue('H' . $num, "提成金额")
                    ->setCellValue('I' . $num, "投标时间")
                    ->setCellValue('J' . $num, "起息日")
                    ->setCellValue('K' . $num, "结息日")
                    ->setCellValue('L' . $num, "状态");
                $num = 2;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $val['id'])
                ->setCellValue('B' . $num, $val['user_name'])
                ->setCellValue('C' . $num, $val['name'])
                ->setCellValue('D' . $num, $val['cate_id'])
                ->setCellValue('E' . $num, $val['rate'])
                ->setCellValue('F' . $num, $val['real_self_money'])
                ->setCellValue('G' . $num, $val['repay_time'])
                ->setCellValue('H' . $num, $val['commission'])
                ->setCellValue('I' . $num, $val['create_date'])
                ->setCellValue('J' . $num, $val['qixi_time'])
                ->setCellValue('K' . $num, $val['jiexi_time'])
                ->setCellValue('L' . $num, $val['has_repay']);


            $num++;
        }
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = $admin_name."邀请投资的提成表";
        php_export_excel($objPHPExcel, $filename);
    }

    public function deal_load_static() {
        //获取当前的时间
        $time = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (30 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $_REQUEST['start_time'] = $start_time;
        $_REQUEST['end_time'] = $end_time;


        $sql = "select d.repay_time,sum(dl.money) as total_money from " . DB_PREFIX . "deal_load dl LEFT JOIN  " . DB_PREFIX . "deal d on dl.deal_id = d.id where d.is_effect = 1 AND dl.is_auto = 0 AND dl.contract_no != '' ";
        if ($start_time) {
            $sql .= " AND d.create_time >= " . strtotime($start_time);
        }
        if ($end_time) {
            $sql .= " AND d.create_time <= " . strtotime($end_time);
        }
        $sql .= " GROUP BY d.repay_time";
        $list = $GLOBALS['db']->getAll($sql);
        $count_num = "";
        foreach ($list as $key => $value) {
            $count_num += $value['total_money'];
        }
        $data_array = array();
        foreach ($list as $key => $value) {
            $data_name_raw[] = $value['repay_time'] . "天" . "(" . number_format(num_format($value['total_money']), 2) . "元" . ")";
            $pie_data_array[] = round($value['total_money'] / $count_num, 4);
            $data_array[] = array($value['repay_time'] . "天", floatval($value['total_money']));
        }

        $this->assign('new_data_array', json_encode($data_array));
        $this->assign('data_name', json_encode($data_name_raw));
        $this->assign('series_name', json_encode("百分比"));
        $this->assign('pie_data_array', json_encode($pie_data_array));
        $this->assign("list", $list);
        $this->assign('type', 'pie');
        $this->display();
    }

    public function repay_list() {
        //获取当前的时间
        $time = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : $time;
        //从url中获取结束时间
        $end_time = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : to_date(time() + (7 * 3600 * 24), "Y-m-d");
        //$sql = "select deal_id,FROM_UNIXTIME(repay_time,'%Y-%m-%d') as repay_time, sum(repay_money) as total_repay_money from " . DB_PREFIX . "deal_load_repay where has_repay =0 group by FROM_UNIXTIME(repay_time,'%Y%m%d')";
        $sql = "SELECT d.jiexi_time,sum(dlr.repay_money) as total_repay_money FROM " . DB_PREFIX . "deal_load_repay dlr LEFT JOIN " . DB_PREFIX . "deal d ON dlr.deal_id = d.id WHERE d.jiexi_time >= '" . $start_time . "' AND d.jiexi_time <= '" . $end_time . "' AND dlr.has_repay = 0 GROUP BY d.jiexi_time ORDER BY d.jiexi_time";
        $repay_list = $GLOBALS["db"]->getAll($sql);
        if ($repay_list) {
            foreach ($repay_list as $val) {

                $repay_list['gross']['total_repay_money']+=$val['total_repay_money'];
                $data_array[] = array($val['jiexi_time'], floatval($val['total_repay_money']));
            }
        }
        $repay_list['gross']['jiexi_time'] = "总计";
        $this->assign("data_array", json_encode($data_array));
        $this->assign("repay_list", $repay_list);
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
        return;
    }

    public function repay_total() {
        //获取当前的时间
        $time = to_date(time(), "Y-m-d");
        //从url中获取开始时间，默认为一个月前的时间
        $start_time = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"] : to_date(time() - (7 * 3600 * 24), "Y-m-d");
        //从url中获取结束时间
        $end_time = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"] : $time;
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
        $end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型

        if ($start_time == $end_time) {
            $date_lists = M("statistical_repay")->order("repay_date asc")->where("repay_date>=$start_time_int and repay_date<=$end_time_int")->field("repay_date")->group("repay_date")->findAll();
            for ($i = 1; $i <= 2; $i++) {
                $statistics_data[] = M("statistical_repay")->order("repay_date asc")->where("repay_date = $start_time_int and repay_type=$i")->find();
            }
            $list = array();
            //将数组中的充值数据转换成百分比的形式，这样转换是为了饼图的显示
            if (!empty($statistics_data)) {
                $sum = 0;
                foreach ($statistics_data as $key => $val) {
                    $sum += $val['repay_count'];
                }
                foreach ($statistics_data as $key => $val) {
                    if ($val['repay_type'] == 1) {
                        $data_name_raw[0] = "应还金额（" . $val['repay_count'] . "元）";
                    } else {
                        $data_name_raw[1] = "实还金额（" . $val['repay_count'] . "元）";
                    }
                    if (empty($val)) {
                        $list[$key] = 0;
                    } else {
                        $list[$key] = round($val['repay_count'] / ($sum) * 100, 2);
                    }
                }
            }
            $this->assign('type', 'pie'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $date_list[] = $start_time_int;
        } else {
            //日期不相等
            $date_lists = M("statistical_repay")->order("repay_date asc")->where("repay_date>=$start_time_int and repay_date<=$end_time_int")->field("repay_date")->group("repay_date")->findAll();
            for ($i = 1; $i <= 2; $i++) {
                $lists[] = M("statistical_repay")->order("repay_date asc")->where("repay_date >= $start_time_int and repay_date <= $end_time_int and repay_type=$i")->findAll();
            }

            $list = array();
            if (!empty($lists)) {
                foreach ($lists as $key => $val) {
                    foreach ($val as $k => $v) {
                        $list[$key][] = (float) $v['repay_count'];
                    }
                }
            }
            //时间重新组装，去掉关联索引改成自然索引
            $date_list = array();
            if (!empty($date_lists)) {
                foreach ($date_lists as $k => $v) {
                    $date_list[] = $v['repay_date'];
                }
            }
            $data_name_raw = ['应还金额', '实还金额'];
            $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
            $statistics_data = M("statistical_repay")->where("repay_date>=$start_time_int and repay_date<=$end_time_int")->findAll();
        }

        $client_list = array();
        foreach ($statistics_data as $key => $val) {
            $client_list['gross'][$val['repay_type']]+=$val['repay_count'];
            $client_list[$val['repay_date']]['repay_date'] = $val['repay_date'];
            if ($val['repay_type'] == $statistics_data[$key]['repay_type']) {
                $client_list[$val['repay_date']][$val['repay_type']] = $val['repay_count'];
            }
        }
        $client_list['gross']['repay_date'] = "总计";
        krsort($client_list);
        $this->assign("repay_total", $repay_total);
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));

        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        $this->assign('date_list', $date_list);
        //传入的数据数组，必填，类型为数组
        $this->assign('data_array', json_encode($list));
        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit', json_encode("元"));
        $this->assign('series_name', json_encode("百分比"));
        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name', json_encode($data_name_raw));
        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($list));
        $this->assign('client_list', $client_list); //表格中的数据
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    //还款逾期的列表
    public function overdue() {
        $begin_time = !empty($_REQUEST['begin_time']) ? $_REQUEST['begin_time'] : "2016-02-09";
        $end_time = !empty($_REQUEST['end_time']) ? $_REQUEST['end_time'] : "2016-02-22";
        $user_name = isset($_REQUEST['user_name']) ? $_REQUEST['user_name'] : "";
        $real_name = isset($_REQUEST['real_name']) ? $_REQUEST['real_name'] : "";
        $mobile = isset($_REQUEST['mobile']) ? $_REQUEST['mobile'] : "";
        $is_mohu = isset($_REQUEST['is_mohu']) ? $_REQUEST['is_mohu'] : "";
        $where = "";
        if ($begin_time && $end_time) {
            $where.= " and d.jiexi_time>='$begin_time' and d.jiexi_time<='$end_time' ";
            $this->assign("begin_time",$begin_time);
            $this->assign("end_time",$end_time);
        }
        if($is_mohu){
            $before_str.="";
        }
        if($user_name){
            $where.=" and u.user_name ";
            $where.=$is_mohu?"like '%$user_name%'":"='$user_name'";
        }
        if($real_name){
            $where.=" and u.real_name ";
            $where.=$is_mohu?"like '%$real_name%'":"='$real_name'";
        }
        if($mobile){
            $where.=" and u.mobile ";
            $where.=$is_mohu?"like '%$mobile%'":"='$mobile'";
        }
        $sql_str = "SELECT
	dlr.user_id,
            dlr.self_money * d.rate / 100 / 360 * (
                    dlr.true_repay_date - d.jiexi_time
            ) AS jxmoney
        ,d.name as deal_name
        ,d.rate as deal_rate
        ,dlr.self_money as deal_load_money
        ,dlr.true_repay_date as true_repay_date
        ,d.jiexi_time as jiexi_time
        ,dlr.true_repay_date - d.jiexi_time as day
FROM
	fanwe_deal_load_repay AS dlr
LEFT JOIN fanwe_deal AS d ON (
	dlr.deal_id = d.id
)
LEFT JOIN fanwe_user AS u ON (
	dlr.user_id = u.id
)
WHERE
	dlr.true_repay_date != '0000-00-00'
AND d.jiexi_time < dlr.true_repay_date " . $where;
        if ($_GET['xls'] != 'true') {
            $this->_Sql_list(D(), $sql_str,"","dlr.id");
            $this->display();
        } else {
            $all_list = $GLOBALS['db']->getAll($sql_str." group by dlr.id desc");
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num = 1;
            foreach ($all_list as $key => $val) {
                if ($num == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '用户名')
                            ->setCellValue('B' . $num, '标的名称')
                            ->setCellValue('C' . $num, '实际还款日')
                            ->setCellValue('D' . $num, '结息日')
                            ->setCellValue('E' . $num, '投资金额')
                            ->setCellValue('F' . $num, '年化利率')
                            ->setCellValue('G' . $num, '逾期天数')
                            ->setCellValue('H' . $num, '逾期补偿金额');
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, strip_tags(get_user_name_real($val['user_id'])))
                        ->setCellValue('B' . $num, $val['deal_name'])
                        ->setCellValue('C' . $num, $val['true_repay_date'])
                        ->setCellValue('D' . $num, $val['jiexi_time'])
                        ->setCellValue('E' . $num, $val['deal_load_money'])
                        ->setCellValue('F' . $num, $val['deal_rate'])
                        ->setCellValue('G' . $num, $val['day'])
                        ->setCellValue('H' . $num, num_format($val['jxmoney']));
                $num++;
            }

            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(13);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
            $filename = $begin_time . '~' . $end_time . "逾期还款详情";
            php_export_excel($objPHPExcel, $filename);
            exit;
        }
    }

}
