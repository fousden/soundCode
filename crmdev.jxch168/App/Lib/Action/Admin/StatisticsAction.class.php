<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class StatisticsAction extends Action{

    public function _initialize(){
         $action = array(
                'permission' => array(),
                'allow'=>array('signLog'),
          );
        B('Authenticate', $action);
    }

    public function index(){
        if(!session('?name') || !session('?user_id')){
            redirect(U('User/login/'), 1, L('PLEASE_LOGIN_FIRSET'));
        }
        $user_id = session("user_id");
        $dMonth = $_REQUEST['dmonth'] ? trim($_REQUEST['dmonth']) : date("Y-m",time()) ;
        $info = D("Calendar")->CalendarMonth($dMonth);
        $week_info = $info['0'];
        $result['week_info'] = $week_info;
        array_shift($info);
        $field = "user_id, checkin_time,checkout_time,work_start_time,work_end_time,create_date";
        $new_calendar = array();
        $nowdate = date("Y-m-d",time());
        $nowtime = date("H:i",time()); // 现在的时间
        $count = 0;
        foreach($info as $key=>$val){
            $count++;
            foreach($val as $k=>$v){
                // 从check表中查询数据
                $res = M('check')->field($field)->where(array('create_date'=>$v,'user_id'=>$user_id))->select();
                // 如果不是当天
                    if($res){// 不是当天数据库中有记录
                            $checkin_time = $res['0']['checkin_time'];
                            $checkout_time = $res['0']['checkout_time'];
                            $work_start_time = $res['0']['work_start_time'];
                            $work_end_time = $res['0']['work_end_time'];
                            $new_calendar[$key][$k]['week_day'] = $v;
                            $new_calendar[$key][$k]['check_in'] = $checkin_time;
                            $new_calendar[$key][$k]['check_out'] = $checkout_time;
                            if($v<$nowdate){
                                if($checkin_time == ""){
                                    $new_calendar[$key][$k]['status'] = '上班未打卡';
                                }elseif($checkin_time>$work_start_time){
                                    $new_calendar[$key][$k]['status'] = '迟到';
                                }else{
                                    $new_calendar[$key][$k]['status'] = ' ';
                                }
                                if($checkout_time == ""){
                                    $new_calendar[$key][$k]['estatus'] = '下班未打卡';
                                }elseif($checkout_time <$work_end_time ){
                                    $new_calendar[$key][$k]['estatus'] = '早退';
                                }else{
                                    $new_calendar[$key][$k]['estatus'] = ' ';
                                }
                            }else{
                                $new_calendar[$key][$k]['status'] = ' ';
                                $new_calendar[$key][$k]['estatus'] = ' ';
                            }
                    }else{
                            if($v<$nowdate){
                                $new_calendar[$key][$k]['week_day'] = $v;
                                $new_calendar[$key][$k]['check_in'] = ' ';
                                $new_calendar[$key][$k]['check_out'] = ' ';
                                $new_calendar[$key][$k]['status'] = '上班未打卡';
                                $new_calendar[$key][$k]['estatus'] = '下班未打卡';
                            }else{
                                $new_calendar[$key][$k]['week_day'] = $v;
                                $new_calendar[$key][$k]['check_in'] = ' ';
                                $new_calendar[$key][$k]['check_out'] = ' ';
                                $new_calendar[$key][$k]['status'] = ' ';
                                $new_calendar[$key][$k]['estatus'] = ' ';
                            }
                    }
            }
        }
        $key = $count-1;
        $k = $nowdate;
//        array_pop($new_calendar[$key]); // 将当天的数据去掉单独处理;
        $res = M("check")->where(array("create_date"=>$nowdate,"user_id"=>$user_id))->select();
        $role_id = M("role")->where(array("user_id"=>$user_id))->getField("role_id");
        $info = getUserByRoleId($role_id);
        $department_id = $info['department_id'];
        $time_info = M("role_department")->field("work_start_date,work_end_date")->where(array("department_id"=>$department_id))->select();
        $work_start_time = $time_info['0']['work_satrt_date'];
        $work_end_time = $time_info['0']['work_end_date'];
        $check_in_time = " ";
        $check_out_time = " ";
        if($nowtime<$work_end_time){
            if($res){
                 $check_in_time = $res['0']['checkin_time'];
                 $check_out_time = $res['0']['checkout_time'];
                 if($checkin_time > $work_start_time){
                     $status = "迟到";//
                 }else{
                     $status = " ";// 上班时间为空或者小于考勤时间都算正常考勤不显示状态
                 }

                 if($checkout_time !=' ' && $checkout_time<$work_end_time){
                     $estatus = "早退";
                 }else{
                     $estatus = " ";
                 }
            }else{
                // 还未到下班时间数据库没有记录都不用显示异常状态
                $status = " ";
                $estatus = " ";
            }
        }else{
            if($res){
                 $check_in_time = $res['0']['checkin_time'];
                 $check_out_time =$res['0']['checkout_time'];
                 if($checkin_time == ""){
                     $status = "上班未打卡";
                 }else{
                     if($checkin_time>$work_start_time){
                         $status = "迟到";
                     }else{
                         $status = " ";
                     }
                 }

                 if($checkout_time != " " && $checkout_time<$work_end_time){
                     $estatus = "早退";
                 }else{
                     $estatus = "1";
                 }
            }else{
                $status = "上班未打卡";
                $estatus = " ";
            }
        }
        foreach($new_calendar as $key => $val){
            foreach($val as $k=>$v){
                if($v['week_day'] = $nowdate){
                     $v['check_in'] = $check_in_time;
                     $v['check_out'] = $check_out_time;
                     $v['status'] = $status;
                     $v['estatus'] = $estatus;
                }
            }
        }
        $now_month = explode('-',$dMonth);
        $result['new_calendar'] = $new_calendar;
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("week_info", $result['week_info']);
        $this->assign("dMonth", $dMonth);
        $this->assign("Month", $now_month[0]."年".$now_month[1]."月");
        $this->assign("onMonth", D('Calendar')->onMonth($dMonth));
        $this->assign("lastMonth", D('Calendar')->lastMonth($dMonth));
        $this->assign("today", date("Y-m-d"));
        $this->display();
    }
    public function signLog(){
         if(!session('?name') || !session('?user_id')){
            redirect(U('User/login/'), 1, L('PLEASE_LOGIN_FIRSET'));
        }
        $user_id = session("user_id");
        $dMonth = $_REQUEST['dmonth'] ? trim($_REQUEST['dmonth']) : date("Y-m",time()) ;
        $info = D("Calendar")->CalendarMonth($dMonth);
        $week_info = $info['0'];
        $result['week_info'] = $week_info;
        array_shift($info);
        $field = "user_id, checkin_time,checkout_time,work_start_time,work_end_time,create_date";
        $new_calendar = array();
        foreach($info as $key=>$val){
            foreach($val as $k=>$v){
                // 从check表中查询数据
                $checkin_count = M('check')->where("checkin_time !='' and create_date='$v' ")->count(); // 当天上班签到人数
                $checkout_count = M('check')->where("checkout_time !='' and create_date = '$v' ")->count(); // 当天下班签退人数
                $late_count = M('check')->where("checkin_time > work_start_time and checkin_time != '' and create_date='$v'")->count(); // 迟到人数
                $early_count = M('check')->where("checkout_time < work_end_time and checkout_time != '' and create_date='$v' ")->count(); // 早退人数
                $new_calendar[$key][$k]['week_day'] = $v;
                $new_calendar[$key][$k]['checkin_count'] = $checkin_count;
                $new_calendar[$key][$k]['checkout_count'] = $checkout_count;
                $new_calendar[$key][$k]['late_count'] = $late_count;
                $new_calendar[$key][$k]['early_count'] = $early_count;
            }
        }
        $now_month = explode('-',$dMonth);
        $result['new_calendar'] = $new_calendar;
        $this->assign("new_calendar", $result['new_calendar']);
        $this->assign("week_info", $result['week_info']);
        $this->assign("dMonth", $dMonth);
        $this->assign("Month", $now_month[0]."年".$now_month[1]."月");
        $this->assign("onMonth", D('Calendar')->onMonth($dMonth));
        $this->assign("lastMonth", D('Calendar')->lastMonth($dMonth));
        $this->assign("today", date("Y-m-d"));
        $this->display();
    }
    public function show(){
         if(!session('?name') || !session('?user_id')){
            redirect(U('User/login/'), 1, L('PLEASE_LOGIN_FIRSET'));
         }
         $where = "";
         $status = array();
         $status = $_REQUEST['status'];
         $date = trim($_REQUEST['date']);
         $user_name = isset($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
         if($user_name){
             $user_id_arr = M("user")->field("user_id")->where("name like '%{$user_name}%'")->select();
//             echo "<pre>";
//             print_r($user_id);exit;
             foreach($user_id_arr as $val){
                 $check_user_id .= $val['user_id'].",";
             }
            $check_user_id = trim($check_user_id,",");
            $where.="and user_id in (".$check_user_id.") ";
//             if(is_array($user_id)){
//                 $check_user_id = explode(',',$user_id);
//                 $where.="and user_id in (".$check_user_id.") ";
//             }else{
//                 $check_user_id = $user_id;
//                 $where.="and user_id = $check_user_id ";
//             }
         }
         if(!empty($status)){
             foreach($status as $val){
                 switch($val){
                     case "1":
                         $where.="and checkin_time>work_start_time ";// 迟到
                         break;
                     case "2":
                         $where.="and checkin_time<work_start_time and checkin_time !=' ' ";// 上班准时
                         break;
                     case "3":
                         $where.="and checkin_time = ' ' ";// 未签到
                         break;
                     case "4":
                         $where.="and checkout_time<work_end_time and checkout_time != ' ' ";// 早退
                         break;
                     case "5":
                         $where.="and checkout_time>work_end_time";// 下班准时
                         break;
                     case "6":
                         $where.="and checkout_time = ' ' ";// 未签退
                 }
             }
         }
//         $field = trim($_REQUEST['field']); // 搜索关键字user_name用户名，sign签到时间，sign_out签退时间
//        // $condition = trim($_REQUEST['condition']);//关键字的情况contains包含，not_contain不包含，is是 isnot不是 start_with开始字符end_with结束字符is_empty为空is_not_empty不为空
//        // $search = trim($_REQUEST['search']);// 搜索内容
//         $where = '';
//         if($field){
//              $search = empty($_REQUEST['search']) ? '' : trim($_REQUEST['search']);
//              $condition = empty($_REQUEST['condition']) ? 'is' : trim($_REQUEST['condition']);
//              if	('checkin_time' == $field || 'checkout_time' == $field) {
//                  $search = is_numeric($search)?$search:date("H:i",strtotime($search));
//              }
//              switch($condition){
//                    case "is" :  $where[$field] = array('eq',$search);break;
//                    case "isnot" :  $where[$field]  = array('neq',$search);break;
//                    case "contains" :  $where[$field]  = array('like','%'.$search.'%');break;
//                    case "not_contain" :  $where[$field]  = array('notlike','%'.$search.'%');break;
//                    case "start_with" :  $where[$field]  = array('like',$search.'%');break;
//                    case "end_with" :  $where[$field]  = array('like','%'.$search);break;
//                    case "is_empty" :  $where[$field] = array('eq','');break;
//                    case "is_not_empty" :  $where[$field]  = array('neq','');break;
//                    case "gt" :  $where[$field]  = array('gt',$search);break;
//                    case "egt" :  $where[$field]  = array('egt',$search);break;
//                    case "lt" :  $where[$field]  = array('lt',$search);break;
//                    case "elt" :  $where[$field]  = array('elt',$search);break;
//                    case "eq" : $where[$field]  = array('eq',$search);break;
//                    case "neq" : $where[$field] = array('neq',$search);break;
//                    case "between" : $where[$field]  = array('between',array($search-1,$search+86400));break;
//                    case "nbetween" : $where[$field]  = array('not between',array($search,$search+86399));break;
//                    case "tgt" :  $where[$where[$field] ] = array('gt',$search+86400);break;
//                    default :	$where[$field] = array('eq',$search);
//              }
//               $params = array('field='.trim($_REQUEST['field']), 'condition='.$condition, 'search='.$_REQUEST["search"]);
//         }
         $m_check = M("check");
         //$info = $m_check->where("create_date = '$date'")->select();
         //$this->assign("list",$info);
         // 制造出一个数组checkInfoList包含1每页显示数，2用户的具体信息，3分页的相关信息 params=>array(0=>listrows=15)page相关信息
         // 接受listrows
         $listrows = isset($_GET['listrows']) ? trim($_GET['listrows'])  : 15;
         $checkInfoList['listrows'] = $listrows; // 每页显示记录数
         $params[] = "listrows=" . $listrows;
         $checkInfoList['params'] = $params;
         $p = intval($_GET['p'])?intval($_GET['p']):1; // 页数
         $where.="and create_date = '$date' ";
         $where = trim($where,"and");
//         echo $where;
         $checkInfoList['list'] = $m_check->where($where)->page($p.','.$checkInfoList['listrows'])->select();// 查出分页后的数据
//         echo "<pre>";
//         print_r($checkInfoList['list']);exit;
         foreach($checkInfoList['list'] as $key=>$val){
              $role_id = M("role")->where(array("user_id"=>$val['user_id']))->getField("role_id");
              $checkInfoList['list'][$key]['user'] = getUserByRoleId($role_id);
//             通过user_id 查出 position_id
//             $position_id = M("role")->where(array("user_id"=>$val['user_id']))->getField("position_id");
//             $field = "name,department_id";
//             $position_info = M("position")->field($field)->where(array("position_id"=>$position_id))->select();
//             $position_name = $position_info['0']['name'];
//             $department_id = $position_info['0']['department_id'];
//             // 通过岗位department_id找到获取name
//             $department_name = M("role_department")->where(array("department_id"=>$department_id))->getField("name");
//             // 通过user_id获取用户名
//             $user_name = M("user")->where(array("user_id"=>$val['user_id']))->getField("name");
//             $checkInfoList['list'][$key]['user_name'] = $user_name;
//             $checkInfoList['list'][$key]['position_name'] = $position_name;
//             $checkInfoList['list'][$key]['department_name'] = $department_name;
         }
         $count = $m_check->where($where)->count();
         import("@.ORG.Page");
         $Page = new Page($count,$checkInfoList['listrows']);
         $checkInfoList['page'] = $Page->show();
         $this->listrows = $checkInfoList['listrows'];
         $Page->parameter = implode('&', $checkInfoList['params']);
         $this->assign('page', $checkInfoList['page']);
         $checkInfoList['list']=empty($checkInfoList['list'])? ' ' : $checkInfoList['list'];
         $this->assign('list',$checkInfoList['list']);
         $this->assign('date',$date);
         $this->assign('status',$status);
//         echo "<pre>";
//         print_r($status);exit;
         $this->display();
    }
    public function login_log(){
        if(!session('?name') || !session('?user_id')){
           redirect(U('User/login/'), 1, L('PLEASE_LOGIN_FIRSET'));
        }
        // 获取30天前的时间戳
        $start_time =time()-24*30*3600; //
        $view = array();
        $view['user_name'] = $user_name = ($_REQUEST['user_name']) ? trim($_REQUEST['user_name']) : '';
        $login_start_time = empty($_REQUEST['login_start_time']) ? $start_time : strtotime(trim($_REQUEST['login_start_time'])); // 如果不设置默认为30天前
        $login_end_time =empty($_REQUEST['login_end_time']) ? time() : strtotime(trim($_REQUEST['login_end_time']) ) ;// 如果不设置则为当前时间
        $view['login_start_time'] = date("Y-m-d H:i:s",$login_start_time);
        $view['login_end_time'] = date("Y-m-d H:i:s",$login_end_time);
//        echo "<pre>";
//        print_r($_REQUEST);exit;
//        if($loigin_end_time<$login_start_time){
//            exit;
//        }
        $where = "";
        if($user_name){
            $user_id = M("user")->where(array("name"=>$user_name))->getField("user_id");
            $where .="and user_id=$user_id ";
        }
        if($login_start_time){
            $where.="and login_time > $login_start_time ";
        }
        if($login_end_time){
            $where.="and login_time<$login_end_time ";
        }

        $where = trim($where,"and");
//        echo $where;
        // 导入分页类
        import("@.ORG.Page");
        $listrows = isset($_GET['listrows']) ? trim($_GET['listrows'])  : 15;
        $p = intval($_GET['p'])?intval($_GET['p']):1;
        // 登录日志
        $login_info = M("loginHistory")->where($where)->page($p.','.$listrows)->select();
        // echo "<pre>";
        // print_r(M("loginHistory"));exit;
        $count = M("loginHistory")->where($where)->count();
        $Page = new Page($count,$listrows);
        foreach ($login_info as $key => $val) {
            $login_info[$key]['user_name'] = M("user")->where(array("user_id"=>$val['user_id']))->getField("name");
            $login_info[$key]['login_time'] = date("Y-m-d H:y:s",$val['login_time']);
        }
        $this->listrows = $listrows;
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign("list",$login_info);
        $this->assign("view",$view);
        $this->display();
    }
    public function working_log(){
        if(!session('?name') || !session('?user_id')){
            redirect(U('User/login/'), 1, L('PLEASE_LOGIN_FIRSET'));
        }
        import("@.ORG.Page");
        $listrows = isset($_GET['listrows']) ? trim($_GET['listrows'])  : 15;
        $p = intval($_GET['p'])?intval($_GET['p']):1;
        // 工作日志
        $work_info = M("workingLog")->page($p.",".$listrows)->select();
        $count = M("workingLog")->count();
        $Page = new Page($count,$listrows);
        foreach($work_info as $key=>$val){
            $work_info[$key]['user_name'] = M("user")->where(array("user_id"=>$val['user_id']))->getField("name");
            $work_info[$key]['customer_name'] = M("customer")->where(array("customer_id"=>$val['customer_id']))->getField("name");
            if($val['log_type']==1){
                $work_info[$key]['type'] = "外勤签到日志";
            }else{
                $work_info[$key]['type'] = "汇报记录日志";
            }
            $work_info[$key]['create_time'] = date("Y-m-d H:y:s",$val['create_time']);
        }
        $this->listrows = $listrows;
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign("list",$work_info);
//        $this->assign("view",$view);
        $this->display();
    }
}