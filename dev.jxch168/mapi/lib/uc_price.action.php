<?php

/**
 *
 * @api {get} ?act=uc_price&r_type=1&email=dch&pwd=123456&status=0 奖品展示页
 * @apiName 奖品展示页
 * @apiGroup jxch
 * @apiVersion 1.0.0
 * @apiDescription 请求url
 *
 * @apiParam {string} act 动作{uc_price}
 * @apiParam {string} email 账户名
 * @apiParam {string} pwd 密码
 * @apiParam {string} status 奖品发放状态,不传则默认为待发放
 * @apiParam {string} p 分页页码 不传默认为第一页
 * @apiParam {string} page_size 每页展示数目
 *
 * @apiSuccess {string} response_code 结果码
 * @apiSuccess {string} show_err 消息说明
 * @apiSuccess {string} address_status 是否有地址 1为有地址0为没有地址
 * @apiSuccess {string} count 总记录条数
 * @apiSuccess {string} lottery_info['create_time'] 获奖时间
 * @apiSuccess {string} lottery_info['mobile'] 手机号
 * @apiSuccess {string} lottery_info['prize_name'] 奖品名称
 * @apiSuccess {string} lottery_info['prize_desc'] 奖品来源
 *
 * @apiSuccessExample 返回示范:
{
"address_status": 1,
"count": "1",
"page_size": 10,
"lottery_info": [
{
"id": "2030",
"lotter_id": "0",
"create_time": "2016-03-21 12:19:37",
"mobile": "18612356988",
"prize_name": "iPad Air 2 64G",
"prize_desc": "金享市场合作活动",
"prize_type": "4",
"obj_id": "0",
"status": "0",
"use_deal_load_id": "10062,10063,10064,10067,10068,10069,10070,10071,10072,10075,10079,10080,10081,10082,10083,10094,10096,10097,10099,10100,10101,10102,10103,10105,10108,10113,10120,10121,10172,10173,10174,10175,10176,10177,10178,10181,10186",
"use_money": "12298319",
"is_purchase": "0"
}
],
"act": "uc_price",
"func": "index"
}
 */

class uc_price {

    public $email;
    public $pwd;
    public $user_id;

    public function __construct(){
        $this->email = strim($GLOBALS['request']['email']);
        $this->pwd = strim($GLOBALS['request']['pwd']);
        $user = user_check($this->email,$this->pwd);
        $this->user_id = intval($user['id']);
        if($this->user_id<=0){
            $root['response_code'] = '0';
            $root['show_err'] ="未登录";
            $root['user_login_status'] = '0';
            output($root);
        }

    }
    public function index() {
        $root = array();
        $user_id = $this->user_id;
        if($user_id>0){
            // 判断是否有地址
            $sql = "select * from ".DB_PREFIX."user_address where user_id = ".$user_id;
            $address_info = $GLOBALS['db']->getRow($sql);
            if($address_info){
                // 如果存在地址则
                $root['address_status']='1';
            }else{
                // 不存在地址
                $root['address_status']='0';
            }
            // 查出此用户手机
            $sql = "select mobile from ".DB_PREFIX."user where id = ".$user_id;
            $mobile = $GLOBALS['db']->getOne($sql);
            // 从lottery_log表中查出此手机号码的获奖记录分页
            $status = isset($_REQUEST['status'])? trim($_REQUEST['status']) : 0;// 默认为待发放
            $sql = "select count(*) as count from ".DB_PREFIX."user_lottery_log where prize_type=4 and mobile=".$mobile." and status=".$status;
            $count = $GLOBALS['db']->getOne($sql);
//            $root['count'] = $count; // 返回总数据条数
            $page_size = 10; // 分页数
            $page_num = ceil($count/10); // 总页数
            $root['page_num'] = (string)$page_num;
            $page = intval($_REQUEST['p']); // 页码
            if($page==0)
                $page = 1;
            $p = $page;
            $root['p'] = (string)$p; // 当前为第几页
            $limit = (($page-1)*$page_size).",".$page_size;
            // 查询的时候判断是否是选择了待发放或者已发放
            $GLOBALS['tmpl']->assign("status", $status);
            $sql = "select * from ".DB_PREFIX."user_lottery_log where prize_type=4 and status=".$status." and mobile=".$mobile." limit ".$limit;
//            echo $sql;exit;
            $lottery_info = $GLOBALS['db']->getALL($sql);
//            echo "<pre>";
//            print_r(SITE_DOMAIN);exit;
            foreach($lottery_info as $key => $val){
                $lottery_info[$key]['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
                $path="front/images/activity/prize_img/".$val['prize_img'].".png";
                if(!file_exists(APP_ROOT_PATH.$path)){
                    $lottery_info[$key]['price_img'] = SITE_DOMAIN."/front/images/activity/prize_img/0.png";
                }else{
                    $lottery_info[$key]['price_img']=SITE_DOMAIN.'/'.$path;
                }
            }
//            var_dump(APP_ROOT_PATH."front/images/activity/prize_img/1.png",file_exists(."front/images/activity/prize_img/1.png"));die;
            $root['lottery_info'] = $lottery_info;
            output($root);
        }
    }

}

?>
