<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContracttongjiAction
 *
 * @author lujun
 */
class ContracttongjiAction extends Action
{

    public function _initialize()
    {
//        $action = array(
//            'permission' => array(),
//            'allow'      => array('changecontent', 'listdialog', 'getcontractlist')
//        );
//        B('Authenticate', $action);
    }

    public function index()
    {
        $list = D('contract')->field(" other_td,sum(investment_money) as moneysum,year,month ")->where(" year >= 2015 and other_td !='' ")->group("year,month,other_td")->select();

        $xAxis_pot   = array(); //["20151106", "20151107", "20151108", "20151109", "20151110", "20151111", "20151112"];
        $yAxis_title = '报表';
        $data_name   = array(); //["\u5168\u90e8", "web", "wap", "Android", "ios"];
        $data_array  = array(); //[[112700, 91508, 43160, 295101, 395618, 293536, 669351], [0, 0, 2000, 180100, 0, 82000, 130324], [0, 0, 0, 0, 0, 0, 0], [61200, 21508, 7060, 58000, 29618, 21440, 169750], [51500, 70000, 34100, 57001, 366000, 190096, 369277]];
        $unit        = '报表';
        $series_name = 'series_name';

        $zcT = array();
        if ($list) {
            foreach ($list as $val) {
                $zcT[$val['other_td']][$val['year'] . '-' . $val['month']] = $val;
            }

            $data_name = array_keys($zcT);
            for ($i = 1; $i < 13; $i++) {
                $xAxis_pot[] = '2015-' . $i;
            }

            foreach ($zcT as $zcname => $zcVal) {
                $dayArr = array();
                for ($i = 1; $i < 13; $i++) {
                    if (isset($zcVal['2015-' . $i])) {
                        $dayArr[] = $zcVal['2015-' . $i]['moneysum'] / 100;
                    } else {
                        $dayArr[] = 0;
                    }
                }

                $data_array[] = $dayArr;
            }
        }
        //var_dump(count($xAxis_pot),count($data_array),count($data_name));exit;
        $this->xAxis_pot  = json_encode($xAxis_pot);
        $this->data_name  = json_encode($data_name);
        $this->data_array = json_encode($data_array);

        $this->xAxis_pot_arr  = $xAxis_pot;
        $this->data_name_arr  = $data_name;
        $this->data_array_arr = $data_array;

        $this->display();
    }

    public function select()
    {
        //门店信息
        $department_list       = M('roleDepartment')->where('parent_id = %d', 0)->select();
        array_shift($department_list);
        array_shift($department_list);
        array_shift($department_list);
        $this->department_list = $department_list;
        //产品列表
        $this->productList     = D('ProductView')->select();
        $whereArr              = array();
        if ($_GET['department_id']) {
            $whereArr['contract.department_id'] = $_GET['department_id'];
        }
        if ($_GET['product_id']) {
            $whereArr['contract.product_id'] = $_GET['product_id'];
        }

        if ($_GET['start_date']) {
            $whereArr['contract.start_date'] = array($_GET['start_date_type'], $_GET['start_date']);
        }
        if ($_GET['end_date']) {
            $whereArr['contract.end_date'] = array($_GET['end_date_type'], $_GET['end_date']);
        }

        //TODO 付息日

        $contract = D('ContractView');
        $list     = $contract->where($whereArr)->select();
        if ($list)
        {
            foreach($list as $key => $val){
                $list[$key]['department_name'] = M('roleDepartment')->where('department_id = %d', $val['department_id'])->getField('name');
                $list[$key]['product_name'] = D('ProductView')->where('product.product_id = %d',$val['product_id'])->getField('name');
            }
        }
        $this->list=$list;
        $this->display();
    }

}
