<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class uc_queryblank{
	public function index(){
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if($user_id>0){

			$sql = "select t.id,t.bank_id,
				(select `name` from ".DB_PREFIX."bank where fuyou_bankid is not null and fuyou_bankid = t.bank_id) bankcard_name,
				t.bankcard,
				t.real_name,
				t.region_lv1,
				(select `DistrictName` from ".DB_PREFIX."district_info where DistrictCode = t.region_lv1) region_lv1_name,
				t.region_lv2,
				(select `DistrictName` from ".DB_PREFIX."district_info where DistrictCode = t.region_lv2) region_lv2_name,
				t.bankzone 
				from ".DB_PREFIX."user_bank t where user_id = ". $user_id;

			$banklist = $GLOBALS['db']->getAll($sql);
			$root['response_code'] = 1;
			foreach ($banklist as $key => $value) {

				$arr=str_split($str,4);//4的意思就是每4个为一组
				$str=implode('-',$arr);
				$banklist[$key]['bankcard_s'] = preg_replace('/^(.{4})(.*)(.{4})$/i','${1}**********$3',$value['bankcard']);
			}

			$root['list'] = $banklist;
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		output($root);
	}
}

?>