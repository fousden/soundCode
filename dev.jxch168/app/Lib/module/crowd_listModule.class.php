<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/crowd_func.php';
class crowd_listModule extends SiteBaseModule
{
	
	public function index(){
		
		 
		$GLOBALS['tmpl']->display("page/crowd_list.html");
	}
 	
}
?>
