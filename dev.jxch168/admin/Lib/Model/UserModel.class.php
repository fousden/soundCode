<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行 后台财务管理模块 业务逻辑处理类
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class UserModel extends CommonModel {

        protected $tableName = 'user';

        //自动验证
        protected $_validate = array(
			  // array("username","require","用户名必须填写!")
		);

        //自动完成
        protected $_auto=array(
                       // array('reg_time','time',1,'function'), //注册时间
        );

        //用户手机号码同步
        public function mobile_synchro($user_id){
            //实现用户平台账户与富友账户银行卡同步
            require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
            $fuyou_payment = new Fuyou_payment();
            //判断是否绑过银行卡
            $old_bank = M("user_bank")->where(array('user_id'=>$user_id))->find();
            if(!$old_bank){
                $result['status'] = 0;
                $result['info'] = '该用户尚未绑卡开户，无法同步!';
                return $result;
            }
            //查询用户信息
            $user_info = $this->where(array('is_effect'=>1,'is_delete'=>0))->getById($user_id);
            //查询客户富有最新银行卡信息
            $fuyou_user_data = $fuyou_payment->getFuYouUserInfo($user_info);
            if ($fuyou_user_data['status'] == 1) {
                //准备数据 更新平台用户银行卡信息
                $mobile_no = $fuyou_user_data['data']['results']['result']['mobile_no'];//手机号码
                $user_idno = $fuyou_user_data['data']['results']['result']['certif_id'];//身份证号码
                if($user_info["idno"] != $user_idno){
                    //用户信息不统一，无法同步d
                    $result['status'] = 0;
                    $result['info'] = '用户身份不统一，无法同步';
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_mobile_synchroAction.log', "post:[" . json_encode($user_info) . "];return:[".json_encode($fuyou_user_data)."];提示信息:["  . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    return $result;
                }
                //判断富友与金享财行账户银行卡是否一致
                if($user_info['mobile'] == $mobile_no){
                    //银行卡信息一致，无需同步
                    $result['status'] = 0;
                    $result['info'] = '手机号码一致，无需同步';
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_mobile_synchroAction.log', "post:[" . json_encode($user_info) . "];return:[".json_encode($fuyou_user_data)."];提示信息:["  . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    return $result;
                }
                $update_data['mobile'] = $mobile_no;
                $update_id = M("user")->where(array("id"=>$user_info["id"]))->save($update_data);
                if($update_id){
                    $result['status'] = 1;
                    $result['info'] = '手机号码同步成功';
                }else{
                    $result['status'] = 0;
                    $result['info'] = '手机号码不一致，数据更新失败，同步失败';
                }
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_mobile_synchroAction.log', "post:[" . json_encode($user_info) . "];return:[".json_encode($fuyou_user_data)."];提示信息:[" .$result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                return $result;
            } elseif ($fuyou_user_data['status'] == 2) {
                //服务器忙 响应失败，请稍后重试！
                $result['status'] = 0;
                $result['info'] = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_mobile_synchroAction.log', "post:[" . json_encode($user_info) . "];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                return $result;
            }
        }
        
        //用户银行卡信息同步
        public function bank_synchro($user_id){
            //实现用户平台账户与富友账户银行卡同步
            require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
            $fuyou_payment = new Fuyou_payment();

            //判断是否绑过银行卡
            $old_bank = M("user_bank")->where(array('user_id'=>$user_id))->find();
            if(!$old_bank){
                $result['status'] = 0;
                $result['info'] = '该用户尚未绑定银行卡，无法同步!';
                return $result;
            }

            //查询用户信息
            $user_info = $this->where(array('is_effect'=>1,'is_delete'=>0))->getById($user_id);
            $fuyou_user_data = $fuyou_payment->getFuYouUserInfo($user_info);
            if ($fuyou_user_data['status'] == 1) {
                //准备数据 更新平台用户银行卡信息
                $mobile_no = $fuyou_user_data['data']['results']['result']['mobile_no'];//手机号码
                $user_idno = $fuyou_user_data['data']['results']['result']['certif_id'];//身份证号码

                $new_bank['user_id'] = $this->where(array('fuiou_account' => $mobile_no))->getField("id");//用户id
                $new_bank['real_name'] = $fuyou_user_data['data']['results']['result']['cust_nm'];//客户姓名
                $new_bank['region_lv1'] = M('district_info')->where(array('DistrictCode' => $fuyou_user_data['data']['results']['result']['city_id']))->getField("ParentCode");//开户行地区代码 省份
                $new_bank['region_lv2'] = $fuyou_user_data['data']['results']['result']['city_id'];//开户行地区代码 区县
                $new_bank['bank_id'] = $fuyou_user_data['data']['results']['result']['parent_bank_id'];//开户行行别
                $new_bank['bankzone'] = $fuyou_user_data['data']['results']['result']['bank_nm'] ? $fuyou_user_data['data']['results']['result']['bank_nm'] : '';//开户行支行名称
                $new_bank['bankcard'] = $fuyou_user_data['data']['results']['result']['capAcntNo'];//卡号 账号
                
                if($old_bank['user_id'] != $new_bank['user_id'] || $user_info["idno"] != $user_idno){
                    //用户信息不统一，无法同步d
                    $result['status'] = 0;
                    $result['info'] = '用户信息不统一，无法同步';
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_bank_synchroAction.log', "new_bank:[" . json_encode($new_bank) . "];old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    return $result;
                }
                //判断富友与金享财行账户银行卡是否一致
                if($old_bank['user_id'] == $new_bank['user_id'] && $old_bank['real_name'] == $new_bank['real_name'] && $old_bank['region_lv1'] == $new_bank['region_lv1'] && $old_bank['region_lv2'] == $new_bank['region_lv2'] && $old_bank['bank_id'] == $new_bank['bank_id'] && $old_bank['bankzone'] == $new_bank['bankzone'] && $old_bank['bankcard'] == $new_bank['bankcard']){
                    //银行卡信息一致，无需同步
                    $result['status'] = 0;
                    $result['info'] = '银行卡信息一致，无需同步';
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_bank_synchroAction.log', "new_bank:[" . json_encode($new_bank) . "];old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:["  . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    return $result;
                }
                $new_bank['id'] = $old_bank['id'];
                $update_id = M("user_bank")->save($new_bank);
                if($update_id){
                    $result['status'] = 1;
                    $result['info'] = '银行卡同步成功';
                }else{
                    $result['status'] = 0;
                    $result['info'] = '银行卡信息不一致，数据更新失败，同步失败';
                }
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_bank_synchroAction.log', "new_bank:[" . json_encode($new_bank) . "];old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                return $result;
            } elseif ($fuyou_user_data['status'] == 2) {
                //服务器忙 响应失败，请稍后重试！
                $result['status'] = 0;
                $result['info'] = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_bank_synchroAction.log', "new_bank:[" . json_encode($new_bank) . "];old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $result['info'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                return $result;
            }
        }
}
?>