<?php

class UserBonusAction extends CommonAction{
    public function index($map) {
        $name=$this->getActionName();
        $model = D ($name);
        if (! empty ( $model )) {
                $this->_list ( $model, $map );
        }
        $list = $this->get("list");
        $result = array();
        $row = 0;
        foreach($list as $k=>$v)
        {
                //$v['level'] = -1;
                $v['name'] = $v['name'];
                $result[$row] = $v;
                $row++;

        }
        foreach($result as $key=>$val){
            $user_info = M('user')->field('user_name')->find($val['user_id']);
            $result[$key]['user_name'] = $user_info['user_name'];
        }
		return $result;
    }
    //待放款红包
    public function bonus_no_loans() {
        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['begin_time']?($BeginDateToday):($_REQUEST['begin_time']);
        $begin_time = strtotime($BeginDate);
        $where = array('is_effect' => 1,'release_date'=> $begin_time,'status' => 1);
        $all_bonus = M(MODULE_NAME)->field('count(id) as bonus_num,release_date')->where(array('is_effect' => 1,'status' => 1))->group('release_date')->findAll();
        $result = $this->index($where);
        foreach($result as $key=>$val){
            $result[$key]['remark'] = $val['remark'].'活动，获得现金红包'.$val['money'].'元';
        }
        $this->assign("BeginDate",$BeginDate);
        $this->assign("list",$result);
        $this->assign("all_bonus",$all_bonus);
        $this->assign("now_action",'bonus_no_loans');
        $this->display ();
        return;
    }
    //已放款红包
    public function bonus_yes_loans() {
        //搜索时间限制
        $BeginDate = !$_REQUEST['begin_time']?0:($_REQUEST['begin_time']);
        $begin_time = strtotime($BeginDate);
        if($begin_time){
            $where = array('is_effect' => 1,'release_date'=> $begin_time,'status' => 2);
        }else{
            $where = array('is_effect' => 1,'status' => 2);
        }

        $result = $this->index($where);
        foreach($result as $key=>$val){
            $result[$key]['remark'] = $val['remark'].'活动，获得现金红包'.$val['money'].'元';
        }
        $this->assign("list",$result);
        if($BeginDate){
            $this->assign("BeginDate",$BeginDate);
        }
        $this->assign("now_action",'bonus_yes_loans');
        $this->display ();
        return;
    }
    //未提现红包
    public function bonus_wait_carry(){
        //$BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $real_name = !$_REQUEST['real_name'] ? '' : trim(($_REQUEST['real_name']));
        $user_name = !$_REQUEST['user_name'] ? '' : trim(($_REQUEST['user_name']));
        $mobile = !$_REQUEST['mobile'] ? '' : trim(($_REQUEST['mobile']));
        $userBonus = !$_REQUEST['userBonus'] ? '' : trim(($_REQUEST['userBonus']));
        $whereSqlArr  = array();
        if ($real_name)
        {
            $whereSqlArr['real_name'] = $_REQUEST['real_name'];
        }
         if ($user_name)
        {
            $whereSqlArr['user_name'] = $_REQUEST['user_name'];
        }
        if ($mobile)
        {
            $whereSqlArr['mobile'] = $_REQUEST['mobile'];
        }
        if($whereSqlArr){
             $user_info = M('user')->where($whereSqlArr)->select();
        }
        if ($user_info) {
            $where['user_id'] = $user_info['0']['id'];
        }
        if($userBonus==1){
            $where['status'] = 0;// 未处理
        }
        if($userBonus==2){
            $where['status'] = 1;// 提现申请中
        }
        if($userBonus==3){
            $where['status'] = 2;// 已放款
        }
        if($where){
            $result = $this->index($where);
        }
        foreach($result as $key=>$val){
            $result[$key]['remark'] = $val['remark'].'活动，获得现金红包'.$val['money'].'元';
        }

        $this->assign("list",$result);
        if($BeginDate){
            $this->assign("BeginDate",$BeginDate);
        }
        $this->assign("now_action",'bonus_wait_carry');
        $this->display ();
        return;
    }

    //导出红包记录
    public function export_bonus(){
        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['begin_time']?($BeginDateToday):($_REQUEST['begin_time']);
        $begin_time = strtotime($BeginDate);
        $status = $_REQUEST['status'];
        $now_action = $_REQUEST['now_action'];
        $where = array('is_effect' => 1,'release_date'=> $begin_time,'status' => $status);
        $result = M(MODULE_NAME)->where($where)->select();

        //如果为空则不导出数据 停止返回
        if(!$result){
            $result = $this->index($where);
            $this->assign("list",$result);
            $this->assign("BeginDate",$BeginDate);
            $this->assign("now_action",$now_action);
            $this->display ($now_action);
            return ;
        }
        //导出Excel入金表
        require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
        $objPHPExcel = new PHPExcel();

        $user_bonus_lists = array();
        //数据格式化
        foreach($result as $key => $value){
            $value['bonus_type'] = getBonusTypeName($value['bonus_type']);
            if($value['status'] == 0){
                $value['status'] = '未处理';
            }elseif($value['status'] == 1){
                $value['status'] = '提现申请中';
            }elseif($value['status'] == 2){
                $value['status'] = '已放款';
            }
            $value['user_info'] = $GLOBALS['db']->getRow("select real_name,mobile from ".DB_PREFIX."user where id = ".$value['user_id']);
            $value['money'] = num_format($value['money']);
            $value['generation_time'] = empty($value['generation_time'])?'暂无':date('Y-m-d H:i:s',$value['generation_time']);
            $value['apply_time'] = empty($value['apply_time'])?'暂无':date('Y-m-d H:i:s',$value['apply_time']);
            $value['release_time'] = empty($value['release_time'])?'暂无':date('Y-m-d H:i:s',$value['release_time']);
            $value['release_date'] = empty($value['release_date'])?'暂无':date('Y-m-d',$value['release_date']);
            $user_bonus_lists[$key+1] = $value;
        }
        $user_bonus_lists[0] = array();
        ksort($user_bonus_lists);
        /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
        foreach($user_bonus_lists as $key => $value){
            $num=$key + 1;
            if($key == 0){
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, '序号')
                          ->setCellValue('B'.$num, "付款方登录名")
                          ->setCellValue('C'.$num, "付款方中文名称")
                          ->setCellValue('D'.$num, "付款资金来自冻结")
                          ->setCellValue('E'.$num, "收款方登录名")
                          ->setCellValue('F'.$num, "收款方中文名称")
                          ->setCellValue('G'.$num, "收款后立即冻结")
                          ->setCellValue('H'.$num, "合同号")
                          ->setCellValue('I'.$num, "交易金额")
                          ->setCellValue('J'.$num, "备注信息")
                          ->setCellValue('K'.$num, "预授权合同号");
            }else{
                $objPHPExcel->setActiveSheetIndex(0)
                         ->setCellValue('B'.$num, PAY_LOG_NAME)
                          ->setCellValue('C'.$num, PAY_NAME)
                          ->setCellValue('D'.$num, "否")
                          ->setCellValue('E'.$num, $value['user_info']['mobile'])
                          ->setCellValue('G'.$num, "否")
                          ->setCellValue('H'.$num,'')
                          ->setCellValue('J'.$num, $value['remark'].'活动，获得现金红包'.$value['money'].'元')
                          ->setCellValue('K'.$num, '');

                $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num,str_pad(($num-1),4,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,$value['user_info']['real_name'],PHPExcel_Cell_DataType::TYPE_STRING);
                $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$num,$value['money'],PHPExcel_Cell_DataType::TYPE_STRING);
            }
        }
        //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(55);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:K1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename = app_conf("SHOP_TITLE") . "红包奖励记录表";
        php_export_excel($objPHPExcel,$filename);
    }
    //红包批量放款
    public function batch_deal(){

        $BeginDateToday = date('Y-m-d');
        //搜索时间限制
        $BeginDate = !$_REQUEST['begin_time']?($BeginDateToday):($_REQUEST['begin_time']);
        $begin_time = strtotime($BeginDate);
        $now_action = $_REQUEST['now_action'];
        $where = array('is_effect' => 1,'release_date'=> $begin_time,'status' => 1);
        $result = M(MODULE_NAME)->where($where)->select();
        $is_success = true;
        foreach($result as $key=>$user_bonus_info){
            $user_info = M('user')->find($user_bonus_info['user_id']);
            //开始发放红包
            $data['status'] = 2;
            $data['release_time'] = time();
            $data['release_date'] = strtotime(date('Y-m-d',time()));
            $map['id'] = $user_bonus_info['id'];
            $res = M(MODULE_NAME) -> where($map) -> save($data);
            if($res){
                require_once(APP_ROOT_PATH . "system/libs/user.php");
                modify_account(array('money'=>$user_bonus_info['money']),$user_info['id'],"用户".$user_info['user_name']."参与".$user_bonus_info['reward_name']."活动获得".$user_bonus_info['money']."现金红包奖励",29);
                //发送短信通知
                //如果成功 则短信通知
                $TPL_SMS_NAME = "TPL_SMS_BONUS";
                $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = '".$TPL_SMS_NAME."'");
                $tmpl_content = $tmpl['content'];
                $notice['user_name'] = $user_info['user_name'];
                $notice['get_month'] = date('m',$user_bonus_info['apply_time']);
                $notice['get_day'] = date('d',$user_bonus_info['apply_time']);
                $notice['money'] = $user_bonus_info['money'];
                $GLOBALS['tmpl']->assign("notice",$notice);
                $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                $msg_data['dest'] = $user_info['mobile'];
                $msg_data['send_type'] = 0;
                $msg_data['title'] = "现金红包短信通知";
                $msg_data['content'] = addslashes($msg);;
                $msg_data['send_time'] = 0;
                $msg_data['is_send'] = 0;
                $msg_data['create_time'] = TIME_UTC;
                $msg_data['user_id'] = $user_info['id'];
                $msg_data['is_html'] = 0;
                $msg_id = M('deal_msg_list') -> add($msg_data);//插入

		/*if ($msg_id > 0) {
                    $result = send_sms_email($msg_data);
                    发送结束，更新当前消息状态
                    $GLOBALS['db']->query("update " . DB_PREFIX . "deal_msg_list set is_success = " . intval($result['status']) . ",result='" . $result['msg'] . "',send_time='" . TIME_UTC . "' where id =" . $msg_id);
                    file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_bonus_notice.log', "POST:[" .$user_info['user_name']."的现金红包短信通知发送状态".$result['msg']."，短信内容：".json_encode($msg_data) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }*/
                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_deal_bonus.log', "POST:[" .$user_info['user_name']."的红包发放成功".json_encode($user_bonus_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            }else{
                $is_success = false;
                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_deal_bonus.log', "POST:[" .$user_info['user_name']."的红包发放失败".json_encode($user_bonus_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            }
        }
        if($is_success){
            $this->ajaxReturn('','红包放款成功！', 1);
        }else{
            $this->ajaxReturn('','红包放款失败！', 0);
        }
    }
    //红包放款
    public function grant_money(){
        $bonus_id = $_REQUEST['bonus_id'];
        if(!$bonus_id){
            $this->ajaxReturn('','该红包不存在！', 2);
            die();
        }
        $user_bonus_info = M(MODULE_NAME) -> find($bonus_id);
        if(!$user_bonus_info){
            $this->ajaxReturn('','该红包不存在！', 2);
            die();
        }
        if($user_bonus_info['status'] == 0){
            $this->ajaxReturn('','该红包未曾发起提现申请，暂时无法款放！', 2);
            die();
        }
        if($user_bonus_info['status'] == 2){
            $this->ajaxReturn('','该红包已经发放过，不能再次发放！', 2);
            die();
        }
        $bonus_money = $user_bonus_info['money'];
        if($bonus_money <= 0){
            $this->ajaxReturn('','该红包没有可用金额，无法发放！', 2);
            die();
        }
        $user_info = M('user')->find($user_bonus_info['user_id']);
        //开始发放红包
        $data['status'] = 2;
        $data['release_time'] = time();
        $data['release_date'] = strtotime(date('Y-m-d',time()));
        $where['id'] = $bonus_id;
        $res = M(MODULE_NAME) -> where($where) -> save($data);
        if($res){
            require_once(APP_ROOT_PATH . "system/libs/user.php");
            modify_account(array('money'=>$bonus_money),$user_info['id'],"用户".$user_info['user_name']."参与".$user_bonus_info['reward_name']."活动获得".$bonus_money."现金红包奖励",29);
            //发送短信通知
            //如果成功 则短信通知
            $msg = "尊敬的". app_conf("SHOP_TITLE") ."用户". $user_info['user_name']  . "，您的参与" .$user_bonus_info['reward_name']. "活动获得".$bonus_money."现金红包奖励已于".to_date(TIME_UTC,"Y-m-d")."成功到账。";
            $msg_data['dest'] = $user_info['mobile'];
            $msg_data['send_type'] = 0;
            $msg_data['title'] = "投标红包短信通知";
            $msg_data['content'] = addslashes($msg);;
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['user_id'] = $user_info['id'];
            $msg_data['is_html'] = 0;
            M('deal_msg_list') -> add($msg_data);//插入
            file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_deal_bonus.log', "POST:[" .$user_info['user_name']."的红包发放成功".json_encode($user_bonus_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $this->ajaxReturn('','红包放款成功！', 1);
            die();
        }
    }
    public function red_envelope_trash()
	{
		$condition['is_delete'] = 1;
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function add()
	{
		$this->assign("newsort",M(MODULE_NAME)->where("is_delete=0")->max("sort")+1);
		$this->display();
	}

	public function insert() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['money']))
		{
			$this->error("红包金额不能为空！");
		}

		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {

			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
			rm_auto_cache("cache_vip_red_envelope");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("INSERT_FAILED"),0);
			$this->error(L("INSERT_FAILED"));
		}
	}

	public function edit() {
		$id = intval($_REQUEST ['id']);
		$condition['is_delete'] = 0;
		$condition['id'] = $id;
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );

		$this->display ();
	}

	//状态 变更
    public function set_effect()
	{
		$id = intval($_REQUEST['id']);
		$ajax = intval($_REQUEST['ajax']);
		$info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		$c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
		$n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
		M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);
		save_log($info.l("SET_EFFECT_".$n_is_effect),1);
		rm_auto_cache("cache_vip_red_envelope");
		$this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
	}

	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
		rm_auto_cache("cache_vip_red_envelope");
		$this->success(l("SORT_SUCCESS"),1);
	}

	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("title");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			save_log($log_info.L("UPDATE_SUCCESS"),1);
			rm_auto_cache("cache_vip_red_envelope");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			save_log($log_info.L("UPDATE_FAILED"),0);
			$this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
		}
	}

	public function delete() {
		//删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
                                $res = M(MODULE_NAME)->where($condition)->delete();
				if ($res!==false) {
					save_log($info.l("DELETE_SUCCESS"),1);
					rm_auto_cache("cache_vip_red_envelope");
					$this->success (l("DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("DELETE_FAILED"),0);
					$this->error (l("DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	public function restore() {
		//恢复指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->setField ( 'is_delete', 0 );
				if ($list!==false) {
					save_log($info.l("RESTORE_SUCCESS"),1);
					rm_auto_cache("cache_vip_red_envelope");
					$this->success (l("RESTORE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("RESTORE_FAILED"),0);
					$this->error (l("RESTORE_FAILED"),$ajax);

				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );

				$rel_data = M(MODULE_NAME)->where($condition)->findAll();
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();

				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					rm_auto_cache("cache_vip_red_envelope");
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}

	//获得红包
	public function red_envelope_record(){

		if(trim($_REQUEST['user_name'])!='')
		{
			$sql  ="select group_concat(id) from ".DB_PREFIX."user where user_name like '%".trim($_REQUEST['user_name'])."%'";
			$ids = $GLOBALS['db']->getOne($sql);
			$map[DB_PREFIX.'red_envelope_record.user_id'] = array("in",$ids);
		}

		if(trim($_REQUEST['money'])!='')
		{
			$map[DB_PREFIX.'red_envelope_record.money'] = array('eq',intval($_REQUEST['money']));
		}
		$begin_time  = trim($_REQUEST['begin_time']);
		$end_time  = trim($_REQUEST['end_time']);
		if($begin_time !='' || $end_time !=''){
			if($end_time==0)
			{
				$map[DB_PREFIX.'red_envelope_record.release_date'] = array('egt',$begin_time);
			}
			else
				$map[DB_PREFIX.'red_envelope_record.release_date']= array("between",array($begin_time,$end_time));

		}

		$model = D ("RedEnvelopeRecord");

		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
	}

}
?>
