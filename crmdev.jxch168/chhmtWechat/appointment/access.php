<?php
include_once "comm/WechatHleper.php";
$wxHelper = new WechartHleper();
$currentUrl = 'http://'.$_SERVER['SERVER_NAME'].str_replace('access.php','index.php',$_SERVER["REQUEST_URI"]); 
$wxHelper->getUserAccessCode(urlencode($currentUrl));
