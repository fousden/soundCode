<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/23
 * Time: 9:46
 */
class UserAddressAction extends CommonAction{
    public function index(){
        // 客户收货地址管理

            $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
            $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
            $user_mobile = isset($_REQUEST['user_mobile']) ? trim($_REQUEST['user_mobile']) : '';
            $real_name = isset($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
            $model = D('user_address');
            $user_ids = array();
            // 查询条件
            if($user_name){
                $user_id_arr = M("user")->field('id')->where("user_name like '%".$user_name."%'")->select();
                foreach($user_id_arr as $val){
                    $user_ids[] = $val["id"];
                }
                $user_id = implode(",",$user_ids);
                $map['user_id'] = array("in",$user_id);
            }

            if($mobile) {
                $map['mobile'] = array("eq",$mobile);
            }


            if($user_mobile){
                $user_id_arr = M("user")->field('id')->where("mobile = ".$user_mobile)->select();
                foreach($user_id_arr as $val){
                    $user_ids[] = $val["id"];
                }
                $user_id = implode(",",$user_ids);
                $map['user_id'] = array("in",$user_id);
            }

            if($real_name){
                $map['real_name'] = array("like","%".$real_name."%");
            }
            $this->_list($model,$map);
            $this->display();

        }

    public function edit(){
        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';
        $address_info = M("user_address")->where("id=".$id)->find();
        $user_name = M("user")->field("user_name")->where("id=".$address_info['user_id'])->find();
        $address_info['user_name'] = $user_name['user_name'];
        $this->assign("list",$address_info);
        $this->display();
    }

    public function update(){
        $info = M("user_address")->create();
        $id = $_REQUEST['id'];
        foreach($info as $val){
            if($val==''){
                $this->error("表单不能为空");
                exit;
            }
        }
        $res = M("user_address")->where("id=".$id)->save($info);
        if($res || $res===0){
            $this->success("修改成功");
        }else{
            $this->error("修改失败");
        }
    }

    public function delete(){
        $ajax = intval($_REQUEST['ajax']);
        $id = trim($_REQUEST['id']);
        $res = M("user_address")->where("id=".$id)->delete();
        if($res){
            $this->success("删除成功",$ajax);
        }else{
            $this->error("删除失败",$ajax);
        }
    }
}