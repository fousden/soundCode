<?php

/**
 * 模板消息接口
 *
 * @author dch
 */
class templateMsg extends weixin {
//    public function __construct() {
//        parent::__construct();
//    }

    /**
     * 设置所属行业
     */
    public function api_set_industry() {
        $post = '{
          "industry_id1":"8",
          "industry_id2":"4"
       }';
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=' . $this->access_token;
        $data = $this->curl_post($url, $post);
        return $data;
    }

    /**
     * 获取设置的行业信息
     */
    public function get_industry() {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token=' . $this->access_token;
        $data = $this->curl_post($url);
        return $data;
    }

    /**
     * 获得模板ID
     */
    public function api_add_template() {
        $post = '{
           "template_id_short":"TM00015"
       }';
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token=' . $this->access_token;
        $data = $this->curl_post($url, $post);
        return $data;
    }

    /**
     * 获取模板列表
     */
    public function get_all_private_template() {
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=' . $this->access_token;
        $data = $this->curl_post($url, $post);
        return $data;
    }

    /**
     * 删除模板
     */
    public function del_private_template($template_id) {
        $post = '{
            "template_id":"HF_j4MrmqG3m8FtCqOxZNyWdm5W3I8DqqpV0S-dL8VY"
            }';
        $url = 'https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=' . $this->access_token;
        $data = $this->curl_post($url, $post);
        return $data;
    }

    /**
     * 发送模板
     */
    public function send_template($openid, $template_id, $data, $url = '', $is_queue = false) {
        $post_arr['touser'] = $openid;
        $post_arr['template_id'] = $template_id;
        $post_arr['url'] = $url;
        $post_arr['data'] = serialize($data);
        if ($is_queue) {
            $post_arr['data'] = serialize($data);
            return MO('WeixinTemplateMsgBox')->addTemplate($post_arr);
        } else {
            $post_arr['data'] = $data;
            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->access_token;
            $data = $this->curl_post($url, json_encode($post_arr));
            return $data;
        }
    }

}
