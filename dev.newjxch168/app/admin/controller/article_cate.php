<?php
namespace admin\controller;
use \base\controller\backend;

/**
 * 文章分类控制器
 *
 * @author jxch
 */

class ArticleCate extends backend{
    public function index() {
        $articleCateModel = D("ArticleCate");
        $article_cate_list = $articleCateModel->getArticleCateList(0);
        $this->assign("page", $article_cate_list['page']);
        $this->assign("nowPage", $article_cate_list["nowPage"]);
        $this->assign("list",$article_cate_list['data_list']);
        return $this->fetch();
    }
    
    //改变状态
    public function set_effect(){
        $id = intval($_REQUEST['id']);
	$ajax = intval($_REQUEST['ajax']);
        $info = M('ArticleCate')->where("id=".$id)->getField("title");
        $c_is_effect = M('ArticleCate')->where("id=".$id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M('ArticleCate')->where("id=".$id)->setField("is_effect",$n_is_effect);
        die(json_encode(array("status"=>$ajax,"info"=>l("SET_EFFECT_".$n_is_effect),"data"=>$n_is_effect)));	
    }
    
    //增加分类
    public function add(){
        $articleCateModel = D('ArticleCate');
        if(IS_POST){
            //插入文章分类
            if($articleCateModel->addArticleCate()){
                return $this->success("添加文章分类成功");
            }else{
                return $this->error("添加文章分类失败");
            }
        }else{
            //展示数据
            $class = $articleCateModel->cateTree();
            $this->assign("cate_tree",$class);
            return $this->fetch();
        }
    }
   
    //编辑分类
    public function edit(){
        $articleCateModel = D('ArticleCate');
        if(IS_POST){
            //编辑文章分类
            if($articleCateModel->editArticleCate()){
                return $this->success("添加文章分类成功");
            }else{
                return $this->error("添加文章分类失败");
            }
        }else{
            //展示数据
            $class = $articleCateModel->cateTree();
            $this->assign("cate_tree",$class);
            $content = $articleCateModel->getArticleCateInfo();
            $this->assign("vo",$content);
            return $this->fetch();
        }
    }
    
    //删除分类
    public function delete(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_GET['id'];
        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
        $result = M('ArticleCate')->where($condition)->setField('is_delete','1');
        if($result > 0){
            die(json_encode(array("status"=>$ajax,"info"=>"删除成功")));

        }else{
            die(json_encode(array("status"=>0,"info"=>"删除失败")));
        }
    }
    
    //恢复分类
    public function restore(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_GET['id'];
        $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
        $result = M('ArticleCate')->where($condition)->setField('is_delete','0');
        if($result > 0){
            die(json_encode(array("status"=>$ajax,"info"=>"恢复成功")));
        }else{
            die(json_encode(array("status"=>0,"info"=>"恢复失败")));
        }
    }
    
    //分类列表回收站
    public function trash() {
        $articleCateModel = D("ArticleCate");
        $article_cate_list = $articleCateModel->getArticleCateList(1);
        $this->assign("page", $article_cate_list['page']);
        $this->assign("nowPage", $article_cate_list["nowPage"]);
        $this->assign("list",$article_cate_list['data_list']);
        return $this->fetch();
    }
}
