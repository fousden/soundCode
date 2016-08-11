<?php

/**
 * 友盟消息推送类
 */
class MessagePush{
    protected $iosKey           = "55bf001767e58e01970023e7"; 
    protected $iosSecret        = "fzrzie1cp58sknjx3olkkrsbpnwgmk0z";
    protected $androidKey       = "559c8af167e58ea610007171";
    protected $androidSecret    = "gbjpkpnzdjkvpcpzdsyfyaogfip3bydm";
    
    /**
     * 
     * @param type $msg['user_id']      用户id
     * @param type $msg['title']        消息标题
     * @param type $msg['content']      消息内容
     * @param type $msg['type']         消息类型  1普通网址  2 banner 3 标的ID  4 项目列表  空 打开应用 
     * @param type $msg['data']         参数值
     */
   
    public function send($msg){
        if( empty($msg['user_id']) || empty($msg['title']) || empty($msg['content']) ){
            return false;
        }
        $msg['msg_type']    = 4;            //消息推送类型,列播
        $msg['create_time'] = time();       //发送时间
        $log_id = M('device_msg')->add($msg);
        if($log_id){
            $msg['id']      = $log_id;
            //获取设备ID和设备型号
            $device_token_data = M('device_push')->where(array('user_id'=>array("in",$msg['user_id'])))->field(array('device_token','device_type'))->select() ;
            foreach($device_token_data as $key=>$val){
                if($val['device_type'] ==1 && $val['device_token'] != ''){
                    $android_token_list[] =$val['device_token'];
                }else if($val['device_type'] ==2 && $val['device_token'] != ''){
                    $ios_token_list[] =$val['device_token'];
                }
            }

            $status['ios'] = 2;
            $status['android'] = 2;
            //如果列表中包含IOS设备，则调用IOS方法推送消息
            if($ios_token_list){
                $status['ios'] = $this->iosUnicast($msg,$ios_token_list);
            }

            //如果列表中包含安卓设备，则调用安卓方法推送消息
            if($android_token_list){
                $status['android'] = $this->androidUnicast($msg,$android_token_list);
            }

            $update_data['update_time'] = time();
            $update_data['id'] = $log_id;     
            if ($status['ios'] = 1 && $status['android'] = 0) {
                $update_data['status'] = 2;   
            }elseif ($status['ios'] = 0 && $status['android'] = 1) {
                $update_data['status'] = 3;
            }elseif ($status['ios'] = 1 && $status['android'] = 1) {
                $update_data['status'] = 1; 
            }elseif (($status['ios'] = 1 && $status['android'] = 2) || ($status['ios'] = 2 && $status['android'] = 1)) {
                $update_data['status'] = 1;   
            }else{
                $update_data['status'] = 0;  
            }
            $update_id = M('device_msg')->save($update_data);
        }  
    }    

    //苹果列播
    private function iosUnicast($msg,$ios_token_list){
        //先将数据保存到device_msg_log表里
        $msg_log_data['log_id'] = $msg['id'];
        $msg_log_data['device_type'] = 2;
        $insert_id = M('device_msg_log')->add($msg_log_data);
        if($insert_id){
            //友盟
            require_once APP_ROOT_PATH . "system/umeng/umeng.php";
            $pushMsg = new UMeng($this->iosKey,$this->iosSecret);
            $device_token=implode(',',$ios_token_list);
            $data  = $pushMsg->sendIOSUnicast($device_token,$msg,$insert_id);

            $msg_log_data['id'] = $insert_id;
            $msg_log_data['update_time'] = time();
            $msg_log_data['info_code'] = $data['code'];

            if($data['status']){
                $info  = json_decode($data['info'],true); 
                if($info['ret'] = "SUCCESS"){
                    $msg_log_data['ret'] = 1;
                    $msg_log_data['msg_id'] = $info['data']['msg_id'];

                    $result = 1;
                }else{
                    $msg_log_data['ret'] = 0;
                    $msg_log_data['error_code'] = $info['data']['error_code'];

                    $result = 0;
                }
            }else{ 
                $result = 0;
            }
            M('device_msg_log')->save($msg_log_data);
        }else{ 
            $result = 0;
        }
        return $result;
    }
    
    //安卓列播
    private function androidUnicast($msg,$android_token_list){
        //先将数据保存到device_msg_log表里
        $msg_log_data['log_id'] = $msg['id'];
        $msg_log_data['device_type'] = 1;
        $insert_id = M('device_msg_log')->add($msg_log_data);
        if($insert_id){
            require_once APP_ROOT_PATH . "system/umeng/umeng.php";
            $pushMsg = new UMeng($this->androidKey,$this->androidSecret);
            $msg['device_token']=implode(',',$android_token_list);
            $data  = $pushMsg->sendAndroidUnicast($msg,$insert_id);

            $msg_log_data['id'] = $insert_id;
            $msg_log_data['update_time'] = time();
            $msg_log_data['info_code'] = $data['code'];

            if($data['status']){
                $info  = json_decode($data['info'],true); 
                if($info['ret'] = "SUCCESS"){
                    $msg_log_data['ret'] = 1;
                    $msg_log_data['msg_id'] = $info['data']['msg_id'];

                    $result = 1;
                }else{
                    $msg_log_data['ret'] = 0;
                    $msg_log_data['error_code'] = $info['data']['error_code'];

                    $result = 0;
                }
            }else{ 
                $result = 0;
            }
            M('device_msg_log')->save($msg_log_data);
        }else{ 
            $result = 0;
        }
        return $result;
    }
    
   
    
    
}