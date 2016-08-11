<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class UserAction extends CommonAction {

    public $_mod;

    //初始化函数
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('User');
    }

    public function __construct() {
        parent::__construct();
        require_once APP_ROOT_PATH . "/system/libs/user.php";
    }

    public function index() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $map[DB_PREFIX . 'user.is_auto'] = 0;
        $this->getUserList(0, 0, $map);

        $list = $this->view->get('list');
        // 关联投标的表和充值的表
        $list3 = array();
        foreach ($list as $k => $v) {
             $tmp1 = $GLOBALS['db']->getRow("SELECT sum(money)  as num FROM `fanwe_deal_load` WHERE user_id = '{$v['id']}' and `contract_no` !='' ");
            $money = $tmp1['num'];
            if ($money) {
                $v['deal'] = $money;
            } else {
                $v['deal'] = 0;
            }
            array_push($list3, $v);
        }
        //var_dump(M("deal_load"));
        $list2 = array();
        foreach ($list3 as $item) {
            $where = array('mobile' => $item["mobile"]);
            $mobile = M("UserMobileHomeaddress")->where($where)->find();
            if ($mobile) {
                $item['mobile'] = $item['mobile'] . "[" . $mobile['province'] . "-" . $mobile['city'] . "]";
            }
            array_push($list2, $item);
        }

        $list4 = array();
        foreach ($list2 as $k => $v) {


            $where = array('user_id' => $v["id"], 'is_paid' => 1);
            $money = M("payment_notice")->where($where)->getField('sum(money)');
            if ($money) {
                $v['payment'] = $money;
            } else {
                $v['payment'] = 0;
            }
            $v['error_num'] = M("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 0))->count();
            $v['success_num'] = M("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 1))->count();
            array_push($list4, $v);
        }

        $this->assign('list', $list4);
        $this->display();
    }

    public function company_index() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $this->getUserList(1, 0, $map);
        $this->display("index");
    }

    public function black() {
        $map[DB_PREFIX . 'user.is_black'] = array('eq', 1);
        $this->getUserList(0, 0, $map);
        $this->display("index");
    }

    public function company_black() {
        $map[DB_PREFIX . 'user.is_black'] = array('eq', 1);

        $this->getUserList(1, 0, $map);
        $this->display("index");
    }

    public function register() {
        $map[DB_PREFIX . 'user.is_effect'] = array('eq', 0);
        $map[DB_PREFIX . 'user.login_time'] = array('eq', 0);
        $map[DB_PREFIX . 'user.login_ip'] = array('eq', '');

        $this->getUserList(0, 0, $map);
        $this->display("index");
    }

    public function company_register() {
        $map[DB_PREFIX . 'user.is_effect'] = array('eq', 0);
        $map[DB_PREFIX . 'user.login_time'] = array('eq', 0);
        $map[DB_PREFIX . 'user.login_ip'] = array('eq', '');

        $this->getUserList(1, 0, $map);
        $this->display("index");
    }

    //富友交易记录
    public function trading_record() {
        $BeginDate = date('Ym01', strtotime(date("Ymd")));
        $default_end = date('Ymd');
        //$default_begin = date("Ymd",strtotime("-31 day",strtotime($default_end)));
        $default_begin = $BeginDate;

        $begin_time = $_REQUEST['begin_time'] ? $_REQUEST['begin_time'] : $default_begin;
        $end_time = $_REQUEST['end_time'] ? $_REQUEST['end_time'] : $default_end;

        $user_id = $_REQUEST['user_id'];
        //富友转账 还款
        require_once APP_ROOT_PATH . "system/payment/fuyou.php";
        $fuyou = new fuyou();
        $user_info = M('user')->find($user_id);
        $trade_type = $_REQUEST['trade_type'];
        if (!$trade_type) {
            $trade_type = "PWPC";
        }

        //转账记录数据
        $arr = $fuyou->findTradeRecord($user_info, $trade_type, $begin_time, $end_time);
        $arr = json_decode(json_encode($arr->plain), TRUE);
        $remind_codes = require_once APP_ROOT_PATH."data_conf/remind_code.php";
        if("0000" != $arr['resp_code']){
            if($remind_codes[$arr['resp_code']]){
                $resp_desc = $remind_codes[$arr['resp_code']];
            }else{
                $resp_desc = "时间周期选择有误或富友返回数据异常，请重试或查看日志！";
            }
            $this->error($resp_desc);
            exit;
        }

        if (is_array($arr['results']['result'][0])) {
            foreach ($arr['results']['result'] as $key => $val) {
                $arr['results']['result'][$key]['id'] = $key + 1;
                $arr['results']['result'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                $arr['results']['result'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                $arr['results']['result'][$key]['txn_amt'] = $val['txn_amt'] / 100;
                $arr['results']['result'][$key]['txn_amt_suc'] = $val['txn_amt_suc'] / 100;
            }
        } else {
            foreach ($arr['results'] as $key => $val) {
                $arr['results'][$key]['id'] = $key + 1;
                $arr['results'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                $arr['results'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                $arr['results'][$key]['txn_amt'] = $val['txn_amt'] / 100;
                $arr['results'][$key]['txn_amt_suc'] = $val['txn_amt_suc'] / 100;
            }
        }

        //数据格式化
        if (is_array($arr['results']['result'][0])) {
            $list = $arr['results']['result'];
        } else {
            $list = $arr['results'];
        }

        $this->assign("list", $list);
        $this->assign("user_id", $user_id);
        $this->assign("begin_time", $begin_time);
        $this->assign("end_time", $end_time);
        $this->assign("trade_type", $trade_type);
        $this->display();
    }

    //富友充值提现记录
    public function incharge_carry_record() {
        $trade_type = $_REQUEST['trade_type'];
        if (!$trade_type) {
            $trade_type = "PW11";
        }
        $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $default_end = date('Y-m-d');
        if($trade_type == "PW11"){
            $default_begin = $BeginDate;
        }else if($trade_type == "PWTX"){
            $default_begin = date("Y-m-d",strtotime("-31 day",strtotime($default_end)));
        }
        $begin_time = $_REQUEST['begin_time'] ? $_REQUEST['begin_time'] : $default_begin;
        $end_time = $_REQUEST['end_time'] ? $_REQUEST['end_time'] : $default_end;
        $start_time = $begin_time . " 00:00:00";
        $final_time = $end_time . " 23:59:59";

        $user_id = $_REQUEST['user_id'];
        //富友转账 还款
        require_once APP_ROOT_PATH . "system/payment/fuyou.php";
        $fuyou = new fuyou();
        $user_info = M('user')->find($user_id);

        //转账记录数据
        $arr = $fuyou->findInchargeCarryRecord($user_info, $trade_type, $start_time, $final_time);
        $arr = json_decode(json_encode($arr->plain), TRUE);
        $remind_codes = require_once APP_ROOT_PATH."data_conf/remind_code.php";
        if("0000" != $arr['resp_code']){
            if($remind_codes[$arr['resp_code']]){
                $resp_desc = $remind_codes[$arr['resp_code']];
            }else{
                $resp_desc = "时间周期选择有误或富友返回数据异常，请重试或查看日志！";
            }
            $this->error($resp_desc);
            exit;
        }

        if (is_array($arr['results']['result'][0])) {
            foreach ($arr['results']['result'] as $key => $val) {
                $arr['results']['result'][$key]['id'] = $key + 1;
                $arr['results']['result'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                $arr['results']['result'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                $arr['results']['result'][$key]['txn_amt'] = $val['txn_amt'] / 100;
            }
        } else {
            foreach ($arr['results'] as $key => $val) {
                $arr['results'][$key]['id'] = $key + 1;
                $arr['results'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                $arr['results'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                $arr['results'][$key]['txn_amt'] = $val['txn_amt'] / 100;
            }
        }
        //数据格式化
        if (is_array($arr['results']['result'][0])) {
            $list = $arr['results']['result'];
        } else {
            $list = $arr['results'];
        }

        foreach($list as $key=>$val){
            if($trade_type == "PW11"){
                $id = M("payment_notice")->where(array("notice_sn"=>$val['mchnt_ssn']))->getField("id");
            }else if($trade_type == "PWTX"){
                $id = M("user_carry")->where(array("mchnt_txn_ssn"=>$val['mchnt_ssn']))->getField("id");
            }
            if($id){
               $list[$key]["jxtype"] = 1;
            }
        }

        $this->assign("list", $list);
        $this->assign("user_id", $user_id);
        $this->assign("begin_time", $begin_time);
        $this->assign("end_time", $end_time);
        $this->assign("trade_type", $trade_type);
        $this->display();
    }

    public function info() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $this->getUserList(0, 0, $map);
        $this->display();
    }

    public function company_info() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $this->getUserList(1, 0, $map);
        $this->display("info");
    }

    public function trash() {
        $this->getUserList(0, 1, array());
        $this->display();
    }

    public function company_trash() {
        $this->getUserList(1, 1, array());
        $this->display("trash");
    }

    //坏账会员列表
    public function bad_member() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }

        $bad_id = $GLOBALS['db']->getAll("SELECT DISTINCT user_id FROM  " . DB_PREFIX . "user_sta WHERE  bad_count > 0");

        $flatmap = array_map("array_pop", $bad_id);
        $bad_id = implode(',', $flatmap);

        $map[DB_PREFIX . 'user.id'] = array("exp", "in (" . $bad_id . ")");

        //当前登录管理员所属会员
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_name = $adm_session['adm_name'];
        $adm_id = intval($adm_session['adm_id']);

        $is_department = M("Admin")->where("id=" . $adm_id)->getField("is_department");

        if ($is_department == 0) {
            $map[DB_PREFIX . 'user.admin_id'] = array('eq', $adm_id);
        } elseif ($is_department == 1) {
            $adm_pid = $GLOBALS['db']->getAll("SELECT id FROM  " . DB_PREFIX . "admin WHERE  pid = " . $adm_id);
            $flatmap = array_map("array_pop", $adm_pid);
            $adm_pid = implode(',', $flatmap);
            $map[DB_PREFIX . 'user.admin_id'] = array("exp", "in (" . $adm_id . "," . $adm_pid . ")");
        }


        $this->getUserList(0, 0, $map);
        $this->display("index");
    }

    //借款会员列表
    public function borrowing_member() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }

        $user_id = $GLOBALS['db']->getAll("SELECT DISTINCT user_id FROM  " . DB_PREFIX . "deal");
        $flatmap = array_map("array_pop", $user_id);
        $user_id = implode(',', $flatmap);
        $map[DB_PREFIX . 'user.id'] = array("exp", "in (" . $user_id . ")");

        //当前登录管理员所属会员
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $adm_name = $adm_session['adm_name'];
        $adm_id = intval($adm_session['adm_id']);

        $is_department = M("Admin")->where("id=" . $adm_id)->getField("is_department");


        if ($is_department == 0) {
            $map[DB_PREFIX . 'user.admin_id'] = array('eq', $adm_id);
        } elseif ($is_department == 1) {
            $adm_pid = $GLOBALS['db']->getAll("SELECT id FROM  " . DB_PREFIX . "admin WHERE  pid = " . $adm_id);
            $flatmap = array_map("array_pop", $adm_pid);
            $adm_pid = implode(',', $flatmap);
            $map[DB_PREFIX . 'user.admin_id'] = array("exp", "in (" . $adm_id . "," . $adm_pid . ")");
        }



        $this->getUserList(0, 0, $map);
        $this->display("index");
    }

    /*
     * $user_type  0普通会员 1企业会员
     */

    private function getUserList($user_type = 0, $is_delete = 0, $map) {

        $group_list = M("UserGroup")->findAll();
        $this->assign("group_list", $group_list);

        $map[DB_PREFIX . 'user.user_type'] = $user_type;
        //定义条件
        $map[DB_PREFIX . 'user.is_delete'] = $is_delete;

        if (intval($_REQUEST['group_id']) > 0) {
            $map[DB_PREFIX . 'user.group_id'] = intval($_REQUEST['group_id']);
        }

        if (trim($_REQUEST['user_name']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.user_name'] = array('eq', trim($_REQUEST['user_name']));
            else
                $map[DB_PREFIX . 'user.user_name'] = array('like', '%' . trim($_REQUEST['user_name']) . '%');
        }
        if (trim($_REQUEST['real_name']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.real_name'] = array('eq', trim($_REQUEST['real_name']));
            else
                $map[DB_PREFIX . 'user.real_name'] = array('like', '%' . trim($_REQUEST['real_name']) . '%');
        }
        if (trim($_REQUEST['email']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.email'] = array('eq', trim($_REQUEST['email']));
            else
                $map[DB_PREFIX . 'user.email'] = array('like', '%' . trim($_REQUEST['email']) . '%');
        }
        if (trim($_REQUEST['mobile']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.mobile'] = array('eq', trim($_REQUEST['mobile']));
            else
                $map[DB_PREFIX . 'user.mobile'] = array('like', '%' . trim($_REQUEST['mobile']) . '%');
        }
        if (trim($_REQUEST['pid_name']) != '') {
            $pid = M("User")->where("user_name='" . trim($_REQUEST['pid_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.pid'] = $pid;
        }
        // 处理注册来源
        if (trim($_REQUEST['search_channel']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.search_channel'] = array('eq', trim($_REQUEST['search_channel']));
            else
                $map[DB_PREFIX . 'user.search_channel'] = array('like', '%' . trim($_REQUEST['search_channel']) . '%');
        }

        // 处理销售
        if (trim($_REQUEST['admin_name']) != '') {
            $admin_id = M("Admin")->where("adm_name='" . trim($_REQUEST['admin_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.admin_id'] = $admin_id;
        }

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        if ($begin_time > 0 || $end_time > 0) {
            if ($end_time == 0) {
                $map[DB_PREFIX . 'user.create_time'] = array('egt', $begin_time);
            } else
                $map[DB_PREFIX . 'user.create_time'] = array("between", array($begin_time, $end_time));
        }
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
    }

    public function add() {
        $group_list = M("UserGroup")->findAll();
        $this->assign("group_list", $group_list);

        $cate_list = M("TopicTagCate")->findAll();
        $this->assign("cate_list", $cate_list);

        $field_list = M("UserField")->order("sort desc")->findAll();
        foreach ($field_list as $k => $v) {
            $field_list[$k]['value_scope'] = preg_split("/[ ,]/i", $v['value_scope']);
        }

        //地区列表

        $region_lv2 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where region_level = 2");  //二级地址
        $this->assign("region_lv2", $region_lv2);

        $this->assign("field_list", $field_list);
        $this->display();
    }

    public function insert() {

        B('FilterString');
        $ajax = intval($_REQUEST['ajax']);
        $data = M(MODULE_NAME)->create();

        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/add"));

        if (!check_empty($data['user_pwd'])) {
            $this->error(L("USER_PWD_EMPTY_TIP"));
        }
        if ($data['user_pwd'] != $_REQUEST['user_confirm_pwd']) {
            $this->error(L("USER_PWD_CONFIRM_ERROR"));
        }
        $res = save_user($_REQUEST);
        if ($res['status'] == 0) {
            $error_field = $res['data'];
            if ($error_field['error'] == EMPTY_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EMPTY_TIP"));
                } elseif ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EMPTY_TIP"));
                } else {
                    $this->error(sprintf(L("USER_EMPTY_ERROR"), $error_field['field_show_name']));
                }
            }
            if ($error_field['error'] == FORMAT_ERROR) {
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_FORMAT_TIP"));
                }
            }

            if ($error_field['error'] == EXIST_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_EXIST_TIP"));
                }
            }
        }
        $user_id = intval($res['user_id']);
        foreach ($_REQUEST['auth'] as $k => $v) {
            foreach ($v as $item) {
                $auth_data = array();
                $auth_data['m_name'] = $k;
                $auth_data['a_name'] = $item;
                $auth_data['user_id'] = $user_id;
                M("UserAuth")->add($auth_data);
            }
        }


        foreach ($_REQUEST['cate_id'] as $cate_id) {
            $link_data = array();
            $link_data['user_id'] = $user_id;
            $link_data['cate_id'] = $cate_id;
            M("UserCateLink")->add($link_data);
        }

        // 更新数据
        $log_info = $data['user_name'];
        save_log($log_info . L("INSERT_SUCCESS"), 1);
        $this->success(L("INSERT_SUCCESS"));
    }

    public function edit() {
        $id = intval($_REQUEST ['id']);
        $condition['is_delete'] = 0;
        $condition['id'] = $id;
        $vo = M(MODULE_NAME)->where($condition)->find();
        $device_token = M('device_push')->where(array('user_id'=>$id))->getField('device_token');
        $vo['device_token'] = $device_token ? $device_token : '未获取';
        $this->assign('vo', $vo);
        $group_list = M("UserGroup")->findAll();
        $this->assign("group_list", $group_list);

        $cate_list = M("TopicTagCate")->findAll();
        foreach ($cate_list as $k => $v) {
            $cate_list[$k]['checked'] = M("UserCateLink")->where("user_id=" . $vo['id'] . " and cate_id = " . $v['id'])->count();
        }
        $this->assign("cate_list", $cate_list);
        $field_list = M("UserField")->order("sort desc")->findAll();
        foreach ($field_list as $k => $v) {
            $field_list[$k]['value_scope'] = preg_split("/[ ,]/i", $v['value_scope']);
            $field_list[$k]['value'] = M("UserExtend")->where("user_id=" . $id . " and field_id=" . $v['id'])->getField("value");
        }
        $this->assign("field_list", $field_list);

        $rs = M("UserAuth")->where("user_id=" . $id . " and rel_id = 0")->findAll();
        foreach ($rs as $row) {
            $auth_list[$row['m_name']][$row['a_name']] = 1;
        }

        //地区列表
        $region_lv2 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where region_level = 2");  //二级地址
        foreach ($region_lv2 as $k => $v) {
            if ($v['id'] == intval($vo['province_id'])) {
                $region_lv2[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("region_lv2", $region_lv2);

        $region_lv3 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where pid = " . intval($vo['province_id']));  //三级地址
        foreach ($region_lv3 as $k => $v) {
            if ($v['id'] == intval($vo['city_id'])) {
                $region_lv3[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("region_lv3", $region_lv3);

        $n_region_lv3 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where pid = " . intval($vo['n_province_id']));  //三级地址
        foreach ($n_region_lv3 as $k => $v) {
            if ($v['id'] == intval($vo['n_city_id'])) {
                $n_region_lv3[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("n_region_lv3", $n_region_lv3);

        $this->assign("auth_list", $auth_list);
        $this->display();
    }

    public function delete() {
        //删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset($id)) {
            $deal_condition = array('user_id' => array('in', explode(',', $id)));
            if (M("Deal")->where($deal_condition)->count() > 0) {
                $this->error("删除的会员有借款记录", $ajax);
            }
            //删除验证
            if (M("DealOrder")->where(array('user_id' => array('in', explode(',', $id))))->count() > 0) {
                $this->error(l("ORDER_EXIST_DELETE_FAILED"), $ajax);
            }
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['user_name'];
            }
            if ($info)
                $info = implode(",", $info);
            $list = M(MODULE_NAME)->where($condition)->setField('is_delete', 1);
            if ($list !== false) {
                //把信息屏蔽
                M("Topic")->where("user_id in (" . $id . ")")->setField("is_effect", 0);
                M("TopicReply")->where("user_id in (" . $id . ")")->setField("is_effect", 0);
                M("Message")->where("user_id in (" . $id . ")")->setField("is_effect", 0);
                save_log($info . l("DELETE_SUCCESS"), 1);
                $this->success(l("DELETE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("DELETE_FAILED"), 0);
                $this->error(l("DELETE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function restore() {
        //删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['user_name'];
            }
            if ($info)
                $info = implode(",", $info);
            $list = M(MODULE_NAME)->where($condition)->setField('is_delete', 0);
            if ($list !== false) {
                //把信息屏蔽
                M("Topic")->where("user_id in (" . $id . ")")->setField("is_effect", 1);
                M("TopicReply")->where("user_id in (" . $id . ")")->setField("is_effect", 1);
                M("Message")->where("user_id in (" . $id . ")")->setField("is_effect", 1);
                save_log($info . l("RESTORE_SUCCESS"), 1);
                $this->success(l("RESTORE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("RESTORE_FAILED"), 0);
                $this->error(l("RESTORE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function foreverdelete() {
        //彻底删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['user_name'];
            }
            if ($info)
                $info = implode(",", $info);
            $ids = explode(',', $id);
            foreach ($ids as $uid) {
                delete_user($uid);
            }
            save_log($info . l("FOREVER_DELETE_SUCCESS"), 1);
            clear_auto_cache("consignee_info");
            $this->success(l("FOREVER_DELETE_SUCCESS"), $ajax);
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function update() {
        B('FilterString');
        $data = M(MODULE_NAME)->create();

        $log_info = M(MODULE_NAME)->where("id=" . intval($data['id']))->getField("user_name");
        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/edit", array("id" => $data['id'])));
        if (!check_empty($data['user_pwd']) && $data['user_pwd'] != $_REQUEST['user_confirm_pwd']) {
            $this->error(L("USER_PWD_CONFIRM_ERROR"));
        }

        $res = save_user($_REQUEST, 'UPDATE');
        if ($res['status'] == 0) {
            $error_field = $res['data'];
            if ($error_field['error'] == EMPTY_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EMPTY_TIP"));
                } elseif ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EMPTY_TIP"));
                } else {
                    $this->error(sprintf(L("USER_EMPTY_ERROR"), $error_field['field_show_name']));
                }
            }
            if ($error_field['error'] == NOT_EDIT_ERROR) {
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("用户手机不允许更改"));
                }
            }
            if ($error_field['error'] == FORMAT_ERROR) {
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_FORMAT_TIP"));
                }
            }

            if ($error_field['error'] == EXIST_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_EXIST_TIP"));
                }
            }
        }

        //更新权限

        M("UserAuth")->where("user_id=" . $data['id'] . " and rel_id = 0")->delete();
        foreach ($_REQUEST['auth'] as $k => $v) {
            foreach ($v as $item) {
                $auth_data = array();
                $auth_data['m_name'] = $k;
                $auth_data['a_name'] = $item;
                $auth_data['user_id'] = $data['id'];
                M("UserAuth")->add($auth_data);
            }
        }
        //开始更新is_effect状态
        M("User")->where("id=" . intval($_REQUEST['id']))->setField("is_effect", intval($_REQUEST['is_effect']));
        $user_id = intval($_REQUEST['id']);
        M("UserCateLink")->where("user_id=" . $user_id)->delete();
        foreach ($_REQUEST['cate_id'] as $cate_id) {
            $link_data = array();
            $link_data['user_id'] = $user_id;
            $link_data['cate_id'] = $cate_id;
            M("UserCateLink")->add($link_data);
        }
        save_log($log_info . L("UPDATE_SUCCESS"), 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    public function set_effect() {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = M(MODULE_NAME)->where("id=" . $id)->getField("user_name");
        $c_is_effect = M(MODULE_NAME)->where("id=" . $id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=" . $id)->setField("is_effect", $n_is_effect);
        save_log($info . l("SET_EFFECT_" . $n_is_effect), 1);
        $this->ajaxReturn($n_is_effect, l("SET_EFFECT_" . $n_is_effect), 1);
    }

    //资金同步
    public function cash_synchro() {
        $user_id = intval($_REQUEST['user_id']);
        $ajax = intval($_REQUEST['ajax']);
        if (!$user_id) {
            $this->ajaxReturn('', '用户不存在', 2);
            die();
        }
        //实现用户平台账户与富友账户余额同步
        require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
        $fuyou_payment = new Fuyou_payment();

        //查询用户信息
        $user_info = M("User")->where(array('is_effect' => 1, 'is_delete' => 0))->getById($user_id);
        $fuyou_user_data = $fuyou_payment->check_balance($user_info);
        if ($fuyou_user_data['status'] == 1) {
            $money = ($fuyou_user_data['ca_balance']); //富友账户可用余额
            $lock_money = ($fuyou_user_data['cf_balance']); //富友账户冻结余额
            $account_total = ($money + $lock_money); //富友账户总余额
            //金享财行平台用户账户总余额
            $jxch_user_account = ($user_info['money'] + $user_info['lock_money']);
            if ($jxch_user_account != $account_total || $user_info['money'] != $money || $user_info['lock_money'] != $lock_money) {
                //资金不一致 以富友数据为准进行同步
                $data['money'] = $money;
                $data['lock_money'] = $lock_money;
                $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, "UPDATE", "id=" . $user_info['id']);
                $str = '';
                $str .= "[用户" . $user_info['user_name'] . "的金享财行账面总余额为" . $jxch_user_account . "对应富友账面总余额为" . $account_total . "]";
                $str .= "[其中金享财行账面可用余额为" . $user_info['money'] . "，对应富友账面可用余额为" . $money . "]";
                $str .= "[金享财行账面冻结余额为" . $user_info['lock_money'] . "，对应富友账面冻结金额为" . $lock_money . "]";
                $str .= "[资金同步以富友对应账户余额数据为准进行同步][" . date('Y-m-d H:i:s') . "]";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_sigle_moneySynchronizationAction.log', "POST:[" . json_encode($fuyou_user_data) . "];return:[" . $str . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                $this->ajaxReturn('', '资金同步成功', 1);
                die();
            } else {
                //资金一致 资金同步日志
                $str = '';
                $str .= "[用户" . $user_info['user_name'] . "的金享财行账户和富友账户资金一致。账面总余额均为" . $account_total . "，账面可用余额均为" . $money . "，账面冻结余额为" . $lock_money . "]";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_sigle_moneySynchronizationAction.log', "POST:[" . json_encode($fuyou_user_data) . "];return:[" . $str . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                $this->ajaxReturn($fuyou_user_data, '资金一致，无需同步', 2);
                die();
            }
        } elseif ($fuyou_user_data['status'] == 2) {
            //服务器忙 响应失败，请稍后重试！
            $str = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_moneySynchronizationAction.log', "POST:[" . json_encode($fuyou_user_data) . "];return:[" . $str . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $this->ajaxReturn('', '服务器忙 响应失败，请稍后重试！', 3);
            die();
        }
    }

    //银行卡同步
    public function bank_synchro() {
        $user_id = intval($_REQUEST['user_id']);
        $ajax = intval($_REQUEST['ajax']);
        if (!$user_id) {
            $this->ajaxReturn('', '用户不存在', 2);
            die();
        }
        $result = $this->_mod->bank_synchro($user_id);
        $this->ajaxReturn('', $result['info'], $result['status']);
    }

    //手机号码同步
    public function mobile_synchro() {
        $user_id = intval($_REQUEST['user_id']);
        $ajax = intval($_REQUEST['ajax']);
        if (!$user_id) {
            $this->ajaxReturn('', '用户不存在', 2);
            die();
        }
        $result = $this->_mod->mobile_synchro($user_id);
        $this->ajaxReturn('', $result['info'], $result['status']);
    }
    
    public function set_black() {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = M(MODULE_NAME)->where("id=" . $id)->getField("user_name");
        $c_is_effect = M(MODULE_NAME)->where("id=" . $id)->getField("is_black");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=" . $id)->setField("is_black", $n_is_effect);
        save_log($info . l("SET_EFFECT_" . $n_is_effect), 1);
        $this->ajaxReturn($n_is_effect, l("SET_EFFECT_" . $n_is_effect), 1);
    }

    public function account() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $this->assign("user_info", $user_info);
        $this->display();
    }

    public function modify_account() {
        $user_id = intval($_REQUEST['id']);
        $money = 0;
        $score = intval($_REQUEST['score']);
        $point = intval($_REQUEST['point']);
        $quota = floatval($_REQUEST['quota']);
        $lock_money = floatval($_REQUEST['lock_money']);

        if ($lock_money != 0) {
            if ($lock_money > 0 && $lock_money > D("User")->where('id=' . $user_id)->getField("money")) {
                $this->error("输入的冻结资金不得超过账户总余额");
            }

            if ($lock_money < 0 && $lock_money < -D("User")->where('id=' . $user_id)->getField("lock_money")) {
                $this->error("输入的解冻资金不得大于已冻结的资金");
            }

            $money -=$lock_money;
        }

        $msg = trim($_REQUEST['msg']) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($_REQUEST['msg']);
        modify_account(array('money' => $money, 'score' => $score, 'point' => $point, 'quota' => $quota, 'lock_money' => $lock_money), $user_id, $msg, 13);
        if (floatval($_REQUEST['quota']) != 0) {
            //$content = "您好，".app_conf("SHOP_TITLE")."审核部门经过综合评估您的信用资料及网站还款记录，将您的信用额度调整为：".D("User")->where("id=".$user_id)->getField('quota')."元";

            $group_arr = array(0, $user_id);
            sort($group_arr);
            $group_arr[] = 4;

            $sh_notice['shop_title'] = app_conf("SHOP_TITLE");         // 网站名称
            $sh_notice['quota'] = D("User")->where("id=" . $user_id)->getField('quota');    // 信用额度
            $GLOBALS['tmpl']->assign("sh_notice", $sh_notice);
            $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_INS_QUOTA_TZ'", false);
            $sh_content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_sz_failed_content['content']);

            $msg_data['content'] = $sh_content;
            $msg_data['to_user_id'] = $user_id;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['type'] = 0;
            $msg_data['group_key'] = implode("_", $group_arr);
            $msg_data['is_notice'] = 4;

            $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_box", $msg_data);
            $id = $GLOBALS['db']->insert_id();
            $GLOBALS['db']->query("update " . DB_PREFIX . "msg_box set group_key = '" . $msg_data['group_key'] . "_" . $id . "' where id = " . $id);
        }
        save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    public function account_detail() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $log_begin_time = trim($_REQUEST['log_begin_time']) == '' ? 0 : to_timespan($_REQUEST['log_begin_time']);
        $log_end_time = trim($_REQUEST['log_end_time']) == '' ? 0 : to_timespan($_REQUEST['log_end_time']);
        $t = strim($_REQUEST['t']) == "" ? "money" : strim($_REQUEST['t']);

        $map['user_id'] = $user_id;
        if (trim($_REQUEST['log_info']) != '') {
            if ($t == "" || $t == "quota") {
                $map['log_info'] = array('like', '%' . trim($_REQUEST['log_info']) . '%');
            } else {
                $map['memo'] = array('like', '%' . trim($_REQUEST['log_info']) . '%');
            }
        }

        if ($log_end_time == 0) {
            if ($t == "" || $t == "quota") {
                $map['log_time'] = array('gt', $log_begin_time);
            } else {
                $map['create_time'] = array('gt', $log_begin_time);
            }
        } elseif ($log_begin_time > 0 || $log_end_time > 0) {
            if ($t == "" || $t == "quota") {
                $map['log_time'] = array('between', array($log_begin_time, $log_end_time));
            } else {
                $map['create_time'] = array('between', array($log_begin_time, $log_end_time));
            }
        }

        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

        if ($t == "money") { //资金日志
            $model = M("UserMoneyLog");
        } elseif ($t == "point") {
            $model = M("UserPointLog");
            //信用积分日志
        } elseif ($t == "score") {
            $model = M("UserScoreLog");
            //积分日志
        } elseif ($t == "freeze") {
            $model = M("UserLockMoneyLog");
        } elseif ($t == "nmc_amount") {
            $model = M("UserMoneyLog");
            $map['type'] = array('in', array(22, 28, 29));
            //不可提现资金
        } else {
            //$map['quota'] = array('gt',0);
            $model = M("UserLog");
        }

        if (!empty($model)) {
            $this->_list($model, $map);
        }


        $this->assign("t", $t);
        $this->assign("user_id", $user_id);
        $this->assign("user_info", $user_info);
        $this->display();
        return;
    }

    //收货地址
    public function address() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);

        $map['user_id'] = $user_id;

        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

        $model = M("UserAddress");

        if (!empty($model)) {
            $this->_list($model, $map);
        }

        $this->assign("user_info", $user_info);
        $this->display();
        return;
    }

    public function address_add() {
        $this->display("address_add");
    }

    public function address_insert() {
        B('FilterString');
        $data = M("UserAddress")->create();
        //开始验证有效性
        //$this->assign("jumpUrl",u("UserAddress"."/add"));
        if ($data['name'] == "") {
            $this->error("收货姓名不能为空");
        }
        if ($data['address'] == "") {
            $this->error("收货地址不能为空");
        }
        if ($data['phone'] == "") {
            $this->error("收货电话不能为空");
        }
        // 更新数据

        $user_name = strim($_REQUEST['user_name']);
        $user_id = M("User")->where("user_name='" . $user_name . "'")->getField("id");

        if ($user_id == 0) {
            $this->error("该用户不存在");
        }

        $data['user_id'] = intval($user_id);

        $list = M("UserAddress")->add($data);
        if (false !== $list) {
            //成功提示
            if ($data['is_default'] == 1) {
                $GLOBALS['db']->query("update " . DB_PREFIX . "user_address set is_default = 0 where id != " . $list);
            }
            save_log($log_info . L("INSERT_SUCCESS"), 1);
            $this->success(L("INSERT_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info . L("INSERT_FAILED"), 0);
            $this->error(L("INSERT_FAILED"));
        }
    }

    public function address_edit() {
        $address_id = intval($_REQUEST['address_id']);
        $condition['id'] = $address_id;
        $vo = M("UserAddress")->where($condition)->find();
        $this->assign('vo', $vo);
        $this->display("address_edit");
    }

    public function address_update() {
        B('FilterString');
        $data = M("UserAddress")->create();
        // 更新数据
        $list = M("UserAddress")->save($data);

        if ($data['is_default'] == 1) {
            $GLOBALS['db']->query("update " . DB_PREFIX . "user_address set is_default = 0 where id != " . $data['id']);
        }
        if (false !== $list) {
            //成功提示
            save_log($data['name'] . L("UPDATE_SUCCESS"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else {
            //错误提示
            save_log($data['name'] . L("UPDATE_FAILED"), 0);
            $this->error(L("UPDATE_FAILED"), 0, $data['name'] . L("UPDATE_FAILED"));
        }
    }

    public function address_del() {
        //删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset($id)) {

            $condition = array();
            $condition['id'] = array('in', explode(',', $id));
            /*
              $rel_data = M("UserAddress")->where($condition)->findAll();
              foreach($rel_data as $k=>$v){
              $info[] =$v['name'];
              }
              if($info) $info = implode(",",$info);
             */
            $list = M("UserAddress")->where($condition)->delete();
            if ($list !== false) {
                save_log($info . l("DELETE_SUCCESS"), 1);
                $this->success(l("DELETE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("DELETE_FAILED"), 0);
                $this->error(l("DELETE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function passed() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);

        $field_array = array(
            "credit_identificationscanning" => "idcardpassed",
            "credit_contact" => "workpassed",
            "credit_credit" => "creditpassed",
            "credit_incomeduty" => "incomepassed",
            "credit_house" => "housepassed",
            "credit_car" => "carpassed",
            "credit_marriage" => "marrypassed",
            "credit_titles" => "skillpassed",
            "credit_videoauth" => "videopassed",
            "credit_mobilereceipt" => "mobiletruepassed",
            "credit_residence" => "residencepassed",
            "credit_seal" => "sealpassed",
        );


        $this->assign("user_info", $user_info);



        $t_credit_file = M("UserCreditFile")->where("user_id=" . $user_id)->findAll();
        foreach ($t_credit_file as $k => $v) {
            $file_list = array();
            if ($v['file'])
                $file_list = unserialize($v['file']);

            if (is_array($file_list))
                $v['file_list'] = $file_list;

            $credit_file[$v['type']] = $v;
        }


        $loantype = intval($_REQUEST['loantype']);
        $needs_credits = array();
        if ($loantype > 0) {
            $loantypeinfo = M("DealLoanType")->getById($loantype);
            if ($loantypeinfo['credits'] != "") {
                $needs_credits = unserialize($loantypeinfo['credits']);
            }
        }

        $credit_type = load_auto_cache("credit_type");
        $credit_list = array();
        foreach ($credit_type['list'] as $k => $v) {

            if ($v['must'] == 1 || $loantype == 0 || (count($needs_credits) > 0 && in_array($v['type'], $needs_credits))) {
                $credit_list[$v['type']] = $credit_type['list'][$v['type']];
                $credit_list[$v['type']]['credit'] = $credit_file[$v['type']];

                //User表里面的数据
                if ($user_info[$field_array[$v['type']]]) {
                    $credit_list[$v['type']]['credit']['passed'] = $user_info[$field_array[$v['type']]];
                }
            }
        }


        $this->assign("credits", $credit_list);

        $this->display();
        return;
    }

    public function agencies_passed() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);

        $field_array = array(
            "credit_identificationscanning" => "idcardpassed"
        );


        $this->assign("user_info", $user_info);

        $t_credit_file = M("UserCreditFile")->where("user_id=" . $user_id)->findAll();

        foreach ($t_credit_file as $k => $v) {
            $file_list = array();

            if ($v['file']) {
                $file_list = unserialize($v['file']);
            }
            if (is_array($file_list))
                $v['file_list'] = $file_list;

            $credit_file[$v['type']] = $v;
        }


        $loantype = intval($_REQUEST['loantype']);
        $needs_credits = array();
        if ($loantype > 0) {
            $loantypeinfo = M("DealLoanType")->getById($loantype);
            if ($loantypeinfo['credits'] != "") {
                $needs_credits = unserialize($loantypeinfo['credits']);
            }
        }
        $credit_type = load_auto_cache("credit_type");
        $credit_list = array();

        foreach ($credit_type['list'] as $k => $v) {
            if ($v["type"] == "credit_identificationscanning") {
                if ($v['must'] == 1 || $loantype == 0 || (count($needs_credits) > 0 && in_array($v['type'], $needs_credits))) {
                    $credit_list[$v['type']] = $credit_type['list'][$v['type']];
                    $credit_list[$v['type']]['credit'] = $credit_file[$v['type']];

                    //User表里面的数据
                    if ($user_info[$field_array[$v['type']]]) {
                        $credit_list[$v['type']]['credit']['passed'] = $user_info[$field_array[$v['type']]];
                    }
                }
            }
        }


        $this->assign("credits", $credit_list);

        $this->display();
        return;
    }

    public function export_csv($page = 1) {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        //定义条件
        $map[DB_PREFIX . 'user.is_delete'] = 0;

        if (intval($_REQUEST['group_id']) > 0) {
            $map[DB_PREFIX . 'user.group_id'] = intval($_REQUEST['group_id']);
        }

        if (trim($_REQUEST['user_name']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.user_name'] = array('eq', trim($_REQUEST['user_name']));
            else
                $map[DB_PREFIX . 'user.user_name'] = array('like', '%' . trim($_REQUEST['user_name']) . '%');
        }
        if (trim($_REQUEST['email']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.email'] = array('eq', trim($_REQUEST['email']));
            else
                $map[DB_PREFIX . 'user.email'] = array('like', '%' . trim($_REQUEST['email']) . '%');
        }
        if (trim($_REQUEST['mobile']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.mobile'] = array('eq', trim($_REQUEST['mobile']));
            else
                $map[DB_PREFIX . 'user.mobile'] = array('like', '%' . trim($_REQUEST['mobile']) . '%');
        }
        if (trim($_REQUEST['pid_name']) != '') {
            $pid = M("User")->where("user_name='" . trim($_REQUEST['pid_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.pid'] = $pid;
        }

        // 处理注册来源
        if (trim($_REQUEST['search_channel']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.search_channel'] = array('eq', trim($_REQUEST['search_channel']));
            else
                $map[DB_PREFIX . 'user.search_channel'] = array('like', '%' . trim($_REQUEST['search_channel']) . '%');
        }

        // 处理销售
        if (trim($_REQUEST['admin_name']) != '') {
            $admin_id = M("Admin")->where("adm_name='" . trim($_REQUEST['admin_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.admin_id'] = $admin_id;
        }

        $begin_time = trim($_REQUEST['begin_time']) == '' ? 0 : to_timespan($_REQUEST['begin_time']);
        $end_time = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        if ($begin_time > 0 || $end_time > 0) {
            if ($end_time == 0) {
                $map[DB_PREFIX . 'user.create_time'] = array('egt', $begin_time);
            } else
                $map[DB_PREFIX . 'user.create_time'] = array("between", array($begin_time, $end_time));
        }

        $map[DB_PREFIX . 'user.user_type'] = intval($_REQUEST['user_type']);

        $list = M(MODULE_NAME)
                        ->where($map)
                        ->join(DB_PREFIX . 'user_level ON ' . DB_PREFIX . 'user.level_id = ' . DB_PREFIX . 'user_level.id')
                        ->field(DB_PREFIX . 'user.*,' . DB_PREFIX . 'user_level.name')
                        ->limit($limit)->findAll();


        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv'), $page + 1);

            $user_value = array('id' => '""', 'user_name' => '""', 'create_time' => '""', 'money' => '""', 'lock_money' => '""', 'email' => '""', 'mobile' => '""', 'idno' => '""', 'level_id' => '""', 'deal' => '""', 'payment' => '""', 'success_num' => '""', 'error_num' => '""', 'search_channel' => '""', 'pid' => '""', 'admin_id' => '""');
            if ($page == 1)
                $content = iconv("utf-8", "gbk", "编号,用户名,注册时间,可用余额,冻结金额,电子邮箱,手机号,身份证,会员等级,累积投标金额,累积充值金额,充值成功次数,充值失败次数,注册来源,推荐人,销售");


            //开始获取扩展字段
            $extend_fields = M("UserField")->order("sort desc")->findAll();
            foreach ($extend_fields as $k => $v) {
                $user_value[$v['field_name']] = '""';
                if ($page == 1)
                    $content = $content . "," . iconv('utf-8', 'gbk', $v['field_show_name']);
            }
            if ($page == 1)
                $content = $content . "\n";

            foreach ($list as $k => $v) {
                //累计充值
                $where = array('user_id' => $v["id"], 'is_paid' => 1);
                $money = M("payment_notice")->where($where)->getField('sum(money)');
                if ($money) {
                    $v['payment'] = $money;
                } else {
                    $v['payment'] = 0;
                }
                //累计投资
                $where = array('user_id' => $v['id']);
                $money = M("deal_load")->where($where)->getField('sum(money)');
                if ($money) {
                    $v['deal'] = $money;
                } else {
                    $v['deal'] = 0;
                }
                $v['error_num'] = M("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 0))->count();
                $v['success_num'] = M("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 1))->count();

                $user_value = array();
                $user_value['id'] = iconv('utf-8', 'gbk', '"' . $v['id'] . '"');
                $user_value['user_name'] = iconv('utf-8', 'gbk', '"' . $v['user_name'] . '"');
                $user_value['create_time'] = iconv('utf-8', 'gbk', '"' . date('Y-m-d H:i:s', $v['create_time']) . '"');
                $user_value['money'] = iconv('utf-8', 'gbk', '"' . number_format($v['money'], 2) . '"');
                $user_value['lock_money'] = iconv('utf-8', 'gbk', '"' . number_format($v['lock_money'], 2) . '"');
                $user_value['email'] = iconv('utf-8', 'gbk', '"' . $v['email'] . '"');
                $user_value['mobile'] = ' ' . iconv('utf-8', 'gbk', '"' . $v['mobile'] . '"');
                $user_value['idno'] = ' ' . iconv('utf-8', 'gbk', '"' . $v['idno'] . '"');
                $user_value['level_id'] = iconv('utf-8', 'gbk', '"' . $v['name'] . '"');
                $user_value['deal'] = iconv('utf-8', 'gbk', '"' . format_price($v['deal']) . '"');
                $user_value['payment'] = iconv('utf-8', 'gbk', '"' . format_price($v['payment']) . '"');
                $user_value['success_num'] = iconv('utf-8', 'gbk', '"' . $v['success_num'] . '"');
                $user_value['error_num'] = iconv('utf-8', 'gbk', '"' . $v['error_num'] . '"');
                $user_value['search_channel'] = iconv('utf-8', 'gbk', '"' . $v['search_channel'] . '"');
                $user_value['pid'] = iconv('utf-8', 'gbk', '"' . M("User")->where("id=" . $v['pid'])->getField("user_name") . '"');
                $user_value['admin_id'] = iconv('utf-8', 'gbk', '"' . get_admin_name($v['admin_id']) . '"');


                //取出扩展字段的值
                $extend_fieldsval = M("UserExtend")->where("user_id=" . $v['id'])->findAll();
                foreach ($extend_fields as $kk => $vv) {
                    foreach ($extend_fieldsval as $kkk => $vvv) {
                        if ($vv['id'] == $vvv['field_id']) {
                            $user_value[$vv['field_name']] = iconv('utf-8', 'gbk', '"' . $vvv['value'] . '"');
                            break;
                        }
                    }
                }

                $content .= implode(",", $user_value) . "\n";
            }


            header("Content-Disposition: attachment; filename=user_list.csv");
            echo $content;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    function lock_money() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $this->assign("user_info", $user_info);
        $this->display();
    }

    function check_merchant_name() {
        $merchant_name = addslashes(trim($_REQUEST['merchant_name']));
        $ajax = intval($_REQUEST['ajax']);
        $result = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "supplier_account where account_name = '" . $merchant_name . "'");
        if (intval($result) == 0)
            $this->error(l("MERCHANT_NAME_NOT_EXIST"), $ajax);
        else
            $this->success("", $ajax);
    }

    function info_down() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);

        $this->assign("user_info", $user_info);

        $this->display();
    }

    function modify_info_down() {
        if (intval($_REQUEST['id']) == 0) {
            $this->error("会员不存在！");
            exit();
        }
        $is_delete = intval($_REQUEST['is_delete']);
        $file = $_FILES['file']['name'];

        if ($file == "" && $is_delete == 0) {
            $this->error("请选择要上传的文件！");
            exit();
        }

        $file = pathinfo($file);

        if (
                strpos($file['extension'], "asp") !== false ||
                strpos($file['extension'], "aspx") !== false ||
                strpos($file['extension'], "php") !== false ||
                strpos($file['extension'], "jsp") !== false
        ) {
            $this->error("非法文件格式！");
            exit();
        }



        if ($is_delete == 1) {
            $data['info_down'] = "";
        }

        if ($file['error'] == 0 && $_FILES['file']['name'] != "") {
            if (!file_exists(APP_ROOT_PATH . "/public/info_down"))
                @mkdir(APP_ROOT_PATH . "/public/info_down", 0777);

            $time = to_date(TIME_UTC, "Ym");
            if (!file_exists(APP_ROOT_PATH . "/public/info_down/" . $time))
                @mkdir(APP_ROOT_PATH . "/public/info_down/" . $time, 0777);

            $file_name = md5(TIME_UTC . $_REQUEST['id']) . "." . $file['extension'];
            //@file_put_contents(APP_ROOT_PATH."/public/info_down/".$time."/".$file_name,$_FILES['file']['tmp_name']);
            move_uploaded_file($_FILES['file']['tmp_name'], APP_ROOT_PATH . "/public/info_down/" . $time . "/" . $file_name);

            if (!file_exists(APP_ROOT_PATH . "/public/info_down/" . $time . "/" . $file_name)) {
                $this->error("上传资料失败！");
            }

            $data['info_down'] = "./public/info_down/" . $time . "/" . $file_name;
        }


        if ($GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, "UPDATE", "id=" . $_REQUEST['id'])) {
            if ($_REQUEST['old_info_down']) {
                @unlink(APP_ROOT_PATH . $_REQUEST['old_info_down']);
            }
            if ($is_delete == 1) {
                $this->success("操作成功！");
            } else {
                $this->success("上传资料成功！");
            }
        } else {
            $this->error("上传资料失败！");
        }
    }

    function view_info() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $old_imgdata_str = unserialize($user_info['view_info']);
        $this->assign("user_info", $user_info);
        $this->assign("old_imgdata_str", $old_imgdata_str);
        $this->display();
    }

    function private_info() {
        $uid = $_REQUEST['user_id'];
        $list = M('user')->field(true)->where("id = {$uid}")->select();
        $this->assign('mobile', $list[0]['mobile']);
        $this->assign('email', $list[0]['email']);
        $this->assign('real_name', $list[0]['real_name']);
        $this->assign('idno', $list[0]['idno']);
        $this->display();
    }

    function gains_info(){
        $user_id = $_REQUEST['user_id'];
        $sql = "SELECT sum(dlr.repay_money) as h_repay_money,sum(dlr.self_money - dl.coupon_cash) as h_self_money,sum(dlr.pure_interests) as h_pure_interests,sum(dlr.coupon_interests) as h_coupon_interests,sum(dlr.active_interest_money) as h_act_interests,sum(dl.coupon_cash) as h_coupon_cash,sum(dlr.interest_money) as h_all_interests FROM " . DB_PREFIX . "deal_load_repay dlr left join " . DB_PREFIX . "deal_load dl on dlr.load_id = dl.id where dlr.user_id={$user_id} and   dl.user_id={$user_id} and dlr.has_repay = 1 ";
        $has_repay_info = D()->query($sql);
        $sql = "SELECT sum(dlr.repay_money) as h_repay_money,sum(dlr.self_money - dl.coupon_cash) as h_self_money,sum(dlr.pure_interests) as h_pure_interests,sum(dlr.coupon_interests) as h_coupon_interests,sum(dlr.active_interest_money) as h_act_interests,sum(dl.coupon_cash) as h_coupon_cash,sum(dlr.interest_money) as h_all_interests FROM " . DB_PREFIX . "deal_load_repay dlr left join " . DB_PREFIX . "deal_load dl on dlr.load_id = dl.id where dlr.user_id={$user_id} and   dl.user_id={$user_id} and dlr.has_repay = 0 ";
        $need_repay_info = D()->query($sql);
        $this->assign("has_repay_info",$has_repay_info['0']);
        $this->assign("need_repay_info",$need_repay_info['0']);
        $this->display();
    }

    function modify_view_info() {

        if (intval($_REQUEST['id']) == 0) {
            $this->error("会员不存在！");
            exit();
        }

        $view_down_data = array();
        foreach ($_FILES['img_data']['name'] as $k => $v) {
            $file = pathinfo($v);

            if ($file['error'] == 0) {
                if (!file_exists(APP_ROOT_PATH . "/public/view_info"))
                    @mkdir(APP_ROOT_PATH . "/public/view_info", 0777);

                $time = to_date(TIME_UTC, "Ym");
                if (!file_exists(APP_ROOT_PATH . "/public/view_info/" . $time))
                    @mkdir(APP_ROOT_PATH . "/public/view_info/" . $time, 0777);

                $file_name = md5(TIME_UTC . $_REQUEST['id'] . $v . $k) . "." . $file['extension'];

                move_uploaded_file($_FILES['img_data']['tmp_name'][$k], APP_ROOT_PATH . "/public/view_info/" . $time . "/" . $file_name);

                if (file_exists(APP_ROOT_PATH . "/public/view_info/" . $time . "/" . $file_name)) {
                    $view_down_data[$k]['img'] = "./public/view_info/" . $time . "/" . $file_name;
                    $view_down_data[$k]['name'] = strim($_REQUEST['file_name'][$k]);
                }
            }
        }

        $new_view_info_arr = array();
        $old_view_info = M("User")->where("id=" . intval($_REQUEST['id']))->getField("view_info");
        if ($old_view_info != "") {
            $old_view_info_arr = unserialize($old_view_info);

            foreach ($old_view_info_arr as $k => $v) {
                $new_view_info_arr[$k] = $v;
            }
        }

        foreach ($view_down_data as $k => $v) {
            $new_view_info_arr[] = $v;
        }


        $data['view_info'] = serialize($new_view_info_arr);


        if ($GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, "UPDATE", "id=" . $_REQUEST['id'])) {
            $this->success("上传资料成功！");
        } else {
            $this->error("上传资料失败！");
        }
    }

    function view_info_del_img() {
        if (intval($_REQUEST['id']) == 0) {
            $this->error("会员不存在！");
            exit();
        }

        if (strim($_REQUEST['src']) == "") {
            $this->error("删除的文件不存在！");
            exit();
        }

        $old_view_info = M("User")->where("id=" . intval($_REQUEST['id']))->getField("view_info");
        if ($old_view_info != "") {
            $old_view_info_arr = unserialize($old_view_info);
            foreach ($old_view_info_arr as $k => $v) {
                if ($v['img'] == strim($_REQUEST['src'])) {
                    @unlink(APP_ROOT_PATH . $v['img']);
                    unset($old_view_info_arr[$k]);
                }
            }
        }
        $data['view_info'] = serialize($old_view_info_arr);

        if ($GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, "UPDATE", "id=" . $_REQUEST['id'])) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    public function company_manage() {
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = "desc";
        }
        if (isset($_REQUEST ['_order'])) {
            $sorder = $_REQUEST ['_order'];
        } else {
            $sorder = "user_id";
        }


        switch ($sorder) {
            case "user_name":
                $order = "u." . $sorder;
                break;
            default :
                $order = "c." . $sorder;
                break;
        }

        $extWhere = " 1=1 ";

        if (trim($_REQUEST['user_name']) != '') {
            $extWhere .= " AND u.user_name like '%" . strim($_REQUEST['user_name']) . "%' ";
        }

        $id = intval($_REQUEST['id']);
        if ($id > 0) {
            $extWhere = " and c.user_id = $id ";
            $_REQUEST['user_name'] = M("User")->where("id=" . $id)->getField("user_name");
        }


        $rs_count = $GLOBALS['db']->getOne("select count(DISTINCT c.user_id) from " . DB_PREFIX . "user_company c LEFT JOIN " . DB_PREFIX . "user u ON u.id=c.user_id where $extWhere");
        if ($rs_count > 0) {
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($rs_count, $listRows);
            $page = $p->show();

            $list = $GLOBALS['db']->getAll("select c.*,u.user_name from " . DB_PREFIX . "user_company c LEFT JOIN " . DB_PREFIX . "user u ON u.id=c.user_id where $extWhere ORDER BY $order $sort LIMIT " . $p->firstRow . ',' . $p->listRows);

            $this->assign("page", $page);
        }

        $this->assign("list", $list);
        $this->display();
    }

    public function company() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $this->assign("user_info", $user_info);
        $company = M("UserCompany")->where("user_id=" . $user_id)->find();
        $this->assign("company", $company);


        $this->display();
    }

    public function modify_company() {
        $data['user_id'] = intval($_REQUEST['id']);
        $data['company_name'] = trim($_REQUEST['company_name']);
        $data['contact'] = trim($_REQUEST['contact']);
        $data['officetype'] = trim($_REQUEST['officetype']);
        $data['officedomain'] = trim($_REQUEST['officedomain']);
        $data['officecale'] = trim($_REQUEST['officecale']);
        $data['register_capital'] = trim($_REQUEST['register_capital']);
        $data['asset_value'] = trim($_REQUEST['asset_value']);
        $data['officeaddress'] = trim($_REQUEST['officeaddress']);
        $data['description'] = trim($_REQUEST['description']);
        $data['enterpriseName'] = trim($_REQUEST['enterpriseName']);
        ;
        $data['bankLicense'] = trim($_REQUEST['bankLicense']);
        ;
        $data['orgNo'] = trim($_REQUEST['orgNo']);
        ;
        $data['businessLicense'] = trim($_REQUEST['businessLicense']);
        ;
        $data['taxNo'] = trim($_REQUEST['taxNo']);
        ;


        if ($GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "user_company WHERE user_id=" . $data['user_id']) == 0) {
            //添加
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_company", $data, "INSERT");
        } else {
            //编辑
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_company", $data, "UPDATE", "user_id=" . $data['user_id']);
        }
        if ($GLOBALS['db']->affected_rows() > 0) {
            $user_info_re = array();
            $user_info_re['enterpriseName'] = $data['enterpriseName'];
            $user_info_re['bankLicense'] = $data['bankLicense'];
            $user_info_re['orgNo'] = $data['orgNo'];
            $user_info_re['businessLicense'] = $data['businessLicense'];
            $user_info_re['taxNo'] = $data['enterpriseName'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $user_info_re, "UPDATE", "id=" . intval($data['user_id']));
        }
        save_log("编辑" . $data['user_id'] . "公司信息", 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    public function work_manage() {
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = "desc";
        }
        if (isset($_REQUEST ['_order'])) {
            $sorder = $_REQUEST ['_order'];
        } else {
            $sorder = "user_id";
        }


        switch ($sorder) {
            case "user_name":
                $order = "u." . $sorder;
                break;
            default :
                $order = "c." . $sorder;
                break;
        }

        $extWhere = " 1=1 ";

        if (trim($_REQUEST['user_name']) != '') {
            $extWhere .= " AND u.user_name like '%" . strim($_REQUEST['user_name']) . "%' ";
        }

        $id = intval($_REQUEST['id']);
        if ($id > 0) {
            $extWhere = " and c.user_id = $id ";
            $_REQUEST['user_name'] = M("User")->where("id=" . $id)->getField("user_name");
        }


        $rs_count = $GLOBALS['db']->getOne("select count(DISTINCT c.user_id) from " . DB_PREFIX . "user_work c LEFT JOIN " . DB_PREFIX . "user u ON u.id=c.user_id where $extWhere");
        if ($rs_count > 0) {
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($rs_count, $listRows);
            $page = $p->show();

            $list = $GLOBALS['db']->getAll("select c.*,u.user_name from " . DB_PREFIX . "user_work c LEFT JOIN " . DB_PREFIX . "user u ON u.id=c.user_id where $extWhere ORDER BY $order $sort LIMIT " . $p->firstRow . ',' . $p->listRows);

            $this->assign("page", $page);
        }

        $this->assign("list", $list);
        $this->display();
    }

    public function work() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $this->assign("user_info", $user_info);
        $work_info = M("UserWork")->where("user_id=" . $user_id)->find();
        $this->assign("work_info", $work_info);

        //地区列表
        $region_lv2 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where region_level = 2");  //二级地址
        if ($work_info) {
            foreach ($region_lv2 as $k => $v) {
                if ($v['id'] == intval($work_info['province_id'])) {
                    $region_lv2[$k]['selected'] = 1;
                    break;
                }
            }
        }
        $this->assign("region_lv2", $region_lv2);

        if ($work_info) {
            $region_lv3 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where pid = " . intval($work_info['province_id']));  //三级地址

            foreach ($region_lv3 as $k => $v) {
                if ($v['id'] == intval($work_info['city_id'])) {
                    $region_lv3[$k]['selected'] = 1;
                    break;
                }
            }

            $this->assign("region_lv3", $region_lv3);
        }
        $this->display();
    }

    public function modify_work() {
        $data['user_id'] = intval($_REQUEST['id']);
        $data['office'] = trim($_REQUEST['office']);
        $data['jobtype'] = trim($_REQUEST['jobtype']);
        $data['province_id'] = intval($_REQUEST['province_id']);
        $data['city_id'] = intval($_REQUEST['city_id']);
        $data['officetype'] = trim($_REQUEST['officetype']);
        $data['officedomain'] = trim($_REQUEST['officedomain']);
        $data['officecale'] = trim($_REQUEST['officecale']);
        $data['position'] = trim($_REQUEST['position']);
        $data['salary'] = trim($_REQUEST['salary']);
        $data['workyears'] = trim($_REQUEST['workyears']);
        $data['workphone'] = trim($_REQUEST['workphone']);
        $data['workemail'] = trim($_REQUEST['workemail']);
        $data['officeaddress'] = trim($_REQUEST['officeaddress']);

        if (isset($_REQUEST['urgentcontact']))
            $data['urgentcontact'] = trim($_REQUEST['urgentcontact']);
        if (isset($_REQUEST['urgentrelation']))
            $data['urgentrelation'] = trim($_REQUEST['urgentrelation']);
        if (isset($_REQUEST['urgentmobile']))
            $data['urgentmobile'] = trim($_REQUEST['urgentmobile']);
        if (isset($_REQUEST['urgentcontact2']))
            $data['urgentcontact2'] = trim($_REQUEST['urgentcontact2']);
        if (isset($_REQUEST['urgentrelation2']))
            $data['urgentrelation2'] = trim($_REQUEST['urgentrelation2']);
        if (isset($_REQUEST['urgentmobile2']))
            $data['urgentmobile2'] = trim($_REQUEST['urgentmobile2']);

        if ($GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "user_work WHERE user_id=" . $data['user_id']) == 0) {
            //添加
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_work", $data, "INSERT");
        } else {
            //编辑
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_work", $data, "UPDATE", "user_id=" . $data['user_id']);
        }
        $msg = trim($_REQUEST['msg']) == '' ? l("ADMIN_MODIFY_ACCOUNT_WORK") : trim($_REQUEST['msg']);

        save_log(l("ADMIN_MODIFY_ACCOUNT_WORK"), 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    /* 银行卡管理 */

    public function bank_manage() {
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = "desc";
        }
        if (isset($_REQUEST ['_order'])) {
            $sorder = $_REQUEST ['_order'];
        } else {
            $sorder = "id";
        }


        switch ($sorder) {
            case "user_name":
                $order = "u." . $sorder;
                break;
            case "bank_name":
                $order = "b." . $sorder;
                break;
            default :
                $order = "a." . $sorder;
                break;
        }

        $extWhere = " 1=1 ";

        if (trim($_REQUEST['user_name']) != '') {
            $extWhere .= " AND u.user_name like '%" . strim($_REQUEST['user_name']) . "%' ";
        }

        $id = intval($_REQUEST['id']);
        if ($id > 0) {
            $extWhere .= " and a.user_id = $id ";
            $_REQUEST['user_name'] = M("User")->where("id=" . $id)->getField("user_name");
        }


        $rs_count = $GLOBALS['db']->getOne("select count(DISTINCT a.id) from " . DB_PREFIX . "user_bank a LEFT JOIN " . DB_PREFIX . "bank b ON a.bank_id=b.id LEFT JOIN " . DB_PREFIX . "user u ON u.id=a.user_id where $extWhere");
        if ($rs_count > 0) {
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($rs_count, $listRows);
            $page = $p->show();

            $list = $GLOBALS['db']->getAll("select a.*,b.name as bank_name,u.user_name from " . DB_PREFIX . "user_bank a LEFT JOIN " . DB_PREFIX . "bank b ON a.bank_id=b.fuyou_bankid LEFT JOIN " . DB_PREFIX . "user u ON u.id=a.user_id where $extWhere ORDER BY $order $sort LIMIT " . $p->firstRow . ',' . $p->listRows);

            $this->assign("page", $page);
        }

        $this->assign("list", $list);
        $this->display();
    }

    public function de_bank() {
        $id = intval($_REQUEST['id']);
        $list = M("UserBank")->where('id=' . $id)->delete(); // 删除

        if (!$list) {
            $this->error("删除失败");
        } else {
            $this->success("删除成功");
        }
    }

    public function fund_management() {
        $title_arrays = array(
            "0" => "结存",
            "1" => "充值",
            "2" => "投标成功",
            "3" => "招标成功",
            "4" => "偿还本息",
            "5" => "回收本息",
            "6" => "提前还款",
            "7" => "提前回收",
            "8" => "申请提现",
            "9" => "提现手续费",
            "10" => "借款管理费",
            "11" => "逾期罚息",
            "12" => "逾期管理费",
            "13" => "人工操作",
            "14" => "借款服务费",
            "15" => "出售债权",
            "16" => "购买债权",
            "17" => "债权转让管理费",
            "18" => "开户奖励",
            "19" => "流标还返",
            "20" => "投标管理费",
            "21" => "投标逾期收入",
            "22" => "兑换",
            "23" => "邀请返利",
            "24" => "投标返利",
            "25" => "签到成功",
            "26" => "逾期罚金（垫付后）",
            "27" => "其他费用",
            "28" => "投资奖励",
            "29" => "红包奖励",
        );
        $this->assign("title_array", $title_arrays);

        $cate = isset($_REQUEST ['cate']) ? (intval($_REQUEST ['cate']) >= 0 ? intval($_REQUEST['cate']) : -1) : -1;

        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = "desc";
        }
        if (isset($_REQUEST ['_order'])) {
            $sorder = $_REQUEST ['_order'];
        } else {
            $sorder = "id";
        }
        //会员资金记录指定排序
        if(isset($_REQUEST ['mobile']) || $_REQUEST['user_names'] || $_REQUEST['real_names']){
            $sorder = "user_info";
        }

        switch ($sorder) {
            case "user_name":
                $order = "user_id";
                break;
            case "type_format":
                $order = "type";
                break;
            case "user_info":
                $order = "create_time";
                break;
            default :
                $order = $sorder;
                break;
        }
        $extWhere = " 1=1 ";
        if ($cate >= 0) {
            $extWhere .= " AND type = " . $cate;
        }

        if (trim($_REQUEST['user_names']) != '') {
            $extWhere .= " and u.user_name like '%" . trim($_REQUEST['user_names']) . "%'";
        }
        $begin_time = !isset($_REQUEST['begin_time']) ? 0 : (trim($_REQUEST['begin_time']) == "" ? 0 : to_timespan($_REQUEST['begin_time'], "Y-m-d"));
        $end_time = !isset($_REQUEST['end_time']) ? 0 : (trim($_REQUEST['end_time']) == "" ? 0 : to_timespan($_REQUEST['end_time'], "Y-m-d"));


        if ($begin_time > 0 || $end_time > 0) {
            if ($end_time == 0) {
                $extWhere .= " and uml.create_time >= $begin_time ";
            } else
                $extWhere .= " and uml.create_time between  $begin_time and $end_time ";
        }

        $real_name = isset($_REQUEST['real_names']) ? trim($_REQUEST['real_names']) : "";
        if ($real_name) {
            $extWhere .= " and u.real_name like '%" . $real_name . "%'";
        }

        $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : "";
        if ($mobile) {
            $extWhere .= " and u.mobile like '%" . $mobile . "%'";
        }

        $_REQUEST['begin_time'] = to_date($begin_time, "Y-m-d");
        $_REQUEST['end_time'] = to_date($end_time, "Y-m-d");
        $rs_count = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "user_money_log uml left join " . DB_PREFIX . "user u on uml.user_id=u.id  where  $extWhere");
        if ($rs_count > 0) {
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($rs_count, $listRows);
            $page = $p->show();
            $list = $GLOBALS['db']->getAll("select uml.*,u.user_name  from " . DB_PREFIX . "user_money_log uml left join " . DB_PREFIX . "user u on uml.user_id=u.id  where $extWhere ORDER BY ".$order." ". $sort." ,id desc". " LIMIT " . $p->firstRow . ',' . $p->listRows);
            $sql = "select uml.*,u.user_name  from " . DB_PREFIX . "user_money_log uml left join " . DB_PREFIX . "user u on uml.user_id=u.id  where $extWhere ORDER BY $order $sort LIMIT " . $p->firstRow . ',' . $p->listRows;
            foreach ($list as $k => $v) {
                $n = $list[$k]['type'];
                $list[$k]['type_format'] = $title_arrays[$n];
            }

            $this->assign("page", $page);
        }

        $sortImg = $sort; //排序图标
        $sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
        $sort = $sort == 'desc' ? 1 : 0; //排序方式
        $this->assign('cate', $cate);
        $this->assign('sort', $sort);
        $this->assign('order', $sorder);
        $this->assign('sortImg', $sortImg);
        $this->assign('sortType', $sortAlt);


        $this->assign("list", $list);
        $this->display();
    }

    //手动操作-快速充值
    public function hand_recharge() {
        $this->display();
    }

    public function update_hand_recharge() {

        $user_name = strim($_REQUEST['user_name']);
        $type = intval($_REQUEST['type']); //预留
        $money = floatval($_REQUEST['money']);
        $memo = strim($_REQUEST['memo']);

        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");

        if (strim($_REQUEST['money']) == "") {
            $this->error(L("金额不能为空"));
        }

        if ($money <= 0) {
            $this->error(L("金额应为正数"));
        }

        if ($user_id > 0) {
            $msg = trim($memo) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($memo);
            modify_account(array('money' => $money), $user_id, $msg, 13);

            save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else
            $this->error(L("用户不存在，或用户名输入错误"));
    }

    //手动操作-快速扣款
    public function hand_overdue() {
        $this->display();
    }

    public function update_hand_overdue() {

        $user_name = strim($_REQUEST['user_name']);
        $type = intval($_REQUEST['type']);
        $money = floatval($_REQUEST['money']);
        $memo = strim($_REQUEST['memo']);

        if (!in_array($type, array(26, 27))) {
            $this->error(L("不允许的类型"));
        }

        if ($money <= 0) {
            $this->error(L("金额应为正数"));
        }

        $money = -$money;
        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");

        if (strim($_REQUEST['money']) == "") {
            $this->error(L("金额不能为空"));
        }


        if (es_session::get("verify") != md5($_REQUEST['adm_verify'])) {
            $this->error(L('ADM_VERIFY_ERROR'));
        }

        if ($user_id > 0) {
            $msg = trim($memo) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($memo);
            modify_account(array('money' => $money), $user_id, $msg, $type);

            save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else
            $this->error(L("用户不存在，或用户名输入错误"));
    }

    //手动操作-冻结资金
    public function hand_freeze() {
        $this->display();
    }

    public function update_hand_freeze() {

        $user_name = strim($_REQUEST['user_name']);
        $lock_money = floatval($_REQUEST['lock_money']);

        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");

        if ($lock_money != 0) {
            if ($lock_money > 0 && $lock_money > D("User")->where('id=' . $user_id)->getField("money")) {
                $this->error("输入的冻结资金不得超过账户总余额");
            }

            if ($lock_money < 0 && $lock_money < -D("User")->where('id=' . $user_id)->getField("lock_money")) {
                $this->error("输入的解冻资金不得大于已冻结的资金");
            }

            $money -=$lock_money;
        }

        $msg = trim($_REQUEST['msg']) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($_REQUEST['msg']);
        modify_account(array('money' => $money, 'lock_money' => $lock_money), $user_id, $msg, 13);
        if (floatval($_REQUEST['quota']) != 0) {
            //$content = "您好，".app_conf("SHOP_TITLE")."审核部门经过综合评估您的信用资料及网站还款记录，将您的信用额度调整为：".D("User")->where("id=".$user_id)->getField('quota')."元";

            $group_arr = array(0, $user_id);
            sort($group_arr);
            $group_arr[] = 4;

            $sh_notice['shop_title'] = app_conf("SHOP_TITLE");         // 网站名称
            $sh_notice['quota'] = D("User")->where("id=" . $user_id)->getField('quota');    // 信用额度
            $GLOBALS['tmpl']->assign("sh_notice", $sh_notice);
            $tmpl_sz_failed_content = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_INS_QUOTA_TZ'", false);
            $sh_content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_sz_failed_content['content']);

            $msg_data['content'] = $sh_content;
            $msg_data['to_user_id'] = $user_id;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['type'] = 0;
            $msg_data['group_key'] = implode("_", $group_arr);
            $msg_data['is_notice'] = 4;

            $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_box", $msg_data);
            $id = $GLOBALS['db']->insert_id();
            $GLOBALS['db']->query("update " . DB_PREFIX . "msg_box set group_key = '" . $msg_data['group_key'] . "_" . $id . "' where id = " . $id);
        }
        save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    //手动操作-信用积分
    public function hand_integral() {
        $this->display();
    }

    //手动操作-积分操作
    public function hand_integrals() {
        $this->display();
    }

    public function update_hand_integral() {
        $user_name = strim($_REQUEST['user_name']);
        $point = intval($_REQUEST['point']);
        $msg = strim($_REQUEST['msg']);

        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");


        if ($user_id > 0) {
            $msg = trim($msg) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($msg);
            modify_account(array('point' => $point), $user_id, $msg, 13);

            save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else
            $this->error(L("用户不存在，或用户名输入错误"));
    }

    public function update_hand_integrals() {
        $user_name = strim($_REQUEST['user_name']);
        $score = intval($_REQUEST['point']);
        $msg = strim($_REQUEST['msg']);

        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");

        if ($user_id > 0) {
            $msg = trim($msg) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($msg);
            modify_account(array('score' => $score), $user_id, $msg, 13);

            save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else
            $this->error(L("用户不存在，或用户名输入错误"));
    }

    //手动操作-信用额度
    public function hand_quota() {
        $this->display();
    }

    public function update_hand_quota() {
        $user_name = strim($_REQUEST['user_name']);
        $quota = floatval($_REQUEST['quota']);
        $msg = strim($_REQUEST['msg']);

        $user_id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where user_name = '" . $user_name . "'");

        if ($user_id > 0) {
            $msg = trim($msg) == '' ? l("ADMIN_MODIFY_ACCOUNT") : trim($msg);
            modify_account(array('quota' => $quota), $user_id, $msg, 13);

            save_log(l("ADMIN_MODIFY_ACCOUNT"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else
            $this->error(L("用户不存在，或用户名输入错误"));
    }

    /* 授权服务机构 */

    public function agencies_index() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $this->getUserList(3, 0, $map);
        $this->display("agencies_index");
    }

    public function agencies_register() {
        $group_list = M("UserGroup")->findAll();
        $this->assign("group_list", $group_list);

        $cate_list = M("TopicTagCate")->findAll();
        $this->assign("cate_list", $cate_list);

        $field_list = M("UserField")->order("sort desc")->findAll();
        foreach ($field_list as $k => $v) {
            $field_list[$k]['value_scope'] = preg_split("/[ ,]/i", $v['value_scope']);
        }

        //地区列表

        $region_lv2 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where region_level = 2");  //二级地址
        $this->assign("region_lv2", $region_lv2);

        $this->assign("field_list", $field_list);
        $this->display("agencies_add");
    }

    public function agencies_insert() {

        B('FilterString');
        $ajax = intval($_REQUEST['ajax']);
        $data = M(MODULE_NAME)->create();

        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/agencies_add"));

        if (!check_empty($data['user_pwd'])) {
            $this->error(L("USER_PWD_EMPTY_TIP"));
        }
        if ($data['user_pwd'] != $_REQUEST['user_confirm_pwd']) {
            $this->error(L("USER_PWD_CONFIRM_ERROR"));
        }

        if (!check_empty($data['idno'])) {
            $this->error(sprintf(L("USER_EMPTY_ERROR"), L("IPS_IDENT_TYPE_1")));
        }

        if (!check_empty($data['real_name'])) {
            $this->error(sprintf(L("USER_EMPTY_ERROR"), L("REAL_NAME")));
        }

        $_REQUEST ["user_type"] = 3;
        $_REQUEST ["idcardpassed"] = 1;
        $_REQUEST ["idcardpassed_time"] = TIME_UTC;

        $res = save_user($_REQUEST);
        if ($res['status'] == 0) {
            $error_field = $res['data'];
            if ($error_field['error'] == EMPTY_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EMPTY_TIP"));
                } elseif ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EMPTY_TIP"));
                } else {
                    $this->error(sprintf(L("USER_EMPTY_ERROR"), $error_field['field_show_name']));
                }
            }
            if ($error_field['error'] == FORMAT_ERROR) {
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_FORMAT_TIP"));
                }
            }

            if ($error_field['error'] == EXIST_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_EXIST_TIP"));
                }
            }
        }
        $user_id = intval($res['user_id']);
        foreach ($_REQUEST['auth'] as $k => $v) {
            foreach ($v as $item) {
                $auth_data = array();
                $auth_data['m_name'] = $k;
                $auth_data['a_name'] = $item;
                $auth_data['user_id'] = $user_id;
                M("UserAuth")->add($auth_data);
            }
        }


        foreach ($_REQUEST['cate_id'] as $cate_id) {
            $link_data = array();
            $link_data['user_id'] = $user_id;
            $link_data['cate_id'] = $cate_id;
            M("UserCateLink")->add($link_data);
        }

        // 更新数据
        $log_info = $data['user_name'];
        save_log($log_info . L("INSERT_SUCCESS"), 1);
        $this->success(L("INSERT_SUCCESS"));
    }

    public function agencies_edit() {
        $id = intval($_REQUEST ['id']);
        $condition['is_delete'] = 0;
        $condition['id'] = $id;
        $vo = M(MODULE_NAME)->where($condition)->find();
        $this->assign('vo', $vo);

        $group_list = M("UserGroup")->findAll();
        $this->assign("group_list", $group_list);

        $cate_list = M("TopicTagCate")->findAll();
        foreach ($cate_list as $k => $v) {
            $cate_list[$k]['checked'] = M("UserCateLink")->where("user_id=" . $vo['id'] . " and cate_id = " . $v['id'])->count();
        }
        $this->assign("cate_list", $cate_list);
        $field_list = M("UserField")->order("sort desc")->findAll();
        foreach ($field_list as $k => $v) {
            $field_list[$k]['value_scope'] = preg_split("/[ ,]/i", $v['value_scope']);
            $field_list[$k]['value'] = M("UserExtend")->where("user_id=" . $id . " and field_id=" . $v['id'])->getField("value");
        }
        $this->assign("field_list", $field_list);

        $rs = M("UserAuth")->where("user_id=" . $id . " and rel_id = 0")->findAll();
        foreach ($rs as $row) {
            $auth_list[$row['m_name']][$row['a_name']] = 1;
        }

        //地区列表
        $region_lv2 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where region_level = 2");  //二级地址
        foreach ($region_lv2 as $k => $v) {
            if ($v['id'] == intval($vo['province_id'])) {
                $region_lv2[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("region_lv2", $region_lv2);

        $region_lv3 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where pid = " . intval($vo['province_id']));  //三级地址
        foreach ($region_lv3 as $k => $v) {
            if ($v['id'] == intval($vo['city_id'])) {
                $region_lv3[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("region_lv3", $region_lv3);

        $n_region_lv3 = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "region_conf where pid = " . intval($vo['n_province_id']));  //三级地址
        foreach ($n_region_lv3 as $k => $v) {
            if ($v['id'] == intval($vo['n_city_id'])) {
                $n_region_lv3[$k]['selected'] = 1;
                break;
            }
        }
        $this->assign("n_region_lv3", $n_region_lv3);

        $this->assign("auth_list", $auth_list);
        $this->display("agencies_edit");
    }

    public function agencies_update() {
        B('FilterString');
        $data = M(MODULE_NAME)->create();

        $log_info = M(MODULE_NAME)->where("id=" . intval($data['id']))->getField("user_name");
        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/agencies_edit", array("id" => $data['id'])));
        if (!check_empty($data['user_pwd']) && $data['user_pwd'] != $_REQUEST['user_confirm_pwd']) {
            $this->error(L("USER_PWD_CONFIRM_ERROR"));
        }

        if (!check_empty($data['idno'])) {
            $this->error(sprintf(L("USER_EMPTY_ERROR"), L("IPS_IDENT_TYPE_1")));
        }

        if (!check_empty($data['real_name'])) {
            $this->error(sprintf(L("USER_EMPTY_ERROR"), L("REAL_NAME")));
        }

        $_REQUEST ["user_type"] = 3;
        $_REQUEST ["idcardpassed"] = 1;
        $_REQUEST ["idcardpassed_time"] = TIME_UTC;

        $res = save_user($_REQUEST, 'UPDATE');
        if ($res['status'] == 0) {
            $error_field = $res['data'];
            if ($error_field['error'] == EMPTY_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EMPTY_TIP"));
                } elseif ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EMPTY_TIP"));
                } else {
                    $this->error(sprintf(L("USER_EMPTY_ERROR"), $error_field['field_show_name']));
                }
            }
            if ($error_field['error'] == FORMAT_ERROR) {
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_FORMAT_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_FORMAT_TIP"));
                }
            }

            if ($error_field['error'] == EXIST_ERROR) {
                if ($error_field['field_name'] == 'user_name') {
                    $this->error(L("USER_NAME_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'email') {
                    $this->error(L("USER_EMAIL_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'mobile') {
                    $this->error(L("USER_MOBILE_EXIST_TIP"));
                }
                if ($error_field['field_name'] == 'idno') {
                    $this->error(L("USER_IDNO_EXIST_TIP"));
                }
            }
        }

        //更新权限

        M("UserAuth")->where("user_id=" . $data['id'] . " and rel_id = 0")->delete();
        foreach ($_REQUEST['auth'] as $k => $v) {
            foreach ($v as $item) {
                $auth_data = array();
                $auth_data['m_name'] = $k;
                $auth_data['a_name'] = $item;
                $auth_data['user_id'] = $data['id'];
                M("UserAuth")->add($auth_data);
            }
        }
        //开始更新is_effect状态
        M("User")->where("id=" . intval($_REQUEST['id']))->setField("is_effect", intval($_REQUEST['is_effect']));
        $user_id = intval($_REQUEST['id']);
        M("UserCateLink")->where("user_id=" . $user_id)->delete();
        foreach ($_REQUEST['cate_id'] as $cate_id) {
            $link_data = array();
            $link_data['user_id'] = $user_id;
            $link_data['cate_id'] = $cate_id;
            M("UserCateLink")->add($link_data);
        }
        save_log($log_info . L("UPDATE_SUCCESS"), 1);
        $this->success(L("UPDATE_SUCCESS"));
    }

    public function agencies_info() {
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $this->getUserList(3, 0, $map);
        $this->display("agencies_info");
    }

    public function agencies_trash() {
        $this->getUserList(3, 1, array());
        $this->display("agencies_trash");
    }

    public function agencies_export_csv($page = 1) {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        //定义条件
        $map[DB_PREFIX . 'user.is_delete'] = 0;

        if (intval($_REQUEST['group_id']) > 0) {
            $map[DB_PREFIX . 'user.group_id'] = intval($_REQUEST['group_id']);
        }

        if (trim($_REQUEST['user_name']) != '') {
            $map[DB_PREFIX . 'user.user_name'] = array('like', '%' . trim($_REQUEST['user_name']) . '%');
        }
        if (trim($_REQUEST['email']) != '') {
            $map[DB_PREFIX . 'user.email'] = array('like', '%' . trim($_REQUEST['email']) . '%');
        }
        if (trim($_REQUEST['mobile']) != '') {
            $map[DB_PREFIX . 'user.mobile'] = array('like', '%' . trim($_REQUEST['mobile']) . '%');
        }
        if (trim($_REQUEST['pid_name']) != '') {
            $pid = M("User")->where("user_name='" . trim($_REQUEST['pid_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.pid'] = $pid;
        }

        $map[DB_PREFIX . 'user.user_type'] = 3;

        $list = M(MODULE_NAME)
                        ->where($map)
                        ->join(DB_PREFIX . 'user_level ON ' . DB_PREFIX . 'user.level_id = ' . DB_PREFIX . 'user_level.id')
                        ->field(DB_PREFIX . 'user.*,' . DB_PREFIX . 'user_level.name')
                        ->limit($limit)->findAll();


        if ($list) {
            register_shutdown_function(array(&$this, 'export_csv'), $page + 1);

            $user_value = array('id' => '""', 'user_name' => '""', 'money' => '""', 'lock_money' => '""', 'email' => '""', 'mobile' => '""', 'idno' => '""', 'level_id' => '""');
            if ($page == 1)
                $content = iconv("utf-8", "gbk", "编号,用户名,可用余额,冻结金额,电子邮箱,手机号,身份证,会员等级");


            //开始获取扩展字段
            $extend_fields = M("UserField")->order("sort desc")->findAll();
            foreach ($extend_fields as $k => $v) {
                $user_value[$v['field_name']] = '""';
                if ($page == 1)
                    $content = $content . "," . iconv('utf-8', 'gbk', $v['field_show_name']);
            }
            if ($page == 1)
                $content = $content . "\n";

            foreach ($list as $k => $v) {
                $user_value = array();
                $user_value['id'] = iconv('utf-8', 'gbk', '"' . $v['id'] . '"');
                $user_value['user_name'] = iconv('utf-8', 'gbk', '"' . $v['user_name'] . '"');
                $user_value['money'] = iconv('utf-8', 'gbk', '"' . number_format($v['money'], 2) . '"');
                $user_value['lock_money'] = iconv('utf-8', 'gbk', '"' . number_format($v['lock_money'], 2) . '"');
                $user_value['email'] = iconv('utf-8', 'gbk', '"' . $v['email'] . '"');
                $user_value['mobile'] = iconv('utf-8', 'gbk', '"' . $v['mobile'] . '"');
                $user_value['idno'] = iconv('utf-8', 'gbk', '"' . $v['idno'] . '"');
                $user_value['level_id'] = iconv('utf-8', 'gbk', '"' . $v['name'] . '"');

                //取出扩展字段的值
                $extend_fieldsval = M("UserExtend")->where("user_id=" . $v['id'])->findAll();
                foreach ($extend_fields as $kk => $vv) {
                    foreach ($extend_fieldsval as $kkk => $vvv) {
                        if ($vv['id'] == $vvv['field_id']) {
                            $user_value[$vv['field_name']] = iconv('utf-8', 'gbk', '"' . $vvv['value'] . '"');
                            break;
                        }
                    }
                }

                $content .= implode(",", $user_value) . "\n";
            }


            header("Content-Disposition: attachment; filename=user_list.csv");
            echo $content;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    public function agencies_account_detail() {
        $user_id = intval($_REQUEST['id']);
        $user_info = M("User")->getById($user_id);
        $log_begin_time = trim($_REQUEST['log_begin_time']) == '' ? 0 : to_timespan($_REQUEST['log_begin_time']);
        $log_end_time = trim($_REQUEST['log_end_time']) == '' ? 0 : to_timespan($_REQUEST['log_end_time']);
        $t = strim($_REQUEST['t']) == "" ? "money" : strim($_REQUEST['t']);

        $map['user_id'] = $user_id;
        if (trim($_REQUEST['log_info']) != '') {
            if ($t == "" || $t == "quota") {
                $map['log_info'] = array('like', '%' . trim($_REQUEST['log_info']) . '%');
            } else {
                $map['memo'] = array('like', '%' . trim($_REQUEST['log_info']) . '%');
            }
        }

        if ($log_end_time == 0) {
            if ($t == "" || $t == "quota") {
                $map['log_time'] = array('gt', $log_begin_time);
            } else {
                $map['create_time'] = array('gt', $log_begin_time);
            }
        } elseif ($log_begin_time > 0 || $log_end_time > 0) {
            if ($t == "" || $t == "quota") {
                $map['log_time'] = array('between', array($log_begin_time, $log_end_time));
            } else {
                $map['create_time'] = array('between', array($log_begin_time, $log_end_time));
            }
        }

        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

        if ($t == "money") { //资金日志
            $model = M("UserMoneyLog");
        } elseif ($t == "freeze") {
            $model = M("UserLockMoneyLog");
        }

        if (!empty($model)) {
            $this->_list($model, $map);
        }


        $this->assign("t", $t);
        $this->assign("user_id", $user_id);
        $this->assign("user_info", $user_info);
        $this->display();
        return;
    }

    public function fund_export_csv($page = 1) {
        set_time_limit(0);
        $limit = (($page - 1) * intval(app_conf("BATCH_PAGE_SIZE"))) . "," . (intval(app_conf("BATCH_PAGE_SIZE")));

        //定义条件
        $title_arrays = array(
            "0" => "结存",
            "1" => "充值",
            "2" => "投标成功",
            "3" => "招标成功",
            "4" => "偿还本息",
            "5" => "回收本息",
            "6" => "提前还款",
            "7" => "提前回收",
            "8" => "申请提现",
            "9" => "提现手续费",
            "10" => "借款管理费",
            "11" => "逾期罚息",
            "12" => "逾期管理费",
            "13" => "人工操作",
            "14" => "借款服务费",
            "15" => "出售债权",
            "16" => "购买债权",
            "17" => "债权转让管理费",
            "18" => "开户奖励",
            "19" => "流标还返",
            "20" => "投标管理费",
            "21" => "投标逾期收入",
            //"22" => "兑换",
            "23" => "邀请返利",
            "24" => "投标返利",
            "25" => "签到成功",
            "26" => "逾期罚金（垫付后）",
            "27" => "其他费用",
            "28" => "投资奖励",
            "26" => "红包奖励",
        );

        $cate = isset($_REQUEST ['cate']) ? (intval($_REQUEST ['cate']) >= 0 ? intval($_REQUEST['cate']) : -1) : -1;
        $extWhere = " 1=1 ";

        if ($cate >= 0) {
            $extWhere .= " AND type = " . $cate;
        }

        if (trim($_REQUEST['user_names']) != '') {
            $extWhere .= " and u.user_name like '%" . trim($_REQUEST['user_names']) . "%'";
        }
        $begin_time = !isset($_REQUEST['begin_time']) ? 0 : (trim($_REQUEST['begin_time']) == "" ? 0 : to_timespan($_REQUEST['begin_time'], "Y-m-d"));
        $end_time = !isset($_REQUEST['end_time']) ? 0 : (trim($_REQUEST['end_time']) == "" ? 0 : to_timespan($_REQUEST['end_time'], "Y-m-d"));


        if ($begin_time > 0 || $end_time > 0) {
            if ($end_time == 0) {
                $extWhere .= " and uml.create_time >= $begin_time ";
            } else
                $extWhere .= " and uml.create_time between  $begin_time and $end_time ";
        }

        $_REQUEST['begin_time'] = to_date($begin_time, "Y-m-d");
        $_REQUEST['end_time'] = to_date($end_time, "Y-m-d");
        $list = $GLOBALS['db']->getAll("select uml.*,u.user_name  from " . DB_PREFIX . "user_money_log uml left join " . DB_PREFIX . "user u on uml.user_id=u.id  where $extWhere order by id desc  LIMIT " . $limit);

        foreach ($list as $k => $v) {
            $n = $list[$k]['type'];
            $list[$k]['type_format'] = $title_arrays[$n];
        }
        if ($list) {
            register_shutdown_function(array(&$this, 'fund_export_csv'), $page + 1);
            $user_value = array('id' => '""', 'user_id' => '""', 'type_format' => '""', 'money' => '""', 'account_money' => '""', 'memo' => '""', 'create_time_ymd' => '""');
            if ($page == 1)
                $content = iconv("utf-8", "gbk", "编号,会员名,类型,操作金额,余额,备注,操作时间");
            $content = $content . "\n";
            foreach ($list as $k => $v) {
                $fund_list = array();
                $fund_list['id'] = iconv('utf-8', 'gbk', '"' . $v['id'] . '"');
                $fund_list['user_name'] = iconv('utf-8', 'gbk', '"' . get_user_name_reals($v['user_id']) . '"');
                $fund_list['type_format'] = iconv('utf-8', 'gbk', '"' . $v['type_format'] . '"');
                $fund_list['money'] = iconv('utf-8', 'gbk', '"' . format_price($v['money']) . '"');
                $fund_list['account_money'] = iconv('utf-8', 'gbk', '"' . format_price($v['account_money']) . '"');
                $fund_list['memo'] = iconv('utf-8', 'gbk', '"' . $v['memo'] . '"');
                $fund_list['create_time_ymd'] = iconv('utf-8', 'gbk', '"' . $v['create_time_ymd'] . '"');

                $content .= implode(",", $fund_list) . "\n";
            }
            header("Content-Disposition: attachment; filename=fund_list.csv");
            echo $content;
        } else {
            if ($page == 1)
                $this->error(L("NO_RESULT"));
        }
    }

    //我的会员投资记录
    public function user_populog() {
        $this->assign("main_title", "会员投资记录");
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $a_id = intval($adm_session['adm_id']);
        //如果是线下门店店长账号登录，则可以看到属于他的销售成员下所有的客户投资记录，如果是销售登录，只看到属于自己的客户投资记录
        $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $rate = isset($_REQUEST['rate']) ? trim($_REQUEST['rate']) : 0.005;
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        $condition = array();// 查询总记录用
        $where = ""; // 分页链表用
        $condition['is_auto'] = array("eq",0);
        $condition['contract_no'] = array("neq", '');
        $where .= "dl.is_auto=0 ";
        $where .= "and dl.contract_no != ' ' ";

        if ($user_name) {
            $condition['user_name'] = array('like', '%' . $user_name . '%');
            $where .= "and dl.user_name like '%".$user_name."%' ";
        }
        if ($begin_time && $end_time=='') {
            $begin_time = strtotime($begin_time);
            $condition['create_time'] = array('gt', $begin_time);
            $where .= "and dl.create_time >=".$begin_time." ";
        }
        if ($end_time && $begin_time=='') {
            $end_time = strtotime($end_time."23:59:59");
            $condition['create_time'] = array('lt', $end_time);
            $where .= "and dl.create <=".$end_time." ";
        }
        if($begin_time && $end_time){
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time."23:59:59");
            $condition['create_time'] = array(array('gt', $begin_time),array('lt', $end_time));
        }
        $is_who = M('admin')->where("id = {$a_id}")->find();
        if ($is_who['is_department'] == 1) {
            $is_dianzhang = M('admin')->where("pid = {$a_id}")->select();
            $user_tmp = array();
            foreach ($is_dianzhang as $k => $v) {
                $xsx_user = M('user')->field('id,admin_id')->where("admin_id = {$v['id']}")->select();
                $user_tmp = array_merge($xsx_user, $user_tmp);
            }
            foreach($user_tmp as $key=>$val){
                $user_id_arr[] = $val['id'];
            }
            $user_id = implode(",",$user_id_arr);
            $condition['user_id'] = array("in",$user_id);
            $where .= "and dl.user_id in (".$user_id.") ";
            $count = M("deal_load")->where($condition)->count();
            import("ORG.Util.Page");       //导入分页类
            $Page = new Page($count,18);
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where."
                    order by dl.create_time desc
                    limit {$Page->firstRow},{$Page->listRows}
                    ";
            $list = D()->query($sql);

            $arrtmp = $list;
            //合并后的数组按时间排列
            foreach ($arrtmp as $key => $row) {
                $volume[$key] = $row['create_time'];
            }
            array_multisort($volume, SORT_DESC, $arrtmp);
            foreach ($arrtmp as &$va) {
                //投资人真实名
                $arr = M('user')->field('id,real_name,mobile,admin_id')->where("id = {$va['user_id']}")->find();
                $va['real_name'] = $arr['real_name'];
                //投资人属于哪个销售
                $is_sale = M('admin')->field('id,adm_name')->where("id = {$arr['admin_id']}")->find();
                $va['is_sale_name'] = $is_sale['adm_name'];
                //联系方式
                $va['mobile'] = $arr['mobile'];
                //年利率
                $ptt = M('deal')->field('id,name,rate,deal_status,enddate')->where("id = {$va['deal_id']}")->find();
                $va['rate'] = $ptt['rate'];
                //标状态
                if ($ptt['deal_status'] == 1)
                    $va['deal_status'] = "投资中";
                elseif ($ptt['deal_status'] == 2)
                    $va['deal_status'] = "满标";
                elseif ($ptt['deal_status'] == 3)
                    $va['deal_status'] = "流标";
                elseif ($ptt['deal_status'] == 4)
                    $va['deal_status'] = "还款中";
                else
                    $va['deal_status'] = "已完成";
            }

            $sql = "select sum(truncate((d.repay_time*dl.money*{$rate}/360),2)) as sum_commission  from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where;
            $res = D()->query($sql);
            $sum_commission = $res['0']['sum_commission'];
            $this->assign("sum_commission", $sum_commission);
            // 模拟设置分页额外传入的参数
            $Page->parameter = 'search=key&name=thinkphp';
            // 设置分页显示
            $Page->setConfig('header', '条数据');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $page = $Page->show();
            $this->assign("page", $page);
            $this->assign('arrtmp', $arrtmp);
            $this->display();
            //else是如果是销售则只看到自己下面的客户投资
        }else {
            //$list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load d LEFT JOIN ".DB_PREFIX."user u ON d.user_id = u.id LEFT JOIN ".DB_PREFIX."admin a ON u.admin_id = a.id  WHERE  = d.user_id".$a_id);
            import("ORG.Util.Page");       //导入分页类
            $user_list = M('user')->field('id,admin_id')->where("admin_id = {$a_id}")->select();
            $arrtmp = array();
            foreach($user_list as $key=>$val){
                $user_id_arr[] = $val['id'];
            }
            $user_id = implode(",",$user_id_arr);
            $condition['user_id'] = array("in",$user_id);
            $where .= "and dl.user_id in (".$user_id.") ";
            $count = M("deal_load")->where($condition)->count();
            $Page = new Page($count,18);
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where."
                    order by dl.create_time desc
                    limit {$Page->firstRow},{$Page->listRows}
                    ";
            $list = D()->query($sql);
            if($list){
                $arrtmp = array_merge($list, $arrtmp);
            }
            //合并后的数组按时间排列
            foreach ($arrtmp as $key => $row) {
                $volume[$key] = $row['create_time'];
            }
            array_multisort($volume, SORT_DESC, $arrtmp);
            foreach ($arrtmp as &$va) {
                //投资人真实名
                $arr = M('user')->field('id,real_name,mobile')->where("id = {$va['user_id']}")->find();
                $va['real_name'] = $arr['real_name'];
                // 销售员
                $res = M("admin")->field("adm_name")->where("id=".$a_id)->find();
                $va['is_sale_name'] = $res['adm_name'];
                //联系方式
                $va['mobile'] = $arr['mobile'];
                //年利率
                $ptt = M('deal')->field('id,name,rate,deal_status,repay_time')->where("id = {$va['deal_id']}")->find();
                $va['rate'] = $ptt['rate'];
                //标状态
                if ($ptt['deal_status'] == 1)
                    $va['deal_status'] = "投资中";
                elseif ($ptt['deal_status'] == 2)
                    $va['deal_status'] = "满标";
                elseif ($ptt['deal_status'] == 3)
                    $va['deal_status'] = "流标";
                elseif ($ptt['deal_status'] == 4)
                    $va['deal_status'] = "还款中";
                else
                    $va['deal_status'] = "已完成";
            }
            $sql = "select  sum(truncate((d.repay_time*dl.money*{$rate}/360),2)) as sum_commission  from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where;
            $res = D()->query($sql);
            $sum_commission =$res['0']['sum_commission'];
            $this->assign("sum_commission", $sum_commission);
            // 模拟设置分页额外传入的参数
            $Page->parameter = 'search=key&name=thinkphp';
            // 设置分页显示
            $Page->setConfig('header', '条数据');
            $Page->setConfig('first', '首页');
            $Page->setConfig('last', '尾页');
            $page = $Page->show();
            $this->assign("page", $page);
            $this->assign('arrtmp', $arrtmp);
            $this->display();
        }
    }

    // 导出
    public function export_csv_user_populog(){
        $this->assign("main_title", "会员投资记录");
        $adm_session = es_session::get(md5(conf("AUTH_KEY")));
        $a_id = intval($adm_session['adm_id']);
        //如果是线下门店店长账号登录，则可以看到属于他的销售成员下所有的客户投资记录，如果是销售登录，只看到属于自己的客户投资记录
        $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $rate = isset($_REQUEST['rate']) ? trim($_REQUEST['rate']) : 0.005;
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        $condition = array();// 查询总记录用
        $where = ""; // 分页链表用
        $condition['is_auto'] = array("eq",0);
        $condition['contract_no'] = array("neq", '');
        $where .= "dl.is_auto=0 ";
        $where .= "and dl.contract_no != ' ' ";

        if ($user_name) {
            $condition['user_name'] = array('like', '%' . $user_name . '%');
            $where .= "and dl.user_name like '%".$user_name."%' ";
        }
        if ($begin_time && $end_time=='') {
            $begin_time = strtotime($begin_time);
            $condition['create_time'] = array('gt', $begin_time);
            $where .= "and dl.create_time >=".$begin_time." ";
        }
        if ($end_time && $begin_time=='') {
            $end_time = strtotime($end_time."23:59:59");
            $condition['create_time'] = array('lt', $end_time);
            $where .= "and dl.create <=".$end_time." ";
        }
        if($begin_time && $end_time){
            $begin_time = strtotime($begin_time);
            $end_time = strtotime($end_time."23:59:59");
            $condition['create_time'] = array(array('gt', $begin_time),array('lt', $end_time));
        }
        $is_who = M('admin')->where("id = {$a_id}")->find();
        if ($is_who['is_department'] == 1) {
            $is_dianzhang = M('admin')->where("pid = {$a_id}")->select();
            $user_tmp = array();
            foreach ($is_dianzhang as $k => $v) {
                $xsx_user = M('user')->field('id,admin_id')->where("admin_id = {$v['id']}")->select();
                $user_tmp = array_merge($xsx_user, $user_tmp);
            }
            foreach($user_tmp as $key=>$val){
                $user_id_arr[] = $val['id'];
            }
            $user_id = implode(",",$user_id_arr);
            $condition['user_id'] = array("in",$user_id);
            $where .= "and dl.user_id in (".$user_id.") ";
            $count = M("deal_load")->where($condition)->count();
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where."
                    order by dl.create_time desc";
            $list = D()->query($sql);
            $arrtmp = $list;
            //合并后的数组按时间排列
            foreach ($arrtmp as $key => $row) {
                $volume[$key] = $row['create_time'];
            }
            array_multisort($volume, SORT_DESC, $arrtmp);
            foreach ($arrtmp as &$va) {
                //投资人真实名
                $arr = M('user')->field('id,real_name,mobile,admin_id')->where("id = {$va['user_id']}")->find();
                $va['real_name'] = $arr['real_name'];
                //投资人属于哪个销售
                $is_sale = M('admin')->field('id,adm_name')->where("id = {$arr['admin_id']}")->find();
                $va['is_sale_name'] = $is_sale['adm_name'];
                //联系方式
                $va['mobile'] = $arr['mobile'];
                //年利率
                $ptt = M('deal')->field('id,name,rate,deal_status,enddate')->where("id = {$va['deal_id']}")->find();
                $va['rate'] = $ptt['rate'];
                //标状态
                if ($ptt['deal_status'] == 1)
                    $va['deal_status'] = "投资中";
                elseif ($ptt['deal_status'] == 2)
                    $va['deal_status'] = "满标";
                elseif ($ptt['deal_status'] == 3)
                    $va['deal_status'] = "流标";
                elseif ($ptt['deal_status'] == 4)
                    $va['deal_status'] = "还款中";
                else
                    $va['deal_status'] = "已完成";
            }

            //else是如果是销售则只看到自己下面的客户投资
        }else {
            //$list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."deal_load d LEFT JOIN ".DB_PREFIX."user u ON d.user_id = u.id LEFT JOIN ".DB_PREFIX."admin a ON u.admin_id = a.id  WHERE  = d.user_id".$a_id);

            $user_list = M('user')->field('id,admin_id')->where("admin_id = {$a_id}")->select();
            $arrtmp = array();
            foreach($user_list as $key=>$val){
                $user_id_arr[] = $val['id'];
            }
            $user_id = implode(",",$user_id_arr);
            $condition['user_id'] = array("in",$user_id);
            $where .= "and dl.user_id in (".$user_id.") ";
            $sql = "select dl.* ,truncate((d.repay_time*dl.money*{$rate}/360),2) as commission,d.name,d.cate_id,d.qixi_time,d.jiexi_time,d.repay_time,d.rate from ".DB_PREFIX."deal_load as dl
                    left join ".DB_PREFIX."deal as d on dl.deal_id = d.id
                    where ".$where."
                    order by dl.create_time desc";
            $list = D()->query($sql);
            if($list){
                $arrtmp = array_merge($list, $arrtmp);
            }
            //合并后的数组按时间排列
            foreach ($arrtmp as $key => $row) {
                $volume[$key] = $row['create_time'];
            }
            array_multisort($volume, SORT_DESC, $arrtmp);
            foreach ($arrtmp as &$va) {
                // 用户名
                $va['user_name'] = get_user_name_reals($va["user_id"]);
                // 投标类型
                $va['cate_id'] = get_deal_cate_name($va['cate_id']);
                //投资人真实名
                $arr = M('user')->field('id,real_name,mobile')->where("id = {$va['user_id']}")->find();
                $va['real_name'] = $arr['real_name'];
                // 销售员
                $res = M("admin")->field("adm_name")->where("id=".$a_id)->find();
                $va['is_sale_name'] = $res['adm_name'];
                // 投资本金
                $va["real_self_money"] = $va['money'] - $va['coupon_cash'];
                //联系方式
                $va['mobile'] = $arr['mobile'];
                //年利率
                $ptt = M('deal')->field('id,name,rate,deal_status,repay_time')->where("id = {$va['deal_id']}")->find();
                $va['rate'] = $ptt['rate'];
                //标状态
                if ($ptt['deal_status'] == 1)
                    $va['deal_status'] = "投资中";
                elseif ($ptt['deal_status'] == 2)
                    $va['deal_status'] = "满标";
                elseif ($ptt['deal_status'] == 3)
                    $va['deal_status'] = "流标";
                elseif ($ptt['deal_status'] == 4)
                    $va['deal_status'] = "还款中";
                else
                    $va['deal_status'] = "已完成";
            }
        }
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();
        $num = 1;
        foreach ($arrtmp as $key => $val) {
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
                ->setCellValue('L' . $num,  $val['deal_status']);


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

//        if ($start_time = $end_time) {
//            $filename = $start_time . "的入金统计表";
//        } else {
//            $filename = $start_time . '~' . $end_time . "的入金统计表";
//        }
        $filename = "邀请投资的提成表";
        php_export_excel($objPHPExcel, $filename);
    }

    public function send_welfare() {
        if (!$_REQUEST['type'] || !$_REQUEST['remark'] || !$_REQUEST['type_name']) {
            $root['info'] = "非法请求！";
            ajax_return($root);
        }
        $user_id = $_REQUEST['user_id'];
        $type = $_REQUEST['type'];
        if ($type = "dixian") {
            $remark = $_REQUEST['remark'];
            $value = $_REQUEST['value'];
            $min_limit= $_REQUEST['min_limit'];
            $type_name = $_REQUEST['type_name'];
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_name = $adm_session['adm_name'];
            $succ_str='';
            $err_str='';
            $data['coupon_flag'] = "后台".$adm_name.'用户发送';
            $data['remark'] = $remark;
            if($value==100 && $min_limit==50000){
                $confId=1;
            }else if($value==100 && $min_limit==10000){
                $confId=4;
            }
            foreach ($user_id as $val) {
                $coupon_id = MO("Coupon")->confAdd($val, $data,$confId);
                if($coupon_id){
                    $succ_str.=",".$val;
                }else{
                    $err_str.=",".$val;
                }
            }
        }
        $root['info'] = "发放" . $type_name . "成功的用户id为：".$succ_str;
        if($err_str){
            $root['info'].="失败的用户id为：$err_str";
        }
        ajax_return($root);
    }

//判断是门店还是销售if end
}

?>