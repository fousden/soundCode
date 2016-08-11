<?php

namespace home\model;

use base\model\frontend;

/**
 * 前台user 公用业务逻辑类
 *
 * @author jxch
 */
class Help extends frontend {
    //表名
    protected $tableName = 'Article';
    
    //获取文章分类列表
    function cateTree(){
        $menu = M('ArticleCate')->where('is_delete = 0')->field('id,pid')->distinct('title')->select();
        foreach($menu as $key=>$val){
            if(empty($val['pid'])){
                $list[]['id']=$val['id'];
            }
            foreach($menu as $k=>$v){
                if($val['id']==$v['pid']){
                    $list[]['id']=$v['id'];
                }
            }
        }
        
        foreach($list as $key=>$val){
            $class[$key] = M('ArticleCate')->where('id = '.$val["id"])->field('id,title,pid')->find();
            if($class[$key]['pid'] == 0){
                $class[$key]['title'] = "|--".$class[$key]['title'];
            }
            if($class[$key]['pid'] != 0){
                $class[$key]['title'] = "&nbsp;&nbsp;&nbsp;&nbsp;|--".$class[$key]['title'];
            }
        }
        return $class;
    }
}

