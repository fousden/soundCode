<?php

/**
 * 微信接口的基类
 */
class weixin {

    /**
     * 设置公众号token的存放位置
     */
    const path = "../api/weixin/weixin.txt";

    /**
     * 公众号的appid(在公众号后台可见)
     */
    const appid = "wxa08a258ed51ab4d0"; //dch测试号

    /**
     * 公众号的secret(在公众号后台可见)
     */
    const secret = "804871c606c1b931d67ca7da0b087d13"; //dch测试号

    /**
     * 公众号的token失效时间，暂时为2个小时-10秒
     */
    const token_efficacy_time = 7190;
//    const token_efficacy_time = 0;
    const authcode_key = "jxch168weixin";

    protected $access_token;

    public function __construct() {
//        echo '<pre>';var_dump(self::path);echo '</pre>';die;
        if (time() - filemtime(self::path) >= self::token_efficacy_time) {
            $token_data = $this->get_access_token();
            if ($token_data) {
                file_put_contents(self::path, authcode($token_data['access_token'], "", self::authcode_key));
                $this->access_token = $token_data['access_token'];
            }
        } else {
            $this->access_token = authcode(file_get_contents(self::path), "DECODE", self::authcode_key);
        }
    }

    /**
     * 获得微信公众号的access_token值
     * @return type
     */
    private function get_access_token() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . self::appid . "&secret=" . self::secret;
        return $this->curl_post($url);
    }

    /**
     * curl请求
     * @param type $url 微信公众平台api接口的url
     * @param type $post 微信公众平台api接口的post数据包
     * @return 返回请求接口后的数据，数据类型为数组
     */
    public function curl_post($url, $post = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);  //post
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); //post内容
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return json_decode(curl_exec($ch), true);
    }

}
