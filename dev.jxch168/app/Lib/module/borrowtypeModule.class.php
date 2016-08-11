<?php
// +----------------------------------------------------------------------
// ｜jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------
require APP_ROOT_PATH.'app/Lib/page.php';
class borrowtypeModule extends SiteBaseModule
{
	public function index()
	{
		$GLOBALS['tmpl']->display("page/borrowtype.html");              
	}		
}
?>