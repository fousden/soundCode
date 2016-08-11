<?php

require APP_ROOT_PATH . 'app/Lib/uc.php';

class redbag {

    public function index() {
        $root = array();
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码

        $root['email'] = $email;
        $root['pwd'] = $pwd;



        //检查用户,用户密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);
        if ($user_id > 0) {
            //获取红包信息
            $page = intval($GLOBALS['request']['page']);
            if ($page == 0) {
                $page = 1;
            }
            $order = '';
            if (isset($_REQUEST['create_time']) && !empty($_REQUEST['create_time'])) {
                $order = "id " . $_REQUEST['create_time'];
            }
            $where = '';
            if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) {
                $where['type'] = $_REQUEST['type'];
            }
            $result = getBonusList(intval($user_id), $page, '', '', $where, $order);
            $count = $result['count'];
            $list = $result['list'];

            $root['response_code'] = 1;
            $root['item'] = $list;
            $root['page'] = array("page" => $page, "page_total" => ceil($count / app_conf("DEAL_PAGE_SIZE")), "page_size" => app_conf("DEAL_PAGE_SIZE"));
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
        }

        $root['program_title'] = "我的红包";
        output($root);
    }

}

?>