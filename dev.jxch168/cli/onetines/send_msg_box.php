<?php

/*
 * 添加站内信
 * 
 */
$sql_str = "SELECT
	u.user_name,
	ub.user_id,
	sum(ub.money) as money,
	count(ub.id) as c
FROM
	fanwe_user_bonus AS ub
LEFT JOIN fanwe_user AS u ON (ub.user_id = u.id)
WHERE
	bonus_type = 10
GROUP BY
	user_id";
$msg_list = $GLOBALS['db']->getAll($sql_str);
if ($msg_list) {
    foreach ($msg_list as $val) {
        //$msg_data['title']="恭喜您中奖了";
        $msg_data['content'] = $val['user_name'] . "您好，对于过年期间投标造成的逾期情况，我们从2015年的盈利中提取85万用于在补偿过年期间延期支付的利息补贴。所以发放给您的现金红包补贴共为" . $val['money'] . "元，三日内发放至账户上";
        $msg_data['to_user_id'] = $val['user_id'];
        $msg_data['is_notice'] = 1;
        $msg_data['create_time'] = time();
        $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_box", $msg_data, "INSERT");
    }
}


