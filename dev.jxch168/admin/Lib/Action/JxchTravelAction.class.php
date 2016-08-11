<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class JxchTravelAction extends CommonAction {
    //投资列表
    function invest(){
        if($_POST['prize_type'] !=''){
            $prize_type = $_POST['prize_type'];			
	}
        $invest_data = $this->get_invest_list($prize_type);
        $this->assign('list',$invest_data);
        $this->display();
    }
    
    //导出投资列表
    function export_csv_invest(){
        if($_GET['prize_type'] !=''){
            $prize_type = $_GET['prize_type'];			
	}
        $invest_data = $this->get_invest_list($prize_type);
        if($invest_data){
            $account_value = array('id'=>'""','user_id'=>'""','type'=>'""','money'=>'""','times'=>'""','status'=>'""');
            $content = iconv("utf-8","gbk","编号,用户ID,用户类型,累计入金,投资次数,备注");
            $content = $content . "\n";
            foreach($invest_data as $k=>$v){	
                $account_value = array();
                $account_value['id']      = iconv('utf-8','gbk','"' . $v['id'] . '"');
                $account_value['user_id'] = iconv('utf-8','gbk','"' . $v['user_id'] . '"');
                $account_value['type']    = iconv('utf-8','gbk','"' . $v['type'] . '"');
                $account_value['money']   = iconv('utf-8','gbk','"' . $v['money'] . '"');
                $account_value['times']   = iconv('utf-8','gbk','"' . $v['times'] . '"');
                $account_value['status']  = iconv('utf-8','gbk','"' . $v['status'] . '"');
                $content .= implode(",", $account_value) . "\n";
            }	
            header("Content-Disposition: attachment; filename=jxch_travel_invest_list.csv");
	    echo $content;  
	}else{
            $this->error(L("NO_RESULT"));
	}
    }
    
    
    
    //推荐列表
    function invite(){
        if($_POST['prize_type'] !=''){
            $prize_type = $_POST['prize_type'];			
	}
        $invite_data = $this->get_invite_list($prize_type);
        $this->assign('list',$invite_data);
        $this->display(); 
    }
    
    //导出推荐列表
    function export_csv_invite(){
        if($_GET['prize_type'] !=''){
            $prize_type = $_GET['prize_type'];			
	}
        $invite_data = $this->get_invite_list($prize_type);
        if($invite_data){
            $account_value = array('id'=>'""','user_id'=>'""','type'=>'""','money'=>'""','times'=>'""','status'=>'""');
            $content = iconv("utf-8","gbk","编号,用户ID,推荐入金,推荐人数,备注");
            $content = $content . "\n";
            foreach($invite_data as $k=>$v){	
                $account_value = array();
                $account_value['id']      = iconv('utf-8','gbk','"' . $v['id'] . '"');
                $account_value['user_id'] = iconv('utf-8','gbk','"' . $v['user_id'] . '"');
                $account_value['money']   = iconv('utf-8','gbk','"' . $v['money'] . '"');
                $account_value['num']   = iconv('utf-8','gbk','"' . $v['num'] . '"');
                $account_value['status']  = iconv('utf-8','gbk','"' . $v['status'] . '"');
                $content .= implode(",", $account_value) . "\n";
            }	
            header("Content-Disposition: attachment; filename=jxch_travel_invite_list.csv");
	    echo $content;  
	}else{
            $this->error(L("NO_RESULT"));
	}
    }
    
    //获取投资数据
    private function get_invest_list($prize_type){
        $activity_info = M("ActivityConf")->field(array('start_time','end_time'))->where(array('key'=>'zhizunjxchtravel'))->find();
        $start_time_stamp = $activity_info['start_time'];
        $end_time_stamp   = $activity_info['end_time'];
        
        //计算活动期内投资的用户
        $invest_sql = "select dl.user_id,sum(dl.money) as money,count(dl.id) as times from " .DB_PREFIX. "deal_load as dl left join " .DB_PREFIX. "user as u on dl.user_id = u.id where dl.create_time >= '".$start_time_stamp."' and dl.create_time <= '".$end_time_stamp."' AND u.acct_type is null AND u.is_auto = 0 and u.is_effect = 1 AND u.is_delete = 0 and dl.contract_no !='' group by dl.user_id order by money desc";
        $invest_data = $GLOBALS['db']->getAll($invest_sql);

        foreach($invest_data as $key=>$val){
            $deal_log_sql = "select id from " .DB_PREFIX. "deal_load where create_time <= '".$start_time_stamp."' and user_id = '".$val['user_id']."'";
            $deal_log = $GLOBALS['db']->getOne($deal_log_sql);

            if($deal_log){
                # 老用户
                $invest_data[$key]['type'] = '老用户';
                if($val['money'] >= 800000){
                    $invest_data[$key]['status'] = '获奖';
                }else{
                    if(!$prize_type) unset($invest_data[$key]);
                }
            }else{
                #新用户
                $invest_data[$key]['type'] = '新用户';
                if($val['money'] >= 1000000){
                    $invest_data[$key]['status'] = '获奖';
                }else{
                    if(!$prize_type) unset($invest_data[$key]);
                }
            }
            
           
        }
        $i = 1;
        foreach($invest_data as $key=>$val){
            $invest_data[$key]['id'] = $i++;
            $invest_data[$key]['money'] = number_format($val['money'],2); //格式化入金
        }
        return $invest_data;
    }
    
    //获取推荐数据
    private function get_invite_list($prize_type){
        $activity_info = M("ActivityConf")->field(array('start_time','end_time'))->where(array('key'=>'zhizunjxchtravel'))->find();
        $start_time_stamp = $activity_info['start_time'];
        $end_time_stamp   = $activity_info['end_time'];
        
        //计算在活动期内推荐客户的用户
        $invite_sql = "select pid,count(id) as num from " .DB_PREFIX. "user where create_time >= '".$start_time_stamp."' and create_time <= '".$end_time_stamp."' and pid != 0 AND acct_type is null AND is_auto = 0 and is_effect = 1 AND is_delete = 0 group by pid";
        $invite_data = $GLOBALS['db']->getAll($invite_sql);

        foreach($invite_data as $key=>$val){
            $deal_log_sql = "select id from " .DB_PREFIX. "deal_load where create_time <= '".$start_time_stamp."' and user_id = '".$val['pid']."' and contract_no !=''";
            $deal_log = $GLOBALS['db']->getOne($deal_log_sql);
            if($deal_log){
                # 老用户
                $invite_money_sql = "select sum(dl.money) from " .DB_PREFIX. "deal_load as dl left join " .DB_PREFIX. "user as u on dl.user_id = u.id where u.pid = '" .$val['pid']. "' and u.create_time >= '".$start_time_stamp."' and dl.create_time >= '".$start_time_stamp."' and dl.create_time <= '" .$end_time_stamp. "' and u.is_auto = '0' and dl.contract_no !='' and dl.contract_no !='' group by u.pid ";
                $invite_money = $GLOBALS['db']->getOne($invite_money_sql);
                $invite_money = $invite_money ? $invite_money :0 ;
                
                $invite_data[$key]['user_id'] = $val['pid'];
                $invite_data[$key]['num'] = $val['num'];
                $invite_data[$key]['money'] = $invite_money;

                if($invite_money >= 1000000){
                    $invite_data[$key]['status'] = '获奖';
                }else{
                    if(!$prize_type) unset($invite_data[$key]);
                }
            }else{
                unset($invite_data[$key]);
            }
        }

        $money = array();
        foreach ($invite_data as $val) {
            $money[] = $val['money'];
        }
        array_multisort($money, SORT_DESC, $invite_data);

        $i = 1;
        foreach($invite_data as $key=>$val){
            $invite_data[$key]['id'] = $i++;
            $invite_data[$key]['money'] = number_format($val['money'],2); //格式化推荐入金
        }
        return $invite_data;  
    }
    
    
 
}

?>