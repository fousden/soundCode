<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Statistics
 *
 * @author lujun
 */
class ReportAction extends Action
{

    public function _initialize()
    {
//         $action = array(
//                'permission' => array(),
//                'allow'=>array('signLog'),
//          );
//        B('Authenticate', $action);
    }

    public function index()
    {

        $department_id = (int) $_GET['department_id'];
        if (!isset($_GET['begin_time']) || empty($_GET['begin_time'])) {
            $_GET['begin_time'] = date('Y-m-' . '01');
            $_GET['end_time']   = date('Y-m-t');
        }
        if ($department_id > 0) {
            $departmentList = M('roleDepartment')->where('department_id = %d', $department_id)->select();
        } else {
            $departmentList = departmentList();
        }

        $startTime = strtotime($_GET['begin_time']);
        $endTime   = strtotime($_GET['end_time']);
        $res       = array();
        $tmpRank   = array();
        if ($departmentList) {
            foreach ($departmentList as $val) {
                $row['department_id'] = $val['department_id'];
                $row['name']          = $val['name'];
                //用户用户
                $row['newUsers']      = current(current(M('roleDepartment')->query("SELECT count(*) FROM new5kcrm_customer a LEFT JOIN new5kcrm_role b ON a.owner_role_id = b.role_id "
                                        . "LEFT JOIN new5kcrm_position AS d ON b.position_id = d.position_id LEFT JOIN new5kcrm_role_department AS e ON d.department_id = e.department_id "
                                        . "WHERE a.create_time >= {$startTime} AND a.create_time <={$endTime}   AND e.department_id = {$val['department_id']}")));

                //新增合同
                $row['contractCnt']      = M('contract')->where(" department_id = {$val['department_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->count();
                //exit;
                //入金数
                $row['contractMoneyCnt'] = (M('contract')->where(" department_id = {$val['department_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(investment_money) as investment_money ") ) / 100;
                //平均年龄
                $row['average_age']      = '-';
                //平均周期
                $row['average_cycle']    =M('contract')->where(" department_id = {$val['department_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(closure_period) /count(closure_period) as closure_period ") ;

                //保有量
                $row['retain_quantity']  = '-';
                //入金排名
                $row['entry_into_gold']  = '-';
                //平均利率
                $row['average_interest'] = (M('contract')->where(" department_id = {$val['department_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(investment_rate) /count(investment_rate) as average_interest ") ) / 100;

                //销售数量
                $row['admin_num']         = current(current(M('roleDepartment')->query("SELECT
                                                                                    count(*)
                                                                            FROM
                                                                                    new5kcrm_user USER
                                                                            LEFT JOIN new5kcrm_user_category user_category ON USER .category_id = user_category.category_id
                                                                            LEFT JOIN new5kcrm_role role ON USER .user_id = role.user_id
                                                                            LEFT JOIN new5kcrm_position position ON position.position_id = role.position_id
                                                                            LEFT JOIN new5kcrm_role_department role_department ON role_department.department_id = position.department_id
                                                                            WHERE
                                                                                    (`status` = 1)
                                                                             and role_department.department_id = " . $val['department_id'])));
                //  人均入金
                $row['persContractMoney'] = number_format($row['contractMoneyCnt'] / $row['admin_num'], 2);

                //实动率
                /**
                 * 签约合同的销售数
                 */
                $tmpqxadminnum             = M('contract')->where(" department_id = {$val['department_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->group('owner_role_id')->count();
                $row['real_dynamic_ratio'] = number_format($row['admin_num'] / $tmpqxadminnum * 100, 2);

                $res[]                          = $row;
                //处理排行榜原始数据
                $tmpRank[$val['department_id']] = $row['contractMoneyCnt'];
            }

            //处理排行榜
            arsort($tmpRank);
            $rank = array_keys($tmpRank);
            foreach ($res as $key => $valR) {
                $res[$key]['entry_into_gold'] = array_search($valR['department_id'], $rank) + 1;
                // 图用数据
                $charts_user[]                = array('name' => $valR['name'], 'y' => floor($valR['newUsers']));
                $charts_contractCnt[]         = array('name' => $valR['name'], 'y' => floor($valR['contractCnt']));
                $charts_contractMoneyCnt[]    = array('name' => $valR['name'], 'y' => floor($valR['contractMoneyCnt']), 'ext' => number_format($valR['contractMoneyCnt']));
            }
        }
        //var_dump($res);exit;
        // 图用数据
        $this->charts_user             = json_encode($charts_user);
        $this->charts_contractCnt      = json_encode($charts_contractCnt);
        $this->charts_contractMoneyCnt = json_encode($charts_contractMoneyCnt);

        //列表
        $this->res = $res;
        $this->display();
    }

    /**
     * 门店
     */
    public function Store()
    {
        $department_id = (int) $_GET['department_id'];
        $sql           = "SELECT  c.user_id,  c.name ,b.role_id FROM new5kcrm_position AS a
            inner JOIN new5kcrm_role AS b ON a.position_id = b.position_id
            inner JOIN new5kcrm_user AS c ON b.user_id = c.user_id
            AND c.user_id > 0 and a.department_id = {$department_id}";
        $userList      = M('roleDepartment')->query($sql);

        $startTime = strtotime($_GET['begin_time']);
        $endTime   = strtotime($_GET['end_time']);
        $res       = array();
        $tmpRank   = array();
        if ($userList) {
            foreach ($userList as $val) {
                $row['user_id']          = $val['user_id'];
                $row['name']             = $val['name'];
                //用户用户
                $row['newUsers']         = (int) current(current(M('roleDepartment')->query("SELECT count(*) FROM new5kcrm_customer "
                                                . "WHERE create_time >= {$startTime} AND create_time <={$endTime}   AND owner_role_id = {$val['user_id']}")));
                //             echo D()->getLastSql();
                //新增合同
                $row['contractCnt']      = M('contract')->where(" owner_role_id = {$val['role_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->count();
                // echo D()->getLastSql();
                //入金数-- 合同生效时间
                $row['contractMoneyCnt'] = (M('contract')->where(" owner_role_id = {$val['role_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(investment_money) as investment_money ") ) / 100;
                //入金数-- 合同生效时间
                $row['average_interest'] = (M('contract')->where(" owner_role_id = {$val['role_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(investment_rate) /count(investment_rate) as average_interest ") ) / 100;

                //平均年龄
                $row['average_age']   = '-';
                //平均周期
                $row['average_cycle'] = (M('contract')->where(" owner_role_id = {$val['role_id']} and create_time >= {$startTime}  and create_time <= {$endTime} ")->getField(" sum(closure_period) /count(closure_period) as average_interest ") ) ;;

                //保有量
                $row['retain_quantity']   = '-';
                //入金排名
                $row['entry_into_gold']   = '-';
                $res[]                    = $row;
                //处理排行榜原始数据
                $tmpRank[$val['user_id']] = $row['contractMoneyCnt'];
            }
            //处理排行榜
            arsort($tmpRank);

            $rank = array_keys($tmpRank);
            foreach ($res as $key => $valR) {
                $res[$key]['entry_into_gold'] = array_search($valR['user_id'], $rank) + 1;
                // 图用数据
                $charts_user[]                = array('name' => $valR['name'], 'y' => floor($valR['newUsers']));
                $charts_contractCnt[]         = array('name' => $valR['name'], 'y' => floor($valR['contractCnt']));
                $charts_contractMoneyCnt[]    = array('name' => $valR['name'], 'y' => floor($valR['contractMoneyCnt']), 'ext' => number_format($valR['contractMoneyCnt']));
            }
        }
        // 图用数据
        $this->charts_user             = json_encode($charts_user);
        $this->charts_contractCnt      = json_encode($charts_contractCnt);
        $this->charts_contractMoneyCnt = json_encode($charts_contractMoneyCnt);
        //列表
        $this->res                     = $res;
        $this->display();
    }

}
