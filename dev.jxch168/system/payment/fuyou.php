<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class fuyou
{

    /**
     * 预冻结
     * @param type $out_cust_no  出账账户
     * @param type $amt 预授权金额
     * @return boolean
     */
    function preAuthAction($out_cust_no,$amt,$load_id)
    {
        $fun = 'preAuth.action';
        $url = FUYOU_URL . $fun;
        //查询标的名称，备注信息
        $deal_name              = $GLOBALS['db']->getOne("select d.name from " . DB_PREFIX . "deal_load dl left join ".DB_PREFIX."deal d on dl.deal_id = d.id where dl.id = " . $load_id);
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_DEAL_LOAD_PREFIX . $load_id; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //出账账户   预授权个人用户
        $setArr['in_cust_no']    = FUYOU_MCHNT_FR; //入账账户 企业账户或个人用户
        $setArr['amt']           = $amt * 100; //预授权金额   以分为单位 (无小数位)
        $setArr['rem']           = '您投的标的名称为'.$deal_name; //

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_preAuth.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }

    /**
     * 预冻结撤销
     * @param type $out_cust_no  出账账户
     * @param type $contract_no 预授权合同号
     * @return boolean
     */
    function preAuthCancelAction($out_cust_no,$contract_no,$load_id)
    {
        $fun = 'preAuthCancel.action';
        $url = FUYOU_URL . $fun;

        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_DEAL_LOAD_PREFIX . $load_id; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //出账账户   预授权个人用户
        $setArr['in_cust_no']    = FUYOU_MCHNT_FR; //入账账户 企业账户或个人用户
        $setArr['contract_no']    = $contract_no; //预授权时的合同号
        $setArr['rem']           = ''; //

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }
        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_preAuthCancel.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }

    /**
     * 转账 (商户与个人之间)
     */
    function transferBmuAction($out_cust_no,$in_cust_no,$amt,$contract_no = '',$deal_load_repay_id = '',$fun = "")
    {
        if(!$fun){
            $fun = 'transferBmu.action';
        }

        $url = FUYOU_URL . $fun;
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_BONUS_TRANSFER_PREFIX . $deal_load_repay_id ; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //付款登录账户
        $setArr['in_cust_no']    = $in_cust_no; //收款登录账户
        $setArr['amt']           = $amt * 100; //划拨金额   以分为单位 (无小数位)
        $setArr['contract_no'] = $contract_no;//预授权合同号
        $setArr['rem']           = ''; //备注

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }

        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBmuReturnDataFuyou.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }

    //查询富友充值提现记录
    public function findInchargeCarryRecord($user_info,$trade_type = "PW11",$begin_time,$end_time){
        
        $fun = 'querycztx.action';
        $url = FUYOU_URL . $fun;

        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_INCHARGE_CARRY_RECORD .time(); //流水号
        $setArr['busi_tp']   = $trade_type; //交易类型 PW11 充值   PWTX 提现
        $setArr['start_time'] = $begin_time; //开始时间 当前月
        $setArr['end_time']  = $end_time; //结束时间 当前月
        //$setArr['cust_no'] = $user_info['mobile'];//个人或商户账户
        $setArr['cust_no'] = $user_info['fuiou_account'];//个人或商户富友账户
        $setArr['txn_st'] = 1;//交易状态 1交易成功 2交易失败
        $setArr['page_no']           = 1; //页码
        $setArr['page_size']           = 50; //每页显示多少条记录

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }

        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_findInchargeCarryRecord.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }

    //查询用户富友交易记录
    function findTradeRecord($user_info,$trade_type = "PWPC",$begin_time,$end_time){
        $fun = 'queryTxn.action';
        $url = FUYOU_URL . $fun;

        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_TRADING_RECORD .time(); //流水号
        $setArr['busi_tp']   = $trade_type; //交易类型 （PWPC 转账）（PW13 预授权）（PWCF 预授权撤销）（PW03 划拨）（PW14 转账冻结）（PW15 划拨冻结）（PWDJ 冻结）（PWJD 解冻）（PW19 冻结付款到冻结）
        $setArr['start_day'] = $begin_time; //开始时间 当前月
        $setArr['end_day']  = $end_time; //结束时间 当前月
        $setArr['txn_ssn'] = '';//交易流水
        //$setArr['cust_no'] = $user_info['mobile'];//个人或商户账户
        $setArr['cust_no'] = $user_info['fuiou_account'];//个人或商户富友账户
        $setArr['txn_st'] = 1;//交易状态 1交易成功 2交易失败
        $setArr['remark']           = ''; //备注
        $setArr['page_no']           = 1; //页码
        $setArr['page_size']           = 50; //每页显示多少条记录

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }

        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_findTradeRecord.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }

    //划拨 （账户之间转账 个人与个人之间）
    function transferBuAction($out_cust_no,$in_cust_no,$amt,$contract_no = '',$deal_load_repay_id = '',$fun = "")
    {
        if(!$fun){
            $fun = 'transferBu.action';
        }

        $url = FUYOU_URL . $fun;
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_DEAL_LOAD_CALLBACK_PREFIX . $deal_load_repay_id ; //流水号
        $setArr['out_cust_no']   = $out_cust_no; //付款登录账户
        $setArr['in_cust_no']    = $in_cust_no; //收款登录账户
        $setArr['amt']           = $amt * 100; //划拨金额   以分为单位 (无小数位)
        $setArr['contract_no'] = $contract_no;//预授权合同号
        $setArr['rem']           = ''; //备注

        $str                     = '';
        ksort($setArr);
        foreach ($setArr as $valV) {
            $str .= $valV . '|';
        }

        $setArr['signature'] = $this->rsaSign(substr($str, 0, -1), APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $data                = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_transferBuReturnDataFuyou.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
    }
    
    //更换手机号
    function getChangeMobileCode($user_info,$isApp = 0){
        $parameter['mchnt_cd']      = FUYOU_MCHNT_CD; // 商户代码
        $parameter['mchnt_txn_ssn'] = FUYOU_CHANGE_MOBILE_PREFIX .time(); // 获得订单的流水号，补零到10位
        $parameter['login_id']      = $user_info['fuiou_account'];//富友账号 提现
        
        if ($isApp) {
            $isAppAtr  = 'app/';
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'wap/member.php?ctl=uc_change_mobile_notice';
            if (isset($GLOBALS['request']['_m']) && $GLOBALS['request']['_m']) {
                $parameter['page_notify_url'] =
                $parameter['page_notify_url']
                . '&_m=' . $GLOBALS['request']['_m']
                . '&version='.$GLOBALS['request']['version']
                . '&email='.strim($GLOBALS['request']['email'])
                . '&pwd='.strim($GLOBALS['request']['pwd']);
            }
        } else {
            $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'member.php?ctl=uc_account&act=changeMobileNotice';
        }
        
        ksort($parameter);
        
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str                    = substr($str, 0, -1);
        $signature              = $this->rsaSign($str, APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据

        $payLinks = '<form id="change_now" style="text-align:center;" action="' . FUYOU_URL .$isAppAtr. '400101.action" style="margin:0px;padding:0px" method="post" >';
        foreach ($parameter AS $key => $val) {
            $payLinks .= "<input type='hidden' name='$key' value='$val' />";
        }
        $payLinks .= "</form>";
        $code                    = '<div style="text-align:center">' . $payLinks . '</div>';
        $code .= "<script type='text/javascript'>document.getElementById('change_now').submit();</script>";
        
        return $code;
    }
    
    //更换手机号回调
    function changeMobileBack($request){
        $reArr = $request;
        unset($request['ctl']);
        unset($request['act']);
        unset($request['signature']);

        if ($request['resp_code'] != '0000') {
            $a = $this->getXml('0003', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changemobilecallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 0;
            $return["resp_desc"] = $request['resp_desc'];
            return $return;
        }
        ksort($request);
        $str = '';
        foreach ($request as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);
        //验证签名是否被篡改
        if ($this->rsaVerify($str, './fuyou_key/php_pbkey.pem', $reArr['signature'])) {
            //修改手机号
            $mobile['mobile'] = $reArr['new_mobile'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $mobile, "UPDATE", "fuiou_account = '" . $reArr['login_id']."'");
            $a  = $this->getXml('0000', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changemobilecallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 1; 
        } else {
            $a = $this->getXml('0001', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changemobilecallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 0;
        }
        $return["resp_desc"] = $request['resp_desc'];
        
        return $return;
    }
    
    //修改银行卡
    public function getChangeBankCode($user_info){
        
        $parameter['mchnt_cd']      = FUYOU_MCHNT_CD; // 商户代码
        $parameter['mchnt_txn_ssn'] = FUYOU_CHANGE_BANK_PREFIX .time(); // 获得订单的流水号，补零到10位
        $parameter['login_id']      = $user_info['fuiou_account'];//富友账号 提现
        $parameter['page_notify_url'] = FUYOU_CALLBACK_URL . 'member.php?ctl=uc_money&act=changeBankNotice';
        
        ksort($parameter);
        
        $str = '';
        foreach ($parameter as $valV) {
            $str .= $valV . '|';
        }
        $str                    = substr($str, 0, -1);
        $signature              = $this->rsaSign($str, APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        $parameter['signature'] = $signature; //签名数据

        $payLinks = '<form id="change_now" style="text-align:center;" action="' . FUYOU_URL . 'changeCard2.action" style="margin:0px;padding:0px" method="post" >';
        foreach ($parameter AS $key => $val) {
            $payLinks .= "<input type='hidden' name='$key' value='$val' />";
        }
        $payLinks .= "</form>";
        $code                    = '<div style="text-align:center">' . $payLinks . '</div>';
        $code .= "<script type='text/javascript'>document.getElementById('change_now').submit();</script>";
        //保存该次申请记录
        $user_bank_examine["user_id"] = $user_info["id"];
        $user_bank_examine["mchnt_txn_ssn"] = $parameter['mchnt_txn_ssn'];
        $user_bank_examine["create_time"] = time();
        //插入申请记录
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $user_bank_examine, "INSERT");
        
        return $code;
    }
    
    //修改银行卡前台回调
    public function changeBankBack(){
        
        $reArr = $_REQUEST;
        unset($_REQUEST['ctl']);
        unset($_REQUEST['act']);
        unset($_REQUEST['signature']);

        //修改银行卡申请状态描述信息
        $back_data['resp_desc'] = $_REQUEST['resp_desc'];
        $back_data['update_time'] = time();
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $back_data, "UPDATE", "mchnt_txn_ssn = '" . $reArr['mchnt_txn_ssn']."'");
        if ($_REQUEST['resp_code'] != '0000') {
            $a = $this->getXml('0003', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changebankcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 0;
            $return["resp_desc"] = $_REQUEST['resp_desc'];
            return $return;
        }
        ksort($_REQUEST);
        $str = '';
        foreach ($_REQUEST as $valV) {
            $str .= $valV . '|';
        }
        $str = substr($str, 0, -1);
        //验证签名是否被篡改
        if ($this->rsaVerify($str, './fuyou_key/php_pbkey.pem', $reArr['signature'])) {
            $a  = $this->getXml('0000', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changebankcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 1; 
        } else {
            $a = $this->getXml('0001', FUYOU_MCHNT_CD, $reArr['mchnt_txn_ssn']);
            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_changebankcallback.log', "POST:[" . json_encode($reArr) . "];return:[" . $a . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            $return["status"] = 0;
        }
        $return["resp_desc"] = $_REQUEST['resp_desc'];
        
        return $return;
    }
    
    //查询用户更换银行卡 的审核结果
    public function queryChangeCard($user_info,$org_mchnt_txn_ssn){
        $fun = 'queryChangeCard.action';
        $url = FUYOU_URL . $fun;
        
        $setArr['mchnt_cd']      = FUYOU_MCHNT_CD; //商户代码
        $setArr['mchnt_txn_ssn'] = FUYOU_FIND_BANK_INFO_PREFIX .time(); //流水号
        $setArr['login_id']   = $user_info["fuiou_account"]; //个人或商户富友账户
        $setArr['txn_ssn'] = $org_mchnt_txn_ssn;//交易流水

        $str = '';
        ksort($setArr);
        foreach ($setArr as $val) {
            $str .= $val . '|';
        }
        $str = substr($str, 0, -1);
        $setArr['signature'] = $this->rsaSign($str, APP_ROOT_PATH . 'fuyou_key/php_prkey.pem');
        
        $data = $this->open_url($url, '', '', '', $setArr);
        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_findChangeBankInfo.log', "POST:[" . json_encode($setArr) . "];return:[" . $data . "];[请求URL地址:".$url."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);

        return $this->getResult($data);
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

    private function open_url($URL, $ip = "", $cks = "", $cksfile = "", $post = "", $ref = "", $fl = 0, $nbd = 0, $hder = 0, $tmout = "120")
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

    /*     *
     * 支付宝接口RSA函数
     * 详细：RSA签名、验签、解密
     * 版本：3.3
     * 日期：2012-07-23
     * 说明：
     * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
     * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
     */

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
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

    /**
     * RSA解密
     * @param $content 需要解密的内容，密文
     * @param $private_key_path 商户私钥文件路径
     * return 解密后内容，明文
     */
    function rsaDecrypt($content, $private_key_path)
    {
        $priKey  = file_get_contents($private_key_path);
        $res     = openssl_get_privatekey($priKey);
        //用base64将内容还原成二进制
        $content = base64_decode($content);
        //把需要解密的内容，按128位拆开解密
        $result  = '';
        for ($i = 0; $i < strlen($content) / 128; $i++) {
            $data = substr($content, $i * 128, 128);
            openssl_private_decrypt($data, $decrypt, $res);
            $result .= $decrypt;
        }
        openssl_free_key($res);
        return $result;
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

}
