<?php

namespace home\model;
use base\model\frontend;

/**
 * 前台user_incharge 用户充值公用业务逻辑类
 *
 * @author jxch
 */

class UserIncharge extends frontend{
    //表名
    protected $tableName = 'user_incharge';

    //用户充值
    function doIncharge($user_info,$request,$incharge_source = 1,$is_app = 0){
        if ($user_info['id'] <= 0){
            $status['status'] = 0;
            $status['info'] = "未登录";
            return $status;
        }

        //验证充值金额
        $money = floatval($request['money']*100);
        if($money<=0){
            $status['status'] = 0;
            $status['info'] = "请输入正确的充值金额";
            return $status;
        }

        //添加充值信息
        $incharge_data['user_id']     = intval($user_info["id"]);
        $incharge_data['notice_sn']   = date("YmdHis").rand(10000,99999);
        $incharge_data['money_e2']    = $money;
        $incharge_data['payment_id']  = $request['payment'];
        $incharge_data['status']      = 0;
        $incharge_data['create_time'] = time();
        $incharge_data['create_date'] = date('Y-m-d');
        $incharge_data['resp_desc']   = "充值中断";

        //保存用户充值记录
        $incharge_id = $this->add($incharge_data);
        if($incharge_id){
            $fuyou = D("base/fuyou");
            $result = $fuyou->get_user_incharge_code($request,$incharge_id,$is_app);
            $status["status"] = 1;
            $status["info"] = "富友充值申请成功";
            $status["html_content"] = $result["code"];
        }else{
            $status["status"] = 0;
            $status["info"] = "富友充值失败";
        }
        return $status;
    }

    //用户充值回调
    function doInchargeNotify($request){
        //将回调信息保存到回调日志表里
        $callback_data['order_sn'] = $request['mchnt_txn_ssn'];
        $callback_data['callback_time'] = time();
        $callback_data['callback_date'] = date("Y-m-d");
        $callback_data['type'] = 1;
        $callback_data['amt'] = $request['amt'];
        $callback_data['rem'] = $request['rem'];
        $callback_data['login_id'] = $request['login_id'];
        $callback_data['resp_code'] = $request['resp_code'];
        $callback_data['resp_desc'] = $request['resp_desc'];
        $callback_data['signature'] = $request['signature'];
        $callback_data['info_code'] = json_encode($request);
        
        //验证签名是否被篡改
        $reArr = $request;
        $signature = $reArr["signature"];
        unset($reArr['PATH_INFO']);
        unset($reArr['signature']);
        unset($reArr['resp_desc']);
        ksort($reArr);
        $str = '';
        foreach ($reArr as $val) {
            $str .= $val . '|';
        }
        $str = substr($str, 0, -1);
        $fuyou = D("base/fuyou");
        if ($fuyou->rsaVerify($str, PUBLIC_PATH.'fuyou_key/php_pbkey.pem',$signature)) {
            //找到订单记录
            $incharge_info = $this->where("notice_sn = '".$request["mchnt_txn_ssn"]."'")->find();
            if($incharge_info){
                $callback_data['user_id'] = $incharge_info['user_id'];
                //验证信息是否被篡改
                $account = M('User')->where(array("id"=>$incharge_info['user_id']))->getField('fuiou_account');
                if($request['amt'] == $incharge_info['money_e2'] && $request['login_id'] ==$account){
                    //判断是否充值成功
                    if($request['resp_code'] == '0000'){
                        //更新订单信息
                        $update['id'] = $incharge_info['id'];
                        $update['status'] = 1;
                        $update['update_time'] = time();
                        $update['resp_desc'] = "充值成功";
//                        M('user_incharge')->where(array('id'=>$incharge_info['id']))->save($update);
                        M('user_incharge')->save($update);
                        //将回调信息保存到充值回调表中
                        $callback_data['status']      = 1;
                        M("user_incharge_carry_log")->add($callback_data);
                        
                        //先注释掉 充值成功后才进行资金修改同步
                        //$user_incharge_info = $this->find($incharge_info['id']);
                        //modify_account(array('money_e2' => $user_incharge_info['money_e2']), $user_incharge_info['user_id'], "会员充值", 1);
                    }else{
                        //更新订单信息
                        $update['id'] = $incharge_info['id'];
                        $update['status'] = 2;
                        $update['resp_desc'] = $request['resp_desc'];
                        $this->save($update);
                        
                        //将回调信息保存到充值回调表中 
                        $callback_data['status']      = 2;
                        M("user_incharge_carry_log")->add($callback_data);
                        //记录日志
//                        $a = $fuyou->getXml('0003', $fuyou->fuyou_mchnt_cd, $request['mchnt_txn_ssn']);
//                        file_put_contents(PUBLIC_PATH . 'log/fuyou/' . date('Y-m-d') . '_carryCallBack.log', "POST:[" . json_encode($request) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
//                        die();
                    }
                }else{
                    $callback_data['status'] = 3;
                    M("user_incharge_carry_log")->add($callback_data);
                }
            }else{
                $callback_data['status'] = 4;
                M("user_incharge_carry_log")->add($callback_data);
            }
        }else{
            $callback_data['status'] = 5;
            M("user_incharge_carry_log")->add($callback_data);
        }   
    }

    //获取所有充值记录
    function getInchargeList($request,$user_info,$_order_by = "create_time desc",$condition = []){
        //取得满足条件的记录数
        $condition['user_id'] = $user_info['id'];
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($request ['listRows'])) {
                $listRows = $request ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $incharge_list = $this->where($condition)->order($_order_by)->limit($p->firstRow . ',' . $p->listRows)->select();
            
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['incharge_list'] = $incharge_list;
        }

        //充值回调成功与否状态提示信息
        if($request['resp_code']){
            $return['resp_desc'] = $this->where("notice_sn = '".$request["mchnt_txn_ssn"]."'")->getField('resp_desc');
        }
        return $return;
    }
}