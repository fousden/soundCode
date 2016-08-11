<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_center
{
	public function index(){
		
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码

		$version = strim($GLOBALS['request']['version']);
		$_m = strim($GLOBALS['request']['_m']);
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);

		$user_id  = intval($user['id']);
		if ($user_id >0){
			
			$root['user_login_status'] = 1;									
		
			$user['group_name'] = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."user_group where id = ".$user['group_id']." ");
			
			//绑定银行卡个数
			$user['bankcardcount'] = $GLOBALS['db']->getOne("select count(1) from ".DB_PREFIX."user_bank where user_id = ".$user_id);

			$province_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$user['province_id']);
			$city_str = $GLOBALS['db']->getOne("select name from ".DB_PREFIX."region_conf where id = ".$user['city_id']);

			if($province_str.$city_str=='')
				$user_location = $GLOBALS['lang']['LOCATION_NULL'];
			else
				$user_location = $province_str." ".$city_str;

			$user['user_location'] = $user_location;

			$user_data = sys_user_status($user_id,false);

			//可用金额
			$root['money'] = $user['money'];
			$root['money_format'] = number_format($user['money'],2);

			//代收本金
			$all_wait_deals = $this->get_loadlist($user_id,'');
			$total_invest_money = 0.00;
			foreach($all_wait_deals as $k=>$v)
			{
				$total_invest_money += $v["repay_money"];
			}
			$root['lock_money'] = floatval($total_invest_money);	//冻结金额
			$root['lock_money_format'] = number_format($total_invest_money,2);

			//资金金额
			$root["total_money"] = floatval($user["money"]) + 
													floatval($user["lock_money"]);
			$root['total_money_format'] = number_format($root['total_money'],2);

			$user['create_time_format'] = to_date($user['create_time'],'Y-m-d'); //注册时间
			
			$root['response_code'] = 1;
			$root['user_location'] = $user['user_location'];
			$root['user_name'] = $user['user_name'];
			$root['group_name'] = $user['group_name'];
			$root['mobile'] = $user['mobile'];
                        
            $root['create_time_format'] = $user['create_time_format'];
			$root['idno'] = $user['idno'];
			$root['real_name'] = $user['real_name'];
			//是否开启《平安保险网银卫士》资金安全担保条款
			$root['insurance_show']=$GLOBALS['db']->getOne("select value from ".DB_PREFIX."conf where name = 'PINGANPROVISION'");
			$root['bankcard_count'] = $user['bankcardcount'];
                        
            if(empty($user['paypassword'])){
                $root['ispay'] = 0;
            }else{
            	$root['ispay'] = 1;
            }
			
			if(intval(app_conf("OPEN_IPS")) > 0){
				$app_url = APP_ROOT."/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=".$user_id."&from=".$GLOBALS['request']['from'];
				//申请
				$root['app_url'] = str_replace("/mapi", "", SITE_DOMAIN.$app_url);
				$root['acct_url'] = $root['app_url'];				
			}		
			
			$root['ips_acct_no'] = $user['ips_acct_no'];
			$root['open_ips'] = intval(app_conf("OPEN_IPS"));
			
			//第三方托管标
			if (!empty($user['ips_acct_no']) && intval(app_conf("OPEN_IPS")) > 0){
				$result = GetIpsUserMoney($user_id,0);
					
				$root['ips_balance'] = $result['pBalance'];//可用余额
				$root['ips_lock'] = $result['pLock'];//冻结余额
				$root['ips_needstl'] = $result['pNeedstl'];//未结算余额
			}else{
				$root['ips_balance'] = 0;//可用余额
				$root['ips_lock'] = 0;//冻结余额
				$root['ips_needstl'] = 0;//未结算余额
			}
			
			$root['ips_balance_format'] = num_format($root['ips_balance']);
			$root['ips_lock_format'] = num_format($root['ips_lock']);
			$root['ips_needstl_format'] = num_format($root['ips_needstl']);

			//消息个数
			$sql_message_count = "select count(1) from fanwe_msg_box where is_read = 0 and is_delete = 0 and to_user_id = " . $user_id;
			$message_count = $GLOBALS['db']->getOne($sql_message_count);
			$root['message_count'] = $message_count;
			
			//账户中心推广信息
			$root['act_show'] = $GLOBALS['m_config']['m_act_show'];
			$root['act_title'] = $GLOBALS['m_config']['m_act_title'];
			$root['act_url'] = $GLOBALS['m_config']['m_act_url'];
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title'] = "会员中心";
		output($root);		
	}

	private function get_loadlist($user_id,$where) {
		$condtion = "   AND dlr.has_repay = 0  ".$where." ";
    	$sql = "select dlr.*,u.user_name,u.level_id,u.province_id,u.city_id from ".DB_PREFIX."deal_load_repay dlr LEFT JOIN ".DB_PREFIX."user u ON u.id=dlr.user_id  where ((dlr.user_id = ".$user_id." and dlr.t_user_id = 0) or dlr.t_user_id = ".$user_id.") $condtion order by dlr.repay_time desc ";
		$list = $GLOBALS['db']->getAll($sql);
		return $list;
    }
}
?>
