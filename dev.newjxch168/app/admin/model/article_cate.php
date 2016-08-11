<?php

/**
 * 后台文章分类模块 model业务逻辑类
 */
namespace admin\model;
use base\model\backend;

class ArticleCate extends backend{
    //表名
    protected $tableName = "article_cate";
    
    //获取文章分类列表
    function getArticleCateList($is_delete){
        //未删除的
        $condition["is_delete"] = $is_delete;
        if(@$_REQUEST["title"]){
            $condition["title"] = trim($_REQUEST["title"]);
        }
        $article_cate_list = $this->_list($this,$condition);
        foreach($article_cate_list['data_list'] as $key=>$val){
            switch ($val['type_id'])
            {
            case 0:
              $article_cate_list['data_list'][$key]['type'] = "普通文章";
              continue;
            case 1:
              $article_cate_list['data_list'][$key]['type'] = "帮助文章";
              continue;
            case 2:
              $article_cate_list['data_list'][$key]['type'] = "公告文章";
              continue;
            case 3:
              $article_cate_list['data_list'][$key]['type'] = "系统文章";
              continue;
            }
        }
       return $article_cate_list;
    }
    
    //获取文章分类列表
    function cateTree(){
        $menu = $this->where('is_delete = 0')->field('id,pid')->distinct('title')->select();
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
    
    //增加文章分类
    function addArticleCate(){
        $list = $this->create();
        $data['title']        = trim($list['title']);
        $data['brief']        = trim($list['brief']);
        $data['pid']          = intval($list['pid']);
        $data['sort']         = intval($list['sort']);
        $data['is_effect']    = intval($list['is_effect']);
        $data['type_id']      = intval($list['type_id']);
        $data['icon']         = "0";
        $data['is_delete']    = 0; 
        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }
    }
    
    //获取单个文章分类信息
    function getArticleCateInfo($id){
        return $this->find($id);
    }
    
    //更新文章分类信息
    function editArticleCate(){
        $list = $this->create();
        $data['id']           = $list['id'];
        $data['title']        = trim($list['title']);
        $data['brief']        = trim($list['brief']);
        $data['pid']          = intval($list['pid']);
        $data['sort']         = intval($list['sort']);
        $data['is_effect']    = intval($list['is_effect']);
        $data['type_id']      = intval($list['type_id']);
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