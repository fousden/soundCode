<?php
/**
 * 富友基础业务逻辑类
 *
 * @author jxch
 */
namespace base\model;
use think\Model;

class Fuyou extends Model{

    public $fuyou_mchnt_cd = "0002900F0041270";//商户代码
    public $fuyou_url = "http://www-1.fuiou.com:9057/jzh/";//接口地址
    public $fuyou_callback_url = 'http://newdev.jxch168.com/';
    const fuyou_prefix = 'JXCH';
    public $fuyou_bindcard_prefix = self::fuyou_prefix."BINDCARD";//流水号
    const FUYOU_MCHNT_FR='13999999999';// 登录 Id 或法人账号

    //充值
    function get_user_incharge_code($request,$incharge_id,$is_app){
        $incharge_info = M("user_incharge")->find($incharge_id);
        $user_info = M("user")->field("mobile,fuiou_account")->where(array("id"=>$incharge_info["user_id"]))->find();
        $parameter['mchnt_cd'] = $this->fuyou_mchnt_cd; // 商户代码
        $parameter['mchnt_txn_ssn'] = $incharge_info['notice_sn']; // 获得订单的流水号
        $parameter['login_id'] = $user_info['fuiou_account'];//富友账号
        $parameter['amt'] = floatval($incharge_info['money_e2']); //跳转提现页面锁定金额

         if ($is_app) {
            $parameter['page_notify_url'] = $this->fuyou_callback_url . 'wap/member.php?ctl=uc_incharge_log';
            if (isset($request['_m']) && $request['_m']) {
                $parameter['page_notify_url'] =
                $parameter['page_notify_url']
                . '&_m=' . $request['_m']
		. '&withdrawals=1'
                . '&version='.$request['version']
                . '&email='.strim($request['email'])
                . '&pwd='.strim($request['pwd']);
            }
        } else {
            $parameter['page_notify_url'] = $this->fuyou_callback_url . 'home/user/incharge_log';
        }
        $parameter['back_notify_url'] = $this->fuyou_callback_url . 'home/user/incharge_notify';

        ksort($parameter);
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);
        $signature = $this->rsaSign($str, APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据

        //是否移动端
        if ($is_app) {
            $isAppAtr = 'app/';
            $incharge_content = '<form id="incharge_now" style="text-align:center;" action="' . $this->fuyou_url . $isAppAtr . '500002.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $incharge_content .= "<input type='hidden' name='$key' value='$val' />";
            }
            $incharge_content .= "<input type='submit' class='paybutton' value='前往富友充值'></form>";
            $code = '<div style="text-align:center">' . $incharge_content . '</div>';
            $code .= "<br /><div style='text-align:center' class='red'>" . $user_info['user_name'] . "正在进行充值操作，" . $GLOBALS['lang']['CARRY_TOTAL_PRICE'] . "为:" . format_price($request['money']) . "</div>";
            $result['code']          = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
        }else{
            $incharge_content = '<form id="incharge_now" style="text-align:center;" action="' . $this->fuyou_url . '500002.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $incharge_content .= "<input type='hidden' name='$key' value='$val' />";
            }
            $incharge_content .= "<input type='hidden' class='paybutton' ></form>";
            $code = '<div style="text-align:center">' . $incharge_content . '</div>';
            $code .= '<script type="text/javascript">document.getElementById("incharge_now").submit();</script>';
            $result['code'] = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
        }
        return $result;

    }

    //提现
    function get_user_carry_code($request,$carry_id,$is_app){
        //提现记录信息
        $carry_info = M("user_carry")->find($carry_id);
        //用户信息
        $user_info = M("user")->field("mobile,fuiou_account")->where(array("id"=>$carry_info["user_id"]))->find();
        //提现相关数据
        $parameter['mchnt_cd'] = $this->fuyou_mchnt_cd; // 商户代码
        $parameter['mchnt_txn_ssn'] = date("YmdHis").rand(0,99999).$carry_id; // 获得订单的流水号
        $parameter['login_id'] = $user_info['fuiou_account'];//富友账号 提现
        $parameter['amt'] = floatval($carry_info['money_e2']); //跳转提现页面锁定金额
        if ($is_app) {
            $parameter['page_notify_url'] = $this->fuyou_callback_url . 'wap/member.php?ctl=uc_incharge_log';
            if (isset($request['_m']) && $request['_m']) {
                $parameter['page_notify_url'] =
                $parameter['page_notify_url']
                . '&_m=' . $request['_m']
		. '&withdrawals=1'
                . '&version='.$request['version']
                . '&email='.strim($request['email'])
                . '&pwd='.strim($request['pwd']);
            }
        } else {
            $parameter['page_notify_url'] = $this->fuyou_callback_url . 'home/user/carry_log';
        }
        $parameter['back_notify_url'] = $this->fuyou_callback_url . 'home/user/carry_notify';

        ksort($parameter);
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);
        $signature = $this->rsaSign($str, APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据
        //是否移动端
        if ($is_app) {
            $isAppAtr = 'app/';
            $carry_content = '<form id="carry_now" style="text-align:center;" action="' . $this->fuyou_url . $isAppAtr . '500003.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $carry_content .= "<input type='hidden' name='$key' value='$val' />";
            }
            $carry_content .= "<input type='submit' class='paybutton' value='前往富友提现'></form>";
            $code = '<div style="text-align:center">' . $carry_content . '</div>';
            $code .= "<br /><div style='text-align:center' class='red'>" . $user_info['user_name'] . "正在进行提现操作，" . $GLOBALS['lang']['CARRY_TOTAL_PRICE'] . "为:" . format_price($user_carry_info['money']) . "</div>";
            $result['code']          = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
        }else{
            $carry_content = '<form id="carry_now" style="text-align:center;" action="' . $this->fuyou_url . '500003.action" style="margin:0px;padding:0px" method="post" >';
            foreach ($parameter AS $key => $val) {
                $carry_content .= "<input type='hidden' name='$key' value='$val' />";
            }
            $carry_content .= "<input type='hidden' class='paybutton' ></form>";
            $code = '<div style="text-align:center">' . $carry_content . '</div>';
            $code .= '<script type="text/javascript">document.getElementById("carry_now").submit();</script>';
            $result['code'] = $code;
            $result['mchnt_txn_ssn'] = $parameter['mchnt_txn_ssn'];
        }
        return $result;
    }

    //划拨 （账户之间转账 个人与个人之间）
    function transferBuAction($out_cust_no,$in_cust_no,$amt,$contract_no = '',$deal_load_repay_id = '')
    {
        $url = $this->fuyou_url.'transferBu.action';
        $setArr['mchnt_cd']      = $this->fuyou_mchnt_cd; //商户代码
        $setArr['mchnt_txn_ssn'] = date("YmdHis").rand(0,99999).$deal_load_repay_id ; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //付款登录账户
        $setArr['in_cust_no']    = $in_cust_no; //收款登录账户
        $setArr['amt']           = $amt; //划拨金额   以分为单位 (无小数位)
        $setArr['contract_no'] = $contract_no;//预授权合同号
        $setArr['rem']           = ''; //备注

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1),APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        $data = $this->open_url($url, '', '', '', $setArr);

        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBuReturnDataFuyou.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        return $this->getResult($data);
    }

    //开户
    function fuyouRegAction($userId)
    {
        $field = "real_name,idno,fuiou_account";
        $user_info = D('user')->field($field)->where("id=".$userId)->find();
        $ext_field = "region_lv2,bank_id,bank_card";
        $user_ext_info = D('user_extend')->where(array("user_id"=>$userId))->find();
        $user_info = array_merge($user_info,$user_ext_info);
        $setArr['mchnt_cd']       = $this->fuyou_mchnt_cd; //商户代码  M
        $setArr['mchnt_txn_ssn']  =$userId . time(); //流水号 M
        $setArr['cust_nm']        = $user_info['real_name']; //客户姓名 M
        $setArr['certif_id']      = strtoupper($user_info['idno']); //身份证号码 M  如果身份证号码中有字母则转换成大写
        //$setArr['mobile_no']      = $user_info['mobile']; //手机号码 M
        $setArr['mobile_no']      = $user_info['fuiou_account']; //富友账号 M
        $setArr['email']          = ''; //邮箱地址
        $setArr['city_id']        = $user_info['region_lv2']; //开户行地区代码 M
        $setArr['parent_bank_id'] = $user_info['bank_id']; //开户行行别 M
        $setArr['bank_nm']        = ''; //开户行支行名称
        $setArr['capAcntNm']      = ''; //户名
        $setArr['capAcntNo']      = str_replace(' ', '', $user_info['bank_card']); //帐号 M
        $setArr['password']       = ''; //提现密码
        $setArr['lpassword']      = ''; //登录密码
        $setArr['rem']            = ''; //备注

        $str                      = '';
        ksort($setArr);

        foreach ($setArr as $val) {
            $str .= $val . '|';
        }
         $str       = substr($str, 0, -1);

        $signature = $this->rsaSign($str, APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        $setArr['signature'] = $signature;
//        return $setArr;
         //签名信息 M
        $data = $this->open_url($this->fuyou_url . 'reg.action', '', '', '', $setArr);

        // file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '.log', "ERR:【" .   $data ."】;【".date('Y-m-d H:i:s')."】\r\n", FILE_APPEND);
        file_put_contents(ROOT. 'public/log/fuyou/' . date('Y-m-d') . '_regAction.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$this->fuyou_url . "reg.action];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        $xml = simplexml_load_string($data);
        $resp_code = $xml->plain->resp_code;
        $resp_code .= '';
        return $resp_code;
    }

    //投标时检测富友余额 如果余额足够返回TRUE 余额不足返回FALSE
    function check_balance($user_info)
    {
        $setArr['mchnt_cd']      = $this->fuyou_mchnt_cd; //商户代码  M
        $setArr['mchnt_txn_ssn'] = date("YmdHis").rand(0,99999) . $user_info['id']; //流水号 M
        $setArr['mchnt_txn_dt']  = date('Ymd', time()); //交易日期 M
        $setArr['cust_no']       = $user_info['fuiou_account']; //待查询的富友账户 M
        $str = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        //签名信息 M
        $data                = $this->open_url($this->fuyou_url . 'BalanceAction.action', '', '', '', $setArr);
        //写入文件日志信息
        //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_BalanceAction.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求url地址".FUYOU_URL ."BalanceAction.action];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        //载入数据
        $xml                 = simplexml_load_string($data);
        $arr                 = array();
        if ('0000' == $xml->plain->resp_code) {
            $arr['status']        = 1;
            $arr['mchnt_cd']      = '' . $xml->plain->mchnt_cd; //商户代码
            $arr['mchnt_txn_ssn'] = '' . $xml->plain->mchnt_txn_ssn; //请求流水号
            $arr['user_id']       = '' . $xml->plain->results->result->user_id; //用户名
            $arr['ct_balance']    = intval($xml->plain->results->result->ct_balance) / 100; //账面总余额
            $arr['ca_balance']    = intval($xml->plain->results->result->ca_balance) / 100; //可用余额
            $arr['cf_balance']    = intval($xml->plain->results->result->cf_balance) / 100; //冻结余额
            $arr['cu_balance']    = intval($xml->plain->results->result->cu_balance) / 100; //未转结余额
        } else {
            $arr['status']     = 2;
            $arr['show_error'] = '对不起，服务器忙，请稍后再试！';
        }
        return $arr;
    }

    /**
     * 预冻结
     * @param type $out_cust_no  出账账户
     * @param type $amt 预授权金额
     * @param type $load_id 标的id
     * @param type $deal_name 标的名称
     * @return boolean
     */
    function preAuthAction($out_cust_no,$amt,$load_id,$deal_name)
    {
        $fun = 'preAuth.action';
        $url = $this->fuyou_url . $fun;
        $setArr['mchnt_cd']      = $this->fuyou_mchnt_cd; //商户代码
        $setArr['mchnt_txn_ssn'] = $this->fuyou_bindcard_prefix . $load_id; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //出账账户   预授权个人用户
        $setArr['in_cust_no']    = self::FUYOU_MCHNT_FR; //入账账户 企业账户或个人用户
        $setArr['amt']           = $amt * 100; //预授权金额   以分为单位 (无小数位)
        $setArr['rem']           = '您投的标的名称为'.$deal_name; //

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_PATH . 'ORG/fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_preAuth.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        return simplexml_load_string($data);
    }

    private function open_url($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "120")
    {//,$ctimeout="60
        //echo $URL . "\r\n<BR>";
        $start_time=microtime_float();
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
        $end_time=microtime_float();
        D("base/pay_log")->addLog($post,$re,$start_time,$end_time);
        return $re; //返回得到的结果
    }

    //富友返回值处理 返回结果
    public function getResult($data){
        if(!$data){
            return false;
        }
        $xml = simplexml_load_string($data);
        if ('0000' == $xml->plain->resp_code) {
            return $xml;
        } else {
            return false;
        }
    }

    function getXml($resp_code, $mchnt_cd, $mchnt_txn_ssn)
    {
        $strArr = array(
            'resp_code'     => '',
            'mchnt_cd'      => '',
            'mchnt_txn_ssn' => '',
        );
        ksort($strArr);
        $str    = '';
        foreach ($strArr as $valV) {
            $str .= $valV . '|';
        }
        $str       = substr($str, 0, -1);
        $signature = $this->rsaSign($str, ROOT_PATH . 'fuyou_key/php_prkey.pem');

        $str = '<?xml version="1.0" encoding="UTF-8"?>';
        $str .= '<ap>';
        $str .= '<plain>';
        $str .= '<resp_code>' . $resp_code . '</resp_code>';
        $str .= '<mchnt_cd>' . $mchnt_cd . '</mchnt_cd>';
        $str .= '<mchnt_txn_ssn>' . $mchnt_txn_ssn . '</mchnt_txn_ssn>';
        $str .= '</plain>';
        $str .= '<signature>' . $signature . '</signature>';
        $str .= '</ap>';
        return $str;
    }

    //签名
    function rsaSign($data, $private_key_path)
    {
        $priKey = file_get_contents($private_key_path);
        $res    = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign   = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign)
    {
        $pubKey = file_get_contents($ali_public_key_path);
        $res    = openssl_get_publickey($pubKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }
}

