<?php

/**
 * 基础公用控制器
 *
 * @author jxch
 */

namespace base\controller;

use \think\Controller;
use \think\Verify;
//use \base\model\collectprocess;

class base extends Controller {

    //生成验证码
    public function verify() {
        $config = array(
            'fontSize' => 14, // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'fontttf' => '5.ttf', // 关闭验证码杂点
            'useCurve' => false,
        );
        $verify = new Verify($config);
        $verify->entry();
    }
    /**
     * 收集数据
     * 暂为SQL收集
     */
    private  function collectprocess()
    {
       $obj = D('collectprocess','jxch_debug');
       $obj->runCollect();
    }


    public function __destruct()
    {
        //收集数据
        $this->collectprocess();
    }
    
    
}
