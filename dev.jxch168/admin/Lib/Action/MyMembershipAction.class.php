<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class MyMembershipAction extends CommonAction{
	public function index()
	{
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$adm_name = $adm_session['adm_name'];
		$adm_id = intval($adm_session['adm_id']);
		$condition['is_delete'] = 0;
		$condition['is_effect'] = 1;
		//is_department  0:管理员  1：部门
		$is_department = M("Admin")->where("id=".$adm_id)->getField("is_department");

		if( $is_department == 0){
			$condition['admin_id'] = $adm_id;
		}elseif($is_department == 1)
		{
			$id = $GLOBALS['db']->getAll("SELECT id FROM  ".DB_PREFIX."admin WHERE  pid = ".$adm_id);
			$flatmap = array_map("array_pop",$id);
			$id=implode(',',$flatmap);
			$condition['admin_id'] = array("exp","in (".$adm_id.",".$id.")");
		}
		$this->assign("default_map",$condition);

		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		//追加默认参数
		if($this->get("default_map"))
		$map = array_merge($map,$this->get("default_map"));

		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}

		$model = D ("User");
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$list = $this->get("list");
		$this->assign("list",$list);

		$this->display ();
		return;
	}


        public function myindex(){
            //销售推广链接生成
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_name = $adm_session['adm_name'];
            $adm_id = intval($adm_session['adm_id']);

            $share_url = SITE_DOMAIN;
            $qz = M('admin')->field('work_id')->where("id = {$adm_id}")->find();
            $sale_url = $share_url.'/'.$qz['work_id'];
            $this->assign('sale_url',$sale_url);
            //我的客户总数（销售登录看）
            $myinvitet_count = M('user')->field('id,admin_id')->where("admin_id = {$adm_id}")->count();
            $this->assign('myinvitet_count',$myinvitet_count);
            //我的客户投资总额（销售登录看）
            $user_list = M('user')->field('id,admin_id')->where("admin_id = {$adm_id}")->select();
            $all_sum_money = 0;
            foreach($user_list as $v){
                $sum_money = M('deal_load')->field('user_id,sum(money) as sum_money')->where("user_id = {$v['id']}")->select();
                $sum_money[0]['sum_money'] = intval($sum_money[0]['sum_money']);
                $all_sum_money+=$sum_money[0]['sum_money'];
            }
            $this->assign('all_sum_money',$all_sum_money);

            //该门店下成员总数
            $is_mendian = M('admin')->field('id,is_department')->where("id = {$adm_id}")->find();
            if($is_mendian['is_department'] == "1"){
                $department_count = M('admin')->where("pid = {$is_mendian['id']}")->count();
                $this->assign('department_count',$department_count);
            }
            $this->assign('is_department',$is_mendian['is_department']);

            //该门店下成员发展客户总数
            $mendian_list = M('admin')->field('id,pid')->where("pid = {$adm_id}")->select();
            $tmpcount = array();
            foreach($mendian_list as $v){
                //$v['id'] = intval($v['id']);
                $mendian_list_one = M('user')->field('id,admin_id')->where("admin_id = {$v['id']}")->select();
                $tmpcount = array_merge($mendian_list_one,$tmpcount);

            }
            $user_count_all = count($tmpcount);
             //生成二维码
            //二维码URL
            $qr_code_url = SITE_DOMAIN . '/wap/index.php?ctl=register_red&r=' . $qz['work_id'];
            $logo_img_url = APP_ROOT_PATH.'/public/images/logo.png';
            $my_grcode = gen_qrcode($qr_code_url,$logo_img_url, 3);
            //分享代码
            $show_share_code = share_code();
            //var_dump($show_share_code);exit;
            $this->assign('my_grcode',$my_grcode);
            $this->assign("show_share_code", $show_share_code);
            $this->assign('qr_code_url',$qr_code_url);
            $this->assign('user_count_all',$user_count_all);
            $this->display();
        }

        public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {

			$condition = array ();
			$condition['id'] = array ('in', explode ( ',', $id ) );

			$list = M("User")->where ( $condition )->setField ( 'admin_id', 0 );
			if ($list!==false) {
				save_log($info.l("DELETE_SUCCESS"),1);
				$this->success (l("DELETE_SUCCESS"),$ajax);
			} else {
				save_log($info.l("DELETE_FAILED"),0);
				$this->error (l("DELETE_FAILED"),$ajax);
			}

		} else {
			$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}



	public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M("DealCity")->where("id=".$id)->getField("name");
		$c_is_effect = M("DealCity")->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M("DealCity")->where("id=".$id)->setField("is_effect",$n_is_effect);
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);

		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
	}


}
?>