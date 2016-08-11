<?php
/**
 * 微信用户授权
 * @author dch
 */
class userOauth extends weixin {

    /**
     * 根据code查询微信用户的openid值
     * @param type $code
     * @return type
     */
    public function get_openid($code) {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . self::appid . '&secret=' . self::secret . '&code=' . $code . '&grant_type=authorization_code';
        $code_data = $this->curl_post($url);
        return $code_data['openid'];
    }

    /**
     * 根据openid查询微信用户的相关信息
     * @param type $openid
     */
    public function getWeixinUserInfo($openid) {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->access_token . '&openid=' . $openid;
        $user_info = $this->curl_post($url);
        return $user_info;
    }

    /**
     * 
     */
    public function authorization() {
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::appid . '&redirect_uri=http:dch.dev.jxch168.com/wap/index.php&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
        header('location:' . $url);
    }

}