<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class MobileAnomalyLogAction extends CommonAction {

    public function index() {
        //列表数据
        $start_time = isset($_REQUEST['begin_time']) ? strtotime($_REQUEST['begin_time']) : 0;
        $end_time = isset($_REQUEST['end_time']) ? strtotime($_REQUEST['end_time']) : 0;
        $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $udid = isset($_REQUEST['udid']) ? strtoupper(trim($_REQUEST['udid'])) : '';
        $app_version = isset($_REQUEST['app_version']) ? strtoupper(trim($_REQUEST['app_version'])) : '';
        $sql_str = "SELECT * FROM `fanwe_mobile_anomaly_log` where 1=1 ";
        if ($start_time || $end_time) {
            $sql_str.=" and create_time>" . $start_time . " and create_time<" . $end_time . " ";
        }
        if ($mobile) {
            $sql_str.=" and mobile=" . $mobile . " ";
        }
        if ($udid) {
            $sql_str.=" and udid=" . $udid . " ";
        }
        if($app_version){
            $sql_str.=" and app_version='$app_version' ";
        }
        $list = $this->_Sql_list(D(), $sql_str, '', 'id', false);
        //app版本号的数据列表
        $app_version_list = M('MobileAnomalyLog')->field('app_version')->where(['app_version'=>['neq','']])->group('app_version')->select();
        $this->assign('app_version_list',$app_version_list);
        $this->display();
    }

    public function show_info($id) {
        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        if ($id) {
            $info = M('mobile_anomaly_log')->where(array('id' => $id))->getfield("content");
        } else {
            $info = "无错误详情";
        }
        echo '<pre>' . $info . '</pre>';
        die;
    }

}

?>