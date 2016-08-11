<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class recModule extends SiteBaseModule
{
	public function rhot()
	{	
		$GLOBALS['tmpl']->assign("rtype","rhot");
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['HOT_LIST']);	
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('rec_list.html', $cache_id))
		{	
			//输出商城分类
			$cate_tree = get_cate_tree();
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);	
			
		}
		$GLOBALS['tmpl']->display("rec_list.html",$cache_id);
	}
	public function rnew()
	{		
		$GLOBALS['tmpl']->assign("rtype","rnew");	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['NEW_LIST']);
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('rec_list.html', $cache_id))
		{	
			//输出商城分类
			$cate_tree = get_cate_tree();
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);	
			

		}
		$GLOBALS['tmpl']->display("rec_list.html",$cache_id);
	}
	public function rbest()
	{			
		$GLOBALS['tmpl']->assign("rtype","rbest");	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['BEST_LIST']);
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('rec_list.html', $cache_id))
		{	
			//输出商城分类
			$cate_tree = get_cate_tree();
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);	
			

		}
		$GLOBALS['tmpl']->display("rec_list.html",$cache_id);
	}
	public function rsale()
	{			
		$GLOBALS['tmpl']->assign("rtype","rsale");	
		$GLOBALS['tmpl']->assign("page_title",$GLOBALS['lang']['SALE_LIST']);
		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.$GLOBALS['deal_city']['id']);		
		if (!$GLOBALS['tmpl']->is_cached('rec_list.html', $cache_id))
		{	
			//输出商城分类
			$cate_tree = get_cate_tree();
			$GLOBALS['tmpl']->assign("cate_tree",$cate_tree);
			
		}
		$GLOBALS['tmpl']->display("rec_list.html",$cache_id);
	}
}
?>