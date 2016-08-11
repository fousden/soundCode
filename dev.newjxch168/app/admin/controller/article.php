<?php
namespace admin\controller;
use \base\controller\backend;

/**
 * 文章控制器
 *
 * @author jxch
 */

class Article extends backend{
    
    //所有的文章列表
    public function index(){
        $articleModel = D("Article");
        $article_list = $articleModel->getArticleList(0);
        $this->assign("page", $article_list['page']);
        $this->assign("nowPage", $article_list["nowPage"]);
        $this->assign("list",$article_list['data_list']);
        return $this->fetch();
    }
    
    //改变状态
    public function set_effect(){
        $id = intval($_REQUEST['id']);
	$ajax = intval($_REQUEST['ajax']);
        $info = M('Article')->where("id=".$id)->getField("title");
        $c_is_effect = M('Article')->where("id=".$id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M('Article')->where("id=".$id)->setField("is_effect",$n_is_effect);
        die(json_encode(array("status"=>$ajax,"info"=>l("SET_EFFECT_".$n_is_effect),"data"=>$n_is_effect)));	
    }
    
    //增加文章
    public function add() {
        $articleModel = D("Article");
        if(IS_POST){
            //插入文章
            if($articleModel->addArticle()){
              return $this->success("添加文章成功");
              die;
            }else{
                return $this->error("添加文章失败");
                die;
            }
        }else{
            //展示新增界面
            $class = $articleModel->cateTree();
            $this->assign("cate_tree",$class);
            return $this->fetch();
        }        
    }
    
    //编辑文章
    public function edit($id) {
        $articleModel = D("Article");
        if(IS_POST){
            //更新文章
            if($articleModel->editArticle()){
                return $this->success("文章更新成功");
                die;
            }else{
                return $this->error("文章更新失败");
                die;
            }
        }else{
            //展示文章
            $class = $articleModel->cateTree();
            $this->assign("cate_tree",$class);
            $content = $articleModel->getArticleInfo($id);
            $this->assign("vo",$content);
            return $this->fetch();
        }  
    }
    
    //删除文章
    public function delete(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_GET['id'];
        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
        $result = M('Article')->where($condition)->setField('is_delete','1');
        if($result > 0){
            die(json_encode(array("status"=>$ajax,"info"=>"删除成功")));

        }else{
            die(json_encode(array("status"=>0,"info"=>"删除失败")));
        }
    }
    
    //恢复文章
    public function restore(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_GET['id'];
        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
        $result = M('Article')->where($condition)->setField('is_delete','0');
        if($result > 0){
            die(json_encode(array("status"=>$ajax,"info"=>"恢复成功")));
        }else{
            die(json_encode(array("status"=>0,"info"=>"恢复失败")));
        }
    }
    
    //文章回收站
    public function trash() {
        $articleModel = D("Article");
        $article_list = $articleModel->getArticleList(1);
        $this->assign("page", $article_list['page']);
        $this->assign("nowPage", $article_list["nowPage"]);
        $this->assign("list",$article_list['data_list']);
        return $this->fetch();
    }
    
    //说明
    //删、恢复、彻底删除注意多id 的问题
}

