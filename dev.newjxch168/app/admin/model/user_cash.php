<?php

/**
 * 后台用户充值、提现公共模块 model业务逻辑类
 *
 * @author jxch
 */

namespace admin\model;
use base\model\backend;

class UserCash extends backend{

    //获取用户列表
    function getListData($tableName,$_request){
        $condition = array();
        //查询时间
        if(!empty($_request['start_time']) || !empty($_request['end_time'])){
            $start_time = isset($_request["start_time"]) ? strtotime($_request["start_time"]) : strtotime("-6 days");//默认提前一周
            $end_time = isset($_request["end_time"]) ? strtotime($_request["end_time"]) : time();//默认提前一周
            $condition['create_time'] = array('between',array($start_time,$end_time));
        }
        //查询充值订单号
        if(!empty($_request['serial_num'])){
            $condition['serial_num'] = trim($_request['serial_num']);
        }
        //查询订单号
        if(!empty($_request['notice_sn'])){
            $condition['notice_sn'] = trim($_request['notice_sn']);
        }
        //查询用户名
        if(!empty($_request['user_name'])){
            $user_name = trim($_request['user_name']);
            $user_id = M('User')->where(array('user_name'=>$user_name))->getField('id');
            $condition['user_id'] = $user_id;
        }
        //查询手机号
        if(!empty($_request['mobile'])){
            $mobile = intval($_request['mobile']);
            $user_id = M('User')->where(array('mobile'=>$mobile))->getField('id');
            $condition['user_id'] = $user_id;
        }

        $list_data = $this->_list($tableName,$condition);
        foreach($list_data['data_list'] as $key=>$val){
            $list_data['data_list'][$key]['money'] = $val['money_e2']/100;
        }
        return $list_data;
    }

    //处理时间
    function getDateList($tableName,$_request){
        //获取当前的时间
        $time           = date("Y-m-d");
        $start_date     = isset($_request["start_date"]) ? $_request["start_date"] : date("Y-m-d",strtotime("-6 days"));//默认提前一周
        $end_date       = isset($_request["end_date"]) ? $_request["end_date"] : $time;

        $condtion['create_date'] = array('between',array($start_date,$end_date));
        $condtion['status']     = 1;
        $date_lists = M($tableName)->where($condtion)->field('distinct create_date')->select();

        //重组时间数组
        foreach($date_lists as $key=>$val){
            $date_list[] = $val['create_date'];
        }
        return $date_list;
    }

    //饼图
    function getPieData($tableName,$date_list){
        //饼图数据
        $total_money = $this->where(array('create_date'=>$date_list[0]))->field('sum(money_e2) as money')->group('create_date')->find();
        for($i=1;$i<5;$i++){
            $condition['create_date']     = $date_list[0];
            $condtion['status'] = 1;
            $source = $tableName == "user_incharge" ? "incharge_source" : "carry_source" ;
            $condition[$source] = $i;
            $part_money =array();
            $part_money = M($tableName)->where($condition)->field('sum(money_e2) as money')->group('create_date')->find();
            $lists[$i] = round($part_money['money'] / ($total_money['money']) * 100, 2);
        }
        return $lists;
    }

    //折线图
    function getLineData($tableName,$date_list){
        //折线图数据
        for($i=1;$i<5;$i++){
            foreach($date_list as $key=>$val){
                $condition['create_date'] = $val;
                $condtion['status'] = 1;
                $source = $tableName == "user_incharge" ? "incharge_source" : "carry_source" ;
                $condition[$source] = $i;
                $total_money =array();
                $total_money = M($tableName)->where($condition)->field('sum(money_e2) as money')->group('create_date')->find();
                $lists[$i][$key] = (float)($total_money['money']/100);
                $lists[5][$key] += (float)($total_money['money']/100);
            }
        }
        ksort($lists);
        return $lists;
    }

    //统计日账单
    function getAccount($tableName,$date_list){
        //列表数据
        $client_list = array();
        foreach($date_list as $key=>$val){
            $client_list[$key]['date'] = $val;
            $condition['create_date'] = $val;
            $condition['status'] = 1;
            unset($condition[$source]);
            $client_list[$key]['people'] = M($tableName)->where($condition)->count('distinct user_id');
            for($i=1;$i<5;$i++){
                $source = $tableName == "user_incharge" ? "incharge_source" : "carry_source" ;
                $condition[$source] = $i;
                $total_money =array();
                $total_money = M($tableName)->where($condition)->field('sum(money_e2) as money')->group('create_date')->find();
                $client_list[$key][$i] = (float)($total_money['money']/100);
                $client_list[$key]['money'] += (float)($total_money['money']/100);
                $client_list['gross'][$i] += (float)($total_money['money']/100);
                $client_list['gross']['money'] += (float)($total_money['money']/100);
            }
            $client_list['gross']['people']+= $client_list[$key]['people'];

        }
        $client_list['gross']['date'] = "总计";
        krsort($client_list);
        return $client_list;
    }

    //用户充值、提现排行榜
    function getUserRank($tableName,$_request){
        $condition = array();
        //查询时间
        if(!empty($_request['start_time']) || !empty($_request['end_time'])){
            $start_time = isset($_request["start_time"]) ? strtotime($_request["start_time"]) : strtotime("-6 days");//默认提前一周
            $end_time = isset($_request["end_time"]) ? strtotime($_request["end_time"]) : time();//默认提前一周
            $condition['create_time'] = array('between',array($start_time,$end_time));
        }
        //查询用户名
        if(!empty($_request['user_name'])){
            $user_name = trim($_request['user_name']);
            $user_id = M('User')->where(array('user_name'=>$user_name))->getField('id');
            $condition['user_id'] = $user_id;
        }
        //查询手机号
        if(!empty($_request['mobile'])){
            $mobile = intval($_request['mobile']);
            $user_id = M('User')->where(array('mobile'=>$mobile))->getField('id');
            $condition['user_id'] = $user_id;
        }
        $condtion['status'] = 1;
        $sortBy = 'money';
        $_field = "sum(money_e2) as money,user_id";
        $_group = "user_id";
        $user_rank_list = $this->_list($tableName,$condition,$sortBy,0,$_field,$_group);

        foreach($user_rank_list['data_list'] as $key=>$val){
            $user_rank_list['data_list'][$key]['money'] = $val['money']/100;
            $user_rank_list['data_list'][$key]['id']    = $key+1;
        }
        return $user_rank_list;
    }
    //分页
    private function _list($tableName,$condition,$sortBy = '', $asc = false,$_field='',$_group='') {
        if (isset ( $_REQUEST['_order'] )) {
            $_order = $_REQUEST['_order'];
        } else {
            $_order = !empty($sortBy)?$sortBy:M($tableName)->getPk ();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST['_sort'])) {
            $_sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
        } else {
            $_sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $result = M($tableName)->where($condition)->field($_field)->group($_group)->select();
        $count = count($result);
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $data_list = M($tableName)->where($condition)->field($_field)->group($_group)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['data_list'] = $data_list;
        }
        return $return;
    }
}