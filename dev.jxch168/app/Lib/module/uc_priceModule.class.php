<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/uc.php';

class uc_priceModule extends SiteBaseModule{
    public function index(){
        $user_id = $GLOBALS['user_info']['id'];
        // 判断是否是点击了修改地址
        $tip = $_REQUEST['tip'];
        if($tip=="change"){
            $address_id = $_REQUEST['address_id'];
            $sql = "select * from ".DB_PREFIX."user_address where id = ".$address_id;
            $change_info = $GLOBALS['db']->getRow($sql);
//            echo "<pre>";
//            print_r($change_info);exit;
            $change_info['tip'] = "change";
            $province_info = $GLOBALS['db']->getRow("select DistrictCode from ".DB_PREFIX."district_info where DistrictName='".$change_info['province']."'");
            $change_info['province_code'] = $province_info['DistrictCode'];
            $city_info = $GLOBALS['db']->getRow("select DistrictCode from ".DB_PREFIX."district_info where DistrictName='".$change_info['city']."'");
            $change_info['city_code'] = $city_info['DistrictCode'];
            $GLOBALS['tmpl']->assign("change_info", $change_info);
        }
        // 判断是否有地址
        $sql = "select * from ".DB_PREFIX."user_address where user_id = ".$user_id;
        $address_info = $GLOBALS['db']->getRow($sql);
//        echo "<pre>";
//        print_r($address_info);exit;
        $GLOBALS['tmpl']->assign("address_info", $address_info);
        // 联动的地址
        $sql = "select * from ".DB_PREFIX."district_info where parentcode=0";
        $region_lv1 = $GLOBALS['db']->getALL($sql);
        $GLOBALS['tmpl']->assign("region_lv1", $region_lv1);
        // 查出此用户手机
        $sql = "select mobile from ".DB_PREFIX."user where id = ".$user_id;
        $mobile = $GLOBALS['db']->getOne($sql);
        // 从lottery_log表中查出此手机号码的获奖记录分页
        $status = isset($_REQUEST['status'])? trim($_REQUEST['status']) : 0;// 默认为待发放
        $sql = "select count(*) as count from ".DB_PREFIX."user_lottery_log where prize_type=4 and mobile=".$mobile." and status=".$status;
        $count = $GLOBALS['db']->getOne($sql);
        $page_size = 10;
        $page = new Page($count,$page_size);   //初始化分页对象
        $p  =  $page->show();
        $GLOBALS['tmpl']->assign('pages',$p);
        $GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['UC_COLLECT']);
        $page = intval($_REQUEST['p']);
        if($page==0)
            $page = 1;
        $limit = (($page-1)*$page_size).",".$page_size;
        // 查询的时候判断是否是选择了待发放或者已发放
        $GLOBALS['tmpl']->assign("status", $status);
        $order = $_REQUEST['type']==0 ? 'desc' : 'asc';
        $sql = "select * from ".DB_PREFIX."user_lottery_log where prize_type=4 and status=".$status." and mobile=".$mobile." order by create_time ".$order." limit ".$limit;
        $lottery_info = $GLOBALS['db']->getALL($sql);
        foreach($lottery_info as $key => $val){
            $lottery_info[$key]['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
        }
        $GLOBALS['tmpl']->assign("lottery_info", $lottery_info);
        $GLOBALS['tmpl']->assign("page_title", "我的奖品");
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_price.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    public function get_city(){
        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        $sql = "select * from ".DB_PREFIX."district_info where parentcode=".$id;
        $city_list = $GLOBALS['db']->getALL($sql);
        ajax_return($city_list);
    }

    public function save_user_info(){
        $user_id = $GLOBALS['user_info']['id'];
        $province_code = isset($_REQUEST['province'])? trim($_REQUEST['province']) : '';
        $city_code = isset($_REQUEST['city'])? trim($_REQUEST['city']) : '';
        $address = isset($_REQUEST['address'])? trim($_REQUEST['address']) : '';
        $real_name = isset($_REQUEST['real_name'])? trim($_REQUEST['real_name']) : '';
        $mobile = isset($_REQUEST['mobile'])? trim($_REQUEST['mobile']) : '';
        $code = isset($_REQUEST['code'])? trim($_REQUEST['code']) : '';
        // 判断mobile是否为11位数字
        if(!check_mobile($mobile)){
            $root['status'] = 0;
            $root['info'] = "请填写11位手机号";
            ajax_return($root);
        };
//        if(!check_zip_code($code)){
//            $root['status'] = -2;
//            $root['info'] = "请填写6位邮政编码";
//            ajax_return($root);
//        }
        if(empty($address)){
            $root['status'] = 0;
            $root['info'] = "地址不能为空";
            ajax_return($root);
        }
        if(empty($real_name)){
            $root['status'] = 0;
            $root['info'] = "收件人姓名不能为空";
            ajax_return($root);
        }
        // 判断是否有多个地址
        $sql = "select * from ".DB_PREFIX."user_address where user_id=".$user_id;
        $info = $GLOBALS['db']->getRow($sql);
        if($info){
            $root['status']= 0;
            $root['info'] = "请不要重复操作";
            ajax_return($root);
        }
        $province_info = $GLOBALS["db"]->getRow("select DistrictName from ".DB_PREFIX."district_info where DistrictCode=".$province_code);
        $city_info = $GLOBALS["db"]->getRow("select DistrictName from ".DB_PREFIX."district_info where DistrictCode=".$city_code);
        $province = $province_info['DistrictName']; // 获取省的名字
        $city = $city_info['DistrictName']; // 获取市的名字
        $user_address['user_id'] = $user_id;
        $user_address['real_name'] = $real_name;
        $user_address['mobile'] = $mobile;
        $user_address['province'] = $province;
        $user_address['city'] = $city;
        $user_address['address'] = $address;
        $user_address['code'] = $code;
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_address", $user_address, 'INSERT', '', 'SILENT');
        if($res){
            $root['status'] = 1;
            $root['info'] = "添加地址成功";
        }
        ajax_return($root);
    }

    public function update_user_info(){
        $user_id = $GLOBALS['user_info']['id'];
        $province_code = isset($_REQUEST['province'])? trim($_REQUEST['province']) : '';
        $city_code = isset($_REQUEST['city'])? trim($_REQUEST['city']) : '';
        $address = isset($_REQUEST['address'])? trim($_REQUEST['address']) : '';
        $address_id = isset($_REQUEST['address_id'])? trim($_REQUEST['address_id']) : '';
        $real_name = isset($_REQUEST['real_name'])? trim($_REQUEST['real_name']) : '';
        $mobile = isset($_REQUEST['mobile'])? trim($_REQUEST['mobile']) : '';
        $code = isset($_REQUEST['code'])? trim($_REQUEST['code']) : '';
        // 判断mobile是否为11位数字
        if(!check_mobile($mobile)){
            $root['status'] = 0;
            $root['info'] = "请填写11位手机号";
            ajax_return($root);
        };
//        if(!check_zip_code($code)){
//            $root['status'] = -2;
//            $root['info'] = "请填写6位邮政编码";
//            ajax_return($root);
//        }
        if(empty($address)){
            $root['status'] = 0;
            $root['info'] = "地址不能为空";
            ajax_return($root);
        }
        if(empty($real_name)){
            $root['status'] = 0;
            $root['info'] = "收件人姓名不能为空";
            ajax_return($root);
        }
        // 获取省
        $province_info = $GLOBALS["db"]->getRow("select DistrictName from ".DB_PREFIX."district_info where DistrictCode=".$province_code);
        $city_info = $GLOBALS["db"]->getRow("select DistrictName from ".DB_PREFIX."district_info where DistrictCode=".$city_code);
        $province = $province_info['DistrictName']; // 获取省的名字
        $city = $city_info['DistrictName']; // 获取市的名字
        $user_address['user_id'] = $user_id;
        $user_address['real_name'] = $real_name;
        $user_address['mobile'] = $mobile;
        $user_address['province'] = $province;
        $user_address['city'] = $city;
        $user_address['address'] = $address;
        $user_address['code'] = $code;
        $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_address", $user_address, 'UPDATE', 'id='.$address_id, 'SILENT');
        if($res){
            $root['status'] = 1;
            $root['info'] = "修改地址成功";
        }else{
            $root['status'] = 0;
            $root['info'] = "修改地址失败";
        }
        ajax_return($root);
    }

    public function del_address(){
        $address_id = isset($_REQUEST['address_id']) ? trim($_REQUEST['address_id']) : '' ;
        // 删除user_receive_price_info中的记录
        $sql = "delete from ".DB_PREFIX."user_address where id=".$address_id;
        $GLOBALS['db']->query($sql);
        if($GLOBALS['db']->affected_rows()){
            $root['status'] = 1;
            $root['info'] = "删除成功";
        }else{
            $root['status'] = 0;
            $root['info'] = "删除失败";
        }
        ajax_return($root);
    }

}