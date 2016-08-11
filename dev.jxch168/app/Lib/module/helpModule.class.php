<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/page.php';

class helpModule extends SiteBaseModule {

    public function index() {
	$GLOBALS['tmpl']->caching = true;
	$cache_id = md5(MODULE_NAME . ACTION_NAME . trim($_REQUEST['id']) . $GLOBALS['deal_city']['id']);
	if (!$GLOBALS['tmpl']->is_cached('page/help_index.html', $cache_id)) {
	    $id = intval($_REQUEST['id']);

	    $uname = addslashes(trim($_REQUEST['id']));

	    if ($id == 0 && $uname == '') {
		$id = $GLOBALS['db']->getOne("select a.id from " . DB_PREFIX . "article as a left join " . DB_PREFIX . "article_cate as ac on a.cate_id = ac.id where ac.type_id = 1 order by a.sort desc");
	    } elseif ($id == 0 && $uname != '') {
		$id = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "article where uname = '" . $uname . "'");
	    }
	    $article = get_article($id);

	    if (!$article || $article['type_id'] != 1) {
		app_redirect(APP_ROOT . "/");
	    } else {
		if (check_ipop_limit(CLIENT_IP, "article", 60, $article['id'])) {
		    //每一分钟访问更新一次点击数
		    $GLOBALS['db']->query("update " . DB_PREFIX . "article set click_count = click_count + 1 where id =" . $article['id']);
		}

		if ($article['rel_url'] != '') {
		    if (!preg_match("/http:\/\//i", $article['rel_url'])) {
			if (substr($article['rel_url'], 0, 2) == 'u:') {
			    app_redirect(parse_url_tag($article['rel_url']));
			} else
			    app_redirect(APP_ROOT . "/" . $article['rel_url']);
		    } else
			app_redirect($article['rel_url']);
		}
	    }
	    $article = get_article($id);
	    $GLOBALS['tmpl']->assign("article", $article);
	    $seo_title = $article['seo_title'] != '' ? $article['seo_title'] : $article['title'];
	    $GLOBALS['tmpl']->assign("page_title", $seo_title);
	    $seo_keyword = $article['seo_keyword'] != '' ? $article['seo_keyword'] : $article['title'];
	    $GLOBALS['tmpl']->assign("page_keyword", $seo_keyword . ",");
	    $seo_description = $article['seo_description'] != '' ? $article['seo_description'] : $article['title'];
	    $GLOBALS['tmpl']->assign("page_description", $seo_description . ",");
	    $GLOBALS['tmpl']->assign("relate_help", $cate_list);
	}
//var_dump($seo_keyword);die;
	//title,keywords,description
	$ctl = $_REQUEST['ctl'];
	$id = $_REQUEST['id'];

	if ($ctl == 'help' && $id == '1') {
//                $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['ABOUT_US_TITLE'];
//                $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['ABOUT_US_KEYWORD'];
//                $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['ABOUT_US_DESCRIPTION'];
	    $page_title = $GLOBALS['lang']['ABOUT_US_TITLE'];
	    $page_keyword = $GLOBALS['lang']['ABOUT_US_KEYWORD'];
	    $page_description = $GLOBALS['lang']['ABOUT_US_DESCRIPTION'];
	    $GLOBALS['tmpl']->assign("page_title", $page_title);
	    $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
	    $GLOBALS['tmpl']->assign("page_description", $page_description);
//var_dump($page_keyword);die;
	}

	if ($ctl == 'help' && $id == '80') {
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['PARTNER_TITLE'];
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['PARTNER_KEYWORD'];
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['PARTNER_DESCRIPTION'];
	    $page_title = $GLOBALS['lang']['PARTNER_TITLE'];
	    $page_keyword = $GLOBALS['lang']['PARTNER_KEYWORD'];
	    $page_description = $GLOBALS['lang']['PARTNER_DESCRIPTION'];
	    $GLOBALS['tmpl']->assign("page_title", $page_title);
	    $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
	    $GLOBALS['tmpl']->assign("page_description", $page_description);
	}
	if ($ctl == 'help' && $id == '74') {
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['CONTACT_US_TITLE'];
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['CONTACT_US_KEYWORD'];
	    $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['CONTACT_US_DESCRIPTION'];
	    $page_title = $GLOBALS['lang']['CONTACT_US_TITLE'];
	    $page_keyword = $GLOBALS['lang']['CONTACT_US_KEYWORD'];
	    $page_description = $GLOBALS['lang']['CONTACT_US_DESCRIPTION'];
	    $GLOBALS['tmpl']->assign("page_title", $page_title);
	    $GLOBALS['tmpl']->assign("page_keyword", $page_keyword);
	    $GLOBALS['tmpl']->assign("page_description", $page_description);
	}


	$cate_tree = get_acate_tree();
//        $GLOBALS['tmpl']->assign("page_title", $page_title);
	$GLOBALS['tmpl']->assign("acate_tree", $cate_tree);

	$GLOBALS['tmpl']->display("page/help_index.html", $cache_id);
    }

    public function earnings_calculator() {
	$GLOBALS['tmpl']->display("inc/earnings_calculator.html");
    }

}

?>