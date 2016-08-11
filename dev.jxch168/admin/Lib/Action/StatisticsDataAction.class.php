<?php

class StatisticsDataAction extends CommonAction {

    public function statistics_terrace() {
        $start_time = isset($_REQUEST['start_time']) ? $_REQUEST['start_time'] : to_date(strtotime("-8 day"), "Y-m-d");
        $end_time = isset($_REQUEST['end_time']) ? $_REQUEST['end_time'] : to_date(strtotime("-1 day"), "Y-m-d");
        $date = to_date(time(), "Y-m-d");
        if ($start_time <= $date && $end_time >= $date) {
            require_once APP_ROOT_PATH . "cli/statisticalRun.php";
            $statistica = new statisticalRun();
            $statistica->importing_date($date);
        }
        $conf_lists = require_once APP_ROOT_PATH . "data_conf/search_channel_config.php";
        $conf_list = array();
        $statistical = array();
        $i = 1;
        foreach ($conf_lists as $key => $val) {
            $statistical[$i++] = $key;
            foreach ($val as $k => $v) {
                $conf_list[$k] = $v;
            }
        }
        $conf_str = 'null';
        foreach ($conf_list as $key => $val) {
            $conf_str.=",'" . $key . "'";
        }
        $this->assign("statistical", $statistical);
        $this->assign("channel_conut", $channel_conut);
        $sql_str = '';
        $sql = '';
        if (isset($_REQUEST['statistical'])) {
            $statistical_arr = $_REQUEST['statistical'];
//	    foreach($statistical_arr as $key=>$val){
//		if($val==1){
//		    $val='web';
//		}elseif($val==2){
//		    $val='wap';
//		}elseif($val==3){
//		    $val='Android';
//		}elseif($val==4){
//		    $val='ios';
//		}
//		$choise_arr[] = $conf_lists[$val];
//	    }
            $str = implode(',', $statistical_arr);
            $sql_str = "and statistical_type in($str)";
            $this->assign("statistical_arr", $statistical_arr);
        }
        if (isset($_REQUEST['choise_web']) || isset($_REQUEST['choise_wap']) || isset($_REQUEST['choise_Android']) || isset($_REQUEST['choise_ios'])) {
            $choise_arr = array();
            if (isset($_REQUEST['choise_web'])) {
                $choise_web = $_REQUEST['choise_web'];
                $choise_arr[] = array_values($choise_web);
                $this->assign("choise_web", $choise_web);
            }
            if (isset($_REQUEST['choise_wap'])) {
                $choise_wap = $_REQUEST['choise_wap'];
                $choise_arr[] = array_values($choise_wap);
                $this->assign("choise_wap", $choise_wap);
            }
            if (isset($_REQUEST['choise_Android'])) {
                $choise_Android = $_REQUEST['choise_Android'];
                $choise_arr[] = array_values($choise_Android);
                $this->assign("choise_Android", $choise_Android);
            }
            if (isset($_REQUEST['choise_ios'])) {
                $choise_ios = $_REQUEST['choise_ios'];
                $choise_arr[] = array_values($choise_ios);
                $this->assign("choise_ios", $choise_ios);
            }

            $choise = array();
            foreach ($choise_arr as $key => $val) {
                foreach ($val as $k => $val) {
                    $choise[] = "'$val'";
                }
            }
            $conf_str = implode(',', $choise);
        }

        $sqls = "SELECT * FROM " . DB_PREFIX . "statistical_data WHERE channel_type in($conf_str ) and date>='$start_time' and date<='$end_time' $sql_str $sql ";
        if ($_REQUEST['submit'] || $_REQUEST['date'] || $_REQUEST['xls']) {
            $list = $GLOBALS['db']->getAll($sqls);
            foreach ($list as $key => $val) {
                $gross_info['user_reg_count']+=$val['user_reg_count'];
                $gross_info['pingan_count']+=$val['pingan_count'];
                $gross_info['activation_count']+=$val['activation_count'];
                $gross_info['deal_user_count']+=$val['deal_user_count'];
                $gross_info['deal_count']+=$val['deal_count'];
                $gross_info['deal_month_sum']+=$val['deal_month_sum'];
                $gross_info['payment_sum']+=$val['payment_sum'];
            }
            $this->assign("gross_info", $gross_info);
            $list = $this->_Sql_list(D(), $sqls, '', 'date', false);
            foreach ($list as $key => $val) {
                $list[$key]['channel_type_zh'] = $conf_list[$val['channel_type']];
            }
            $this->assign("list", $list);
        }
        if ($_REQUEST['xls'] == 'true') {
            $list = $GLOBALS['db']->getAll($sqls);
            krsort($list);
            $count = count($list) + 1;
            foreach ($list as $key => $val) {
                $list[$count]['activation_count']+=$val['activation_count'];
                $list[$count]['user_reg_count']+=$val['user_reg_count'];
                $list[$count]['pingan_count']+=$val['pingan_count'];
                $list[$count]['deal_user_count']+=$val['deal_user_count'];
                $list[$count]['deal_count']+=$val['deal_count'];
                $list[$count]['deal_month_sum']+=$val['deal_month_sum'];
                $list[$count]['payment_sum']+=$val['payment_sum'];
                $list[$count]['date'] = "总计";
                $list[$key]['channel_type_zh'] = $conf_list[$val['channel_type']];
                $list[$key]['statistical_type_zh'] = $statistical[$val['statistical_type']];
            }

            if ($start_time <= $date && $end_time >= $date) {
                $sql = "delete from " . DB_PREFIX . "statistical_data where date='$date'";
                $GLOBALS['db']->query($sql);
            }
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num = 3;
            foreach ($list as $key => $val) {
                if ($num == 3) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '日期')
                            ->setCellValue('B' . $num, '激活数')
                            ->setCellValue('C' . $num, "注册人数")
                            ->setCellValue('D' . $num, "交易人数")
                            ->setCellValue('E' . $num, "充值额")
                            ->setCellValue('F' . $num, "交易笔数")
                            ->setCellValue('G' . $num, "交易额")
                            ->setCellValue('H' . $num, "来源")
                            ->setCellValue('I' . $num, "渠道")
                            ->setCellValue('J' . $num, "平安保险成功")
                            ->setCellValue('A1', "平台数据统计表")
                            ->setCellValue('A2', $start_time . '~' . $end_time);
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $val['date'])
                        ->setCellValue('B' . $num, $val['activation_count'])
                        ->setCellValue('C' . $num, $val['user_reg_count'])
                        ->setCellValue('D' . $num, $val['deal_user_count'])
                        ->setCellValue('E' . $num, $val['payment_sum'])
                        ->setCellValue('F' . $num, $val['deal_count'])
                        ->setCellValue('G' . $num, $val['deal_month_sum'])
                        ->setCellValue('H' . $num, $val['statistical_type_zh'])
                        ->setCellValue('I' . $num, $val['channel_type'])
                        ->setCellValue('J' . $num, $val['pingan_count']);
                $num++;
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A3:J3')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = $start_time . '~' . $end_time . "的平台数据统计表";
            php_export_excel($objPHPExcel, $filename);
            exit;
        }
        $this->assign('conf_list', $conf_list);
        $this->assign('conf_lists', $conf_lists);
        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        if ($start_time <= $date && $end_time >= $date) {
            $sql = "delete from " . DB_PREFIX . "statistical_data where date='$date'";
            $GLOBALS['db']->query($sql);
        }
        $this->display();
    }

    public function statistics_user() {
        $from = isset($_REQUEST['from']) ? $_REQUEST['from'] : '';
        $u_start_time = isset($_REQUEST['u_start_time']) ? $_REQUEST['u_start_time'] : ($from ? '' : to_date(strtotime("-8 day"), "Y-m-d"));
        $u_end_time = isset($_REQUEST['u_end_time']) ? $_REQUEST['u_end_time'] : ($from ? '' : to_date(strtotime("-1 day"), "Y-m-d"));
        $dl_start_time = isset($_REQUEST['dl_start_time']) ? $_REQUEST['dl_start_time'] : ($from ? '' : to_date(strtotime("-1 day"), "Y-m-d"));
        $dl_end_time = isset($_REQUEST['dl_end_time']) ? $_REQUEST['dl_end_time'] : ($from ? '' : to_date(strtotime("-1 day"), "Y-m-d"));
        $sql = '';
        $this->assign("u_start_time", $u_start_time);
        $this->assign("u_end_time", $u_end_time);
        $this->assign("dl_start_time", $dl_start_time);
        $this->assign("dl_end_time", $dl_end_time);
        if ($u_start_time && $u_end_time) {
            $sql.="and u.create_date>='$u_start_time' and u.create_date<='$u_end_time' ";
        }
        if ($dl_end_time && $dl_start_time) {
            $sql.= "and dl.create_date>='$dl_start_time' and dl.create_date<='$dl_end_time' ";
        }

        if (isset($_REQUEST['channel_type']) && isset($_REQUEST['statistical_type'])) {
            $channel_type = $_REQUEST['channel_type'];
            $statistical_type = $_REQUEST['statistical_type'];
            $sql.="and u.search_channel='$channel_type' and u.terminal='$statistical_type'";
            $choise_arr = array($channel_type);
            $this->assign("channel_type", $channel_type);
            $this->assign("statistical_type", $statistical_type);
        }
//        if ($_REQUEST['unlimited_dl'] != 1) {
//            $sql2 = "and dl.create_date>='$dl_start_time' and dl.create_date<='$dl_end_time' ";
//        } else {
//            $this->assign("unlimited_dl", $_REQUEST['unlimited_dl']);
//        }
        if (isset($_REQUEST['statistical'])) {
            $statistical_arr = $_REQUEST['statistical'];
            $str = implode(',', $statistical_arr);
            $sql.= "and u.terminal in($str)";
            $this->assign("statistical_arr", $statistical_arr);
        }
        if (isset($_REQUEST['choise_web']) || isset($_REQUEST['choise_wap']) || isset($_REQUEST['choise_Android']) || isset($_REQUEST['choise_ios'])) {
            if (isset($_REQUEST['choise_web'])) {
                $choise_web = $_REQUEST['choise_web'];
                $choise_arr[] = array_values($choise_web);
                $this->assign("choise_web", $choise_web);
            }
            if (isset($_REQUEST['choise_wap'])) {
                $choise_wap = $_REQUEST['choise_wap'];
                $choise_arr[] = array_values($choise_wap);
                $this->assign("choise_wap", $choise_wap);
            }
            if (isset($_REQUEST['choise_Android'])) {
                $choise_Android = $_REQUEST['choise_Android'];
                $choise_arr[] = array_values($choise_Android);
                $this->assign("choise_Android", $choise_Android);
            }
            if (isset($_REQUEST['choise_ios'])) {
                $choise_ios = $_REQUEST['choise_ios'];
                $choise_arr[] = array_values($choise_ios);
                $this->assign("choise_ios", $choise_ios);
            }

            $choise = array();
            foreach ($choise_arr as $key => $val) {
                foreach ($val as $k => $val) {
                    $choise[] = "'$val'";
                }
            }
            $str = implode(',', $choise);
            $sql.= "and u.search_channel in($str)";
        }
        $this->assign("choise_arr", $choise_arr);
        if (!empty($_REQUEST['idno'])) {
            $indo = trim($_REQUEST['idno']);
            $sql.="and u.idno like '%" . $indo . "%'";
        }
        if (!empty($_REQUEST['mobile'])) {
            $mobile = trim($_REQUEST['mobile']);
            $sql.="and u.mobile like '%" . $mobile . "%'";
        }
        if (!empty($_REQUEST['start_age']) || !empty($_REQUEST['end_age'])) {
            $date = to_date(time(), "Y-m-d");
            $start_age = $date - trim($_REQUEST['start_age']);
            $end_age = $date - trim($_REQUEST['end_age']);
            if (!empty($_REQUEST['start_age'])) {
                $sql.="and u.byear<=$start_age";
            } elseif (!empty($_REQUEST['end_age'])) {
                $sql.="and u.byear>=$end_age";
            } else {
                $sql.="and u.byear>=$end_age and u.byear<=$start_age";
            }
        }
        if (!empty($_REQUEST['user_name'])) {
            $user_name = trim($_REQUEST['user_name']);
            $sql.="and u.user_name like '%" . $user_name . "%'";
        }
        if (!empty($_REQUEST['city'])) {
            $city = trim($_REQUEST['city']);
            $sql.="and umh.city like '%" . $city . "%'";
        }
        if (isset($_REQUEST['sex'])) {
            $sex = trim($_REQUEST['sex']);
            $sql.="and u.sex=$sex";
        }
        $conf_lists = require_once APP_ROOT_PATH . "data_conf/search_channel_config.php";
        $conf_list = array();
        $statistical = array();
        $i = 1;
        foreach ($conf_lists as $key => $val) {
            $statistical[$i++] = $key;
            foreach ($val as $k => $v) {
                $conf_list[$k] = $v;
                $conf_arr[] = "'$k'";
            }
        }
        $conf_str = implode(',', $conf_arr);
        $this->assign('conf_lists', $conf_lists);
        $this->assign("statistical", $statistical);
        $sql_str = "SELECT u.user_name
	    ,u.mobile
	    ,u.sex
	    ,u.idno
	    ,u.byear
	    ,umh.city
	    ,dl.money as money_sum
	    ,d.name
	    ,u.create_time as u_create_time
	    ,dl.create_time as dl_create_time
	    ,u.terminal
	    ,u.search_channel
		FROM " . DB_PREFIX . "user as u LEFT JOIN " . DB_PREFIX . "deal_load as dl on (u.id=dl.user_id) and dl.is_auto=0 $sql2 
		left join " . DB_PREFIX . "deal as d on(d.id=dl.deal_id) 
		left join " . DB_PREFIX . "user_mobile_homeaddress as  umh on(u.mobile=umh.mobile) 
		where u.is_auto=0 and u.acct_type is null $sql and search_channel in ($conf_str)";
        if (!$_REQUEST['xls']) {
            $list = $this->_Sql_list(D(), $sql_str);
            foreach ($list as $key => $val) {
                $list[$key]['search_channel_zh'] = $conf_list[$val['search_channel']];
            }
            $this->assign("list", $list);
        }
        if ($_REQUEST['xls'] == 'true') {
            $list = $GLOBALS['db']->getAll($sql_str);
            $date = to_date(time(), "Y-m-d");
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num = 1;
            $sex_conf = ['-1' => '默认', '0' => '女', '1' => '男'];
            foreach ($list as $key => $val) {
                if ($num == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '账户')
                            ->setCellValue('B' . $num, "手机号")
                            ->setCellValue('C' . $num, "手机归属地")
                            ->setCellValue('D' . $num, "性别")
                            ->setCellValue('E' . $num, "年龄")
                            ->setCellValue('F' . $num, "身份证号")
                            ->setCellValue('G' . $num, "交易额")
                            ->setCellValue('H' . $num, "购买理财产品")
                            ->setCellValue('I' . $num, "注册时间")
                            ->setCellValue('J' . $num, "交易时间")
                            ->setCellValue('K' . $num, "来源")
                            ->setCellValue('L' . $num, "渠道");
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $val['user_name'])
                        ->setCellValue('B' . $num, $val['mobile'])
                        ->setCellValue('C' . $num, $val['city'])
                        ->setCellValue('D' . $num, $sex[$val['sex']])
                        ->setCellValue('E' . $num, $val['byear'] ? $date - $val['byear'] : '')
                        ->setCellValue('F' . $num, '"' . $val[idno] . '"')
                        ->setCellValue('G' . $num, $val['money_sum'])
                        ->setCellValue('H' . $num, $val['name'])
                        ->setCellValue('I' . $num, to_date($val['u_create_time'], 'Y-m-d H:i:s'))
                        ->setCellValue('J' . $num, to_date($val['dl_create_time'], 'Y-m-d H:i:s'))
                        ->setCellValue('K' . $num, $statistical[$val['terminal']])
                        ->setCellValue('L' . $num, $conf_list[$val['search_channel']]);
                $num++;
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(5);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = "用户数据统计表";
            php_export_excel($objPHPExcel, $filename);
        }
        $this->display();
    }

    /**
     * 针对标的做还款统计
     */
    public function statistics_deal_repay() {
        $deal_status = isset($_REQUEST['deal_status']) ? $_REQUEST['deal_status'] : 4;
        $where = ' where is_effect=1 and is_delete=0 ';
        if($deal_status!='-1'){
            $where.=" and deal_status=$deal_status ";
        }
        $sql_str = "SELECT
	id,
	name,
	borrow_amount,
	rate,
        enddate,
        repay_time,
	start_time
FROM
	fanwe_deal $where ";
        if ($_REQUEST['xls'] == 'true') {
            $sql_str.=' order by id desc ';
            $list = $GLOBALS['db']->getAll($sql_str);
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num = 1;
            foreach ($list as $val) {
                if ($num == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, '编号')
                    ->setCellValue('B' . $num, "贷款名称")
                    ->setCellValue('C' . $num, "贷款金额")
                    ->setCellValue('D' . $num, "年化利率(%)")
                    ->setCellValue('E' . $num, "标的开始时间")
                    ->setCellValue('F' . $num, "标的结束时间")
                    ->setCellValue('G' . $num, "利息")
                    ->setCellValue('H' . $num, "还款总额");
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $val['id'])
                        ->setCellValue('B' . $num, $val['name'])
                        ->setCellValue('C' . $num, $val['borrow_amount'])
                        ->setCellValue('D' . $num, $val['rate'])
                        ->setCellValue('E' . $num, to_date($val['start_time']))
                        ->setCellValue('F' . $num, get_deal_end_time($val['start_time'], $val['enddate']))
                        ->setCellValue('G' . $num, get_deal_interest_money($val['borrow_amount'], $val))
                        ->setCellValue('H' . $num, get_deal_total_repay_money($val['borrow_amount'], $val));
                $num++;
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
//            $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
//            $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = "贷款统计";
            php_export_excel($objPHPExcel, $filename);
        } else {
            $sql = "SELECT
	sum(borrow_amount) AS self_money,
        sum(borrow_amount*rate/100/360*repay_time) as interest_money,
        deal_status
FROM
	`fanwe_deal`
        where deal_status in(4,5) 
GROUP BY
	deal_status order by deal_status desc ";
            $total_list=$GLOBALS['db']->getAll($sql);
            foreach($total_list as $val){
                $new_total_list[$val['deal_status']]=$val;
            }
            unset($total_list);
            $this->assign('total_list',$new_total_list);
            $this->_Sql_list(D(), $sql_str, '', 'id', true);
            $this->display();
        }
    }

}

/**
 *获取标的的结束时间
 * @param type $start_time
 * @param type $enddate
 * @return type
 */
function get_deal_end_time($start_time,$enddate){
    return to_date($enddate*86400+$start_time);
}
/**
 * 获得标的的利息
 * @param type $borrow_amount
 * @param type $deal_info
 */
function get_deal_interest_money($borrow_amount,$deal_info){
    return number_format(num_format($borrow_amount*$deal_info['rate']/100/360*$deal_info['repay_time']),2);
}
/**
 * 获得标的还款总额
 * @param type $borrow_amount
 * @param type $deal_info
 * @return type
 */
function get_deal_total_repay_money($borrow_amount,$deal_info){
    return number_format($borrow_amount+get_deal_interest_money($borrow_amount,$deal_info),2);
}
