<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class FeedbackAction extends CommonAction{
	public function index() {
		$condition = array();
		if($_REQUEST['user']==1){
			$condition['user_id'] = array("gt",0);// 注册用户
		}
		if($_REQUEST['user']==2){
			$condition['user_id'] = 0;// 非注册用户
		}
		if($_REQUEST['is_response']==1){
			$condition['msg_id']=0; // 未回复
		}
		if($_REQUEST['is_response']==2){
			$condition['msg_id']=array("gt",0); // 未回复
		}
		$this->assign("default_map", $condition);
		parent::index();
	}

	public function response(){
		$id = $_REQUEST['id'];
		$info = M("feedback")->where("id=".$id)->find();
		$user_name = M("user")->where("id=".$info['user_id'])->getField("user_name");
		$this->assign("feedback",$info);
		$this->assign("user_name",$user_name);
		$this->display();
	}

	public function insert(){
		$title='';
		$content = isset($_REQUEST['content']) ? trim($_REQUEST['content']) : '';
		$from_user_id = 0;
		$to_user_id = isset($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '';
		$create_time = time();
		$is_notice = 22;
		$fav_id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
		$response_id = send_users_msg($title, $content, $from_user_id, $to_user_id, $create_time, $sys_msg_id = 0, $only_send = true, $is_notice, $fav_id);
		// 更新下feedback中msg_id;
		if($response_id>0){
			$data=array();
			$data['msg_id'] = $response_id;
			$res=M("feedback")->where("id=".$_REQUEST['id'])->save($data);
			if($res){
				$this->success(L("回复成功！"));
			}
		}

	}

	public function get_response(){
		$id = $_REQUEST['id'];
		$ajax = $_REQUEST['ajax'];
		// 判断是否是注册用户
		$user_id = M(MODULE_NAME)->where("id=".$id)->getField("user_id");
		if(!$user_id){
			ajax_return("该用户非注册用户，无法回复！");
		}
		// 如果满足注册用户且没有回复过
		$data['response_code']=1;
		$data['show_err'] = "跳转回复页！";
		ajax_return($data);
	}

	public function get_responses(){
		$id = $_REQUEST['id'];
		$ajax = $_REQUEST['ajax'];
		$msg_id = M("feedback")->where("id=".$id)->getField("msg_id");
		$content = M("MsgBox")->where("id=".$msg_id)->getField("content");
		ajax_return($content);
	}
}