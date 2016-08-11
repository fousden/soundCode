<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MatterAction extends CommonAction {

    public function __construct() {
        parent::__construct();
    }

    public function index($prize_type) {
//        $conf_list = array(
//            '-1' => '全部',
//            '0' => '金享有礼爱车活动',
//            '1' => '大转盘活动',
//            '3' => '抽奖活动',
//            '20151106' => '2015金博会现场注册抽奖',
//            'market_activities' => '市场合作抽奖活动',
//            'retail_financial' => '零售供应链金融活动',
//            'yuemojiaxi' => '金享票号月末加息活动',
//        );
        $lotter = M("User_lottery_log");
        $conf_list = $lotter->field("prize_desc")->where("prize_desc not like '%投%'")->order("id desc")->group("prize_desc")->select();
        import("ORG.Util.Page");

        $where = " 1=1 ";
        if (isset($_REQUEST['prize_type'])) {
            $prize_type = trim($_REQUEST['prize_type']);
            if ($prize_type != -1) {
                $where.=" and prize_type=$prize_type";
            }
        } else {
            $prize_type = -1;
            $_REQUEST['prize_type'] = -1;
        }

        if ($_REQUEST['prize_desc']) {
            $where.=" and prize_desc='{$_REQUEST['prize_desc']}'";
        }

        if ($_REQUEST['start_time']) {
            $start_time = strtotime($_REQUEST['start_time']);
            $where.=" and create_time>$start_time ";
        }
        if ($_REQUEST['end_time']) {
            $end_time = strtotime($_REQUEST['end_time']);
            $where.=" and create_time<$end_time ";
        }

        if (empty($_REQUEST['status']) && $_REQUEST['status'] != '0') {
            $_REQUEST['status'] = -1;
        } else {
            if ($_REQUEST['status'] != -1) {
                $where.=" and status={$_REQUEST['status']}";
            }
        }
        $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $prize_name = isset($_REQUEST['prize_name']) ? trim($_REQUEST['prize_name']) : '';
        $prize_desc = isset($_REQUEST['prize_desc']) ? trim($_REQUEST['prize_desc']) : '';
        $is_mohu = isset($_REQUEST['is_mohu']) ? trim($_REQUEST['is_mohu']) : '';
        if ($user_name) {
            $condition = "user_name";
            $condition.=$is_mohu ? " like '%$user_name%'" : "='$user_name' ";
            $mobile_arr = M("user")->field("mobile")->where($condition)->select();
            $mobile_arrs = array_map('array_shift', $mobile_arr);
            $where.=" and mobile in(" . implode(",", $mobile_arrs) . ")";
        }
        if ($mobile) {
            $where.=" and mobile";
            $where.=$is_mohu ? " like '%$mobile%'" : "='$mobile' ";
        }
        if ($prize_name) {
            $where.=" and prize_name";
            $where.=$is_mohu ? " like '%$prize_name%'" : "='$prize_name' ";
        }
        if ($prize_desc) {
            $where.=" and prize_desc";
            $where.=$is_mohu ? " like '%$prize_desc%'" : "='$prize_desc' ";
        }
        $count = $lotter->where($where)->getField('count(*)');
        $Page = new Page($count);
        $show = $Page->show();
        $nowPage = isset($_GET['p']) ? $_GET['p'] : 1;
        $pageStart = ($nowPage - 1) * ($Page->listRows);
        //是否导出记录
        if ($_REQUEST["oper_type"] == "export") {
            $res = $lotter->where($where)->order("id desc,lotter_id desc")->select();

            $this->export_all_matter($res);
        } else {
            $res = $lotter->where($where)->order("id desc,lotter_id desc")->limit($pageStart . ',' . $Page->listRows)->select();
        }
        $this->assign('conf_list', $conf_list);
//        $this->assign('info', $info);
        $this->assign('list', $res);
        $this->assign('page', $show);
        $this->display();
    }

    public function set_present() {
        $lotter = M("User_lottery_log");
        $id = $_GET['id'];
        $data['status'] = 1;
        $res = $lotter->where(array('id' => array('in', $id)))->save($data);
        if ($res) {
            $msg = 1; // 领取成功
        }
        print_r(json_encode($msg));
    }

    function export_all_matter($matter_lists) {
        //导出Excel表
        require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $matter_list = array();
        //数据格式化
        foreach ($matter_lists as $key => $value) {
            $user_info = M("user")->field(array('id', 'user_name', 'real_name', 'mobile'))->where(array("mobile" => $value['mobile'], "is_delete" => array('eq', 0)))->find();
            if ($user_info) {
                $value['user_name'] = $user_info['user_name'] . '[' . $user_info['real_name'] . '][' . $user_info['mobile'] . ']';
                $user_address = M("user_address")->field(array('id', 'province', 'city', 'address', 'code'))->where(array("user_id" => $user_info['id']))->find();
                if ($user_address) {
                    $value['province'] = $user_address['province'];
                    $value['city'] = $user_address['city'];
                    $value['address'] = $user_address['address'];
                    $value['code'] = $user_address['code'];
                }
            }

            $invest_money = M("deal_load")->where(array("user_id" => $user_id, "is_auto" => 0, "contract_no" => array("neq", "")))->getField("sum(money)");
            $value["invest_money"] = number_format($invest_money, 2);

            if (!$value['status']) {
                $value['status_desc'] = "尚未领取";
            } else {
                $value['status_desc'] = "已经领取";
            }

            $matter_list[$key + 1] = $value;
        }
        $matter_list[0] = array();
        ksort($matter_list);
        /* 以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改 */
        foreach ($matter_list as $key => $value) {
            $num = $key + 1;
            if ($key == 0) {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $num, '序号')
                        ->setCellValue('B' . $num, '编号')
                        ->setCellValue('C' . $num, "创建时间")
                        ->setCellValue('D' . $num, "账户名")
                        ->setCellValue('E' . $num, "手机号")
                        ->setCellValue('F' . $num, "投资总额")
                        ->setCellValue('G' . $num, "抽奖消耗金额")
                        ->setCellValue('H' . $num, "礼品名称")
                        ->setCellValue('I' . $num, "礼品描述")
                        ->setCellValue('J' . $num, "礼品领取")
                        ->setCellValue('K' . $num, "省份")
                        ->setCellValue('L' . $num, "城市")
                        ->setCellValue('M' . $num, "详细地址")
                        ->setCellValue('N' . $num, "邮编");
            } else {
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('B' . $num, $value['id'])
                        ->setCellValue('C' . $num, date("Y-m-d H:i:s", $value['create_time']))
                        ->setCellValue('D' . $num, $value['user_name'])
                        ->setCellValue('E' . $num, $value['mobile'])
                        ->setCellValue('F' . $num, $value['invest_money'])
                        ->setCellValue('G' . $num, number_format($value['use_money']))
                        ->setCellValue('H' . $num, $value['prize_name'])
                        ->setCellValue('I' . $num, $value['prize_desc'])
                        ->setCellValue('J' . $num, $value['status_desc'])
                        ->setCellValue('K' . $num, $value['province'])
                        ->setCellValue('L' . $num, $value['city'])
                        ->setCellValue('M' . $num, $value['address'])
                        ->setCellValue('N' . $num, $value['code']);

                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $num, str_pad(($num - 1), 4, "0", STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
            }
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = app_conf("SHOP_TITLE") . "金博会实物清单表";
        php_export_excel($objPHPExcel, $filename);
    }

    public function purchase() {
        $where['id'] = array("in", $_REQUEST['id']);
        $data['is_purchase'] = (int) $_REQUEST['is_purchase'];
        M("user_lottery_log")->where($where)->save($data);
        $root['info'] = $data['is_purchase'] == 1 ? '是' : '否';
        ajax_return($root);
    }

}
