<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/SiteBaseModule.class.php';
require APP_ROOT_PATH.'app/Lib/app_init.php';
define("CTL",'ctl');
define("ACT",'act');

class SiteApp{
	private $module_obj;
	//网站项目构造
	public function __construct(){
		if($GLOBALS['pay_req'][CTL])
			$_REQUEST[CTL] = $GLOBALS['pay_req'][CTL];
		if($GLOBALS['pay_req'][ACT])
			$_REQUEST[ACT] = $GLOBALS['pay_req'][ACT];

		$module = strtolower($_REQUEST[CTL]?$_REQUEST[CTL]:"index");
		$action = strtolower($_REQUEST[ACT]?$_REQUEST[ACT]:"index");
		$module = filter_ma_request($module);
		$action = filter_ma_request($action);

		if(!file_exists(APP_ROOT_PATH."app/Lib/module/".$module."Module.class.php"))
		$module = "index";

		require_once APP_ROOT_PATH."app/Lib/module/".$module."Module.class.php";
		if(!class_exists($module."Module"))
		{
			$module = "index";
			require_once APP_ROOT_PATH."app/Lib/module/".$module."Module.class.php";
		}
		if(!method_exists($module."Module",$action))
		$action = "index";

		if(!defined("MODULE_NAME"))
			define("MODULE_NAME",$module);
		define("ACTION_NAME",$action);

		$module_name = $module."Module";
		$this->module_obj = new $module_name;
		$this->module_obj->$action();
	}

	public function __destruct()
	{
		unset($this);
	}
}
?>