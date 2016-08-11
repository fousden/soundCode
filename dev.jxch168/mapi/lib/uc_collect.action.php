<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
require APP_ROOT_PATH.'app/Lib/deal.php';
class uc_collect
{
	public function index(){
		
		$root = array();
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		$page = intval($GLOBALS['request']['page']);
		
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			require APP_ROOT_PATH.'app/Lib/uc_func.php';
			
			$root['user_login_status'] = 1;
			$root['response_code'] = 1;
			
			if($page==0)
				$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			
			$result = get_collect_list($limit,$user_id);
                        
            $rdata = array();
            //删除过期的标
            $time = TIME_UTC;
            foreach($result['list'] as $key => $value){
                if(($value["start_time"] - TIME_UTC) > 0){
                    $value["bfinish_time"] = 0;
                }else{
                    $value["bfinish_time"] = 1;
                }

                if($value['deal_status'] == 4){
                    $value['name'].='(还款中)';
                    $value['progress_point'] = '100.00';
                }else if($value['deal_status'] == 2){
                    $value['name'].='(满标)';
                    $value['progress_point'] = '100.00';
                }

				format_deal_item($value, '', '');
                if ($value["agency_info"]) {
                    $value["guarantee"] = $value["agency_info"]["user_name"];
                } else {
                    $value["guarantee"] = "";
                }
                
                array_push($rdata, $value);
            }
			 
			$root['item'] = $rdata;
			$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("PAGE_SIZE")),"page_size"=>app_conf("PAGE_SIZE"));
			
		}else{
			$root['response_code'] = 0;
			$root['show_err'] ="未登录";
			$root['user_login_status'] = 0;
		}
		$root['program_title']="我的关注";
		output($root);		
	}
}
?>
