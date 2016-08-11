<?php

class UserRewardAction extends CommonAction{
    protected $_mod;
    protected $adminInfo;

    //初始化函数
    public function _initialize() {
            parent::_initialize();
            $this->_mod = D('UserReward');

            //管理员信息
            $this->adminInfo = $this->_mod->getAdminInfo();
            $this->assign("adminInfo",$this->adminInfo);
    }
    //提成奖励审核
    public function  rewardIdentify(){
        $_REQUEST['type'] = "identify";
        //已审核的提成奖励
        $map['verify_status'] = 0;
        $this->getCommonWay($map);
    }

    //提成奖励日历展示 提成奖励放款
    public function rewardRepay(){
        $_REQUEST['type'] = "repay";

        //已审核的提成奖励
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
        //待放款提成奖励
        $map['status'] = 0;
        //合并
        $map = array_merge($map,$map_arr);
        $result = $this->_mod->init_reward($map,$dMonth);
        $now_month = explode('-',$dMonth);
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("week_info", $result['week_info']);
        $this->assign("dMonth", $dMonth);
        $this->assign("Month", $now_month[0]."年".$now_month[1]."月");
        $this->assign("onMonth", D('Calendar')->onMonth($dMonth));
        $this->assign("lastMonth", D('Calendar')->lastMonth($dMonth));
        $this->assign("today", date("Y-m-d"));
        if($_REQUEST['type'] == "identify"){
            $this->assign("rewardOper", "rewardIdentify");

        }else if($_REQUEST['type'] == "repay"){
            $this->assign("rewardOper", "rewardRepay");
        }
        $this->display("rewardRepay");
        return;
    }

    //某日期下 提成奖励详情展示
    public function show_reward(){

        $release_date = trim($_REQUEST['release_date']);
        $orderBy ="release_date desc";
        //查询条件
        $map['release_date'] = strtotime($release_date);
        $map['is_effect'] = 1;
        $map['status'] = array("in","0,1");//0 未发放 2 已发放
        if($_REQUEST['type'] == "identify"){
            $map['verify_status'] = array("in","0,1");//0 未审核 1 审核
        }else if($_REQUEST["type"] == "repay"){
            $map['verify_status'] = array("in","1");//0 未审核 1 审核
        }
        //输出提成奖励列表 分页参数
        $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
        $page_size = 10;
        $limit = $this->_mod->getLimit($page,$page_size);
        $return = $this->_mod->getRewardList($map,$orderBy,$limit);

        //选择账户 PAY_LOG_NAME FUYOU_MCHNT_FR
        $account_name = PAY_LOG_NAME;
        //富友资金池信息数据
        $cash_data = $this->_mod->getAccount($account_name);
        $where['release_date'] = strtotime($release_date);
        $where['is_effect'] = 1;
        $where['status'] = 0;
        $where['verify_status'] = 1;
        $res = $this->_mod->getRewardList($where,$orderBy,'');
        $this->assign("reward_money_total",$res['reward_money_total']);
        //剩余总额
        $cash_data['ca_balance'] = $cash_data['ca_balance'] ? $cash_data['ca_balance'] : 0;
        $this->assign("account_all_money",($cash_data['ca_balance'] - $res['reward_money_total']));
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

        //是否设置管理员提成奖励操作密码
         $adminInfo = $this->_mod->getAdminInfo();
        if($_REQUEST['type'] == "identify"){
            $type_pwd = $adminInfo["reward_identify_pwd"];
        }else if($_REQUEST["type"] == "repay"){
            $type_pwd = $adminInfo["reward_repay_pwd"];
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
        if(!$this->_mod->checkAdminPwd($old_password,'reward_'.$type.'_pwd')){
            $result['status'] = 0;
            $result['info'] = $info."原始密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
            die();
        }
        $this->ajaxReturn($result, 1, 1);
    }

    //提成奖励批量审核
    public function batch_verify(){
        $this->batch_operate();
    }

    //提成奖励批量放款
    public function batch_repay(){
        $this->batch_operate();
    }

    //提成奖励批量操作公共方法
    public function batch_operate(){
        set_time_limit(0);
        //验证管理员操作密码是否正确
        if(!$this->_mod->checkAdminPwd(md5($_REQUEST["oper_password"]),"reward_".$_REQUEST["type"]."_pwd")){
            $result['status'] = 0;
            $result['info'] = "提成奖励".$_REQUEST["info"]."密码输入错误，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
        }

        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['release_date']?($BeginDateToday):($_REQUEST['release_date']);
        $release_date = strtotime($BeginDate);
        $orderBy ="release_date desc";
        $where = array('is_effect' => 1,'release_date'=> $release_date,'status' => 0);
        if($_REQUEST["type"] == "identify"){
            $where["verify_status"] = 0;
            $result = $this->_mod->batch_verify($where,$orderBy);

        }else if($_REQUEST["type"] == "repay"){
            $where["verify_status"] = 1;
            $result = $this->_mod->batch_reward($where,$orderBy);
            //管理员日志
            $result['admin_log']['admin_name'] = $this->adminInfo['adm_name'];
            $result['admin_log']['admin_id'] = $this->adminInfo['id'];
            $result['admin_log']['module_name'] = MODULE_NAME;
            $result['admin_log']['action_name'] = ACTION_NAME;
            $admin_log_id = M("admin_log")->add($result['admin_log']);
        }else{
            $result["info"] = "非法操作！";
            $result["status"] = 0;
        }

        $this->ajaxReturn('',$result["info"], $result["status"]);
    }

    //导出提成奖励记录
    public function export_reward(){

        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['release_date']?($BeginDateToday):($_REQUEST['release_date']);
        $begin_time = strtotime($BeginDate);
        $orderBy ="release_date desc";
        $where = array('is_effect' => 1,'release_date'=> $begin_time,"status" => array("in","0,1"));
        if($_REQUEST['type'] == "repay"){
            $where["verify_status"] = 1;
        }
        $this->_mod->export_reward($where,$orderBy);
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
            $data['reward_'.$type.'_pwd'] = md5($pwd);
            if($type == "identify"){
                $name = "审核";
            }else if($type == "repay"){
                $name = "放款";
            }
            $res = $this->_mod->saveRewardPwd($data);
            if($res){
                $result['info'] = "提成奖励".$name."密码设置成功";
            }else{
                $result['info'] = "提成奖励".$name."密码设置失败";
            }
            $this->success($result['info']);
        }else{
            $oper_password = $_REQUEST['oper_password'];
            $confirm_oper_password = $_REQUEST['confirm_oper_password'];
            if($oper_password != $confirm_oper_password){
                $this->ajaxReturn($result, "两次密码输入不一致！",0);
            }
            if($_REQUEST['oper_type'] == "identify"){
                $data['reward_identify_pwd'] = md5($oper_password);
                $name = "审核";
            }else if($_REQUEST["oper_type"] == "repay"){
                $data['reward_repay_pwd'] = md5($oper_password);
                $name = "放款";
            }
            $res = $this->_mod->saveRewardPwd($data);
            if($res){
                $result['info'] = "提成奖励".$name."密码设置成功";
                $result['status'] = 1;
            }else{
                $result['info'] = "提成奖励".$name."密码设置失败";
                $result['status'] = 0;
            }
            $this->ajaxReturn($result, $result['info'], $result['status']);
        }
    }
}
?>
