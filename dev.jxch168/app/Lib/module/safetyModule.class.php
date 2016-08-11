<?php

class safetyModule  extends SiteBaseModule {
//    function index() {
//        $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['GOLD_SAFE_TITLE'];
//        $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['GOLD_SAFE_KEYWORD'];
//        $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['GOLD_SAFE_DESCRIPTION'];
//        $page_title = $GLOBALS['lang']['GOLD_SAFE_TITLE'];
//        $GLOBALS['tmpl']->assign("page_title",$page_title);
//        $GLOBALS['tmpl']->display("page/safety-assurance.html");
//    }

    function safe(){
        $GLOBALS['tmpl']->_var['site_info']['SHOP_TITLE'] = $GLOBALS['lang']['GOLD_SAFE_TITLE'];
        $GLOBALS['tmpl']->_var['site_info']['SHOP_KEYWORD'] = $GLOBALS['lang']['GOLD_SAFE_KEYWORD'];
        $GLOBALS['tmpl']->_var['site_info']['SHOP_DESCRIPTION'] = $GLOBALS['lang']['GOLD_SAFE_DESCRIPTION'];
        $page_title = $GLOBALS['lang']['GOLD_SAFE_TITLE'];
        $GLOBALS['tmpl']->assign("page_title",$page_title);
        $GLOBALS['tmpl']->display("page/safe.html");
    }
}