<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行 提成管理模块 提成放款业务逻辑相关处理类
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class UserRewardModel extends CommonModel {

        protected $tableName = 'user_reward';

        public function init_reward($map,$dMonth){
            $cInfo = D('Calendar')->calendarMonth($dMonth);
            //周信息
            $week_info = $cInfo[0];
            $result['week_info'] = $week_info;
            //去除数组的第一个元素
            array_shift($cInfo);
            //初始化信息  //根据起息日
            $new_calendar = array();
            foreach($cInfo as $key => $val){
                foreach($val as $k => $v){
                    $map["release_date"] = strtotime($v);
                    //排序
                    $orderBy ="release_date desc";
                    $return = $this->getRewardList($map,$orderBy);
                    $reward_count = $return['count'];
                    $new_calendar[$key][$k]['week_day'] = $v;
                    $new_calendar[$key][$k]['reward_count'] = $reward_count;
                    //某个日期下 提成总额
                    $new_calendar[$key][$k]['reward_money_total'] = num_format($return['reward_money_total'],2);
                }
            }

            $result['new_calendar'] = $new_calendar;
            return $result;
        }

        //获取提成列表
        public function getRewardList($map,$orderby = '',$limit = ''){
            $return['count'] = $this->where($map)->order($orderby)->count();
            $return['list'] = $this->where($map)->order($orderby)->limit($limit)->select();
            foreach($return['list'] as $key => $val){

                $return['reward_money_total'] += $val["money"];
                //提成状态
                if($val["status"] == 0){
                    $return['list'][$key]["status_desc"] = "未发放";
                }elseif($val["status"] == 1){
                    $return['list'][$key]["status_desc"] = "已发放";
                }
                //提成类型  1提成奖励',
                if($val["reward_type"] == 1){
                    $return['list'][$key]["repay_type_desc"] = "提成奖励";
                }
                //是否有效
                if($val["is_effect"] == 0){
                    $return['list'][$key]["is_effect_desc"] = "无效";
                }elseif($val["is_effect"] == 1){
                    $return['list'][$key]["is_effect_desc"] = "有效";
                }
                //标的信息
                $deal = M("deal")->find($val["deal_id"]);
                $return['list'][$key]["deal_name"] = $deal["name"];
                $return['list'][$key]["deal_rate"] = $deal["rate"];
                $return['list'][$key]["repay_time"] = $deal["repay_time"];
                //用户名 邀请人信息
		$user_info = M("user")->find($val["user_id"]);
                $return['list'][$key]["user_name"] = $user_info["user_name"];
                $return['list'][$key]["real_name"] = $user_info["real_name"];
                $return['list'][$key]["mobile"] = $user_info["mobile"];

                //被邀请人投资信息
                $deal_load = M("deal_load")->find($val["load_id"]);
                $return['list'][$key]["load_create_time"] = $deal_load["create_time"];
                $return['list'][$key]["load_money"] = $deal_load["money"];
                //被邀请人信息
                $user_invite_info = M("user")->find($deal_load["user_id"]);
                $return['list'][$key]["load_user_id"] = $user_invite_info['id'];
                $return['list'][$key]["load_user_name"] = $user_invite_info['user_name'];
                $return['list'][$key]["load_real_name"] = $user_invite_info['real_name'];
                $return['list'][$key]["load_mobile"] = $user_invite_info['mobile'];
            }

            //数据格式化
            $reward_lists = $return['list'];
            $l = $r = array();
            foreach($reward_lists as $val){
                if($val['verify_status'] == 0){
                    $l[] = $val;
                }else if($val['verify_status'] == 1){
                    $r[] = $val;
                }
            }
            $reward_lists = array_merge($l,$r);

            $left_arr = $right_arr = array();
            foreach($reward_lists as $val){
                if($val['status'] == 0){
                    $left_arr[] = $val;
                }else if($val['status'] == 1){
                    $right_arr[] = $val;
                }
            }
            $reward_lists = array_merge($left_arr,$right_arr);
            $return['list'] = $reward_lists;
            return $return;
        }

        //提成审核
        public function batch_verify($where,$orderBy,$limit = ''){
            $result = $this->getRewardList($where,$orderBy,$limit);
            //判断是否可放款
            if(!$result["list"]){
                $return["status"] = 0;
                $return["info"] = "没有可审核的提成";
                return $return;
            }
            $flag_type = false;
            foreach($result["list"] as $key=>$user_reward_info){
                $user_info = M('user')->find($user_reward_info['user_id']);

                //开始审核提成
                $data['verify_status'] = 1;
                $data['verify_time'] = time();
                $data['id'] = $user_reward_info['id'];
                $res = M("user_reward")->save($data);
                if(!$res){
                    $flag_type = true;
                    $data['verify_status'] = 1;
                    $data['verify_time'] = 0;
                    M("user_reward")->save($data);
                }

            }
            $return["info"] = "提成审核成功！";
            if($flag_type){
                $return["info"] = "提成审核未完全成功！";
            }
            $return["status"] = 1;

            return $return;
        }

        //提成放款
        public function batch_reward($where,$orderBy,$limit = ''){
            $result = $this->getRewardList($where,$orderBy,$limit);

            //判断是否可放款
            if(!$result["list"]){
                $return["status"] = 0;
                $return["info"] = "没有可放款的提成";
                return $return;
            }

            //富友资金池信息数据 验证余额是否充足
            $reward_all_money = 0;
            $cash_data = $this->getAccount(PAY_LOG_NAME);
            foreach($result["list"] as $k=>$v){
                $reward_all_money += $v["money"];
            }
            if($cash_data['ca_balance'] <=0 || ($cash_data['ca_balance'] > 0 && $cash_data['ca_balance'] < $reward_all_money)){
                $return["status"] = 0;
                $return["info"] = "平台账户余额不足，请及时充值后再操作！";
                return $return;
            }

            $err_money = 0;
            $data_err = false;
            foreach($result["list"] as $key=>$user_reward_info){
                $user_info = M('user')->find($user_reward_info['user_id']);
                //富友转账 还款
                require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                $fuyou = new fuyou();
                //提成ID
                $load_repay_id[]= $user_reward_info['id'];
                //转账记录数据 走平台账户PAY_LOG_NAME FUYOU_MCHNT_FR
                $arr = $fuyou->transferBmuAction(PAY_LOG_NAME,$user_info['fuiou_account'], $user_reward_info['money'],'',$user_reward_info['id']);
                //转账成功
                if ('0000' == $arr->plain->resp_code) {
                    //开始发放提成
                    $data['status'] = 1;
                    $data['act_release_time'] = time();
                    $map['id'] = $user_reward_info['id'];
                    $res = M("user_reward")->where($map)->save($data);
                    if($res){
                        require_once(APP_ROOT_PATH . "system/libs/user.php");
                        modify_account(array('money'=>$user_reward_info['money']),$user_info['id'],"用户".$user_info['user_name']."参与".$user_reward_info['reward_name']."活动获得".$user_reward_info['money']."现金提成奖励",29);
                        //成功的日志
                        $admin_log['money'] += $user_reward_info['money'];

                        //发送短信通知 如果成功 则短信通知//
                        $TPL_SMS_NAME = "TPL_SMS_REWARD";
                        $tmpl = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."msg_template where name = '".$TPL_SMS_NAME."'");

                        $tmpl_content = $tmpl['content'];
                        $notice['mobile'] = $user_info['mobile'];
                        $notice['money'] = $user_reward_info['money'];
                        $GLOBALS['tmpl']->assign("notice",$notice);
                        $msg = $GLOBALS['tmpl']->fetch("str:".$tmpl_content);

                        $msg_data['dest'] = $user_info['mobile'];
                        $msg_data['send_type'] = 0;
                        $msg_data['title'] = "现金提成短信通知";
                        $msg_data['content'] = addslashes($msg);;
                        $msg_data['send_time'] = 0;
                        $msg_data['is_send'] = 0;
                        $msg_data['create_time'] = TIME_UTC;
                        $msg_data['user_id'] = $user_info['id'];
                        $msg_data['is_html'] = 0;
                        $msg_id = M('deal_msg_list') -> add($msg_data);//插入

                        file_put_contents(APP_ROOT_PATH . 'log/userReward/' . date('Y-m-d') . '_user_deal_reward.log', "POST:[" .$user_info['user_name']."的提成发放成功".json_encode($user_reward_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }else{
                        $admin_log['err_money'] += $user_reward_info['money'];
                        $err_load_repay_id[]= $user_reward_info['id'];
                        //更新数据库失败
                        $data_err = true;
                        file_put_contents(APP_ROOT_PATH . 'log/userReward/' . date('Y-m-d') . '_user_deal_reward.log', "POST:[" .$user_info['user_name']."的提成发放失败,富友转账成功，数据库更新失败".json_encode($user_reward_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }
                } else {
                    $admin_log['err_money'] += $user_reward_info['money'];
                    $err_load_repay_id[]= $user_reward_info['id'];
                    //划拨失败
                    $err_money += $user_reward_info['money'];
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_user_deal_reward.log', "[提成放款富友转账失败，失败的user_reward提成记录的ID为" . $user_reward_info['id'] . "，投标人ID为" . $user_info['user_id'] . ",投标人用户名为" . $user_info['user_name'] . ",失败金额为" . $user_reward_info['money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
            }
            //如果提成放款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理员//
            if($admin_log['err_money']){
                $msg = date("Y-m-d",$result["list"][0]["release_date"])."的提成放款数据存在异常，请您确认。";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_user_deal_reward.log', $msg."异常信息：【未成功放款的提成ID：".json_encode($err_load_repay_id)."】；【未成功金额：".$admin_log['err_money']."】", FILE_APPEND);
                info_admin($msg,"提成放款");
            }

            if($err_money == 0){
                $return["info"] = "提成放款成功";
                if($data_err){
                   $return["info"] = "提成放款成功,但是有部分用户平台数据更新失败";
                }
                $return["status"] = 1;
            }else{
                $return["status"] = 0;
                $return["info"] = "提成放款失败";
            }

            //管理员操作日志
            $admin_log['operate_type'] = 6;//操作类型 6代表提成放款
            $admin_log['load_repay_id'] = $load_repay_id ? implode(",", $load_repay_id) : '';
            $admin_log['err_load_repay_id'] = $err_load_repay_id ? implode(",", $err_load_repay_id) : '';
            $admin_log['operate_desc'] = "提成放款";
            $admin_log['operate_time'] = time();
            $admin_log['operate_date'] = date("Y-m-d");
            $admin_log['operate_ip'] = get_client_ip();
            $admin_log['remark'] = $return['info'];
            $return['admin_log'] = $admin_log;

            return $return;
        }

        public function saveRewardPwd($data){
            $adminInfo = $this->getAdminInfo();
            $data["id"] = $adminInfo['id'];
            $res = M("admin")->save($data);
            return $res;
        }

        //导出提成列表
        public function export_reward($where,$orderBy,$limit = ''){
            $result = $this->getRewardList($where,$orderBy,$limit);

            //导出Excel提成表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();

            $user_reward_lists = array();
            //数据格式化
            foreach($result["list"] as $key => $value){
                if($value['reward_type'] == 1){
                    $value['reward_type'] = '提现奖励';
                }
                if($value['status'] == 0){
                    $value['status'] = '未放款';
                }elseif($value['status'] == 1){
                    $value['status'] = '已放款';
                }
                if($value['verify_status'] == 0){
                    $value['verify_status'] = '待审核';
                }elseif($value['verify_status'] == 1){
                    $value['verify_status'] = '已审核';
                }
                if($value['is_effect'] == 0){
                    $value['is_effect'] = '无效';
                }elseif($value['is_effect'] == 1){
                    $value['is_effect'] = '有效';
                }
                $value['user_info'] = $GLOBALS['db']->getRow("select real_name,mobile from ".DB_PREFIX."user where id = ".$value['user_id']);
                $value['money'] = num_format($value['money']);
                $value['generation_time'] = empty($value['generation_time'])?'暂无':date('Y-m-d H:i:s',$value['generation_time']);
                $value['release_date'] = empty($value['release_date'])?'暂无':date('Y-m-d',$value['release_date']);
                $value['act_release_time'] = empty($value['act_release_time'])?'暂无':date('Y-m-d',$value['act_release_time']);
                $user_reward_lists[$key+1] = $value;
            }
            $user_reward_lists[0] = array();
            ksort($user_reward_lists);
            /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
            foreach($user_reward_lists as $key => $value){
                $num=$key + 1;
                if($key == 0){
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, '序号')
                              ->setCellValue('B'.$num, "提成ID")
                              ->setCellValue('C'.$num, '标的名称')
                              ->setCellValue('D'.$num, '标的利率')
                              ->setCellValue('E'.$num, '标的期限')
                              ->setCellValue('F'.$num, "邀请人")
                              ->setCellValue('G'.$num, "投资人")
                              ->setCellValue('H'.$num, "投资金额")
                              ->setCellValue('I'.$num, "投资时间")
                              ->setCellValue('J'.$num, "提成奖励类型")
                              ->setCellValue('K'.$num, "提成奖励利率")
                              ->setCellValue('L'.$num, "提成奖励金额")
                              ->setCellValue('M'.$num, "提成奖励状态")
                              ->setCellValue('N'.$num, "提成奖励生成时间")
                              ->setCellValue('O'.$num, "预计发放日期")
                              ->setCellValue('P'.$num, "实际发放日期")
                              ->setCellValue('Q'.$num, "审核状态")
                              ->setCellValue('R'.$num, "是否有效")
                              ->setCellValue('S'.$num, "备注");
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)
                             ->setCellValue('B'.$num, $value['id'])
                              ->setCellValue('C'.$num, $value['deal_name'])
                              ->setCellValue('D'.$num, $value['deal_rate'])
                              ->setCellValue('E'.$num, $value['repay_time'])
                              ->setCellValue('F'.$num, $value['user_name']."[".$value['real_name']."]")
                              ->setCellValue('G'.$num, $value['load_user_name']."[".$value['load_real_name']."]")
                              ->setCellValue('H'.$num,$value['load_money'])
                              ->setCellValue('I'.$num, date("Y-m-d H:i:s",$value['load_create_time']))
                              ->setCellValue('J'.$num, $value['reward_type'])
                              ->setCellValue('K'.$num, $value['reward_rate'])
                              ->setCellValue('L'.$num, $value['money'])
                              ->setCellValue('M'.$num, $value['status'])
                              ->setCellValue('N'.$num, $value['generation_time'])
                              ->setCellValue('O'.$num, $value['release_date'])
                              ->setCellValue('P'.$num, $value['act_release_time'])
                              ->setCellValue('Q'.$num, $value['verify_status'])
                              ->setCellValue('R'.$num, $value['is_effect'])
                              ->setCellValue('S'.$num, $value['remark']);

                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num,str_pad(($num-1),4,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:R1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:R1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = app_conf("SHOP_TITLE") . "提成奖励记录表";
            php_export_excel($objPHPExcel,$filename);
        }
}
?>