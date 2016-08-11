<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/uc_func.php';
require APP_ROOT_PATH.'app/Lib/deal_func.php';
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_invest
{
	public function index(){
		
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$page = intval($GLOBALS['request']['page']);// 第几页
		$cate_id = intval($GLOBALS['request']['cid']);// 分类id
		$mode = strim($GLOBALS['request']['mode']);//index,invite,ing,over,bad
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			$root['user_login_status'] = 1;
			
			$status = intval($GLOBALS['request']['status']);
			$root['status'] = $status;
			/*
			 * $status 1 进行中,2还款中,3已还清,4满标,5流标,0或其他 默认为全部
			 */
			if(isset($status) && $status=="1"){
				$result = getInvestList($mode = "in",$user_id,$page,$email,$user['user_pwd'],$cate_id);	//进行中
			}elseif(isset($status) && $status=="2"){
				$result = getInvestList($mode = "ing",$user_id,$page,$email,$user['user_pwd'],$cate_id); //还款中
			}elseif(isset($status) && $status=="3"){
				$result = getInvestList($mode = "over",$user_id,$page,$email,$user['user_pwd'],$cate_id); //已还清
			}elseif(isset($status) && $status=="4"){
				$result = getInvestList($mode = "full",$user_id,$page,$email,$user['user_pwd'],$cate_id); //满标
			}elseif(isset($status) && $status=="5"){
				$result = getInvestList($mode = "flow",$user_id,$page,$email,$user['user_pwd'],$cate_id); //流标
			}else{
				$result = getInvestList($mode,$user_id,$page,$email,$user['user_pwd'],$cate_id);
			}
			 //修改描述字段
			$list_coyp=$result['list'];
            foreach ($list_coyp  as $key => $value) {
                $list_coyp[$key]['description_ios'] = $list_coyp[$key]['description'];
                $list_coyp[$key]['bfinish_time'] = 1;

                if($list_coyp[$key]['day'] == '0'){
                	$load_money = $list_coyp[$key]['u_load_money'] + $list_coyp[$key]['rate']/100 * $list_coyp[$key]['u_load_money'] / 360 * $list_coyp[$key]['repay_time'];
            	}else{
            		$load_money = $list_coyp[$key]['u_load_money'] + $list_coyp[$key]['rate']/100 * $list_coyp[$key]['u_load_money'] / 360 * $list_coyp[$key]['repay_time'] * 30;
            	}

            	$dhbx = num_format($load_money);
            	$hk_time = $list_coyp[$key]['last_mback_time'];

                format_deal_item($value, '', '');
                if ($value["agency_info"]) {
                    $value["guarantee"] = $value["agency_info"]["user_name"];
                } else {
                    $value["guarantee"] = "";
                }

                $value['dhbx'] = $dhbx;
                $value['hk_time'] = $hk_time;
                
                unset($value["agency_info"]);
                unset($value["cate_info"]);
                unset($value["type_info"]);
                unset($value['descreption']);
                unset($value['user']);

                $list_coyp[$key] = $value;
			}
			$root['item'] = $list_coyp;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("DEAL_PAGE_SIZE")),"page_size"=>app_conf("DEAL_PAGE_SIZE"));
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
		}
		$root['program_title'] = "我的投资";
		output($root);		
	}
}
?>
