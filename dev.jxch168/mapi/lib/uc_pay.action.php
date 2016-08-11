<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class uc_pay{
	public function index(){
		$payment_notice = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment_notice where id = " . intval($_REQUEST['id']));

		if ($payment_notice) {
			if ($payment_notice['is_paid'] == 0) {
				$payment_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "payment where id = " . $payment_notice['payment_id']);

				if ($payment_info) {
					require_once APP_ROOT_PATH . "system/payment/" . $payment_info['class_name'] . "_payment.php";
					$payment_class  = $payment_info['class_name'] . "_payment";
					$payment_object = new $payment_class();
					$payment_code   = $payment_object->get_payment_code($payment_notice['id'],1);
				}

				$root['program_title'] =  $GLOBALS['lang']['PAY_NOW'];
				$root['notice_sn'] =  $GLOBALS['lang']['NOTICE_SN'];
				$root['my_orders'] =  $GLOBALS['lang']['MY_ORDERS'];
				$root['modify_payment_type'] =  $GLOBALS['lang']['MODIFY_PAYMENT_TYPE'];

				$root['payment_code'] =  $payment_code;
				$root['payment_notice'] = $payment_notice;

				if (intval($_REQUEST['check']) == 1) {
					$root['err'] = $GLOBALS['lang']['PAYMENT_NOT_PAID_RENOTICE'];
				}
			} else {
				$root['err'] = '支付成功';
			}
		} else {
			$root['err'] = $GLOBALS['lang']['NOTICE_SN_NOT_EXIST'];
		}
		output($root);
	}
}
?>
