<?php

class ReportAction extends Action {

    function __construct() {
        parent::__construct();
        $action = array();
        if (!in_array(ACTION_NAME, $action)) {
            $this->user_info = checkLogin();
        }
    }

    //汇报记录的列表
    function index() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id']) {
            output("缺少请求参数");
        }
        $root = $this->index_data($user_info['user_id'], $_REQUEST['customer_id']);
        $root['code'] = "1";
        $root['errmsg'] = "请求数据成功！";
        output($root);
    }

    //添加客户的汇报记录
    public function add() {
        $user_info = $this->user_info;
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '1';
        if (!$_REQUEST['customer_id'] || !$_REQUEST['agent']) {
            output("缺少请求参数！");
        } else if (!$_REQUEST['content']) {
            output("请输入汇报的内容！");
        }
        $report = M("report");
        $data['user_id'] = $user_info['user_id'];
        $data['customer_id'] = $_REQUEST['customer_id'];
        $data['create_time'] = time();
        $data['type'] = $type;
        $data['content'] = $_REQUEST['content'];
        $data['agent'] = $_REQUEST['agent'];
        $res = $report->add($data);
        if (!$res) {
            output("操作失败，请重新操作！");
        } else {
            $working_log_data['user_id'] = $user_info['user_id'];
            $working_log_data['customer_id'] = $_REQUEST['customer_id'];
            $working_log_data['customer_name'] = M("customer")->where(array("customer_id"=>$data['customer_id']))->getField("name");
            $working_log_data['create_time'] = time();
            $working_log_data['report_type'] = $type;
            $working_log_data['agent'] = $_REQUEST['agent'];
            $working_log_data['information'] = $_REQUEST['content'];
            $working_log_data['log_type'] = 2;
            M("working_log")->add($working_log_data);
            $root = $this->index_data($user_info['user_id'], $_REQUEST['customer_id']);
            $root['code'] = "1";
            $root['errmsg'] = "操作成功";
            output($root);
        }
    }

    //此方法是用于取出汇报记录列表的数据，当前在index和add方法中使用
    public function index_data($user_id, $customer_id) {
        $where['customer_id'] = $customer_id;
        $report = M("report");
        $where['user_id'] = $user_id;
        if (!$_REQUEST['page']) {
            $page = "1";
        } else {
            $page = $_REQUEST['page'];
        }
        $root['page'] = $page;
        $limit = (($page - 1) * 10) . ",10";
        $count = $report->where($where)->getfield("count(*)");
        $root['page_total'] = (string) ceil($count / 10);
        $field = "r.create_time,r.type,r.content,r.agent,u.name as user_name";
        $report_list = $report->table(C('DB_PREFIX') . "report as r")->field($field)->where($where)->order("report_id desc")->join(C('DB_PREFIX') . "user as u using(user_id)")->limit($limit)->select();
        if (!$report_list) {
            $report_list = array();
        } else {
            foreach ($report_list as $key => $val) {
                $report_list[$key]['create_time'] = time_tran($val['create_time']);
            }
        }
        $root['data'] = $report_list;
        return $root;
    }

    //上传图片以及语音方法
    public function update_file() {
        //如果有文件上传
//        import('@.ORG.UploadFile');
//        //导入上传类
//        $upload = new UploadFile();
//        //设置上传文件大小
//        $upload->maxSize = 20000000;
//        //设置附件上传目录
//        $dirname = UPLOAD_PATH . date('Ym', time()).'/'.date('d', time()).'/';
//        $upload->allowExts  = array('jpg','jpeg','png','gif');// 设置附件上传类型
//        if (!is_dir($dirname) && !mkdir($dirname, 0777, true)) {
//                $this->error('附件上传目录不可写');
//        }
//        $upload->savePath = $dirname;
//        if(!$upload->upload()) {// 上传错误提示错误信息
//                alert('error', $upload->getErrorMsg(), $_SERVER['HTTP_REFERER']);
//        }else{
//            alert('error', "上传成功", $_SERVER['HTTP_REFERER']);
//        }
    }

}
