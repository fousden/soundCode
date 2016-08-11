<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

if (isset($_GET['dbg']) && $_GET['dbg'] = 'jxch168' . date('Ymd')) {
    define("APP_DEBUG", true);
    define("SHOW_DEBUG", true);
}

ob_start();
if (!defined('ROOT_PATH'))
    define('ROOT_PATH', str_replace('index.php', '', str_replace('\\', '/', __FILE__)));

require ROOT_PATH . 'system/common.php';

if ($_REQUEST['is_pc'] == 1)
    es_cookie::set("is_pc", "1", 24 * 3600 * 30);


if (intval($GLOBALS['pay_req']['is_sj']) == 1) {
    $_REQUEST['is_sj'] = 1;
}

//echo es_cookie::get("is_pc");
if($_GET['cid']==4) {header("Location: /"); exit;}

if (isMobile() && !isset($_REQUEST['is_pc']) && file_exists(APP_ROOT_PATH . "wap/index.php") && !isset($_REQUEST['is_sj'])) {
    app_redirect("./wap/index.php");
} else {
    require ROOT_PATH . 'app/Lib/SiteApp.class.php';
    //实例化一个网站应用实例
    $AppWeb = new SiteApp();
}

if (isset($_GET['dbg']) && $_GET['dbg'] = 'jxch168' . date('Ymd')) {
    echo run_info();
    echo "<pre>";
    var_dump(get_included_files());
    echo "</pre>";
}
?>
