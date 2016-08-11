<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class LogAction extends CommonAction{
	public function index()
	{
		if(trim($_REQUEST['log_info'])!='')
		{
			$map['log_info'] = array('like','%'.trim($_REQUEST['log_info']).'%');			
		}
		
		$log_begin_time  = trim($_REQUEST['log_begin_time'])==''?0:to_timespan($_REQUEST['log_begin_time']);
		$log_end_time  = trim($_REQUEST['log_end_time'])==''?0:to_timespan($_REQUEST['log_end_time']);
		if($log_end_time==0)
		{
			$map['log_time'] = array('gt',$log_begin_time);	
		}
		else
		$map['log_time'] = array('between',array($log_begin_time,$log_end_time));	
		
		
		$this->assign("default_map",$map);
		parent::index();
	}
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		
		$condition = array ("log_time" => array("lt",next_replay_month(TIME_UTC,-6)) );			
		
		$list = M(MODULE_NAME)->where ( $condition )->delete();
		if ($list!==false) {
			save_log("清除半年前的记录",1);
			$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
		} else {
			save_log("清除半年前的记录",0);
			$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
		}
			
	}
	
	
}
?>