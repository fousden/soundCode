<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'multi_module'          =>  true, // 是否允许多模块 如果为false 则必须设置
    'deny_module_list'      =>  [''],// 禁止访问的模块列表
    'allow_module_list'     =>  ["home","admin","mapi","wap","base"],    // 允许访问的模块列表
    'default_module'     => 'home', //默认模块
    'default_controller'    =>  'Index', // 默认控制器名称
    'default_action'        =>  'index', // 默认操作名称
    'default_lang'          =>  'zh-cn', // 默认语言
    'parse_str'=>[
                    '__PUBLIC__' => "/style",
                    '__ADMIN__' => "/style/admin",
                    '__HOME__' => "/style/home",
                    '__WAP__' => "/style/wap",
                    '__ROOT__' => '/',
                 ],
    /*'url_route_on' => false,
    'log'          => [
        'type'             => 'socket',
        'host'             => '111.202.76.133',
        //日志强制记录到配置的client_id
        'force_client_id'  => '',
        //限制允许读取日志的client_id
        'allow_client_ids' => [],
    ],*/
//    'url_route_on' => true,
//    'url_route_must'=>  true,
    //调试模式
//    'response_exit'=>false,
//    'log' => ['type'=>'trace','trace_file'=> THINK_PATH.'tpl/page_trace.tpl'],
];
