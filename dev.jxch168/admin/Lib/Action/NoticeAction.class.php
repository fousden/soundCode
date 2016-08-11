<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/16
 * Time: 13:20
 */
class NoticeAction extends CommonAction{
    public function index(){
        if(trim($_REQUEST['title'])!='')
        {
            $condition['title'] = array('like','%'.trim($_REQUEST['title']).'%');
        }
        $condition['is_effect'] = 1;
        $condition['is_delete'] = 0;
        $this->assign("default_map",$condition);
        parent::index();
    }

    public function add(){
        $this->assign("new_sort", M("Notice")->where("is_delete=0 and is_effect=1")->max("sort")+1);
        $this->display();
    }

    public function edit(){
        $id = $_REQUEST['id'];
        $data = M("Notice")->where("id=".$id)->find();
        $data["begin_date"] = date("Y-m-d H:i:s",$data['begin_time']);
        $data["end_date"] = date("Y-m-d H:i:s",$data['end_time']);
        $this->assign("data",$data);
        $this->display();
    }

    // 新增公告
    public function insert(){

        B('FilterString');
        $data = M(MODULE_NAME)->create();
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        //开始验证有效性
        $this->assign("jumpUrl",u(MODULE_NAME."/add"));
        if(empty($data['title']))
        {
            $this->error("公告标题不能为空");
        }
        if(empty($data['brief']))
        {
            $this->error("公告内容不能为空");
        }
        if($data['begin_time']=='' || $data['end_time']=='')
        {
            $this->error("请设置有效时间");
        }

        if($data['end_time']<$data['begin_time']){
            $this->error("结束时间不能小于开始时间");
        }
        // 更新数据
        $log_info = $data['title'];
        $data['create_time'] = TIME_UTC;
        $data['update_time'] = TIME_UTC;
        $data['is_delete'] = 0;
        $list=M(MODULE_NAME)->add($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info.L("INSERT_SUCCESS"),1);
            clear_auto_cache("get_help_cache");
            $this->success(L("INSERT_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info.L("INSERT_FAILED"),0);
            $this->error(L("INSERT_FAILED"));
        }

    }

    // 编辑公告
    public function update(){
        B('FilterString');
        $id = $_REQUEST['id'];
        $data = M(MODULE_NAME)->create();
        $log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("title");
        //开始验证有效性
        $this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
        if(empty($data['brief']))
        {
            $this->error("公告内容不能为空");
        }
        if($data['begin_time']=='' || $data['end_time']=='')
        {
            $this->error("请设置有效时间");
        }
        if($data['end_time']<$data['begin_time']){
            $this->error("结束时间不能小于开始时间");
        }
        // 更新数据
        $data['update_time'] = TIME_UTC;
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $list=M(MODULE_NAME)->where("id=".$id)->save($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info.L("UPDATE_SUCCESS"),1);
            clear_auto_cache("get_help_cache");
            $this->success(L("UPDATE_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info.L("UPDATE_FAILED"),0);
            $this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
        }
    }

    public function delete(){
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset ( $id )) {
            $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach($rel_data as $data)
            {
                $info[] = $data['title'];
            }
            if($info) $info = implode(",",$info);
            $list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 1 );
            if ($list!==false) {
                save_log($info.l("DELETE_SUCCESS"),1);
                clear_auto_cache("get_help_cache");
                $this->success (l("DELETE_SUCCESS"),$ajax);
            } else {
                save_log($info.l("DELETE_FAILED"),0);
                $this->error (l("DELETE_FAILED"),$ajax);
            }
        } else {
            $this->error (l("INVALID_OPERATION"),$ajax);
        }
    }

    public function set_sort()
    {
        $id = intval($_REQUEST['id']);
        $sort = intval($_REQUEST['sort']);
        $log_info = M(MODULE_NAME)->where("id=".$id)->getField("title");
        if(!check_sort($sort))
        {
            $this->error(l("SORT_FAILED"),1);
        }
        M("Article")->where("id=".$id)->setField("sort",$sort);
        save_log($log_info.l("SORT_SUCCESS"),1);
        clear_auto_cache("get_help_cache");
        $this->success(l("SORT_SUCCESS"),1);
    }

    public function set_effect()
    {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = M(MODULE_NAME)->where("id=".$id)->getField("title");
        $c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);
        save_log($info.l("SET_EFFECT_".$n_is_effect),1);
        clear_auto_cache("get_help_cache");
        $this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
    }
}