<?php

return array(
//    'SHOW_PAGE_TRACE'=>true,
    'APP_GROUP_LIST'	    =>"Admin,Mapi",
    'DEFAULT_GROUP'         => 'Admin',  // 默认分组
    'DEFAULT_MODULE'        => 'Index', // 默认模块名称
    'DEFAULT_ACTION'        => 'index', // 默认操作名称
    //'DEFAULT_THEME'    =>    'default',
    'APP_AUTOLOAD_PATH' => '@.ORG', //自动加载项目类库,@Pinlib,@.Pintag
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
    // 'TAGLIB_PRE_LOAD' => 'html' ,
    // 'APP_AUTOLOAD_PATH'         =>  '@.TagLib',
    // 'html'=> '@.TagLib.TagLibHtml' ,
);
?>