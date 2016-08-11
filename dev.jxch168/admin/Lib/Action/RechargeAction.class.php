<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceAction
 *
 * @author xuchaomin
 */
class RechargeAction extends CommonAction
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        import("ORG.Util.Page");
        $is_paid    = isset($_REQUEST['is_paid']) ? trim($_REQUEST['is_paid']) : '';
        $mobile     = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : to_date(time() - (7 * 3600 * 24), "Y-m-d H:i:s");
        $end_time   = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : to_date(time(), "Y-m-d H:i:s");
        if(strtotime($end_time)-strtotime($begin_time)>31 * 3600 * 24){
             $this->error("查询时间间隔不能大于31天");
        }
        $where = '';
        $url = '';
        if (!empty($mobile)) {
            $where .= "AND u.mobile='{$mobile}' ";
            $url .= "&mobile='{$mobile}'";
        }
        if ($is_paid == 1) {
            $where .= "AND pn.is_paid=1 ";
            $url .= "&is_paid=1";
        } elseif ($is_paid == 2) {
            $where .= "AND pn.is_paid=0 ";
            $url .="&is_paid=0";
        } elseif ($is_paid == 3) {
            $where.="AND pn.notice_sn is null "; // 未充值
            $url .="&is_paid=3";
        }
        if (!empty($begin_time) && !empty($end_time)) {
            $begin_time_totime = strtotime($begin_time);
            $end_time_totime   = strtotime($end_time);
            if ($is_paid != 3) {
                $where.= "AND pn.create_time>'{$begin_time_totime}' AND pn.create_time<'{$end_time_totime}' ";
            }else{
                $where.= "AND u.create_time>'{$begin_time_totime}' AND u.create_time<'{$end_time_totime}' ";
            }
            $url .="&begin_time={$begin_time}&end_time={$end_time}";
        }
        $where    = trim($where, 'AND');
        //echo $is_paid;
        $recharge = M("Payment_notice");
        $user     = M("User");
        if (!empty($mobile) || $is_paid!=0) {
            // 有条件，充值成功和或者失败的
            $sql       = "select *,u.id as uid,u.create_time as reg_time from fanwe_user u left join ".DB_PREFIX."payment_notice pn on u.id = pn.user_id where u.user_type = 0  and  u.is_auto = 0  AND u.acct_type is null  and u.is_effect = 1 AND u.is_delete = 0 and".$where." group by u.id ";
            $res_count = $recharge->query($sql);
             //取得满足条件的记录数
            $incharge_count = count($res_count);
            if ($incharge_count > 0) {
                 //输出投标列表 分页参数
                $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
                //echo $where;
                $page_size = 20;
                 if ($page == 0){
                    $page = 1;
                }
                 if($_REQUEST['type'] == "export_incharge"){
                    $limit='';
                }else{
                    $limit = (($page - 1) * $page_size) . "," . $page_size;
                }
                //分页查询数据
                $_sql  = "select *,u.id as uid,u.create_time as reg_time from fanwe_user u left join ".DB_PREFIX."payment_notice pn on u.id = pn.user_id where u.user_type = 0  and  u.is_auto = 0  AND u.acct_type is null  and u.is_effect = 1 AND u.is_delete = 0 and".$where." group by u.id ";
                if($limit){
                    $_sql .= "limit ".$limit;
                }
                 $res= $recharge->query($_sql);
              }

        } else {
            // 默认为空
            $res = array();
        }
        if ($res) {
            foreach ($res as $k => $v) {
                if ($v['is_paid'] == 1) {
                    $v['is_paid'] = '充值成功';
                } elseif ($v['is_paid'] == '0') {
                    $v['is_paid'] = $v['resp_describle'];
                } else {
                    $v['is_paid'] = '未充值';
                    $v['user_id'] = $v['id'];
                }
                $list[] = $v;
            }
        }

        if($_REQUEST['type'] == "export_incharge"){
                    //导出Excel入金表
                    require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
                    $objPHPExcel = new PHPExcel();
                    $incharge_lists = array();
                    foreach($list as $key => $value){
                        $incharge_lists[$key+1] = $value;
                    }
                    $incharge_lists[0] = array();
                    ksort($incharge_lists);
                    /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
                    foreach($incharge_lists as $key => $value){
                        $num=$key + 1;
                        if($key == 0){
                            $objPHPExcel->setActiveSheetIndex(0)
                                      ->setCellValue('A'.$num, '编号')
                                      ->setCellValue('B'.$num, "用户名")
                                      ->setCellValue('C'.$num, "手机号")
                                      ->setCellValue('D'.$num, "注册时间")
                                      ->setCellValue('E'.$num, "充值状态");
                        }else{
                            $objPHPExcel->setActiveSheetIndex(0)
                                     ->setCellValue('A'.$num, $value["id"])
                                     ->setCellValue('B'.$num, $value["user_name"])
                                      ->setCellValue('C'.$num, $value["mobile"])
                                      ->setCellValue('D'.$num, $value["reg_time"])
                                      ->setCellValue('E'.$num, $value['is_paid']);

                            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num,str_pad(($num-1),4,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
                        }
                    }
                    //设置属性
                    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
                    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getStyle( 'A1:E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle( 'A1:E1')->getFill()->getStartColor()->setARGB('FFFFD700');
                    $filename = app_conf("SHOP_TITLE") . "充值异常记录表";
                    php_export_excel($objPHPExcel,$filename);
        }

        $rs_count = $incharge_count;
        $page_all = ceil($rs_count / $page_size);
        $this->assign("page_all", $page_all);
        $this->assign("rs_count", $rs_count);
        $this->assign("page", $page);
        $this->assign("page_prev", $page - 1);
        $this->assign("page_next", $page + 1);
        $this->assign("url", $url);
        $this->assign('list', $list);
        $this->assign('begin_time',$begin_time);
        $this->assign('end_time',$end_time);
        $this->display();
    }
}
