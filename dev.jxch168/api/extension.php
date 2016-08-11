<?php
/**
 * Created by PhpStorm.
 * User: ningchengzeng
 * Date: 15/7/20
 * Time: 下午2:47
 */

require dirname(dirname(__FILE__)).'/system/common.php';

$app = $_REQUEST["app"];
$udid = $_REQUEST["udid"];
$drkey = $_REQUEST["drkey"];
$source = $_REQUEST["source"];

$returnFormat = $_REQUEST["returnFormat"];
$pburl = $_REQUEST["pburl"];


$mobile_count = $GLOBALS['db']->getOne("select count(1) ".DB_PREFIX."mobile_extension where app='".$app."' and udid='".$udid."'");
if($mobile_count == 0) {
    $data['app'] = $app;

    $data['pburl'] = $pburl;
    $data['source'] = $source;
    $data['create_time'] = time();

    $data['year']=date("Y");
    $data['month']=date("m");
    $data['day']=date("d");
    $data['week']=date("W");

    $data['state'] = 0;

    if($source == 'dianru'){
        $data['udid'] = $drkey;
    }
    else if($source == 'limei'){
        $data['udid'] = $udid;
    }
    
    $GLOBALS['db']->autoExecute(DB_PREFIX."mobile_extension",$data);
}

if($source == 'limei'){
    header("Location: " .$pburl);
    exit();
}
?>