<?php
/**
 * 后台财务管理model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Finance extends backend{

    //表名
    protected $tableName = 'deal_load';

    //初始化标的信息 公用的函数库
    public function init_deals($map,$dMonth,$type='qixi_date'){
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
                //剩余入金总额
                $new_calendar[$key][$k]['remain_capital'] = $return['remain_capital'];
            }
        }

        $result['new_calendar'] = $new_calendar;
        return $result;
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
            $deal_load_static = M("deal_load")->field("sum(money_e2) as all_money,sum(capital_e2) as all_capital,sum(coupon_cash_e2) as all_coupon_cash,sum(pure_interest_e2) as all_pure_interest,sum(active_interest_e2) as all_active_interest,sum(coupon_interest_e2)  as all_coupon_interest,sum(all_interest_e2)  as all_interest,sum(all_repay_money_e2)  as all_repay_money")->where($load_condition)->find();
            //该标的下所有已入金金额
            $has_capital = M("deal_load")->where(array("deal_id"=>$val["id"],"contract_no"=>array("neq","''"),"is_auto"=>0,"is_has_loan"=>1))->getField("sum(capital_e2)");
            //该标的下已经还款的金额
            $already_repay_money = M("deal_load_repay")->where(array("deal_id"=>$val["id"],"has_repay"=>1))->getField("sum(all_repay_money_e2)");

            if(!$static){
                //标的分类
                $deal_lists[$key]['cate_name'] =  M("deal_cate")->where(array("id"=>$val['cate_id']))->getField("name");
                //所有投标 投资人次
                $loads_passengers = M("deal_load")->where(array("is_auto"=>0,"contract_no"=>array("neq",''),"deal_id"=>$val['id']))->getField("count(*)");
                $deal_lists[$key]['loads_passengers'] = $loads_passengers ? $loads_passengers : 0;
                //投资期限
                $deal_lists[$key]['repay_time_name'] = $val['repay_time']."天";

                //该日期下所有标的 投资总额（包括抵现券）
                $deal_lists[$key]['deal_all_money'] = $deal_load_static['all_money'];
                //该日期下所有标的 入金总额
                $deal_lists[$key]['deal_all_capital'] = $deal_load_static['all_capital'];
                //该日期下所有标的 已入金总额
                $deal_lists[$key]['has_capital'] = $has_capital;
                //该日期下所有标的 抵现券总额
                $deal_lists[$key]['deal_all_coupon_cash'] = $deal_load_static['all_coupon_cash'];
                //该日期下所有标的 纯利息总额
                $deal_lists[$key]['deal_all_pure_interest'] = $deal_load_static['all_pure_interest'];
                //该日期下所有标的 收益券收益总额
                $deal_lists[$key]['deal_all_coupon_interest'] = $deal_load_static['all_coupon_interest'];
                //该日期下所有标的 活动收益总额
                $deal_lists[$key]['deal_all_active_interest'] = $deal_load_static['all_active_interest'];
                //该日期下所有标的 总收益总额
                $deal_lists[$key]['deal_all_interest'] = $deal_load_static['all_interest'];
                //该日期下所有标的 应还本息
                $deal_lists[$key]['deal_all_repay_money'] = $deal_load_static['all_repay_money'];
                //该日期下所有标的 已还本息
                $deal_lists[$key]['already_repay_money'] = $already_repay_money;
            }
            //该日期下所有标的 标的总额
            $return['date_borrow_amount'] += $val['borrow_amount_e2'];
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
            //已经还款金额
            $return['already_repay_money'] += $already_repay_money;
            //剩余应还金额
            $return['remain_repay_money'] += $deal_load_static['all_repay_money'] - $already_repay_money;
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
         $deal_load_count = M("deal_load")->where($where)->getField("count(*)");
         //标的下投资记录总数
         $return['load_count'] = $deal_load_count ? $deal_load_count : 0;
         //所有投资记录
         $return['load_list'] = M("deal_load")->where($where)->limit($limit)->select();
         //格式化数据
         foreach ($return['load_list'] as $k => $v) {
            //投资记录还款状态
            $return['load_list'][$k]['repay_info'] = M("deal_load_repay")->field("has_repay")->where(array("load_id"=>$v['id']))->find();
            //标的用户信息
            $return['load_list'][$k]['user_info'] = M("user")->field("user_name,real_name,mobile")->find($v["user_id"]);
            //还款信息
            $return['load_list'][$k]['deal_info'] = M("deal")->field("jiexi_date")->find($deal_id);
         }
         return $return;
    }
    
    //入金划拨款
    public function do_site_loan($id){
        if(!$id){
            $result['status'] = 0;
            $result['info'] = '数据错误';
            return $result;
        }
        $deal_info = M("deal")->find($id);

         //标的是否存在
        if (!$deal_info) {
            $result['status'] = 0;
            $result['info'] = '借款不存在';
            return $result;
        }
        if ($deal_info['deal_status'] != 2) {
            $return['info'] = "入金放款失败，借款不是满标状态";
            return $return;
        }
        //该借款所属的企业或个人富友账号
        $company_account = M("user")->where(array("id"=>$deal_info['borrower_id']))->getField("fuiou_account");
        //该标的下所有投资记录
        $load_list = M('deal_load')->where(array("deal_id"=>$id,"contract_no"=>array("neq","''"),"is_auto"=>0,"is_has_loan"=>0))->select();
        foreach ($load_list as $key=>$val){
            if($val["contract_no"]){
                //标的对应投资人
                $loan_user_info = M('User')->find($val['user_id']);
                //富友转账 还款
                $fuyou = D("base/fuyou");
                //转账记录数据
                $arr = $fuyou->transferBuAction($loan_user_info['fuiou_account'],$company_account,$val['capital_e2'],$val['contract_no'],$val['id']);
                //转账成功
                if ('0000' == $arr->plain->resp_code){
                    //更新投资记录状态
                    $update_data['contract_no_flag'] = 1;
                    $update_data['is_has_loan'] = 1;
                    $update_id = M("deal_load")->where(array("id"=>$val['id']))->data($update_data)->save();
                    if ($update_id){
                        $loan_flags = true;
                        //更新资金日志 待开发

                    }else{
                        $loan_flags = false;
                    }
                }else{
                    //划拨失败
                    $loan_flags = false;
                    //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[满标放款富友转账失败，失败的deal_load投标记录的ID为" . $val['id'] . "，投标人ID为" . $val['user_id'] . ",投标人用户名为" . $val['user_name'] . ",失败金额为" . $val['capital_e2'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
            }
        }
        //contract_no排除
        $no_lean_count = M("deal_load")->where(array("deal_id"=>$id,"is_auto"=>0,"is_has_loan"=>0))->count();
        if (!$no_lean_count) {
            //全部还完 更新标的状态
            //syn_deal_status($deal_info['id']);
            $deal_data['id'] = $id;
            $deal_data['deal_status'] = 3;
            $deal_data['verify_status'] = 1;
            $deal_data['is_has_loans'] = 1;
            $status = M('deal')->save($deal_data);
            if($status){
                $result['status'] = 1;
                $result['info'] = '入金放款成功！';
            }else{
                $result['status'] = 1;
                $result['info'] = '入金放款成功,标的状态更新失败！';
            }
        }else{
            //未全部还完 可以继续还款
            $result['status'] = 0;
            $result['info'] = '入金放款成功没有全部完成,可以再次确认还款！';
        }

        //生成还款计划数据
        $this->make_repay_plan($deal_info['id']);

        return $result;
    }

    //网站代还款
    public function do_site_repay($id,$l_key){
         if (!$id) {
             $result['status'] = 0;
             $result['info'] = '数据错误';
             return $result;
         }
         $deal_info = M("deal")->find($id);
         //标的是否存在
         if (!$deal_info) {
             $result['status'] = 0;
             $result['info'] = '借款不存在';
             return $result;
         }
         //该借款所属的企业或个人富友账号
         $company_account = M("user")->where(array("id"=>$deal_info['borrower_id']))->getField("fuiou_account");
         //指定标的 还款计划
         $deal_load_repay_list = M("deal_load_repay")->where(array("deal_id"=>$id))->select();
         //网站转账 实现在线自动还款
         foreach ($deal_load_repay_list as $kk => $vv) {
            if ($vv['has_repay'] == 0) {//借入者已还款，但是没打款到借出用户中心
                //准备更新数据
                $load_repay_data['true_jiexi_time'] = time();
                $load_repay_data['true_jiexi_date'] = date("Y-m-d",$load_repay_data['true_jiexi_time']);
                if($deal_info["jiexi_date"] == strtotime($load_repay_data['true_jiexi_date'])){
                    $load_repay_data['status'] = 1;//准时
                }else if($deal_info["jiexi_date"] < strtotime($load_repay_data['true_jiexi_date'])){
                    $load_repay_data['status'] = 2;//2逾期 3严重逾期
                }else{
                    $load_repay_data['status'] = 0;//0提前
                }
                $load_repay_data['is_site_repay'] = 1;//网站垫付
                $load_repay_data['has_repay'] = 1;//是否已还

                //标的对应的投资人
                $repay_user_info = M("user")->find($vv['user_id']);
                //富友转账 还款
                $fuyou = D("base/fuyou");
                //转账记录数据
                $arr = $fuyou->transferBuAction($company_account,$repay_user_info['fuiou_account'], $vv['all_repay_money_e2'],'',$vv['id']);
                //转账成功
                if ('0000' == $arr->plain->resp_code) {
                    $update_id = M("deal_load_repay")->where(array("id"=>$vv["id"],"has_repay"=>0))->data($load_repay_data)->save();
                    if ($update_id) {
                         //更新用户账户资金记录
                         $repay_flag = true;
                         //modify_account(array("money" => $user_load_data['true_repay_money']), $in_user_id, "[<a href='" . $deal_info['url'] . "' target='_blank'>" . $deal_info['name'] . "</a>],第" . ($kk + 1) . "期,回报本息", 5);
                         //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账成功，成功的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",成功金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }else{
                         $repay_flag = false;
                         //失败则还原账户金额
                         $fuyou->transferBuAction($repay_user_info['fuiou_account'], $company_account, $vv['all_repay_money_e2'],'',$vv['id']);
                         //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款更新还款计划记录失败，失败的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",失败金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }
                } else {
                     //划拨失败
                     $repay_flag = false;
                     //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferCash.log', "[网站代还款富友转账失败，失败的deal_load_repay还款记录的ID为" . $vv['id'] . "，投标人ID为" . $vv['user_id'] . ",投标人用户名为" . $vv['user_name'] . ",失败金额为" . $vv['month_repay_money'] . "];return[返回信息" . json_encode($arr) . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
                /*if($repay_flag){
                    if ($vv['all_repay_money_e2'] != 0) {
                        //短信通知


                        //站内信


                        //邮件

                    }
                }*/
            }
         }
         $no_repay_count = M("deal_load_repay")->where(array("deal_id"=>$id,"has_repay"=>0))->count();
         //所有的都还完完成
         if (!$no_repay_count) {
             //全部还完 更新标的状态
             $deal_data["deal_status"] = 4;
             M("deal")->where(array("id"=>$deal_info["id"]))->data($deal_data)->save();
             //syn_deal_status($deal_info['id']);
             $result['status'] = 1;
             $result['info'] = '网站代还款成功！';
         }else{
             //未全部还完 可以继续还款
             $result['status'] = 0;
             $result['info'] = '网站代还款没有全部完成,可以再次确认还款！';
         }
         //如果网站还款存在异常 即短信通知系统管理员 发送短信通知 如果异常 则短信通知管理员

         //管理员操作日志

         return $result;
    }

    //生成还款计划
    function make_repay_plan($deal_id){
        $deal_info = M("deal")->find($deal_id);
        //该标的下所有已放款的投资记录
        $load_list = M('deal_load')->where(array("deal_id"=>$deal_id,"contract_no"=>array("neq","''"),"is_auto"=>0,"is_has_loan"=>1))->select();
        foreach($load_list as $key=>$val){
            if($val['contract_no']){
                $deal_load_repay_data['deal_id'] = $deal_id;
                $deal_load_repay_data['user_id'] = $val['user_id'];
                $deal_load_repay_data['load_id'] = $val['id'];
                $deal_load_repay_data['coupon_id'] = $val['coupon_id'];
                $deal_load_repay_data['coupon_type'] = $val['coupon_type'];
                $deal_load_repay_data['money_e2'] = $val['money_e2'];
                $deal_load_repay_data['capital_e2'] = $val['capital_e2'];
                $deal_load_repay_data['rate_e2'] = $val['rate_e2'];
                $deal_load_repay_data['increase_rate_e2'] = $val['increase_rate_e2'];
                $deal_load_repay_data['repay_time'] = $val['repay_time'];
                $deal_load_repay_data['pure_interest_e2'] = $val['pure_interest_e2'];
                $deal_load_repay_data['active_interest_e2'] = $val['active_interest_e2'];
                $deal_load_repay_data['coupon_interest_e2'] = $val['coupon_interest_e2'];
                $deal_load_repay_data['all_interest_e2'] = $val['all_interest_e2'];
                $deal_load_repay_data['all_repay_money_e2'] = $val['all_repay_money_e2'];
                $deal_load_repay_data['coupon_cash_e2'] = $val['coupon_cash_e2'];
                $deal_load_repay_data['jiexi_time'] = strtotime($deal_info["jiexi_date"]);
                $deal_load_repay_data['jiexi_date'] = $deal_info["jiexi_date"];
                //插入
                $add_id = M("deal_load_repay")->add($deal_load_repay_data);
            }
        }
    }
}