<?php

class uc_invite{
	public function index(){

		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
        $_mobile = strim($GLOBALS['request']['_m']);//设备类型

		//检查用户,用户密码
        $user = user_check($email,$pwd);
        $user_id  = intval($user['id']);

        if ($user_id >0){
        	$root['user_login_status'] = 1;

        	$referral_user_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where pid = ".$user_id." and is_effect=1 and is_delete=0 AND user_type in(0,1) ");

            if(intval(app_conf("URL_MODEL")) == 0)
                $depart="&";
            else
                $depart="?"; 
            
            $share_url = SITE_DOMAIN . '/wap/index.php?ctl=register';
            
        	$share_url_mobile = $share_url.$depart."r=".base64_encode($user['mobile']);
			$share_url_username = $share_url.$depart."r=".base64_encode($user['user_name']);

            $site_url = str_replace("/mapi", "", SITE_DOMAIN.APP_ROOT)."/";//站点域名;

            $root['android_down_url'] = $site_url.$GLOBALS['m_config']['android_filename'];//android下载包名
            $root['ios_down_url'] = $GLOBALS['m_config']['ios_down_url'];   //IOS应用地址

            $root['referral_user_count'] = $referral_user_count;
            $root['share_url_username'] = $share_url_username;
            $root['share_url_mobile'] = $share_url_mobile;
        }else{
        	$root['response_code'] = 0;
            $root['show_err'] ="未登录";
            $root['user_login_status'] = 0;
        }

        $root['program_title'] = "好友邀请";
        output($root);		
	}
}