<?php

// +----------------------------------------------------------------------
// | 一信通短信
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true) {
    $module['server_url'] = 'http://zx.ums86.com:8899/sms/Api/Send.do?';

    $module['class_name']        = 'YY';
    /* 名称 */
    $module['name']              = "一信通短信平台";
    $module['enterprise_number'] = '219435';  //  企业编号：
    return $module;
}

// 企信通短信平台
require_once APP_ROOT_PATH . "system/libs/sms.php";  //引入接口

class YY_sms implements sms
{

    public $sms;
    public $message = "";

    public function __construct($smsInfo = '')
    {
        if (!empty($smsInfo)) {
            $this->sms = $smsInfo;
        }
    }

    public function sendSMS($mobile_number, $content)
    {
        if (in_array(CONDITION, array('dev','test')))
        {
            $result['status'] = true;
            $result['msg'] = $content;
            return $result;
        }

        if (is_array($mobile_number)) {
            $mobile_number = $mobile_number[0];
        }

        $post_data                   = array();
        $post_data['SpCode']         = '219435';
        $post_data['LoginName']      = $this->sms['user_name'];
        $post_data['Password']       = $this->sms['password'];
        $post_data['MessageContent'] = iconv('UTF-8', 'GB2312', $content); // 短信内容, 最大1000个字符（短信内容要求的编码为gb2312或gbk）
        $post_data['UserNumber']     = $mobile_number;
        $post_data['f']              = '1';  //1 --- 提交号码中有效的号码仍正常发出短信，无效的号码在返回参数faillist中列出e

        $url = 'http://zx.ums86.com:8899/sms/Api/Send.do?';
        $o   = '';
        foreach ($post_data as $k => $v) {
            $o.="$k=" . $v . '&';
        }
        $post_data = substr($o, 0, -1);
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $res       = curl_exec($ch);
        $resIconv  = iconv('GB2312', 'UTF-8', $res);
        parse_str($resIconv, $resArr);

       // var_dump($resIconv);exit;
        if ($resArr['result'] < 1) {
            $result['status'] = true;
            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_yy_true.log', "MSG:【" .   iconv('GB2312', 'UTF-8', $o) ."】;ERR【" . $resIconv  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);
        } else {
            $result['status'] = false;
            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_yy_err.log', "MSG:【" .   iconv('GB2312', 'UTF-8', $o) ."】;ERR【" . $resIconv  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);
        }
        $result['msg'] = $strIconv;
        return $result;
    }

	//获取剩余短信条数
	public function get_count_msg(){
		$post_data                   = array();
                $post_data['SpCode']         = '219435';
                //$post_data['LoginName']      = $this->sms['user_name'];
                $post_data['LoginName']      = 'shhmtx';
                //$post_data['Password']       = $this->sms['password'];
                $post_data['Password']       = 'Shhmt2015';

                //$post_data['MessageContent'] = iconv('UTF-8', 'GB2312', $content); // 短信内容, 最大1000个字符（短信内容要求的编码为gb2312或gbk）
                //$post_data['UserNumber']     = $mobile_number;
                //$post_data['f']              = '1';  //1 --- 提交号码中有效的号码仍正常发出短信，无效的号码在返回参数faillist中列出e

                $url = 'http://zx.ums86.com:8899/sms/Api/SearchNumber.do?';
                $o   = '';
                foreach ($post_data as $k => $v) {
                    $o.="$k=" . $v . '&';
                }
                $post_data = substr($o, 0, -1);
                $ch        = curl_init();
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
                $res       = curl_exec($ch);
                $resIconv  = iconv('GB2312', 'UTF-8', $res);
                parse_str($resIconv, $resArr);
                file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_yy_sms_remain.log', "POST:【" .   iconv('GB2312', 'UTF-8', $o) ."】;return:【" . $resIconv  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);

                return $resArr;
	}

    public function getSmsInfo()
    {
        return "一信通短信平台";
    }

    public function check_fee()
    {
        $post_data              = array();
        $post_data['SpCode']    = '219435';
        $post_data['LoginName'] = $this->sms['user_name'];
        $post_data['Password']  = $this->sms['password'];

        $url = 'http://zx.ums86.com:8899/sms/Api/SearchNumber.do?';

        foreach ($post_data as $k => $v) {
            $o.="$k=" . $v . '&';
        }
        $post_data = substr($o, 0, -1);

        $ch       = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $res      = curl_exec($ch);
        $resIconv = iconv('GB2312', 'UTF-8', $res);
        parse_str($resIconv, $resArr);
        if ($resArr['result'] < 1) {
            $str = $resArr['description'] . $resArr['number'] . '条';
        } else {
            $str = $resArr['description'];
        }

        return $str;
    }

}

?>