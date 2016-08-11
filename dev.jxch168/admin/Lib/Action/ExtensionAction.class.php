<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExtensionAction
 *
 * @author ningchengzeng
 */
class ExtensionAction extends CommonAction{
      public function com_search(){
      		$map = array ();
      		if (!isset($_REQUEST['end_time']) || $_REQUEST['end_time'] == '') {
      			$_REQUEST['end_time'] = to_date(get_gmtime(), 'Y-m-d');
      		}
      		
      		if (!isset($_REQUEST['start_time']) || $_REQUEST['start_time'] == '') {
      			$_REQUEST['start_time'] = dec_date($_REQUEST['end_time'], 7);// $_SESSION['q_start_time_7'];
      		}
      	

      		$map['start_time'] = trim($_REQUEST['start_time']);
      		$map['end_time'] = trim($_REQUEST['end_time']);
      	
      	
      		$this->assign("start_time",$map['start_time']);
      		$this->assign("end_time",$map['end_time']);
      	
      	
      		$d = explode('-',$map['start_time']);
      		if (checkdate($d[1], $d[2], $d[0]) == false){
      			$this->error("开始时间不是有效的时间格式:{$map['start_time']}(yyyy-mm-dd)");
      			exit;
      		}
      	
      		$d = explode('-',$map['end_time']);
      		if (checkdate($d[1], $d[2], $d[0]) == false){
      			$this->error("结束时间不是有效的时间格式:{$map['end_time']}(yyyy-mm-dd)");
      			exit;
      		}
      	
      		if (to_timespan($map['start_time']) > to_timespan($map['end_time'])){
      			$this->error('开始时间不能大于结束时间');
      			exit;
      		}
      		$q_date_diff = 70;
      		$this->assign("q_date_diff",$q_date_diff);
      		if ($q_date_diff > 0 && (abs(to_timespan($map['end_time']) - to_timespan($map['start_time'])) / 86400  + 1 > $q_date_diff)){
      			$this->error("查询时间间隔不能大于  {$q_date_diff} 天");
      			exit;
      		}
      		
      		return $map;
  	 }
    
    
    public function index(){
         $map = $this->com_search();

        $channel = $_REQUEST['channel'];
        
        $sql_str = "select "
                . "datetime as 时间 ,"
                . "channel as 渠道 ,"
                . "down as 软件,"
                . "activity as 激活,"
                . "register as 注册,"
                . "buy as 购买,"
                . "ofbuy as 购买回调失败, "
                . "buymoney as 当天注册当天投资, "
                . "moneys as 首次当天入金非当天注册 "
                . "from ".DB_PREFIX."statistical_mchannel_analysis where "
                . " TO_DAYS(datetime) >= TO_DAYS('".$map['start_time']."') and TO_DAYS(datetime) <= TO_DAYS('".$map['end_time']."') ";
        if(!empty($channel)){
         	$sql_str .= " and channel = '".$channel."'";
        }
        $sql_str.=" order by datetime desc";
        $model = D();
        $voList = $this->_Sql_list($model, $sql_str);
        $conf_lists = require_once APP_ROOT_PATH . "data_conf/search_channel_config.php";
        $this->assign("channel_arr",$conf_lists['ios']);
        $this->assign("channel",$channel);
        $this->display();
    }

    function do_export_load($page = 1) {
    	$map = $this->com_search();
    	
        $channel = $_REQUEST['channel'];

		$sql_str = "select "
                . "datetime,"
                . "channel,"
                . "down,"
                . "activity,"
                . "register,"
                . "buy,"
                . "ofbuy,"
                . "buymoney "
                . "from ".DB_PREFIX."statistical_mchannel_analysis where "
                . " TO_DAYS(datetime) >= TO_DAYS('".$map['start_time']."') and TO_DAYS(datetime) <= TO_DAYS('".$map['end_time']."') "
                . " order by datetime desc";
                
        if(!empty($channel)){
        	$sql_str .= " and channel = '".$channel."'";
        }
        
        $model = D();
        $volist = $model->query($sql_str);

        //导出Excel入金表
        require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A1', '时间')
                          ->setCellValue('B1', "渠道")
                          ->setCellValue('C1', "软件下载")
                          ->setCellValue('D1', "激活")
                          ->setCellValue('E1', "注册")
                          ->setCellValue('F1', "购买")
                          ->setCellValue('G1', "购买回调失败")
                          ->setCellValue('H1', "购买金额");

        /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
        foreach($volist as $key => $value){
        	$num= $key + 2;
            $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, $value['datetime'])
                          ->setCellValue('B'.$num, $value['channel'])
                          ->setCellValue('C'.$num, $value['down'])
                          ->setCellValue('D'.$num, $value['activity'])
                          ->setCellValue('E'.$num, $value['register'])
                          ->setCellValue('F'.$num, $value['buy'])
                          ->setCellValue('G'.$num, $value['ofbuy'])
                          ->setCellValue('H'.$num, $value["buymoney"]);
        }
        
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);

        $filename = "IOS 推广分析";
        php_export_excel($objPHPExcel,$filename);
    }
}