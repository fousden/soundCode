<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of downloadModule
 *
 * @author lujun
 */
class downloadModule extends SiteBaseModule
{

    public function index()
    {
        $sql = "select val from ".DB_PREFIX."m_config where code = 'android_filename'";
        $android_filename = $GLOBALS['db']->getOne($sql);
        $GLOBALS['tmpl']->assign("url",$android_filename);
        $GLOBALS['tmpl']->display("download.html");
    }

}
