<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/uc.php';

class uc_msgModule extends SiteBaseModule
{

    public function index()
    {



        $user_id = intval($GLOBALS['user_info']['id']);

        //直接进入页面显示全部消息
        $sql_all = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC";
        $result_all = $GLOBALS['db']->getAll($sql_all);
        $count = count($result_all);
        if($count!=0){
            $num = ceil($count / 8);
        }
        else{
            $num = 1;
        }


        $sql = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC limit 0,8";
        $result = $GLOBALS['db']->getAll($sql);
        foreach($result as $k => $v){
            $date = date('m-d h:i',$v['create_time']);
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
                $li .= "<div class='Clear'></div></div><div class='cont showmsg hidemsg'><p class='text f_l'>{$v['content']}</p><a class='deletebtn f_r' href='javascript:;' rel='{$v['id']}' type='1'>删除</a>";
                $li .= "<div class='Clear'></div></div></div><div class='Clear'></div></li>";
                $arr_all[] = $li;
            }
        }
        if($num >= 2 ){
            $GLOBALS['tmpl']->assign("num_next", 2);
        }
        else{
            $GLOBALS['tmpl']->assign("num_next", 1);
        }

        $GLOBALS['tmpl']->assign("count", $count);
        $GLOBALS['tmpl']->assign("arr_all", $arr_all);
        $GLOBALS['tmpl']->assign("type", 1);
        $GLOBALS['tmpl']->assign("num", $num);

        //分页
        if ($_REQUEST['readtype'] == 1) {
            //点击分页操作
            if ($_REQUEST['p']) {
                $page     = intval($_POST['p'] - 1); //当前页
                $pageSize = 8; //每页显示数

                $sql_all = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_all);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 1;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);

                foreach($result as $k => $v){
                    $date = date('m-d h:i',$v['create_time']);
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

                $sql_all = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_all);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 1;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);
                foreach($result as $k => $v){
                    $date = date('m-d h:i',$v['create_time']);
                    if($v['is_read']=='1'){
                        $li = "";
                        $li .= "<li class='msglist' rel='{$v['id']}'><div class='f_l typebox'><p class='type'>系统消息</p><p class='data'>{$date}</p>";
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

                $sql_read = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_read);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 3;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
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

                $sql_nread = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_nread);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 3;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 and is_read = 1 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
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

                $sql_nread = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_nread);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 2;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
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

                $sql_nread = "select content,title,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC";
                $result    = $GLOBALS['db']->getAll($sql_nread);
                $total     = count($result);

                $totalPage = ceil($total / $pageSize); //总页数
                $startPage = $page * $pageSize; //开始记录
                //构造数组
                $arr['total']     = $total;
                $arr['pageSize']  = $pageSize;
                $arr['totalPage'] = $totalPage;
                $arr['readtype'] = 2;
                $sql              = "select id,title,content,to_user_id,create_time,is_read,is_notice from " . DB_PREFIX . "msg_box "
                        . "where to_user_id = $user_id and is_delete = 0 and is_read = 0 order by create_time DESC limit $startPage,$pageSize";
                $result           = $GLOBALS['db']->getAll($sql);
                foreach ($result as $k => $v) {
                    $date = date('m-d h:i',$v['create_time']);
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



        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_msg_index.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    public function changeread()
    {
        $user_id = intval($GLOBALS['user_info']['id']);
        if($_REQUEST['nread']==1&isset($_REQUEST['readid'])){
                $readid = intval($_REQUEST['readid']);
                $sql = "update ".DB_PREFIX."msg_box set is_read = 1 where id = {$readid} and to_user_id = $user_id";
                $GLOBALS['db']->query($sql);
                $result = array('result' => 1);
                ajax_return($result);
        }

    }

    public function delete()
    {
        $user_id = intval($GLOBALS['user_info']['id']);
        if(isset($_REQUEST['deleteid'])){
                $deleteid = intval($_REQUEST['deleteid']);
                $sql = "update ".DB_PREFIX."msg_box set is_delete = 1,is_read = 1 where id = {$deleteid} and to_user_id = $user_id";
                $GLOBALS['db']->query($sql);
                $result = array('result' => 1);
                ajax_return($result);
        }

    }

    public function deleteAll()
    {
        $user_id = intval($GLOBALS['user_info']['id']);
        if($_REQUEST['deleteall']==1){

                $sql = "update ".DB_PREFIX."msg_box set is_delete = 1 where to_user_id = $user_id";
                $GLOBALS['db']->query($sql);
                $result = array('result' => 1);
                ajax_return($result);
        }
    }

    public function deal()
    {
        $group_key = strim($_REQUEST['id']);
        $user_id   = intval($GLOBALS['user_info']['id']);
        $sql       = "select count(*) as count,max(system_msg_id) as system_msg_id,max(id) as id,max(is_notice) as is_notice from " . DB_PREFIX . "msg_box
				where is_delete = 0 and ((to_user_id = " . $user_id . " and `type` = 0) or (from_user_id = " . $user_id . " and `type` = 1))
				and group_key = '" . $group_key . "'";
        $row       = $GLOBALS['db']->getRow($sql);
        if ($row['count'] == 0 && isset($_REQUEST['id'])) {
            app_redirect(url("index", "uc_msg"));
        } elseif ($row['count'] == 0 && !isset($_REQUEST['id'])) {
            //没有消息对象， 直接创建消息
            //查出fans列表
            $user_info = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "user where id = " . $user_id);
            $page_size = 24;
            $page      = intval($_REQUEST['p']);
            if ($page == 0)
                $page      = 1;
            $limit     = (($page - 1) * $page_size) . "," . $page_size;

            //输出粉丝
            $total     = $GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "user_focus where focused_user_id = " . $user_id);
            $fans_list = array();
            if ($total > 0)
                $fans_list = $GLOBALS['db']->getAll("select focus_user_id as id,focus_user_name as user_name from " . DB_PREFIX . "user_focus where focused_user_id = " . $user_id . " order by id desc limit " . $limit);

            $GLOBALS['tmpl']->assign("fans_list", $fans_list);

            $page = new Page($total, $page_size);   //初始化分页对象
            $p    = $page->show();
            $GLOBALS['tmpl']->assign('pages', $p);

            $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['WRITE_PM']);
            $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_msg_deal_send.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }  elseif ($row['system_msg_id'] > 0 || $row['is_notice'] >= 1) {
            //end count==0
            //系统消息，仅查看
            $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['SYSTEM_PM']);

            //分页
            $page  = intval($_REQUEST['p']);
            if ($page == 0)
                $page  = 1;
            $limit = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");

            $extW = "";
            if ($row['is_notice'] == 0 && $row['system_msg_id'] > 0)
                $extW = " is_notice=" . $row['is_notice'] . " AND from_user_id=0 ";
            else
                $extW = " system_msg_id = " . $row['system_msg_id'] . " AND is_notice=" . $row['is_notice'];

            $sql_count = "select count(*) from " . DB_PREFIX . "msg_box where $extW AND to_user_id=" . $GLOBALS['user_info']['id'] . " and is_delete = 0";

            $total = $GLOBALS['db']->getOne($sql_count);
            $list  = array();
            if ($total > 0) {
                $sql  = "select * from " . DB_PREFIX . "msg_box where $extW AND to_user_id=" . $GLOBALS['user_info']['id'] . " and is_delete = 0 ORDER BY id DESC LIMIT " . $limit;
                $list = $GLOBALS['db']->getAll($sql);
            }
            $GLOBALS['tmpl']->assign("list", $list);

            $page = new Page($total, app_conf("PAGE_SIZE"));   //初始化分页对象
            $p    = $page->show();
            $GLOBALS['tmpl']->assign('pages', $p);

            $GLOBALS['db']->query("update " . DB_PREFIX . "msg_box set is_read = 1 where $extW ");

            $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_msg_deal_system.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        } else {
            //消息记录
            $page      = intval($_REQUEST['p']);
            if ($page == 0)
                $page      = 1;
            $limit     = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");
            $user_id   = intval($GLOBALS['user_info']['id']);
            $sql       = "select * from " . DB_PREFIX . "msg_box
					where is_delete = 0 and ((to_user_id = " . $user_id . " and `type` = 0) or (from_user_id = " . $user_id . " and `type` = 1))
					and group_key = '" . $group_key . "'
					order by create_time desc limit " . $limit;
            $sql_count = "select count(*) from " . DB_PREFIX . "msg_box
					where is_delete = 0 and ((to_user_id = " . $user_id . " and `type` = 0) or (from_user_id = " . $user_id . " and `type` = 1)) and group_key = '" . $group_key . "'";

            $upd_sql = "update " . DB_PREFIX . "msg_box set is_read = 1
					where is_delete = 0 and ((to_user_id = " . $user_id . " and `type` = 0) or (from_user_id = " . $user_id . " and `type` = 1))
					and group_key = '" . $group_key . "' ";

            $GLOBALS['db']->query($upd_sql);

            $count = $GLOBALS['db']->getOne($sql_count);
            $list  = array();
            if ($count > 0) {
                $list = $GLOBALS['db']->getAll($sql);
                foreach ($list as $k => $v) {
                    if ($v['to_user_id'] != $user_id) {
                        $dest_user_id = $v['to_user_id'];
                        break;
                    }
                    if ($v['from_user_id'] != $user_id) {
                        $dest_user_id = $v['from_user_id'];
                        break;
                    }
                }
            }


            $dest_user_name = $GLOBALS['db']->getOne("select user_name from " . DB_PREFIX . "user where id = " . $dest_user_id);

            $page = new Page($count, app_conf("PAGE_SIZE"));   //初始化分页对象
            $p    = $page->show();
            $GLOBALS['tmpl']->assign('pages', $p);

            $GLOBALS['tmpl']->assign("msg_list", $list);
            $GLOBALS['tmpl']->assign("count", $count);
            $GLOBALS['tmpl']->assign("dest_user_name", $dest_user_name);
            $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['PM_LIST']);
            $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_msg_deal_list.html");
            $GLOBALS['tmpl']->display("page/uc.html");
        }
    }

    public function setting()
    {
        $GLOBALS['tmpl']->assign("page_title", $GLOBALS['lang']['UC_MSG_SETTING']);

        $msg_setting = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_conf where user_id = " . $GLOBALS['user_info']['id']);

        $GLOBALS['tmpl']->assign("msg_setting", $msg_setting);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_msg_setting.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    public function savesetting()
    {
        if ($GLOBALS['user_info']['id'] > 0) {
            $data['user_id']           = intval($GLOBALS['user_info']['id']);
            $data['mail_asked']        = intval($_REQUEST['mail_asked']);
            $data['sms_asked']         = intval($_REQUEST['sms_asked']);
            $data['mail_bid']          = intval($_REQUEST['mail_bid']);
            $data['sms_bid']           = intval($_REQUEST['sms_bid']);
            $data['mail_myfail']       = intval($_REQUEST['mail_myfail']);
            $data['sms_myfail']        = intval($_REQUEST['sms_myfail']);
            $data['mail_half']         = intval($_REQUEST['mail_half']);
            $data['sms_half']          = intval($_REQUEST['sms_half']);
            $data['mail_bidsuccess']   = intval($_REQUEST['mail_bidsuccess']);
            $data['sms_bidsuccess']    = intval($_REQUEST['sms_bidsuccess']);
            $data['mail_fail']         = intval($_REQUEST['mail_fail']);
            $data['sms_fail']          = intval($_REQUEST['sms_fail']);
            $data['mail_bidrepaid']    = intval($_REQUEST['mail_bidrepaid']);
            $data['sms_bidrepaid']     = intval($_REQUEST['sms_bidrepaid']);
            $data['mail_answer']       = intval($_REQUEST['mail_answer']);
            $data['sms_answer']        = intval($_REQUEST['sms_answer']);
            $data['mail_transferfail'] = intval($_REQUEST['mail_transferfail']);
            $data['sms_transferfail']  = intval($_REQUEST['sms_transferfail']);
            $data['mail_transfer']     = intval($_REQUEST['mail_transfer']);
            $data['sms_transfer']      = intval($_REQUEST['sms_transfer']);
            $data['mail_redenvelope']  = intval($_REQUEST['mail_redenvelope']);
            $data['sms_redenvelope']   = intval($_REQUEST['sms_redenvelope']);
            $data['mail_rate']         = intval($_REQUEST['mail_rate']);
            $data['sms_rate']          = intval($_REQUEST['sms_rate']);
            $data['mail_integral']     = intval($_REQUEST['mail_integral']);
            $data['sms_integral']      = intval($_REQUEST['sms_integral']);
            $data['mail_gift']         = intval($_REQUEST['mail_gift']);
            $data['sms_gift']          = intval($_REQUEST['sms_gift']);


            if ($GLOBALS['db']->getOne("select count(*) from " . DB_PREFIX . "msg_conf where user_id = " . $data['user_id']) == 0) {
                //添加
                $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_conf", $data, "INSERT");
            } else {
                //编辑
                $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_conf", $data, "UPDATE", "user_id=" . $data['user_id']);
            }
            $key = md5("USER_MSG_CONF_" . $data['user_id']);
            //更新配置缓存
            set_dynamic_cache($key, $data);
            showSuccess($GLOBALS['lang']['MESSAGE_POST_SUCCESS']);
        } else {
            app_redirect(url("index", "user#login"));
        }
    }

}

?>