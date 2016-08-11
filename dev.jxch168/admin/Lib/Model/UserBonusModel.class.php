<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行 红包管理模块 红包放款业务逻辑相关处理类
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class UserBonusModel extends CommonModel {

        protected $tableName = 'user_bonus';

        public function init_bonus($map,$dMonth){
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
                    $orderBy ="release_time desc";
                    $return = $this->getBonusList($map,$orderBy);
                    $bonus_count = $return['count'];

                    $new_calendar[$key][$k]['week_day'] = $v;
                    $new_calendar[$key][$k]['bonus_count'] = $bonus_count;
                    //某个日期下 红包总额
                    $new_calendar[$key][$k]['bonus_money_total'] = num_format($return['bonus_money_total'],2);
                }
            }

            $result['new_calendar'] = $new_calendar;
            return $result;
        }

        //获取红包列表
        public function getBonusList($map,$orderby = '',$limit = ''){
            $return['count'] = $this->where($map)->order($orderby)->count();
            $return['list'] = $this->where($map)->order($orderby)->limit($limit)->select();
            foreach($return['list'] as $key => $val){
                $return['bonus_money_total'] += $val["money"];
                //红包状态
                if($val["status"] == 0){
                    $return['list'][$key]["status_desc"] = "未处理";
                }elseif($val["status"] == 1){
                    $return['list'][$key]["status_desc"] = "已提交提现申请";
                }elseif($val["status"] == 2){
                    $return['list'][$key]["status_desc"] = "已发放";
                }
                //红包类型  0满就送 1邀请注册 2邀请投资 3大转盘 4刮刮卡 5人人摇  6微信分享抽奖活动  7实名认证',......

                $return['list'][$key]["bonus_type_desc"] = getBonusTypeName($val["bonus_type"]);


                //是否有效
                if($val["is_effect"] == 0){
                    $return['list'][$key]["is_effect_desc"] = "无效";
                }elseif($val["is_effect"] == 1){
                    $return['list'][$key]["is_effect_desc"] = "有效";
                }
                //用户名
				$user_info = M("user")->find($val["user_id"]);
                $return['list'][$key]["user_name"] = $user_info["user_name"];
                $return['list'][$key]["real_name"] = $user_info["real_name"];
            }

            //数据格式化
            $bonus_lists = $return['list'];
            $left_arr = $mid_arr = $right_arr = array();
            foreach($bonus_lists as $val){
                if($val['status'] == 1){
                    $left_arr[] = $val;
                }else if($val['status'] == 0){
                    $mid_arr[] = $val;
                }else if($val['status'] == 2){
                    $right_arr[] = $val;
                }
            }
            $bonus_lists = array_merge($left_arr,$mid_arr,$right_arr);
            $l = $r = array();
            foreach($bonus_lists as $val){
                if($val['verify_status'] == 0){
                    $l[] = $val;
                }else if($val['verify_status'] == 1){
                    $r[] = $val;
                }
            }
            $bonus_lists = array_merge($l,$r);
            $return['list'] = $bonus_lists;

            return $return;
        }

        //红包审核
        public function batch_verify($where,$orderBy,$limit = ''){
            $result = $this->getBonusList($where,$orderBy,$limit);
            //判断是否可放款
            if(!$result["list"]){
                $return["status"] = 0;
                $return["info"] = "没有可审核的红包";
                return $return;
            }
            $flag_type = false;
            foreach($result["list"] as $key=>$user_bonus_info){
                $user_info = M('user')->find($user_bonus_info['user_id']);

                //开始审核红包
                $data['verify_status'] = 1;
                $data['verify_time'] = time();
                $data['id'] = $user_bonus_info['id'];
                $res = M("user_bonus")->save($data);
                if(!$res){
                    $flag_type = true;
                    $data['verify_status'] = 1;
                    $data['verify_time'] = 0;
                    M("user_bonus")->save($data);
                }

            }
            $return["info"] = "红包审核成功！";
            if($flag_type){
                $return["info"] = "红包审核未完全成功！";
            }
            $return["status"] = 1;

            return $return;
        }

        //红包放款
        public function batch_bonus($where,$orderBy,$limit = ''){
            $result = $this->getBonusList($where,$orderBy,$limit);

            //判断是否可放款
            if(!$result["list"]){
                $return["status"] = 0;
                $return["info"] = "没有可放款的红包";
                return $return;
            }

            //富友资金池信息数据 验证余额是否充足
            $bonus_all_money = 0;
            $cash_data = $this->getAccount(PAY_LOG_NAME);
            foreach($result["list"] as $k=>$v){
                $bonus_all_money += $v["money"];
            }
            if($cash_data['ca_balance'] <=0 || ($cash_data['ca_balance'] > 0 && $cash_data['ca_balance'] < $bonus_all_money)){
                $return["status"] = 0;
                $return["info"] = "平台账户余额不足，请及时充值后再操作！";
                return $return;
            }

            $err_money = 0;
            $data_err = false;
            foreach($result["list"] as $key=>$user_bonus_info){
                $user_info = M('user')->find($user_bonus_info['user_id']);
                //富友转账 还款
                require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                $fuyou = new fuyou();
                //红包ID
                $load_repay_id[]= $user_bonus_info['id'];
                //转账记录数据 走平台账户PAY_LOG_NAME FUYOU_MCHNT_FR
                $arr = $fuyou->transferBmuAction(PAY_LOG_NAME,$user_info['fuiou_account'], $user_bonus_info['money'],'',$user_bonus_info['id']);
                //转账成功
                if ('0000' == $arr->plain->resp_code) {
                    //开始发放红包
                    $data['status'] = 2;
                    $data['act_relase_time'] = time();
                    $map['id'] = $user_bonus_info['id'];
                    $res = M("user_bonus")->where($map)->save($data);
                    if($res){
                        require_once(APP_ROOT_PATH . "system/libs/user.php");
                        modify_account(array('money'=>$user_bonus_info['money']),$user_info['id'],"用户".$user_info['user_name']."参与".$user_bonus_info['reward_name']."活动获得".$user_bonus_info['money']."现金红包奖励",29);
                        //成功的日志
                        $admin_log['money'] += $user_bonus_info['money'];

                        //发送短信通知 如果成功 则短信通知//
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

                        file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_deal_bonus.log', "POST:[" .$user_info['user_name']."的红包发放成功".json_encode($user_bonus_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }else{
                        $admin_log['err_money'] += $user_bonus_info['money'];
                        $err_load_repay_id[]= $user_bonus_info['id'];
                        //更新数据库失败
                        $data_err = true;
                        file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_deal_bonus.log', "POST:[" .$user_info['user_name']."的红包发放失败,富友转账成功，数据库更新失败".json_encode($user_bonus_info) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }
                } else {
                    $admin_log['err_money'] += $user_bonus_info['money'];
                    $err_load_repay_id[]= $user_bonus_info['id'];
                    //划拨失败
                    $err_money += $user_bonus_info['money'];
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_user_deal_bonus.log', "[红包放款富友转账失败，失败的user_bonus红包记录的ID为" . $user_bonus_info['id'] . "，投标人ID为" . $user_info['user_id'] . ",投标人用户名为" . $user_info['user_name'] . ",失败金额为" . $user_bonus_info['money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
            }
            //如果红包放款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理员//
            if($admin_log['err_money']){
                $msg = date("Y-m-d",$result["list"][0]["release_date"])."的红包放款数据存在异常，请您确认。";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_user_deal_bonus.log', $msg."异常信息：【未成功放款的红包ID：".json_encode($err_load_repay_id)."】；【未成功金额：".$admin_log['err_money']."】", FILE_APPEND);
                info_admin($msg,"红包放款");
            }

            if($err_money == 0){
                $return["info"] = "红包放款成功";
                if($data_err){
                   $return["info"] = "红包放款成功,但是有部分用户平台数据更新失败";
                }
                $return["status"] = 1;
            }else{
                $return["status"] = 0;
                $return["info"] = "红包放款失败";
            }

            //管理员操作日志
            $admin_log['operate_type'] = 4;//操作类型 4代表红包放款
            $admin_log['load_repay_id'] = $load_repay_id ? implode(",", $load_repay_id) : '';
            $admin_log['err_load_repay_id'] = $err_load_repay_id ? implode(",", $err_load_repay_id) : '';
            $admin_log['operate_desc'] = "红包放款";
            $admin_log['operate_time'] = time();
            $admin_log['operate_date'] = date("Y-m-d");
            $admin_log['operate_ip'] = get_client_ip();
            $admin_log['remark'] = $return['info'];
            $return['admin_log'] = $admin_log;

            return $return;
        }

        public function saveBonusPwd($data){
            $adminInfo = $this->getAdminInfo();
            $data["id"] = $adminInfo['id'];

            $res = M("admin")->save($data);
            return $res;
        }

        //导出红包列表
        public function export_bonus($where,$orderBy,$limit = ''){
            $result = $this->getBonusList($where,$orderBy,$limit);

            //导出Excel红包表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();

            $user_bonus_lists = array();
            //数据格式化
            foreach($result["list"] as $key => $value){
                $value['bonus_type'] = getBonusTypeName($value['bonus_type']);

                if($value['status'] == 0){
                    $value['status'] = '未处理';
                }elseif($value['status'] == 1){
                    $value['status'] = '提现申请中';
                }elseif($value['status'] == 2){
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
                              ->setCellValue('B'.$num, "红包ID")
                              ->setCellValue('C'.$num, "用户名")
                              ->setCellValue('D'.$num, "红包金额")
                              ->setCellValue('E'.$num, "红包类型")
                              ->setCellValue('F'.$num, "红包状态")
                              ->setCellValue('G'.$num, "红包生成时间")
                              ->setCellValue('H'.$num, "申请提现时间")
                              ->setCellValue('I'.$num, "预计发放日期")
                              ->setCellValue('J'.$num, "审核状态")
                              ->setCellValue('K'.$num, "是否有效")
                              ->setCellValue('L'.$num, "备注");
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)
                             ->setCellValue('B'.$num, $value['id'])
                              ->setCellValue('C'.$num, $value['user_name']."（".$value['real_name']."）")
                              ->setCellValue('D'.$num, $value['money'])
                              ->setCellValue('E'.$num, $value['bonus_type'])
                              ->setCellValue('F'.$num, $value['status'])
                              ->setCellValue('G'.$num,$value['generation_time'])
                              ->setCellValue('H'.$num, $value['apply_time'])
                              ->setCellValue('I'.$num, $value['release_date'])
                              ->setCellValue('J'.$num, $value['verify_status'])
                              ->setCellValue('K'.$num, $value['is_effect'])
                              ->setCellValue('L'.$num, $value['remark']);

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
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(35);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:L1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:L1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $filename = app_conf("SHOP_TITLE") . "红包奖励记录表";
            php_export_excel($objPHPExcel,$filename);
        }
}
?>