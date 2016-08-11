<?php

/**
 * User Related
 * 用户相关模块
 *
 * */
class UserAction extends Action {

    function __construct() {
        parent::__construct();
        $action = array("login", "send_reset_pwd_code", "resetpw", "get_all_code", "user_qrcode_card");
        if (!in_array(ACTION_NAME, $action)) {
            $this->user_info = checkLogin();
        }
    }

    /**
     *  
     * @api {get} &m=user&a=login&name=admin&password=admin&r_type=1&ip=127.0.0.1&agent=iphone5s 用户登录 
     * @apiName 用户登录 
     * @apiGroup User
     * @apiVersion 1.0.0 
     * @apiDescription 请求url 
     *  
     * @apiParam {string} name {必传}手机号或者邮箱
     * @apiParam {string} password {必传}账户密码
     * 
     * @apiSuccess {string} code 结果码 
     * @apiSuccess {string} errmsg 消息说明 
     * @apiSuccess {string} admin 是否是后台管理员
     * @apiSuccess {string} user_id 用户id
     * @apiSuccess {string} user_name 用户名称
     * @apiSuccess {string} position_id 岗位id
     * @apiSuccess {string} department_name 部门名称
     * @apiSuccess {string} password 用户密码
     * @apiSuccess {string} sid session_id(用于以后的登录)
     * @apiSuccess {string} work_log 工作日志
     * @apiSuccess {json} index_left_data
     * @apiSuccess {string} index_left_data.name 用户名称  
     * @apiSuccess {string} index_left_data.mobile 手机号  
     * @apiSuccess {string} index_left_data.qq QQ号码  
     * @apiSuccess {string} index_left_data.weixinid 微信号码  
     * @apiSuccess {string} index_left_data.email 邮箱账号  
     * @apiSuccess {string} index_left_data.count 用户等级（星星的个数，最大是5）  
     * 
     * @apiSuccessExample 返回示范: 
      {
      "user_id": "7",
      "user_name": "dch",
      "password": "31ff4d00f6770e685cc6dc0ee6ece7f9",
      "salt": "906855",
      "role_name": "总经理",
      "department_name": "互联网事业部",
      "index_left_data": {
      "name": "dch",
      "mobile": "18565826594",
      "qq": "444",
      "weixinid": "okoloook",
      "email": "wangpeitao@chhyt.com",
      "target_count": "1000",
      "count": "2",
      "work_log": "汇报记录"
      },
      "sid": "tiqorpa5chofr24kd8p54a7832",
      "code": "1",
      "errmsg": "登录成功",
      "m": "user",
      "a": "login"
      }
     */
    public function login() {
        $m_announcement = M('announcement');
        $m_loghistory = M('loginHistory');
        $where['status'] = array('eq', 1);
        $where['isshow'] = array('eq', 1);
        $this->announcement_list = $m_announcement->where($where)->order('order_id')->select();
        if (!isset($_REQUEST['name']) || $_REQUEST['name'] == '') {
            output("请输入用户名或者手机号");
        }
        if (!isset($_REQUEST['password']) || $_REQUEST['password'] == '') {
            output("请输入密码");
        }
        $name = $_REQUEST['name'];
        $m_user = M('user');
        $condition['name'] = $name;
        $condition['mobile'] = $name;
        $condition['_logic'] = 'OR';
        $user = $m_user->where($condition)->find();
//        $login_where['user_id'] = $user['user_id'];
//        $login_where['login_status'] = 2;
//        $login_where['login_time'] = array('gt', time() - 10 * 60);
//        $login_count = $m_loghistory->where($login_where)->count();
//        if ($login_count >= 3) {
//            $login_time = $m_loghistory->where(array('user_id' => $user['user_id'], 'login_status' => 2))->order('login_time desc')->getField('login_time');
//            $point_time = 10 - (round((time() - $login_time) / 60));
//            output("您登录的错误次数过于频繁，请{$point_time}分钟后再试。或点击忘记密码重置");
//        }
        //记入登录记录
        $record['user_id'] = $user['user_id'];
        $record['login_time'] = time();
        $record['login_ip'] = $_REQUEST['ip'];
        $record['agent'] = $_REQUEST['agent'];
        if ($user['password'] != md5(md5(trim($_REQUEST['password'])) . $user['salt'])) {
            $record['login_status'] = 2;
            $m_loghistory->add($record);
            output("账号或者密码不对");
        }
        if (-1 == $user['status']) {
            output('您的账号未通过审核，请联系管理员！');
        } elseif (0 == $user['status']) {
            output('您的账号正在审核中，请耐心等待！');
        } elseif (2 == $user['status']) {
            output('此账号已停用！');
        } else {
            $d_role = D('RoleView');
            $role = $d_role->where('user.user_id = %d', $user['user_id'])->find();
            if (!is_array($role) || empty($role)) {
                output('系统没有给您分配任何岗位，请联系管理员！');
            } else {
                $record['login_status'] = 1;
                $m_loghistory->add($record);
                session_start();
                session("[regenerate]");
                if ($user['category_id'] == 1) {
                    session('admin', "1");
                }
                session('user_id', $user['user_id']);
                session('user_name', $user['name']);
                session('user_img', $user['img']);
                session('mobile', $user['mobile']);
                session('role_id', $role['role_id']);
                session('role_name', $role['role_name']);
                session('position_id', $role['position_id']);
                session('department_id', $role['department_id']);
                session('department_name', $role['department_name']);
                session('password', $user['password']);
                session('salt', $user['salt']);
                $root['user_id'] = $user['user_id'];
                $root['user_name'] = $user['name'];
                $root['password'] = $user['password'];
                $root['salt'] = $user['salt'];
                $root['role_name'] = $role['role_name'];
                $root['department_name'] = $role['department_name'];
                $root['index_left_data'] = $this->get_index_left_data($user['user_id']);
                $root['sid'] = session_id();
                M("user")->where(array("user_id" => $user['user_id']))->save(array("sid" => $root['sid']));
                $root['code'] = "1";
                $root['errmsg'] = "登录成功";
                output($root);
            }
        }
    }

    //密码重置
    public function resetpw() {
        if (!$_REQUEST['mobile']) {
            output("请输入手机号码");
        } else if (!$_REQUEST['verify_code']) {
            output("请输入手机验证码");
        } else if (!$_REQUEST['user_pwd']) {
            output("请输入新密码");
        } else if (!$_REQUEST['user_pwd_confirm']) {
            output("请输入确定密码");
        }
        $mobile = addslashes(htmlspecialchars(trim($_REQUEST['mobile'])));
        $verify_code = addslashes(htmlspecialchars(trim($_REQUEST['verify_code'])));
        $user_pwd = addslashes(htmlspecialchars(trim($_REQUEST['user_pwd'])));
        $user_pwd_confirm = addslashes(htmlspecialchars(trim($_REQUEST['user_pwd_confirm'])));
        if (!preg_match("/^1\d{10}$/", $mobile)) {
            output("请输入正确的手机号码");
        }
        if ($user_pwd != $user_pwd_confirm) {
            output("两次输入的密码不一致");
        }
        $m_user = M('User');
        $user = $m_user->where('mobile = %s', $mobile)->find();
        if (md5(md5($user['lostpw_time']) . $user['salt']) == $user_pwd) {
            output("您输入的密码跟原密码一致，无需重置");
        }
        if (!$user) {
            output("手机号码不存在或被禁用");
        }
        if ($user['verify_code'] != $verify_code) {
            output("验证码错误");
        }
        if ($user['lostpw_time'] < time() - 180) {
            output("验证码已过期，请重新点击发送验证码");
        }
        $password = md5(md5(trim($user_pwd)) . $user['salt']);
        $m_user->where('mobile =' . $mobile)->save(array('password' => $password, 'lostpw_time' => 0));
        $root['code'] = "1";
        $root['errmsg'] = "密码修改成功";
        output($root);
    }

//发送重置登录密码的验证码
    function send_reset_pwd_code() {

        if (!$_REQUEST['mobile'] || $_REQUEST['mobile'] == "") {
            output("请输入手机号码");
        }
        $mobile = trim($_REQUEST['mobile']);
        if (!preg_match("/^1\d{10}$/", $mobile)) {
            output("请输入正确的手机号码");
        }
        $where['mobile'] = $mobile;
        $user_info = M("User")->where($where)->find();
        if (!$user_info) {
            output("手机号码不存在或被禁用");
        }
        $user = M("User");
        $user_info = $user->field("lostpw_time,verify_code")->where($where)->find();
        if (time() - $user_info['lostpw_time'] < 60) {
            $time = 60 - (time() - $user_info['lostpw_time']);
            output("发送的验证码太过于频繁，请{$time}秒后再发");
        }
        //开始生成手机验证
        $data['verify_code'] = rand(100000, 999999);
        $data['lostpw_time'] = time();
        $user->where($where)->save($data);
        $content = "手机号{$mobile}华陌通金融找回密码手机验证码{$data['verify_code']},请于3分钟内完成验证,如非本人操作,请忽略此短信.";
        $res = send_sms($mobile, $content);
        if (!$res) {
            output("验证码发送失败，请再次发送！");
        } else {
            $root['code'] = "1";
            $root['errmsg'] = "验证码发送成功！";
            output($root);
        }
    }

    //退出
    public function logout() {
        $user_info = $this->user_info;
        session_id($_REQUEST['sid']);
        session_start();
        session(null);
        M("user")->where(array("user_id" => $user_info['user_id']))->save(array("sid" => ""));
        $root['code'] = "1";
        $root['errmsg'] = "退出成功";
        output($root);
    }

    //CRM首页
    public function index() {
        $user_info = $this->user_info;
        $user_id = $user_info['user_id'];
        //获取首页左边栏的数据
        $root = $this->get_index_left_data($user_id);
        //
        $start_time = strtotime(date("Y-m"));
        $end_time = strtotime(date("Y-m-t"));
        $where = " create_time>=$start_time and create_time<=$end_time";
        $root['new_report_count'] = (string) (int) M('customer')->where($where . " and owner_role_id={$user_info['role_id']}")->getfield("count(*)");
        $telephone_count = M("mobile_call_log")->where($where . " and user_id=$user_id")->getField("count(*)");
        if (!$telephone_count) {
            $telephone_count = "0";
        }
        $root['telephone_count'] = $telephone_count;
        $root['code'] = "1";
        $root['errmsg'] = "数据请求成功！";

        $position_id = M("role")->where('user_id', $user_id)->getField("position_id");
        $position_info = M("position")->field("department_id,name")->where("position_id=%d", $position_id)->find();
        $root['role_name'] = $position_info['name'];
        $root['department_name'] = M('role_department')->where("department_id=%d", $position_info['department_id'])->getField("name");
        $root['qrcode_url'] = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : 'http' . '://' . $_SERVER['SERVER_NAME'] . "/index.php?g=mapi&m=user&a=user_qrcode_card&user_id=" . $user_id;
        output($root);
    }

    //登录日志
    public function login_log() {
        $user_info = $this->user_info;
        $login_history = M("login_history");
        $where['user_id'] = $user_info['user_id'];
        $where['login_status'] = 1;
        $field = "login_time,agent,login_ip,login_status";
        $order['login_id'] = 'desc';

        if (!$_REQUEST['page']) {
            $page = 1;
        } else {
            $page = $_REQUEST['page'];
        }
        $root['page'] = $page;
        $limit = (($page - 1) * 10) . ",10";
        $count = $login_history->where($where)->getfield("count(*)");
        $root['page_total'] = (string) ceil($count / 10);
        $log_list = $login_history->field($field)->where($where)->order($order)->limit($limit)->select();
        foreach ($log_list as $key => $val) {
            $log_list[$key]['login_time'] = date("Y年m月d日 H:i:s", $val['login_time']);
            $log_list[$key]['login_status'] = $val['login_status'] == 1 ? '登录成功' : '登录失败';
        }
        $root['code'] = "1";
        $root['errmsg'] = "数据请求成功！";
        $root['data'] = $log_list;
        output($root);
    }

    //修改密码
    public function save_reset_pwd() {
        $user_info = $this->user_info;
        if (!$_REQUEST['old_pwd']) {
            output("请输入旧密码");
        } else if (!$_REQUEST['new_pwd']) {
            output("请输入新密码");
        } else if (!$_REQUEST['new_pwd_confirm']) {
            output("请输入确定新密码");
        } else if ($_REQUEST['new_pwd'] != $_REQUEST['new_pwd_confirm']) {
            output("两次输入的密码不一致，请重新输入");
        }
        $old_pwd = md5(md5(trim($_REQUEST['old_pwd'])) . $user_info['salt']);
        $new_pwd = md5(md5($_REQUEST['new_pwd']) . $user_info['salt']);
        if ($old_pwd != $user_info['password']) {
            output("您输入的旧密码不正确，请重新输入");
        }
        $data['password'] = $new_pwd;
        $where['user_id'] = $user_info['user_id'];
        $res = M("user")->where($where)->save($data);
        if (!$res) {
            output("密码修改失败,请重新操作！");
        } else {
            $root['code'] = "1";
            $root['errmsg'] = "密码修改成功,请重新登录！";
            output($root);
        }
    }

    //发送绑定手机号的验证码
    function send_save_mobile_code() {
        $user_info = $this->user_info;
        if (!$_REQUEST['mobile'] || $_REQUEST['mobile'] == "") {
            output("请输入手机号码");
        }
        $mobile = trim($_REQUEST['mobile']);
        if (!preg_match("/^1\d{10}$/", $mobile)) {
            output("请输入正确的手机号码");
        }
        $user = M("User");
        $is_mobile = $user->where(array("mobile" => $mobile))->getField("mobile");
        if ($is_mobile) {
            output("此手机号已经被绑定！");
        }
        $where['user_id'] = $user_info['user_id'];
        $user_info = $user->field("lostpw_time,verify_code")->where($where)->find();
        if (time() - $user_info['lostpw_time'] < 60) {
            $time = 60 - (time() - $user_info['lostpw_time']);
            output("发送的验证码太过于频繁，请{$time}秒后再发");
        }
        //开始生成手机验证
        $data['verify_code'] = rand(100000, 999999);
        $data['lostpw_time'] = time();
        $user->where($where)->save($data);
        $content = "手机号{$mobile}华陌通金融绑定手机号的手机验证码{$data['verify_code']},请于3分钟内完成验证,如非本人操作,请忽略此短信.";
        $res = send_sms($mobile, $content);
        if (!$res) {
            output("验证码发送失败，请再次发送！");
        } else {
            $root['code'] = "1";
            $root['errmsg'] = "验证码发送成功！";
            output($root);
        }
    }

    //绑定手机号码
    public function save_mobile() {
        $user_info = $this->user_info;
        if (!$_REQUEST['mobile']) {
            output("请输入新的手机号码");
        } else if (!$_REQUEST['verify_code']) {
            output("请输入新的手机验证码");
        } else if (!preg_match("/^1\d{10}$/", $_REQUEST['mobile'])) {
            output("请输入正确的手机号码");
        }
        $user = M("user");
        $where['user_id'] = $user_info['user_id'];
        $user_info = $user->field("lostpw_time,verify_code,mobile")->where($where)->find();
        if ($_REQUEST['mobile'] == $user_info['mobile']) {
            output("您输入的新手机号与旧手机一致，无需修改！");
        }
        if (time() - $user_info['lostpw_time'] > 180) {
            output("验证码已经过期");
        } else if ($user_info['verify_code'] != $_REQUEST['verify_code']) {
            output("输入的验证码不正确");
        }
        $data['mobile'] = $_REQUEST['mobile'];
        $res = $user->where($where)->save($data);
        if (!$res) {
            output("操作失败");
        }
        $root['code'] = "1";
        $root['errmsg'] = "操作成功！";
        output($root);
    }

    //显示所有的验证码
    public function get_all_code() {

        $verify_code_list = M("user")->where("verify_code!=''")->field("user_id,name,verify_code")->order("lostpw_time desc")->limit(20)->select();
        header("Content-Type:text/html; charset=utf-8");
        $html_str = "<table><tr><td>编号</td><td>用户名</td><td>验证码</td></tr>";
        foreach ($verify_code_list as $key => $val) {
            $html_str.="<tr><td>{$val['user_id']}</td><td>{$val['name']}</td><td>{$val['verify_code']}</td></tr>";
        }
        echo $html_str;
    }

    //获取首页左边栏的数据
    public function get_index_left_data($user_id = '') {
        $where['user_id'] = $user_id;
        $field = "name,mobile,qq,weixinid,email,target_count";
        $root = M("user")->field($field)->where($where)->find();
        $field = "FROM_UNIXTIME(login_time,'%Y-%m-%d') as login_date";
        $log_list = M("login_history")->field($field)->group("login_date")->select();
        $root['count'] = (string) (ceil(count($log_list) / 30));
        if ($root['count'] > "5") {
            $root['count'] = "5";
        }
        $word_log_type = M("working_log")->where("user_id=$user_id")->order("create_time desc")->getfield("log_type");
        if (!$word_log_type) {
            $root['work_log'] = "";
        } else if ($word_log_type == 1) {
            $root['work_log'] = "外勤签到";
        } else if ($word_log_type == 2) {
            $root['work_log'] = "汇报记录";
        }
        return $root;
    }

    public function call_mobile() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id'] || !$_REQUEST['mobile']) {
            output("缺少请求参数");
        }
        $data['customer_id'] = $_REQUEST['customer_id'];
        $data['user_id'] = $user_info['user_id'];
        $data['mobile'] = $_REQUEST['mobile'];
        $data['create_time'] = time();
        $mobile_call_log = M("mobile_call_log");
        if (!$mobile_call_log->add($data)) {
            output("操作失败！");
        } else {
            $root['code'] = "1";
            $root['errmsg'] = "操作成功！";
            output($root);
        }
    }

    //用户信息的编辑
    public function user_edit() {
        $user_info = $this->user_info;
        $where['user_id'] = $user_info['user_id'];
        $data = $_REQUEST;
        M("user")->where($where)->save($data);
        $root['code'] = "1";
        $root['errmsg'] = "操作成功！";
        output($root);
    }

    //个人中心二维码的页面
    public function user_qrcode_card() {
        $where['user_id'] = $_REQUEST['user_id'];
        $field = "name,mobile,email,qq,weixinid,role_id";
        $root = M('user')->field($field)->where($where)->find();

        $position_id = M("role")->where('user_id=%d', $where['user_id'])->getField("position_id");
        $position_info = M("position")->field("department_id,name")->where("position_id=%d", $position_id)->find();
        $root['role_name'] = $position_info['name'];
        $department_info = M('role_department')->field("name,address")->where("department_id=%d", $position_info['department_id'])->find();
//        $root['position_name']=$department_info['name'];
        $root['address'] = $department_info['address'];
        $this->assign("data", $root);
        $this->display();
    }

}
