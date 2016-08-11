<?php

/*
 * 功能：实时运行预警检测系统 存在异常则报警
 * 时间：2015年12月14日
 * author：chushangming
 */

//php /网站根目录/cli/detectWarningRunning.php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'init.php';
require_once 'DetectWarningSystem.php';

//运行脚本 传递所需执行函数名 【短信预警函数：smsWarning】【途虎兑换码预警函数：tuActiveWarning】【过期标的预警函数：expireDeal】【异常订单预警函数：abnormalOrders】
//脚本执行命令示例 ： php /cli/detectWarningRunning.php smsWarning
//如果不传递函数名 则跑所有脚本

run($argv[1]);

function run($act = '') {
    $detectWarningSystem = new DetectWarningSystem();
    $reflectionClass = new ReflectionClass("DetectWarningSystem");
    $detectWarningMethods = $reflectionClass->getMethods();
    if ($act) {
	$detectWarningSystem->$act();
    } else {
	if ($detectWarningMethods) {
	    foreach ($detectWarningMethods as $val) {
		//获取方法的注释
		//$doc = $val->getDocComment();
		$funName = $val->name;
		$detectWarningSystem->$funName();
	    }
	}
    }
}