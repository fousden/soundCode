<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//php /home/lujun/lujun.jxch168.com/cli/statistical.php 2015-02-03~2015-06-07
//php /home/lujun/lujun.jxch168.com/cli/statistical.php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once 'init.php';
require_once 'statisticalRun.php';

IF (!in_array(CONDITION, array('prepublication', 'produce'))) {
    if (isset($_GET['dg'])) {
	$argc = 3;
	$argv[1] = $_GET['date'];
	$argv[2] = isset($_GET['act']) ? $_GET['act'] : '';
    }
}
if ($argc > 1) {
    $dateTmp = explode('~', $argv[1]);
    $runDate = $dateTmp[0];
    do {
	$act = isset($argv[2]) ? $argv[2] : '';
	run($runDate, $act);
	$runDate = date('Y-m-d', strtotime($runDate . ' + ' . 1 . ' day'));
    } while (strtotime($dateTmp[1]) >= strtotime($runDate));
} else {
    $date = date('Y-m-d', strtotime(' -1 day'));
    run($date, $argv[2]);
}

function run($date, $act = '') {
    $obj = new statisticalRun();
    $reflection = new ReflectionClass("statisticalRun");
    $getMethods = $reflection->getMethods();
    if ($act) {
	$obj->$act($date);
    } else {
	if ($getMethods) {
	    foreach ($getMethods as $val) {
		//获取方法的注释
		$doc = $val->getDocComment();
		$funName = $val->name;
		//echo $funName;
		//echo $doc;
		$obj->$funName($date);
	    }
	}
    }
}

//print_r($argc); //CLI下获取参数的数目，最小值为1
//print_r($argv);