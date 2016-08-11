<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------


//用于处理 api同步登录的回调

require './system/common.php';
require './app/Lib/app_init.php';
$payment_notice_id = intval($_REQUEST['id']);
if(!$payment_notice_id){
	$data['status'] = 0;
	echo json_encode($data);
}
if(file_exists(APP_ROOT_PATH.'system/payment/Baofoo_payment.php'))
{
	require_once APP_ROOT_PATH.'system/payment/Baofoo_payment.php';
	
	$p_obj = new Baofoo_payment();
	$p_obj->orderquery($payment_notice_id);
}

?>