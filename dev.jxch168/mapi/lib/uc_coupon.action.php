<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

/**
 * 优惠券列表查询
 */
class uc_coupon
{

	public function index()
	{
		$root = array();

		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		$coupon_type = intval($GLOBALS['request']['coupon_type']);	//类型 0:全部 1:抵现券 2:收益券
		$coupon_status = intval($GLOBALS['request']['coupon_status']);	//是否可用 0:可用 1:已使用 2:已过期

		$page = intval($GLOBALS['request']['page']);
		if($page == 0)
			$page = 1;

		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id > 0){
			$sql = "select id,
						face_value,
						coupon_type,
						coupon_name,
						coupon_desc,
						`status`,
						min_limit,
						gain_time,
						start_time,
						end_time,
						FROM_UNIXTIME(start_time,'%Y-%m-%d') start_date,
						FROM_UNIXTIME(end_time,'%Y-%m-%d') end_date 
						from ".DB_PREFIX."user_coupon where user_id = ". $user_id;

			if($coupon_type == 1){
				$sql = $sql. " and coupon_type = 2"; //收益券
			}else if($coupon_type == 2){
				$sql = $sql. " and coupon_type = 1"; //抵现券
			}
			
			if($coupon_status == 0){
				$sql = $sql. " and status = 0 and unix_timestamp(now()) - end_time < 0 "; //没有过期
			}
			else if($coupon_status == 1){
				$sql = $sql. " and status = 1 "; //已经使用
			}
			else if($coupon_status == 2){
				$sql = $sql. " and status = 0 and unix_timestamp(now()) - end_time >= 0 "; //已经过期
			}

			$sql_count = "select count(1) from (".$sql.") t";
			$count = $GLOBALS['db']->getOne($sql_count);
			$items = array();
			if($count>0) {
				$items = $GLOBALS['db']->getAll($sql . " limit ". $limit);
			}
			
                        foreach($items as $key=>$val){
                            $items[$key]['over_now']=$coupon_status;
                        }
                        $root['items'] = $items;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($count/app_conf("DEAL_PAGE_SIZE")),"page_size"=>app_conf("DEAL_PAGE_SIZE"));
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