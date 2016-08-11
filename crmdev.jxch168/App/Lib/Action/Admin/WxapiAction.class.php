<?php

/**
 * CRM API
 *
 * @author lujun
 */
class WxapiAction extends Action
{

    public function _initialize()
    {
//        $action = array(
//            'permission' => array(),
//            'allow'      => array('getSubDepartment'),
//        );
        // B('Authenticate', $action);
    }

    /**
     * 门店列表
     */
    public function getSubDepartment()
    {
        $department_list = M('roleDepartment')->where('parent_id = %d', 0)->select();
        array_shift($department_list);
        array_shift($department_list);
        array_shift($department_list);

        $this->output($department_list);
    }

    /**
     * 预约提交
     */
    public function reserve()
    {

        $m_customer      = D('Customer');
        $m_customer_data = D('CustomerData');
        $row             = $m_customer->where('mobile = %d', $_GET['mobile'])->find();
        if ($row) {
            $rowData = $m_customer_data->where('customer_id = %d', $row['customer_id'])->find();
            $row     = array_merge($row, $rowData);
            $this->output(array('state' => -1, 'data' => $row));
        }
        $m_customer->create_time     = time();
        $m_customer->update_time     = time();
        $m_customer->owner_role_id   = 0;
        $m_customer->name            = $_GET['name'];
        $m_customer->reserve_shop    = $_GET['reserve_shop'];
        $m_customer->mobile          = $_GET['mobile'];
        $m_customer->reserve_product = $_GET['reserve_product'];

        $customer_id = $m_customer->add();
        if ($customer_id) {
            $m_customer_data->customer_id  = $customer_id;
            $m_customer_data->reserve_time = $_GET['reserve_time'];
            //   $m_customer_data->openid = $_GET['openid'];
            $m_customer_data->add();
        }
        $row     = $m_customer->where('mobile = %d', $_GET['mobile'])->find();
        $rowData = $m_customer_data->where('customer_id = %d', $row['customer_id'])->find();
        $row     = array_merge($row, $rowData);
        $this->output(array('state' => 1, 'data' => $row));
    }

    /**
     * 产品列表
     */
    public function Productlist()
    {
        $m_customer       = D('Customer');
        $productList      = D('ProductView')->select();
        $m_product_images = M('productImages');
        foreach ($productList as $k => $v) {
            $productList[$k]['path']        = $m_product_images->where('product_id = %d and is_main = 1', $v['product_id'])->getField('path');
            $productList[$k]['reserve_num'] = (INT) $m_customer->where('reserve_product = "%s"', $v['name'])->count();
        }
        $this->output($productList);
    }

    /**
     * 输出反回
     * @param type $data
     */
    private function output($data)
    {
        die(json_encode($data));
    }

}
