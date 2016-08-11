<?php

class aa_consoleModule extends SiteBaseModule {

    public function __construct() {
        parent::__construct();
        $host = $_SERVER['HTTP_HOST'];
        if ((strpos($host, "dev") === false && strpos($host, "test") === false) || !in_array(CONDITION, array('dev', 'test'))) {
            die;
        }
        $login_arr = array("send_user_coupon");
        if (in_array($_REQUEST['act'], $login_arr)) {
            $this->user_info = es_session::get("user_info");
            if (!$this->user_info) {
                ajax_return("请登录后进行操作！");
            }
        }
    }

    //主控制器主页
    public function index() {
        $menu_list = array(
            '0' => array(
                array("dch后台页面", "/m.php"),
                array("dch前台首页", "/"),
            ),
            '1' => array(
                array("发放dch账户红包", "send_user_bonus"),
                array("执行sql语句", "execute_sql"),
                array("前台登录", "home_login"),
                array("后台登录", "adm_login"),
                array("添加标的", "add_deal"),
                array("投标", "add_deal_load&moeny=10000"),
                array("切换jxch168.recovery库", "barterDb&dbname=jxch168.recovery"),
                array("切换fanwe库", "barterDb&dbname=fanwe"),
                array("清除所有的缓存", "clear_data"),
            ),
            '2' => array(
                array("发放100元抵现劵", "send_user_coupon&coupon_type=2&is_ove=0"),
                array("发放过期的100元抵现劵", "send_user_coupon&coupon_type=2&is_ove=1"),
                array("发放的0.5收益劵", "send_user_coupon&coupon_type=1&is_ove=0"),
                array("发放过期的0.5收益劵", "send_user_coupon&coupon_type=1&is_ove=1"),
            ),
        );
        $a = '1111';
        $GLOBALS['tmpl']->assign("menu_list", $menu_list);
        $GLOBALS['tmpl']->assign("page_title", '控制台');
        $GLOBALS['tmpl']->display("aa_console.html");
    }

    //切换数据库
    function barterDb() {
        $dbname = isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : '';
        $filename = APP_ROOT_PATH . 'public/db_config.php';
        $data = preg_replace("/'DB_NAME'(.*)=>(.*)'(.*)'/i", "'DB_NAME'$1=>$2'" . $dbname . "'", file_get_contents($filename));
        file_put_contents($filename, $data);
        clear_cache();
        $this->login();
        ajax_return(['response_code' => 1]);
    }

    //发送用户的优惠劵
    public function send_user_coupon() {
        $coupon_type = isset($_REQUEST['coupon_type']) ? trim($_REQUEST['coupon_type']) : 2;
        $is_ove = isset($_REQUEST['is_ove']) ? trim($_REQUEST['is_ove']) : 0;
        if ($coupon_type == 2) {
            $face_value = "100";
            $coupon_name = "100元抵现券";
            $coupon_desc = "100元抵用券（满50000元可使用）";
            $min_limit = "50000.00";
        } else if ($coupon_type == 1) {
            $face_value = "0.50";
            $coupon_name = "+0.5%收益券";
            $coupon_desc = "投满10万可用";
            $min_limit = "100000.00";
        }
        if ($is_ove) {
            $start_time = strtotime("-3month");
            $end_time = time();
        } else {
            $start_time = time();
            $end_time = strtotime("+3month");
        }

        $user_id = $this->user_info['id'];
        $user_name = $this->user_info['user_name'];
        $str_str = "INSERT INTO `fanwe_user_coupon` ( `user_id`, `user_name`, `face_value`, `coupon_type`, `coupon_flag`, `coupon_name`, `coupon_desc`, `status`, `min_limit`, `load_id`, `gain_time`, `start_time`, `end_time`, `orderby`, `remark`) VALUES ( '$user_id', '$user_name', '$face_value', '$coupon_type', 'lottery', '$coupon_name', '$coupon_desc', '1', '$min_limit', '0', '0'," . $start_time . ", " . $end_time . ", '0', '开挂操作');
";
        $GLOBALS['db']->query($str_str);
        $root['response_code'] = 1;
        ajax_return($root);
    }

    //发放红包
    public function send_user_bonus() {
        $sql_str = "INSERT INTO `fanwe`.`fanwe_user_bonus` ( `deal_id`, `deal_load_id`, `user_id`, `reward_name`, `money`, `bonus_type`, `cash_type`, `cash_period`, `verify_status`, `status`, `verify_time`, `generation_time`, `apply_time`, `release_time`, `release_date`, `act_relase_time`, `min_limit`, `start_time`, `end_time`, `is_effect`, `remark`) VALUES ('', '', '861', '5元现金红包', '5.00', '3', '1', '3', '0', '0', '0', '1443095353', '1458195859', '1458455059', '1458403200', '0', '500.00', '1443095353', '1490871353', '1', '大转盘抽奖获得')
";
        $GLOBALS['db']->query($sql_str);
        $root['response_code'] = 1;
        ajax_return($root);
    }

    public function add_deal_load() {
        $sql_str = "INSERT INTO `fanwe_deal_load` ( `deal_id`, `user_id`, `coupon_id`, `pure_interests`, `coupon_interests`, `coupon_cash`, `act_interests`, `user_name`, `money`, `create_time`, `is_repay`, `is_rebate`, `is_auto`, `pP2PBillNo`, `pContractNo`, `pMerBillNo`, `is_has_loans`, `msg`, `is_old_loan`, `create_date`, `rebate_money`, `is_winning`, `income_type`, `income_value`, `bid_score`, `contract_no`, `contract_no_flag`, `order_source`, `bonus_withdrawals`) VALUES ( '767', '861', '0', '13000.00', '0.00', '0.00', '0.00', 'dch'," . $_GET['moeny'] . ", '1456391116', '0', '0', '0', NULL, NULL, NULL, '0', NULL, '0', '2016-02-25', '0.00', '0', '0', '', '2400', '000008554823', '0', '1', '0');
";
        $GLOBALS['db']->query($sql_str);
        ajax_return(['response_code' => 1]);
    }

    //添加标的
    public function add_deal() {
        $this->login();
        $sql = "select deal_sn from " . DB_PREFIX . "deal order by id desc limit 1";
        $deal_sn = $GLOBALS['db']->getOne($sql);
        $deal_sn = "MER" . (str_replace("MER", "", $deal_sn) + 1);
        $url = "http://dch.dev.jxch168.com/m.php?m=Cache&a=clear_data&is_all=1";
        $post = "icon[]=titlecolor&deal_sn=" . $deal_sn . "&name=金享保理商超货款(活动专享标" . date("ymd") . "期)&sub_name=测试标的&user_name=dch&user_id=11&cate_id=1&yield_ratio=1&agency_id=26855&warrant=0&guarantor_margin_amt=0.00&guarantor_amt=0.00&guarantor_pro_fit_amt=0.00&icon[]=&type_id=2&loantype=2&contract_id=3&tcontract_id=2&borrow_amount=1000000&guarantees_amt=0.00&uloadtype=0&min_loan_money=100&max_loan_money=0&portion=10000&max_portion=0&repay_time=180&repay_time_type=0&rate=13&enddate=3&description&risk_rank=0&risk_security=&deal_status=1&start_time=" . date('Y-m-d H:i:s') . "&benxibaozhang=222&bad_time=bad_msg&sort=765&services_fee=5&score=0&manage_fee=0.28&user_loan_manage_fee=0&m=Deal&a=insert&is_effect=1";
        $strCookie = "PHPSESSID=" . $_COOKIE['PHPSESSID'];
        $this->curl($url, '', $strCookie, '', $post);
        ajax_return(['response_code' => 1]);
    }

    //清除所有的缓存
    public function clear_data() {
        clear_cache();
        ajax_return(['response_code' => 1]);
    }

    //执行sql
    public function execute_sql() {
        $sql = "update `fanwe_deal_load` set is_winning=0";
        $GLOBALS['db']->query($sql);
        ajax_return(['response_code' => 1]);
    }

    //前台登录
    public function home_login() {
        $user_info['id'] = "861";
        $user_info['user_name'] = "dch";
        es_session::set("user_info", $user_info);
        ajax_return(['response_code' => 1]);
    }

    //后台登录
    public function adm_login() {
        $this->login();
        ajax_return(['response_code' => 1]);
    }

    //后台登录
    private function login($adm_name='admin') {
        $data['adm_name'] = $adm_name;
        $sql_str="SELECT id FROM ".DB_PREFIX."admin where adm_name='$adm_name'";
        $data['adm_id'] = $GLOBALS['db']->getOne($sql_str);
        unset($sql_str);
        return es_session::set("f20d96e55af10cb7babb177b50f81535", $data);
    }

    //curl请求
    private function curl($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "120") {
        if ($cks && $cksfile) {
            $logstr = "[[cookie]]: There is a NULL bettwn cks and cksfile at one time! \r\n";
            echo $logstr;
            return 0;
        }
        $ch = curl_init(); //初始化一个curl资源(resource)
        curl_setopt($ch, CURLOPT_URL, $URL); //初始化一个url
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $tmout); //设置连接时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $tmout); //设置执行时间

        if ($ip) { //设置代理服务器
            curl_setopt($ch, CURLOPT_PROXY, $ip);
        }
        if ($cksfile) { //设置保存cookie 的文件路径
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cksfile); //读上次cookie
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cksfile); //写本次cookie
        }
        if ($cks) { //设置cookies字符串，不要与cookie文件同时设置
            curl_setopt($ch, CURLOPT_COOKIE, $cks);
        }
        if ($ref) { //url reference
            curl_setopt($ch, CURLOPT_REFERER, $ref);
        }

        if ($post) { //设置post 字符串
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $fl); //设置是否允许页面跳转 1 跳转，0 不跳转
        curl_setopt($ch, CURLOPT_HEADER, $hder); //设置是否返回头文件 1返回，0 不返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //
        curl_setopt($ch, CURLOPT_NOBODY, $nbd); //设置是否返回body信息，1 不返回，0 返回
        //设置用户浏览器信息
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)');

        //执行
        $re = curl_exec($ch); //

        if ($re === false) { //检错
            $logstr = "[[curl]]: " . curl_error($ch);
            echo $logstr;
        }
        curl_close($ch); //关闭curl资源
        return $re; //返回得到的结果
    }

}
