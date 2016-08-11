<?php 
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------\
define("FILE_PATH",""); //文件目录，空为根目录
require_once './system/common.php';
es_session::start();
require APP_ROOT_PATH."system/utils/es_image.php";
$verify = isset($_REQUEST['vname']) ? !empty($_REQUEST['vname']) ? strim($_REQUEST['vname']) : 'verify' : 'verify';
$w = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 50;
$h = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : 22;
es_image::buildImageVerify(4,1,'gif',$w,$h,$verify);
?>