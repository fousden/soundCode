<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceAction
 *
 * @author xuchaomin
 */
class PinganAction extends CommonAction
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        import("ORG.Util.Page");
        $name       = isset($_REQUEST['real_name']) ? trim($_REQUEST['real_name']) : '';
        $mobile     = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $status     = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : '';
        $end_time   = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : '';
        $where = '';
       if (!empty($name)) {
            $where .= "and name='{$name}' ";
        }
        if (!empty($mobile)) {
            $where .= "and mobile='{$mobile}' ";
        }
        if ($status == 1) {
            $where .= 'and status = 0 ';
        } elseif ($status == 2) {
            $where .= 'and status > 0 ';
        } elseif ($status == 3) {
            $where .= "and status = '-1'  ";
        }

        if (!empty($begin_time) && !empty($endtime)) {
            $begin_time = strtotime($begin_time);
            $end_time   = strtotime($end_time);
            $where      = "and create_time>'{$begin_time}' and create_time>'{$end_time}' ";
        }
        $where  = trim($where, 'and');
        $pingan = M('Insure');
        if ($where) {
            $count     = $pingan->where($where)->getField('count(*)');
            $Page      = new Page($count);
            $show      = $Page->show();
            $nowPage   = isset($_GET['p']) ? $_GET['p'] : 1;
            $pageStart = ($nowPage - 1) * ($Page->listRows);
            $res       = $pingan->where($where)->order("create_time desc")->limit($pageStart . ',' . $Page->listRows)->select();
        } else {
            $count     = $pingan->where($where)->getField('count(*)');
            $Page      = new Page($count);
            $show      = $Page->show();
            $nowPage   = isset($_GET['p']) ? $_GET['p'] : 1;
            $pageStart = ($nowPage - 1) * ($Page->listRows);
            $res       = $pingan->order("create_time desc")->limit($pageStart . ',' . $Page->listRows)->select();
        }
        $list = array();
        if ($res) {
            foreach ($res as $k => $v) {
                $v['sex'] = 1 ? '男' : '女';
                if ($v['status'] == '0') {
                    $v['status'] = '投保成功';
                } elseif ($v['status'] == '-1') {
                    $v['status'] = '未投保';
                } else {
                    $v['status'] = '投保失败';
                }
                $list[] = $v;
            }
        }

        // 统计选择投保而没有处理的用户数
        $user         = M("user");
        $res          = $user->where("status=1")->count();
        $data['list'] = $list;
        $data['sum']  = $res;
        $this->assign('page', $show);
        $this->assign('data', $data);
        $this->display();
    }

}
