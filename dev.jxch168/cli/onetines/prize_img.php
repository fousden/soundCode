<?php
require_once dirname(dirname(__FILE__)).'/init.php';
$prize_img_conf=  require APP_ROOT_PATH."data_conf/user_material_conf.php";
foreach ($prize_img_conf as $key=>$val){
    $sql_str="update ".DB_PREFIX."user_lottery_log set prize_img='$key' where prize_type='4' and prize_name like '%{$val['name']}%'";
    $GLOBALS['db']->query($sql_str);
}