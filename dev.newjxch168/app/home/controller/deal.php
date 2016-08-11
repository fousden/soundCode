<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台borrow借款公用控制器
 *
 * @author jxch
 */

class Deal extends frontend{

    //投资列表
    function index(){

        //初始化投资列表信息
        $result = D("deal")->initDealInfo($_REQUEST);
        //标的分类
        $this->assign('cate_list_url', $result['cate_list_url']);
        //标的状态
        $this->assign('deal_status_url', $result['deal_status_url']);
        //标的收益
        $this->assign('interest_url', $result['interest_url']);
        //标的期限
        $this->assign('months_type_url', $result['months_type_url']);
        //排序
        $this->assign('sort_url', $result['sort_url']);
        //理财列表
        $return = D("deal")->getDealList($_REQUEST);
        $this->assign('page', $return['page']);
        $this->assign('nowPage', $return['nowPage']);
        $this->assign('deal_list', $return['deal_list']);
        return $this->fetch();
    }
    
    //投资详情
    public function deal_info(){
        $deal_id=  isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;
        $data_info=D('mapi/deal')->getDealInfoById($deal_id,"*");
        $user_id=$_SESSION['user_info']['id'];
        // 判断该标的改用户是否关注过
        if($user_id){
            $res = M("collection")->where("deal_id=$deal_id and user_id=$user_id")->find();
            if($res){
                $data_info['is_faved'] = 1;
            }else{
                $data_info['is_faved'] = 0;
            }
        }

        $user_info=D('user')->getUserInfoById($user_id);
        $coupon_list=D('coupon')->getCouponList('',$user_id);
        $this->assign('coupon_list',$coupon_list);
        $this->assign('deal',$data_info);
        $this->assign('user_info',$user_info);
//        echo '<pre>';var_dump($data_info);echo '</pre>';die;
        return $this->fetch();
    }
    public function collect(){
        $user_id = $_SESSION['user_info']['id'];
        if(!$user_id){
            $html = $this->fetch("public/login_form");
            $data['status'] = -1;
            $data['html'] = $html;
            ajax_return($data) ;
        }

        // 如果$user_id存在
        $deal_id = $_REQUEST['deal_id'];
        $info['user_id'] = $user_id;
        $info['deal_id'] = $deal_id;
        $info['create_time'] = time();
        $id = M("collection")->add($info);
        if($id>0){
            $data['status'] = 1;
            $data['info'] = "操作成功";
            ajax_return($data);
        }
    }

    public function cancel(){
        $user_id = $_SESSION['user_info']['id'];
        $deal_id = $_REQUEST['deal_id'];
        $res = M("collection")->where("user_id=".$user_id." and deal_id=".$deal_id)->delete();
        if($res){
            $data['status'] = 1;
            $data['info'] = "操作成功";
            ajax_return($data);
        }
    }
}
