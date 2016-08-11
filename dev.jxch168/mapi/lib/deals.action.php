<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/deal.php';
class deals
{
	public function index(){
		//require APP_ROOT_PATH.'app/Lib/page.php';
		$page = intval($GLOBALS['request']['page']);
		if($page==0)
			$page = 1;

		$keywords = trim(htmlspecialchars($GLOBALS['request']['keywords']));
		$level = intval($GLOBALS['request']['level']);
		$interest = intval($GLOBALS['request']['interest']);
		$months = intval($GLOBALS['request']['months']);
		$lefttime = intval($GLOBALS['request']['lefttime']);
		$deal_status = intval($GLOBALS['request']['deal_status']);
		$porderby = intval($GLOBALS['request']['orderby']);			//排序

		$limit = (($page-1)*app_conf("DEAL_PAGE_SIZE")).",".app_conf("DEAL_PAGE_SIZE");
		$level_list = load_auto_cache("level");
		$cate_id = intval($GLOBALS['request']['cid']);
                                    if ($cate_id == 4)
                                    {
                                        $cate_id = 88;
                                    }
		$n_cate_id = 0;
		$condition = " publish_wait = 0 and deal_status in(1,2,4,5)";

		$orderby = ' deal_status asc,';

		if($porderby == 0){

		}
		else if($porderby == 1){
			$qorderby .= 'rate desc,';
		}
		else if($porderby == 2){
			$qorderby .= 'enddate asc,';
		}
		else if($porderby == 3){
			$qorderby .= 'jiexi_time asc,';
		}

		$orderby .= $qorderby."start_time desc,update_time desc" ;

		if($cate_id > 0){
			$condition .= " and cate_id =" .$cate_id;
		}

		if($keywords){
			$kw_unicode = str_to_unicode_string($keywords);
			$condition .=" and (match(name_match,deal_cate_match,tag_match,type_match) against('".$kw_unicode."' IN BOOLEAN MODE))";
		}

		if($level > 0){
			$point  = $level_list['point'][$level];
			$condition .= " AND user_id in(SELECT u.id FROM ".DB_PREFIX."user u LEFT JOIN ".DB_PREFIX."user_level ul ON ul.id=u.level_id WHERE ul.point >= $point)";
		}

		if($interest > 0){
			$condition .= " AND rate >= ".$interest;
		}

		if($months > 0){
			if($months==12)
				$condition .= " AND repay_time <= ".$months;
			elseif($months==18)
				$condition .= " AND repay_time >= ".$months;
		}

		if($lefttime > 0){
			$condition .= " AND (start_time + enddate*24*3600 - ".TIME_UTC.") < ".$lefttime*24*3600;
		}

		//$condition .= " AND (start_time + enddate*24*3600 - ".TIME_UTC.") >= 0 ";

		/*if ($deal_status > 0){
			$condition .= " AND deal_status = ".$deal_status;
		}*/
		$result = get_deal_list_mobile($limit,$n_cate_id,$condition,$orderby);

		$rdata = array();
		//删除过期的标
		$time = TIME_UTC;
		foreach($result['list'] as $value){
			if(($value["start_time"] - TIME_UTC) > 0){
				$value["bfinish_time"] = 0;
			}else{
				$value["bfinish_time"] = 1;
			}

                        if($value['deal_status'] == 4 || $value['deal_status'] == 2){
                            $value['progress_point'] = '100.00';
                        }

                        //开始时间
                        $start_time = $value['start_time'];
                        //筹标期限
                        $enddate = $value['enddate'];
                        //标的有效时间 是否过期
                        $remain_time = intval($start_time + $enddate * 24 * 3600 - $time);
                        //两天时间时间戳表示
                        if ($value['deal_status'] == 1 && $remain_time <= 0) {
                        }else{
                            array_push($rdata, $value);
                        }
		}

		$root = array();
		$root['response_code'] = 1;
		$root['item'] = $rdata;

		//$root['DEAL_PAGE_SIZE'] = app_conf("DEAL_PAGE_SIZE");
		//$root['count'] = $result['count'];

		$root['page'] = array("page"=>$page,"page_total"=>ceil($result['count']/app_conf("DEAL_PAGE_SIZE")),"page_size"=>app_conf("DEAL_PAGE_SIZE"));
		$root['program_title'] = "投资列表";
		output($root);
	}
}
?>
