<?php
class WechartHleper {
    //cache存放目录
	private $cacheFileUrl ;
    //参数配置
	private $parms = array(
		"appId"=>"wxfd3462aa749cb1e1",
		"appSecret"=>"6a3544fa00dd17cff35613e344af0a1d"
	);
    //请求超时时间
    private $accessTokenTimeOut  = 7100;
    //构造函数
   function __construct() {
		//cache存放目录
	   $this->cacheFileUrl = dirname(dirname(dirname(dirname(__FILE__)))) . '/Uploads/wechat.cache';
   }
   
   //获取AccessToken
	public function getAccessToken(){
        //如果文件存在,获取token 否则为null
		$token = file_exists($this->cacheFileUrl)?file_get_contents($this->cacheFileUrl):null;
        //如果token不存在
       //如果为null或者 少于7100毫秒
		if (is_null($token)||time() - filemtime($this->cacheFileUrl)>$this->accessTokenTimeOut)
		{
            //请求accessToken的地址 
			$r = @file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->parms["appId"].'&secret='.$this->parms["appSecret"]);
			$token = json_decode($r,true)["access_token"];
			file_put_contents($this->cacheFileUrl, $token);
		}
		return $token;
	}

    //获取用户授权
    public function getUserAccessCode($redirect_uri){
        //echo 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->parms["appId"].'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=1#wechat_redirect';
        header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->parms["appId"].'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state=1#wechat_redirect');
        exit;
    }
}

