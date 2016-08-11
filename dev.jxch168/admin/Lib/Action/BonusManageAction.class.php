<?php

class BonusManageAction extends CommonAction{
    protected $_mod;
    protected $adminInfo;

    //初始化函数
    public function _initialize() {
            parent::_initialize();
            $this->_mod = D('UserBonus');

            //管理员信息
            $this->adminInfo = $this->_mod->getAdminInfo();
            $this->assign("adminInfo",$this->adminInfo);
    }
    //红包审核
    public function  bonusIdentify(){
        $_REQUEST['type'] = "identify";
        //已审核的红包
        $map['verify_status'] = 0;
        $this->getCommonWay($map);
    }

    //红包日历展示 红包放款
    public function bonusRepay(){
        $_REQUEST['type'] = "repay";
        //已审核的红包
        $map['verify_status'] = 1;
        $this->getCommonWay($map);
    }

    //公共方法
    public function getCommonWay($map_arr){
        $dMonth = $_REQUEST['dmonth'];

        if (empty($dMonth)) {
            $dMonth = date('Y-m');
        }
        $map['is_effect'] = 1;
        //待放款红包
        $map['status'] = 1;
        //合并
        $map = array_merge($map,$map_arr);
        $result = $this->_mod->init_bonus($map,$dMonth);
        $now_month = explode('-',$dMonth);
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("week_info", $result['week_info']);
        $this->assign("dMonth", $dMonth);
        $this->assign("Month", $now_month[0]."年".$now_month[1]."月");
        $this->assign("onMonth", D('Calendar')->onMonth($dMonth));
        $this->assign("lastMonth", D('Calendar')->lastMonth($dMonth));
        $this->assign("today", date("Y-m-d"));
        if($_REQUEST['type'] == "identify"){
            $this->assign("rewardOper", "bonusIdentify");
        }else if($_REQUEST['type'] == "repay"){
            $this->assign("rewardOper", "bonusRepay");
        }
        $this->display("bonusRepay");
        return;
    }

    //某日期下 红包详情展示
    public function show_bonus(){

        $release_date = trim($_REQUEST['release_date']);
        $orderBy ="release_time desc";
        //查询条件
        $map['release_date'] = strtotime($release_date);
        $map['is_effect'] = 1;
        $map['status'] = array("in","0,1,2");//0 未处理 1 已提交提现申请 2 已发放
        if($_REQUEST['type'] == "identify"){
            $map['verify_status'] = array("in","0,1");//0 未审核 1 审核
        }else if($_REQUEST["type"] == "repay"){
            $map['verify_status'] = array("in","1");//0 未审核 1 审核
        }
        //输出红包列表 分页参数
        $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
        $page_size = 10;
        $limit = $this->_mod->getLimit($page,$page_size);
        $return = $this->_mod->getBonusList($map,$orderBy,$limit);

        //选择账户 PAY_LOG_NAME FUYOU_MCHNT_FR
        $account_name = PAY_LOG_NAME;
        //富友资金池信息数据
        $cash_data = $this->_mod->getAccount($account_name);
        $where['release_date'] = strtotime($release_date);
        $where['is_effect'] = 1;
        $where['status'] = 1;
        $where['verify_status'] = 1;
        $res = $this->_mod->getBonusList($where,$orderBy,'');
        $this->assign("bonus_money_total",$res['bonus_money_total']);
        //剩余总额
        $cash_data['ca_balance'] = $cash_data['ca_balance'] ? $cash_data['ca_balance'] : 0;
        $this->assign("account_all_money",($cash_data['ca_balance'] - $res['bonus_money_total']));
        $this->assign("ca_balance",$cash_data['ca_balance']);

        //分页数据
        $rs_count = $return['count'];
        $page_all = ceil($rs_count / $page_size);
        $this->assign("page_all", $page_all);
        $this->assign("rs_count", $rs_count);
        $this->assign("page", $page);
        $this->assign("page_prev", $page - 1);
        $this->assign("page_next", $page + 1);
        $this->assign("list",$return['list']);
        //前一天
        $this->assign("yesterday_date",date("Y-m-d",strtotime("-1 day",strtotime($release_date))));
        //后一天
        $this->assign("tomorrow_date",date("Y-m-d",strtotime("+1 day",strtotime($release_date))));
        $this->assign("release_date",$release_date);
        $this->assign("FUYOU_MCHNT_FR",FUYOU_MCHNT_FR);

        //是否设置管理员红包操作密码
         $adminInfo = $this->_mod->getAdminInfo();
        if($_REQUEST['type'] == "identify"){
            $type_pwd = $adminInfo["bonus_identify_pwd"];
        }else if($_REQUEST["type"] == "repay"){
            $type_pwd = $adminInfo["bonus_repay_pwd"];
        }
        $this->assign("type_pwd",$type_pwd);
        $this->assign("account_name",$account_name);

        $this->display();
        return;
    }

    //检测原始密码
    public function checkOldPwd(){
        $type = strim($_REQUEST['type']);
        $info = strim($_REQUEST['info']);
        $old_password = md5(strim($_REQUEST['old_password']));
        //验证管理员操作密码是否正确
        if(!$this->_mod->checkAdminPwd($old_password,'bonus_'.$type.'_pwd')){
            $result['status'] = 0;
            $result['info'] = $info."原始密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
            die();
        }
        $this->ajaxReturn($result, 1, 1);
    }

    //红包奖励批量审核
    public function bonus_verify(){
        $this->batch_operate();
    }

    //红包奖励批量放款
    public function bonus_repay(){
        $this->batch_operate();
    }

    //红包批量操作 批量放款 批量审核
    public function batch_operate(){
        set_time_limit(0);
        //验证管理员操作密码是否正确
        if(!$this->_mod->checkAdminPwd(md5($_REQUEST["oper_password"]),"bonus_".$_REQUEST["type"]."_pwd")){
            $result['status'] = 0;
            $result['info'] = "红包".$_REQUEST["info"]."密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
        }

        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['release_date']?($BeginDateToday):($_REQUEST['release_date']);
        $release_date = strtotime($BeginDate);
        $orderBy ="release_time desc";
        $where = array('is_effect' => 1,'release_date'=> $release_date,'status' => 1);
        if($_REQUEST["type"] == "identify"){
            $where["verify_status"] = 0;
            $result = $this->_mod->batch_verify($where,$orderBy);

        }else if($_REQUEST["type"] == "repay"){
            $where["verify_status"] = 1;
            $result = $this->_mod->batch_bonus($where,$orderBy);
            //管理员日志
            $result['admin_log']['admin_name'] = $this->adminInfo['adm_name'];
            $result['admin_log']['admin_id'] = $this->adminInfo['id'];
            //$result['admin_log']['admin_role_id'] = $this->adminInfo['role_id'];
            //$admin_role = M("role")->find($this->adminInfo['role_id']);
            //$result['admin_log']['admin_role_name'] = $admin_role['name'];
            $result['admin_log']['module_name'] = MODULE_NAME;
            $result['admin_log']['action_name'] = ACTION_NAME;
            $admin_log_id = M("admin_log")->add($result['admin_log']);
        }else{
            $result["info"] = "非法操作！";
            $result["status"] = 0;
        }

        $this->ajaxReturn('',$result["info"], $result["status"]);
    }

    //导出红包记录
    public function export_bonus(){

        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['release_date']?($BeginDateToday):($_REQUEST['release_date']);
        $begin_time = strtotime($BeginDate);
        $orderBy ="release_time desc";
        $where = array('is_effect' => 1,'release_date'=> $begin_time,"status" => array("in","0,1,2"));
        if($_REQUEST['type'] == "repay"){
            $where["verify_status"] = 1;
        }
        $this->_mod->export_bonus($where,$orderBy);
        die;
    }

    //保存密码
    public function savePwd(){
        if($_REQUEST['ajax_off']){
            $type = $_REQUEST["type"];
            $pwd = $_REQUEST[$type."_password"];
            $confirm_pwd = $_REQUEST["confirm_".$type."_password"];
            if($pwd != $confirm_pwd){
                $this->error("两次密码输入不一致！");
            }
            $data['bonus_'.$type.'_pwd'] = md5($pwd);
            if($type == "identify"){
                $name = "审核";
            }else if($type == "repay"){
                $name = "放款";
            }
            $res = $this->_mod->saveBonusPwd($data);
            if($res){
                $result['info'] = "红包".$name."密码设置成功";
            }else{
                $result['info'] = "红包".$name."密码设置失败";
            }
            $this->success($result['info']);
        }else{
            $oper_password = $_REQUEST['oper_password'];
            $confirm_oper_password = $_REQUEST['confirm_oper_password'];
            if($oper_password != $confirm_oper_password){
                $this->ajaxReturn($result, "两次密码输入不一致！",0);
            }
            if($_REQUEST['oper_type'] == "identify"){
                $data['bonus_identify_pwd'] = md5($oper_password);
                $name = "审核";
            }else if($_REQUEST["oper_type"] == "repay"){
                $data['bonus_repay_pwd'] = md5($oper_password);
                $name = "放款";
            }
            $res = $this->_mod->saveBonusPwd($data);
            if($res){
                $result['info'] = "红包".$name."密码设置成功";
                $result['status'] = 1;
            }else{
                $result['info'] = "红包".$name."密码设置失败";
                $result['status'] = 0;
            }
            $this->ajaxReturn($result, $result['info'], $result['status']);
        }
    }
}
?>
