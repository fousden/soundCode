<?php
set_time_limit(0);
require_once 'init.php';
$last_id = 0;
$error_count = 0;
$succeed_count = 0;
$error_str = '';
$succeed_str = '';
while (1){
$sql_str = "SELECT id,mobile FROM " . DB_PREFIX . "user where id>$last_id and idno!='' order by id asc limit 1";
$user_list = $GLOBALS['db']->getAll($sql_str);
foreach ($user_list as $key => $val) {
    $result = MO("ActivityConf")->ActivityConfByType(1, $val['id']);
    if ($result['status'] == 0) {
	$error_str.=$result['user_id'] . ",";
	$error_count++;
    } else if ($result['status'] == 1) {
	$succeed_str.=$result['user_id'] . ",";
	$succeed_count++;
	$msg = "尊敬的{$val['mobile']}您账户中有一张{$result['name']}券还有{$result['date']}天即将到期，请尽快使用。回复TD拒收！";
	$msg_data['dest'] = $val['mobile'];
	$msg_data['send_type'] = 0;
	$msg_data['title'] = "现金红包短信通知";
	$msg_data['content'] = addslashes($msg);
	$msg_data['send_time'] = 0;
	$msg_data['is_send'] = 0;
	$msg_data['create_time'] = get_gmtime();
	$msg_data['user_id'] = $val['id'];
	$msg_data['is_html'] = 0;
	$GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT");
	sleep(1);
    }
    $last_id = $val['id'];
}
    if (count($user_list) < 1) {
        echo "操作成功".$succeed_count."个用户,用户分别为".$succeed_str."<br>";
        echo "操作失败".$error_count."个用户,用户分别为".$error_str."<br>";
        exit;
    }
}