<?php
namespace admin\controller;
use \base\controller\backend;

/**
 * 后台 黑名单控制器
 *
 * @author jxch
 */

class Blacklist extends backend{
    
    //手机黑名单列表
    function mobile_index(){
        $blackListModel = D('Blacklist');
        $type = 1;
        $mobile_list = $blackListModel->getBlackList($type);
        $this->assign("page", $mobile_list['page']);
        $this->assign("nowPage", $mobile_list["nowPage"]);
        $this->assign("list",$mobile_list['data_list']);
        return $this->fetch();
    }
    
    //删除
    function delete(){
        $id = $_REQUEST['id'];
        $ajax = $_REQUEST['ajax'];
        if($id){
            $arr = explode(',',$id);
            foreach($arr as $key=>$val){
                $update_data['id'] = $val;
                $update_data['remove_time'] = time();
                $update_data['is_delete'] = 1;
                M('blacklist')->save($update_data);
            }
            
            die(json_encode(array('status'=>1)));
        }
        die(json_encode(array('status'=>0,'info'=>'请重新操作！')));
    }
}

