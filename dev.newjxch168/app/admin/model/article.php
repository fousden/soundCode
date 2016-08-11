<?php

/**
 * 后台文章模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Article extends backend{
    //表名
    protected $tableName = 'article';

    //获取文章列表
    function getArticleList($is_delete){
        //未删除的
        $condition["is_delete"] = $is_delete;
        if(@$_REQUEST["title"]){
            $condition["title"] = trim($_REQUEST["title"]);
        }
       
        $condition["is_delete"] = $is_delete;
        $article_list = $this->_list($this,$condition);
        foreach($article_list['data_list'] as $key=>$val){
            $article_list['data_list'][$key]['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
            $article_list['data_list'][$key]['update_time'] = date("Y-m-d H:i:s",$val['update_time']);
            $article_list['data_list'][$key]['cate_id'] = M('ArticleCate')->where('id = '.$val['cate_id'])->getField('title');
        }
        return $article_list;
    }
    
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
    
    //添加文章
    function addArticle(){
        $list = $this->create();
        $admin_id = $_SESSION['think']['admin_info']['id'];
        $data['title']           = trim($list['title']);
        $data['sub_title']       = trim($list['sub_title']);
        $data['brief']           = trim($list['brief']);
        $data['rel_url']         = trim($list['rel_url']);
        $data['sort']            = intval($list['sort']);
        $data['is_effect']       = intval($list['is_effect']);
        $data['content']         = trim($list['content']);
        $data['seo_title']       = trim($list['seo_title']);
        $data['seo_keyword']     = trim($list['seo_keyword']);
        $data['seo_description'] = trim($list['seo_description']);
        $data['hits']            = intval($list['hits']);
        $data['add_admin_id']    = $admin_id;
        $data['create_time']     = time();
        $data['update_time']     = time();
        $data['cate_id']         = intval($list['pid']);
        $data['update_admin_id'] = $admin_id;
        $data['is_delete']       = 0;
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }
    }

    //获取单个文章信息
    function getArticleInfo($id){
        return $this->find($id);
    }
    
    //编辑文章
    function editArticle(){
        $list = $this->create();
        $admin_id = $_SESSION['think']['admin_info']['id'];
        $data['id']              = trim($list['id']);
        $data['title']           = trim($list['title']);
        $data['sub_title']       = trim($list['sub_title']);
        $data['brief']           = trim($list['brief']);
        $data['rel_url']         = trim($list['rel_url']);
        $data['sort']            = intval($list['sort']);
        $data['is_effect']       = intval($list['is_effect']);
        $data['content']         = trim($list['content']);
        $data['seo_title']       = trim($list['seo_title']);
        $data['seo_keyword']     = trim($list['seo_keyword']);
        $data['seo_description'] = trim($list['seo_description']);
        $data['hits']            = intval($list['hits']);
        $data['update_time']     = time();
        $data['cate_id']         = intval($list['pid']);
        $data['update_admin_id'] = $admin_id;
        if($id = $this->save($data)){
            return $id;
        }else{
            return false;
        }
    }
    
    //分页
    private function _list($model,$condition,$sortBy = '', $asc = false) {
        if (isset ( $_REQUEST['_order'] )) {
            $_order = $_REQUEST['_order'];
        } else {
            $_order = !empty($sortBy)?$sortBy:$model->getPk ();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST['_sort'])) {
            $_sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $_sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $data_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['data_list'] = $data_list;
        }
        return $return;
    }
}
