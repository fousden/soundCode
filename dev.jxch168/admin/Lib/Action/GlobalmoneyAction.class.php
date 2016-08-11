<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class GlobalmoneyAction extends CommonAction
{

    public function index()
    {
        $cateId      = (int) $_GET['cate_id'];
        $cateInfoSql = "select * from fanwe_deal_cate;";
        $cateInfo    = $GLOBALS['db']->getAll($cateInfoSql);
        $this->assign("cateInfo", $cateInfo);
        $this->assign("cateId", $cateId);

        $cateOtherSql = '';
        if ($cateId > 0) {
            $cateOtherSql = ' where  d.id = ' . $cateId;
        }
        $timeOtherSql = '';
        $start_time   = $_GET['start_time'];
        $end_time     = $_GET['end_time'];
        $deal_name     = $_GET['deal_name'];

        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
         $this->assign("deal_name", $deal_name);

        if ($start_time && $end_time) {
            $timeOtherSql = ' and a.create_time >= ' . to_timespan($start_time) . ' and a.create_time <= ' . to_timespan($end_time ." 23:59:59");
        }
        $dealNameSql = '';
        if ($deal_name)
        {
            if ($cateOtherSql)
            {
                $cateOtherSql .= " and  c.name like '%$deal_name%' ";
            } else {
                $cateOtherSql =" where c.name like '%$deal_name%' ";
            }

        }

        $sql     = "select d.name as name1,sum(c.money) as cnt,count(c.deal_id) as pcnt from (select b.name,b.cate_id,a.deal_id,a.money from fanwe_deal_load  as a inner join fanwe_deal as b on a.deal_id = b.id where a.is_auto = 0 and a.deal_id > 32 " . $timeOtherSql  . ") as c inner join fanwe_deal_cate as d on c.cate_id = d.id " .  $cateOtherSql . " GROUP BY d.name";
        $typeCnt = $GLOBALS['db']->getAll($sql);
        $cMCnt   = 0;
        $cPCnt   = 0;
        foreach ($typeCnt as $ttK => $ttV) {
            $cMCnt += $ttV['cnt'];
            $cPCnt += $ttV['pcnt'];
        }
        $typeCnt[] = array('name1' => '合计', 'cnt' => $cMCnt, 'pcnt' => $cPCnt);
        $this->assign("typeCnt", $typeCnt);


        $sql1 = "select deal_id,name,cnt,user_name,rate,repay_time,tname,pcnt from (select c.deal_id,c.name,sum(c.money) as cnt,c.agency_id,d.name as tname,c.rate,c.repay_time,count(c.deal_id) as pcnt from (select b.name,b.cate_id,a.deal_id,a.money,b.agency_id,b.rate,b.repay_time from fanwe_deal_load  as a inner join fanwe_deal as b on a.deal_id = b.id and a.is_auto = 0 and a.deal_id > 32" . $timeOtherSql  .") as c INNER join fanwe_deal_cate as d on c.cate_id = d.id " . $cateOtherSql . "  group by c.name order by c.deal_id  desc ) as e inner join fanwe_user as f on e.agency_id = f.id";
        $list = $GLOBALS['db']->getAll($sql1);
        $this->assign("list", $list);

        if ($_GET['xls'] == 'true') {
            require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num         = 1;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, '标的类型数据 ');
            $num         = 2;
            foreach ($typeCnt as $Tkey => $Tval) {
                if ($num == 2) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '标地类型 ')
                            ->setCellValue('B' . $num, "总额 ")
                            ->setCellValue('C' . $num, "人次  ");
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $Tval['name1'])
                        ->setCellValue('B' . $num, $Tval['cnt'])
                        ->setCellValue('C' . $num, $Tval['pcnt']);
                $num++;
            }
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, ' ');
            $num++;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, ' ');
            $num++;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, '现有标的实际投标数据 ');
            $num++;
            $twoB = 1;
            foreach ($list as $Lkey => $Lval) {
                if ($twoB == 1) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A' . $num, '编号')
                            ->setCellValue('B' . $num, "标的名")
                            ->setCellValue('C' . $num, "标的类型")
                             ->setCellValue('D' . $num, "利息")
                             ->setCellValue('E' . $num, "标的天数")
                            ->setCellValue('F' . $num, "担保机构")
                            ->setCellValue('G' . $num, "人次")
                            ->setCellValue('H' . $num, "总额");
                    $twoB = 2;
                    $num++;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, $Lval['deal_id'])
                        ->setCellValue('B' . $num, $Lval['name'])
                        ->setCellValue('C' . $num, $Lval['tname'])
                         ->setCellValue('D' . $num, $Lval['rate'] .'%')
                         ->setCellValue('E' . $num, $Lval['repay_time'])
                        ->setCellValue('F' . $num, $Lval['user_name'])
                        ->setCellValue('G' . $num, $Lval['pcnt'])
                        ->setCellValue('H' . $num, $Lval['cnt']);
                $num++;
            }

            $filename = $start_time . '~' . $end_time . "资源流向";
            php_export_excel($objPHPExcel, $filename);
            exit;
        }
        $this->display();
        return;
    }

}
