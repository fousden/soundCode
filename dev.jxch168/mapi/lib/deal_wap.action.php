<?php
/**
 * Created by IntelliJ IDEA.
 * User: ningchengzeng
 * Date: 15/7/14
 * Time: 下午6:35
 */
require APP_ROOT_PATH.'app/Lib/deal.php';
class deal_wap{
    public function index(){
        $root = array();
        
        $id = intval($GLOBALS['request']['id']);
        //检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		if ($user_id >0){
			
			$root['is_faved'] = $GLOBALS['db']->getOne("SELECT count(*) FROM ".DB_PREFIX."deal_collect WHERE deal_id = ".$id." AND user_id=".$user_id);	
		}else{
			$root['is_faved'] = 0;//0：未关注;>0:已关注
		}
        $deal = get_deal($id,0);
        send_deal_contract_email($id,$deal,$deal['user_id']);

        if($deal){

            //结息日期格式转换
            if($deal['jiexi_time'] == '0000-00-00')
            {
                $deal['jiexi_time'] = '暂无';
            }
            //最迟还款日格式转换
            if($deal['last_mback_time'] == '0000-00-00')
            {
                $deal['last_mback_time'] = '暂无';
            }
            //起息日期格式转换
            if($deal['repay_start_date'] == '0000-00-00')
            {
                $deal['repay_start_date'] = '暂无';
            }

            $root['response_code'] = 1;
            $root['deal'] = $deal;
        }else{
            $root['response_code'] = 0;
        }
        
        $root['program_title'] = "标的详情";
        output($root);
    }
}
?>