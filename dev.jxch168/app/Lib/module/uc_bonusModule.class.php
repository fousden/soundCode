<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

require APP_ROOT_PATH . 'app/Lib/uc.php';

class uc_BonusModule extends SiteBaseModule
{

    public function index()
    {
//            $deal_load_num = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "deal_load where user_id = ".$GLOBALS['user_info']['id']. " AND is_auto = 0");
//            var_dump($deal_load_num);die;

//
        if(!$_REQUEST['type']){
            $_REQUEST['type'] = 1;
        }
        //获取红包信息
        $result = getBonusList(intval($GLOBALS['user_info']['id']), intval($_REQUEST['p']),'','',$_REQUEST['type']);
//                $sql_bonus = "SELECT start_time,end_time,min_limit,is_effect,user_id FROM ".DB_PREFIX."user_bonus WHERE id=296";
//                $bonus_info = $GLOBALS['db']->getAll($sql_bonus);
//                                $sql_deal = "SELECT create_time,money FROM ".DB_PREFIX."deal_load WHERE user_id=1711";
//                    $deal_info = $GLOBALS['db']->getAll($sql_deal);
//                echo '<pre>';
//                var_dump($deal_info);die;
        $list   = $result['list'];
        $count       = $result['count'];
        $money_count = $result['money_count'];
        $GLOBALS['tmpl']->assign("list", $list);
        $page        = new Page($count, app_conf("PAGE_SIZE"));   //初始化分页对象
        $p           = $page->show();
        $GLOBALS['tmpl']->assign('pages', $p);
        $GLOBALS['tmpl']->assign('type', $_REQUEST['type']);
        $GLOBALS['tmpl']->assign('money_count', $money_count);
        $GLOBALS['tmpl']->assign("page_title", "我的红包");
        $GLOBALS['tmpl']->assign("inc_file", "inc/uc/uc_bonus.html");
        $GLOBALS['tmpl']->display("page/uc.html");
    }

    //红包提现申请
    public function carry_now()
    {
        $bonus_id = $_REQUEST['bonus_id'];
        $bonus_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_bonus where id = $bonus_id");
        //如果没有充值或者投资 则不允许提现
        $payment_notice_num = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "payment_notice where user_id = " . $GLOBALS['user_info']['id'] . " AND is_paid = 1");
        $deal_load_num      = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "deal_load where user_id = " . $GLOBALS['user_info']['id'] . " AND is_auto = 0");
        if (!$deal_load_num) {
            if($bonus_info['bonus_type']==8){
                $result['status'] = 0;
                $result['info']   = "<div style='font-size:16px;color:#C40000'>在活动期间内，任意投资一笔，可领取红包，红包3个工作日内到账。红包不与其他抵用券、现金券冲突，可同时叠加使用。</div>";
                ajax_return($result);
                die();
            }else{
                $result['status'] = 0;
                $result['info']   = "<div style='font-size:16px;color:#C40000'>您没有任何投资记录，暂时无法提现！</div>";
                ajax_return($result);
                die();
            }

        }
        if (!$payment_notice_num) {
            if($bonus_info['bonus_type']==8){
                $result['status'] = 0;
                $result['info']   = "<div style='font-size:16px;color:#C40000'>在活动期间内，任意投资一笔，可领取红包，红包3个工作日内到账。红包不与其他抵用券、现金券冲突，可同时叠加使用。</div>";
                ajax_return($result);
                die();
            }else{
                $result['status'] = 0;
                $result['info']   = "<div style='font-size:16px;color:#C40000'>您没有任何充值记录，暂时无法提现！</div>";
                ajax_return($result);
                die();
            }
        }


        $obj = MO('Bonus');
		$res = $obj->bonusWithdrawals($GLOBALS['user_info']['id'], $bonus_id);
        if ($res === true) {
            $result['status'] = 1;
            $result['info']   = "<div style='font-size:16px;color:#C40000'>您已成功领取红包，预计3个工作日内到账。</div>";
            ajax_return($result);
            die();
        } else {
            $result['status'] = 0;
            $result['info']   = "<div style='font-size:16px;color:#C40000'>" . $res . "</div>";
            ajax_return($result);
            die();
        }
    }

}

?>