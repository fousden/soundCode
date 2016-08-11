<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/crowd_func.php';
class crowdModule extends SiteBaseModule
{
	
	public function show(){
		
		 
		$GLOBALS['tmpl']->display("page/crowd.html");
	}
 	
}
?>
