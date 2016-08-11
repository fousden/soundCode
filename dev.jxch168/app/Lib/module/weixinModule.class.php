<?php

class weixinModule extends SiteBaseModule
{

    /**
     * 接口平台
     * @var type
     */
    public $platformArr;
    public $ipArr;

    function __construct()
    {
        parent::__construct();
        $this->platformArr = array(
            'renrenyao' => '817ed7a0aadb14839b161060820e419b',
        );
        $this->ipArr       = array(
            '180.168.52.3',
            '140.206.126.42',
            '192.168.2.69',
            '124.74.111.94',
            '121.40.237.112',
            '140.206.33.46',
            '192.168.27.18',
        );
    }

    function index()
    {
        $jl     = (int) $_GET['jl'];
        $mobile = trim($_GET['mobile']);
        $hg     = 0;
        if (1 == $jl) {
            $hg = 50;
        } else if (2 == $jl) {
            $hg = 100;
        } else if (3 == $jl) {
            $hg = 200;
        } else if (4 == $jl) {
            $hg = 500;
        }
        $user_bonus = array(
            'mobile'      => $mobile,
            'hb'          => $hg,
            'create_time' => time(),
        );
        $sql        = "select * from " . DB_PREFIX . "weixin_hb where  mobile = '{$mobile}'";
        $tmpFlag    = $GLOBALS['db']->getAll($sql);
        if (empty($tmpFlag)) {
            $sql  = "INSERT INTO " . DB_PREFIX . "weixin_hb (mobile,hb,create_time) VALUE ('{$user_bonus['mobile']}','{$user_bonus['hb']}','{$user_bonus['create_time']}')";
            $flag = $GLOBALS['db']->query($sql); //插入红包数据
        } else {
            echo "-1";
            exit;
        }
        echo "1";
        exit;
    }

    /**
     * 摇一摇活动（类似可重复）
     * 初始活动人人摇
     */
    public function yaoyiyao()
    {
//        if (!array_search(get_client_ip(), $this->ipArr)) {
//            $this->resMsg(6);
//        }
        $source = trim($_GET['source']);
        if (!isset($this->platformArr[$source])) {
            $this->resMsg(1);
        }

        $ikey = trim($_GET['ikey']);
        if ($this->platformArr[$source] != $ikey) {
            $this->resMsg(2);
        }
        $mobile     = trim($_GET['mobile']);
        $coupon_num = intval($_GET['coupon_num']);
        $weixinid   = trim($_GET['weixinid']);
        if (empty($mobile)) {
            $this->resMsg(3);
        }
        if (empty($coupon_num)) {
            $this->resMsg(4);
        }
        if (empty($weixinid)) {
            $this->resMsg(5);
        }
        $other_info = array('weixinid' => $weixinid);
        $userInfo   = MO('User')->userInfoByMobile($mobile);

        //中间表做为日志使用
        $obj    = MO('BonusMiddel');
        $setArr = array(
            'mobile'      => $mobile,
            'reward_name' => "50元现金红包",
            'money'       => "50.00",
            'bonus_type'  => 5,
            'cash_type'   => 0,
            'min_limit'   => 5000,
            'remark'      => "人人摇获取",
            'other_info'  => json_encode($other_info),
        );
        if ($userInfo) {
            $setArr['status'] = 1;
        }
        $obj->add($setArr);

        if ($userInfo) {
            $obj    = MO('Bonus');
            $setArr = array('user_id'     => $userInfo['id']
                , 'reward_name' => "50元现金红包",
                'money'       => "50.00",
                'bonus_type'  => 5,
                'cash_type'   => 0,
                'min_limit'   => 5000,
                'remark'      => "人人摇获取",
            );
            $obj->add($setArr);
        } else {

        }
        $this->resMsg(0);
    }

    /**
     * 消息统一处理
     * @param type $code
     */
    private function resMsg($code)
    {
        $msgArr = array(
            0 => '处理成功',
            1 => '平台号不对',
            2 => '验证密钥不对',
            3 => '手机号不能为空',
            4 => '券值不能为空',
            5 => '微信ID不能为空',
            6 => '不在可访问的IP列表中',
        );

        echo json_encode(array('code' => $code, 'message' => $msgArr[$code]));
        exit;
    }

}
