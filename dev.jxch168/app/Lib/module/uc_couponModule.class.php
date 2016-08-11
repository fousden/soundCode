<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/uc.php';

class uc_couponModule extends SiteBaseModule
{

    public function index()
    {

        $user_id = $GLOBALS['user_info']['id'];
        $now        = TIME_UTC;
        $sql_coupon = "SELECT count(*) as count FROM " . DB_PREFIX . "user_coupon WHERE user_id={$user_id} and end_time>{$now} and status=0";
        $not_use    = $GLOBALS['db']->getOne($sql_coupon);
        $GLOBALS['tmpl']->assign("not_use", $not_use);


        $sql_time   = "SELECT count(*) as count FROM " . DB_PREFIX . "user_coupon WHERE user_id={$user_id} and end_time<{$now} and status=0";
        $end_coupon = $GLOBALS['db']->getOne($sql_time);
        $GLOBALS['tmpl']->assign("end_coupon", $end_coupon);

        $sql_money = "SELECT sum(face_value) as money FROM " . DB_PREFIX . "user_coupon WHERE user_id={$user_id} and coupon_type=2";
        $money     = $GLOBALS['db']->getOne($sql_money);
        $GLOBALS['tmpl']->assign("money", $money);

        $sql_profit   = "SELECT sum(coupon_interests) as profit_money FROM " . DB_PREFIX . "deal_load WHERE user_id={$user_id}";
        $profit_money = $GLOBALS['db']->getOne($sql_profit);
        $GLOBALS['tmpl']->assign("profit_money", $profit_money);

        if (empty($_GET['type'])) {
            $_GET['type'] = -1;
        }
        if (empty($_GET['use'])) {
            $_GET['use'] = 1;
        }

        $sqlOther = '';
        if ($_GET['type'] > 0) {
            $sqlOther = " and coupon_type = '{$_GET['type']}' ";
        }

        if ($_GET['use'] == 1) {
            $sqlOther .= " and status = 0";
            $sqlOther .= " and end_time >= {$now}";
        }
        if ($_GET['use'] == 2) {
            $sqlOther .= " and status = 1";
        }
        if ($_GET['use'] == 3) {
            $sqlOther .= "  and end_time < {$now} and status = 0 ";
        }
        $sql_count = "SELECT count(*) FROM " . DB_PREFIX . "user_coupon WHERE user_id={$user_id} " . $sqlOther;
        $cnt       = $GLOBALS['db']->getOne($sql_count);
        $list     = array();
        $p        = '';
        $pageSize = app_conf("DEAL_PAGE_SIZE");
        if ($cnt > 0) {
            $page      = new Page($cnt, $pageSize);   //初始化分页对象
            $p         = $page->show();

            $sql = "SELECT status,min_limit,coupon_type,face_value,start_time,end_time,gain_time,remark FROM " . DB_PREFIX . "user_coupon WHERE user_id={$user_id}  " . $sqlOther ." order by id desc limit " . $page->firstRow . " , " . $pageSize;

            $list = $GLOBALS["db"]->getAll($sql);
            foreach ($list as $k => $v) {
                $list[$k]['start_time'] = date('Y-m-d H:i', $v['start_time']);
                $list[$k]['end_time']   = date('Y-m-d H:i', $v['end_time']);
                $list[$k]['min_limit'] = number_format($v['min_limit'],0);
                $list[$k]['face_value'] = number_format($v['face_value'],1);
                if($list[$k]['coupon_type']==1){
                    $list[$k]['face_value'] = number_format($v['face_value'],1);
                }elseif($list[$k]['coupon_type']==2){
                    $list[$k]['face_value'] = number_format($v['face_value'],0);
                }
            }
        }
        $GLOBALS['tmpl']->assign('pages', $p);
        $GLOBALS['tmpl']->assign("list", $list);
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_coupon.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

}

?>