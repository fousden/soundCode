<?php

namespace home\model;

use base\model\frontend;

/**
 * 前台user 公用业务逻辑类
 *
 * @author jxch
 */
class User extends frontend {

    //表名
    protected $tableName = 'user';

    public function getUserInfoById($id) {
        $where['is_delete'] = 0;
        $where['is_effect'] = 1;
        $where['is_black'] = 0;
        $where['id'] = $id;
        return $this->where($where)->find();
    }

    public function recoverUserMoney($user_id,$money){
        $user_data['id']=$user_id;
        $user_data['money_e2']=$money;
    }

    public function resetPwd($arr){
        $old_pwd = !empty($arr['old_pwd']) ? md5(trim($arr['old_pwd'])) : '';
        $new_pwd = !empty($arr['new_pwd']) ? md5(trim($arr['new_pwd'])) : '';
        $renew_pwd = !empty($arr['renew_pwd']) ? md5(trim($arr['renew_pwd'])) : '';
        // 判断是否有空的
        if($old_pwd==''){
            return array_return("旧密码不能为空");
        }
        if($new_pwd==''){
            return array_return("新密码不能为空");
        }
        if($renew_pwd==''){
            return array_return("此处不能为空");
        }
        // 先判断旧的密码是否正确
        $user_id = $_SESSION['user_info']['user_id'];
        $user_pwd = D("user")->where("id=".$user_id)->getField("user_pwd");
        if($old_pwd!=$user_pwd){
            return array_return("旧密码输入错误");
        }
        // 再判断新旧密码是不是一样的
        if($old_pwd==$new_pwd){
            return array_return("新密码不能与旧密码一样");
        }
        // 判断两次输入的新密码是否一致
        if($new_pwd!=$renew_pwd){
            return array_return("两次输入的密码不一致");
        }
        // 判断新密码是不是符合规则
        $user_pwd = $arr['new_pwd'];
        $match = checkPassword($user_pwd);
        if(!$match){
            return array_return("长度在6~16之间，至少包含字符、数字和下划线两种组合。");
        }

        // 将密码写进数据库
        $data['user_pwd'] = $new_pwd;
        $res = D("user")->where("id=".$user_id)->save($data);
        if($res){
            return array_return("修改成功",'1');
        }else{
            return array_return("修改失败");
        }
    }

    // 设置支付密码时候的手机验证码
    function getCode(){
        //获取验证码
        $mobile = $_SESSION['user_info']['mobile'];
        $status = send_msg($mobile);
        if($status){
            return array_return("发送验证码成功","1");
        }else{
            return array_return("发送验证码失败");
        }
    }

    function checkSubmit($arr){
        $pay_pwd = isset($arr['paypassword']) ? trim($arr['paypassword']) : '' ;
        $repay_pwd = isset($arr['rpaypassword']) ? trim($arr['rpaypassword']) : '' ;
        $code= isset($arr['verify']) ? trim($arr['verify']) : '' ;
        $mobile = $_SESSION['user_info']['mobile'];
        // 判断是否有空的
        if($pay_pwd==''){
            return array_return("支付密码不能为空");
        }
        if($repay_pwd==''){
            return array_return("确认密码不能为空");
        }
        if($code==''){
            return array_return("手机验证码不能为空");
        }
        // 判断手机验证码是否正确
        $status = verify_msg($mobile,$code);
        if(!$status){
            return array_return("手机验证码不正确");
        }
        $user_id = $_REQUEST['user_info']['user_id'];
        $data['pay_pwd'] = md5($pay_pwd);
        $res = D("user")->where("id=".$user_id)->save($data);
        if($res){
            return array_return("修改支付密码成功");
        }else{
            return array_return("修改支付密码失败");
        }
    }

    public function getCollection($user_id,$limit){
        // 搜索出该用户关注的标的id
        $deal_id_arr = M("collection")->field("deal_id")->where("user_id=".$user_id)->limit($limit)->select();
        $count = M("collection")->field("deal_id")->where("user_id=".$user_id)->count();
        foreach($deal_id_arr as $val){
            $deal_id[] = $val['deal_id'];
        }
        // 查出标的id的具体信息
        foreach($deal_id as $val){
            $deal_info = M("deal")->where("id=".$val)->find();
            $list[] = $deal_info;
        }
        // 处理需要的数据
        foreach($list as $k=>$v){
            $list[$k]['borrow_amount_format'] = format_price($v['borrow_amount_e2']);
            $list[$k]['borrow_amount_format'] = format_price($v['borrow_amount_e2'] / 10000) . "万"; //format_price($deal['borrow_amount']);
            $list[$k]['rate_foramt_w'] = number_format($v['rate'], 2) . "%";

            $list[$k]['rate_foramt'] = number_format($v['rate'], 2);
            //本息还款金额
            $list[$k]['month_repay_money'] = format_price(pl_it_formula($v['borrow_amount_e2'], $v['rate'] / 12 / 100, $v['repay_time']));
            //还需多少钱
            $list[$k]['need_money'] = format_price($v['borrow_amount_e2'] - $v['load_money_e2']);
            //百分比
            if ($v['deal_status'] == 4) {
                $list[$k]['month_repay_money'] = pl_it_formula($v['borrow_amount_e2'], $v['rate'] / 12 / 100, $v['repay_time']);
                $list[$k]['remain_repay_money'] = $list[$k]['month_repay_money'] * $v['repay_time'];
                $list[$k]['progress_point'] = (int) ($v['repay_money_e2'] / $list[$k]['remain_repay_money'] * 100);
            } else {
                $list[$k]['progress_point'] = (int) ($v['load_money_e2'] / $v['borrow_amount_e2'] * 100);
            }

            $user_location = M("region_conf")->where("id=".intval($v['city_id']))->find();
            if ($user_location == ''){
                $user_location = M("region_conf")->where("id=".intval($v['province_id']))->find();
            }
            $list[$k]['user_location'] = $user_location;
            $list[$k]['point_level'] = M("user_level")->where("id=".intval($v['level_id']))->find();
//            $durl = "/index.php?ctl=deal&act=mobile&is_sj=1&id=" . $v['id'];
//            $list[$k]['app_url'] = str_replace("/mapi", "", SITE_DOMAIN . $durl);
        }

        return array("list" => $list, 'count' => $count);
    }
}
