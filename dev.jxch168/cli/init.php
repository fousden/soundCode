<?php
/**
 *
 *功能：1、初始化加载一些共用的文件
 *      2、CLI范围限制，如果不是通过CLI访问，则终止执行
 * User: chushangming
 * Date: 2015年7月28日
 * Time: 下午03:08
 */

set_time_limit(0);
ini_set('display_errors', 'On');
error_reporting(E_ALL);
$type = strtolower(php_sapi_name());
/*
if(!$type || (isset($type) && $type != 'cli'))
{
    header('Content-Type:text/html;charset=utf-8');
    die('对不起，您没有访问权限！');
}
 *
 */
//引入公共文件

require dirname(dirname(__FILE__)).'/system/common.php';
require dirname(dirname(__FILE__)).'/system/libs/user.php';
require dirname(dirname(__FILE__)).'/app/Lib/app_init.php';
require dirname(dirname(__FILE__)).'/app/Lib/deal.php';
require dirname(dirname(__FILE__)).'/system/define.php';
//require dirname(dirname(__FILE__)).'/app/Lib/common.php';