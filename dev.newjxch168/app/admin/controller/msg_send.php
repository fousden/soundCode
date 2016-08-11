<?php
namespace admin\controller;
use base\controller\backend;

/**
 * 短信邮件控制器
 *
 * @author jxch
 */

class MsgSend extends backend{
    //信息列表
    function index(){
        $msgSendModel = D("MsgSend");
        $msg_list = $msgSendModel->getMsgList();
        $this->assign("page", $msg_list['page']);
        $this->assign("nowPage", $msg_list["nowPage"]);
        $this->assign("list",$msg_list['data_list']);
        return $this->fetch();
    }

    //增加消息
    function add(){
        $msgSendModel = D("MsgSend");
        if(IS_POST){
            if($msgSendModel->addMsg()){
                return $this->success("添加成功");
                die;
            }else{
                return $this->error("添加失败");
                die;
            }
        }else{
           //获取短信、邮件模板列表
            $temp_tree = $msgSendModel->getTempList();
            $this->assign("temp_tree", $temp_tree);
           return $this->fetch(); 
        }
        
    }
    
    //编辑信息
    function edit($id){
        $msgSendModel = D("MsgSend");
        if(IS_POST){
            if($msgSendModel->addMsg()){
                return $this->success("更新成功");
                die;
            }else{
                return $this->error("更新失败");
                die;
            }
        }else{
            $msg_info = $msgSendModel->getMsgInfo($id);
            $this->assign('msgInfo',$msg_info);
            return $this->fetch(); 
        }
    }
    
    //发送信息
    function send($id){
        $msgSendModel = D("MsgSend");
        $result = $msgSendModel->sendMsg($id);
        die(json_encode($result));
    }
    
    //选择短信接口
    function sms_interface(){
        $alias = $_GET['alias'];
        $class_name = array("yxt", "lqd");
        if(in_array($alias,$class_name)){
            M('sms')->where("class_name <> '" .$alias ."'")->setField("is_effect",0);
            M('sms')->where("class_name =  '" .$alias ."'")->setField("is_effect",1);
            return true;
        }
        return false;
    }
    
    //获得模板内容
    function get_temp(){
        $send_type = $_POST['send_type'];
        $temp_id = $_POST['temp_id'];
        $temp_content = getMsgTemp($send_type,$temp_id);
        if($temp_content){
            die(json_encode($temp_content));
        }else{
            return false;
        }
    }
    
    //信息队列
    function send_list(){
        $msgSendModel = D("MsgSend");
        $msg_list = $msgSendModel->getSendList();
        $this->assign("page", $msg_list['page']);
        $this->assign("nowPage", $msg_list["nowPage"]);
        $this->assign("list",$msg_list['data_list']);
        return $this->fetch();
    }
    
    //查看消息队列某一内容
    function show_content(){
        $id = intval($_REQUEST['id']);
	header("Content-Type:text/html; charset=utf-8");
	echo htmlspecialchars(M('msg_send_list')->where("id=".$id)->getField("content"));
    }
    
    //短信接口列表
    function sms_list(){
        $msgSendModel = D("MsgSend");
        $sms_list = $msgSendModel->getSmsInterfaceList();
        $this->assign("sms_list", $sms_list);
        return $this->fetch();
    }
    
}
