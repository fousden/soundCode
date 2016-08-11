<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行 后台财务管理模块 业务逻辑处理类
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class FinanceModel extends CommonModel {

        protected $tableName = 'deal_load';

        //自动验证
        protected $_validate = array(
			  // array("username","require","用户名必须填写!")
		);

        //自动完成
        protected $_auto=array(
                       // array('reg_time','time',1,'function'), //注册时间
        );
        
        //获得还款计划信息
        public function getRemainRepayList($map,$order,$sort,$listRows = 100){            
            $Model=D('deal_load_repay');
            $repay_list = $Model->field('dlr.user_id')->table(DB_PREFIX.'deal_load_repay as dlr')->join(DB_PREFIX.'deal as d on d.id=dlr.deal_id')->where($map)->group("dlr.user_id,d.jiexi_time")->select();
            $repay_count = count($repay_list);
            //统计数据
            $p = new Page ($repay_count,$listRows);
            //分页查询数据
            $remain_repay_list = $Model->table(DB_PREFIX.'deal_load_repay as dlr')
                ->join(DB_PREFIX.'deal as d on d.id=dlr.deal_id')
                ->join(DB_PREFIX.'deal_load as dl on dl.id=dlr.load_id')
                ->field('dlr.id,dlr.user_id,d.jiexi_time,sum(dlr.repay_money) as total_repay_money,sum(dl.money) as total_money,sum(dl.money - dl.coupon_cash) as total_pricipal,sum(dl.coupon_cash) as total_coupon_cash,sum(dl.pure_interests) as total_pure_interests,sum(dl.coupon_interests) as total_coupon_interests,sum(dl.act_interests) as total_act_interests,sum(dlr.interest_money) as total_interest_money,dl.user_name')
                ->where($map)
                ->group("dlr.user_id,d.jiexi_time")
                ->order("d.jiexi_time ASC,`" . $order . "` " . $sort)
                ->limit($p->firstRow . ',' . $p->listRows)
                ->select();
            //分页跳转的时候保证查询条件
            foreach ( $map as $key => $val ) {
                if (! is_array ( $val )) {
                        $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                }
            }
            foreach($remain_repay_list as $key=>$val){
                $remain_repay_list[$key]["id"] = $key + 1;
            }
            //分页显示
            $page = $p->show();
            $return['page'] =$page;
            $return['nowPage'] =$p->nowPage;
            $return['remain_repay_list'] = $remain_repay_list;
            return $return;             
        }
        
        //获得标的信息
        public function getDealList($map,$orderby = '',$limit = '',$static = false){
            $deal_count = M('deal')->where($map)->order($orderby)->count();
            if(!$static){
                $deal_lists = M('deal')->where($map)->order($orderby)->limit($limit)->select();
            }else{
                //统计数据
                $deal_lists = M('deal')->field("id")->where($map)->order($orderby)->limit($limit)->select();
            }
            //格式化标的信息
            foreach($deal_lists as $key => $val){                
                $load_condition["is_auto"] = 0;
                $load_condition["contract_no"] = array("neq","''");
                $load_condition["deal_id"] = $val["id"];
                //获取该标的下 所有投资记录统计信息
                $deal_load_static = M("deal_load")->field("sum(money) as all_money,sum(money-coupon_cash) as all_capital,sum(coupon_cash) as all_coupon_cash,sum(pure_interests) as all_pure_interest,sum(act_interests) as all_active_interest,sum(coupon_interests)  as all_coupon_interest,sum(pure_interests+act_interests+coupon_interests)  as all_interest,sum(money+pure_interests+act_interests+coupon_interests)  as all_repay_money")->where($load_condition)->find();
                
                //该标的下所有已入金金额
                $has_capital = M("deal_load")->where(array("deal_id"=>$val["id"],"contract_no"=>array("neq","''"),"is_auto"=>0,"is_has_loans"=>1))->getField("sum(money-coupon_cash)");
                //该标的下已经还款的金额
                $already_repay_money = M("deal_load_repay")->where(array("deal_id"=>$val["id"],"has_repay"=>1))->getField("sum(repay_money)");
                $already_repay_money = $already_repay_money ? $already_repay_money : 0;
                //该标的下所有罚息
                $all_impose_money = M("deal_load_repay")->where(array("deal_id"=>$val["id"],"has_repay"=>1))->getField("sum(impose_money)");
                $all_impose_money = $all_impose_money ? $all_impose_money : 0;
                
                if(!$static){
                    //标的分类
                    $deal_lists[$key]['cate_name'] =  M("deal_cate")->where(array("id"=>$val['cate_id']))->getField("name");
                    //所有投标 投资人次
                    $loads_passengers = M("deal_load")->where(array("is_auto"=>0,"contract_no"=>array("neq",''),"deal_id"=>$val['id']))->getField("count(id)");
                    $deal_lists[$key]['loads_passengers'] = $loads_passengers ? $loads_passengers : 0;
                    //投资期限
                    $deal_lists[$key]['repay_time_name'] = $val['repay_time']."天";

                    //该标的下 投资总额（包括抵现券）
                    $deal_lists[$key]['deal_all_money'] = $deal_load_static['all_money'];
                    //该标的下 入金总额
                    $deal_lists[$key]['deal_all_capital'] = $deal_load_static['all_capital'];
                    //该标的下 已入金总额
                    $deal_lists[$key]['has_capital'] = $has_capital;
                    //该标的下 抵现券总额
                    $deal_lists[$key]['deal_all_coupon_cash'] = $deal_load_static['all_coupon_cash'];
                    //该标的下 纯利息总额
                    $deal_lists[$key]['deal_all_pure_interest'] = $deal_load_static['all_pure_interest'];
                    //该标的下 收益券收益总额
                    $deal_lists[$key]['deal_all_coupon_interest'] = $deal_load_static['all_coupon_interest'];
                    //该标的下 活动收益总额
                    $deal_lists[$key]['deal_all_active_interest'] = $deal_load_static['all_active_interest'];
                    //该标的下 总收益总额
                    $deal_lists[$key]['deal_all_interest'] = $deal_load_static['all_interest'];
                    //罚息
                    $deal_lists[$key]['deal_all_impose_money'] = $all_impose_money;
                    //该标的下 应还本息+罚息 罚息可为负值
                    $deal_lists[$key]['deal_all_repay_money'] = $deal_load_static['all_repay_money'] + $all_impose_money;
                    //该标的下 已还本息
                    $deal_lists[$key]['already_repay_money'] = $already_repay_money;
                }
                //该日期下所有标的 标的总额
                $return['date_borrow_amount'] += $val['borrow_amount'];
                //该日期下所有标的 投资总额（包括抵现券）
                $return['date_all_money'] += $deal_load_static['all_money'];
                //该日期下所有标的 入金总额 （已放款金额 + 未放款金额）
                $return['date_all_capital'] += $deal_load_static['all_capital'];
                //已放款金额
                $return['has_capital'] += $has_capital;
                //未放款金额
                $return['remain_capital'] += $deal_load_static['all_capital'] - $has_capital;
                //该日期下所有标的 抵现券总额
                $return['date_all_coupon_cash'] += $deal_load_static['all_coupon_cash'];
                //该日期下所有标的 纯利息总额
                $return['date_all_pure_interest'] += $deal_load_static['all_pure_interest'];
                //该日期下所有标的 收益券收益总额
                $return['date_all_coupon_interest'] += $deal_load_static['all_coupon_interest'];
                //该日期下所有标的 活动收益总额
                $return['date_all_active_interest'] += $deal_load_static['all_active_interest'];
                //该日期下所有标的 总收益总额
                $return['date_all_interest'] += $deal_load_static['all_interest'];
                //该日期下所有标的 应还本息 （已经还款金额 + 剩余应还金额）
                $return['date_all_repay_money'] += $deal_load_static['all_repay_money'];
                //该日期下所有标的 已经还款金额
                $return['date_all_has_repay_money'] += $already_repay_money;
                //该日期下所有标的 剩余应还金额
                $return['date_all_remain_repay_money'] += $deal_load_static['all_repay_money'] - $already_repay_money;
            }
            //是否统计数据
            if(!$static){
                $left_arr = $center_arr = $right_arr = array();
                foreach($deal_lists as $val){
                    if($val['verify_status'] == 0){
                        $left_arr[] = $val;
                    }else if($val['verify_status'] == 1){
                        $center_arr[] = $val;
                    }else{
                        $right_arr[] = $val;
                    }
                }
                $deal_lists = array_merge($left_arr,$center_arr,$right_arr);

                $return['deal_lists'] = $deal_lists;
            }
            $return['deal_count'] = $deal_count;
            return $return;
        }

        //获取标的 投标记录
        public function get_deal_load_list($deal_id,$where = [], $limit = ''){
             $where["is_auto"] = 0;
             $where["contract_no"] = array("neq","''");
             $where["deal_id"] = $deal_id;
             $deal_load_count = M("deal_load")->where($where)->getField("count(id)");
             //标的下投资记录总数
             $return['load_count'] = $deal_load_count ? $deal_load_count : 0;
             //所有投资记录
             $return['load_list'] = M("deal_load")->where($where)->limit($limit)->select();
             //格式化数据
             foreach ($return['load_list'] as $k => $v) {
                $return['load_list'][$k]['act_invest_money'] = $v['money'] - $v['coupon_cash'];
                $return['load_list'][$k]['all_interest'] = $v['pure_interests'] + $v['act_interests'] + $v['coupon_interests'];
                $return['load_list'][$k]['all_repay_money'] = $v['money'] + $v['pure_interests'] + $v['act_interests'] + $v['coupon_interests'];
                //投资记录还款状态
                $return['load_list'][$k]['repay_info'] = M("deal_load_repay")->field("has_repay")->where(array("load_id"=>$v['id']))->find();
                //标的用户信息
                $return['load_list'][$k]['user_info'] = M("user")->field("user_name,real_name,mobile")->find($v["user_id"]);
                //还款信息
                $return['load_list'][$k]['deal_info'] = M("deal")->field("jiexi_time,rate")->find($deal_id);
             }
             return $return;
        }

        //公用的函数库
        public function init_deals($map,$dMonth,$type='qixi_time'){
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
                    $map[$type] = $v;
                    //排序
                    $orderBy ="create_time desc";
                    $return = $this->getDealList($map,$orderBy,'',true);
                    $new_calendar[$key][$k]['week_day'] = $v;
                    $new_calendar[$key][$k]['deal_load_count'] = $return['deal_count'];
                    //某个日期下 应还本息
                    $new_calendar[$key][$k]['date_all_capital'] = $return['date_all_capital'];
                    $new_calendar[$key][$k]['date_all_coupon_cash'] = $return['date_all_coupon_cash'];
                    $new_calendar[$key][$k]['date_all_pure_interest'] = $return['date_all_pure_interest'];
                    $new_calendar[$key][$k]['date_all_coupon_interest'] = $return['date_all_coupon_interest'];
                    $new_calendar[$key][$k]['date_all_active_interest'] = $return['date_all_active_interest'];
                    $new_calendar[$key][$k]['date_all_interest'] = $return['date_all_interest'];
                    $new_calendar[$key][$k]['date_all_repay_money'] = $return['date_all_repay_money'];
                    $new_calendar[$key][$k]['date_all_has_repay_money'] = $return['date_all_has_repay_money'];
                    $new_calendar[$key][$k]['date_all_remain_repay_money'] = $return['date_all_remain_repay_money'];
                    //剩余入金总额
                    $new_calendar[$key][$k]['remain_capital'] = $return['remain_capital'];
                }
            }
            $result['new_calendar'] = $new_calendar;
            return $result;
        }

        //更新表信息
        public function updateModel($table,$data){
            //更新数据信息
            $up_id = M($table)->save($data);
            return $up_id;
        }

        //更新当前管理员密码信息
        function saveAdmin($data){
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_id = intval($adm_session['adm_id']);
            //置空重置
            $condition[key($data)] = '';
            $condition['id'] = $adm_id;
            M('admin')->save($condition);

            $data['id'] = $adm_id;
            $up_id = M('admin')->save($data);
            return $up_id;
        }

        //验证资金池账户余额
        function checkFuyouBalance($colum_name,$id){
            $data = $this->getAccount();
            $sql = "SELECT  sum(repay_money) as repay_money_all FROM " . DB_PREFIX . "deal_load_repay where has_repay = 0 AND ".$colum_name."='" . $id ."'";
            $list = $this->query($sql);
            if($data['ca_balance'] <= 0 || ($data['ca_balance'] > 0 && $data['ca_balance'] <  $list[0]['repay_money_all'])){
                return false;
            }else{
                return true;
            }
        }

        //检测总账
        function checkJxchAccount($ca_balance,$trade_money,$deal_id,$title){
            //入金前后对总账
            $cash_data = $this->getAccount();
            //交易后金额
            $now_ca_balance = (string)($cash_data['ca_balance'] * 100);
            //交易前金额
            $ca_balance = $ca_balance ? ($ca_balance * 100) : 0;
            //交易金额
            $trade_money = $trade_money ? ($trade_money * 100) : 0;
            if($title == "do_loans"){
                $now_account = (string)($ca_balance + $trade_money);
                $title_desc = "入金";
            }else if($title == "repay"){
                $now_account = (string)($ca_balance - $trade_money);
                $title_desc = "还款";
            }
            $remain_money = $now_account - $now_ca_balance;
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_checkJxchAccount.log', "标的或单个投资记录ID：[".$deal_id."];".$title_desc."前账面金额：[" . ($ca_balance/100) . "];交易金额:[".($trade_money/100)."];预计交易后账面金额：[".($now_account/100)."];实际".$title_desc."后账面金额:[".($now_ca_balance/100)."];[差额(交易后减交易前)：".($remain_money/100)."][" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            if($now_account != $now_ca_balance){
                //异常通知
                $contents['title'] = $email_contents['title'] = date("Y-m-d").$title_desc."前后检测总账是否一致预警";
                $contents['msg'] = date("Y-m-d")."标的或单个投资记录ID：".$deal_id."的".$title_desc."前后总账不一致，差额为".($remain_money/100)."元";
                $email_contents['msg'] = "【".$title_desc."后检测总账信息如下：】【标的或单个投资记录ID：".$deal_id."】【交易前账面金额：".($ca_balance/100)."元】" ."【交易金额：".($trade_money/100)."元】" . "【预计交易后账面金额：".($now_account/100)."元】【实际交易后账面金额：".($now_ca_balance/100)."元】【差额(交易后减交易前)：".($remain_money/100)."】【" . date('Y-m-d H:i:s') . "】<br/>";
                //系统管理员
                adnormal_warning($contents,$email_contents,INFO_USER,INFO_EMAIL_USER);
            }
        }

        //导出excel 入金 还款计划
        function do_export_load($data){
            $sql = "";
            //导出入金表
            $sql .= "select dl.*,d.name,d.cate_id,d.enddate,d.last_mback_time,d.rate,d.deal_status,u.real_name,d.yield_ratio,d.repay_time,d.qixi_time,d.jiexi_time  from ".DB_PREFIX."deal_load dl left join ".DB_PREFIX."deal d on dl.deal_id = d.id left join ".DB_PREFIX."user u on dl.user_id = u.id where d.deal_status in (2,4,5) AND dl.is_auto = 0 AND dl.contract_no != '' and d.".$data['type']."_time = '".$data['deal_time']."'";
            if($data['id']){
                require_once(APP_ROOT_PATH . "app/Lib/common.php");
                require_once(APP_ROOT_PATH . "app/Lib/deal.php");
                $deal_info = get_deal($data['id'], 0);
                $sql .= " AND dl.deal_id = ".$data['id'];
            }else{
                $deal_info['name'] = "所有满标标的";
            }
            $deal_load_list = $this->query($sql);
            //导出入金表
            $this->export_cash_coming($deal_load_list,$deal_info['name']);
        }

        /**
        * 满标放款
        * $type 0 普通 1代表 第三方
        * $is_loan 0 不返款， 1 返款
        */
       public function doLoans($id, $repay_start_time, $type = 0,$loans_pic='') {
            //引入公共文件
            require_once APP_ROOT_PATH . 'system/libs/user.php';
            require_once APP_ROOT_PATH . 'system/common.php';
            require_once(APP_ROOT_PATH . "app/Lib/common.php");
           $return = array("status" => 0, "info" => "");
           if ($id == 0) {
               $return['info'] = "入金放款失败，借款不存在";
               return $return;
           }
           require_once(APP_ROOT_PATH . "app/Lib/deal.php");
           $deal_info = get_deal($id);
           if (!$deal_info) {
               $return['info'] = "入金放款失败，借款不存在";
               return $return;
           }
           if ($deal_info['deal_status'] != 2) {
               $return['info'] = "入金放款失败，借款不是满标状态";
               return $return;
           }
           if ($type == 0) {
               $loan_data['repay_start_time'] = $repay_start_time == '' ? 0 : to_timespan(to_date(to_timespan($repay_start_time), "Y-m-d"), "Y-m-d");
           } else {
               $loan_data['repay_start_time'] = $repay_start_time;
           }
           if ($loan_data['repay_start_time'] == 0) {
               $return['info'] = "入金放款失败，时间没选择";
               return $return;
           }
           if ($type == 0 && $deal_info['ips_bill_no'] != "") {
               $return['status'] = 2;
               $return['info'] = "";
               $return['jump'] = APP_ROOT . "/index.php?ctl=collocation&act=Transfer&pTransferType=1&deal_id=" . $id . "&ref_data=" . $loan_data['repay_start_time'];
               return $return;
           }
           if ($loan_data['repay_start_time'] > 0) {
               $deal_info['next_repay_time'] = $loan_data['next_repay_time'] = next_replay_month($loan_data['repay_start_time']);
           }
           $deal_info['deal_status'] = $loan_data['deal_status'] = 4;
           $deal_info['is_has_loans'] = $loan_data['is_has_loans'] = 1;
           $loan_data['repay_start_date'] = to_date($loan_data['repay_start_time'], "Y-m-d");
           $deal_info['repay_start_time'] = $loan_data['repay_start_time'];
           //去除自动投标金额
           $auto_money_arr = $GLOBALS['db']->getAll("SELECT money FROM " . DB_PREFIX . "deal_load where deal_id=" . $id . " AND is_auto = 1 AND is_has_loans = 0");
           $all_auto_money = 0;
           foreach ($auto_money_arr as $key => $val) {
               $all_auto_money += $val['money'];
           }
           //实际获得的借款金额
           $deal_info['load_money'] = $deal_info['load_money'] - $all_auto_money;
           format_deal_item($deal_info);
           require_once APP_ROOT_PATH . "system/libs/user.php";

           //放款失败的金额
           $fail_money = 0;
           $admin_log = array();
           //满标放款转账操作
           $flags = true;
           //管理员日志 数据
           $admin_log['deal_id'] = $id;

           $load_list = $GLOBALS['db']->getAll("SELECT id,user_id,user_name,`money`,coupon_cash,`is_old_loan`,`rebate_money`,`bid_score`,`is_winning`,`income_type`,`income_value`,`contract_no`,`contract_no_flag` FROM " . DB_PREFIX . "deal_load where deal_id=" . $id . " and is_auto = 0 AND contract_no != '' AND contract_no_flag = 0 and is_rebate = 0  AND is_has_loans = 0");
           foreach ($load_list as $lk => $lv) {
                require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                $fuyou = new fuyou();
                $user_info = M("User")->getById($lv['user_id']);

                //管理员日志 数据
                $load_repay_id[]= $lv['id'];

                 //看是否使用了抵用券
                 if($lv['coupon_cash'] > 0 && $lv['coupon_cash'] <= $lv['money']){
                     $lv['money'] -= $lv['coupon_cash'];
                 }

                $arr = $fuyou->transferBuAction($user_info['fuiou_account'],FUYOU_MCHNT_FR,$lv['money'],$lv['contract_no'],$lv['id']);//富友账户 划拨
                if ('0000' == $arr->plain->resp_code) {
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", array('contract_no_flag' => 1), "UPDATE", ' `id` ="' . $lv['id'] . '"  ');
                    $tmp = $GLOBALS['db']->affected_rows();
                    //扣除冻结资金
                    if ($type == 0) {
                        $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "deal_load SET is_has_loans =1 WHERE id=" . $lv['id'] . " AND is_has_loans = 0 AND user_id=" . $lv['user_id']);
                        if ($GLOBALS['db']->affected_rows()) {
                             if ($lv['is_old_loan'] == 0) {
                                modify_account(array("lock_money" => -$lv['money']), $lv['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],投标成功", 2);
                                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBu.log', "[满标放款富友转账成功，成功的deal_load投标记录的ID为" . $lv['id'] . "，投标人ID为" . $lv['user_id'] . ",投标人用户名为" . $lv['user_name'] . ",成功金额为" . $lv['money'] . "，抵现券面值".$lv['coupon_cash']."];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                             }
                        }
                    }
                    if (!$tmp) {
                        //更新投标记录状态如果失败 可以尝试再次更新  或者将投标记录设为 is_auto = 1
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", array('contract_no_flag' => 1), "UPDATE", ' `id` ="' . $lv['id'] . '"  ');
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load", array('is_auto' => 1), "UPDATE", ' `id` ="' . $lv['id'] . '"  ');
                        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_update_deal_load.log', "UPDATE:[" . json_encode(array('contract_no_flag' => 1)) . "];return:[满标放款富友转账成功，但是金享财行更新投资列表数据失败！" . $tmp . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }
                    $admin_log['money'] += $lv['money'];
                    $flags = true;
                } else {
                    $flags = false;
                    $fail_money += $lv['money'];
                    $admin_log['err_money'] += $lv['money'];
                    $err_load_repay_id[]= $lv['id'];
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBu.log', "[满标放款富友转账失败，失败的deal_load投标记录的ID为" . $lv['id'] . "，投标人ID为" . $lv['user_id'] . ",投标人用户名为" . $lv['user_name'] . ",失败金额为" . $lv['money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }

                if ($flags) {
                   //返利给用户
                   if (floatval($lv["rebate_money"]) != 0 || intval($lv["bid_score"]) != 0) {
                       $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "deal_load SET is_rebate =1 WHERE id=" . $lv['id'] . " AND is_rebate = 0 AND user_id=" . $lv['user_id']);
                       if ($GLOBALS['db']->affected_rows()) {
                           //返利
                           if (floatval($lv["rebate_money"]) != 0) {
                               modify_account(array("money" => $lv['rebate_money']), $lv['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],投资返利", 24);
                           }
                           //积分
                           if (intval($lv["bid_score"]) != 0) {
                               modify_account(array("score" => $lv['bid_score']), $lv['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],投资返积分", 2);
                           }
                           //VIP奖励
                           if (((int) $lv['income_type'] == 1 || (int) $lv['income_type'] == 2 || (int) $lv['income_type'] == 3 || (int) $lv['income_type'] == 4) && $lv['is_winning'] == 1) {
                               $user_msg_conf = get_user_msg_conf($lv['user_id']);
                               //发放奖励
                               if ($lv['income_type'] == 1) {
                                   //红包记录 增加用户金额与不可提现金额
                                   $red_envelope_date['user_id'] = $lv['user_id'];
                                   $red_envelope_date['deal_id'] = $id;
                                   $red_envelope_date['load_id'] = $lv['id'];
                                   $red_envelope_date['reward_name'] = "投标收益奖励";
                                   $red_envelope_date['gift_type'] = 1;
                                   $redmoney = $GLOBALS['db']->getOne("SELECT money FROM " . DB_PREFIX . "vip_red_envelope WHERE id='" . (int) $lv['income_value'] . "'");
                                   $red_envelope_date['gift_value'] = $redmoney;
                                   $red_envelope_date['status'] = 1;
                                   $red_envelope_date['generation_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $red_envelope_date['release_date'] = to_date(TIME_UTC, "Y-m-d");

                                   $is_send_mail = $user_msg_conf['mail_redenvelope'];
                                   $is_send_sms = $user_msg_conf['sms_redenvelope'];
                                   $TPL_MAIL_NAME = "TPL_MAIL_RED_ENVELOPE";
                                   $TPL_SMS_NAME = "TPL_SMS_RED_ENVELOPE";
                                   $gift_value = $redmoney;

                                   if ($redmoney != 0 && $redmoney != "") {
                                       $GLOBALS['db']->autoExecute(DB_PREFIX . "gift_record", $red_envelope_date); //插入
                                       modify_account(array('money' => $redmoney, 'nmc_amount' => $redmoney), $lv['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],的投标收益奖励  红包现金", 28);
                                   }
                               } elseif ($lv['income_type'] == 2) {
                                   //收益率
                                   $rate_date['user_id'] = $lv['user_id'];
                                   $rate_date['deal_id'] = $id;
                                   $rate_date['load_id'] = $lv['id'];
                                   $rate_date['reward_name'] = "投标收益奖励";
                                   $rate_date['gift_type'] = 2;
                                   $loadinfo = $GLOBALS['db']->getRow("SELECT * FROM " . DB_PREFIX . "deal_load_repay WHERE load_id='" . $lv['id'] . "'");
                                   $interest_money = $loadinfo['repay_money'] - $loadinfo['self_money'];
                                   $gift_value = $interest_money * (float) $lv['income_value'] * 0.01;
                                   $rate_date['reward_money'] = $gift_value;
                                   $rate_date['gift_value'] = $lv['income_value'];
                                   $rate_date['status'] = 1;
                                   $rate_date['generation_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $rate_date['release_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $GLOBALS['db']->autoExecute(DB_PREFIX . "gift_record", $rate_date); //插入
                               } elseif ($lv['income_type'] == 3) {
                                   //积分
                                   $score = (int) $lv['income_value'];
                                   $score_date['user_id'] = $lv['user_id'];
                                   $score_date['deal_id'] = $id;
                                   $score_date['load_id'] = $lv['id'];
                                   $score_date['reward_name'] = "投标收益奖励";
                                   $score_date['gift_type'] = 3;
                                   $score_date['gift_value'] = (int) $lv['income_value'];
                                   $score_date['status'] = 1;
                                   $score_date['generation_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $score_date['release_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $GLOBALS['db']->autoExecute(DB_PREFIX . "gift_record", $score_date); //插入

                                   $is_send_mail = $user_msg_conf['mail_integral'];
                                   $is_send_sms = $user_msg_conf['sms_integral'];
                                   $TPL_MAIL_NAME = "TPL_MAIL_INTEGRAL";
                                   $TPL_SMS_NAME = "TPL_SMS_INTEGRAL";
                                   $gift_value = (int) $lv['income_value'];

                                   if ($score != 0) {
                                       modify_account(array("score" => $score), $lv['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],的投标收益奖励 积分", 28);
                                   }
                               } elseif ($lv['income_type'] == 4) {
                                   //礼品记录
                                   $gift_date['user_id'] = $lv['user_id'];
                                   $gift_date['deal_id'] = $id;
                                   $gift_date['load_id'] = $lv['id'];
                                   $gift_date['reward_name'] = "投标收益奖励";
                                   $gift_date['gift_type'] = 4;
                                   $gift_date['gift_value'] = (int) $lv['income_value'];
                                   $gift_date['status'] = 1;
                                   $gift_date['generation_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $gift_date['release_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $GLOBALS['db']->autoExecute(DB_PREFIX . "gift_record", $gift_date); //插入

                                   $is_send_mail = $user_msg_conf['mail_gift'];
                                   $is_send_sms = $user_msg_conf['sms_gift'];
                                   $TPL_MAIL_NAME = "TPL_MAIL_GIFT";
                                   $TPL_SMS_NAME = "TPL_SMS_GIFT";

                                   $gift_value = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "vip_gift where id = " . $gift_date['gift_value']);
                               }
                               $user_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where id = " . $lv['user_id']);
                               $deal_name = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "deal where id = " . $id);
                               //邮件
                               if ($is_send_mail == 1 && app_conf("MAIL_ON") == 1) {
                                   $tmpl = $GLOBALS['db']->getRowCached("select * from " . DB_PREFIX . "msg_template where name = '" . $TPL_MAIL_NAME . "'");
                                   $tmpl_content = $tmpl['content'];


                                   $notice['user_name'] = $user_info['user_name'];
                                   $notice['deal_name'] = $deal_name;
                                   $notice['release_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $notice['site_name'] = app_conf("SHOP_TITLE");
                                   $notice['gift_value'] = $gift_value;

                                   $GLOBALS['tmpl']->assign("notice", $notice);

                                   $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);
                                   $msg_data['dest'] = $user_info['email'];
                                   $msg_data['send_type'] = 1;
                                   $msg_data['title'] = "投标奖励邮件通知";
                                   $msg_data['content'] = addslashes($msg);
                                   $msg_data['send_time'] = 0;
                                   $msg_data['is_send'] = 0;
                                   $msg_data['create_time'] = TIME_UTC;
                                   $msg_data['user_id'] = $user_info['id'];
                                   $msg_data['is_html'] = $tmpl['is_html'];
                                   $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                               }
                               //短信
                               if ($is_send_sms == 1 && app_conf("SMS_ON") == 1) {
                                   $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = '" . $TPL_SMS_NAME . "'");
                                   $tmpl_content = $tmpl['content'];

                                   $notice['user_name'] = $user_info['user_name'];
                                   $notice['deal_name'] = $deal_name;
                                   $notice['release_date'] = to_date(TIME_UTC, "Y-m-d");
                                   $notice['site_name'] = app_conf("SHOP_TITLE");
                                   $notice['gift_value'] = $gift_value;

                                   $GLOBALS['tmpl']->assign("notice", $notice);

                                   $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);

                                   $msg_data['dest'] = $user_info['mobile'];
                                   $msg_data['send_type'] = 0;
                                   $msg_data['title'] = "投标奖励短信通知";
                                   $msg_data['content'] = addslashes($msg);
                                   ;
                                   $msg_data['send_time'] = 0;
                                   $msg_data['is_send'] = 0;
                                   $msg_data['create_time'] = TIME_UTC;
                                   $msg_data['user_id'] = $user_info['id'];
                                   $msg_data['is_html'] = $tmpl['is_html'];
                                   $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                               }
                           }
                       }
                   }
               }
           }

           //放款给用户
           $GLOBALS['db']->autoExecute(DB_PREFIX . "deal", $loan_data, "UPDATE", "id=" . $id . " AND is_has_loans=0 ");
           if ($GLOBALS['db']->affected_rows() > 0) {
               if ($type == 0) {
                   $deal_info['borrow_amount'] = $deal_info['borrow_amount'] - $fail_money;
                   modify_account(array("money" => $deal_info['borrow_amount']), $deal_info['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],招标成功", 3);
               }
               //积分
               if ($deal_info['score'] != 0) {
                   modify_account(array("score" => $deal_info['score']), $deal_info['user_id'], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],招标成功", 3);
               }
               //$GLOBALS['db']->autoExecute(DB_PREFIX."deal_load",array("is_has_loans"=>1),"UPDATE","deal_id=".$id);
               make_repay_plan($deal_info);
               //发借款成功邮件
               send_deal_success_mail_sms($id, $deal_info);
               //发借款成功站内信
               send_deal_success_site_sms($id, $deal_info);

               //如果成功 初始化数据 投标 收益奖励
                $list = array();
                //更新凭证
                $loan_data = array();
                $loan_data['loans_pic'] = strim($loans_pic);

                //更新审核状态
                $loan_data['verify_status'] = 1;

                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal", $loan_data, "UPDATE", "id=" . $id);

                $sql_list = "select * from " . DB_PREFIX . "deal_load where deal_id='$id' ";
                $list = $GLOBALS['db']->getAll($sql_list);
                foreach ($list as $k => $v) {
                    //投标人 VIP升级
                    $tuser_id = $v['user_id'];
                    $ttype = 1;
                    $ttype_info = 2;
                    $tresultdate = syn_user_vip($tuser_id, $ttype, $ttype_info);
                }
                $this->get_manage($id);

                //VIP升级 -借款
                $user_id = M("Deal")->where("id='$id'")->getField("user_id");
                $type = 1;
                $type_info = 1;
                $resultdate = syn_user_vip($user_id, $type, $type_info);

                if ($fail_money > 0) {
                    $return['status'] = 1;
                    $return['info'] = "部分入金放款成功，未成功放款投标请查看失败日志信息！";
                    $admin_log['status'] = 2;
                    $fail_money = 0;
               } else {
                    $return['status'] = 1;
                    $return['info'] = "入金放款成功";
                    $admin_log['status'] = 1;
               }
           } else {
               $return['info'] = "放款失败";
               $admin_log['status'] = 0;
           }
            //如果满标放款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理元//
            if($admin_log['err_money']){
                $msg = "标的ID为".$admin_log['deal_id']."的".$deal_info['name']."满标放款数据存在异常，请您确认。";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_do_Loans_error.log', $msg."异常信息：【未成功放款的订单ID：".json_encode($err_load_repay_id)."】；【未成功金额：".$admin_log['err_money']."】", FILE_APPEND);
                info_admin($msg,"满标放款");
            }
            //管理员操作日志
            $admin_log['operate_type'] = 1;//操作类型 1代表入金
            $admin_log['load_repay_id'] = $load_repay_id ? implode(",", $load_repay_id) : '';
            $admin_log['err_load_repay_id'] = $err_load_repay_id ? implode(",", $err_load_repay_id) : '';
            $admin_log['operate_desc'] = "满标放款";
            $admin_log['operate_time'] = time();
            $admin_log['operate_date'] = date("Y-m-d");
            $admin_log['operate_ip'] = get_client_ip();
            $admin_log['remark'] = $return['info'];
            $return['admin_log'] = $admin_log;

            return $return;
       }

       //网站代还款
       public function do_site_repay($id,$l_key = 0,$repay_type = 1,$act_jiexi_time = ''){
            if ($id == 0) {
                $result['status'] = 0;
                $result['info'] = '数据错误';
                return $result;
            }
            require_once(APP_ROOT_PATH . "app/Lib/common.php");
            require_once(APP_ROOT_PATH . "app/Lib/deal.php");
            $deal_info = get_deal($id);

            if (!$deal_info) {
                $result['status'] = 0;
                $result['info'] = '借款不存在';
                return $result;
            }

            if ($deal_info['ips_bill_no'] != "") {
                $result['status'] = 0;
                $result['info'] = '第三方同步暂无法代还款';
                return $result;
            }

            $user_id = $deal_info['user_id'];

            require_once(APP_ROOT_PATH . "system/libs/user.php");
            //指定标的 还款计划
            $user_loan_list = get_deal_user_load_list($deal_info, 0, -1 , -1, 0, 0, 1,'');

            //管理员日志 数据
            $admin_log['deal_id'] = $id;

            //网站转账 实现在线自动还款
            foreach ($user_loan_list['item'] as $kk => $vv) {
                    if ($vv['has_repay'] == 0) {//借入者已还款，但是没打款到借出用户中心
                        //管理员日志 数据
                       $load_repay_id[]= $vv['id'];

                        $user_load_data = array();

                        $user_load_data['true_repay_time'] = TIME_UTC;
                        $user_load_data['true_repay_date'] = to_date(TIME_UTC);
                        $user_load_data['is_site_repay'] = 1;
                        $user_load_data['status'] = 0;

                        $user_load_data['true_repay_money'] = num_format($vv['month_repay_money']);
                        $user_load_data['true_self_money'] = num_format($vv['self_money']);
                        $user_load_data['true_interest_money'] = num_format($vv['interest_money']);
                        $user_load_data['true_manage_money'] = num_format($vv['manage_money']);
                        $user_load_data['true_manage_interest_money'] = num_format($vv['manage_interest_money']);
                        $user_load_data['true_repay_manage_money'] = num_format($vv['repay_manage_money']);
                        $user_load_data['true_manage_interest_money_rebate'] = num_format($vv['manage_interest_money_rebate']);
                        $user_load_data['impose_money'] = num_format($vv['impose_money']);
                        $user_load_data['repay_manage_impose_money'] = num_format($vv['repay_manage_impose_money']);
                        $user_load_data['true_reward_money'] = num_format($vv['reward_money']);
                        //1准时还款 2提前 3逾期
                        if($repay_type != 1){
                            $repay_capital = M("deal_load")->where(array("id"=>$vv["load_id"],"is_auto"=>0,"contract_no"=>array("neq","''")))->getField("money");//投资总金额
                            $repay_coupon_interests = M("deal_load")->where(array("id"=>$vv["load_id"],"is_auto"=>0,"contract_no"=>array("neq","''")))->getField("coupon_interests");//收益券收益总金额
                            $repay_act_interests = M("deal_load")->where(array("id"=>$vv["load_id"],"is_auto"=>0,"contract_no"=>array("neq","''")))->getField("act_interests");//活动收益总金额
                            //实际期限
                            $datetime1 = new DateTime($deal_info["qixi_time"]);  
                            $datetime2 = new DateTime($act_jiexi_time);  
                            $interval = $datetime1->diff($datetime2);
                            $act_repay_time = $interval->format("%d");
                            //预计回款总额
                            $total_money = num_format($repay_capital + (($deal_info['rate'] / 100 / 360) * $repay_capital * intval($act_repay_time)) + $repay_coupon_interests + $repay_act_interests);
                            //罚息
                            $user_load_data['impose_money'] = $total_money - $user_load_data['true_repay_money'];
                            //实际回款金额
                            $user_load_data['true_repay_money'] = $total_money;
                            //实际结息日期
                            $user_load_data['true_repay_date'] = $act_jiexi_time;
                        }
                        
                                                
                        if ($vv['status'] > 0){
                            $user_load_data['status'] = $vv['status'] - 1;
                        }
                        $user_load_data['has_repay'] = 1;

                        $in_user_id = $vv['user_id'];

                        $loan_user_info['user_name'] = $vv['t_user_name'];
                        $loan_user_info['email'] = $vv['t_email'];
                        $loan_user_info['mobile'] = $vv['mobile'];
                        $loan_user_info['fuiou_account'] = $vv['fuiou_account'];

                        //富友转账 还款
                        require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                        $fuyou = new fuyou();
                        //转账记录数据
                        $arr = $fuyou->transferBuAction(FUYOU_MCHNT_FR,$loan_user_info['fuiou_account'], $user_load_data['true_repay_money'],'',$vv['id']);
                        //转账成功
                        if ('0000' == $arr->plain->resp_code) {
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load_repay", $user_load_data, "UPDATE", "id=" . $vv['id'] . " AND has_repay = 0 ", "SILENT");
                            if ($GLOBALS['db']->affected_rows() > 0) {
                                //更新用户账户资金记录
                                $repay_flag = true;
                                $admin_log['money'] += $user_load_data['true_repay_money'];
                                modify_account(array("money" => $user_load_data['true_repay_money']), $in_user_id, "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($kk + 1) . "期,回报本息", 5);
                                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账成功，成功的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",成功金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                            }else{
                                $admin_log['err_money'] += $user_load_data['true_repay_money'];
                                $err_load_repay_id[]= $vv['id'];
                                $repay_flag = false;
                                //失败则还原账户金额
                                $fuyou->transferBuAction($loan_user_info['fuiou_account'], FUYOU_MCHNT_FR, $user_load_data['true_repay_money'],'',time());
                                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款更新还款计划记录失败，失败的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",失败金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                            }
                        } else {
                            $admin_log['err_money'] += $user_load_data['true_repay_money'];
                            $err_load_repay_id[]= $vv['id'];
                            //划拨失败
                            $repay_flag = false;
                            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账失败，失败的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",失败金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                        }
                        if($repay_flag){
                            $unext_loan = $user_loan_list[$vv['u_key']][$kk + 1];
                            if ($unext_loan) {
                                $notices['content'] = ",本笔投标的下个还款日为" . to_date($unext_loan['repay_day'], "Y年m月d日") . "，需还本息" . number_format($unext_loan['month_repay_money'], 2) . "元。";
                            } else {
                                $load_repay_rs = $GLOBALS['db']->getOne("SELECT (sum(true_interest_money) + sum(impose_money)) as shouyi,sum(impose_money) as total_impose_money FROM " . DB_PREFIX . "deal_load_repay WHERE deal_id=" . $deal_info['id'] . " AND user_id=" . $vv['user_id']);
                                $all_shouyi_money = number_format($load_repay_rs['shouyi'], 2);
                                $all_impose_money = number_format($load_repay_rs['total_impose_money'], 2);
                                $notices['content'] = ",本次投标共获得收益:" . $all_shouyi_money . "元,其中违约金为:" . $all_impose_money . "元,本次投标已回款完毕！";
                            }

                            if ($user_load_data['impose_money'] != 0 || $user_load_data['true_manage_money'] != 0 || $user_load_data['true_repay_money'] != 0) {
                                    //普通会员邀请返利
                                    get_referrals($vv['id']);
                                    $msg_conf = get_user_msg_conf($in_user_id);
                                    //短信通知
                                    if (app_conf("SMS_ON") == 1 && app_conf('SMS_REPAY_TOUSER_ON') == 1) {
                                        $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
                                        $tmpl_content = $tmpl['content'];

                                        $notice['user_name'] = $loan_user_info['user_name'];
                                        $notice['deal_name'] = $deal_info['sub_name'];
                                        $notice['deal_url'] = $deal_info['url'];
                                        $notice['site_name'] = app_conf("SHOP_TITLE");
                                        $notice['repay_money'] = number_format(($user_load_data['true_repay_money'] + $user_load_data['impose_money']), 2);
                                        if ($unext_loan) {
                                            $notice['need_next_repay'] = $unext_loan;
                                            $notice['next_repay_time'] = to_date($unext_loan['repay_day'], "Y年m月d日");
                                            $notice['next_repay_money'] = number_format($unext_loan['month_repay_money'], 2);
                                        } else {
                                            $notice['all_repay_money'] = $all_shouyi_money;
                                            $notice['impose_money'] = $all_impose_money;
                                        }

                                        $GLOBALS['tmpl']->assign("notice", $notice);
                                        $sms_content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);

                                        $msg_data['dest'] = $loan_user_info['mobile'];
                                        $msg_data['send_type'] = 0;
                                        $msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
                                        $msg_data['send_time'] = 0;
                                        $msg_data['is_send'] = 0;
                                        $msg_data['create_time'] = TIME_UTC;
                                        $msg_data['user_id'] = $in_user_id;
                                        $msg_data['is_html'] = 0;
                                        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                                    }

                                    //站内信
                                    $notices['shop_title'] = app_conf("SHOP_TITLE");
                                    $notices['url'] = "“<a href=\"" . $deal_info['url'] . "\">" . $deal_info['name'] . "</a>”";
                                    $notices['money'] = ($user_load_data['true_repay_money'] + $user_load_data['impose_money']);

                                    $tmpl_content = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_SITE_REPAY'", false);
                                    $GLOBALS['tmpl']->assign("notice", $notices);
                                    $content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content['content']);

                                    if ($msg_conf['sms_bidrepaid'] == 1){
                                        send_user_msg("", $content, 0, $in_user_id, TIME_UTC, 0, true, 9);
                                    }
                                    //邮件
                                    if ($msg_conf['mail_bidrepaid'] == 1 && app_conf('MAIL_ON') == 1) {

                                        $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'", false);
                                        $tmpl_content = $tmpl['content'];

                                        $notice['user_name'] = $loan_user_info['user_name'];
                                        $notice['deal_name'] = $deal_info['sub_name'];
                                        $notice['deal_url'] = $deal_info['url'];
                                        $notice['site_name'] = app_conf("SHOP_TITLE");
                                        $notice['site_url'] = SITE_DOMAIN . APP_ROOT;
                                        $notice['help_url'] = SITE_DOMAIN . url("index", "helpcenter");
                                        $notice['msg_cof_setting_url'] = SITE_DOMAIN . url("index", "uc_msg#setting");
                                        $notice['repay_money'] = number_format(($vv['month_repay_money'] + $vv['impose_money']), 2);
                                        if ($unext_loan) {
                                            $notice['need_next_repay'] = $unext_loan;
                                            $notice['next_repay_time'] = to_date($unext_loan['repay_day'], "Y年m月d日");
                                            $notice['next_repay_money'] = number_format($unext_loan['month_repay_money'], 2);
                                        } else {
                                            $notice['all_repay_money'] = $all_shouyi_money;
                                            $notice['impose_money'] = $all_impose_money;
                                        }

                                        $GLOBALS['tmpl']->assign("notice", $notice);

                                        $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);
                                        $msg_data['dest'] = $loan_user_info['email'];
                                        $msg_data['send_type'] = 1;
                                        $msg_data['title'] = "“" . $deal_info['name'] . "”回款通知";
                                        $msg_data['content'] = addslashes($msg);
                                        $msg_data['send_time'] = 0;
                                        $msg_data['is_send'] = 0;
                                        $msg_data['create_time'] = TIME_UTC;
                                        $msg_data['user_id'] = $in_user_id;
                                        $msg_data['is_html'] = $tmpl['is_html'];
                                        $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                                    }
                            }
                        }
                    }
            }

            $s_count = $GLOBALS['db']->getOne("SELECT count(*) FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $id . " AND has_repay = 0");
            //所有的都还完完成
            if ($s_count == 0) {
                $rs_sum = $GLOBALS['db']->getRow("SELECT sum(true_repay_money) as total_repay_money,sum(true_self_money) as total_self_money,sum(true_interest_money) as total_interest_money,sum(true_repay_manage_money) as total_manage_money,sum(impose_money) as total_impose_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $id . " AND has_repay = 1");

                $deal_load_list = get_deal_load_list($deal_info);

                //统计网站代还款
                $rs_site_sum = $GLOBALS['db']->getRow("SELECT sum(true_repay_money) as total_repay_money,sum(true_self_money) as total_self_money,sum(true_repay_manage_money) as total_manage_money,sum(impose_money) as total_impose_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $id . " AND is_site_repay=1 and has_repay = 1");

                $repay_data['status'] = (int) $GLOBALS['db']->getOne("SELECT `status` FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $id . " AND  has_repay = 1 AND is_site_repay=1  ORDER BY l_key DESC");
                $repay_data['true_repay_time'] = TIME_UTC;
                $repay_data['true_repay_date'] = to_date(TIME_UTC);
                $repay_data['has_repay'] = 1;
                $repay_data['impose_money'] = floatval($rs_sum['total_impose_money']);
                $repay_data['true_self_money'] = floatval($rs_sum['total_self_money']);
                $repay_data['true_repay_money'] = floatval($rs_sum['total_repay_money']);
                $repay_data['true_manage_money'] = floatval($rs_sum['total_manage_money']);
                $repay_data['true_interest_money'] = floatval($rs_sum['total_interest_money']);
                $repay_data['manage_impose_money'] = floatval($rs_sum['total_repay_manage_impose_money']);
                $rebate_rs = get_rebate_fee($user_id, "borrow");
                $repay_data['true_manage_money_rebate'] = $repay_data['true_manage_money'] * floatval($rebate_rs['rebate']) / 100;

                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_repay", $repay_data, "UPDATE", " deal_id=" . $id . " AND has_repay = 0 ");

                if ($rs_site_sum) {
                    $r_msg = "网站代还款";
                    if ($rs_site_sum['total_repay_money'] > 0) {
                        $r_msg .=",本息：" . format_price($rs_site_sum['total_repay_money']);
                    }
                    if ($rs_site_sum['total_impose_money'] > 0) {
                        $r_msg .=",逾期费用：" . format_price($rs_site_sum['total_impose_money']);
                    }
                    if ($rs_site_sum['total_manage_money'] > 0) {
                        $r_msg .=",管理费：" . format_price($rs_site_sum['total_manage_money']);
                    }
                    if ($rs_site_sum['total_repay_manage_impose_money'] > 0) {
                        $r_msg .=",逾期管理费：" . format_price($rs_site_sum['total_repay_manage_impose_money']);
                    }
                    repay_log($deal_load_list[$l_key]['repay_id'], $r_msg, 0, $adm_session['adm_id']);
                }

                if ($GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "generation_repay WHERE deal_id=" . $id . " AND repay_id=" . $deal_load_list[$l_key]['repay_id'] . "") == 0) {
                    $generation_repay['deal_id'] = $id;
                    $generation_repay['repay_id'] = $deal_load_list[$l_key]['repay_id'];

                    $generation_repay['admin_id'] = $adm_session['adm_id'];
                    $generation_repay['agency_id'] = $deal_info['agency_id'];
                    $generation_repay['repay_money'] = $rs_site_sum['total_repay_money'];
                    $generation_repay['self_money'] = $rs_site_sum['total_self_money'];
                    $generation_repay['impose_money'] = $rs_site_sum['total_impose_money'];
                    $generation_repay['manage_money'] = $rs_site_sum['total_manage_money'];
                    $generation_repay['manage_impose_money'] = $rs_site_sum['total_repay_manage_impose_money'];
                    $generation_repay['create_time'] = TIME_UTC;
                    $generation_repay['create_date'] = to_date(TIME_UTC, "Y-m-d");

                    $GLOBALS['db']->autoExecute(DB_PREFIX . "generation_repay", $generation_repay);

                    $site_money_data['user_id'] = $user_id;
                    $site_money_data['create_time'] = TIME_UTC;
                    $site_money_data['create_time_ymd'] = to_date(TIME_UTC, "Y-m-d");
                    $site_money_data['create_time_ym'] = to_date(TIME_UTC, "Ym");
                    $site_money_data['create_time_y'] = to_date(TIME_UTC, "Y");
                    if ($rs_sum['total_manage_money'] != 0) {
                        $site_money_data['memo'] = "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($l_key) . "期,借款管理费";
                        $site_money_data['type'] = 10;
                        $site_money_data['money'] = $rs_sum['total_manage_money'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "site_money_log", $site_money_data, "INSERT");
                    }
                    if ($rs_sum['total_repay_manage_impose_money'] != 0) {
                        $site_money_data['memo'] = "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($l_key) . "期,逾期管理费";
                        $site_money_data['type'] = 12;
                        $site_money_data['money'] = $rs_sum['total_repay_manage_impose_money'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "site_money_log", $site_money_data, "INSERT");
                    }
                }
                syn_deal_status($deal_info['id']);
                syn_transfer_status(0, $deal_info['id']);
                $result['status'] = 1;
                $result['info'] = '网站代还款执行完毕！';
                $admin_log['status'] = 1;
            }else{
                $result['status'] = 0;
                $result['info'] = '网站代还款没有全部完成,可以再次确认还款！';
                $admin_log['status'] = 2;
            }
            //如果网站还款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理员//
            if($admin_log['err_money']){
                $msg = "标的ID为".$admin_log['deal_id']."的".$deal_info['name']."网站还款数据存在异常，请您确认。";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_do_site_repay_error.log', $msg."异常信息：【未成功还款的还款计划ID：".json_encode($err_load_repay_id)."】；【未成功金额：".$admin_log['err_money']."】", FILE_APPEND);
                info_admin($msg,"网站还款");
            }
            //管理员操作日志
            $admin_log['operate_type'] = 3;//操作类型 3代表网站还款
            $admin_log['load_repay_id'] = $load_repay_id ? implode(",", $load_repay_id) : '';
            $admin_log['err_load_repay_id'] = $err_load_repay_id ? implode(",", $err_load_repay_id) : '';
            $admin_log['operate_desc'] = "网站代还款";
            $admin_log['operate_time'] = time();
            $admin_log['operate_date'] = date("Y-m-d");
            $admin_log['operate_ip'] = get_client_ip();
            $admin_log['remark'] = $result['info'];
            $result['admin_log'] = $admin_log;

            return $result;
       }
       
       //网站代还款 按投资记录 单个还款
       public function do_load_repay($id,$l_key = 0){
            if ($id == 0) {//投资记录id
                $result['status'] = 0;
                $result['info'] = '数据错误';
                return $result;
            }
            require_once(APP_ROOT_PATH . "app/Lib/common.php");
            require_once(APP_ROOT_PATH . "app/Lib/deal.php");
            //获取该投资记录对应的还款计划
            $load_repay_info = M("deal_load_repay")->where(array("load_id"=>$id,"has_repay"=>0,"is_site_repay"=>0))->find();
            $deal_info = M("deal")->where(array("id"=>$load_repay_info["deal_id"],"is_effect"=>1,"is_delete"=>0))->find();
            $user_info = M("user")->where(array("id"=>$load_repay_info["user_id"],"is_effect"=>1,"is_delete"=>0))->find();
            //管理员日志 数据
            $admin_log['deal_id'] = $deal_info["id"];
            //单个投资记录 网站转账 实现在线自动还款
            if ($load_repay_info['has_repay'] == 0) {//借入者已还款，但是没打款到借出用户中心
                //管理员日志 数据
                $load_repay_id[]= $load_repay_info['id'];

                $user_load_data['true_repay_time'] = TIME_UTC;
                $user_load_data['true_repay_date'] = date("Y-m-d");
                $user_load_data['is_site_repay'] = 1;
                $user_load_data['status'] = 0;
                $user_load_data['true_repay_money'] = num_format($load_repay_info['repay_money']);
                $user_load_data['true_self_money'] = num_format($load_repay_info['self_money']);
                $user_load_data['true_interest_money'] = num_format($load_repay_info['interest_money']);
                $user_load_data['true_reward_money'] = num_format($load_repay_info['reward_money']);

                if ($load_repay_info['status'] > 0){
                    $user_load_data['status'] = $load_repay_info['status'] - 1;
                }
                $user_load_data['has_repay'] = 1;

                //富友转账 还款
                require_once APP_ROOT_PATH . "system/payment/fuyou.php";
                $fuyou = new fuyou();
                //转账记录数据
                $arr = $fuyou->transferBuAction(FUYOU_MCHNT_FR,$user_info['fuiou_account'], $user_load_data['true_repay_money'],'',$load_repay_info['id']);
                //转账成功
                if ('0000' == $arr->plain->resp_code) {
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_load_repay", $user_load_data, "UPDATE", "id=" . $load_repay_info['id'] . " AND has_repay = 0 ", "SILENT");
                    if ($GLOBALS['db']->affected_rows() > 0) {
                        //更新用户账户资金记录
                        $repay_flag = true;
                        $admin_log['money'] += $user_load_data['true_repay_money'];
                        require_once(APP_ROOT_PATH . "system/libs/user.php");
                        modify_account(array("money" => $user_load_data['true_repay_money']), $user_info["id"], "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($kk + 1) . "期,回报本息", 5);
                        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账成功，成功的deal_load_repay还款记录的ID为" . $load_repay_info['id'] . "，投标人ID为" . $user_info['user_id'] . ",投标人用户名为" . $user_info['user_name'] . ",成功金额为" . $load_repay_info['repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }else{
                        $admin_log['err_money'] += $user_load_data['true_repay_money'];
                        $err_load_repay_id[]= $load_repay_info['id'];
                        $repay_flag = false;
                        //失败则还原账户金额
                        $fuyou->transferBuAction($user_info['fuiou_account'], FUYOU_MCHNT_FR, $user_load_data['true_repay_money'],'',time());
                        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款更新还款计划记录失败，失败的deal_load_repay还款记录的ID为" . $load_repay_info['id'] . "，投标人ID为" . $user_info['user_id'] . ",投标人用户名为" . $user_info['user_name'] . ",失败金额为" . $load_repay_info['repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }
                } else {
                    $admin_log['err_money'] += $user_load_data['true_repay_money'];
                    $err_load_repay_id[]= $load_repay_info['id'];
                    //划拨失败
                    $repay_flag = false;
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账失败，失败的deal_load_repay还款记录的ID为" . $load_repay_info['id'] . "，投标人ID为" . $user_info['user_id'] . ",投标人用户名为" . $user_info['user_name'] . ",失败金额为" . $load_repay_info['repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
                if($repay_flag){
                    if ($user_load_data['true_repay_money'] != 0) {
                            //普通会员邀请返利
                            get_referrals($load_repay_info['id']);
                            $msg_conf = get_user_msg_conf($user_info['id']);
                            //短信通知
                            if (app_conf("SMS_ON") == 1 && app_conf('SMS_REPAY_TOUSER_ON') == 1) {
                                $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_DEAL_LOAD_REPAY_SMS'");
                                $tmpl_content = $tmpl['content'];
                                $notice['user_name'] = $user_info['user_name'];
                                $notice['deal_name'] = $deal_info['sub_name'];
                                $notice['deal_url'] = $deal_info['url'];
                                $notice['site_name'] = app_conf("SHOP_TITLE");
                                $notice['repay_money'] = number_format(($user_load_data['true_repay_money']), 2);
                                $notice['all_repay_money'] = $user_load_data['true_repay_money'];
                                $notice['impose_money'] = 0;
                                $GLOBALS['tmpl']->assign("notice", $notice);
                                $sms_content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);

                                $msg_data['dest'] = $user_info['mobile'];
                                $msg_data['send_type'] = 0;
                                $msg_data['title'] = $msg_data['content'] = addslashes($sms_content);
                                $msg_data['send_time'] = 0;
                                $msg_data['is_send'] = 0;
                                $msg_data['create_time'] = TIME_UTC;
                                $msg_data['user_id'] = $user_info['id'];
                                $msg_data['is_html'] = 0;
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                            }

                            //站内信
                            $notices['shop_title'] = app_conf("SHOP_TITLE");
                            $notices['url'] = "“<a href=\"" . $deal_info['url'] . "\">" . $deal_info['name'] . "</a>”";
                            $notices['money'] = ($user_load_data['true_repay_money'] + $user_load_data['impose_money']);

                            $tmpl_content = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_SITE_REPAY'", false);
                            $GLOBALS['tmpl']->assign("notice", $notices);
                            $content = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content['content']);

                            if ($msg_conf['sms_bidrepaid'] == 1){
                                send_user_msg("", $content, 0, $user_info['id'], TIME_UTC, 0, true, 9);
                            }
                            //邮件
                            if ($msg_conf['mail_bidrepaid'] == 1 && app_conf('MAIL_ON') == 1) {

                                $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = 'TPL_DEAL_LOAD_REPAY_EMAIL'", false);
                                $tmpl_content = $tmpl['content'];

                                $notice['user_name'] = $user_info['user_name'];
                                $notice['deal_name'] = $deal_info['sub_name'];
                                $notice['deal_url'] = $deal_info['url'];
                                $notice['site_name'] = app_conf("SHOP_TITLE");
                                $notice['site_url'] = SITE_DOMAIN . APP_ROOT;
                                $notice['help_url'] = SITE_DOMAIN . url("index", "helpcenter");
                                $notice['msg_cof_setting_url'] = SITE_DOMAIN . url("index", "uc_msg#setting");
                                $notice['repay_money'] = number_format(($load_repay_info['repay_money'] + $load_repay_info['impose_money']), 2);
                                $notice['all_repay_money'] = $user_load_data['true_repay_money'];
                                $notice['impose_money'] = 0;

                                $GLOBALS['tmpl']->assign("notice", $notice);

                                $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);
                                $msg_data['dest'] = $user_info['email'];
                                $msg_data['send_type'] = 1;
                                $msg_data['title'] = "“" . $deal_info['name'] . "”回款通知";
                                $msg_data['content'] = addslashes($msg);
                                $msg_data['send_time'] = 0;
                                $msg_data['is_send'] = 0;
                                $msg_data['create_time'] = TIME_UTC;
                                $msg_data['user_id'] = $user_info['id'];
                                $msg_data['is_html'] = $tmpl['is_html'];
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                            }
                    }
                }
            }
            $s_count = $GLOBALS['db']->getOne("SELECT count(*) FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $deal_info['id'] . " AND has_repay = 0");
            //所有的都还完完成
            if ($s_count == 0) {
                $rs_sum = $GLOBALS['db']->getRow("SELECT sum(true_repay_money) as total_repay_money,sum(true_self_money) as total_self_money,sum(true_interest_money) as total_interest_money,sum(true_repay_manage_money) as total_manage_money,sum(impose_money) as total_impose_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $deal_info['id'] . " AND has_repay = 1");

                $deal_load_list = get_deal_load_list($deal_info);

                //统计网站代还款
                $rs_site_sum = $GLOBALS['db']->getRow("SELECT sum(true_repay_money) as total_repay_money,sum(true_self_money) as total_self_money,sum(true_repay_manage_money) as total_manage_money,sum(impose_money) as total_impose_money,sum(repay_manage_impose_money) as total_repay_manage_impose_money FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $deal_info['id'] . " AND is_site_repay=1 and has_repay = 1");

                $repay_data['status'] = (int) $GLOBALS['db']->getOne("SELECT `status` FROM  " . DB_PREFIX . "deal_load_repay where deal_id=" . $deal_info['id'] . " AND  has_repay = 1 AND is_site_repay=1  ORDER BY l_key DESC");
                $repay_data['true_repay_time'] = TIME_UTC;
                $repay_data['true_repay_date'] = to_date(TIME_UTC);
                $repay_data['has_repay'] = 1;
                $repay_data['impose_money'] = floatval($rs_sum['total_impose_money']);
                $repay_data['true_self_money'] = floatval($rs_sum['total_self_money']);
                $repay_data['true_repay_money'] = floatval($rs_sum['total_repay_money']);
                $repay_data['true_manage_money'] = floatval($rs_sum['total_manage_money']);
                $repay_data['true_interest_money'] = floatval($rs_sum['total_interest_money']);
                $repay_data['manage_impose_money'] = floatval($rs_sum['total_repay_manage_impose_money']);
                $rebate_rs = get_rebate_fee($user_info["id"], "borrow");
                $repay_data['true_manage_money_rebate'] = $repay_data['true_manage_money'] * floatval($rebate_rs['rebate']) / 100;

                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_repay", $repay_data, "UPDATE", " deal_id=" . $deal_info['id'] . " AND has_repay = 0 ");

                if ($rs_site_sum) {
                    $r_msg = "网站代还款";
                    if ($rs_site_sum['total_repay_money'] > 0) {
                        $r_msg .=",本息：" . format_price($rs_site_sum['total_repay_money']);
                    }
                    if ($rs_site_sum['total_impose_money'] > 0) {
                        $r_msg .=",逾期费用：" . format_price($rs_site_sum['total_impose_money']);
                    }
                    if ($rs_site_sum['total_manage_money'] > 0) {
                        $r_msg .=",管理费：" . format_price($rs_site_sum['total_manage_money']);
                    }
                    if ($rs_site_sum['total_repay_manage_impose_money'] > 0) {
                        $r_msg .=",逾期管理费：" . format_price($rs_site_sum['total_repay_manage_impose_money']);
                    }
                    repay_log($deal_load_list[$l_key]['repay_id'], $r_msg, 0, $adm_session['adm_id']);
                }

                if ($GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "generation_repay WHERE deal_id=" . $deal_info['id'] . " AND repay_id=" . $deal_load_list[$l_key]['repay_id'] . "") == 0) {
                    $generation_repay['deal_id'] = $id;
                    $generation_repay['repay_id'] = $deal_load_list[$l_key]['repay_id'];

                    $generation_repay['admin_id'] = $adm_session['adm_id'];
                    $generation_repay['agency_id'] = $deal_info['agency_id'];
                    $generation_repay['repay_money'] = $rs_site_sum['total_repay_money'];
                    $generation_repay['self_money'] = $rs_site_sum['total_self_money'];
                    $generation_repay['impose_money'] = $rs_site_sum['total_impose_money'];
                    $generation_repay['manage_money'] = $rs_site_sum['total_manage_money'];
                    $generation_repay['manage_impose_money'] = $rs_site_sum['total_repay_manage_impose_money'];
                    $generation_repay['create_time'] = TIME_UTC;
                    $generation_repay['create_date'] = to_date(TIME_UTC, "Y-m-d");

                    $GLOBALS['db']->autoExecute(DB_PREFIX . "generation_repay", $generation_repay);

                    $site_money_data['user_id'] = $user_info["id"];
                    $site_money_data['create_time'] = TIME_UTC;
                    $site_money_data['create_time_ymd'] = to_date(TIME_UTC, "Y-m-d");
                    $site_money_data['create_time_ym'] = to_date(TIME_UTC, "Ym");
                    $site_money_data['create_time_y'] = to_date(TIME_UTC, "Y");
                    if ($rs_sum['total_manage_money'] != 0) {
                        $site_money_data['memo'] = "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($l_key) . "期,借款管理费";
                        $site_money_data['type'] = 10;
                        $site_money_data['money'] = $rs_sum['total_manage_money'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "site_money_log", $site_money_data, "INSERT");
                    }
                    if ($rs_sum['total_repay_manage_impose_money'] != 0) {
                        $site_money_data['memo'] = "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($l_key) . "期,逾期管理费";
                        $site_money_data['type'] = 12;
                        $site_money_data['money'] = $rs_sum['total_repay_manage_impose_money'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "site_money_log", $site_money_data, "INSERT");
                    }
                }
                syn_deal_status($deal_info['id']);
                syn_transfer_status(0, $deal_info['id']);
                /*$result['status'] = 1;
                $result['info'] = '网站代还款执行完毕！';
                $admin_log['status'] = 1;*/
            }
            
            $has_repay = M("deal_load_repay")->where(array("id"=>$load_repay_info["id"]))->getField("has_repay");
            if($has_repay == 1){
                $result['status'] = 1;
                $result['info'] = '该投资记录网站代还款成功！';
                $admin_log['status'] = 1;
            }else{
                $result['status'] = 0;
                $result['info'] = '该投资记录网站代还款失败！';
                $admin_log['status'] = 0;
            }
            
            //如果网站还款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理员//
            if($admin_log['err_money']){
                $msg = "标的ID为".$admin_log['deal_id']."的".$deal_info['name']."网站还款数据存在异常，请您确认。";
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_do_site_repay_error.log', $msg."异常信息：【未成功还款的还款计划ID：".json_encode($err_load_repay_id)."】；【未成功金额：".$admin_log['err_money']."】", FILE_APPEND);
                info_admin($msg,"单个投资记录网站还款");
            }
            //管理员操作日志
            $admin_log['operate_type'] = 6;//操作类型 6代表单个投资记录网站还款
            $admin_log['load_repay_id'] = $load_repay_id ? implode(",", $load_repay_id) : '';
            $admin_log['err_load_repay_id'] = $err_load_repay_id ? implode(",", $err_load_repay_id) : '';
            $admin_log['operate_desc'] = "单个投资记录网站代还款";
            $admin_log['operate_time'] = time();
            $admin_log['operate_date'] = date("Y-m-d");
            $admin_log['operate_ip'] = get_client_ip();
            $admin_log['remark'] = $result['info'];
            $result['admin_log'] = $admin_log;
 
            return $result;
       }

       //收取管理费
        private function get_manage($id) {
            //是否直接收取管理费
            if (intval($_REQUEST['get_manage']) == 1) {
                require_once(APP_ROOT_PATH . "system/libs/user.php");
                require_once(APP_ROOT_PATH . "system/common.php");
                $deal_name = $GLOBALS['db']->getOne("select name from " . DB_PREFIX . "deal where id='$id' ");
                $deal_repay = $GLOBALS['db']->getAll("select * from " . DB_PREFIX . "deal_repay where deal_id='$id' AND has_repay=0 ");
                if ($deal_repay) {
                    foreach ($deal_repay as $k => $v) {
                        if ($v['manage_money'] != 0 && $v['get_manage'] == 0) {
                            $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "deal_repay SET true_manage_money = manage_money,get_manage=1 WHERE id=" . $v['id']);
                            if ($GLOBALS['db']->affected_rows() > 0) {
                                modify_account(array("money" => -$v['manage_money']), $v['user_id'], "[<a href='" . url("index", "deal#index", array("id" => $v['deal_id'])) . "' target='_blank'>" . $deal_name . "</a>],第" . ($v['l_key'] + 1) . "期,借款管理费", 10);
                                $GLOBALS['db']->query("UPDATE " . DB_PREFIX . "deal_load_repay SET true_repay_manage_money = repay_manage_money WHERE repay_id=" . $v['id']);

                                $r_msg = "管理员放款收取";
                                if ($v['manage_money'] > 0) {
                                    $r_msg .=",管理费：" . format_price($v['manage_money']);
                                }

                                repay_log($v['id'], $r_msg, $v['user_id'], 0);
                            }
                        }
                    }
                }
            }
        }

        //导出入金表
        function export_cash_coming($deal_load_list,$deal_name = ''){
            //导出Excel入金表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();

            $deal_load_lists = array();
            foreach($deal_load_list as $key => $value){
                $deal_load_lists[$key+1] = $value;
            }
            $deal_load_lists[0] = array();
            ksort($deal_load_lists);
            /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
            foreach($deal_load_lists as $key => $value){
                $num=$key + 1;

                //标的状态
                if ($value['deal_status'] == 2) {
                    $value['deal_status'] = '满标';
                } elseif ($value['deal_status'] == 3) {
                    $value['deal_status'] = '流标';
                } elseif ($value['deal_status'] == 4) {
                    $value['deal_status'] = '还款中';
                } elseif ($value['deal_status'] == 5) {
                    $value['deal_status'] = '已还清';
                }
                if($key == 0){
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, '编号')
                              ->setCellValue('B'.$num, "投资编号")
                              ->setCellValue('C'.$num, "用户名")
                              ->setCellValue('D'.$num, "标的名称")
                              ->setCellValue('E'.$num, "标的类型")
                              ->setCellValue('G'.$num, "投资期限（天）")
                              ->setCellValue('F'.$num, "年化收益")
                              ->setCellValue('H'.$num, "投资本金")
                              ->setCellValue('I'.$num, "抵现券")
                              ->setCellValue('J'.$num, "纯利息")
                              ->setCellValue('K'.$num, "收益券收益")
                              ->setCellValue('L'.$num, "活动收益")
                              ->setCellValue('M'.$num, "应还总利息")
                              ->setCellValue('N'.$num, "本息总额")
                              ->setCellValue('O'.$num, "投资时间")
                              ->setCellValue('P'.$num, "起息时间")
                              ->setCellValue('Q'.$num, "还款日期")
                              ->setCellValue('R'.$num, "标的状态");
                }else{
                    //保留两位小数
                    $pre_income = $value['pure_interests'] + $value['act_interests'] + $value['coupon_interests'];
                    $account_all = num_format(($value['money'] + $pre_income)); //本息总额
                    $value['cate_name'] = $GLOBALS['db']->getOne("SELECT name from ".DB_PREFIX."deal_cate where id = '".$value['cate_id']."'");
                    $objPHPExcel->setActiveSheetIndex(0)
                              ->setCellValue('A'.$num, $num-1)
                              ->setCellValue('C'.$num, $value['real_name'])
                              ->setCellValue('D'.$num, $value['name'])
                              ->setCellValue('E'.$num, $value['cate_name'])
                              ->setCellValue('G'.$num, $value['repay_time'])
                              ->setCellValue('H'.$num, ($value['money'] - $value['coupon_cash']))
                              ->setCellValue('I'.$num, $value['coupon_cash'])
                              ->setCellValue('J'.$num, $value['pure_interests'])
                              ->setCellValue('K'.$num, $value['coupon_interests'])
                              ->setCellValue('L'.$num, $value['act_interests'])
                              ->setCellValue('M'.$num, $pre_income)
                              ->setCellValue('N'.$num, $account_all)
                              ->setCellValue('O'.$num, date("Y-m-d H:i:s",$value['create_time']))
                              ->setCellValue('P'.$num, $value['qixi_time'])
                              ->setCellValue('Q'.$num, $value['jiexi_time'])
                              ->setCellValue('R'.$num, $value['deal_status']);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$num,str_pad(($num-1),12,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,$value['rate']."%",PHPExcel_Cell_DataType::TYPE_STRING);
                }
            }
            //设置属性
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(12);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:R1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:R1')->getFill()->getStartColor()->setARGB('FFFFD700');

            $filename = $deal_name. "入金表";
            php_export_excel($objPHPExcel,$filename);
            die();
        }

        //导出还款计划
        /*
        function export_repay_plan($repay_list,$deal_name,$l_key){
            //导出Excel还款计划表
            require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $deal_repay_lists = array();
            foreach($repay_list as $key => $value){
                $deal_repay_lists[$key+1] = $value;
            }
            $deal_repay_lists[0] = array();
            ksort($deal_repay_lists);
            foreach($deal_repay_lists as $key => $value){
                $num = $key + 1;
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
                              ->setCellValue('G'.$num, "否")
                              ->setCellValue('H'.$num,'')
                              ->setCellValue('J'.$num, $deal_info['name'])
                              ->setCellValue('K'.$num, '');
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$num,str_pad(($num-1),4,"0",STR_PAD_LEFT),PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('E'.$num,$value['mobile'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('F'.$num,$value['real_name'],PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->setCellValueExplicit('I'.$num,$value['repay_money'],PHPExcel_Cell_DataType::TYPE_STRING);
                    //$objPHPExcel->getActiveSheet()->getStyle('A'.$num)->getNumberFormat()->setFormatCode("@");
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
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(40);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:K1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle( 'A1:K1')->getFill()->getStartColor()->setARGB('FFFFD700');
            $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);

            $filename = $deal_name . "第" . ($l_key + 1) . "期还款计划";
            php_export_excel($objPHPExcel,$filename);
        }
         */
}

?>