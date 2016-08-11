<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/26
 * Time: 10:22
 */
class ChangeRefereeAction extends CommonAction{
    public function index(){
        $user_name = $_REQUEST['user_name'];
        $real_name = $_REQUEST['real_name'];
        $mobile = $_REQUEST['mobile'];
        if($user_name){
            $map["user_name"] = $user_name;
        }
        if($real_name){
            $map["real_name"] = $real_name;
        }
        if($mobile){
            $map["mobile"] = $mobile;
        }
        $model = D("user");
        $this->_list ( $model, $map);
        $this->display();
    }

    public function edit(){
        $user_id = $_REQUEST['id'];
        $pid = M("user")->where("id=".$user_id)->getField("pid"); // 获取推荐人id
        if($pid>0){
            $user_name = M("user")->where("id=".$pid)->getField("user_name");// 获取推荐人用户名
            $referer_memo = M("user")->where("id=".$user_id)->getField("referer_memo");// 获取推荐人备注
            $this->assign("user_name",$user_name);
            $this->assign("referer_memo",$referer_memo);
        }
        $this->assign("user_id",$user_id);
        $this->display();
    }

    public function update(){
        $user_name = isset($_REQUEST["name"])? trim($_REQUEST["name"]) : '';
        $id = isset($_REQUEST["id"])? trim($_REQUEST["id"]) : '';
        $referer_memo = isset($_REQUEST["referer_memo"])? trim($_REQUEST["referer_memo"]) : '';
        if($user_name==''){
            $user_id = 0;
        }else{
            $user_id = M("user")->where("id='".$user_name."' or user_name='".$user_name."' or mobile='".$user_name."'")->getField("id"); // 获取推荐人的id
            if(!$user_id){
                $this->error("推荐人信息有误!");
            }
        }
        $data['pid'] = $user_id;
        $data['referer_memo'] = $referer_memo;
        $res = M("user")->where("id=".$id)->save($data);
        if($res>0){
            $this->success("添加成功");
        }else{
            $this->error("更新失败");
        }
    }
}