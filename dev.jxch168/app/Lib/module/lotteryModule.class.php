<?php

/**
 * 仅用于抽奖活动
 */
class LotteryModule extends SiteBaseModule {

    /**
     * 用于区分活动
     * @var $type 活动类型
     */
    private $type;

    /**
     * 用于之后用于用户的一系列的操作
     * @var $user_info 用户信息
     */
    private $user_info;

    /**
     *
     * @var $title 活动的标题
     */
    private $title;

    /**
     *
     * @var $data 活动的相关参数
     */
    private $data;

    /**
     *
     * @var $prize_conf  中奖的信息
     */
    private $prize_conf;

    /**
     *
     * @var type 临时使用
     */
    private $temp;

    /**
     *
     * @var array $conf 抽奖活动的配置 
     */
    private $conf;

    public function __construct() {
        parent::__construct();
        $this->type = trim(filter_input(INPUT_GET, 'type'));
        $this->temp['email'] = trim($_REQUEST['email']);
        $this->temp['pwd'] = trim($_REQUEST['pwd']);
        $this->type = trim(filter_input(INPUT_GET, 'type'));
        if ($this->temp['email'] && $this->temp['pwd']) {
            $this->user_info = user_check_web($this->temp['email'], $this->temp['pwd']);
        } else {
            $this->user_info = es_session::get("user_info");
        }
        $this->conf = require_once ROOT_PATH . 'data_conf/activityConf/' . $this->type . '_conf.php';
        $this->title = $this->conf['title'];
        $this->temp = '';
    }

    /**
     * 根据url上的type值加载抽奖页面
     */
    public function index() {
        if ($this->user_info['id'] > 0) {
            if (is_string($_REQUEST['share'])) {
                $lottery_number = MO("User")->insert_lottery_number($this->user_info['mobile'], 3)['lottery_number'];
            } else {
                $lottery_number = MO("User")->get_lottery_number($this->user_info['mobile']);
            }
        }
        $GLOBALS['tmpl']->assign("lottery_number", $lottery_number);
        $GLOBALS['tmpl']->assign("user_info", $this->user_info);
        $GLOBALS['tmpl']->assign("page_title", $this->title);
        if (isMobile()) {
            return $GLOBALS['tmpl']->display("inc/activity/" . $this->type . "_wap.html");
        }
        $log_list = $this->get_log_list();
        $show_share_code = share_code(6);
        $GLOBALS['tmpl']->assign("SITE_DOMAIN", SITE_DOMAIN);
        $GLOBALS['tmpl']->assign("log_list", $log_list);
        $GLOBALS['tmpl']->assign("show_share_code", $show_share_code);
        $GLOBALS['tmpl']->display("inc/activity/" . $this->type . ".html");
    }

    /**
     * 添加抽奖次数
     */
    public function add_lottery_number() {
        $this->check_login();
        $this->check_activity_validity();
        $lottery_number_info = MO("User")->insert_lottery_number($this->user_info['mobile'], 1);
        $root['response_code'] = $lottery_number_info['status'];
        $root['lottery_number'] = $lottery_number_info['lottery_number'];
        ajax_return($root);
    }

    /**
     * 执行抽奖动作
     */
    public function do_lottery() {
        //验证是否登录
        $this->check_login();
        //验证活动是否在有效期内
        $this->check_activity_validity();
        //验证抽奖次数是否足够
        $this->check_lottery_number();
        //获取该用户投资的情况
        $deal_load = $this->get_deal_load_list();
        //从活动配置文件中查询出该活动的中奖规则参数集
        $child_param = $this->get_lottery_child_param($deal_load);

        $prize_type = $child_param['prize_type'];
        $this->data['conf_id'] = $child_param['conf_id'];
        $this->data['prize_name'] = getPriceName($prize_type, $child_param['conf_id']);
        //将该用户所有的投资订单中的is_winnig更新为1（即标志着投资订单已经抽过奖）
        $idStr = $this->data['idStr'];
        if ($idStr) {
            $deal_load_deduct_all = $this->conf['param']['deal_load_deduct_all'];
            $where = '';

            if ($deal_load_deduct_all == 'once') {
                $where = ' and id in(' . $idStr . ')';
            }
            if (!MO('DealLoad')->setWinnig($this->user_info['id'], $where)) {
                ajax_return('操作失败！');
            }
        }

        if ($prize_type == 1 || $prize_type == 2) {
            if (!$this->data['obj_id'] = MO("Coupon")->confAdd($this->user_info, ['remark' => $this->title], $this->data['conf_id'])) {
                ajax_return("操作失败！");
            }
        } else if ($prize_type == 4) {
            $this->add_msg_data();
        }

        $root['lottery_number'] = MO("User")->update_lottery_number($this->user_info['mobile']); //将用户的抽奖次数减一
        if (!$this->add_lottery_log_data($child_param)) {
            ajax_return("操作失败!");
        }
        $root['response_code'] = 1;
        $root['num'] = $child_param['prize_id'];
        $root['name'] = $this->data['prize_name'];
        ajax_return($root);
    }

    /**
     * 获取抽奖日志列表
     */
    private function get_log_list() {
        $log_list = MO("Lotterylog")->getLogList($this->type, 10);
        if (count($log_list) < 10) {
            $log_list = MO("Lotterylog")->getLogList(0, 10);
        }
        $this->log_list_format($log_list);
        return $log_list;
    }

    /**
     * 格式化抽奖日志列表数据
     * @param array $log_list
     */
    private function log_list_format(&$log_list) {
        if ($log_list) {
            foreach ($log_list as $key => $val) {
                $log_list[$key]['create_date'] = to_date($val['create_time'], "m-d H:i");
            }
        }
    }

    /**
     * 验证活动的有效性
     * @param string $type 活动类型
     * @return type
     */
    private function check_activity_validity() {
        if (!$activity_info = MO("ActivityConf")->getInfoByType($this->type)) {
            return ajax_return("不在活动期间内，请联系客服！");
        }
    }

    /**
     * 验证是否登录
     */
    private function check_login() {
        if (!$this->user_info) {
            ajax_return('未登录', '-1');
        }
    }

    private function check_lottery_number() {
        if (MO("User")->get_lottery_number($this->user_info['mobile']) <= 0) {
            ajax_return("您的抽奖次数不足！");
        }
    }

    /**
     * 根据传递过来的参数集拼装成where语句
     * @param type $param 参数集
     * @return string 返回where语句
     */
    private function get_lottery_condition($param) {
        $where = '';
        if ($param['repay_time']) {
            $where.=' and d.repay_time' . $param['repay_time'][0] . $param['repay_time'][1] . " ";
        }
        if ($param['create_time']) {
            $where.=' and d.create_time' . $param['create_time'][0] . $param['create_time'][1] . " ";
        }
        if ($param['agency_id']) {
            $where.=' and d.agency_id' . $param['agency_id'][0] . $param['agency_id'][1] . " ";
        }
        if ($param['where']) {
            $where.=$param['where'];
        }
        return $where;
    }

    /**
     * 查询用户符合活动规定的投资记录列表
     * @return array 返回数组
     */
    private function get_deal_load_list() {
        $param = $this->conf['param'];
        $where = $this->get_lottery_condition($param);
        $sql_str = "SELECT dl.money,dl.id FROM " . DB_PREFIX . "deal_load as dl left join " . DB_PREFIX . "deal as d on(dl.deal_id=d.id) where dl.user_id=" . $this->user_info['id'] . " and dl.is_winning=0 " . $where;
        if ($param['compute_type'] == 'once') {
            $sql_str.=" order by money desc limit 1";
        }
        $this->prize_conf = $this->conf['prize_conf'];
        return $GLOBALS['db']->getAll($sql_str);
    }

    /**
     * 根据订单信息与配置文件计算出该中奖信息
     * @param array $deal_load
     * @return array 返回中奖信息
     */
    private function get_lottery_child_param($deal_load) {
        $money = 0;
        $myriad = 10000;
        $idStr = '';
        if ($deal_load) {
            $ids = array();
            foreach ($deal_load as $val) {
                $money+=$val['money'];
                $ids[] = $val['id'];
            }
            $idStr = implode(",", $ids);
        }
        $this->data = ['money' => $money, 'idStr' => $idStr];
        //将数组的顺序打乱
        shuffle($this->prize_conf);
        foreach ($this->prize_conf as $parameter) {
            if ($money >= $parameter['start'] * $myriad && $money < $parameter['end'] * $myriad) {
                return $parameter;
            }
        }
    }

    /**
     * 添加站内信
     * @return bool
     */
    private function add_msg_data() {
        //          $msg_data['title']="恭喜您中奖了";
        $msg_data['content'] = "您于" . date('Y-m-d H:i:s') . "参与" . $this->title . "抽奖，抽得奖品" . $this->data['prize_name'] . "。稍后会有客服联系您并确认发放活动奖励！";
        $msg_data['to_user_id'] = $this->user_info['id'];
        $msg_data['is_notice'] = 1;
        $msg_data['create_time'] = time();
        return $GLOBALS['db']->autoExecute(DB_PREFIX . "msg_box", $msg_data, "INSERT");
    }

    /**
     * 添加抽奖日志
     * @param array $child_param 
     * @return bool 返回bool结果
     */
    private function add_lottery_log_data($child_param) {
        $lottery_log_data['lotter_id'] = $this->type;
        $lottery_log_data['mobile'] = $this->user_info['mobile'];
        $lottery_log_data['prize_name'] = $this->data['prize_name'];

        $lottery_log_data['prize_type'] = isset($child_param['prize_type']) ? $child_param['prize_type'] : 4;
        $lottery_log_data['prize_desc'] = $this->title;
        $lottery_log_data['obj_id'] = isset($this->data['obj_id']) ? $this->data['obj_id'] : 0;
        $lottery_log_data['prize_img'] = $child_param['conf_id'];

        $lottery_log_data['use_deal_load_id'] = $this->data['idStr'];
        $lottery_log_data['use_money'] = $this->data['money'];
        return MO("Lotterylog")->add($lottery_log_data);
    }

}
