<?php

/**
 * 后台公用控制器
 *
 * @author jxch
 */

namespace base\controller;

use \base\controller\base;

class backend extends base {

    //初始化方法
    function _initialize() {
        //判断是否登录
        $this->is_login();
    }

    //是否登录
    function is_login() {
        $admin_info = session("admin_info");
        if (!$admin_info) {
            die($this->error("您尚未登录，请您登录后操作！", '', "/admin/admin/login"));
        }
    }

    public function index($map='') {
        //列表过滤器，生成查询Map对象
        $map = $this->_search('',$map);

        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $name = CONTROLLER_NAME;
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        return $this->fetch();
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param string $name 数据对象名称
      +----------------------------------------------------------
     * @return HashMap
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _search($name = '',$where='') {
        //生成查询条件
        if (empty($name)) {
            $name = CONTROLLER_NAME;
        }
        $name = CONTROLLER_NAME;
        $model = D($name);
        $map = array();
        foreach ($model->getDbFields() as $key => $val) {
            if (isset($_REQUEST [$val]) && $_REQUEST [$val] != '') {
                $map [$val] = $_REQUEST [$val];
            }
        }
        if($where){
            $map=array_merge($where,$map);
        }        
        return $map;
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _list($model, $map, $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            import("ORG.Page");
            $p = new \think\Page($count, $listRows);
            //分页查询数据

            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示

            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式

            $this->assign('list', $voList);
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
            $this->assign("nowPage", $p->nowPage);
        }
        return $voList;
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param string $sql_str Sql语句 不含排序字段的SQL语句
     * @param string $parameter 分页跳转的时候保证查询条件
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    function _Sql_list($model, $sql_str, $parameter = '', $sortBy = '', $asc = false) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = $sortBy;
        }

        if ($sortBy == 'nosort') {
            unset($order);
        }

        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
            $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }

        //取得满足条件的记录数
        $sql_tmp = 'select count(*) as tpcount from (' . $sql_str . ') as a';
        $rs = $model->query($sql_tmp, false);

        $count = intval($rs[0]['tpcount']);
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST['listRows'])) {
                $listRows = $_REQUEST['listRows'];
            } else {
                $listRows = '';
            }

            import("ORG.Page");
            $p = new \think\Page($count, $listRows);
            //分页跳转的时候保证查询条件
            if ((!empty($parameter)) && (substr($parameter, 1, 1) <> '&')) {
                //add by chenfq 2010-06-19 添加分页条件连接缺少 & 问题
                $parameter = '&' . $parameter;
            }
            $p->parameter = $parameter;

            //排序
            if (!empty($order))
                $sql_str .= ' ORDER BY ' . $order . ' ' . $sort;

            //分页查询数据
            $sql_str .= ' LIMIT ' . $p->firstRow . ',' . $p->listRows;

            $voList = $model->query($sql_str, false);
            //分页显示
            $page = $p->show();
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? L('SORT_ASC') : L('SORT_DESC'); //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式

            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign('list', $voList);
            $this->assign("page", $page);
        }
        return $voList;
    }

    //公共删除方法
    function publicDelete() {
        $model_name = $_REQUEST["model_name"] ? $_REQUEST["model_name"] : "";
        $table_name = $_REQUEST["table_name"] ? $_REQUEST["table_name"] : "";
        $ids = $_REQUEST["id"] ? $_REQUEST["id"] : '';
        $is_ajax = $_REQUEST["ajax"] ? $_REQUEST["ajax"] : 0;
        $model_name = D($model_name);
        $data = $model_name->publicDelete($table_name, $ids);
        if ($is_ajax) {
            die(json_encode($data));
        } else {
            return $this->success($data["info"]);
            die();
        }
    }

    //设置标的属性 只能修改 1 0两个值属性
    function set_deal_attr() {
        $model_name = $_REQUEST["model_name"] ? $_REQUEST["model_name"] : "";
        $table_name = $_REQUEST["table_name"] ? $_REQUEST["table_name"] : "";
        $borrowModel = D($model_name);
        $data = $borrowModel->set_deal_attr($table_name);
        die(json_encode($data));
    }

    //全局改变状态
    public function global_set_status() {
        $data = D(CONTROLLER_NAME)->set_deal_attr(CONTROLLER_NAME);
        die(json_encode($data));
    }

    //全局软删方法
    public function global_soft_delete() {
        $where['id'] = array("in", $_REQUEST['id']);
        $model = D(CONTROLLER_NAME);
//        $is_delete=$model->where($where)->getField("is_delete");
//        $data['is_delete']=$is_delete==1?0:1;
        $data['is_delete'] = 0;
        $model->where($where)->save($data);
        $root['response_code'] = 1;
        $root['show_err'] = "删除成功！";
        output($root);
    }
    
    //全局硬删（即彻底删除）
    public function global_delete() {
        $where['id'] = array("in", $_REQUEST['id']);
        $model = D(CONTROLLER_NAME);
        $model->where($where)->delete();
        $root['response_code'] = 1;
        $root['show_err'] = "删除成功！";
        output($root);
    }


    public function toogle_status()
    {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $field = $_REQUEST['field'];
        $info = $id."_".$field;
        $c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField($field);  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=".$id)->setField($field,$n_is_effect);
//        save_log($info.l("SET_EFFECT_".$n_is_effect),1);
        ajax_Return($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
    }

}
