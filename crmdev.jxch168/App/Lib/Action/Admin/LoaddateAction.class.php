<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


ini_set('display_errors', 'On');
error_reporting(E_ALL);

class LoaddateAction extends Action
{

    public function _initialize()
    {

    }

    public function index()
    {
        $fileDate = file(dirname(__FILE__) . "/demo.txt");
        if ($fileDate) {
            foreach ($fileDate as $val) {
                $tmp        = explode("	", trim($val));
                $customerId = $this->getCustomerId($tmp);
                $this->createContract($customerId, $tmp);
                //  echo $val . "<BR>";
            }
        }
    }

    private function createContract($customerId, $tmp)
    {


        $contractInfo = D('contract')->where("number = '" . trim($tmp[2]) . "'")->select();
//        if ($contractInfo) {
//            echo "[]" . implode("|",$tmp)."<BR>";
//        }
        // 产品
        $productInfo  = D('Product')->where("name = '" . trim($tmp[9]) . "'")->select();
        if (empty($productInfo)) {
            echo "产品类型不存在【" . $tmp[9] . "】";
            exit;
        }

        $zhArr = array(
            '世界广场新'   => '世纪广场',
            '奉贤南桥'    => '南桥职场',
            '奉贤平安'    => '平安职场',
            '川沙'      => '川沙职场',
            '金钟李'     => '金钟职场',
            '圣爱大厦'    => '圣爱职场',
            "桐庐"      => '桐庐职场',
            '金钟'      => '金钟职场',
            '金钟朱'     => '金钟职场',
            '圣爱牛'     => '圣爱职场',
            '圣爱高'     => '圣爱职场',
            '川沙新'     => '川沙职场',
            '世界广场'    => '世纪广场',
            '奉贤新'     => '南桥职场',
            '圣爱新'     => '圣爱职场',
            '世界广场无门店' => '世纪广场',
        );
        if (!isset($zhArr[trim($tmp[0])])) {
            echo "职场不存在【" . $tmp[0] . "】";
            var_dump($tmp[0], $zhArr[$tmp[0]]);
            exit;
        }

        //门店
        $role_department = D('role_department')->where("name = '" . $zhArr[trim($tmp[0])] . "'")->select();
        $tmpTime         = explode('.', $tmp[7]);
        $incontractArr   = array(
            'number'                  => trim($tmp[2]),
            'due_time'                => strtotime(str_replace('.', '-', trim($tmp[7]))),
            'owner_role_id'           => 1,
            'creator_role_id'         => 1,
            'create_time'             => time(),
            'update_time'             => time(),
            'start_date'              => strtotime(str_replace('.', '-', trim($tmp[7]))),
            'end_date'                => strtotime(str_replace('.', '-', trim($tmp[8]))),
            'department_id'           => $role_department[0]['department_id'], // 部门ID
            'customer_id'             => $customerId,
            'receivables_bank'        => '',
            'receivables_bankzone'    => trim($tmp[4]),
            'receivables_bankcard'    => trim($tmp[5]),
            'receivables_name'        => trim($tmp[3]),
            'receivables_idno'        => trim($tmp[18]),
            'investment_money'        => $this->getE2($tmp[10]),
            'investment_rate'         => $this->getE2($tmp[11]),
            'month_investment_rate'   => $this->getE2($tmp[12]),
            'closure_period'          => trim($tmp[13]),
            'interest_days'           => trim($tmp[14]),
            'total_interest'          => $this->getE2($tmp[15]),
            'month_interest'          => $this->getE2($tmp[16]),
            'total_receivables_money' => $this->getE2($tmp[17]),
            'product_id'              => $productInfo[0]['product_id'], // 产品ID
            'status'                  => '已创建',
            'other_td'                => trim($tmp[0]),
            'year'                    => $tmpTime[0],
            'month'                   => $tmpTime[1],
            'day'                     => $tmpTime[2],
        );

        D('contract')->add($incontractArr);
    }

    private function getCustomerId($tmp)
    {

        $m_customer      = D('Customer');
        $m_customer_data = D('CustomerData');

        $row = $m_customer->where("idno = '" . $tmp[18] . "'")->find();

        if (empty($row)) {
            $inArr      = array(
                'user_id'         => 1,
                'owner_role_id'   => 1,
                'creator_role_id' => 1,
                'name'            => $tmp[1],
                'create_time'     => time(),
                'update_time'     => time(),
                'idno'            => $tmp[18],
                'bank'            => '',
                'bankzone'        => $tmp[4],
                'bankcard'        => $tmp[5],
            );
            $customerId = $m_customer->add($inArr);
            $inArrData  = array(
                'customer_id' => $customerId,
            );
            $m_customer_data->add($inArrData);
            return $customerId;
        }
        return $row['customer_id'];
    }

    private function getE2($str)
    {
        return (int) (trim($str) * 100);
    }

    public function ht2xs()
    {
        $fileDate = file(dirname(__FILE__) . "/ht2xs.txt");

        if ($fileDate) {
            foreach ($fileDate as $val) {
                $tmp = explode("	", trim($val));
                IF (trim($tmp[2]) == '桐庐职场') {
                    $contractInfo = D('contract')->where("number = '" . trim($tmp[0]) . "' and other_td ='桐庐' ")->select();
                } else {
                    $contractInfo = D('contract')->where("number = '" . trim($tmp[0]) . "' and other_td !='桐庐' ")->select();
                }
                $adminUserInfo = $this->createAdminUser($tmp);
                if (empty($contractInfo)) {

                    echo $val . '<BR>';
                } else {
                    M('contract')->where(array("contract_id" => $contractInfo[0]['contract_id']))->save(
                            array('other_xs'      => trim($tmp[1]),
                                'owner_role_id' => $adminUserInfo[0]['role_id'],
                                'creator_role_id' => $adminUserInfo[0]['role_id'],
                            )
                    );
                }
            }
        }
    }

    private function createAdminUser($tmpInfo)
    {

        $_POST                = array();
        $_POST['name']        = trim($tmpInfo[5]);
        $_POST['password']    = 123456;
        $_POST['category_id'] = 2;
        $_POST['submit']      = '添加';

        $role_department    = D('role_department');
        $roleDepartmentInfo = $role_department->where(array('name' => trim($tmpInfo[6])))->select();

        $m_user = D('User')->where(array('department_id' => $roleDepartmentInfo[0]['department_id'], 'name' => trim($tmpInfo[5])))->select();

        if ($m_user) {
            return $m_user;
        }
        $_POST['department_id'] = $roleDepartmentInfo[0]['department_id'];

        $position             = D('position')->where(array('department_id' => $_POST['department_id'], 'name' => '理财师'))->select();
        $_POST['position_id'] = $position[0]['position_id'];



        $m_user = D('User');
        $m_role = M('Role');
        $m_user->create();

        $m_user->status  = 1;
        //为用户设置默认导航（根据系统菜单设置中的位置）
        $m_navigation    = M('navigation');
        $navigation_list = $m_navigation->order('listorder asc')->select();
        $menu            = array();
        foreach ($navigation_list as $val) {
            if ($val['postion'] == 'top') {
                $menu['top'][] = $val['id'];
            } elseif ($val['postion'] == 'user') {
                $menu['user'][] = $val['id'];
            } else {
                $menu['more'][] = $val['id'];
            }
        }
        $navigation         = serialize($menu);
        $m_user->navigation = $navigation;

        if ($re_id              = $m_user->add()) {

            $data['position_id'] = $_POST['position_id'];
            $data['user_id']     = $re_id;
            if ($role_id             = $m_role->add($data)) {
                $m_user->where('user_id = %d', $re_id)->setField('role_id', $role_id);
            }
            $_POST['role_id'] = $role_id;
        }

        return array($_POST);
    }

}
