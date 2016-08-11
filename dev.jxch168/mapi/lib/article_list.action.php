<?php

class article_list {

    public function index() {

        //分页
        $page = isset($GLOBALS['request']['page']) ? intval($GLOBALS['request']['page']) : 1;
        $root = array();
        $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : 0;
        if ($type == 1) {
            $cate_id = 23;
        } else if ($type == 2) {
            $cate_id = 22;
        } else {
            $cate_id = intval($_REQUEST['cate_id']);
        }
        //分页
        $limit = (($page - 1) * app_conf("PAGE_SIZE")) . "," . app_conf("PAGE_SIZE");

        $sql = "select id,icon,title,create_time,update_time,brief,content from " . DB_PREFIX . "article where is_effect = 1 and cate_id = " . $cate_id . " order by create_time desc ";
        $sql.=" limit " . $limit;

        $sql_count = "select count(*) from " . DB_PREFIX . "article where is_effect = 1 and cate_id = " . $cate_id;
//
        $count = $GLOBALS['db']->getOne($sql_count);
        $list = $GLOBALS['db']->getAll($sql);
        foreach ($list as $key => $val) {
            if (!$val['brief']) {
                $list[$key]['brief'] = trimall(mb_substr(strip_tags($val['content']), 0, 54, 'UTF-8') . '...');
            }
            $list[$key]['brief'] = trimall(mb_substr(strip_tags($val['brief']), 0, 54, 'UTF-8') . '...');
            $list[$key]['create_date'] = date("Y-m-d", $val['create_time']);
            unset($list[$key]['content'], $list[$key]['create_time']);
        }
        $root['page'] = array("page" => $page, "page_total" => ceil($count / app_conf("PAGE_SIZE")), "page_size" => app_conf("PAGE_SIZE"));

        $root['response_code'] = 1;
        $root['list'] = $list;
        $root['program_title'] = "文章列表";
        output($root);
    }

}

?>
