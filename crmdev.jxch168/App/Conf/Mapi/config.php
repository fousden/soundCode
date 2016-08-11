<?php

return array(
    //'SHOW_PAGE_TRACE'=>true,
    'URL_MODEL' => 0,
    'URL_CASE_INSENSITIVE' => true,
    'TMPL_ACTION_ERROR' => 'Public:message',
    'TMPL_ACTION_SUCCESS' => 'Public:message',
    'TMPL_EXCEPTION_FILE' => './App/Tpl/Admin/Public/exception.html',
    'DEFAULT_TIMEZONE' => 'PRC',
    'LOAD_EXT_CONFIG' => 'db,version',
    'LOG_RECORD' => true,
    'LOG_LEVEL' => 'EMERG',
    'OUTPUT_ENCODE' => false,
    'LANG_SWITCH_ON' => true,
    'LANG_AUTO_DETECT' => true,
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'LANG_LIST' => 'en-us,zh-cn',
    'VAR_LANGUAGE' => '1',
    'COOKIE_PATH' => __ROOT__,
    'SESSION_OPTIONS' => array('cookie_path' => __ROOT__),
    'APP_URL' => 'http://crm.yunyou.in',
    'TOKEN_ON' => false,  // 是否开启令牌验证
    'DEFAULT_MODULE'        => 'Index', // 默认模块名称
    'DEFAULT_ACTION'        => 'Index', // 默认操作名称
    'SESSION_AUTO_START'    => false,//默认不开启session
    'SESSION_SAVE_PATH'     =>"/tmp/mapi/",//mapi保存session的路径
    'SESSION_GC_MAXLIFETIME'     =>"2592000",//mapi保存session的有效期（30天）
);
?>