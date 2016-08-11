<?php

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : "choosewebview";
$path_info_conf = array(
    'deals' => 'mapi/deal/index',//投资列表
    'register' => 'mapi/user/register',//注册
    'deal_collect' => 'mapi/deal/deal_collect',//投资详情页
    'home' => 'mapi/index/index',//首页
    'choosewebview' => 'mapi/init/choosewebview',//入口判断原生APP还是webView;
    'anomaly_log' => 'mapi/log/anomaly_log',//入口判断原生APP还是webView;
    'version' => 'mapi/init/version',//入口判断原生APP还是webView;
);
//下面是调试
//$con=new think\db\driver\Mysql($path_info_conf);
$con=new PDO("mysql:dbname=jxch168.com;host=10.10.10.56;charset=utf8", "master", "lovejxch168",array());
$content="[".date('Y-m-d H:i:s')."]--".$act;
//$con->query("delete from jxch_test");
$sql_str="insert into jxch_test (content) values ('$content')";
$con->query($sql_str);
//调试代码结束

if ($path_info_conf[$act]) {
    $_SERVER['PATH_INFO'] = $path_info_conf[$act];
}
unset($path_info_conf);
require_once '../index.php';