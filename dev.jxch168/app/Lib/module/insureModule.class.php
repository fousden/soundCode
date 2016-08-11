<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class insureModule extends SiteBaseModule{
    public function pingan(){
         ini_set('display_errors', 'On');
         error_reporting(E_ALL);
         $name='楚尚明';
         $sex = '1';
         $birth='1990-07-07';
         $mobile = '13122905536';
         $uid = '2';
        MO("insure")->getInfo($uid,$name,$sex,$birth,$mobile);
    }
}

