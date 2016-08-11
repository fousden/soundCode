<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/22
 * Time: 12:58
 */
class loanModule extends SiteBaseModule{
    public function index()
    {
        $GLOBALS['tmpl']->assign("module_name",MODULE_NAME);
        $GLOBALS['tmpl']->display("page/loan_step_one.html");
    }

    public function step_one()
    {
        foreach ($_REQUEST as $k => $v) {
            $_REQUEST[$k] = htmlspecialchars(addslashes($v));
        }
        $name = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
        $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $sex = isset($_REQUEST['sex']) ? trim($_REQUEST['sex']) : '';
        $safe = isset($_REQUEST['safe']) ? trim($_REQUEST['safe']) : '';
        $msg = -1; //  初始值，获取该值则验证通过
        // 判断名字是否为空
        if($name==''){
            $msg = 0;// 名字不能为空
            echo $msg;
            exit;
        }
        // 判断名字是否为2-8个中文
        preg_match_all('/./us', $name, $match);
        $len = count($match[0]);
        if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name) || $len > 8 || $len < 2) {
            $msg = 1; // 名字为2-8个汉字
            echo $msg;
            exit;
        }

        // 判断手机号是否为空
        if($mobile==''){
            $msg = 2; // 手机为空
            echo $msg;
            exit;
        }
        // 判断此手机号是否已经申请过
        $sql = "select * from ".DB_PREFIX."loan where mobile=$mobile";
        $res = $GLOBALS['db']->getRow($sql);
        if($res){
            $msg = 6; // 手机号已经申请过
            echo $msg;
            exit;
        }
        // 判断手机号是否为11为数字
        if(!preg_match("/^1\d{10}$/",$mobile)){
            $msg = 3; // 手机号格式不正确
            echo $msg;
            exit;
        }

        // 判断是否选择性别
        if($sex==""){
            $msg = 4; // 请选择性别
            echo $msg;
            exit;
        }

        // 判断是否阅读安全协议；
        if($safe==0){
            $msg = 5;// 请阅读安全协议
            echo $msg;
            exit;
        }
        es_cookie::set("name",$name);
        es_cookie::set("mobile",$mobile);
        es_cookie::set("sex",$sex);
        es_cookie::set("safe",$safe);
        echo $msg;
    }

    public function step_two(){
        $name = es_cookie::get("name");
        $mobile = es_cookie::get("mobile");
        $sex = es_cookie::get("mobile");
        $safe = es_cookie::get("safe");
        if(!$name || !$mobile || !$sex || !$safe){
            $GLOBALS['tmpl']->assign("module_name",MODULE_NAME);
            $GLOBALS['tmpl']->display("page/loan_step_one.html");
        }
        $GLOBALS['tmpl']->assign("module_name",MODULE_NAME);
        $GLOBALS['tmpl']->assign("module_name",$name);
        $GLOBALS['tmpl']->assign("mobile",$mobile);
        $GLOBALS['tmpl']->assign("sex",$sex);
        $GLOBALS['tmpl']->assign("safe",$safe);
        $GLOBALS['tmpl']->display("page/loan_step_two.html");

    }

    /**
     *
     */
    public function do_step_two(){
        foreach ($_REQUEST as $k => $v) {
            $_REQUEST[$k] = htmlspecialchars(addslashes($v));
        }
        $user_id = 0;
        $name = es_cookie::get("name");
        $mobile = es_cookie::get("mobile");
        $sex = es_cookie::get("sex");
        if($GLOBALS['user_info']['id']){
            $user_id = $GLOBALS['user_info']['id'];
        }else{
            $sql = "select id from ".DB_PREFIX."user where mobile=".$mobile;
            $uid = $GLOBALS['db']->getOne($sql);
            if($uid){
                $user_id=$uid;
            }
        }
        $age = isset($_REQUEST['age']) ? trim($_REQUEST['age']) : '';
        $city = (string)$city = isset($_REQUEST['city']) ? trim($_REQUEST['city']) : '';
        $live_time = (string)$live_time = isset($_REQUEST['live_time']) ? trim($_REQUEST['live_time']) : '';
        $salary = isset($_REQUEST['salary']) ? trim($_REQUEST['salary']) : '';
        $salary_way = isset($_REQUEST['salary_way']) ? trim($_REQUEST['salary_way']) : '';
        $fangdai = isset($_REQUEST['fangdai']) ? trim($_REQUEST['fangdai']) : ''; // 对应数据库house_loan 0 代表无房贷1代表有房贷
        // 对应数据库house_loan_status 房贷已还清or正在还房贷
        $fangdai_status = isset($_REQUEST['fangdai_status']) ? trim($_REQUEST['fangdai_status']) : '';
        // 对应数据库house_loan_pay_time 有房贷且没还清
        $fangdai_status_time = isset($_REQUEST['fangdai_status_time']) ? trim($_REQUEST['fangdai_status_time']) : '';
        // 对应数据库house_loan_days 有房贷且还清了
        $fangdai_status_num = isset($_REQUEST['fangdai_status_num']) ? trim($_REQUEST['fangdai_status_num']) : '';
        $chedai = isset($_REQUEST['chedai']) ? trim($_REQUEST['chedai']) : '';// 对应数据库car_loan 0代表无房贷1代表有房贷
        // 对应数据库的car_loan_status 车贷已还清or正在还车贷
        $chedai_status = isset($_REQUEST['chedai_status']) ? trim($_REQUEST['chedai_status']) : '';
        // 对应数据库的car_loan_pay_time 有车贷且没还清
        $chedai_status_time = isset($_REQUEST['chedai_status_time']) ? trim($_REQUEST['chedai_status_time']) : '';
        // 对应数据库的car_loan_days 有车贷且已还清

        $chedai_status_num = isset($_REQUEST['chedai_status_num']) ? trim($_REQUEST['chedai_status_num']) : '';
        $chexian = isset($_REQUEST['chexian']) ? trim($_REQUEST['chexian']) : '';
        $shouxian = isset($_REQUEST['shouxian']) ? trim($_REQUEST['shouxian']) : '';
        $xinyongka= isset($_REQUEST['xinyongka']) ? trim($_REQUEST['xinyongka']) : '';
        $money = (string)$money = isset($_REQUEST['money']) ? trim($_REQUEST['money']) : '';
        foreach($_REQUEST as $val){
            if($val==''){
                echo "请将表单填写完整";
                exit;
            }
        }
        // 将数据写入数据库
        $time = time();
        $sql = "insert into ".DB_PREFIX."loan (`user_id`,`loan_name`,`mobile`,`sex`,`age`,`live_city`,`live_time`,`net_pay`,`pay_way`,`house_loan`,`car_loan`,
        `auto_insurance_policy`,`life_insurance_policy`,`credit_card`,`application_amount`,`apply_time`,`status`,`house_loan_status`,`house_loan_pay_time`,
        `car_loan_status`,`car_loan_pay_time`,`house_loan_days`,`car_loan_days`) values($user_id,'{$name}',$mobile,$sex,$age,'{$city}','{$live_time}','{$salary}',$salary_way,$fangdai,$chedai,$chexian,$shouxian,$xinyongka,'{$money}',$time,1,'{$fangdai_status}','{$fangdai_status_num}','{$chedai_status}','{$chedai_status_num}',
        '{$fangdai_status_time}','{$chedai_status_time}')";
        $res = $GLOBALS['db']->query($sql);
        if($res){
            $msg = 1;// 申请成功
            echo $msg;
        }
    }

    public function step_three(){
        $GLOBALS['tmpl']->assign("module_name",MODULE_NAME);
        $GLOBALS['tmpl']->display("page/loan_step_three.html");
    }
}