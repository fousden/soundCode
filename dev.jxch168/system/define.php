<?php
define('CONDITION', 'dev');// dev , test ,     prepublication,produce
if(CONDITION == 'dev')
{
    define("CLI_DOMAIN","http://dev.jxch168.com/");
    define("UMENG_MODE","false");
}
if(CONDITION == 'test')
{
    define("CLI_DOMAIN","http://test.jxch168.com/");
    define("UMENG_MODE","false");
}
if(CONDITION == 'produce')
{
   define("CLI_DOMAIN","http://www.jxch168.com/");
   define("UMENG_MODE","true");
}

if (isset($_GET['dbg']) && $_GET['dbg'] = 'jxch168' . date('Ymd')) {
    define("IS_DEBUG", 1);
    define("SHOW_DEBUG", 1);
    define("SHOW_LOG", 1);
} else {
      define("IS_DEBUG", 0);
    define("SHOW_DEBUG", 0);
    define("SHOW_LOG", 0);
}
define("MAX_DYNAMIC_CACHE_SIZE",1000);  //动态缓存最数量
define("SMS_TIMESPAN",60);  //短信验证码发送的时间间隔
define("SMS_EXPIRESPAN",300);  //短信验证码失效时间
define("TIME_UTC",get_gmtime());   //当前UTC时间戳
define("CLIENT_IP",get_client_ip());  //当前客户端IP
define("SITE_DOMAIN",get_domain());   //站点域名
define("MAX_LOGIN_TIME",3600);  //登录的过期时间
define("SESSION_TIME",3600); //session超时时间
define("PAY_LOG_NAME","jzh01"); //付款方登录名
define("PAY_NAME","上海华陌通金融信息服务有限公司"); //付款方中文名称

define('FUYOU_MCHNT_CD', '0002900F0041270');
define('FUYOU_MCHNT_FR', '13999999999'); // 登录 Id 或法人账号
define('FUYOU_URL', 'http://www-1.fuiou.com:9057/jzh/');
define('FUYOU_PREFIX', 'JXCH');
define('FUYOU_ORDER_PREFIX', FUYOU_PREFIX . 'ORDER');
define('FUYOU_BINDCARD_PREFIX', FUYOU_PREFIX . 'BINDCARD');
define('FUYOU_DEAL_LOAD_PREFIX', FUYOU_PREFIX . 'DEALLOAD');
define('FUYOU_DEAL_LOAD_CALLBACK_PREFIX', FUYOU_PREFIX . 'DEALLOADCALLBACK');
define('FUYOU_BONUS_TRANSFER_PREFIX', FUYOU_PREFIX . 'BONUSTRANSFERBMU');
define('FUYOU_DEAL_TRANSFER_BMU', FUYOU_PREFIX . 'TRANSFERBMU');
//富友充值提现记录
define('FUYOU_INCHARGE_CARRY_RECORD', FUYOU_PREFIX . 'INCHARGERECORD');
//富友交易记录
define('FUYOU_TRADING_RECORD', FUYOU_PREFIX . 'TRADINGRECORD');
//修改银行卡流水号
define('FUYOU_CHANGE_BANK_PREFIX', FUYOU_PREFIX . 'CHANGEBANK');
define('FUYOU_CHANGE_MOBILE_PREFIX', FUYOU_PREFIX . 'CHANGEMOBILE');
define('FUYOU_FIND_BANK_INFO_PREFIX', FUYOU_PREFIX . 'FINDBANKINFO');
define('FUYOU_CASH_SERIAL_NUMBER_PREFIX', FUYOU_PREFIX . 'CASHSERIALNUMBER');
define('AUTO_USER_ID',1);
define('AUTO_USER_NAME','fanwe');
//控制自动投标用户数量
define('AUTO_USER_NUM',200);
//判断是不是HTTPS协议
//if ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) {
//    define('FUYOU_CALLBACK_URL','https://jxch.5pa.cn/');
//}else{
//    define('FUYOU_CALLBACK_URL','http://jxch.5pa.cn/');
//}
define('FUYOU_CALLBACK_URL',SITE_DOMAIN .'/');
// 投保接口
define("CHNNEL_CODE",'jinxiang1510');
define("PRODUCT_CODE",'pingan_wyeh');
define("SIGN","3ba2fba613f4068e20d9f6c95ab7b2fe");

//短信余量 最低控制线 到达临界点则报警
define("SMS_MIN",5000);
//途虎活动券余量 到达临界点则报警
define("TU_NUM",500);

//****************管理员短信 邮件配置*********************
//异常数据预警短信通知用户
define("INFO_USER",'13122905536');
//资金同步预警 邮件接收异常
define("INFO_EMAIL_USER",'429194571@qq.com');//可以是多个管理员邮箱，以逗号隔开

//****************客服运营人员短信 邮件配置*********************
//短信通知运营人员
define("OPERATE_USER",'13122905536');
//邮件通知运营人员
define("OPERATE_EMAIL_USER",'429194571@qq.com');//可以是多个管理员邮箱，以逗号隔开

//投资意向的邮件通知
define("DEAL_EMAIL_USER",'649578964@qq.com');//可以是多个管理员邮箱，以逗号隔开
?>
