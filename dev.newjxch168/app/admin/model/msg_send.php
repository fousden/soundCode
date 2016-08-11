<?php

/**
 * 后台短信邮件模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class MsgSend extends backend{
    //表名
    protected $tableName = 'Msg_send';

    //获取信息列表
    function getMsgList(){
        $condition = array();
        if(@$_REQUEST["dest"]){
            $condition["dest"] = array('like','%'.trim($_POST["dest"]).'%'); 
        }
        if(@$_REQUEST["content"]){
            $condition["content"] = array('like','%'.trim($_POST["content"].'%'));
        }
        $msg_list = $this->_list($this,$condition);
        foreach($msg_list['data_list'] as $key=>$val){
            //如果状态为3（发送中），更新一次状态
            if($val['status'] == 3){
                $list_num = M('msg_send_list')->where(array('msg_id'=>$val['id']))->field('status')->count();
                $list_success = M('msg_send_list')->where(array('msg_id'=>$val['id'],'status'=>1))->field('status')->count();
                $list_fail = M('msg_send_list')->where(array('msg_id'=>$val['id'],'status'=>2))->field('status')->count();
                if($list_num == $list_success){
                    $this->where(array('id'=>$val['id']))->setField("status",1);
                }elseif($list_fail){
                    $this->where(array('id'=>$val['id']))->setField("status",2);
                }
            } 
        }
        //数据渲染
        $msg_list['data_list'] = $this->formateData($msg_list['data_list']);
        return $msg_list;      
    }
    
    //添加新信息
    function addMsg(){
        $list = $this->create();
        $msg_data['send_type'] = $list['send_type'];                    //发送方式
            
        $msg_templet = $list['msg_templet'];                            //模板
        $msg_data['is_html'] = $list['is_html'];                        //是否为超文本格式

        $msg_data['create_time'] = time();                              //创建时间
        $msg_data['title'] = trim($list['title']);                      //(邮件)标题
        $msg_data['content'] = trim($list['content']);                  //内容
        $msg_data['priority'] = $list['priority'];                      //优先级 默认为3

        $msg_type = trim($list['msg_type']);                            //发送对象
        switch($msg_type){
           case 1:
               $msg_data['dest'] = 'All Users';
               break;
           case 2:
               $msg_data['dest'] = trim($list['send_define_data']);     //地址
               break;
        }
        
        if($list['id']){
            $msg_data['id'] = $list['id'];//编辑的时候才有ID
            if($update_id = $this->save($msg_data)){
                return $update_id;
            }else{
                return false;
            }
        }else{
            if($insert_id = $this->add($msg_data)){
                return $insert_id;
            }else{
                return false;
            }
        }
        
    }
    
    //获取短信、邮件模板列表
    function getTempList(){
        for($i=0;$i<2;$i++){
            for($j=1;$j<30;$j++){
                $temp_list[$i][$j] = getMsgTempName($i,$j);
                if(!$temp_list[$i][$j]){
                    unset($temp_list[$i][$j]);
                }
            }
        }
        return $temp_list;
    }
    
    //获取某个消息的详细内容
    function getMsgInfo($id){
        $msg_info = $this->find($id);
        $msg_info['create_time'] = get_date($msg_info['create_time']);
        return $msg_info;
    }
    
    //将信息插入到队列中，伪发送
    function sendMsg($id){
        $msg_data = $this->find($id);
        if($msg_data['status'] == 0){
            //添加到队列表promote_msg_list里
            $msg_data['msg_id'] = $msg_data['id'];
            unset($msg_data['id']);
            $msg_data['msg_type'] = 0;
            $dest = $msg_data['dest'];
            if($dest == 'All Users'){
                $dest_name = $msg_data['send_type'] == 0 ? 'mobile' : 'email';
                $condition[$dest_name] = array('neq',' ');
                $condition['is_delete'] = 0;
                $condition['is_effect'] = 1;
                $condition['is_black'] = 0;
                $info_list =M('User')->field("distinct $dest_name,id")->where($condition)->select();
                foreach($info_list as $key=>$val){
                    $msg_data['dest'] = $val[$dest_name];
                    $msg_data['user_id'] = $val['id'];
                    $re = M('msg_send_list')->add($msg_data);
                }
            }else{
                $nums =strstr($dest,',');           //判断是否是多个地址
                if($nums){
                    $dest_string = $msg_data['dest'];
                    $dest_arr = explode(',',$dest_string);
                    foreach($dest_arr as $key=>$val){
                        $msg_data['dest'] = $val;
                        $re = M('msg_send_list')->add($msg_data);
                    }
                }else{
                    $re = M('msg_send_list')->add($msg_data);
                }
            }
            if($re){
                $update_data['id']     = $msg_data['msg_id'];   
                $update_data['status'] = 3;     //发送中 
                $this->save($update_data);
                $result['status'] = 1;
                $result['info'] = "发送成功";
            }else{
                $result['status'] = 0;
                $result['info'] = "消息发送失败，请重新发送";
            }
        }elseif($msg_data['status'] == 3){
            $result['status'] = 2;
            $result['info'] = "消息正在发送当中，请勿重复点击发送";
        }else{
            $result['status'] = 3;
            $result['info'] = "消息已发送";
        }
        return $result;
    }
    
    //获取短信接口列表
    function getSmsInterfaceList(){
        $sms_list = M('sms')->field(array('id','name','class_name','is_effect'))->select();
        foreach($sms_list as $key=>$val){
            $money = check_sms($val['class_name']);
            $sms_list[$key]['money'] = $money['info'];
        }
        return $sms_list;
    }
    
    
    //获取信息队列
    function getSendList(){
        $condition = array();
        $model = M('msg_send_list');
        $msg_list = $this->_list($model,$condition);
        //数据渲染
        $msg_list['data_list'] = $this->formateData($msg_list['data_list']);
        return $msg_list;      
    }
    
    //格式化数据
    private function formateData($data){
        foreach($data as $key=>$val){
            $data[$key]['title']   = $this->substr_cut($val['title'],30);
            $data[$key]['content'] = $this->substr_cut($val['content'],50);
            switch($val['send_type']){
               case 0:
                   $data[$key]['send_type'] = "短信";
                   break;
               case 1:
                   $data[$key]['send_type'] = "邮件";
                   break;
               default:
                   $data[$key]['send_type'] = "无";
            }
            switch($val['priority']){
               case 1:
                   $data[$key]['priority'] = "紧急";
                   break;
               case 2:
                   $data[$key]['priority'] = "高";
                   break;
               case 3:
                   $data[$key]['priority'] = "普通";
                   break;
               case 4:
                   $data[$key]['priority'] = "低";
                   break;
               case 5:
                   $data[$key]['priority'] = "最低";
                   break;
               default:
                   $data[$key]['priority'] = "无";
            }
            switch($val['status']){
               case 0:
                   $data[$key]['status'] = "未发送";
                   break;
               case 1:
                   $data[$key]['status'] = "发送成功";
                   break;
               case 2:
                   $data[$key]['status'] = "发送失败";
                   break;
               case 3:
                   $data[$key]['status'] = "发送中";
                   break;
               default:
                   $data[$key]['status'] = "无";
            }
        }
        return $data;
    }
    
    //字符串截取
    private function substr_cut($str_cut,$length){
        if (strlen($str_cut)>$length){
            $str_cut=str_replace("\r\n","",$str_cut);
            $str_cut=str_replace("<p>","",$str_cut);
            $str_cut=str_replace("</p>","",$str_cut);
            $str_cut = mb_substr($str_cut,0,$length,'utf-8')."……";
        }
        return $str_cut;
    }

    
    //分页
    private function _list($model,$condition,$sortBy = '', $asc = false) {
        if (isset ( $_REQUEST['_order'] )) {
            $_order = $_REQUEST['_order'];
        } else {
            $_order = !empty($sortBy)?$sortBy:$model->getPk ();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST['_sort'])) {
            $_sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $_sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $data_list = $model->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['data_list'] = $data_list;
        }
        return $return;
    }
    
}

