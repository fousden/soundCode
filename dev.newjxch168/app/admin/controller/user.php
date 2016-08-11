<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/11
 * Time: 9:36
 */
namespace admin\controller;

/*
 * 会员管理
 *
 * */
class user extends \base\controller\backend{
    public function index(){
        $arr = isset($_REQUEST)? $_REQUEST : array() ;
        $map = D("user")->get_user_info($arr);
        $name = CONTROLLER_NAME;
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $list = $this->_list($model, $map);
        // 关联用户的表和投标的表
        $list3 = array();
        foreach ($list as $k => $v) {
            $condition['user_id'] = array('eq',$v['id']);
            $condition['contract_no'] = array('neq','');
            $money = D("deal_load")->where($condition)->getField("sum(money)");
            if ($money) {
                $v['deal'] = $money;
            } else {
                $v['deal'] = 0;
            }
            array_push($list3, $v);
        }

        $list2 = array();
        foreach ($list3 as $item) {
            $condition = array('mobile' => $item["mobile"]);
            $mobile = D("user_mobile_address")->where($condition)->select();
            if ($mobile) {
                $item['mobile'] = $item['mobile'] . "[" . $mobile['province'] . "-" . $mobile['city'] . "]";
            }
            array_push($list2, $item);
        }

        $list4 = array();
        foreach ($list2 as $k => $v) {
            $condition = array('user_id' => $v["id"], 'is_paid' => 1);
            $money = D("payment_notice")->where($condition)->getField('sum(money)');
            if ($money) {
                $v['payment'] = $money;
            } else {
                $v['payment'] = 0;
            }
            $v['error_num'] = D("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 0))->count();
            $v['success_num'] = D("payment_notice")->where(array('user_id' => $v["id"], 'is_paid' => 1))->count();
            array_push($list4, $v);
        }
        $this->assign("list",$list4);
        return $this->fetch();
    }

    public function add(){
        return $this->fetch();
    }

    public function insert(){
        $arr = array();
        foreach($_REQUEST as $key => $val){
            $val = $val==""? '':trim($val);
            if(($key=='user_name' || $key=='email' || $key=='user_pwd' || $key=='user_confirm_pwd') & $val=='' ){
                return $this->error("请将必填项填写完整");
            }
            $arr[$key] = $val;
        }
        if($arr['user_pwd']!=$arr['user_confirm_pwd']){
            return $this->error("两次输入密码不一致");
        }
        $id = D('user')->do_insert($arr);
        if($id==-1){
            return $this->error("会员名称已经被占用");
        }
        if($id==-2){
            return $this->error("邮箱已经被占用");
        }
        if($id==-3){
            return $this->error("手机号已经被占用");
        }
        if($id==-4){
            return $this->error("身份证号已经被占用");
        }
        if($id>0){
            return $this->success("添加成功！", "", "/" . MODULE_NAME . "/" . CONTROLLER_NAME . "/index");
        }

//        echo "<pre>";
//        print_r($arr);

    }

    public function edit(){
        $id = intval($_REQUEST ['id']);
//        $condition['is_delete'] = 0;
        $condition['id'] = $id;
        $vo = D("user")->where($condition)->select();
        $field = "login_ip,login_time,byear,bmonth,bday";
        $vo_ext = D("user_extend")->field($field)->where("user_id=".$id)->select();
        $this->assign('vo', $vo['0']);// user表查询的信息
        $this->assign('vo_ext',$vo_ext['0']);// 扩展表查询的信息
        return $this->fetch();
    }

    public function update(){
        $data = $_REQUEST;
        $status = D("user")->do_update($data);
        if($status==-1){
            return $this->error("两次密码输入不一致");
        }
        if($status==0){
            return $this->error("用户信息更新失败");
        }
        if($status==1){
            return $this->success("更新成功", "", "/" . MODULE_NAME . "/" . CONTROLLER_NAME . "/index");
        }
    }

    public function set_effect() {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = D("user")->get_effect($id,$ajax);
        print_r($info);

    }
    public function set_black(){
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = D("user")->get_black($id,$ajax);
        print_r($info);

    }

    public function delete(){
        $id_arr = $_REQUEST['id'];
        $ajax = $_REQUEST['ajax'];
        $result = D("user")->do_delete("$id_arr");
        if($result == 1){
            die(json_encode(array("status"=>$ajax,"info"=>"删除成功")));

        }else{
            die(json_encode(array("status"=>0,"info"=>"删除失败")));
        }
    }
}