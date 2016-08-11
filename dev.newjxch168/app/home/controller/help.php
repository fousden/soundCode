<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台 关于我们help控制器
 *
 * @author jxch
 */

class Help extends frontend{
    //首页
    function index(){
//        $helpModel = D("Help");
//        $article_cate_tree = $helpModel->cateTree();
        $article_cate_tree = M('Article')->where(array("cate_id"=>2,"is_effect"=>1,"is_delete"=>0))->field("id,title,is_effect")->select();
        $lastest_news_tree = M('Article_cate')->where(array("type_id"=>0,"is_effect"=>1,"is_delete"=>0,"pid"=>0))->field("id,title")->order("sort desc")->select();
        $this->assign("article_cate_tree",$article_cate_tree);
        $this->assign("lastest_news_tree",$lastest_news_tree);
        
        $type = $_GET['type'];
        $id = $_GET['id'];
        if($type == 1){
            $article_content = M('Article')->where(array("id"=>$id))->find();
            $this->assign("article_content",$article_content);
        }else{
            $article_list = M('Article')->where(array("cate_id"=>$id))->select();
            $this->assign("article_list",$article_list);
        }
        return $this->fetch();
    }
    
    function calculator(){
        return $this->fetch();
    }
    
}

