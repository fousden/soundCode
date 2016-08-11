<?php

namespace home\model;

use base\model\base;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/19
 * Time: 10:24
 */
class System extends base {

    public function do_ajax($arr) {
        $act = isset($arr) ? trim($arr['act']) : '';
        if ($act == "check_user_name") {
            $user_name = isset($arr['user_name']) ? trim($arr['user_name']) : '';
            $condition['user_name'] = $user_name;
            $id = D("user")->where($condition)->getField("id");
            if ($id) {
                return array_return("用户名被占用");
            } else {
                return array_return("可以使用","1");
            }
        }
        if ($act == "check_mobile") {
            $mobile = isset($arr['mobile']) ? trim($arr['mobile']) : '';
            $condition['mobile'] = $mobile;
            $id = D("user")->where($condition)->getField("id");
            if ($id) {
                return array_return("手机号已经被占用");
            } else {
                return array_return("可以使用","1");
            }
        }
        if ($act == "get_register_verify_code") {
            $mobile = isset($arr['mobile']) ? trim($arr['mobile']) : '';
            $status = send_msg($mobile);
            if ($status == 1) {
                return array_return("验证码发送成功","1");
            }else{
                return array_return("验证码发送失败");
            }
        }

        if ($act == "verify_msg") {
            $verify_code = isset($arr['verify_code']) ? trim($arr['verify_code']) : '';
            $mobile = isset($arr['mobile']) ? trim($arr['mobile']) : '';
            $status =verify_msg($mobile, $verify_code);
            if ($status == 1) {
                return array_return("验证成功","1");
            }
            if ($status == 0) {
                return array_return("验证失败");
            }
        }

        if ($act == "check_referer") {
            $referer = isset($arr['referer']) ? trim($arr['referer']) : '';
            $condition = "mobile='" . $referer . "' or user_name='" . $referer . "'or real_name='" . $referer . "'";
            $id = D("user")->where($condition)->getField("id");
            if ($id) {
                return array_return("推荐人信息正确","1");
            } else {
                return array_return("您填写的推荐人信息在本平台不存在，此次输入作为备注。");
            }
        }

        if ($act == "get_city") {
            $id = isset($arr['id']) ? trim($arr['id']) : '';
            $condition['parentcode'] = $id;
            $city_list = D("district_info")->where($condition)->order("districtcode asc")->select();
            return $city_list;
        }

        if ($act == "check_idno") {
            $idno = isset($arr['idno']) ? trim($arr['idno']) : '';
            $condition['idno'] = $idno;
            $id = D("user")->where($condition)->getField("id");
            if ($id) {
                return array_return("身份证号码已被占用");
//                return "idno_0"; // 改身份证号已被占用
            } else {
                return array_return("可以使用","1");
//                return "idno_1"; // 可以使用
            }
        }
    }

    public function do_register($arr) {
        $data['user_type'] = $user_type = isset($arr['user_type']) ? trim($arr['user_type']) : '';
        $data['user_name'] = $user_name = isset($arr['user_name']) ? trim($arr['user_name']) : '';
        $data['fuiou_account'] = $data['mobile'] = $mobile = isset($arr['mobile']) ? trim($arr['mobile']) : '';
        $sms_code = isset($arr['sms_code']) ? trim($arr['sms_code']) : '';
        $data['user_pwd'] = $user_pwd = isset($arr['user_pwd']) ? md5(trim($arr['user_pwd'])) : '';
        $user_pwd_confirm = isset($arr['user_pwd_confirm']) ? md5(trim($arr['user_pwd_confirm'])) : '';
        $data['referer'] = $referer = isset($arr['referer']) ? trim($arr['referer']) : '';
        $agreement = isset($arr['agreement']) ? trim($arr['agreement']) : '';
        // 做PHP层面的判断
        foreach ($arr as $k => $v) {
            $arr[$k] = htmlspecialchars(addslashes($v));
        }

        if ($user_name == '') {
            // 判断用户名是否为空
            return array_return("用户名不能为空");
        }
        // 判断user_name是否使用过
        $condition['user_name'] = $user_name;
        $id = D("user")->where($condition)->getField("id");
        if ($id) {
            return array_return("用户名被占用");
        }

        if ($user_pwd == '') {
            // 判断密码是否为空
            return array_return("密码不能为空");
        }
        if ($user_pwd != $user_pwd_confirm) {
            // 判断两次密码是否一致
            return array_return("两次密码不一致");
        }
        // 判断密码是否符合规则
        $check_user_pwd = $arr['user_pwd'];
        $match = checkPassword($check_user_pwd);
        if(!$match){
            return output("长度在6~16之间，至少包含字符、数字和下划线两种组合。");
        }
        if ($mobile == '') {
            return array_return("手机不能为空");
        }
        //判断手机是否格式正确
        $match = checkMobile($mobile);
        if (!$match) {
            return array_return("手机格式不正确");
        }

        // 判断验证码是否为空
        if ($sms_code == '') {
            return array_return("验证码不能为空");
        }

        // 判断是否选注册协议
        if ($agreement == 0) {
            return array_return("请勾选注册协议");
        }

        // 判断验证是否正确
        $status = verify_msg($mobile,$sms_code);
        if (!$status) {
            return array_return("验证码不正确或者失效");
        }
        // 将数据写入数据库
        $data['create_time'] = time();
        $data['register_ip'] = $_SERVER['SERVER_ADDR'];
        $insert_id = D("user")->add($data);
        $ext_data['user_id'] = $insert_id;
        $ext_data['login_ip'] = $_SERVER['SERVER_ADDR']; // 注册时，登录IP默认为注册IP
        $ext_data['login_time'] = time(); // 注册时，登录时间默认为注册时间
        $insert_ext_id = D("user_extend")->add($ext_data);
        if ($insert_id && $insert_ext_id) {
            $_SESSION['user_info']['user_id'] = $insert_id;
            $_SESSION['user_info']['user_name'] = $user_name;
            $_SESSION['user_info']['pwd'] = $user_pwd;
            $_SESSION['user_info']['mobile'] = $mobile;
            $root['response_code'] = 1;
            return array_return("注册成功","1");
        } else {
            return array_return("注册失败");
        }
    }
    public function do_account($arr) {
        $data['real_name'] = $real_name = isset($arr['real_name']) ? trim($arr['real_name']) : ''; // 写到user表
        $data['idno'] = $idno = isset($arr['idno']) ? trim($arr['idno']) : ''; // user表
        $ext_data['pinan_status'] = $agreement = isset($arr['agreement']) ? trim($arr['agreement']) : '0'; // user_extend是否同意平安保险
        $ext_data['bank_id'] = $bank_id = isset($arr['bank_id']) ? trim($arr['bank_id']) : ''; //user_extend表
        $ext_data['region_lv1'] = $region_lv1 = isset($arr['region_lv1']) ? trim($arr['region_lv1']) : ''; //user_extend表
        $ext_data['region_lv2'] = $region_lv2 = isset($arr['region_lv2']) ? trim($arr['region_lv2']) : ''; //user_extend表
        $ext_data['bank_card'] =$bank_card =isset($arr['bank_card']) ? str_replace(' ', '', trim($arr['bank_card']))  : ''; //user_extend表
        $rebank_card = isset($arr['rebank_card']) ? str_replace(' ', '', trim($arr['rebank_card']))  : '';
        // 处理身份证号隐藏的信息包括年月日性别
        $id_arr = idinfo($idno);
        $ext_data['byear']=$id_arr['byear'];// user_extend表
        $ext_data['bmonth']=$id_arr['bmonth'];// user_extend表
        $ext_data['bday']=$id_arr['bday'];// user_extend表
        $data['sex']=$id_arr['sex']; // user表
        $user_id = $_SESSION['user_info']['user_id'];
        // 用户名是否填写
        if($real_name==' '){
            return array("真实姓名不能为空");
            //return "user_name_0";// 开户名为空
        }

        // 身份证号验证
        if($idno==''){
            return array_return("身份证号码不能为空");
        }

        // 验证是否被占用
        $condition['idno'] = $idno;
        $id = D("user")->where($condition)->select();
        if($id){
            return array_return("身份证被占用");
//            return "idno_-1";// 身份证被占用
        }
        // 验证银行是否选择
        if($bank_id==0){
            return array_return("请先选择银行");
//            return "bank_id_0";// 没选择银行
        }
        // 验证省市是否选择
        if($region_lv1==0 || $region_lv2==0){
            return array_return("请先选择银行开户地");
//            return "region_lv1_0";// 没选择省
        }
        // 验证银行卡
        if($bank_card==' '){
            return array_return("请先选择银行卡");
//            return "bank_card_o";// 没选择银行卡
        }
        if($bank_card != $rebank_card){
            return array_return("两次卡号输入不一致");
//            return "bank_card_-1";// 两次卡号输入不一致
        }


        // 获取富有回调
        $status = D("fuyou")->fuyouRegAction($user_id);
        if($status=='0000'){
            // 将相关数据写入数据库
            $user_res = D("user")->where("id=".$user_id)->save($data);
            $ext_res = D("user_extend")->where("user_id=".$user_id)->save($ext_data);
            if($user_res && $ext_res){
                // 如果插入数据库成功则返回开户成功并且将相关数据写入session
                $_SESSION['user_info']['real_name'] = $real_name;
                $_SESSION['user_info']['idno'] = $idno;
                return array_return("开户成功",$status); // 写入数据库且开户成功
            }else{
                // 如果插入数据库失败
                return array_return("提交信息失败");
            }
        }else{
            // 富有那边开户没通过
            $err_info = get_remind_code_zh($status); // 根据富有错误代码的具体错误信息
            return array_return("开户失败,错误信息为:".$err_info);
        }
    }

    public function check_login($arr){
        $email = isset($arr['email']) ? trim($arr['email']) : ' ';
        $user_pwd = isset($arr['user_pwd']) ? md5(trim($arr['user_pwd']) ): ' ';
        $auto_login = isset($arr['auto_login']) ? trim($arr['auto_login']) : ' ';
        $condition = "user_name='".$email."' or mobile='".$email."' or email='".$email."'";
        $user_info= D("user")->where($condition)->find();
        $user_extend = D("user_extend")->field("id", true)->where(array("user_id"=>$user_info["id"]))->find();
        if(!empty($user_extend)){
            $user_info = array_merge($user_info,$user_extend);
        }
        $real_pwd = $user_info['user_pwd'];
        if($user_pwd == $real_pwd){
            session("user_info",$user_info);
            $_SESSION["user_info"] = $user_info;
            // 如果登录成功
            return array_return("登录成功",'1');
//            return "login_1"; // 登录成功
        }else{
            return array_return("登录失败，请确定用户名和密码是否正确");
//            return "login_0";//  登录失败
        }
    }
}


