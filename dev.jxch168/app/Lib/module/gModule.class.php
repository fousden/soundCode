<?php

class gModule extends SiteBaseModule
{

    function index()
    {
        echo "cg： ";

        echo floatval($GLOBALS['db']->getOne("SELECT	sum(a.money) FROM 	" . DB_PREFIX . "payment_notice a LEFT JOIN " . DB_PREFIX . "payment b ON a.payment_id = b.id WHERE 	a.is_paid = 1 and b.class_name != 'Otherpay' and pay_date = '" . date('Y-m-d') . "'"));

        echo "<BR> ";
        echo "jy： ";
        echo floatval($GLOBALS['db']->getOne("SELECT sum(money) as all_bid_money FROM " . DB_PREFIX . "deal_load where is_auto = 0 and contract_no != '' and create_date = '" . date('Y-m-d') . "' "));
        echo "<BR> ";
        echo "smqld： ";
        //泉龙达短信条数预警
        $ress = send_sms_email($msg_item, 3, "EN");
        if ($ress['status'] == 1) {
            $result['number'] = $ress['return'];
        } else {
            $result['number'] = 0;
        }
        echo $result['number'];
        echo "<BR> ";
        echo "smyxt ";
        //一信通短信条数预警
        require_once APP_ROOT_PATH . "system/sms/YY_sms.php";
        $yy_sms = new YY_sms();
        $yy_arr = $yy_sms->get_count_msg();
        echo $yy_arr['number'];
        echo "<BR> ";
    }

}
