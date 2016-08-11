<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//定义跟目录
define('ROOT', str_replace("\\", "/", dirname(dirname(__FILE__))).'/');
//定义文件上传的路径
define('UPLOAD_PATH', ROOT."public/uploads/");
// 定义项目路径
define('APP_PATH', ROOT."app/");
//公共资源路径
define('PUBLIC_PATH', ROOT."public/");

// 开启调试模式
define('APP_DEBUG', true);



// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
