<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
class uc_message
{

	private $notice_type = array(
		'用户信息' ,
		'系统通知' ,
		'材料通过' ,
		'审核失败' ,
		'额度更新' ,
		'提现申请' ,
		'提现成功' ,
		'提现失败' , 
		'还款成功' , 
		'回款成功' ,
		'借款流标' ,
		'投标流标' ,
		'三日内还款' ,
		'标被留言' ,
		'标留言被回复' ,
		'借款投标过半' ,
		'投标满标' ,
		'债权转让失败' ,
		'债权转让成功' ,
		'续约成功' ,
		'续约失败' ,
                '途虎活动'
	);


	public function index()
	{
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$read = intval($GLOBALS['request']['read']);
		$page = intval($GLOBALS['request']['page']);

		if($page == 0)
			$page = 1;

		$sql_read = "";
		switch ($read) {
			case 0:			//全部
				break;
			case 1:			//未读
				$sql_read = " and is_read = 0 ";	//1
				break;
			case 2:
				$sql_read = " and is_read = 1 "; 	//2
				break;		//已读
			default:		//全部
				break;
		}

		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);

		$user_id  = intval($user['id']);
		if ($user_id >0){
			$message = array();
			$sql = "select id,title,content,from_user_id,to_user_id,is_notice,create_time,is_read  
					from ".DB_PREFIX."msg_box 
					where is_delete = 0 and to_user_id = $user_id $sql_read 
					order by create_time desc";
			$sql_count = "select count(1) from ($sql) c";

			$count = $GLOBALS['db']->getOne($sql_count);

			if($count > 0){
				$message = $GLOBALS['db']->getAll($sql . " limit $limit");

				$root['message'] = $message;
				foreach ($message as $key => $value) {
					$message[$key]['notice_title'] = $this->notice_type[$value['is_notice']];
					$message[$key]['create_date'] = date('Y-m-d H:i:s',$value['create_time']);
					$message[$key]['content'] = strip_tags($value['content']);

					unset($message[$key]['from_user_id']);
					unset($message[$key]['to_user_id']);
					unset($message[$key]['create_time']);
				}
			}

			$root['page'] = array("page"=>$page,"page_total"=>ceil($count/app_conf("DEAL_PAGE_SIZE")),"page_size"=>app_conf("DEAL_PAGE_SIZE"));
			$root['message'] = $message;
			$root['response_code'] = 1;
			$root['user_login_status'] = 1;
		}
		else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}

		output($root);
	}
}
?>