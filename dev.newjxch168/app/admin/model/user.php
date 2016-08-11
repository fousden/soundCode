<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/1/11
 * Time: 15:45
 */
namespace admin\model;
use base\model\backend;
class user extends backend{
    function get_user_info($arr){
        define(DB_PREFIX,'jxch_');
        $_REQUEST = $arr;
        if (intval($_REQUEST['is_effect']) != -1 && isset($_REQUEST['is_effect'])) {
            $map[DB_PREFIX . 'user.is_effect'] = array('eq', intval($_REQUEST['is_effect']));
        }
        $map[DB_PREFIX . 'user.is_auto'] = 0;
        $map = $this->getUserList(0, 0, $map);
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        return $map;

    }
    private function getUserList($user_type = 0, $is_delete = 0, $map) {

//        $group_list = M("UserGroup")->findAll();
//        $this->assign("group_list", $group_list);

        $map[DB_PREFIX . 'user.user_type'] = $user_type;
        //定义条件
        $map[DB_PREFIX . 'user.is_delete'] = $is_delete;

        if (intval($_REQUEST['group_id']) > 0) {
            $map[DB_PREFIX . 'user.group_id'] = intval($_REQUEST['group_id']);
        }

        if (trim($_REQUEST['user_name']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.user_name'] = array('eq', trim($_REQUEST['user_name']));
            else
                $map[DB_PREFIX . 'user.user_name'] = array('like', '%' . trim($_REQUEST['user_name']) . '%');
        }
        if (trim($_REQUEST['real_name']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.real_name'] = array('eq', trim($_REQUEST['real_name']));
            else
                $map[DB_PREFIX . 'user.real_name'] = array('like', '%' . trim($_REQUEST['real_name']) . '%');
        }
        if (trim($_REQUEST['email']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.email'] = array('eq', trim($_REQUEST['email']));
            else
                $map[DB_PREFIX . 'user.email'] = array('like', '%' . trim($_REQUEST['email']) . '%');
        }
        if (trim($_REQUEST['mobile']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.mobile'] = array('eq', trim($_REQUEST['mobile']));
            else
                $map[DB_PREFIX . 'user.mobile'] = array('like', '%' . trim($_REQUEST['mobile']) . '%');
        }
        if (trim($_REQUEST['pid_name']) != '') {
            $pid = M("User")->where("user_name='" . trim($_REQUEST['pid_name']) . "'")->getField("id");
            $map[DB_PREFIX . 'user.pid'] = $pid;
        }
        // 处理注册来源
        if (trim($_REQUEST['search_channel']) != '') {
            if (intval($_REQUEST['is_mohu']) == 0)
                $map[DB_PREFIX . 'user.search_channel'] = array('eq', trim($_REQUEST['search_channel']));
            else
                $map[DB_PREFIX . 'user.search_channel'] = array('like', '%' . trim($_REQUEST['search_channel']) . '%');
        }

//        // 处理销售
//        if (trim($_REQUEST['admin_name']) != '') {
//            $admin_id = M("Admin")->where("adm_name='" . trim($_REQUEST['admin_name']) . "'")->getField("id");
//            $map[DB_PREFIX . 'user.admin_id'] = $admin_id;
//        }

        $begin_time = trim($_REQUEST['start_time']) == '' ? 0 : to_timespan($_REQUEST['start_time']);
        $end_time = trim($_REQUEST['end_time']) == '' ? 0 : to_timespan($_REQUEST['end_time']);
        if ($begin_time > 0 || $end_time > 0) {
            if ($end_time == 0) {
                $map[DB_PREFIX . 'user.create_time'] = array('egt', $begin_time);
            } else
                $map[DB_PREFIX . 'user.create_time'] = array("between", array($begin_time, $end_time));
        }
        return $map;

    }

    public function do_insert($arr){
        // 将数据写进数据库
        $data['user_name'] = $arr['user_name'];
        $id = D('user')->where("user_name=".$data['user_name'])->getField('id');
        if($id){
            return "-1";
        }
        $data['email'] = $arr['email'];
        $id = D('user')->where("email=".$data['email'])->getField('id');
        if($id){
            return "-2";
        }
        $data['user_pwd'] = md5($arr['user_pwd']);
        $data['mobile'] = $arr['mobile'];
        $id = D('user')->where("mobile=".$data['mobile'])->getField('id');
        if($id){
            return "-3";
        }
        $data['user_type'] = $arr['user_type'];
        $data['is_effect'] = $arr['is_effect'];
        $data['create_time'] = time();
        return D('user')->add($data);
    }


    public function get_effect($id,$ajax){
        $c_is_effect = M(CONTROLLER_NAME)->where("id=" . $id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        $data['is_effect'] = $n_is_effect;
        D("user")->where("id=".$id)->save($data);
//        save_log($info . l("SET_EFFECT_" . $n_is_effect), 1);
        return $this->ajaxReturn($n_is_effect, l("SET_EFFECT_" . $n_is_effect), 1);
    }

    public function get_black($id,$ajax){
        $c_is_black = M(CONTROLLER_NAME)->where("id=" . $id)->getField("is_black");  //当前状态
        $n_is_black = $c_is_black == 0 ? 1 : 0; //需设置的状态
        $data['is_black'] = $n_is_black;
        D("user")->where("id=".$id)->save($data);
//        save_log($info . l("SET_EFFECT_" . $n_is_effect), 1);
        return $this->ajaxReturn($n_is_black, l("SET_BLACK_" . $n_is_black), 1);
    }

    public function do_delete($id_arr){
        // 删除
       $result = D('user')->where("id in (".$id_arr.")")->setField("is_delete",1);
       if($result){
           return "1";
       }else{
           return "0";
       }
    }

    public function do_update($data){
        $id = isset($data['id'])? trim($data['id']) : '';
        $user_data['user_type']=$user_type = isset($data['user_type'])? trim($data['user_type']) : '';
        $user_data['user_name']=$user_name = isset($data['user_name'])? trim($data['user_name']) : '';
        $user_data['email']=$email = isset($data['email'])? trim($data['email']) : '';
        $user_data['mobile']=$mobile = isset($data['mobile'])? trim($data['mobile']) : '';
        $user_data['user_pwd']=$user_pwd = isset($data['user_pwd'])? md5(trim($data['user_pwd'])) : '';
        $user_confirm_pwd = isset($data['user_confirm_pwd'])? md5(trim($data['user_confirm_pwd'])) : '';
        $user_data['real_name']=$real_name = isset($data['real_name'])? trim($data['real_name']) : '';
        $user_data['idno']=$idno = isset($data['idno'])? trim($data['idno']) : '';
        $user_ext_data['byear']=$byear = isset($data['byear'])? trim($data['byear']) : ''; // 扩展表字段
        $user_ext_data['bmonth']=$bmonth = isset($data['bmonth'])? trim($data['bmonth']) : ''; // 扩展表字段
        $user_ext_data['bday']=$bday = isset($data['bday'])? trim($data['bday']) : ''; // 扩展表字段
        $user_data['sex']=$sex = isset($data['sex'])? trim($data['sex']) : '';
        if($user_pwd !== $user_confirm_pwd){
            return "-1";
        }
        $res1 = D("user")->where("id=".$id)->save($user_data);
        $res2 = D("user_extend")->where("user_id=".$id)->save($user_ext_data);
        if($res1 && $res2){
            return "1";
        }else{
            return "0";
        }
    }

}