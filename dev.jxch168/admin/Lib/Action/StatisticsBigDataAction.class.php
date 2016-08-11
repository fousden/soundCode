<?php

class StatisticsBigDataAction extends CommonAction
{

    public function StatisticsBigData_deal_time()
    {
        //获取当前的时间
        $time = to_date(time(), "Y-m-d H").":00:00";
        //从url中获取开始时间，默认为前7个小时
        $start_time     = isset($_REQUEST["start_time"]) ? $_REQUEST["start_time"]: to_date(strtotime("$time-6 hour"), "Y-m-d H");
        $end_time       = isset($_REQUEST["end_time"]) ? $_REQUEST["end_time"]: to_date(strtotime("$time-1 hour"), "Y-m-d H");
        //将开始时间转换成int类型
        $start_time_int = str_replace("-", '', $start_time);
        $start_time_int = str_replace(" ", '', $start_time_int);

        $end_time_int = str_replace("-", '', $end_time);
        $end_time_int = str_replace(" ", '', $end_time_int);

        $start_time_t = $start_time.":00:00";
        $end_time_t = $end_time.":00:00";
        $date_lists = prTimes($start_time_t,$end_time_t);
        $date_list = $date_lists[1];
        foreach($date_lists[1] as $key => $val){
            $date_lists[3][$key] = to_date(strtotime($val."0000"),"Y-m-d H");
//            $time = str_replace(" ", '-', $time);
//            $time_arr = explode('-', $time);
//            $time = $time_arr[0] ."年". $time_arr[1] ."月". $time_arr[2] . "日". $time_arr[3]."时";
//            $date_lists[3][$key]=$time;
        }
        for ($i = 1; $i <= 2; $i++) {
            $sql_lists = "select create_time,data_type,sum(value) as data from fanwe_statistical_province where create_time >= ".$start_time_int . " and create_time <= ".$end_time_int . " and data_type=".$i ." GROUP BY create_time ORDER BY create_time asc";
            $lists[] = $GLOBALS['db']->getAll($sql_lists);
        }

        //将数组转换成图形所需要的数据
        $list = array();

        foreach($date_lists[1] as $key => $val){
            for($i = 0; $i <= 1; $i++){
                foreach($lists[$i] as $k => $v){
                    if($v['create_time'] == $val){
                        $list[$i][0][$key]=(float)$v['data'];
                    } 
                }
                if(!$list[$i][0][$key]){
                    $list[$i][0][$key]=(float)0;
                }
            }
        }
        $this->assign('type', 'line'); //这个数据是告诉模块该显示哪个图标line表示条形图，pie表示饼图
        for($i = 0; $i <= 1; $i++){
            $client_list['gross']['create_time'] = "总计";
            foreach($date_lists[1] as $key => $val){    
                
                foreach($lists[$i] as $k => $v){
                    
                    if($v['create_time'] == $val){  
                        $client_list[$val]['create_time']=$date_lists[3][$key];
                        $client_list[$val][$i]=(string)$v['data'];
                    }
                }
//                if(!$client_list[$val][$i]){
//                    $client_list[$val][$i]=(string)0;
//                } 
                $client_list['gross'][$i]+=$client_list[$val][$i];
            }
        }
        
        
        krsort($client_list);
        $this->assign('client_list', $client_list); //表格中的数据
        //x轴数值名，必填，类型为字符串数组
        $this->assign('xAxis_pot', json_encode($date_list));
        //y轴名，必填，类型为字符串
        $this->assign('yAxis_title', json_encode("yaxis"));
        $this->assign('date_list', $date_list);
        //传入的数据数组，必填，类型为数组
        $this->assign('data_array_1', json_encode($list[0]));
        $this->assign('data_array_2', json_encode($list[1]));
        //单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
        $this->assign('unit_1', json_encode("元"));
        $this->assign('unit_2', json_encode("人"));

        //传入的数据名数组，必填，数据名类型为字符串
        $this->assign('data_name_1', json_encode(array("金额")));
        $this->assign('data_name_2', json_encode(array("人数")));
        //鼠标悬浮时，饼图中间文字显示的内容
        $this->assign('series_name', json_encode("百分比"));

        //饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
        $this->assign('pie_data_array', json_encode($client_side_list));

        $this->assign("start_time", $start_time);
        $this->assign("end_time", $end_time);
        $this->display();
    }

    public function StatisticsBigData_deal_map(){
//        echo "<pre>";
//        print_r($_REQUEST);exit;
        if(array_key_exists("submit_date_7",$_REQUEST)){
            $_REQUEST['begin_time'] = date("Y-m-d H:i:s",time()-7*24*3600);
        }
        if(array_key_exists("submit_date_30",$_REQUEST)){
            $_REQUEST['begin_time'] = date("Y-m-d H:i:s",time()-30*24*3600);
        }
        $begin_time_unix = isset($_REQUEST['begin_time']) ? trim(strtotime($_REQUEST['begin_time'])) : (time()-3600*24*30);//时间区间开始时间
        $end_time_unix = isset($_REQUEST['end_time']) ? trim(strtotime($_REQUEST['end_time'])) : time() ;//时间区间结束时间
        if($end_time_unix<$begin_time_unix){
            $this->error("开始时间不能大于结束时间",$ajax);
            exit;
        }
        if($end_time_unix>time()){
            $this->error("结束时间不能大于当前时间",$ajax);
            exit;
        }
        $begin_time = date("Y-m-d H:i:s",$begin_time_unix);
        $end_time = date("Y-m-d H:i:s",$end_time_unix);
        $begin_date = date("YmdH",$begin_time_unix);
        $end_date = date("YmdH",$end_time_unix);
        $where=array();
        $where['create_time'] = array(array('egt',$begin_date),array('elt',$end_date));
        $where['data_type'] = array('eq',1);
        // 将数据库中的数据查出来按照地域查出来
        $data = M("Statistical_province")->field("*,sum(value) as count")->where($where)->group("city")->select();
        $info = M("Statistical_province")->field("*,sum(value) as count")->where($where)->group("province")->select();
        // 取出count最大值
        foreach($info as $key=>$val){
            $count_arr[] = $val['count'];
        }
        $max_count = max($count_arr);
        foreach($data as $val){
            foreach($val as $k=>$v) {
                $v1 = trim($v);
                $val1[$k] = $v1;
            }
            $data1[] = $val1;
        }
        foreach($info as $val){
            foreach($val as $k=>$v) {
                $v1 = trim($v);
                $val1[$k] = $v1;
            }
            $info1[] = $val1;
        }
        $this->assign("begin_time",$begin_time);
        $this->assign("end_time",$end_time);
        $this->assign("max_count",$max_count);
        $this->assign("city_list",$data1);
        $this->assign("pro_list",$info1);
        $this->display();
    }

}

?>