<?php
namespace base\model\sms;
use think\Model;
use base\model\sms\Sms;

class Lqdsms extends Sms{
    
    protected $timeout    = 60;

    //发送短信
    function send(){
        $res = array('status'=>0,'info'=>'发送失败');
        
        $mobile = array($this->mobile);
        $content = $this->content;

        $multixmt = array();
        foreach($mobile as $key=>$val){
            //转码
            $content_new = iconv('UTF-8', 'GB2312', $content);  
            //发送 $val发送号码 $content发送内容
            $multixmt[] = time().$key.'|'.time().$key.rand(10000,9999).'|'.$val.'|'.base64_encode($content_new);
        }
        $smsInfo = implode(",",$multixmt);
        $smsInfo = str_replace("\\\\","\\",$smsInfo);
        
        $arrSmsInfo['userId']   = $this->username;
        $arrSmsInfo['password'] = $this->password;
        $arrSmsInfo['multixmt'] = $smsInfo;
        $send_url = $this->host.$this->sendPath;
        
        $data = $this->postCommon($arrSmsInfo,$send_url,'string');
        if($data > 0){
            $res['status'] = 1;  
            $res['info']   = '发送成功';  
        }
        $res['code']   = $data; 
        return $res;
    }
    
    //查询剩余条数
    function check(){
        $res = array('status'=>0,'info'=>'查询失败');
        $arrSmsInfo['userId']   = "J50601";
        $arrSmsInfo['password'] = "598712";
        $check_url = "http://61.130.7.220:8023/MWGate/wmgw.asmx/MongateQueryBalance";
        
        $data = $this->postCommon($arrSmsInfo,$check_url,'int');
        if($data > 0){
            $res['status'] = 1;  
            $res['info']   = "剩余条数(包括套内和可超发条数)：" . $data .'条';  
        }else{
            $res['code']   = $data; 
        }
        
        return $res;
    }
    
    private function postCommon($arrSmsInfo,$url,$strkey){
        
        $strParam = '';
        foreach ($arrSmsInfo as $k=>$v){
            $strParam.= $k."=".urlencode($v)."&";
        }
        $str = substr($strParam, 0, -1);
        
        $info=parse_url($url);
        $errno = '';
        $errstr = '';
        $timeout = $this->timeout;
        $fp = fsockopen($info["host"], $info["port"], $errno, $errstr, $this->timeout);
        $head = "POST ".$info['path']." HTTP/1.1\r\n";
        $head .= "Host: ".$info['host']."\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= "Content-Length: ".strlen($str)."\r\n";
        $head .= "\r\n";
        $head .= trim($str);
        $write = fputs($fp, $head);
        while (!feof($fp)){
            @$result.= fread($fp, 4096);
        }
        $data =  $this->GetArrXmlKey($strkey, $result);
        return $data;
    }
    
    private function GetArrXmlKey($strkey, $str){
	$strbegin = "<".$strkey;
	$arr = array();
	$pos = strpos($str, $strbegin);
	while ($pos != ""){
            $arr[] = $this->GetStanderXmlKey($strkey, $str, $pos);
            $pos = strpos($str, $strbegin, $pos + strlen($strbegin));
	}
	return $arr[0];
    }
    
    private function GetStanderXmlKey($strkey, $str, $start = 0){
	$strbegin = "<".$strkey;
	$strend = "</$strkey>";
	$pos = strpos($str, $strbegin, $start);
	if ($pos == ""){
            return "";
	}
	$strRet = substr($str, $pos + 1);
	$pos = strpos($strRet, ">");
	if ($pos == ""){
            return "";
	}
	$strRet = substr($strRet, $pos + 1);
	$pos = strpos($strRet, $strend);
	$strRet = substr($strRet, 0, $pos);
	return $strRet;
    }
}
