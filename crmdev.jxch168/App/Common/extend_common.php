<?php

/*
 * 以后新增全局函数写在这个文件中
 */

//发送一条短信
function send_sms($mobile = 0, $content = '') {
    $host_arr = explode('.', $_SERVER['HTTP_HOST']);
    if (CONDITION == 'dev' || CONDITION == 'test') {
        return true;
    }
    $sms = F('sms');
    $str = "http://61.130.7.220:8023/MWGate/wmgw.asmx/MWGate/wmgw.asmx/MongateSendSubmit?userId=%s&password=%s&pszMobis=%s&pszMsg=%s&iMobiCount=%s&pszSubPort=*&MsgId=14";
    $url = sprintf($str, $sms['uid'], $sms['passwd'], $mobile, $content, 1);
    $str = file_get_contents($url);
    $sasa = (array) simplexml_load_string($str);
    $strlen = strlen($sasa[0]);
    if ($strlen == 19 || $strlen == 20) {
        return true;
    } else {
        return false;
    }
}

//输出接口数据
function output($root) {
    header("Content-Type:text/html; charset=utf-8");
    if (!is_array($root)) {
        $errmsg = $root;
        $root = array();
        $root['code'] = "0";
        $root['errmsg'] = $errmsg;
    }
    $root['m'] = strtolower(MODULE_NAME);
    $root['a'] = strtolower(ACTION_NAME);
    $r_type = intval($_REQUEST['r_type']); //返回数据格式类型; 0:base64;1;json_encode;2:array
    if ($r_type == 1) {
        die(json_encode($root));
    } else if ($r_type == 2) {
        echo '<pre>';
        var_dump($root);
        echo '</pre>';
        die;
    } else if ($r_type == 3) {
        echo '<pre>';
        print_r($root);
        echo '</pre>';
        die;
    } else {
        die(base64_encode(json_encode($root)));
    }
}

function checkLogin() {
    if (!$_REQUEST['sid']) {
        $root['code'] = "-1";
        $root['errmsg'] = "未登录";
        output($root);
    }
    $sid = $_REQUEST['sid'];
    session_id($sid);
    session_start();
    if (!session('user_name')) {
        $root['code'] = '-1';
        $root['errmsg'] = "未登录";
        output($root);
    } else {
        $user_info = M("user")->field("password,sid")->where(array('user_id' => trim(session('user_id'))))->find();
        if ($user_info['password'] != session('password')) {
            $root['code'] = '-1';
            $root['errmsg'] = "未登录";
            output($root);
        }
//        else if($sid != $user_info['sid']){
//            $root['code'] = '-1';
//            $root['errmsg'] = "您的账号在其他设备中已经登录！";
//            output($root);
//        }
    }
    return $_SESSION;
}

function checkLogin1() {
    if (!$_REQUEST['sid']) {
        $root['code'] = "-1";
        $root['errmsg'] = "未登录";
        output($root);
    }
    $user_info = M("user")->where(array('name' => "dch"))->find();
    return $user_info;
}

//根据时间戳转换时间（如果为几秒前，几分钟前。。。）
function time_tran($show_time) {
    $now_time = time();
    $the_time = date("m-d H:i", $show_time);
    $dur = $now_time - $show_time;
    if ($dur < 0) {
        return $the_time;
    } else {
        if ($dur < 60) {
            return "刚刚";
        } else {
            if ($dur < 3600) {
                return floor($dur / 60) . '分钟前';
            } else {
                if ($dur < 86400) {
                    return floor($dur / 3600) . '小时前';
                } else {
                    if ($dur < 259200) {//3天内
                        return floor($dur / 86400) . '天前';
                    } else {
                        return $the_time;
                    }
                }
            }
        }
    }
}

//文件上传方法
function uploading_files($file_data) {
    //上传产品主图和副图至服务器
    if (array_sum($file_data['size'])) {
        //如果有文件上传 上传附件
        import('@.ORG.UploadFile');
        //导入上传类
        $upload = new UploadFile();
        //设置上传文件大小
        $upload->maxSize = 20000000;
        //设置附件上传目录
        $dirname = UPLOAD_PATH . date('Ym', time()) . '/' . date('d', time()) . '/';
        $upload->allowExts = array('jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'); // 设置附件上传类型
        if (!is_dir($dirname) && !mkdir($dirname, 0777, true)) {
            $result['upload_status'] = 0;
            $result['status'] = 0;
            $result['info'] = L('ATTACHMENTS TO UPLOAD DIRECTORY CANNOT WRITE');
            $result['url'] = $_SERVER['HTTP_REFERER'];
            return $result;
        }
        $upload->savePath = $dirname;

        if (!$upload->upload()) {// 上传错误提示错误信息
            $result['upload_status'] = 0;
            $result['status'] = 'error';
            $result['info'] = $upload->getErrorMsg();
            $result['url'] = $_SERVER['HTTP_REFERER'];
        } else {// 上传成功 获取上传文件信息
            /* foreach($info as $iv){
              if($iv['key'] == 'main_pic'){
              //主图
              $img_data['is_main'] = 1;
              }else{
              //副图
              $img_data['is_main'] = 0;
              }
              $img_data['product_id'] = $product_id;
              $img_data['name'] = $iv['name'];
              $img_data['save_name'] = $iv['savename'];
              $img_data['size'] = sprintf("%.2f", $iv['size']/1024);
              $img_data['path'] = $iv['savepath'].$iv['savename'];
              $img_data['create_time'] = time();
              $img_data['listorder'] = intval($m_product_images->max('listorder'))+1;
              } */
            $info = $upload->getUploadFileInfo();
            $result['upload_status'] = 1;
            $result['status'] = 'success';
            $result['upload_data'] = $info;
            $result['info'] = "文件上传成功！";
            $result['url'] = $_SERVER['HTTP_REFERER'];
        }
        return $result;
    }
}

/**
 * 获取报表年份数据
 */
function reportYearList() {
    $beginYear = 2015;
    $nowYear = date('Y');
    $years = $nowYear - $beginYear + 1;
    $yearKV = array();
    for ($i = 0; $i <= $years; $i++) {
        if ($i == $years) {
            $yearKV[$nowYear] = '本年';
        } else {
            $yearKV[$nowYear - $i] = $nowYear - $i;
        }
    }
    return $yearKV;
}

/**
 * 获取门店列表
 * @param type $all 为TURE 为所有，FALSE为去除总部相关部门
 * @return type
 */
function departmentList($all = false) {
    $department_list = M('roleDepartment')->where('parent_id = %d', 0)->select();
    if ($all == false) {
        array_shift($department_list);
        array_shift($department_list);
        array_shift($department_list);
    }
    return $department_list;
}

/**
 * 本周
 */
function thisWeek() {
    $weekToday = date('N');
    $weekbegin = date('Y-m-d', strtotime(" - " . ($weekToday - 1) . " day"));
    $weekEnd = date('Y-m-d', strtotime(" + " . (7 - $weekToday) . " day"));
    return array($weekbegin, $weekEnd);
}

/**
 * 本周
 */
function thisMonth() {
    $monthbegin = date('Y-m-' . '01');
    $monthEnd = date('Y-m-t');
    return array($monthbegin, $monthEnd);
}

function thisYear() {
    $yearBegin = date('Y-' . '01-01');
    $yearEnd = date('Y-' . '12-31');
    return array($yearBegin, $yearEnd);
}

function gen_qrcode($str, $logo, $size = 5, $filename) {
    $root_dir = APP_ROOT_PATH . "public/images/qrcode/";
    if (!is_dir($root_dir)) {
        @mkdir($root_dir);
        @chmod($root_dir, 0777);
    }
    if ($filename == '') {
        $filename = md5($str . "|" . $size);
    }
    $hash_dir = $root_dir . 'c' . substr(md5($filename), 0, 1) . "/";
    if (!is_dir($hash_dir)) {
        @mkdir($hash_dir);
        @chmod($hash_dir, 0777);
    }
    $filesave = $hash_dir . $filename . '.png';
    if (!file_exists($filesave)) {
        require_once APP_ROOT_PATH . "system/phpqrcode/qrlib.php";
        QRcode::png($str, $filesave, 'Q', $size, 2);
    }
    $QR = APP_ROOT_PATH . "public/images/qrcode/c" . substr(md5($filename), 0, 1) . "/" . $filename . ".png";

    if ($logo) {
        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
    }
    //带logo的二维码
    $new_logo = APP_ROOT_PATH . "public/images/qrcode/c" . substr(md5($filename), 0, 1) . "/" . $filename . ".png";
    imagepng($QR, $new_logo);
    //显示使用的url
    $show_qr_logo = "/public/images/qrcode/c" . substr(md5($filename), 0, 1) . "/" . $filename . ".png";
    return $show_qr_logo;
}

function fundsGangway($key = '') {
    $arr = array(
        1 => '现代金融控股',
        2 => '中汇支付',
        3 => '深圳瑞银信',
        4 => '钱宝科技',
        5 => '上海富友',
        6 => '通连支联',
        7 => '嘉连支联',
        8 => '续签',
    );
    if ($key) {
        return isset($arr[$key]) && $arr[$key] ? $arr[$key] : '';
    }
    return $arr;
}

function get_funds_gangway_name($data){
    switch($data){
        case 1:
            return "现代金融控股";
            break;
        case 2:
            return "中汇支付";
            break;
        case 3:
            return "深圳瑞银信";
            break;
        case 4:
            return "钱宝科技";
            break;
        case 5:
            return "上海富友";
            break;
        case 6:
            return "通连支联";
            break;
        case 7:
            return "嘉连支联";
            break;
        case 8:
            return "续签";
            break;
    }
}

/**
 * 数字金额转换成中文大写金额的函数
 * String Int $num 要转换的小写数字或小写字符串
 * return 大写字母
 * 小数位为两位
 * */
function get_amount($num) {
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "数据太长，没有这么大的钱吧，检查下";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int) $num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j - 3;
            $slen = $slen - 3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    if (empty($c)) {
        return "零元整";
    } else {
        return $c . "整";
    }
}


//根据身份证获取性别
function getSexByIdno($idno) {
    return substr($idno, (strlen($idno) == 15 ? -1 : -2), 1) % 2 ? '1' : '0';
}

function idinfo($idno) {
    $length = strlen($idno);
    $arr = array();
    if ($length == 15) {
        $arr['byear'] = '19' . substr($idno, 6, 2);
        $arr['bmonth'] = substr($idno, 8, 2);
        $arr['bday'] = substr($idno, 10, 2);
        $arr['sex'] = (substr($idno, -1, 1)) % 2 ? '1' : '0';
    }
    if ($length == 18) {
        $arr['byear'] = substr($idno, 6, 4);
        $arr['bmonth'] = substr($idno, 10, 2);
        $arr['bday'] = substr($idno, 12, 2);
        $arr['sex'] = (substr($idno, -2, 1)) % 2 ? '1' : '0';
    }
    return $arr;
}
