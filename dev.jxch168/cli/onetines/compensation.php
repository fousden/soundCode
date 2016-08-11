<?php

/*
 * 功能：逾期补偿发放红包
 *
 */
require_once '../init.php';
$sql_str = "SELECT
	dlr.user_id,
		dlr.self_money * d.rate / 100 / 360 * (
			dlr.true_repay_date - d.jiexi_time
		) AS jxmoney
FROM
	fanwe_deal_load_repay AS dlr
LEFT JOIN fanwe_deal AS d ON (
	dlr.deal_id = d.id
	AND d.jiexi_time >= '2016-02-09'
                  AND d.jiexi_time < '2016-02-22'
)
WHERE
	dlr.true_repay_date != '0000-00-00'
AND d.jiexi_time < dlr.true_repay_date
";
$list = $GLOBALS['db']->getAll($sql_str);
if ($list) {
    foreach ($list as $val) {
        //初始化红包数据
        $user_bonus['deal_id'] = 0;
        $user_bonus['deal_load_id'] = 0;
        $user_bonus['user_id'] = $val['user_id'];
        $user_bonus['reward_name'] = getBonusTypeName(10);
        $user_bonus['money'] = num_format($val['jxmoney']);
        $user_bonus['status'] = 1; //默认已提交红包提现申请
        $user_bonus['cash_type'] = 1; //自动提现
        $user_bonus['cash_period'] = 1; //提现周期为3天
        $user_bonus['bonus_type'] = 10; //10代表逾期补偿
        $user_bonus['generation_time'] = time(); //红包生成时间
        $user_bonus['apply_time'] = time(); //默认红包提现申请时间
        $user_bonus['release_time'] = strtotime("+1days", $user_bonus['apply_time']); //预计发放时间
        $user_bonus['release_date'] = strtotime(date('Y-m-d', $user_bonus['release_time'])); //预计发放日期
        $user_bonus['remark'] = getBonusTypeName(10);
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $user_bonus, "INSERT"); //插入红包数据
        $user_bonus_id = $GLOBALS['db']->insert_id();
    }
}
include './send_msg_box.php';


