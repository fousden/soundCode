<?php

/*
 * 功能，定时执行投保任务
 * 时间：2015年10月14日
 *
 */
require_once 'init.php';
// 获取用户表中status字段为1的用户
$sql = 'select * from ' . DB_PREFIX . 'user where status = 1 ';
$user_list = $GLOBALS['db']->getAll($sql);
foreach ($user_list as $k => $v) {
    $uid = $v['id'];
    $name = $v['real_name'];
    $idno = $v['idno'];
    $sex = substr($idno, -2, 1) % 2 ? 1 : 0;
    $byear = substr($idno, 6, 4);
    $bmonth = substr($idno, 10, 2);
    $bday = substr($idno, 12, 2);
    $birth = $byear . '-' . $bmonth . '-' . $bday;
    $mobile = $v['mobile'];
    $info = MO("insure")->pingan($uid, $name, $sex, $birth, $mobile);
    if ($info) {
        if ($info['info'] == 0) {
            $data['status'] = 2;
        } else {
            $data['status'] = 3;
        }
        $mode = "UPDATE";
        $condition = "id={$uid}";
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $data, $mode, $condition);
    }
};

