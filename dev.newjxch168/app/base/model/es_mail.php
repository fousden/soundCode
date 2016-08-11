<?php
namespace base\model;
use think\Model;

class EsMail extends Model{
    
    public $Host      = "smtp.mxhichina.com";
    public $Port      = 25;
    public $Username  = "noreply@jxch168.com";
    public $Password  = "COMjxch168";
    public $From      = "noreply@jxch168.com"; 
    public $FromName  = "金享财行"; 

    function send($address,$title,$content,$is_html) {
        $res = array('status'=>0,'info'=>'发送失败');
        $mail = new phpmail\phpmailer(); 
        $mail->IsSMTP();

        $mail->Host       = $this->Host;        //服务器地址
        $mail->Port       = $this->Port;        //服务器端口
        $mail->Username   = $this->Username;    //用户名
        $mail->Password   = $this->Password;    //密码
        $mail->From       = $this->From;        //邮件发送地址
        $mail->FromName   = $this->FromName;    //邮件发送人姓名
        $mail->AddAddress($address);            //收件人地址
        $mail->Subject  = $title;               //邮件主题
        $mail->Body     = $content;             //邮件内容
        $mail->WordWrap   = 50;                 // 设置每行字符串的长度
        $mail->IsHTML($is_html);                //是否为html格式

        $is_success = $mail->Send();
        $ErrorInfo = $mail->ErrorInfo;
        
        $res['status'] = intval($is_success);
        $res['info'] = $ErrorInfo;
        return $res;
    }
}

?>