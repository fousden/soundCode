<?php

namespace home\controller;
use base\controller\frontend;

/**
 * 前台首页 用户中心user控制器
 *
 * @author jxch
 */

class User extends frontend{

    //用户个人中心
    function index(){
        $user_info = $_SESSION['user_info'];
        $this->assign('user_info',$user_info);
        return $this->fetch();
    }

    //用户充值
    function incharge(){
        if(IS_POST){
            $user_info = session("user_info");
            $result = D("user_incharge")->doIncharge($user_info,$_POST);
            if($result["status"]){
                die($result["html_content"]);
            }else{
                return $this->error($result["info"], $user_info, "/home/user/incharge");
            }
        }else{
            $payment_list = M("payment")->where(array("is_effect"=>1))->select();
            $this->assign("payment_list", $payment_list);
            //充值页面显示
            return $this->fetch();
        }
    }

    //前台充值记录回调页面
    function incharge_log(){
        $user_info = session("user_info");
        $return = D("user_incharge")->getInchargeList($_REQUEST,$user_info);
        //充值记录页面显示
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list", $return['incharge_list']);
        $this->assign("resp_desc", $return['resp_desc']);
        return $this->fetch();
    }

     //后台充值记录回调页面
    function incharge_notify(){
        D("user_incharge")->doInchargeNotify($_REQUEST);
    }

    //用户提现
    function carry(){
        if(IS_POST){
            //用户提现
            $user_info = session("user_info");
            $result = D("user_carry")->doCarry($user_info,$_POST);
            if($result["status"]){
                die($result["html_content"]);
            }else{
                return $this->error($result["info"], $user_info, "/home/user/carry");
            }
        }else{
            $this->assign("user_info", session("user_info"));
            //提现页面显示
            return $this->fetch();
        }
    }

    //前台提现记录回调页面
    function carry_log(){
        $return = D("user_carry")->getCarryList();
        //提现记录页面显示
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list", $return['carry_list']);
        return $this->fetch();
    }

    //后台提现回调
    function carry_notify(){
        //提现订单处理
        D("user_carry")->cash_notify($_REQUEST);
    }

    //银行卡列表
    function bank(){
        $user_info = session("user_info");
        $this->assign("user_info", $user_info);
        return $this->fetch();
    }

    //用户个人投资列表
    function invest(){
        //创建分页对象
        if (!empty($_REQUEST ['listRows'])) {
            $listRows = $_REQUEST ['listRows'];
        } else {
            $listRows = 20;
        }
        $condition["user_id"] = session("user_info.id");
        $return = D("deal_load")->getUserDealLoad($condition,$listRows);
        $this->assign("page", $return['page']);
        $this->assign("nowPage", $return["nowPage"]);
        $this->assign("list", $return["load_list"]);
        return $this->fetch();
    }

    //用户个人资料
    function material(){
        $user_info = session("user_info");
        $this->assign("user_info", $user_info);
        return $this->fetch();
    }

    // 安全信息
    function security(){
        $user_info = $_SESSION['user_info'];
        $user_id = $user_info['user_id'];
        // 查询出用户的相关信息
        //dump($_SESSION['home']);exit;
        $bank_list = D("bank")->where("is_rec=1")->order("is_rec desc,sort desc,id asc")->select();
        $region_lv1 = D("district_info")->where("parentcode = 0")->select();
        $this->assign("user_info",$user_info);
        $this->assign("bank_list",$bank_list);
        $this->assign("region_lv1",$region_lv1);
        return $this->fetch();
    }

    // 实名认证
    function check_idno(){
        $user_info = $_SESSION['user_info'];
        if(empty($user_info)){
            $this->redirect("/home/system/login");
        }
        $arr = $_REQUEST;
        $info = D("system")->do_account($arr);
        if($info=="0000"){
            ajax_return("开户成功","1");
        }else{
            ajax_return($info,'0');
        }
    }

    function reset_pwd(){
        $arr = $_REQUEST;
        $info = D("user")->resetPwd($arr);
        ajax_return($info);
    }

    function get_code(){
        $info = D("user")->getCode();
        ajax_return($info);
    }

    function check_submit(){
        print_r($_REQUEST);
        $arr = $_REQUEST;
        $info = D("user")->checkSubmit($arr);
        ajax_return($info);
    }

    //站内信
    function inner_mail(){
        return $this->fetch();
    }

    function inner_mail_data(){
        $mail = M("UserInnerMail");
        $user_id = intval($_SESSION['user_info']['user_id']);
        //直接进入页面显示全部消息
        $result_all = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->select();
        $count = count($result_all);
        if($count!=0){
            $num = ceil($count / 8);
        }
        else{
            $num = 1;
        }

        $result = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->select();
        foreach($result as $k => $v){
            $date = date('m-d h:i',$v['create_time']);
            if($v['key']==0){
                // 网页
                $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
            }

            if($v['key']==1){
                // 标的
                $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
            }
            if($v['is_read']==1){
                $li = "";
                $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                $arr_all[] = $li;
            }
            elseif($v['is_read']==0){
                $li = "";
                $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox readed'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title jiacu' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</a><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                $arr_all[] = $li;
            }
        }
        if($num >= 2 ){
            $this->assign("num_next", 2);
        }
        else{
            $this->assign("num_next", 1);
        }

        $this->assign("count", $count);
        $this->assign("arr_all", $arr_all);
        $this->assign("type", 1);
        $this->assign("num", $num);

        //分页
        if ($_REQUEST['readtype'] == 1) {
            //点击分页操作
            if ($_REQUEST['p']) {
                $page     = intval($_POST['p'] - 1); //当前页
                $pageSize = 8; //每页显示数

//                $sql_all = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC";
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->select();
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 1;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id."and is_delete=0")->order("create_time desc")->limit($startPage,$pageSize)->select();

                foreach($result as $k => $v){
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }
                    if($v['is_read']==1){
                        $li = "";
                        $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                        $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                        $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                        $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                        $arr['list'][] = $li;
                    }
                    elseif($v['is_read']==0){
                        $li = "";
                        $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox readed'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                        $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title jiacu' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                        $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                        $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                        $arr['list'][] = $li;
                    }
                }


                ajax_return($arr);
            } elseif(isset($_REQUEST['page'])){
                $page     = intval($_POST['page'] - 1); //当前页
                $pageSize = 8; //每页显示数

//                $sql_all = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC";
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->select();
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 1;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->limit($startPage,$pageSize)->select();
                foreach($result as $k => $v){
                    $date = date('m-d h:i',$v['create_time']);
                    // 处理类型
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }

                    if($v['is_read']=='1'){
                        $li = "";
                        $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息111</p><p class='data'>{$date}</p>";
                        $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                        $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                        $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                        $arr['list'][] = $li;
                    }
                    elseif($v['is_read']=='0'){
                        $li = "";
                        $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox readed'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                        $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title jiacu' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                        $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                        $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                        $arr['list'][] = $li;
                    }
                }


                ajax_return($arr);
            }

        }


        //已读分页
        if ($_REQUEST['readtype'] == 3) {
            //点击分页操作
            if ($_REQUEST['p']) {
                $page     = intval($_POST['p'] - 1); //当前页
                $pageSize = 8; //每页显示数

//                $sql_read = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC";
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0")->order("create_time desc")->select();
                $total     = count($result);
                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 3;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=1")->order("create_time desc")->limit($startPage,$pageSize)->select();
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }
                    $li = "";
                    $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                    $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                    $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='3'>删除</a>";
                    $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                    $arr['list'][] = $li;
                }

                ajax_return($arr);
            } elseif(isset($_REQUEST['page'])){
                $page     = intval($_POST['page'] - 1); //当前页
                $pageSize = 8; //每页显示数

//                $sql_nread = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC";
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=1")->order("create_time desc")->select();
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 3;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=1")->order("create_time desc")->limit($startPage,$pageSize)->select();;;
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }
                    $li = "";
                    $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                    $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                    $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='3'>删除</a>";
                    $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                    $arr['list'][] = $li;
                }

                ajax_return($arr);
            }

        }

        //未读分页
        if ($_REQUEST['readtype'] == 2) {
            //点击分页操作
            if ($_REQUEST['p']) {
                $page     = intval($_POST['p'] - 1); //当前页
                $pageSize = 8; //每页显示数

//                $sql_nread = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC";
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=0")->order("create_time desc")->select();
                $total     = count($result);
                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 2;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=0")->order("create_time desc")->limit($startPage,$pageSize)->select();
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }
                    $li = "";
                    $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox readed'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                    $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title jiacu' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                    $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='2'>删除</a>";
                    $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                    $arr['list'][] = $li;
                }
                ajax_return($arr);
            } elseif(isset($_REQUEST['page'])){
                $page     = intval($_POST['page'] - 1); //当前页
                $pageSize = 8; //每页显示数
                $result    = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=0")->order("create_time desc")->select();
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 2;
//                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
//                    . "where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $mail->where("to_user_id=".$user_id." and is_delete=0 and is_read=0")->order("create_time desc")->limit($startPage,$pageSize)->select();
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['key']==0){
                        // 网页
                        $v['content']= $v['content']."&nbsp<a style='color:#09C7F7' href='".$v['value']."'>点击查看</a>";
                    }

                    if($v['key']==1){
                        // 标的
                        $v['content'] = $v['content']."&nbsp<a style='color:#09C7F7' href='/home/deal/deal_info?id=".$v['value']."'>点击查看</a>";
                    }
                    $li = "";
                    $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox readed'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
                    $li .= "</div><div class='f_l container'><div class='header'><div class='f_l title jiacu' rel='{$v['id']}'>{$v['title']}</div><a href='javascript:;' class='f_r showbtn' rel='{$v['id']}'>展开</a>";
                    $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='2'>删除</a>";
                    $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                    $arr['list'][] = $li;
                }
                ajax_return($arr);
            }

        }
    }

    function delete_mail(){
        $user_id = intval($_SESSION['user_info']['user_id']);
        if(isset($_REQUEST['deleteid'])){
            $deleteid = intval($_REQUEST['deleteid']);
            $mail = M("UserInnerMail");
            $data['is_delete'] = 1;
            $data['is_read'] = 1;
            $id = $mail->where("id=".$deleteid." and to_user_id=".$user_id)->save($data);
            if($id){
                $result = array('result' => 1);
                ajax_return($result);
            }

        }
    }

    public function deleteAll()
    {
        $user_id = intval($_SESSION['user_info']['user_id']);
        if($_REQUEST['deleteall']==1){
            $mail = M("UserInnerMail");
            $data['is_delete'] = 1;
            $id = $mail->where("to_user_id=".$user_id)->save($data);
            if($id){
                $result = array('result' => 1);
                ajax_return($result);
            }
        }
    }

    function cheangeread(){
        $user_id = intval($_SESSION['user_info']['user_id']);
        if($_REQUEST['nread']==1&isset($_REQUEST['readid'])){
            $readid = intval($_REQUEST['readid']);
            $mail = M("UserInnerMail");
            $data['is_read'] = 1;
            $id = $mail->where("id=".$readid." and to_user_id=".$user_id)->save($data);
            if($id){
                $result = array('result' => 1);
                ajax_return($result);
            }
        }
    }

    function collection(){
        $user_id = $_SESSION['user_info']['id'];
        // 分页
        $page = intval($_REQUEST['p']);
        if($page==0){
            $page = 1;
        }
        $limit = (($page-1)*10);
        $limit.= ",10";

        // 搜索出我关注的标的id
        $data = D("user")->getCollection($user_id,$limit);
        // 分页
        import("ORG.Page");
        $page = new \think\page($data['count'],10);   //初始化分页对象
        $p  =  $page->show();
        $this->assign('pages',$p);
        $this->assign("list",$data['list']);
        return $this->fetch();
    }
}
