<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class LoadsAction extends CommonAction{

    function index() {
    	$extwhere ="" ;
    	$this->getlist($extwhere);
	$this->display();
    }

    function hand() {
    	$extwhere =" and dl.is_auto=0 " ;
    	$this->getlist($extwhere);
		$this->display("index");
    }

    function auto() {
    	$extwhere =" and dl.is_auto=1 " ;
    	$this->getlist($extwhere);
		$this->display("index");
    }

    function success() {
    	$extwhere =" and dl.is_repay=0 " ;
    	$this->getlist($extwhere);
		$this->display("index");
    }

    function failed() {
    	$extwhere =" and dl.is_repay=1 " ;
    	$this->getlist($extwhere);
		$this->display("index");
    }
    //投标排行榜
    function rank(){
        $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")))." 00:00:00";
        $EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"))." 23:59:59";
        $begin_time  = trim($_REQUEST['begin_time'])==''?strtotime($BeginDate):to_timespan($_REQUEST['begin_time'] . " 00:00:00");
        $end_time  = trim($_REQUEST['end_time'])==''?strtotime($EndDate):to_timespan($_REQUEST['end_time'] . " 23:59:59");
	$conditon = "";
        //时间限制
        $conditon .= " AND dl.create_time >= ".$begin_time;
        $conditon .= " AND dl.create_time <= ".$end_time;
        $show_start_time = date('Y-m-d',$begin_time);
        $show_last_time = date('Y-m-d',$end_time);

        $sql_count = "select sum(dl.money) from ".DB_PREFIX."deal_load dl where dl.is_auto = 0 ".$conditon ."  group by dl.user_id ";
        $count = $GLOBALS['db']->getAll($sql_count);
        $count = count($count);
        if (! empty ( $_REQUEST ['listRows'] )) {
                $listRows = $_REQUEST ['listRows'];
        } else {
                $listRows = '';
        }
        $p = new Page ( $count, $listRows );
        if($count > 0 ){
                $sql = "select sum(dl.money) as total_money,u.* from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id  where dl.is_auto = 0 ".$conditon . " group by dl.user_id ORDER BY total_money desc limit ".$p->firstRow . ',' . $p->listRows;
                $list = $GLOBALS['db']->getAll($sql);

                foreach($list as $key=>$val){
                    $list[$key]['key'] = $key + 1;
                }
                $this->assign("list",$list);
        }
        $page = $p->show();
        $this->assign ( "page", $page );
        $this->assign ( "show_start_time", $show_start_time);
        $this->assign ( "show_last_time", $show_last_time );
        $this->display();
    }
    //导出投资排行榜
    function export_rank(){
            $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")))." 00:00:00";
            $EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"))." 23:59:59";
            $begin_time  = trim($_REQUEST['begin_time'])==''?strtotime($BeginDate):to_timespan($_REQUEST['begin_time'] . " 00:00:00");
            $end_time  = trim($_REQUEST['end_time'])==''?strtotime($EndDate):to_timespan($_REQUEST['end_time'] . " 23:59:59");
            $conditon = "";
            //时间限制
            $conditon .= " AND dl.create_time >= ".$begin_time;
            $conditon .= " AND dl.create_time <= ".$end_time;

            $sql = "select sum(dl.money) as total_money,u.* from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id  where dl.is_auto = 0 ".$conditon . " group by dl.user_id ORDER BY total_money desc";
            $list = $GLOBALS['db']->getAll($sql);
            //表头为空
            $user_rank_lists = array();
            //数据格式化
            foreach($list as $key => $value){
                $user_rank_lists[$key+1] = $value;
            }
            $user_rank_lists[0] = array();
            ksort($user_rank_lists);
            //导出Excel入金表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();

            /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
            foreach($user_rank_lists as $key => $value){
                $num=$key + 1;
                if($key == 0){
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, '编号')
                              ->setCellValue('B'.$num, "会员ID")
                              ->setCellValue('C'.$num, "用户名")
                              ->setCellValue('D'.$num, "投资金额（元）");
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, $num-1)
                              ->setCellValue('B'.$num, $value['id'])
                              ->setCellValue('C'.$num, $value['user_name'])
                              ->setCellValue('D'.$num, $value['total_money']);
                }
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = app_conf("SHOP_TITLE") . "用户投资排行榜统计表";
            php_export_excel($objPHPExcel,$filename);
    }
    //邀请排行榜
    function invite(){
        $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")))." 00:00:00";
        $EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"))." 23:59:59";
        $begin_time  = trim($_REQUEST['begin_time'])==''?strtotime($BeginDate):to_timespan($_REQUEST['begin_time'] . " 00:00:00");
        $end_time  = trim($_REQUEST['end_time'])==''?strtotime($EndDate):to_timespan($_REQUEST['end_time'] . " 23:59:59");
	$conditon = "";
        //时间限制
        $conditon .= " AND dl.create_time >= ".$begin_time;
        $conditon .= " AND dl.create_time <= ".$end_time;
        $show_start_time = date('Y-m-d',$begin_time);
        $show_last_time = date('Y-m-d',$end_time);

        $sql_count = "select sum(dl.money),u.pid from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id where dl.is_auto = 0 AND u.pid <> 0 ".$conditon ."  group by u.pid ";
        $count = $GLOBALS['db']->getAll($sql_count);
        $count = count($count);

        if (! empty ( $_REQUEST ['listRows'] )) {
                $listRows = $_REQUEST ['listRows'];
        } else {
                $listRows = '';
        }
        $p = new Page ( $count, $listRows );
        if($count > 0 ){
                $sql = "select sum(dl.money) as total_money,u.pid from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id  where dl.is_auto = 0 AND u.pid <> 0 ".$conditon . " group by u.pid ORDER BY total_money desc limit ".$p->firstRow . ',' . $p->listRows;
                $list = $GLOBALS['db']->getAll($sql);
                foreach($list as $key=>$val){
                    $list[$key]['user_info'] =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$val['pid']);
                    $list[$key]['key'] = $key + 1;
                }
                $this->assign("list",$list);
        }
        $page = $p->show();
        $this->assign ( "page", $page );
        $this->assign ( "show_start_time", $show_start_time);
        $this->assign ( "show_last_time", $show_last_time );
        $this->display();
    }

    //导出邀请排行榜
    function export_invite(){
            $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")))." 00:00:00";
            $EndDate = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"))." 23:59:59";
            $begin_time  = trim($_REQUEST['begin_time'])==''?strtotime($BeginDate):to_timespan($_REQUEST['begin_time'] . " 00:00:00");
            $end_time  = trim($_REQUEST['end_time'])==''?strtotime($EndDate):to_timespan($_REQUEST['end_time'] . " 23:59:59");
            $conditon = "";
            //时间限制
            $conditon .= " AND dl.create_time >= ".$begin_time;
            $conditon .= " AND dl.create_time <= ".$end_time;

            $sql = "select sum(dl.money) as total_money,u.pid from ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."user u on dl.user_id = u.id  where dl.is_auto = 0 AND u.pid <> 0 ".$conditon . " group by u.pid ORDER BY total_money desc";
            $list = $GLOBALS['db']->getAll($sql);
            foreach($list as $key=>$val){
                $list[$key]['user_info'] =  $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where id = ".$val['pid']);
                $list[$key]['key'] = $key + 1;
            }
            //表头为空
            $user_invite_lists = array();
            //数据格式化
            foreach($list as $key => $value){
                $user_invite_lists[$key+1] = $value;
            }
            $user_invite_lists[0] = array();
            ksort($user_invite_lists);
            //导出Excel入金表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
            foreach($user_invite_lists as $key => $value){
                $num=$key + 1;
                if($key == 0){
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, '编号')
                              ->setCellValue('B'.$num, "会员ID")
                              ->setCellValue('C'.$num, "用户名")
                              ->setCellValue('D'.$num, "投资金额（元）");
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, $num-1)
                              ->setCellValue('B'.$num, $value['user_info']['id'])
                              ->setCellValue('C'.$num, $value['user_info']['user_name'])
                              ->setCellValue('D'.$num, $value['total_money']);
                }
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = app_conf("SHOP_TITLE") . "用户邀请投资排行榜统计表";
            php_export_excel($objPHPExcel,$filename);

    }

    private function getlist($extwhere){
		//分类
		$cate_tree = M("DealCate")->where('is_delete = 0')->findAll();
		$cate_tree = D("DealCate")->toFormatTree($cate_tree,'name');
		$this->assign("cate_tree",$cate_tree);
                
    	        $conditon = " 1=1 AND dl.is_auto = 0 AND dl.contract_no != '' ";
		//开始加载搜索条件
		if(intval($_REQUEST['deal_id'])>0)
		{
			$conditon .= " and dl.deal_id = ".intval($_REQUEST['deal_id']);
		}

		if(intval($_REQUEST['cate_id'])>0)
		{
			require_once APP_ROOT_PATH."system/utils/child.php";
			$child = new Child("deal_cate");
			$cate_ids = $child->getChildIds(intval($_REQUEST['cate_id']));
			$cate_ids[] = intval($_REQUEST['cate_id']);
			$conditon .= " and d.cate_id in (".implode(",",$cate_ids).")";
		}

		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";

			$ids = $GLOBALS['db']->getOne($sql);
			if($ids)
				$conditon .= " and dl.user_id in ($ids) ";
			else
				$conditon .= " and dl.user_id = 0 ";
		}
                //手机查询筛选
                if(trim($_REQUEST['mobile'])!='')
		{
			$sql  ="select id from ".DB_PREFIX."user where mobile = '".trim($_REQUEST['mobile'])."'";

			$ids = $GLOBALS['db']->getOne($sql);
			if($ids){
				$conditon .= " and dl.user_id in ($ids) ";
                        }else{
				$conditon .= " and dl.user_id = 0 ";
                        }
		}

		if(intval($_REQUEST['user_id']) > 0){
			$sql  ="select user_name from ".DB_PREFIX."user where id='".intval($_REQUEST['user_id'])."'";
			$_REQUEST['user_name'] = $GLOBALS['db']->getOne($sql);
			$conditon .= " and dl.user_id = ".intval($_REQUEST['user_id']);
		}

		$begin_time  = trim($_REQUEST['begin_time'])==''?0:to_timespan($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time'])==''?0:to_timespan($_REQUEST['end_time']);
		if($begin_time > 0 || $end_time > 0){
			if($end_time==0)
			{
				$conditon .= " and dl.create_time >= $begin_time ";
			}
			else{
				$conditon .= " and dl.create_time between  $begin_time and $end_time ";
			}
		}
		$sql="SELECT dl.*,(dl.money+dl.pure_interests+dl.coupon_interests+dl.act_interests) as repay_money,d.name,d.jiexi_time,d.repay_time,d.repay_time_type,d.loantype,d.rate,d.cate_id FROM ".DB_PREFIX."deal_load dl LEFT JOIN ".DB_PREFIX."deal d ON d.id =dl.deal_id where $conditon $extwhere";
		$list=$this->_Sql_list(D(), $sql,'','id',false);
	}
}
?>