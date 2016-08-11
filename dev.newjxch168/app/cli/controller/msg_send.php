<?php
namespace cli\controller;
use \base\controller\backend;

/**
 * 控制器
 *
 * @author jxch
 */

class MsgSend extends backend{
    function index(){
//        set_time_limit(0);
        $condition['status'] = 0;
        $msg_list = M('msg_send_list')->where($condition)->order('priority,id asc')->limit(10)->select();
        if($msg_list){
            foreach($msg_list as $key=>$val){
                $result = send_sms_mail($val);
//                $result['status'] = 1;
                $update_date['id'] = $val['id'];
                $update_date['send_time'] = time();
                if($result['status']){
                    $update_date['status'] = 1;  
                }else{
                    $update_date['status'] = 2;
                }
                M('msg_send_list')->save($update_date);
            }  
        }
    }
    
    function addMobileBlacklist(){
        //当一个手机号码累计三天都发送了注册验证码加入黑名单
        $mobile_black_list = M('blacklist')->field(array('distinct dest'))->where(array('type'=>1,'is_delete'=>0))->select();
        $mobiles = array_map('array_shift', $mobile_black_list);
        
        $field = 'dest,FROM_UNIXTIME(create_time,"%Y-%m-%d") as date,count(distinct FROM_UNIXTIME(create_time,"%Y-%m-%d")) as c';
        $condition['send_type'] = 0 ;
        $condition['msg_type'] = 1 ;
        $condition['dest'] = array('not in',$mobiles);
        $mobile_list = M('msg_send_list')->field($field)->where($condition)->group('dest')->having('c >= 3')->select();
        if($mobile_list){
            foreach($mobile_list as $key=>$val){
                $data = M('blacklist')->field(array('id','times'))->where(array('dest'=>$val['dest']))->find();
                if($data){
                    $update_data['id'] = $data['id'];
                    $update_data['update_time'] = time();
                    $update_data['times'] = $data['times'] + 1;
                    $update_data['ip'] = '10.10.10.10';//获取IP地址
                    $update_data['is_delete'] = 0;
                    M('blacklist')->save($update_data);
                }else{
                    $insert_data['type'] = 1;
                    $insert_data['dest'] = $val['dest'];
                    $insert_data['create_time'] = time();
                    $insert_data['times'] = 1;
                    M('blacklist')->add($insert_data);
                }
            }
        }
        
    }
}

