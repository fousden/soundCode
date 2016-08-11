<?php

//输出接口数据
function output($data) {
    ob_clean();
    header("Content-Type:text/html; charset=utf-8");
    $r_type = intval($_REQUEST['r_type']); //返回数据格式类型; 0:base64;1;json_encode;2:array
    if (!is_array($data)) {
        $show_err = $data;
        $data = array();
        $data['response_code'] = "0";
        $data['show_err'] = $show_err;
    }
    $data['act'] = ACT;
    $data['func'] = FUNC;
    $url = '';
    foreach ($_POST as $key => $val) {
        $url.="&" . $key . "=" . $val;
    }
    file_put_contents(APP_ROOT_PATH . "log/mapi/mapi_output" . date("Y-m-d") . ".log", "[" . date("Y-m-d H:i:s") . "]url:" . $_SERVER['REQUEST_URI'] . $url . "###returndata:" . json_encode($data) . "\r\n", FILE_APPEND);
    if ($r_type == 0) {
        echo base64_encode(json_encode($data));
    } else if ($r_type == 1) {
        print_r(json_encode($data));
    } else if ($r_type == 2) {
        print_r($data);
    };
    exit;
}

//获取IP地址
function get_real_ip() {
    $ip = false;
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) {
            array_unshift($ips, $ip);
            $ip = FALSE;
        }
        for ($i = 0; $i < count($ips); $i++) {
            if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

function getMConfig() {

    $m_config = $GLOBALS['cache']->get("m_config");
    if (true || $m_config === false) {
        $m_config = array();
        $sql = "select code,val from " . DB_PREFIX . "m_config";
        $list = $GLOBALS['db']->getAll($sql);
        foreach ($list as $item) {
            $m_config[$item['code']] = $item['val'];
        }

        $GLOBALS['cache']->set("m_config", $m_config);
    }
    return $m_config;
}

/**
 * 过滤SQL查询串中的注释。该方法只过滤SQL文件中独占一行或一块的那些注释。
 *
 * @access  public
 * @param   string      $sql        SQL查询串
 * @return  string      返回已过滤掉注释的SQL查询串。
 */
function remove_comment($sql) {
    /* 删除SQL行注释，行注释不匹配换行符 */
    $sql = preg_replace('/^\s*(?:--|#).*/m', '', $sql);

    /* 删除SQL块注释，匹配换行符，且为非贪婪匹配 */
    //$sql = preg_replace('/^\s*\/\*(?:.|\n)*\*\//m', '', $sql);
    $sql = preg_replace('/^\s*\/\*.*?\*\//ms', '', $sql);

    return $sql;
}

function emptyTag($string) {
    if (empty($string))
        return "";

    $string = strip_tags(trim($string));
    $string = preg_replace("|&.+?;|", '', $string);

    return $string;
}

function get_abs_img_root($content) {
    return str_replace("./public/", SITE_DOMAIN ."/public/", $content);
}

function get_abs_url_root($content) {
    $content = str_replace("./", SITE_DOMAIN . APP_ROOT . "/../", $content);
    return $content;
}

function user_check($username_email, $pwd, $is_log = true) {
    if ($username_email && $pwd) {
        $sql = "select *,id as uid from " . DB_PREFIX . "user where (user_name='" . $username_email . "' or email = '" . $username_email . "' or mobile = '" . $username_email . "') and is_delete = 0";
        $user_info = $GLOBALS['db']->getRow($sql);

        $is_use_pass = false;
        if (strlen($pwd) != 32) {
            if ($user_info['user_pwd'] == md5($pwd . $user_info['code']) || $user_info['user_pwd'] == md5($pwd)) {
                $is_use_pass = true;
            }
        } else {
            if ($user_info['user_pwd'] == $pwd) {
                $is_use_pass = true;
            } else if (md5($user_info['user_pwd'] . date("Y-m-d")) == $pwd) {
                $is_use_pass = true;
                //下面的代码为了兼容ios
            } else if (md5($user_info['user_pwd'] . date("Y-m-d", strtotime("+1 year"))) == $pwd) {
                $is_use_pass = true;
            } else if (md5($user_info['user_pwd'] . date("Y-m-d", strtotime("-1 day"))) == $pwd) {
                $is_use_pass = true;
            }
        }
        if ($is_use_pass) {
            es_session::set("user_info", $user_info);
            $GLOBALS['user_info'] = $user_info;
            return $user_info;
        } else {
            if ($is_log) {
                file_put_contents(APP_ROOT_PATH . "log/mapi/user_check" . date("Y-m-d") . ".log", "[" . date("Y-m-d H:i:s") . "]{账户密码匹配失败}email=" . $username_email . "&pwd=" . $pwd . "\r\n", FILE_APPEND);
            }
            return null;
        }
    } else {
        if ($is_log) {
            file_put_contents(APP_ROOT_PATH . "log/mapi/user_check" . date("Y-m-d") . ".log", "[" . date("Y-m-d H:i:s") . "]{账户或者密码为空}email=" . $username_email . "&pwd=" . $pwd . "\r\n", FILE_APPEND);
        }
        return null;
    }
}

function user_login($username_email, $pwd) {
    require_once APP_ROOT_PATH . "system/libs/user.php";
    if (check_ipop_limit(CLIENT_IP, "user_dologin", intval(app_conf("SUBMIT_DELAY")))) {
        $result = do_login_user($username_email, $pwd);
    } else {
        //showErr($GLOBALS['lang']['SUBMIT_TOO_FAST'],$ajax,url("shop","user#login"));
        $result['status'] = 0;
        $result['msg'] = $GLOBALS['lang']['SUBMIT_TOO_FAST'];
        return $result;
    }

    if ($result['status']) {
        //$GLOBALS['user_info'] = $result["user"];
        return $result;
    } else {
        $GLOBALS['user_info'] = null;
        unset($GLOBALS['user_info']);

        if ($result['data'] == ACCOUNT_NO_EXIST_ERROR) {
            $err = $GLOBALS['lang']['USER_NOT_EXIST'];
        }
        if ($result['data'] == ACCOUNT_PASSWORD_ERROR) {
            $err = $GLOBALS['lang']['PASSWORD_ERROR'];
        }
        if ($result['data'] == ACCOUNT_NO_VERIFY_ERROR) {
            $err = $GLOBALS['lang']['USER_NOT_VERIFY'];
        }

        $result['msg'] = $err;
        return $result;
    }
}

/**
 * 手机端 强行判断不然弹出
 */
function checkMobileReturnMobile() {
    $falg = isMobile();

    $cnt = 0;
//    if ($_REQUEST['_m'] == 'ios') {
//        str_replace(array('iPhone'), '', $_SERVER['HTTP_USER_AGENT'], $cnt);
//    } else if ($_REQUEST['_m'] == 'android') {
//        str_replace(array('Android', 'android','_'), '', $_SERVER['HTTP_USER_AGENT'], $cnt);
//    } else if (trim($_REQUEST['from']) == 'wap') {
//        str_replace(array('iPhone', 'Android', 'android','_'), '', $GLOBALS['request']['HTTP_USER_AGENT'], $cnt);
//    }
    str_replace(array('iPhone', 'Android', 'android','_'), '', $_SERVER['HTTP_USER_AGENT'], $cnt);
    if (!$falg || !$cnt) {
        MO("MobileBlacklist")->addMobile($GLOBALS['request']['mobile']);
        $root['response_code'] = 0;
        $root['show_err'] = "手机异常，请安正常途径操作"; //短信未开启
        output($root);
    }
}

?>