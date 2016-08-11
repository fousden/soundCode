<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/deal.php';
class calc_bid{
	public function index(){
		$root = array();
		require_once APP_ROOT_PATH."app/Lib/deal_func.php";
		
		$id = intval($GLOBALS['request']['id']);
		$minmoney = floatval($GLOBALS['request']['money']);
		$number = floatval($GLOBALS['request']['number']);
		$yield_ratio = floatval($GLOBALS['request']['yield_ratio']);
				
		$deal = $GLOBALS['cache']->get("MOBILE_DEAL_BY_ID_".$id);
		if($deal===false)
		{
			$deal = get_deal($id);
			$GLOBALS['cache']->set("MOBILE_DEAL_BY_ID_".$id,$deal,300);	
		}	
		
		$parmas = array();
		
		$parmas['uloantype'] =  $deal['uloadtype'];
		if($deal['uloadtype'] == 1){
			$parmas['minmoney'] = $minmoney;
			$parmas['money'] = $number;
		}else{
			$parmas['money'] = $minmoney;
		}
		
		$parmas['loantype'] = $deal['loantype'];
		$parmas['rate'] = $deal['rate'];
		$parmas['repay_time'] = $deal['repay_time'];
		$parmas['repay_time_type'] = $deal['repay_time_type'];
		$parmas['user_loan_manage_fee'] = $deal['user_loan_manage_fee'];
		$parmas['user_loan_interest_manage_fee'] = $deal['user_loan_interest_manage_fee'];
		$parmas['yield_ratio'] = $deal['yield_ratio'];
		
		$root['profit'] = bid_calculate($parmas);
		
		$root['profit'] = "¥".$root['profit'] ;
		$root['response_code'] = 1;
	
		output($root);		
	}
}
?>
