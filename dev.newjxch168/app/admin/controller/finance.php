<?php

namespace admin\controller;
use base\controller\backend;

/**
 * 后台财务管理模块控制器
 *
 * @author jxch
 */

class Finance extends backend{

    //入金放款列表
    public function loan(){
        //满标
        $condition['deal_status'] = 2;
        //审核状态
        $condition['verify_status'] = 0;
        $type = 'qixi_date';
        return $this->commonShow($condition,$type,"loan");
    }

    //还款审核列表
    public function verify(){
        //还款中
        $condition['deal_status'] = 3;
        //待审核
        $condition['verify_status'] = 1;
        //$map['verify_status'] = array("in","1,2");
        $type = 'jiexi_date';
        return $this->commonShow($condition,$type,"verify");
    }

    //网站还款列表
    public function repay(){
        //还款中
        $condition['deal_status'] = 3;
        //待审核
        $condition['verify_status'] = 2;
        $type = 'jiexi_date';
        return $this->commonShow($condition,$type,"repay");
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
        $financeModel = D("finance");
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
        return $this->fetch($show_page);
    }

    //入金审核详情列表  显示某个日期入金审核 详细
    function show_loan(){
       //满标
        $map['deal_status'] = array("in","2,3,4");
        //审核状态
        $map['verify_status'] = array("in","0,1,2");
        return $this->repayCommon($map,"show_loan","qixi_date");
    }

    //还款审核详情列表  显示某个日期入还款审核 详细
    function show_verify(){
        //还款中
        //$map['deal_status'] = 3;
        $map['deal_status'] = array("in","3,4");
        //还款审核完成
        $map['verify_status'] = array("in","1,2");
        return $this->repayCommon($map,"show_verify");
    }

    //网站还款某个日期下还款列表
    public function show_repay(){
        //还款中 已还清
        $map['deal_status'] = array("in","3,4");
        //还款审核完成
        $map['verify_status'] = 2;
        return $this->repayCommon($map,"show_repay");
    }

    function repayCommon($condition = [],$show_page,$oper_type="jiexi_date"){
        $oper_date = trim($_REQUEST[$oper_type]);
        $orderBy ="create_time desc";
        //查询条件
        $map[$oper_type] = $oper_date;
        $map['is_delete'] = 0;
        $map['is_effect'] = 1;
        if($condition){
            $map = array_merge($map,$condition);
        }
        //输出投标列表 分页参数
        $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
        $page_size = 10;
        //实例化
        $financeModel = D("finance");
        $limit = $financeModel->getLimit($page,$page_size);
        $return = $financeModel->getDealList($map,$orderBy,$limit);
        //富友资金池信息数据
        $cash_data = $financeModel->getAccount();
        $this->assign("all_loads_money",($return['date_all_repay_money']/100));
        //剩余总额
        if($oper_type == "qixi_date"){
            $remain_money = ($cash_data['ca_balance'] + ($return['remain_capital']/100));
        }else{
            $remain_money = ($cash_data['ca_balance'] - ($return['remain_repay_money']/100));
        }
        $this->assign("account_all_money",($remain_money ? $remain_money : 0));
        $this->assign("ca_balance",($cash_data['ca_balance'] ? $cash_data['ca_balance'] : 0));
        //管理员信息
        $this->assign("admin_info", $financeModel->getAdminInfo());
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
        return $this->fetch($show_page);
    }

    //确认入金
    public function do_loan(){
        $id = intval($_REQUEST['id']);
        $loan_pwd = md5(trim($_REQUEST['loan_pwd']));
        $financeModel = D('finance');
        //验证管理员操作密码是否正确
        if(!$financeModel->checkAdminPwd($loan_pwd,'loan_pwd')){
            $result['status'] = 0;
            $result['info'] = "还款审核密码输入错误，请重新输入！";
            die(json_encode($result));
        }

       $result = D('finance')->do_site_loan($id);
       die(json_encode($result));
    }

    //还款审核操作
    function verify_deal(){
        $id = intval($_REQUEST['id']);
        $verify_pwd = md5(trim($_REQUEST['verify_pwd']));

        //实例化
        $financeModel = D("finance");
        //验证管理员操作密码是否正确
        if(!$financeModel->checkAdminPwd($verify_pwd,'verify_pwd')){
            $result['status'] = 0;
            $result['info'] = "还款审核密码输入错误，请重新输入！";
            die(json_encode($result));
        }
        //更新标的审核状态
        $data['id'] = $id;
        $data['verify_status'] = 2;
        if($financeModel->updateModel('deal',$data)){
            $result['status'] = 1;
            $result['info'] = "还款审核成功！";
        }else{
            $result['status'] = 0;
            $result['info'] = "还款审核失败！";
        }
        die(json_encode($result));
    }

    //网站还款操作 代还款
    function do_site_repay() {
        set_time_limit(0);
        $id = intval($_REQUEST['id']);
        $l_key = intval($_REQUEST['l_key']);
        $repay_pwd = md5(trim($_REQUEST['repay_pwd']));
        //实例化
        $financeModel = D("finance");
        //验证管理员操作密码是否正确
        if(!$financeModel->checkAdminPwd($repay_pwd,'repay_pwd')){
            $result['status'] = 0;
            $result['info'] = "还款操作密码输入错误，请重新输入！";
            die(json_encode($result));
        }
        //验证资金池账户余额
        if(!$financeModel->checkFuyouBalance($id)){
            $result['status'] = 0;
            $result['info'] = "富友资金池账户资金不足，暂时无法还款，请及时充值！";
            die(json_encode($result));
        }
        //打开文件 执行文件锁 进行排它型锁定
        $file_dir = APP_PATH . "ORG/fuyou_key/do_site_repay.lock";
        $fp = fopen($file_dir, "w+");
        if (flock($fp, LOCK_EX)) {
            $result = $financeModel->do_site_repay($id,$l_key);
            flock($fp, LOCK_UN); // 释放锁定
            //关闭文件流
            fclose($fp);
        } else {
            fclose($fp);
            $result['status'] = 0;
            $result['info'] = "网站代还款进程已被占用，请稍后再试...";
            die(json_encode($result));
        }

        //检测总账
        //$this->_mod->checkJxchAccount($_REQUEST["ca_balance"],$result['admin_log']["money"],$id,"repay");
        die(json_encode($result));
    }

    //获取标的下投资记录
    public function get_deal_loads(){
        $deal_id = intval($_REQUEST['deal_id']);
        $l_key = intval($_REQUEST['l_key']);

        if (!$deal_id) {
            die(json_encode(array("status"=>0,"info"=>"数据错误")));
        }
        //输出投标列表 分页参数
        $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
        $page_size = 10;
        //实例化
        $financeModel = D("finance");
        $limit = $financeModel->getLimit($page,$page_size);
        $result = $financeModel->get_deal_load_list($deal_id,'', $limit);
        //分页数据
        $rs_count = $result['load_count'];
        $page_all = ceil($rs_count / $page_size);
        $this->assign("load_user", $result['load_list']);
        $this->assign("l_key", $l_key);
        $this->assign("page_all", $page_all);
        $this->assign("rs_count", $rs_count);
        $this->assign("page", $page);
        $this->assign("deal_id", $deal_id);
        $this->assign("page_prev", $page - 1);
        $this->assign("page_next", $page + 1);
        $html = $this->fetch();
        die(json_encode(array("status"=>1,"info"=>$html)));
    }

    //保存管理密码
    function savePwd(){
        $type = trim($_REQUEST['type']);
        $oper_name_arr = array("verify"=>"还款审核","repay"=>"还款操作","loan"=>"入金操作");

        if($oper_name_arr[$type]){
            $oper_pwd = trim($_REQUEST[$type.'_pwd']);
            $confirm_oper_pwd = trim($_REQUEST['confirm_'.$type.'_pwd']);
            $oper_name = $oper_name_arr[$type];
            if($oper_pwd != $confirm_oper_pwd){
                $result['status'] = 0;
                $result['info'] = "两次输入的密码不一致，请重新输入！";
            }
            $data[$type.'_pwd'] = md5(trim($oper_pwd));
        }else{
            $result['status'] = 0;
            $result['info'] = "没有该密码类型！";
        }

        //错误提示信息
        if($result){
            if(IS_AJAX){
                die(json_encode(array("status"=>$result['status'],"info"=>$result['info'])));
            }else{
                return $this->error($result['info']);
            }
        }
        //实例化
        $financeModel = D("finance");
        $res = $financeModel->saveAdmin($data);
        if($res){
            $result['status'] = 1;
            $result['info'] = $oper_name."密码设置成功！";

        }else{
            $result['status'] = 0;
            $result['info'] = $oper_name."密码设置失败！";
        }
        if(IS_AJAX){
            die(json_encode($result));
        }else{
            if($result['status']){
                return $this->success($result['info']);
            }else{
                return $this->error($result['info']);
            }
        }
    }
}
