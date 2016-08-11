<?php

/**
 * 客户端消息推送
 */
class MsgPushAction extends CommonAction
{
    protected $iosKey           = "55bf001767e58e01970023e7"; 
    protected $iosSecret        = "fzrzie1cp58sknjx3olkkrsbpnwgmk0z";
    protected $androidKey       = "559c8af167e58ea610007171";
    protected $androidSecret    = "gbjpkpnzdjkvpcpzdsyfyaogfip3bydm";
    //消息列表展示
    function index(){
        $model = M('device_msg');
        $map = array();
        
        $this->_list($model,$map);
        $this->display();
    }

    function add(){
        if($_POST){
            $msg['msg_type']    = $_POST['msg_type'];     //消息推送类型
            $msg['type']        = $_POST['type'];         //参数
            $msg['data']        = trim($_POST['data']);   //参数值   
            $msg['content']     = trim($_POST['content']);//消息内容
            $msg['create_time'] = time();                 //发送时间

            $msg['user_id']     = $_POST['user_id'] ? trim($_POST['user_id']) : '';  //用户ID
            $msg['title']       = $_POST['title'] ? trim($_POST['title']) : ''; //安卓需要的标题
            
            $log_id = M('device_msg')->add($msg);
            if($log_id){
                $msg['id']      = $log_id;
                switch($msg['msg_type']){
                    case 1:
                        $status = $this->broadcast($msg);
                        $update_data['id'] = $log_id;
                        $update_data['update_time'] = time();
                        if ($status['ios'] = 1 && $status['android'] = 0) {
                            $update_data['status'] = 2;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("IOS消息发送成功，Android消息发送失败");
                        }elseif ($status['ios'] = 0 && $status['android'] = 1) {
                            $update_data['status'] = 3;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("IOS消息发送失败，Android消息发送成功");
                        }elseif ($status['ios'] = 1 && $status['android'] = 1) {
                            $update_data['status'] = 1;
                            $update_id = M('device_msg')->save($update_data);
                            $this->redirect('MsgPush/index','',1, '消息推送中...');
                        }else{
                            $update_data['status'] = 0;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("消息发送失败");
                        }
                        break;
                    case 2:     //苹果广播
                        $status = $this->iosBroadcast($msg);
                        $update_data['update_time'] = time();
                        $update_data['id'] = $log_id;
                        
                        if($status){
                            $update_data['status'] = 1;
                            $update_id = M('device_msg')->save($update_data);
                            $this->redirect('MsgPush/index','',1, '消息推送中...');
                        }else{
                            $update_data['status'] = 0;
                            $update_id = M('device_msg')->where(array("id"=>$log_id))->save($update_data);
                            $this->error("消息发送失败");
                        }
                        break;
                    case 3:
                        $status = $this->androidBroadcast($msg);
                        $update_data['update_time'] = time();
                        $update_data['id'] = $log_id;
                        
                        if($status){
                            $update_data['status'] = 1;
                            $update_id = M('device_msg')->save($update_data);
                            $this->redirect('MsgPush/index','',1, '消息推送中...');
                        }else{
                            $update_data['status'] = 0;
                            $update_id = M('device_msg')->where(array("id"=>$log_id))->save($update_data);
                            $this->error("消息发送失败");
                        }
                    case 4:     //列播
                        $status = $this->unicast($msg);
                        $update_data['update_time'] = time();
                        $update_data['id'] = $log_id;
                        
                        if ($status['ios'] = 1 && $status['android'] = 0) {
                            $update_data['status'] = 2;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("IOS消息发送成功，Android消息发送失败");
                        }elseif ($status['ios'] = 0 && $status['android'] = 1) {
                            $update_data['status'] = 3;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("IOS消息发送失败，Android消息发送成功");
                        }elseif ($status['ios'] = 1 && $status['android'] = 1) {
                            $update_data['status'] = 1;
                            $update_id = M('device_msg')->save($update_data);
                            $this->redirect('MsgPush/index','',1, '消息推送中...');
                        }elseif (($status['ios'] = 1 && $status['android'] = 2) || ($status['ios'] = 2 && $status['android'] = 1)) {
                            $update_data['status'] = 1;
                            $update_id = M('device_msg')->save($update_data);
                            $this->redirect('MsgPush/index','',1, '消息推送中...');
                        }else{
                            $update_data['status'] = 0;
                            $update_id = M('device_msg')->save($update_data);
                            $this->error("所选用户手机设备号不存在");
                        }
                        break;
                }
            }else{
               $this->error("消息发送失败"); 
            }  
        }else{
            $this->display();
        }
    }    

    function inquiry(){
        $id = $_GET['id'];
        if($id){
            $list = M('device_msg')->where(array('id'=>$id))->find();
            //格式化数据
            $data = $this->getInqList($list);
            $this->assign('vo',$data);
        }
        $this->display();
    }
    
    function check(){
        $id = $_POST['id'];
        $device_type = $_POST['device_type'];

//        $check_data = M('device_msg_log')->where(array('log_id'=>$id,'device_type'=>$device_type))->field(array('msg_id','check_ret','check_code'))->find();
        $check_data = M('device_msg_log')->where(array('log_id'=>$id,'device_type'=>$device_type))->field(array('msg_id'))->find();
        if($check_data){
            /**
           if($check_data['check_ret']){
                $data = $this->getCheckList($check_data);
                $result['status'] = 1;
                $result['info'] = $data;
                ajax_return($result);
           }
             * 
             */
            if($check_data['msg_id']){
               //调用友盟接口查询
               require_once APP_ROOT_PATH . "system/umeng/umeng.php";
               //判断是苹果还是安卓，传不同的key和value
               if($device_type ==1){
                   $checkMsg = new UMeng($this->androidKey,$this->androidSecret);
               }else{
                   $checkMsg = new UMeng($this->iosKey,$this->iosSecret);
               }
               $data = $checkMsg->checkMsg($check_data['msg_id']);
               $msg_log_data['check_code']     = $data["code"];
               
               $info  = json_decode($data['info'],true); 
               if($info['ret'] == "SUCCESS"){
                    $msg_log_data['check_ret']     = 1;

                    $data = $this->getCheckList($msg_log_data);
                    $result['status'] = 1;
                    $result['info'] = $data;
                }else{
                    $msg_log_data['check_ret'] = 0;
                    $msg_log_data['check_error_code'] = $info['data']['error_code'];
                    
                    $result['status'] = 0;
                }
//                $status = M('device_msg_log')->where(array('msg_id'=>$check_data['msg_id']))->save($msg_log_data);
           }   
        }else{
           $result['status'] = 0;  
        }
        ajax_return($result);
    }
    

    //全部广播
    private function broadcast($msg){
        $status['ios']      = $this->iosBroadcast($msg);
        $status['android']  = $this->androidBroadcast($msg);
        return $status;
    }
    
    //苹果广播
    private function iosBroadcast($msg){
        //先将数据保存到device_msg_log表里
        $msg_log_data['log_id'] = $msg['id'];
        $msg_log_data['device_type'] = 2;
        $insert_id = M('device_msg_log')->add($msg_log_data);
        if($insert_id){
            //友盟
            require_once APP_ROOT_PATH . "system/umeng/umeng.php";
            $pushMsg = new UMeng($this->iosKey,$this->iosSecret);
            $data = $pushMsg->sendIOSBroadcast($msg,$insert_id);
            
            $msg_log_data['id'] = $insert_id;
            $msg_log_data['update_time'] = time();
            $msg_log_data['info_code'] = $data['code'];

            if($data['status']){
                $info  = json_decode($data['info'],true); 
                if($info['ret'] = "SUCCESS"){
                    $msg_log_data['ret'] = 1;
                    $msg_log_data['msg_id'] = $info['data']['task_id'];

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
    
    //安卓广播
    private function androidBroadcast($msg){
        //先将数据保存到device_msg_log表里
        $msg_log_data['log_id'] = $msg['id'];
        $msg_log_data['device_type'] = 1;
        $insert_id = M('device_msg_log')->add($msg_log_data);
        if($insert_id){
            //友盟
            require_once APP_ROOT_PATH . "system/umeng/umeng.php";
            $pushMsg = new UMeng($this->androidKey,$this->androidSecret);
            $data = $pushMsg->sendAndroidBroadcast($msg,$insert_id);

            $msg_log_data['id'] = $insert_id;
            $msg_log_data['update_time'] = time();
            $msg_log_data['info_code'] = $data['code'];
            if($data['status']){
                $info  = json_decode($data['info'],true); 
                if($info['ret'] = "SUCCESS"){
                    $msg_log_data['ret'] = 1;
                    $msg_log_data['msg_id'] = $info['data']['task_id'];

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
    
   //列播
    private function unicast($msg){
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
        
        return $status;
        
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
    
    //格式化查询数据
    private function getInqList($list){
        //消息ID
        if($list['id']){
            $data['id'] = $list['id'];
        }
        
        //用户
        if($list['user_id']){
            for($i=1;$i<3;$i++){   // 1 安卓 2 苹果
                $user[$i] = M("device_push")->where(array('user_id'=>array("in",$list['user_id']),'device_type'=>$i))->field('user_id')->select();
            }
            foreach($user as $key=>$val){
                foreach($val as $k=>$v){
                    $user_info =  M("User")->where("id=".$v['user_id']." and is_delete = 0")->Field("user_name,real_name,user_type,mobile")->find();
                    $user_arr[$key][$k] = ($k+1)."&nbsp;&nbsp;<a href=".u("User/".($user_info['user_type']==0? "index" : "company_index"),array("user_name"=>$user_info['user_name']))." target='_blank'>".$user_info['user_name'].($user_info['real_name']!="" ? "[".$user_info['real_name']."]"."[".$user_info['mobile']."]":"")."</a>";
                }
                $user_string[$key]=implode('<br/>',$user_arr[$key]);
            }
            $data['user'] = $user_string;
        }
        
        //消息类型
        if($list['msg_type']){
            $data['msg_type'] = $list['msg_type'];
        }

        //参数
        if($list['type']){
            switch($list['type']){
                case 1:
                    $data['type'] = '网址';
                    break;
                case 2:
                    $data['type'] = 'Banner';
                    break;
                case 3:
                    $data['type'] = '标的ID';
                    break;
                case 4:
                    $data['type'] = '项目列表';
                    break;
            }
        }

        //参数值
        if($list['data']){ 
            $data['data'] = $list['data'];
        }
        
        //标题
        if($list['title']){ 
            $data['title'] = $list['title'];
        }
        
        //内容
        $data['content'] = $list['content'];
        
        //发送时间
        $data['update_time'] = $list['update_time']?date('Y-m-d H:i:s',$list['update_time']):'';
        return $data;
    }

    //格式化状态数据
    private function getCheckList($list){
        $check_code = json_decode($list['check_code'],true);
        $info = json_decode($check_code['info'],true);
        $data = $info['data'];
        if($data['status']){
            switch($data['status']){
                case 0: 
                    $data['check_status'] = '排队中';
                    break;
                case 1:
                    $data['check_status'] = '发送中';
                    break;
                case 2:
                    $data['check_status'] = '发送完成';
                    break;
                case 3:
                    $data['check_status'] = '发送失败';
                    break;
                case 4:
                    $data['check_status'] = '消息被撤销';
                    break;
                case 5:
                    $data['check_status'] = '消息过期';
                    break;
                case 6:
                    $data['check_status'] = '筛选结果为空';
                    break;
                case 7:
                    $data['check_status'] = '定时任务尚未开始处理';
                    break;
                default:
                    $data['check_status'] = '请稍后重新查询';
            }
            
        }
        return $data;
    }
}