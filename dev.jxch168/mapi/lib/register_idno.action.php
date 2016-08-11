<?php

class register_idno {

    public function index() {
        //require_once APP_ROOT_PATH."system/libs/user.php";

        $root = array();
        $root['status'] = 0;
        $email = strim($GLOBALS['request']['email']); //用户名或邮箱
        $pwd = strim($GLOBALS['request']['pwd']); //密码
        //检查用户,用户密码
        $user = user_check($email, $pwd);
        $user_id = intval($user['id']);

        //
        if (isset($_REQUEST['step']) && $_REQUEST['step'] == 2) {
            //是否开启《平安保险网银卫士》资金安全担保条款
            $insurance_show = $GLOBALS['db']->getOne("select value from " . DB_PREFIX . "conf where name = 'PINGANPROVISION'");
            // 如果选择了投保
            if (!$insurance_show == 1) {
                $root['response_code'] = 0;
                $root['show_err'] = "保险未开启,请联系客服";
            } else {
                $user_info_re['status'] = 1;
            }
            $res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $user_info_re, "UPDATE", "id=" . $user_id);
            if (!$res) {
                $root['response_code'] = 0;
                $root['show_err'] = "操作失败";
            } else {
                $root['response_code'] = 2;
                $root['show_err'] = "操作成功";
            }
            output($root);
        }

        $insurance = intval($GLOBALS['request']['insurance']); //获取是否选中保险
        if ($user_id > 0) {

            $root['user_login_status'] = 1;

            $real_name = strim($GLOBALS['request']['real_name']);
            $idno = strim($GLOBALS['request']['idno']);

            if (!$real_name) {
                $root['response_code'] = 0;
                $root['show_err'] = "请输入真实姓名";
                output($root);
            }


            if ($idno == "") {
                $root['response_code'] = 0;
                $root['show_err'] = "请输入身份证号";
                output($root);
            }

            if (getIDCardInfo($idno) == 0) {
                $root['response_code'] = 0;
                $root['show_err'] = "身份证号码错误！";
                output($root);
            }

            //$root['show_err'] = $idno;output($root);
            //判断该实名是否存在
            if ($GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "user where idno = '.$idno.' and id<> $user_id") > 0) {
                $root['response_code'] = 0;
                $root['show_err'] = "该实名已被其他用户认证，非本人请联系客服";
                output($root);
            }

            $user_info_re = array();
            $user_info_re['real_name'] = $real_name;
            $user_info_re['idno'] = $idno;
            $user_info_re['byear'] = substr($idno, 6, 4);
            $user_info_re['bmonth'] = substr($idno, 10, 2);
            $user_info_re['bday'] = substr($idno, 12, 2);
            $user_info_re['sex'] = substr($idno, -2, 1) % 2 ? 1 : 0;
            $user_info_re['idcardpassed'] = 1;

            //是否开启《平安保险网银卫士》资金安全担保条款
            $insurance_show = $GLOBALS['db']->getOne("select value from " . DB_PREFIX . "conf where name = 'PINGANPROVISION'");
            // 如果选择了投保
            if ($insurance == 1 && $insurance_show == 1) {
                $user_info_re['status'] = 1;
            }

            $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $user_info_re, "UPDATE", "id=" . $user_id);
            //实名送100红包活动
            MO("ActivityConf")->ActivityConfByType(1, $user['id']);

            if (intval(app_conf("OPEN_IPS")) > 0) {
                $app_url = APP_ROOT . "/index.php?ctl=collocation&act=CreateNewAcct&user_type=0&user_id=" . $user_id . "&from=" . $GLOBALS['request']['from'];
                $root['app_url'] = str_replace("/mapi", "", SITE_DOMAIN . $app_url);
                $root['acct_url'] = $root['app_url'];
            }
            
           //实名送积分
            MO('UserScore')->add_score($user_id,5);

            $root['open_ips'] = intval(app_conf("OPEN_IPS"));
            $root['response_code'] = 1;
            $root['show_err'] = "验证成功";
            $root['status'] = 1;
        } else {
            $root['response_code'] = 0;
            $root['show_err'] = "未登录";
            $root['user_login_status'] = 0;
        }

        $root['program_title'] = "身份证验证";
        output($root);
    }

}

?>