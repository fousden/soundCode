<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

require APP_ROOT_PATH.'app/Lib/page.php';
class acateModule extends SiteBaseModule
{
	public function index()
	{

		$GLOBALS['tmpl']->caching = true;
		$cache_id  = md5(MODULE_NAME.ACTION_NAME.trim($_REQUEST['id']).intval($_REQUEST['p']));
		if (!$GLOBALS['tmpl']->is_cached('page/acate_index.html', $cache_id))
		{
			$id = intval($_REQUEST['id']);
			$cate_item = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate where id = ".$id." and is_effect = 1 and is_delete = 0");

			if($id>0&&!$cate_item)
			{
				app_redirect(APP_ROOT."/");
			}
			elseif($cate_item['type_id']!=0)
			{
				if($cate_item['type_id']==1)
				app_redirect(url("index","help#index"));
				if($cate_item['type_id']==2)
				app_redirect(url("index","notice#list"));
				if($cate_item['type_id']==3)
				app_redirect(url("index","sys#alist"));
			}

			$cate_id = intval($cate_item['id']);
			$cate_tree = get_acate_tree();

			$GLOBALS['tmpl']->assign("acate_tree",$cate_tree);
			$cate = null;
			foreach($cate_tree as $k=>$v){
				if($id == $v['id']){
					$cate = $v;
				}
			}

			$GLOBALS['tmpl']->assign("cate",$cate);

			//分页
			$page = intval($_REQUEST['p']);
			if($page==0)
			$page = 1;
			$limit = (($page-1)*app_conf("PAGE_SIZE")).",".app_conf("PAGE_SIZE");
			$result = get_article_list($limit,$cate_id,'ac.type_id = 0','');

			$GLOBALS['tmpl']->assign("list",$result['list']);
			$page = new Page($result['count'],app_conf("PAGE_SIZE"));   //初始化分页对象
			$p  =  $page->show();
			$GLOBALS['tmpl']->assign('pages',$p);


			//开始输出当前的site_nav
			$cates = array();
			$cate = $cate_item;
//                                                echo '<pre>';
//                        var_dump($cate);
//                        echo '</pre>';die;
			do
			{
				$cates[] = $cate;
				$pid = intval($cate['pid']);
				$cate = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."article_cate where is_effect =1 and is_delete =0 and id = ".$pid);

			}while($pid!=0);

			foreach($cates as $cate_row)
			{
				$page_title .= $cate_row['title']." - ";
				$page_kd .= $cate_row['title'].",";
			}
			$page_title = substr($page_title,0,-3);
			krsort($cates);

			$site_nav[] = array('name'=>$GLOBALS['lang']['HOME_PAGE'],'url'=>APP_ROOT."/");
			if($cate_item)
			{
				foreach($cates as $cate_row)
				{
					$site_nav[] = array('name'=>$cate_row['title'],'url'=>url("index","acate#index",array("id"=>$cate_row['id'])));
				}
			}
			else
			{
				$site_nav[] = array('name'=>$GLOBALS['lang']['ARTICLE_CATE'],'url'=>url("index","acate#index"));
			}
			$GLOBALS['tmpl']->assign("site_nav",$site_nav);
			//输出当前的site_nav
                        $ctl = trim($_REQUEST['ctl']);
                        $id = trim($_REQUEST['id']);
       if ($ctl == 'acate'&&$id=='23') {
                $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_TITLE'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_KEYWORD'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_DESCRIPTION'];
                $page_title = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_TITLE'];
                $page_keyword = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_KEYWORD'];
                $page_description = $GLOBALS['lang']['OFFICIAL_ANNOUNCE_DESCRIPTION'];
                $GLOBALS['tmpl']->assign("page_title", $page_title);
                $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
                $GLOBALS['tmpl']->assign("page_description", $page_description);
            }
                    elseif ($ctl == 'acate'&&$id=='22') {
                $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['LATEST_ACTIVITY_TITLE'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['LATEST_ACTIVITY_KEYWORD'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['LATEST_ACTIVITY_DESCRIPTION'];
                $page_title = $GLOBALS['lang']['LATEST_ACTIVITY_TITLE'];
                $page_keyword = $GLOBALS['lang']['LATEST_ACTIVITY_KEYWORD'];
                $page_description = $GLOBALS['lang']['LATEST_ACTIVITY_DESCRIPTION'];
                $GLOBALS['tmpl']->assign("page_title", $page_title);
                $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
                $GLOBALS['tmpl']->assign("page_description", $page_description);
            }
                    elseif ($ctl == 'acate'&&$id=='21') {
                $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['FINANCING_NEWS_TITLE'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['FINANCING_NEWS_KEYWORD'];
                $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['FINANCING_NEWS_DESCRIPTION'];
                $page_title = $GLOBALS['lang']['FINANCING_NEWS_TITLE'];
                $page_keyword = $GLOBALS['lang']['FINANCING_NEWS_KEYWORD'];
                $page_description = $GLOBALS['lang']['FINANCING_NEWS_DESCRIPTION'];
                $GLOBALS['tmpl']->assign("page_title", $page_title);
                $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
                $GLOBALS['tmpl']->assign("page_description", $page_description);
            }else{
                			$GLOBALS['tmpl']->assign("page_title",$result['list'][0]['seo_title']);
			$GLOBALS['tmpl']->assign("page_keyword",$result['list'][0]['seo_keyword']);
			$GLOBALS['tmpl']->assign("page_description",$result['list'][0]['seo_description']);
            }


		}



                $deal_help = get_help();
		$GLOBALS['tmpl']->assign("deal_help",$deal_help);
                $article = get_article($id);
		$GLOBALS['tmpl']->assign("article",$article);
		$GLOBALS['tmpl']->display("page/acate_index.html",$cache_id);
	}
}
?>