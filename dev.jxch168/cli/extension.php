/**
*
* 推广定时脚本
*/

<?php
require_once 'init.php';
$time = strtotime("2015-10-22 15:12:30");
$mobile_extension = $GLOBALS['db']->getAll("select id,udid,pburl,source,ip from " . DB_PREFIX . "mobile_extension where state = 2 and create_time>$time and mobile=4 and source!='app_store'");
if (count($mobile_extension) > 0) {
    foreach ($mobile_extension as $mobile) {
//    	$deal_count = $GLOBALS['db']->getOne("select count(1) from ".DB_PREFIX."deal_load where user_id = 
//    							(select u.id from ".DB_PREFIX."user u where u.search_channel='".$mobile["source"]."' and upper(u.mobile_id) = upper('".$mobile["udid"]."') limit 0,1)");
        if ($mobile['source'] == 'wanpu') {
            $sql_str = "select count(*) as c,create_time from " . DB_PREFIX . "user where search_channel='{$mobile['source']}' and upper(mobile_id) = upper('" . $mobile["udid"] . "') and create_time>$time and idno!='' and status=2 limit 1";
        } else {
            $sql_str = "select count(*) as c,create_time from " . DB_PREFIX . "user where search_channel='{$mobile['source']}' and upper(mobile_id) = upper('" . $mobile["udid"] . "') and create_time>$time limit 1";
        }
        $deal_count = $GLOBALS['db']->getRow($sql_str);

        if ($deal_count['c'] > 0) {
            if ($mobile['source'] == 'adwo') {
                $value = open_url(urldecode($mobile['pburl']) . "&activateip=" . $mobile['ip'] . "&acts=" . $deal_count['create_time']);
            } else {
                $value = open_url(urldecode($mobile['pburl']) . '&ip=' . $mobile['ip']);
            }
            if ($value) {
                $state = 3;    //回调成功
            } else {
                $state = 4;      //回调超时
            }

            $sql_extension = "update " . DB_PREFIX . "mobile_extension set state = " . $state . ",test_time=" . TIME_UTC . " where upper(udid) = upper('" . $mobile['udid'] . "')";
            $GLOBALS['db']->query($sql_extension);

            $mobile['state'] = $state;
            file_put_contents(APP_ROOT_PATH . 'log/extension/' . date('Y-m-d') . '_output_callback.log', "[" . date("Y-m-d H:i:s") . "] POST:[" . json_encode($mobile) . "];[return:" . $value . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        }
    }
}

function open_url($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "3") {
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
?> 