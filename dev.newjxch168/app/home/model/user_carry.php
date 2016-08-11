<?php

namespace home\model;
use base\model\frontend;

/**
 * 前台user_carry 用户提现公用业务逻辑类
 *
 * @author jxch
 */

class UserCarry extends frontend{
    //表名
    protected $tableName = 'user_carry';

    //用户提现
    function doCarry($user_info,$request,$carry_source = 1,$is_app = 0){
        if ($user_info["id"] > 0) {
            $pay_pwd = trim($request["pay_pwd"]);
            $money = floatval($request["money"] * 100);
            //验证密码是否为空
            if (!$pay_pwd) {
                $status['status'] = 0;
                $status['info'] = "请输入支付密码";
                return $status;
            }
            //验证支付密码
            if (md5($pay_pwd) != $user_info['pay_pwd']) {
                $status['status'] = 0;
                $status['info'] = "支付密码错误";
                return $status;
            }
            //验证是否绑定银行卡
            if (!$user_info["bank_card"]) {
                $status['status'] = 0;
                $status['info'] = "尚未绑定银行卡";
                return $status;
            }
            //验证提现金额
            if ($money <= 0) {
                $status['status'] = 0;
                $status['info'] = "请输入正确的提现金额";
                return $status;
            }

            //判断提现金额限制 是否超过账户可用余额
            if ($money > floatval($user_info['money_e2'])) {
                $status['status'] = 0;
                $status['info'] = "请输入正确的提现金额";
                return $status;
            }
            //添加提现信息
            $carry_data['user_id'] = intval($user_info["id"]);
            $carry_data['money_e2'] = $money;
            $carry_data['bank_id'] = $user_info['bank_id'];
            $carry_data['bank_name'] = $user_info['bank_name'];
            $carry_data['real_name'] = $user_info['real_name'];
            $carry_data['region_lv1'] = intval($user_info['region_lv1']);
            $carry_data['region_lv2'] = intval($user_info['region_lv2']);
            $carry_data['region_lv3'] = intval($user_info['region_lv3']);
            $carry_data['region_lv4'] = intval($user_info['region_lv4']);
            $carry_data['bank_zone'] = trim($user_info['bank_zone']);
            $carry_data['bank_card'] = trim($user_info['bank_card']);
            $carry_data['carry_source'] = $carry_source;
            $carry_data['create_time'] = time();
            $carry_data['create_date'] = date("Y-m-d",$carry_data['create_time']);
            //先保存用户提现记录
            $carry_id = $this->add($carry_data);
            //如果保存成功 发起提现支付 分移动端 和 网站端
            if($carry_id){
                //发起富友账户提现 富友提现申请
                $fuyou = D("base/fuyou");
                $result = $fuyou->get_user_carry_code($request,$carry_id,$is_app);
                //更新提现流水号
                $update_data["serial_num"] = $result['mchnt_txn_ssn'];
                $update_id = $this->where(array("id"=>$carry_id))->save($update_data);//流水号
                $status["status"] = 1;
                $status["info"] = "富友提现申请成功";
                $status["html_content"] = $result["code"];
            }else{
                $status["status"] = 0;
                $status["info"] = "提现数据保存失败";
            }
            return $status;
        } else {
            $status['status'] = 0;
            $status['info'] = "未登录";
        }
        return $status;
    }

    //用户提现回调
    function cash_notify($request)
    {
        //将回调信息保存到回调日志表里
        $callback_data['order_sn'] = $request['mchnt_txn_ssn'];
        $callback_data['callback_time'] = time();
        $callback_data['callback_date'] = date("Y-m-d");
        $callback_data['type'] = 2;
        $callback_data['amt'] = $request['amt'];
        $callback_data['rem'] = $request['rem'];
        $callback_data['login_id'] = $request['login_id'];
        $callback_data['resp_code'] = $request['resp_code'];
        $callback_data['resp_desc'] = $request['resp_desc'];
        $callback_data['signature'] = $request['signature'];
        $callback_data['info_code'] = json_encode($request);
        M("user_incharge_carry_log")->add($callback_data);
        
        //富友对象
        $fuyou = D("base/fuyou");
        //用户提现信息
        $reArr = $request;
        unset($request['PATH_INFO']);//删除PATH_INFO信息
        unset($request['signature']);
        unset($request['resp_desc']);

        if ($request['resp_code'] != '0000') {
            //记录日志
            $a = $fuyou->getXml('0003', $fuyou->fuyou_mchnt_cd, $reArr['mchnt_txn_ssn']);
            file_put_contents(PUBLIC_PATH . 'log/fuyou/' . date('Y-m-d') . '_carryCallBack.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
        //提现单信息
        $user_carry_info = $this->where(array("serial_num"=>trim($reArr['mchnt_txn_ssn'])))->find();
        //验证金额
        if($reArr['amt'] != $user_carry_info['money_e2'] || !$user_carry_info){
            //记录错误日志 被篡改
            $a = $fuyou->getXml('0003', $fuyou->fuyou_mchnt_cd, $reArr['mchnt_txn_ssn']);
            file_put_contents(PUBLIC_PATH . 'log/fuyou/' . date('Y-m-d') . '_carryCallBack.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }

        ksort($request);
        $str = '';
        foreach ($request as $val) {
            $str .= $val . '|';
        }
        $str = substr($str, 0, -1);
        //验证签名是否被篡改
        if ($fuyou->rsaVerify($str, PUBLIC_PATH.'fuyou_key/php_pbkey.pem', $reArr['signature'])) {
            //更新提现状态
            $carry_data['status'] = 1;
            $carry_data['update_time'] = time();
            $carry_data['resp_desc'] = $reArr['resp_desc'];
            $this->where(array("id"=>$user_carry_info['id']))->data($carry_data)->save();
            //先注释掉 提现成功后才进行资金修改同步
            modify_account(array('money_e2' => -$user_carry_info['money_e2']), $user_carry_info['user_id'], "提现申请", 8);
            //短信通知用户提现 待开发

            //记录日志
            $a = $fuyou->getXml('0000', $fuyou->fuyou_mchnt_cd, $reArr['mchnt_txn_ssn']);
            file_put_contents(PUBLIC_PATH . 'log/fuyou/' . date('Y-m-d') . '_carryCallBack.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        } else {
            //更新提现状态
            $carry_data['resp_desc'] = $reArr['resp_desc'];
            $this->where(array("id"=>$user_carry_info['id']))->data($carry_data)->save();
            //记录日志
            $a = $fuyou->getXml('0001', $fuyou->fuyou_mchnt_cd, $reArr['mchnt_txn_ssn']);
            file_put_contents(PUBLIC_PATH . 'log/fuyou/' . date('Y-m-d') . '_carryCallBack.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            die();
        }
    }

    //获取所有提现记录
    function getCarryList($request,$_order_by = "create_time desc",$condition = []){
        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($request ['listRows'])) {
                $listRows = $request ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $carry_list = $this->where($condition)->order($_order_by)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['carry_list'] = $carry_list;
        }
        return $return;
    }
}

