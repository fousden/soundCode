<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class LotteryAction extends CommonAction {

    public function index() {
	$sql = "where 1=1 ";
	if ($_REQUEST['mobile']) {
	    $sql.=" and mobile like '%{$_REQUEST['mobile']}%' ";
	}
	if ($_REQUEST['start_time']) {
	    $start_time=strtotime($_REQUEST['start_time']);
	    $sql.=" and create_time>$start_time ";
	}
	if ($_REQUEST['end_time']) {
	    $end_time=strtotime($_REQUEST['end_time']);
	    $sql.=" and create_time<$end_time ";
	}
	if ($_REQUEST['prize_type']) {
	    $prize_type=$_REQUEST['prize_type'];
	    $prize_type_str=implode(",",$prize_type);
	    $sql.=" and prize_type in ($prize_type_str) ";
	    $this->assign("prize_type",$prize_type);
	}
	$sql_str = "select * from " . DB_PREFIX . "user_lottery_log $sql";

                    //是否导出记录
                   if($_REQUEST["oper_type"] == "export"){
                       $list = $this->_Sql_list(D(), $sql_str, '', 'id', false,false);
                       $this->export_all_lottery($list);
                   } else {
                       $list = $this->_Sql_list(D(), $sql_str, '', 'id', false,true);
                   }



	$prize_type_arr=array("1"=>"收益券","抵现券","红包类型","实物");
	$this->assign("prize_type_arr",$prize_type_arr);
	$this->display();
    }

    public function coupon_index() {
	$sql = "where 1=1 ";
	if ($_REQUEST['user_name']) {
	    $sql.=" and user_name like '%".trim($_REQUEST['user_name'])."%' ";
	}
	if ($_REQUEST['start_time']) {
	    $start_time=strtotime($_REQUEST['start_time']);
	    $sql.=" and gain_time>$start_time ";
	}
	if ($_REQUEST['end_time']) {
	    $end_time=strtotime($_REQUEST['end_time']);
	    $sql.=" and gain_time<$end_time ";
	}
	if ($_REQUEST['coupon_type']) {
	    $sql.=" and coupon_type={$_REQUEST['coupon_type']} ";
	}
	if ($_REQUEST['status']) {
	    $sql.=" and status={$_REQUEST['status']} ";
	}
	$sql_str = "select * from " . DB_PREFIX . "user_coupon $sql";
	$list = $this->_Sql_list(D(), $sql_str, '', 'id', false);
	$this->display();
    }

    public function entity_index(){
	$sql = "where 1=1 ";
	if ($_REQUEST['mobile']) {
	    $sql.=" and mobile like '%{$_REQUEST['mobile']}%' ";
	}
	if ($_REQUEST['start_time']) {
	    $start_time=strtotime($_REQUEST['start_time']);
	    $sql.=" and create_time>$start_time ";
	}
	if ($_REQUEST['end_time']) {
	    $end_time=strtotime($_REQUEST['end_time']);
	    $sql.=" and create_time<$end_time ";
	}
	$sql_str = "select * from " . DB_PREFIX . "user_lottery_log where prize_type=4";
	$list = $this->_Sql_list(D(), $sql_str, '', 'id', false);
	$this->display();
    }

    //导出记录
    function export_all_lottery($list){
        //导出Excel表
        require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $lottery_list = array();
        //数据格式化
        foreach($list as $key => $value){
            $user_id = M("user")->where(array("mobile"=>$value['mobile']))->getField("id");
            $invest_money = M("deal_load")->where(array("user_id"=>$user_id,"is_auto"=>0,"contract_no"=>array("neq","")))->getField("sum(money)");
            $value["invest_money"] = $invest_money;
            $value["user_name"] = M("user")->where(array("mobile"=>$value['mobile']))->getField("user_name");
            if($value['prize_type']==1){
                $value['prize_type_name'] = '收益券';
            }else if($value['prize_type']==2){
                $value['prize_type_name'] = '抵现券';
            }else if($value['prize_type']==3){
                $value['prize_type_name'] = '红包类型';
            }else if($value['prize_type']==4){
                $value['prize_type_name'] = '实物';
            }
            $lottery_list[$key+1] = $value;
        }
        $lottery_list[0] = array();
        ksort($lottery_list);
        /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
        foreach($lottery_list as $key => $value){
            $num=$key + 1;
            if($key == 0){
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, '序号')
                          ->setCellValue('B'.$num, '编号')
                          ->setCellValue('C'.$num, "奖品名称")
                          ->setCellValue('D'.$num, "获得时间")
                          ->setCellValue('E'.$num, "账户名")
                          ->setCellValue('F'.$num, "手机号码")
                          ->setCellValue('G'.$num, "投资总额")
                          ->setCellValue('H'.$num, "奖品的类型")
                          ->setCellValue('I'.$num, "备注");
            }else{
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('B'.$num, $value['id'])
                          ->setCellValue("C".$num, $value['prize_name'])
                          ->setCellValue('D'.$num, date("Y-m-d H:i:s",$value['create_time']))
                          ->setCellValue('E'.$num, $value['user_name'])
                          ->setCellValue('F'.$num, $value['mobile'])
                          ->setCellValue('G'.$num, $value['invest_money'])
                          ->setCellValue('H'.$num,$value['prize_type_name'])
                          ->setCellValue('I'.$num,$value['prize_desc']);

                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num,str_pad(($num-1),4,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
            }
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:H1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = app_conf("SHOP_TITLE") . "奖品记录表";
        php_export_excel($objPHPExcel,$filename);
    }

}

?>