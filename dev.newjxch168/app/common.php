<?php

/*
 *
 * 常用的自定义函数
 *
 */

/**
 *
 * @api {路径} /app/common.php 对二维数组指定某个键排序
 * @apiName 将数组以json的形式打印（此方法适用于mapi接口）
 * @apiGroup functions
 * @apiVersion 1.0.0
 * @apiDescription 示例：output("操作错误");
 *
 * @apiParam {array} $data {必传}如传入非数组，则自动加数组的值中增加 $data['response_code'] = 0;
 * @apiParam {array} $r_type get或者post传入，0:base64;1;json_encode;2:array
 *
 * @apiSuccess {array} array 返回处理好的数组
 *
 */
function output($data) {
    ob_clean();
    header("Content-Type:text/html; charset=utf-8");
    $r_type = intval($_REQUEST['r_type']);
    if (!is_array($data)) {
        $show_err = $data;
        $data = array();
        $data['response_code'] = 0;
        $data['show_err'] = $show_err;
    }
    if (!$data['act']) {
        $data['act'] = ACTION_NAME;
    }
    if (MODULE_NAME == 'wap' || MODULE_NAME == 'home') {
        return $data;
    } else if ($r_type == 0) {
        echo base64_encode(json_encode($data));
    } else if ($r_type == 1) {
        print_r(json_encode($data));
    } else if ($r_type == 2) {
        print_r($data);
    }
    exit;
}

/**
 *
 * @api {路径} /system/extend_common.php 对二维数组指定某个键排序
 * @apiName 对二维数组指定某个键排序
 * @apiGroup function
 * @apiVersion 1.0.0
 * @apiDescription 示例：arr_sort(array(array('b'=>'11'),array('b'=>12),'b');
 *
 * @apiParam {array} array {必传}二维数组
 * @apiParam {string} key  {必传}排序的键值
 * @apiParam {string} order 排序的方式 asc是升序 desc是降序（默认升序）
 * @apiParam {string} is_key 返回的键值是否重新按自然排序排序（默认按照传出来的键值排序）
 *
 * @apiSuccess {array} array 返回排序完成后的二维数组
 *
 *
 */

/**
 * 对二维数组指定某个键排序
 * @param $array     array       二维数组
 * @param $key       string      排序的键值
 * @param $order     string      排序的方式 asc是升序 desc是降序（默认升序）
 * @param $is_key   string       返回的键值是否重新按自然排序排序（默认按照传出来的键值排序）
 * @return           array       返回排序完成后的二维数组
 *
 * 例子：arr_sort(array(array('b'=>'11'),array('b'=>12),'b');
 */
function arr_sort($array, $key, $order = "asc", $is_key = false) {
    $arr_nums = $arr = array();
    foreach ($array as $k => $v) {
        $arr_nums[$k] = $v[$key];
    }
    if ($order == 'asc') {
        asort($arr_nums);
    } else {
        arsort($arr_nums);
    }
    if ($is_key) {
        foreach ($arr_nums as $k => $v) {
            $arr[] = $array[$k];
        }
    } else {
        foreach ($arr_nums as $k => $v) {
            $arr[$k] = $array[$k];
        }
    }
    return $arr;
}

/**
 * 树的样式 1 => √ , 0 =>×
 * @param  $name  名称
 * @param  $level  节点等级
 */
function get_menu_name($name, $level) {
    if ($level == 2) {
        return "&nbsp;├─ " . $name;
    } else if ($level == 3) {
        return "&nbsp;│ └─ " . $name;
    } else {
        return $name;
    }
}

/**
 * 主要用于判断字段是布尔情况 1 => √ , 0 =>×
 * @param  $status  布尔值
 */
function get_status_name($status) {
    if ($status == 1) {
        return "√";
    } else {
        return "×";
    }
}

/**
 * 根据方法名来返回不同的值add => 新增 , edit =>编辑
 * @param  $path  路径
 */
function get_submit_name() {
    if (ACTION_NAME == 'add') {
        return '新增';
    } else if (ACTION_NAME == 'edit') {
        return '编辑';
    }
}

/**
 * 保留2位小数，不四舍五入
 * @param type $num
 */
function num_format($num) {
    return sprintf("%.2f", substr(sprintf("%.4f", $num), 0, -2));
}

/*
 * 转换成时间戳
 */

function to_timespan($str, $format = 'Y-m-d H:i:s') {

//    $timezone = intval(app_conf('TIME_ZONE'));
    $timezone = 8;
//转换成时间戳
    $time = intval(strtotime($str));

    if ($time != 0) {
//指定的时间
        $fix_time = '2015-08-22 00:00:00';
        $fix_time_stamp = intval(strtotime($fix_time));
//目前的时间戳
        $now_time_stamp = time();
//如果超过规定的时间 则以当地时间进行计算
        if ($now_time_stamp >= $fix_time_stamp) {

            $time = $time;
        } else {
            $time = $time - $timezone * 3600;
        }
    }
    return $time;
}

/*
 * 时间戳到日期
 * */

function to_date($utc_time, $format = 'Y-m-d H:i:s') {
    if (empty($utc_time)) {
        return '';
    }
//获取时间
//    $timezone = intval(app_conf('TIME_ZONE'));
    $timezone = 8;
//指定的时间
    $fix_time = '2015-08-22 00:00:00';
    $fix_time_stamp = intval(strtotime($fix_time));
//目前的时间戳
    $now_time_stamp = time();
//如果超过规定的时间 则以当地时间进行计算
    if ($now_time_stamp >= $fix_time_stamp) {
        $time = $utc_time;
    } else {
        $time = $utc_time + $timezone * 3600;
    }
    return date($format, $time);
}

/*
 * 隐藏手机号
 * */

function hideMobile($mobile) {
    if ($mobile != "")
        return preg_replace('#(\d{3})\d{5}(\d{3})#', '${1}*****${2}', $mobile);
    else
        return "";
}

/*
 * 隐藏邮箱号
 *  */

function hideEmail($email) {
    if ($email != "")
        return preg_replace('#(\w{2})\w+\@+#', '${1}****@${3}', $email);
    else
        return "";
}

//隐藏身份证号
function hideIdCard($idcard) {
    if ($idcard != "")
        return preg_replace('#(\d{14})\d{4}|(\w+)#', '${1}****', $idcard);
    else
        return "";
}

// 判断是手机号是否为11位数字
function checkMobile($mobile)
{
    $reg = "/^1\d{10}$/";
    if (preg_match($reg, $mobile)) {
        return true;
    } else {
        return false;
    }
}

// 判断密码是否为6-16为数字和字母的组合
function checkPassword($password){
    $reg = "/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/";
    if(preg_match($reg,$password)){
        return true;
    }else{
        return false;
    }
}

/*
 * 千分位
 * */

function format_price($price, $decimals = 2) {
    return "" . num_format($price);
}

//状态的显示
function get_is_effect($tag, $id) {
    if ($tag) {
        return "<span class='is_effect' onclick='set_effect(" . $id . ",this);'>" . l("有效") . "</span>";
    } else {
        return "<span class='is_effect' onclick='set_effect(" . $id . ",this);'>" . l("无效") . "</span>";
    }
}

function pl_it_formula($money, $rate, $remoth) {
    if ((pow(1 + $rate, $remoth) - 1) > 0)
        return $money * ($rate * pow(1 + $rate, $remoth) / (pow(1 + $rate, $remoth) - 1));
    else
        return 0;
}

/**
 * 清除文件以及文件夹本身
 * @param  $path  路径
 * opendir 打开文件
 * readdir
 */
function clear_dir_file($path) {
    if ($dir = opendir($path)) {
        while ($file = readdir($dir)) {
            $check = is_dir($path . $file);
            if (!$check) {
                @unlink($path . $file);
            } else {
                if ($file != '.' && $file != '..') {
                    clear_dir_file($path . $file . "/");
                }
            }
        }
        closedir($dir);
        rmdir($path);
        return true;
    }
}

/**
 * JS提示跳转
 * @param  $tip  弹窗口提示信息(为空没有提示)
 * @param  $type 设置类型 close = 关闭 ，back=返回 ，refresh=提示重载，jump提示并跳转url
 * @param  $url  跳转url
 */
function alert($tip = "", $type = "", $url = "") {
    $js = "<script>";
    if ($tip)
        $js .= "alert('" . $tip . "');";
    switch ($type) {
        case "close" : //关闭页面
            $js .= "window.close();";
            break;
        case "back" : //返回
            $js .= "history.back(-1);";
            break;
        case "refresh" : //刷新
            $js .= "parent.location.reload();";
            break;
        case "top" : //框架退出
            if ($url)
                $js .= "top.location.href='" . $url . "';";
            break;
        case "jump" : //跳转
            if ($url)
                $js .= "window.location.href='" . $url . "';";
            break;
        default :
            break;
    }
    $js .= "</script>";
    echo $js;
    if ($type) {
        exit();
    }
}

//状态的显示
function get_is_status($tag, $id, $field, $function_name) {
    $field = str_replace("global_get_", "", $function_name);
    $html = "<span class='is_effect' data=" . $field . " onclick='get_set_status(" . $id . ",this);'>%s</span>";
    if ($tag) {
        return sprintf($html, $field == 'is_effect' ? "有效" : "是");
    } else {
        return sprintf($html, $field == 'is_effect' ? "无效" : "否");
    }
}

function global_get_status($tag, $id) {
    return get_is_status($tag, $id, $field, __FUNCTION__);
}

function global_get_display($tag, $id) {
    return get_is_status($tag, $id, $field, __FUNCTION__);
}

function global_get_is_pc($tag, $id) {
    return get_is_status($tag, $id, $field, __FUNCTION__);
}

function global_get_is_delete($tag, $id) {
    return get_is_status($tag, $id, $field, __FUNCTION__);
}

function global_get_is_effect($tag, $id) {
    return get_is_status($tag, $id, $field, __FUNCTION__);
}

function get_date($time) {
    if($time > 0){
        return date('Y-m-d H:i:s', $time);
    }else{
        return "无";
    }
    
}

//文件上传方法
function uploading_files($file_data) {
//上传产品主图和副图至服务器
    if ($file_data['size']) {
//如果有文件上传 上传附件
        import('ORG.UploadFile');
//导入上传类
        $upload = new UploadFile();
//设置上传文件大小
        $upload->maxSize = 20000000;
//设置附件上传目录
        $dirname = UPLOAD_PATH . date('Ym', time()) . '/' . date('d', time()) . '/';
        $upload->allowExts = array('jpg', 'jpeg', 'png', 'gif'); // 设置附件上传类型
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

//隐藏银行卡卡号字符串
function hidden_str($str, $type = 0) {
    if ($type) {
        $strs = substr($str, -$type, $type);
    } else {
        $strs = substr($str, 0, 8) . " **** **** " . substr($str, -3, 3);
    }
    return $strs;
}

//隐藏名字
function hidden_name($str) {
    $str_len = mb_strlen($str);
    if ($str_len == 3) {
        return substr($str, 0, 3) . " * " . substr($str, -3, 3);
    } else {
        return substr($str, 0, 3) . " **";
    }
}

/**
 * 会员资金操作函数
 * @param array $data 包括 score,money,point
 * @param integer $user_id
 * @param string $log_msg 日志内容
 * @param integer $type  1充值，2投标成功，3招标成功，4偿还本息，5回收本息，6提前还款，7提前回收，8申请提现，9提现手续费，10借款管理费，11逾期罚息，12逾期管理费，13人工充值，14借款服务费，15出售债权，16购买债权，17债权转让管理费，18开户奖励，19流标还返，20投标管理费，21投标逾期收入，22兑换，23邀请返利，24投标返利，26逾期罚金（垫付后），27其他费用 ，28投资奖励，29红包奖励
 */
function modify_account($data, $user_id, $log_msg = '', $type = 0) {
    $user_info = M("user")->find($user_id);
    if (floatval($data['money_e2']) != 0) {
        $user_data["money_e2"] = $user_info["money_e2"] + $data['money_e2'];
        M("user")->where(array("id" => $user_id))->data($user_data)->save();
    }

    if (floatval($data['lock_money_e2']) != 0) {
        $user_data["lock_money_e2"] = $user_info["lock_money_e2"] + $data['lock_money_e2'];
        M("user")->where(array("id" => $user_id))->data($user_data)->save();
    }

//账户可用资金日志
    if (isset($data['money_e2'])) {
        $money_log_info['remark'] = $log_msg;
        $money_log_info['money_e2'] = floatval($data['money_e2']);
        $money_log_info['account_money_e2'] = M("user")->where(array("id" => $user_id))->getField("money_e2");
        $money_log_info['user_id'] = $user_id;
        $money_log_info['create_time'] = time();
        $money_log_info['create_time_ymd'] = date("Y-m-d");
        $money_log_info['create_time_ym'] = strtotime(date("Ym"));
        $money_log_info['create_time_y'] = strtotime(date("Y"));
        $money_log_info['type'] = $type;
        M("user_money_log")->add($money_log_info); //入库
    }
//账户冻结资金日志
    if (isset($data['lock_money_e2'])) {
        $money_log_info['remark'] = $log_msg;
        $money_log_info['lock_money_e2'] = floatval($data['lock_money_e2']);
        $money_log_info['account_lock_money_e2'] = M("user")->where(array("id" => $user_id))->getField("lock_money_e2");
        $money_log_info['user_id'] = $user_id;
        $money_log_info['create_time'] = time();
        $money_log_info['create_time_ymd'] = date("Y-m-d");
        $money_log_info['create_time_ym'] = strtotime(date("Ym"));
        $money_log_info['create_time_y'] = strtotime(date("Y"));
        $money_log_info['type'] = $type;
        M("user_lock_money_log")->add($money_log_info);
    }
}

/* ajax返回 */

function ajax_return($data, $code = 0) {
    ob_clean();
    header("Content-Type:text/html; charset=utf-8");
    if (!is_array($data)) {
        $show_err = $data;
        $data = array();
        $data['response_code'] = $code;
        $data['show_err'] = $show_err;
    }
    die(json_encode($data));
    exit;
}

/* ajax返回 */

function array_return($root, $code = 0) {
    ob_clean();
    header("Content-Type:text/html; charset=utf-8");
    if (!is_array($root)) {
        $show_err = $root;
        $root = array();
        $root['response_code'] = $code;
        $root['show_err'] = $show_err;
    }
    return $root;
}

/* 从身份证中提取年月日和性别 */

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

function send_msg($mobile, $type = 0) {
    if (true) {
        return 1;
    } else {
        return 0;
    }
}

function verify_msg($mobile, $code, $type=0) {
    if (true) {
        return 1;
    } else {
        return 0;
    }
}

//几天几时几分
function remain_time($remain_time) {
    $d = intval($remain_time / 86400);
    $h = intval(($remain_time % 86400) / 3600);
    $m = intval(($remain_time % 3600) / 60);
    return $d . '天' . $h . '时' . $m . '分';
}

//'优惠券类型 1 收益券 2 抵现券 3加息券 ',
function getCouponTypeName($type) {
    $coupon_type_conf = array(1 => "收益券", "抵现券", "加息券");
    return $coupon_type_conf[$type];
}

//获取标的状态 0待发布，1进行中，2满标，3还款中，4已还清',
function getDealStatusName($deal_status) {
    $deal_status = trim($deal_status);
    $deal_status_conf = array("待发布", "进行中", "满标", "还款中", "已还清");
    return $deal_status_conf[$deal_status];
}

//获取支付平台返回的错误中文
function get_remind_code_zh($remind_code) {
    $remind_code = trim($remind_code);
    $remind_code_conf = include APP_PATH . "conf/remind_code.php";
    return $remind_code_conf[$remind_code];
}

//获取短信邮件模板
function getMsgTemp($send_type,$temp_code){
    $msg_temp = include APP_PATH . "conf/msg_template.php";
    return $msg_temp[$send_type][$temp_code];
}

//获取短信邮件模板名称
function getMsgTempName($send_type,$temp_code){
    $msg_temp_name = include APP_PATH . "conf/msg_template_name.php";
    return $msg_temp_name[$send_type][$temp_code];
}
/**
 * 获取毫秒数
 * @return str 秒.毫秒
 */
function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

//获取支付函数方法的中文名
function getPayFunName($funName) {
    $funName = trim($funName);
    $funName_conf = array(
        'transferBuAction' => '划拨',
        'fuyouRegAction' => '开户',
        'check_balance' => '投标时检测富友余额',
        'preAuthAction' => '预冻结',
    );
    return $funName_conf[$funName];
}

/**
 * 多维数组转换为一维数组
 * @param  array $array 多维数组
 * @return array 一维数组
 */
function array_to_linear_array($array) {
    static $arr2 = array();
    if (!is_array($array)) {
        return '方法arrayChange()参数必须是一个数组';
    } else {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                array_to_linear_array($v);
            } else {
                $arr2 [$k] = $v;
            }
        }
    }
    return $arr2;
}

/**
 * 将多维对象转换成多维数组
 * @param object $obj  多维对象
 * @return array 多维数组
 */
function object_to_array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

/**
 * 多维对象转成一维数组
 * @param object $obj 多维对象
 * @return array 一维数组
 */
function object_to_linear_array($obj) {
    static $new_data = array();
    $data_arr = (array) $obj;
    foreach ($data_arr as $key => $val) {
        if (!is_object($val)) {
            $new_data[$key] = $val;
        } else {
            object_to_linear_array($val);
        }
    }
    return $new_data;
}


//还款方式 1到期还本息 2等额本息 待扩展',
function getLoantypeName($loantype){
    $loantype=  trim($loantype);
    $loantype_conf=array(1=>"到期还本息","等额本息");
    return $loantype_conf[$loantype];
}

//短信邮件发送
/**
 * 
 * @param type $msg['send_type'] 0 短信 1 邮件
 * 
 * 短信参数
 * @param type $msg['dest']       手机号
 * @param type $msg['content']    内容
 * 
 * 邮件参数
 * @param type $msg['dest']       邮件地址
 * @param type $msg['title']      邮件标题
 * @param type $msg['content']    邮件内容
 * @param type $msg['is_html']    是否超文本格式
 */
function send_sms_mail($msg){
    $res = array('status'=>0,'info'=>'发送失败');
    switch($msg['send_type']){
        case 0:
            $sendSms = D("base/es_sms");
            $res = $sendSms->sendSmsMsg($msg['dest'],$msg['content']);
            break;
        case 1:
            $sendMail = D("base/es_mail");
            $res = $sendMail->send($msg['dest'],$msg['title'],$msg['content'],$msg['is_html']);
            break;
    }
    return $res;
}

//查询短信剩余条数
/**
 * @param type $type ：lqd '龙泉达'  yxt '一信通'
 */
function check_sms($type){
    $checkSms = D("base/es_sms");
    $res = $checkSms->checkSmsMoney($type);
    return $res;
}

//发送手机验证码
function send_mobile_verify_code($msg){
    $res = array('status'=>0,'info'=>'发送失败');
    //TODO 测试环境直接返回
    
    //检查手机号是否在黑名单中
    $id = M('blacklist')->where(array('dest'=>$msg['dest'],'is_delete'=>0))->getField(id);
    if($id){
        $res['status'] = 0;
        $res['info'] = "您当天发送的短信已经超过5条";
        return $res;
    }
    send_sms_mail($msg);
}
//状态的显示
function get_toogle_status($tag,$id,$field)
{
    if($tag)
    {
        return "<span class='is_effect' onclick=\"toogle_status(".$id.",this,'".$field."');\">".l("YES")."</span>";
    }
    else
    {
        return "<span class='is_effect' onclick=\"toogle_status(".$id.",this,'".$field."');\">".l("NO")."</span>";
    }
}

