<?php

$act = isset($_REQUEST['ctl']) ? trim($_REQUEST['ctl']) : "index";
$path_info_conf = array(
    'index' => 'wap/index/test',
    'home' => 'wap/index/home',
);
if ($path_info_conf [$act]) {
    $_SERVER['PATH_INFO'] = $path_info_conf[$act];
}
unset($path_info_conf);
require_once '../index.php';
