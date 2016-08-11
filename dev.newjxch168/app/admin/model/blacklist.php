<?php

/**
 * 黑名单 model业务逻辑类
 */
namespace admin\model;
use base\model\backend;

class BlackList extends backend{
    //表名
    protected $tableName = "blacklist";
    
    //黑名单列表
    /**
     * 
     * @param type $type  黑名单类型
     * @return type
     */
    function getBlackList($type){
        $condition['type'] = $type;
        $condition['is_delete'] = 0;
        if(@$_REQUEST["mobile"]){
            $condition["dest"] = trim($_POST["mobile"]); 
        }
        $black_list = $this->_list($this,$condition);
        return $black_list;
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
            $data_list = $model->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
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

