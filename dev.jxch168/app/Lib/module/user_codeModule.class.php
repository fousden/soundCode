<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class user_codeModule extends SiteBaseModule
{

    public function register_code()
    {
        $test_code_list = $GLOBALS['db']->getAll("SELECT * FROM " . DB_PREFIX . "mobile_verify_code ORDER BY create_time DESC LIMIT 20");//var_dump($test_code_list);exit;

        $GLOBALS['tmpl']->assign('test_code_list', $test_code_list);
        $GLOBALS['tmpl']->display("register_code.html");
    }

}

?>