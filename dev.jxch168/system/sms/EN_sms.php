<?php

// +----------------------------------------------------------------------
// | 泉龙达短信
// +----------------------------------------------------------------------
// |
// +----------------------------------------------------------------------

set_time_limit(0);

require_once APP_ROOT_PATH.'system/en_sms/config.php';
require_once APP_ROOT_PATH.'system/en_sms/function.php';
// 短信平台
require_once APP_ROOT_PATH.'system/en_sms/Client.php';

/* 模块的基本信息 */
if (isset($read_modules) && $read_modules == true) {
    $module['server_url'] = 'http://61.130.7.220:8023/MWGate/wmgw.asmx?';

    $module['class_name']        = 'EN';
    /* 名称 */
    $module['name']              = "泉龙达短信平台";
    $module['enterprise_number'] = '219434';  //  企业编号：
    return $module;
}

// 企信通短信平台
require_once APP_ROOT_PATH . "system/libs/sms.php";  //引入接口

class En_sms implements sms
{

    public $sms;
    public $message = "";

    public function __construct($smsInfo = '')
    {
        if (!empty($smsInfo)) {
            $this->sms = $smsInfo;
        }
    }
    //默认发送短信 5
    public function sendSMS($mobile_number, $content,$handleType = 5,$sendTime='',$method = 1)
    {
            if (in_array(CONDITION, array('dev','test')))
            {
                $res['status'] = true;
                $res['return'] = '';
                $res['msg'] = $content;
                return $res;
            }
            global $defhandle,$arrret,$statuscode,$pginface,$soapinface,$signlens,$pageurl,$username,$password;
            $result = array();
            $smsInfo['userId'] = $this->sms['user_name'] ? $this->sms['user_name'] : $username;
            $smsInfo['password'] = $this->sms['password'] ? $this->sms['password'] : $password;
            //$smsInfo['pszSubPort'] = $V['port'];
            //$smsInfo['flownum'] = $V['flownum'];
            $action = $pageurl;
            $defhandle = $handleType ? $handleType : $defhandle ; //设置请求接口 默认为5 发送短信
            if ($handleType == 5)
            {
                    $smsInfo['multixmt'] = ' ';
                    $defhandle = $handleType;	//个性化发送  2014-09-11
            }
            //默认使用POST方式请求
            $action.="/".$pginface[$defhandle];
            //$method 默认为1 POST方式发送请求
            $sms = new Client($action, $method);

            $strRet = '';
            switch($handleType)
            {
                    //获取余额
                    case 3:
                        $result = $sms->GetMoney($smsInfo);
                        //错误
                        $strRet = GetCodeMsg($result, $statuscode);
                        file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_en_sms_remain.log', "POST:【" . json_encode($smsInfo) ."】;return:【" . json_encode($result)  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);

                        if(!$strRet){
                              $strRet = "余额查询成功";
                        }
                        break;
                    //发送短信
                    case 5:
                            $multixmt = array();
                            foreach($mobile_number as $key=>$val){
                                //转码
                                $content_new = iconv('UTF-8', 'GB2312', $content);
                                //发送 $val发送号码 $content发送内容
                                $multixmt[] = time().$key.'|'.time().$key.rand(10000,9999).'|'.$val.'|'.base64_encode($content_new);

                            }
                            $smsInfo['multixmt'] = implode(",",$multixmt);
                            $smsInfo['multixmt'] = str_replace("\\\\","\\",$smsInfo['multixmt']);
                            $result = $sms->SefsendSMS($smsInfo);
                            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_en_sms_sending.log', "POST:【" . json_encode($smsInfo) ."】;return:【" . json_encode($result)  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);

                            $strRet = $result;
                            break;

                    default:
                            $strRet = "没有匹配的业务类型";
                            break;
            }

            if($handleType == 3){
                if(!$result){
                    $res['status'] = 0;
                }else{
                    $res['status'] = 1;
                }
                $res['return'] = $result;
                $res['msg'] = $strRet;
            }else if($handleType == 5){
                //20位数
                $str_count = strlen($result);
                if ($str_count == 20 || $str_count == 19) {
                    $res['status'] = 1;
                    $res['count'] = $str_count;
                    $res['return'] = $result;
                    $res['msg'] = "发送成功！";
                    file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_quanlongda_true.log', "【泉龙达短信平台】;MSG:【" .   json_encode($mobile_number) ."】【内容".$content."】;ERR【" . print_r($res,true)  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);

                } else {
                    $res['status'] = 0;
                    $res['return'] = $result;
                    $res['msg'] = $statuscode[$result];
                    file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '_quanlongda_err.log', "【泉龙达短信平台】;MSG:【" .   json_encode($mobile_number) ."】【内容".$content."】;ERR【" . print_r($res,true)  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);
                }
            }else{
                $res['status'] = 0;
                $res['return'] = '';
                $res['msg'] = $strRet;
            }
            return $res;
    }

    //获取剩余短信条数
/*    public function get_count_msg(){
            $post_data                   = array();
            $post_data['SpCode']         = '219435';
            $post_data['LoginName']      = $this->sms['user_name'];
            $post_data['Password']       = $this->sms['password'];
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
            file_put_contents(APP_ROOT_PATH . 'log/msg/' . date('Y-m-d') . '.log', "【泉龙达短信平台】;MSG:【" .   iconv('GB2312', 'UTF-8', $o) ."】;ERR【" . $resIconv  . "】【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);

            return $resArr;
    }
*/
    public function getSmsInfo()
    {
        return "泉龙达短信平台";
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