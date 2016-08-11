<?php

namespace admin\controller;

/**
 * 广告页管理
 *
 * @author jxch
 */
class Adv extends \base\controller\backend {

    public function _initialize() {
        //判断是否登录
        $this->adv_model = D('adv');
    }

    public function index() {
        $where['is_delete'] = 0;
        parent::index($where);
        return $this->fetch();
    }

    public function add() {
        if ($_REQUEST['submit']) {
            $data = $this->adv_model->create();
            unset($data['submit']);
            unset($data['id']);
            $res=uploading_files($_FILES['icon']);
            if($res['upload_status']==1){
                $upload_data=$res['upload_data'][0];
                unset($res);
                $data['icon']=$upload_data['savepath'].$upload_data['savename'];
            }
            $data['start_time']=  strtotime($_REQUEST['start_time']);
            $data['end_time']=  strtotime($_REQUEST['end_time']);
            if ($this->adv_model->add($data)) {
                return $this->success("添加成功！", "", "/" . MODULE_NAME . "/" . CONTROLLER_NAME . "/index");
            } else {
                return $this->success("添加失败！", "", "/" . MODULE_NAME . "/" . CONTROLLER_NAME . "/add");
            }
        }
        return $this->fetch();
    }

    public function edit() {
        if ($_REQUEST['submit']) {
            $data = $this->adv_model->create();
            unset($data['submit']);
            $data['start_time']=  strtotime($_REQUEST['start_time']);
            $data['end_time']=  strtotime($_REQUEST['end_time']);
            $this->adv_model->save($data);
            return $this->success("编辑成功！", "", "/" . MODULE_NAME . "/" . CONTROLLER_NAME . "/index");
        }
        $adv_info = $this->adv_model->where(array("id" => $_GET['id']))->find();
        $this->assign('adv_info', $adv_info);
        return $this->fetch('add');
    }

}
