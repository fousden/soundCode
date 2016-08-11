<?php
namespace base\model\sms;
use think\Model;
use base\model\sms\Sms;

class Yxtsms extends Sms{  
    protected $SpCode = "219435";
    //发送短信
    function send(){
        $res = array('status'=>0,'info'=>'发送失败');
        $arrSmsInfo                   = array();
        $arrSmsInfo['SpCode']         = $this->SpCode;
        $arrSmsInfo['LoginName']      = $this->username;
        $arrSmsInfo['Password']       = $this->password;
        $arrSmsInfo['MessageContent'] = iconv('UTF-8', 'GB2312', $this->content); // 短信内容, 最大1000个字符（短信内容要求的编码为gb2312或gbk）
        $arrSmsInfo['UserNumber']     = $this->mobile;
        $arrSmsInfo['f']              = '1';  //1 --- 提交号码中有效的号码仍正常发出短信，无效的号码在返回参数faillist中列出e
        $sendUrl = $this->host.$this->sendPath;
        
        $resArr = $this->postCommon($arrSmsInfo,$sendUrl);
        if($resArr['result'] == 0){
            $res['status'] = 1;
            $res['code'] = $resArr['task_id'];
        }
        $res['info']   = $resArr['description'];
        return $res;
    }
    
    //查询剩余条数
    function check(){
        $res = array('status'=>0,'info'=>'查询失败');
        $arrSmsInfo                   = array();
        $arrSmsInfo['SpCode']         = "219435";
        $arrSmsInfo['LoginName']      = "shhmtx";
        $arrSmsInfo['Password']       = "Shhmt2015";
        $checkUrl = "http://zx.ums86.com:8899/sms/Api/SearchNumber.do?";
        
        $resArr = $this->postCommon($arrSmsInfo,$checkUrl);
        if($resArr['result'] == 0){
            $res['status'] = 1;
            $res['info']   = "剩余条数(包括套内和可超发条数)：".$resArr['number'] .'条';
        }else{
            $res['code']   = $resArr['result']; 
        }
        
        return $res;
    }
    
    private function postCommon($arrSmsInfo,$url){
        $strParam   = '';
        foreach ($arrSmsInfo as $k => $v) {
            $strParam.="$k=" . $v . '&';
        }
        $str = substr($strParam, 0, -1);
        $ch        = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
        $res       = curl_exec($ch);
        $resIconv  = iconv('GB2312', 'UTF-8', $res);
        parse_str($resIconv, $resArr);
        return $resArr;
    }
}
