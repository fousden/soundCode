<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CheckAction extends Action
{

    public function __construct()
    {
        parent::__construct();
        $action = array();
        if (!in_array(ACTION_NAME, $action)) {
            $this->user_info = checkLogin();
        }
    }

    public function getDistance($lat1 = '', $lng1 = '', $lat2 = '', $lng2 = '')
    {
        // 说明其中$lat1和$lng1是员工的纬度和经度，$lat2和$lng2是考勤地点即公司的纬度和经度
        $earthRadius        = 6367000;
        $lat1               = ($lat1 * pi() ) / 180;
        $lng1               = ($lng1 * pi() ) / 180;
        $lat2               = ($lat2 * pi() ) / 180;
        $lng2               = ($lng2 * pi() ) / 180;
        $calcLongitude      = $lng2 - $lng1;
        $calcLatitude       = $lat2 - $lat1;
        $stepOne            = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo            = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    public function getAroud($lat, $lng, $radius)
    {
        /*
         *  @param $lat 纬度
         *  @param $lng 经度
         *  @param $radius 半径
         */
        $PI         = pi();
        $latitude   = $lat;
        $longitude  = $lng;
        $degree     = (24901 * 1609) / 360.0;
        $raidusMile = $radius;
        $dpmLat     = 1 / $degree;
        $radiusLat  = $dpmLat * $raidusMile;
        $minLat     = $latitude - $radiusLat;
        $maxLat     = $latitude + $radiusLat;
        $mpdLng     = $degree * cos($latitude * ($PI / 180));
        $dpmLng     = 1 / $mpdLng;
        $radiusLng  = $dpmLng * $raidusMile;
        $minLng     = $longitude - $radiusLng;
        $maxLng     = $longitude + $radiusLng;
        $dis_arr    = array(
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLng' => $minLng,
            'maxLng' => $maxLng,
        );
        return $dis_arr;
    }

    public function array_sort($array,$keys,$type="asc"){
        // 按二维数组指定的键值来排序，默认为升序
        if (!isset($array) || !is_array($array) || empty($array)) {
            return '';
        }
        if (!isset($keys) || trim($keys) == '') {
            return '';
        }
        if (!isset($type) || $type == '' || !in_array(strtolower($type), array('asc', 'desc'))) {
            return '';
        }
        $keysvalue = array();
        foreach ($array as $key => $val) {
            $val[$keys]  = str_replace('-', '', $val[$keys]);
            $val[$keys]  = str_replace(' ', '', $val[$keys]);
            $val[$keys]  = str_replace(':', '', $val[$keys]);
            $keysvalue[] = $val[$keys];
        }
        asort($keysvalue); //key值排序
        reset($keysvalue); //指针重新指向数组第一个
        foreach ($keysvalue as $key => $vals) {
            $keysort[] = $key;
        }
        $keysvalue = array();
        $count     = count($keysort);
        if (strtolower($type) != 'asc') {
            for ($i = $count - 1; $i >= 0; $i--) {
                $keysvalue[] = $array[$keysort[$i]];
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $keysvalue[] = $array[$keysort[$i]];
            }
        }
        return $keysvalue;
    }

    public function index()
    {
        $user_info     = $this->user_info;
        $user_id       = $user_info['user_id'];
        $role_id       = $user_info['role_id']; // 所有者ID
        $department_id = $user_info['department_id'];
        // 接受act的值 show为显示页面，calendar为日历请求
        $act           = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : "show";
        //$today = date("Y-m-d",time()); // 接受到当前的日期
        // 点击考勤签到加载的页面
        $lat           = isset($_REQUEST['x']) ? trim($_REQUEST['x']) : '';  // 获取员工的纬度
        $lng           = isset($_REQUEST['y']) ? trim($_REQUEST['y']) : '';  // 获取员工的纬度
        // 如果不存在则参数有误
        $lat1          = M('role_department')->where(array("department_id" => $department_id))->getField('x'); // 考勤的纬度
        $lng1          = M('role_department')->where(array("department_id" => $department_id))->getField('y'); // 考勤的经度
        $distance      = $this->getDistance($lat, $lng, $lat1, $lng1);
        if ($distance > 100) {
            $root['decode'] = '0';
            $messege        = "未进入考勤范围，不能打卡";
        } else {
            $root['decode'] = '1';
            $messege        = "已进入考勤范围，可以打卡";
        }
        // 取出一个月的时间
        $check_date  = isset($_REQUEST["check_date"]) ? trim($_REQUEST['check_date']) : date("Y-m-d", time()); // 前台传过来的数据，如果不传则默认为当前
        $check_month = date("Y-m", strtotime($check_date));
        // 从check数据库中取数据
        $check_start = $check_month . '-01';
        $check_end   = $check_month . '-31';
        $field       = "checkin_time,checkout_time,work_start_time,work_end_time,create_date";
        $info        = M("check")->field($field)->where("create_date between '{$check_start}' and '{$check_end}' and user_id=$user_id ")->order("create_date asc")->select();
        $data        = array();
        $nowdate     = date("Y-m-d", time());
        $nowtime     = date("H:i", time());
//        echo "<pre>";
//        print_r($info);exit;
        if (!empty($info)) {
            foreach ($info as $key => $val) {
                $week = date("w", strtotime($val['create_date']));
                if ($week == 0 || $week == 6) {
                    $val['checkin_time']    = "";
                    $val['checkout_time']   = "";
                    $val['work_start_time'] = "";
                    $val['work_end_time']   = "";
                    $val['create_date']     = $val['create_date'];
                }
                $checkInfo[] = $val;
            }
            foreach ($checkInfo as $val) {
                // 处理非当天且不为周六日的数据
                $week = date("w", strtotime($val['create_date']));
                if ($nowdate != $val['create_date']) {
                    if ($week == 0 || $week == 6) {
                        // 如果是周六或者周日
                        $val['checkin_type']     = "2";
                        $val['checkin_messege']  = "";
                        $val['checkout_type']    = "2";
                        $val['checkout_messege'] = "";
                    } else {
                        if ($val['checkin_time'] == "") {
                            $val['checkin_type']    = '0';
                            $val['checkin_messege'] = '未打卡';
                        } elseif ($val['checkin_time'] > $val['work_start_time']) {
                            $val['checkin_type']    = '1';
                            $val['checkin_messege'] = '迟到';
                        } else {
                            $val['checkin_type']    = '2';
                            $val['checkin_messege'] = '上班正常';
                        }
                        if ($val['checkout_time'] == "") {
                            $val['checkout_type']    = '0';
                            $val['checkout_messege'] = '未打卡';
                        } elseif ($val['checkout_time'] < $val['work_end_time']) {
                            $val['checkout_type']    = '1';
                            $val['checkout_messege'] = '早退';
                        } else {
                            $val['checkout_type']    = '2';
                            $val['checkout_messege'] = '下班正常';
                        }
                    }
                }
                $data[] = $val;
            }
        }
        array_pop($data);
        // 处理当天的数据
        // 如果数据库没数据且此时时间还未到上班则制造一组数据给前台
        $info            = M("check")->where("create_date = '$nowdate' and user_id = $user_id ")->select();
        $field           = "work_start_date,work_end_date";
        $res             = M('role_department')->field($field)->where(array("department_id" => $department_id))->select();
        $work_start_time = $res['0']['work_start_date'];
        $work_end_time   = $res['0']['work_end_date'];
        $week            = date("w", strtotime($nowtdate));
        if (empty($info)) {
            if ($week == 0 || $week == 6) {
                $checkin_time     = "";
                $checkin_type     = "2";
                $checkin_messege  = "";
                $checkout_time    = "";
                $checkout_type    = "2";
                $checkout_messege = "";
                $work_start_time  = "";
                $work_end_time    = "";
            } else {
                // 处理签到
                if ($nowtime < $work_end_time) {
                    // 下班之前一直没有签到则显示白块
                    $checkin_time    = "";
                    $checkin_type    = "2";
                    $checkin_messege = "";
                } else {
                    $checkin_time    = "";
                    $checkin_type    = "0";
                    $checkin_messege = "未打卡";
                }
                //处理签退
                $checkout_time    = "";
                $checkout_type    = "2";
                $checkout_messege = "";
            }
        } else {
            foreach ($info as $key => $val) {
                if ($week == 0 || $week == 6) {
                    $checkin_time     = "";
                    $checkin_type     = "2";
                    $checkin_messege  = "";
                    $checkout_time    = "";
                    $checkout_type    = "2";
                    $checkout_messege = "";
                    $work_start_time  = "";
                    $work_end_time    = "";
                } else {
                    if ($val['checkin_time'] == '') {
                        if ($nowtime < $work_end_time) {
                            $checkin_time    = "";
                            $checkin_type    = "2";
                            $checkin_messege = "";
                        } else {
                            $checkin_time    = "";
                            $checkin_type    = "0";
                            $checkin_messege = "未打卡";
                        }
                    } else {
                        if ($val['checkin_time'] < $work_start_time) {
                            $checkin_time    = $val['checkin_time'];
                            $checkin_type    = "2";
                            $checkin_messege = "今天上班正常打卡";
                        } else {
                            $checkin_time    = $val['checkin_time'];
                            $checkin_type    = "1";
                            $checkin_messege = "今天上班迟到";
                        }
                    }
                    // 如果签退时间为空
                    if ($val['checkout_time'] == "") {
                        // 下班之前一直没有签退
                        $checkout_time    = "";
                        $checkout_type    = "2";
                        $checkout_messege = "";
                    } else {
                        if ($val["checkout_time"] < $work_end_time) {
                            $checkout_time    = $val["checkout_time"];
                            $checkout_type    = "1";
                            $checkout_messege = "今天是下班早退";
                        } else {
                            $checkout_time    = $val["checkout_time"];
                            $checkout_type    = "2";
                            $checkout_messege = "今天是下班正常打卡";
                        }
                    }
                }
            }
        }
        $today_array = array(
            "checkin_time"     => $checkin_time,
            "checkout_time"    => $checkout_time,
            "work_start_time"  => $res['0']['work_start_date'],
            "work_end_time"    => $res['0']['work_end_date'],
            "create_date"      => $nowdate,
            "checkin_type"     => $checkin_type,
            "checkin_messege"  => $checkin_messege,
            "checkout_type"    => $checkout_type,
            "checkout_messege" => $checkout_messege,
        );

        if ($today_array) {
            array_push($data, $today_array);
        }
        if ($data) {
            $root['data'] = $data;
        } else {
            $root['data'] = array();
        }
        $root['act']    = $act;
        $root['code']   = '1';
        $root['errmsg'] = $messege;
        output($root);
    }

    public function sign()
    {
        // 接收签到用户的数据
        $user_info     = $this->user_info;
        $user_id       = $user_info['user_id'];
        $role_id       = $user_info['role_id'];
        $department_id = $user_info['department_id'];
        // 获取上下班时间
        $field         = "work_start_date,work_end_date";
        $time_info     = M('role_department')->where(array('department_id' => $department_id))->select();
        foreach ($time_info as $val) {
            $work_start_time = $val['work_start_date'];
            $work_end_time   = $val['work_end_date'];
        }
        // 获取当前位置
        $data['user_id']         = $user_id;
        $data['x']               = isset($_REQUEST['x']) ? trim($_REQUEST['x']) : '';
        $data['y']               = isset($_REQUEST['y']) ? trim($_REQUEST['y']) : '';
        $data['address']         = isset($_REQUEST['address']) ? trim($_REQUEST['address']) : '';
        $data['work_start_time'] = $work_start_time;
        $data['work_end_time']   = $work_end_time;
        $data['create_date']     = date("Y-m-d", time());
        $lat = $data['x']; // 当前纬度
        $lng = $data['y']; // 当前经度
        $lat1          = M('role_department')->where(array("department_id" => $department_id))->getField('x'); // 考勤的纬度
        $lng1          = M('role_department')->where(array("department_id" => $department_id))->getField('y'); // 考勤的经度
        $distance      = $this->getDistance($lat, $lng, $lat1, $lng1);
        if ($distance > 100) {
            $root['不在考勤范围'];
        }
        if ($_REQUEST['type'] == 1) {
            $nowdate   = date("Y-m-d", time());
            // 签到
            // 获取当前时间
            $sign_time = date("H:i", time());
            if ($sign_time > $work_start_time) {
                $root['checkin_type'] = '1';
                $messege              = "迟到";
            } elseif ($sign_time <= $work_end_time) {
                $root['checkin_type'] = '2';
                $messege              = "签到成功";
            } else {
                $root['checkin_type'] = '3';
                $messege              = "已过签到时间";
            }
            $data['checkin_time'] = $sign_time;
            $root['checkin_time'] = $sign_time;
            // 如果数据库有记录则不让签到如果没有则写入数据库
            $checkin_time         = M("check")->where(array("user_id" => $user_id, "create_date" => $nowdate))->getField('checkin_time');
            if ($checkin_time) {
                output("你今天已经签过到");
            }
            $res = M('check')->add($data); // 签到则写入数据库
        }

        if ($_REQUEST['type'] == 2) {
            // 签退
            $check_time = date("H:i", time());
            $nowdate    = date("Y-m-d", time());
            if ($check_time - $work_end_time < 0) {
                $root['checkout_type'] = '1';
                $messege               = "早退";
            } else {
                $root['checkout_type'] = '2';
                $messege               = "签退成功";
            }
            // 判断早上是否有签到，有的话更新没的话插入
            $checkin_time  = M("check")->where(array("user_id" => $user_id, "create_date" => $nowdate))->getField('checkin_time');
            $checkout_time = M("check")->where(array("user_id" => $user_id, "create_date" => $nowdate))->getField('checkout_time');
            if ($checkin_time) {
                // 已经签到且已经签退
                if ($checkout_time) {
                    output("你今天已经签退过");
                }
                $info['checkout_time'] = $check_time;
                $root['checkout_time'] = $check_time; //返回下班打卡时间
                M('check')->where(array("user_id" => $user_id, "create_date" => $nowdate))->save($info); // 早上有签到，且没有签退的时候更新
            } else {
                // 没有签到但是已经签退过
                if ($checkout_time) {
                    output("你今天已经签退过");
                }
                $data['checkout_time'] = $check_time;
                $root['checkout_time'] = $check_time; // 返回下班打卡时间
                M('check')->add($data); //  早上没有签到，且没有签退的时候直接插入一条数据
            }
        }
        $root['code']   = '1';
        $root['errmsg'] = $messege;
        output($root);
    }

    public function outSide()
    {
        // 外勤签到客户
        $user_info = $this->user_info;
        $user_id   = $user_info['user_id'];
        $role_id   = $user_info['role_id'];
        $radius    = isset($_REQUEST['radius']) ? trim($_REQUEST['radius']) : '';
        $name      = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : ''; // 搜索的客户名
        $lng       = isset($_REQUEST['y']) ? trim($_REQUEST['y']) : ''; // 当前外勤的设定经度
        $lat       = isset($_REQUEST['x']) ? trim($_REQUEST['x']) : ''; // 当前外勤的设定经度
        $page      = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1; // 当前页数
        $page_size = 10;
        $offset    = $page_size * ($page - 1);
        $field     = "customer_id,name,x,y,address";
        $pub_where = '';
        if ($name) {
            $pub_where.= " and name like '%$name%'";
        }
        if ($radius == '') {
            $radius     = 5000;
            $dis_arr    = $this->getAroud($lat, $lng, $radius);
            $minLat     = $dis_arr['minLat']; // 最小的x
            $maxLat     = $dis_arr['maxLat']; // 最大的x
            $minLng     = $dis_arr['minLng']; // 最小的y
            $maxLng     = $dis_arr['maxLng']; // 最大的y
            $cus_where  = "owner_role_id = $role_id and x between $minLat and $maxLat and y between $minLng and $maxLng" . $pub_where;
            $cus_info   = M("customer")->field($field)->where($cus_where)->limit($offset, $page_size)->select(); // 附近的客户
            $count_page = M("customer")->where($cus_where)->count(); //附近的客户
//            $my_where = "user_id = $user_id".$pub_where;
//            $my_cus = M("customer")->field($field)->where($my_where)->select(); // 我的客户
//            if($my_cus){
//               $root['my_cus'] = $my_cus;
//            }else{
//               $root['my_cus'] =array();
//            }
        } else {
            $dis_arr    = $this->getAroud($lat, $lng, $radius);
            $minLat     = $dis_arr['minLat']; // 最小的x
            $maxLat     = $dis_arr['maxLat']; // 最大的x
            $minLng     = $dis_arr['minLng']; // 最小的y
            $maxLng     = $dis_arr['maxLng']; // 最大的y
            $where      = "owner_role_id = $role_id and x between $minLat and $maxLat and y between $minLng and $maxLng";
            $cus_info   = M("customer")->field($field)->where($where)->limit($offset, $page_size)->select(); //附近的客户
            $count_page = M("customer")->where($where)->count(); //附近的客户
        }
        $page_total = ceil($count_page / $page_size); //  总页数
        $page       = ($_REQUEST['page'] > $page_total) ? trim($_REQUEST['page']) : $page_total;
        // 附近的客户
        foreach ($cus_info as $key => $val) {
            $lat1                       = $val['x'];
            $lng1                       = $val['y'];
            $distance                   = $this->getDistance($lat, $lng, $lat1, $lng1);
            $cus_info[$key]['distance'] = (string) ($distance); // 两者之间的距离
            // 拜访记录,签到成功且汇报有记录算拜访一次
            $field                      = "create_time, sign_id,x,y";
            $report_info                = M("sign")->field($field)->where(array("user_id" => $user_id, "customer_id" => $val['customer_id']))->order("create_time desc")->select(); //  签到成功的
            if (!empty($report_info)) {
                $cus_info[$key]['visit_time']  = date("Y-m-d", $report_info['0']['create_time']);
                $cus_info[$key]['visit_count'] = count($report_info); //  总拜访记录
            } else {
                $cus_info[$key]['visit_time']  = '0'; // 无拜访记录为0
                $cus_info[$key]['visit_count'] = '0'; //  总拜访记录
            }
        }
        // 我的客户
//        foreach($my_cus as $key=>$val){
//            $lat1 = $val['x'];
//            $lng1 = $val['y'];
//            $distance = $this->getDistance($lat,$lng,$lat1,$lng2);
//            $my_cus[$key]['distance'] = (string)($distance); // 两者之间的距离
//            // 拜访记录,签到成功且汇报有记录算拜访一次
//            $field = "create_time, sign_id,x,y";
//            $report_info = M("sign")->field($field)->where(array("user_id"=>$user_id,"customer_id"=>$val['customer_id']))->order("create_time desc")->select(); //  签到成功的
//            if(!empty($report_info)){
//                $my_cus[$key]['visit_time'] = date("Y-m-d",$report_info['0']['create_time']);
//                $my_cus[$key]['visit_count'] = count($report_info); //  总拜访记录
//             }else{
//                $my_cus[$key]['visit_time'] = '0'; // 无拜访记录为0
//                $my_cus[$key]['visit_count'] = '0'; //  总拜访记录
//             }
//           }
        $root['code']   = '1';
        $root["errmsg"] = '请求成功';
        if ($cus_info) {
            $cus_info = $this->array_sort($cus_info,'distance','asc');
            $root['cus_info'] = $cus_info;
        } else {
            $root['cus_info'] = array();
        }
        $root['page']       = (string) $page; // 当前页
        $root['page_size']  = (string) $page_size; // 每页显示的页数
        $root['page_total'] = (string) $page_total; // 总页数
        output($root);
    }

    // 外勤签到
    public function outSign()
    {
        $user_info           = $this->user_info;
        $user_id             = $user_info['user_id'];
        $role_id             = $user_info['role_id'];
        $data['user_id']     = $user_id;
        $data['role_id']     = $role_id;
        $data['customer_id'] = isset($_REQUEST['customer_id']) ? trim($_REQUEST['customer_id']) : '';
        $data['remark']      = isset($_REQUEST['remark']) ? trim($_REQUEST['remark']) : '';
        $data['x']           = isset($_REQUEST['x']) ? trim($_REQUEST['x']) : ''; // 当前外勤的设定纬度
        $data['y']           = isset($_REQUEST['y']) ? trim($_REQUEST['y']) : ''; // 当前外勤的设定经度
        $data['agent']       = isset($_REQUEST['agent']) ? trim($_REQUEST['agent']) : ''; // 当前设备信息
        $data['create_time'] = time();
        $data['address']     = $_REQUEST['address'];
        $sign_id             = M("sign")->add($data);
        if ($sign_id && $_REQUEST['customer_id']) {
            $messege = "签到成功";
        }
        // 获取customer_name
        $log_data['customer_name'] = M("customer")->where(array("customer_id"=>$data['customer_id']))->getField("name");
        // 将需要的数据写入日志表
        $log_data['user_id']      = $user_id;
        //备注信息
        $log_data['information'] = $data['remark'];
        $log_data['customer_id'] = $data['customer_id'];
        $log_data['create_time']  = time();
        $log_data['sign_address'] = $data['address'];
        $log_data['agent']        = $data['agent'];
        $log_data['log_type']     = 1;
        M("working_log")->add($log_data);
        $root['code']             = '1';
        $root['errmsg']           = $messege;
        output($root);
    }


}
