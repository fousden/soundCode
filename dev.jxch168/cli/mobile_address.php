<?php
/**
 *
 * 手机归宿地查询
 *
 * User: ningchengzeng
 * Date: 15/7/17
 * Time: 下午4:13
 */
require_once 'init.php';
//归属地更新队列
$mobile_list = $GLOBALS['db']->getAll("select mobile from fanwe_user usr
    where mobile is not null and trim(mobile) != ''
      and not exists(
          select mobile from fanwe_user_mobile_homeaddress addr where addr.mobile = usr.mobile)");

if(count($mobile_list)>0){

    foreach($mobile_list as $mobile){
        $xml_uri = "http://life.tenpay.com/cgi-bin/mobile/MobileQueryAttribution.cgi?chgmobile=".$mobile['mobile'];
        $body = open_url($xml_uri);

        $xml = simplexml_load_string($body);
        $xml = (array)$xml;

        if('0' == $xml['retcode']){
            $mobile['city'] = $xml['city'];
            $mobile['province']= $xml['province'];
            $mobile['telecom']= $xml['supplier'];

            $GLOBALS['db']->autoExecute(DB_PREFIX."user_mobile_homeaddress",$mobile);
        }
    }
}

function open_url($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "120")
{//,$ctimeout="60
    //echo $URL . "\r\n<BR>";
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