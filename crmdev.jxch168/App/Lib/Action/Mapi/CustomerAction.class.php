<?php

class CustomerAction extends Action {

    function __construct() {
        parent::__construct();
        $action = array();
        if (!in_array(ACTION_NAME, $action)) {
            $this->user_info = checkLogin();
        }
    }

    //客户列表
    public function index() {
        $user_info = $this->user_info;
        //按照更新时间降序排序
        if ($_REQUEST['order'] == "update_time") {
            $order['c.update_time'] = "desc";
            //按照创建时间降序排序
        } else if ($_REQUEST['order'] == "create_time") {
            $order['c.create_time'] = "desc";
            //默认按照更新时间降序排序
        } else {
            $order['c.update_time'] = "desc";
        }
        if ($_REQUEST['is_collect']) {
            $join="RIGHT JOIN ".C('DB_PREFIX')."customer_focus as cf using(customer_id)";
        }else{
            $join="";
        }
        if ($_REQUEST['name']) {
            $where['c.name'] = array("like", '%' . $_REQUEST['name'] . '%');
        }
        if (!$_REQUEST['page']) {
            $page = "1";
        } else {
            $page = $_REQUEST['page'];
        }
        $customer = M("customer");
        $where['c.owner_role_id'] = $user_info['role_id'];
        $where['c.is_deleted'] = 0;
        $root['page'] = $page;
        $limit = (($page - 1) * 10) . ",10";
        $count = $customer->table(C('DB_PREFIX')."customer as c")->where($where)->getfield("count(*)");
        $root['page_total'] = (string) ceil($count / 10);
        $field = "c.name,c.custom_type,c.customer_id,c.mobile";
        $customer_list = $customer->table(C('DB_PREFIX')."customer as c")->field($field)->where($where)->order($order)->join($join)->limit($limit)->select();
        if (!$customer_list) {
            $customer_list = array();
        }
        $root['code'] = "1";
        $root['list'] = $customer_list;
        output($root);
    }

    //新建客户
    public function insert() {
        $user_info = $this->user_info;
        if (!$_REQUEST['name']) {
            output("请输入姓名");
        } else if (!$_REQUEST['mobile']) {
            output("请输入联系方式");
        } else if (!$_REQUEST['custom_type']) {
            output("请选择客户性质");
        } else if (!$_REQUEST['rating']) {
            output("请选择客户分级");
        }
        $data = $_REQUEST;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['owner_role_id'] = $user_info['role_id'];
        $data['creator_role_id'] = $user_info['role_id'];
        $customer = M("customer");
        $customer_id = $customer->add($data);
        M("customer_data")->add(array("customer_id"=>$customer_id));
        $root['code'] = "1";
        $root['errmsg'] = "新建成功";
        $root['customer_id'] = (string) $customer_id;
        output($root);
    }

    //新建客户时页面所用到的数据
    public function add_data() {
        $user_info = $this->user_info;
        $root['code'] = "1";
        $root['errmsg'] = "请求成功！";
        $root['custom_info'] = $this->get_field_value("custom_type");
        $root['rating_info'] = $this->get_field_value("rating");
        output($root);
    }

    //已经弃用
    public function collect() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id']) {
            output("参数错误！");
        }
        $where['customer_id'] = $_REQUEST['customer_id'];
        $data['is_collect'] = "1";
        $res = M("customer")->where($where)->save($data);
        if (!$res) {
            output("操作失败！");
        } else {
            $root['code'] = "1";
            $root['errmsg'] = "关注成功！";
            output($root);
        }
    }

    //编辑客户时的数据
    public function edit_data() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id']) {
            output("缺少请求参数");
        }
        $where['customer_id'] = $_REQUEST['customer_id'];
        $field = "name,x,y,address,mobile,remark,custom_type,rating";
        $root = M("customer")->field($field)->where($where)->find();
        $root['custom_info'] = $this->get_field_value("custom_type");
        $root['rating_info'] = $this->get_field_value("rating");
        $root['code'] = "1";
        $root['errmsg'] = "请求数据成功";
        output($root);
    }

    //编辑客户
    public function edit() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id']) {
            output("缺少请求参数");
        }
        $customer = M("customer");
        $where['customer_id'] = $_REQUEST['customer_id'];
        $where['owner_role_id'] = $user_info['role_id'];
        //关注客户
        $customer_focus = isset($_REQUEST['customer_focus']) ? $_REQUEST['customer_focus'] : '2';
        if ($customer_focus == 1) {
            $root['act']="customer_focus";
            $customer_focus = M("customer_focus");
            if (!$customer_focus->where($where)->getField("focus_id")) {
                $data['user_id'] = $user_info['user_id'];
                $data['customer_id'] = $_REQUEST['customer_id'];
                $data['focus_time'] = time();
                if (!$customer_focus->add($data)) {
                    output("操作失败");
                } else {
                    $root['code'] = "1";
                    $root['errmsg'] = "关注成功！";
                    output($root);
                } 
            }
            //取消关注
        } else if ($customer_focus == 0) {
            $root['act']="customer_focus";
            if (!M("customer_focus")->where($where)->delete()) {
                output("操作失败");
            } else {
                $root['code'] = "1";
                $root['errmsg'] = "取消关注成功！";
                output($root);
            }
        }
        $customer_data = $_REQUEST;
        $customer_data['update_time'] = time();
        $customer_id = $customer->where($where)->save($customer_data);
        if (!$customer_id) {
            output("操作失败");
        }
        $root['code'] = "1";
        $root['errmsg'] = "客户编辑成功";
        output($root);
    }

    //客户详情
    public function customer_info() {
        $user_info = $this->user_info;
        if (!$_REQUEST['customer_id']) {
            output("缺少请求参数");
        }
        $where['customer_id'] = $_REQUEST['customer_id'];
        $where['owner_role_id'] = $user_info['role_id'];
        $field = "name,x,y,custom_type,mobile";
        $root = M("customer")->field($field)->where($where)->find();
        $root['custom_info'] = $this->get_field_value("custom_type");
        $root['is_collect'] = M("customer_focus")->where($where)->getField("count(*)");
        $root['code'] = "1";
        $root['errmsg'] = "请求成功！";
        output($root);
    }

    //获得下拉列表的值
    protected function get_field_value($field) {
        $fields = "name,setting";
        $where['field'] = $field;
        $custom_info = M("fields")->field($fields)->where($where)->find();
        $setting_str = '$setting=' . $custom_info['setting'] . ';';
        eval($setting_str);
        return array_values($setting['data']);
    }

}
