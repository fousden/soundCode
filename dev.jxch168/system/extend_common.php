<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
//功能：系统扩展的函数库
// +----------------------------------------------------------------------
//显示富友错误提示信息
function show_fuyou_remind($remind_codes, $resp_code) {

    if (!$remind_codes[$resp_code]) {
        $fuyou_error_remind = '绑卡开户失败';
    } else {
        $fuyou_error_remind = $remind_codes[$resp_code] . '';
    }

    return $fuyou_error_remind;
}

//刮刮卡 大转盘红包活动
function send_bonus($user_info, $reg_time, $active, $bonusId) {
    if ($active == 'Lottery') {
        $bonus_type = 3;
    } else if ($active == 'guajiang') {
        $bonus_type = 4;
    }
    $now_active = strtolower($active);
    $active_config = require_once APP_ROOT_PATH . 'data_conf/' . $now_active . '_active.php';
    //获取活动配置信息
    $act_active = $active_config[$now_active];
    //获取活动状态
    if ($act_active['ACT_STATUS'] == 1) {
        //获取配置信息 计算活动收益 并插入数据库 前提要增加相应数据库字段
        $act_start_time = strtotime($act_active['ACT_START_TIME']);
        $act_end_time = strtotime($act_active['ACT_END_TIME']);
        //如果在活动期间注册 即送红包
        if ($reg_time >= $act_start_time && $reg_time <= $act_end_time) {
            //初始化红包数据
            $user_bonus['deal_id'] = 0;
            $user_bonus['deal_load_id'] = 0;
            $user_bonus['user_id'] = $user_info['id'];
            $user_bonus['reward_name'] = $act_active['ACT_NAME'];
            $user_bonus['money'] = $act_active['ACT_BONUS_ID'][$bonusId];
            $user_bonus['status'] = 0; //未处理
            $user_bonus['cash_type'] = 0; //手动提现
            $user_bonus['cash_period'] = 3; //提现周期为3天
            $user_bonus['bonus_type'] = $bonus_type; //3代表大转盘
            $user_bonus['generation_time'] = time(); //红包生成时间
            $user_bonus['apply_time'] = 0; //默认红包提现申请时间
            $user_bonus['remark'] = $act_active['ACT_NAME'];
            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $user_bonus, "INSERT"); //插入红包数据
            $user_bonus_id = $GLOBALS['db']->insert_id();
            if ($user_bonus_id > 0) {
                //如果成功 则短信通知
//                        $msg = "尊敬的". app_conf("SHOP_TITLE") ."用户". $user_info['user_name']  . "，您的投标" .$act_active['ACT_NAME']. "活动获得红包奖励，红包金额".$user_bonus['money']."，奖励已于".to_date(TIME_UTC,"Y-m-d")."发放。";
//                        $msg_data['dest'] = $user_info['mobile'];
//                        $msg_data['send_type'] = 0;
//                        $msg_data['title'] = "投标红包短信通知";
//                        $msg_data['content'] = addslashes($msg);;
//                        $msg_data['send_time'] = 0;
//                        $msg_data['is_send'] = 0;
//                        $msg_data['create_time'] = TIME_UTC;
//                        $msg_data['user_id'] = $user_info['id'];
//                        $msg_data['is_html'] = 0;
//                        $GLOBALS['db']->autoExecute(DB_PREFIX."deal_msg_list",$msg_data,"INSERT"); //插入
                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_weixin_bonus.log', "POST:[" . $user_info['user_name'] . "的红包发放成功" . json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            } else {
                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_weixin_bonus.log', "POST:[" . $user_info['user_name'] . "的红包发放失败" . json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            }
        }
    }
}

function getHb($uid, $money) {
    //初始化红包数据
    $user_bonus['deal_id'] = 0;
    $user_bonus['deal_load_id'] = 0;
    $user_bonus['user_id'] = $uid;
    $user_bonus['reward_name'] = "大转盘活动";
    $user_bonus['money'] = $money;
    $user_bonus['status'] = 0; //未处理
    $user_bonus['cash_type'] = 0; //手动提现
    $user_bonus['cash_period'] = 3; //提现周期为3天
    $user_bonus['bonus_type'] = 5; //3代表大转盘
    $user_bonus['generation_time'] = time(); //红包生成时间
    $user_bonus['apply_time'] = 0; //默认红包提现申请时间
    $user_bonus['remark'] = "大转盘活动";
    $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $user_bonus, "INSERT"); //插入红包数据
    return $GLOBALS['db']->insert_id();
}

//获取中秋活动收益
function get_act_income($mid_autumn_act, $interest_money, $bid_time) {
    //获取活动配置信息
    if ($mid_autumn_act['ACT_STATUS'] == 1) {
        //获取配置信息 计算活动收益 并插入数据库 前提要增加相应数据库字段
        $act_start_time = strtotime($mid_autumn_act['ACT_START_TIME']);
        $act_end_time = strtotime($mid_autumn_act['ACT_END_TIME']);
        if ($bid_time >= $act_start_time && $bid_time <= $act_end_time) {
            $active_interest_money = $interest_money * $mid_autumn_act['ACT_RATE'];
        }
    }
    return $active_interest_money;
}

//投标成功 如果该用户是被邀请投资 则邀请人将获得随机1-20元随机现金红包
function invite_active($bid_money) {
    //获取活动配置信息
    $invit_bid_act = require_once APP_ROOT_PATH . 'data_conf/invit_bid_active.php';
    $invit_bid_act = $invit_bid_act['invit_bid_acting'];
    if ($invit_bid_act['ACT_STATUS'] == 1) {
        //获取活动时间
        $act_start_time = strtotime($invit_bid_act['ACT_START_TIME']);
        $act_end_time = strtotime($invit_bid_act['ACT_END_TIME']);

        $now_time = time();
        //当前时间必须在活动期间之内
        if ($now_time >= $act_start_time && $now_time <= $act_end_time) {
            //判断该用户注册时间是否是在活动期间注册的
            $user_regiter_time = $GLOBALS['user_info']['create_time'];
            if ($user_regiter_time >= $act_start_time && $user_regiter_time <= $act_end_time) {
                //判断该用户是否有邀请人
                $_sql = 'select * from ' . DB_PREFIX . 'user where id = ' . $GLOBALS['user_info']['pid'] . ' AND is_effect = 1 AND is_delete = 0';
                $user_pid = $GLOBALS['db']->getRow($_sql);
                //如果存在该邀请人 根据被邀请人投资金额随机送现金红包
                if ($user_pid) {
                    //查询活动期间投资记录
                    $sql = 'select * from ' . DB_PREFIX . 'deal_load where user_id = ' . $GLOBALS['user_info']['id'] . ' AND is_auto = 0 AND create_time >= ' . $act_start_time . ' AND create_time <= ' . $act_end_time;
                    $deal_loads = $GLOBALS['db']->getAll($sql);
                    $count = count($deal_loads);
                    //首笔订单
                    if ($count == 1) {
                        //根据规则随机产生红包
                        $invest_range = explode(',', $invit_bid_act['ACT_RULES']['ACT_INVEST_RANGE']);
                        $bonus_range = explode(',', $invit_bid_act['ACT_RULES']['ACT_REWARD_RANGE']);
                        //根据规则随机红包金额 控制概率为20%
                        if ($bid_money >= $invest_range[0] && $bid_money < $invest_range[1]) {
                            //投资金额在0—100范围内      获得1—5元现金红包
                            $bonus_money = mt_rand($bonus_range[0], $bonus_range[1]);
                        } else if ($bid_money >= $invest_range[1] && $bid_money < $invest_range[2]) {
                            //投资金额在100—1000范围内   获得5—10元现金红包
                            $bonus_money = mt_rand($bonus_range[1], $bonus_range[2]);
                        } else if ($bid_money >= $invest_range[2] && $bid_money < $invest_range[3]) {
                            //投资金额在1000—5000范围内  获得10—15元现金红包
                            $bonus_money = mt_rand($bonus_range[2], $bonus_range[3]);
                        } else if ($bid_money >= $invest_range[3]) {
                            //投资金额在5000以上          获得15—20元现金红包 概率20%
                            $bonus_money = mt_rand($bonus_range[3], $bonus_range[4]);
                        }
                        //判断是否发放
                        if ($bonus_money) {
                            //初始化红包数据
                            $user_bonus['deal_id'] = $deal_loads[0]['deal_id']; //借款ID
                            $user_bonus['deal_load_id'] = $deal_loads[0]['id']; //投资id
                            $user_bonus['user_id'] = $user_pid['id'];
                            $user_bonus['reward_name'] = $invit_bid_act['ACT_NAME'];
                            $user_bonus['money'] = $bonus_money;
                            $user_bonus['status'] = 1; //默认已提交红包提现申请
                            $user_bonus['cash_type'] = 1; //自动提现
                            $user_bonus['cash_period'] = 3; //提现周期为3天
                            $user_bonus['bonus_type'] = 2; //2 邀请投资
                            $user_bonus['generation_time'] = time(); //红包生成时间
                            $user_bonus['apply_time'] = time(); //默认红包提现申请时间
                            $user_bonus['release_time'] = strtotime("+3days", $user_bonus['apply_time']); //预计发放时间
                            $user_bonus['release_date'] = strtotime(date('Y-m-d', $user_bonus['release_time'])); //预计发放日期
                            $user_bonus['remark'] = $invit_bid_act['ACT_NAME'];
                            $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bonus", $user_bonus, "INSERT"); //插入红包数据
                            $user_bonus_id = $GLOBALS['db']->insert_id();
                            if ($user_bonus_id > 0) {
                                //发送短信通知
                                //如果成功 则短信通知
                                $TPL_SMS_NAME = "TPL_SMS_INVITE_BONUS";
                                $tmpl = $GLOBALS['db']->getRow("select * from " . DB_PREFIX . "msg_template where name = '" . $TPL_SMS_NAME . "'");
                                $tmpl_content = $tmpl['content'];
                                $notice['user_name'] = $user_pid['user_name'];
                                $notice['invite_name'] = $GLOBALS['user_info']['user_name'];
                                $notice['money'] = $bonus_money;
                                $GLOBALS['tmpl']->assign("notice", $notice);

                                $msg = $GLOBALS['tmpl']->fetch("str:" . $tmpl_content);
                                $msg_data['dest'] = $user_pid['mobile'];
                                $msg_data['send_type'] = 0;
                                $msg_data['title'] = "现金红包短信通知";
                                $msg_data['content'] = addslashes($msg);
                                $msg_data['send_time'] = 0;
                                $msg_data['is_send'] = 0;
                                $msg_data['create_time'] = TIME_UTC;
                                $msg_data['user_id'] = $user_pid['id'];
                                $msg_data['is_html'] = 0;
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data, "INSERT"); //插入
                                //send_sms_email($msg_data);

                                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_invest_bonus.log', "POST:[" . $user_pid['user_name'] . "参与" . $invit_bid_act['ACT_NAME'] . "活动成功获取现金红包" . $bonus_money . "元" . json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                            } else {
                                file_put_contents(APP_ROOT_PATH . 'log/userbonus/' . date('Y-m-d') . '_user_invest_bonus.log', "POST:[" . $user_pid['user_name'] . "参与" . $invit_bid_act['ACT_NAME'] . "活动获取现金红包失败" . json_encode($user_bonus) . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                            }
                        }
                    }
                }
            }
        }
    }
}

//生成N个不重复的会员记录 用于自动投标
function create_users($user_num) {
//    if ($user_num < 0) {
//	$delete_num = abs($user_num);
//	$del_sql = "delete from " . DB_PREFIX . "user where is_auto = 1 AND user_name != '李明明' limit " . $delete_num;
//	$GLOBALS['db']->autoQueryBySql($del_sql);
//    } else {
    //生成N个不重复的会员记录 用于自动投标
    for ($i = 0; $i < $user_num; $i++) {
        //获取手机号码
        $mobile = "";
        $mobile_exist = 1;
        while ($mobile_exist) {
            $mobile = getMobileNo();
            $mobile_exist = $GLOBALS['db']->getOne("select id from " . DB_PREFIX . "user where acct_type is null AND is_delete = 0 AND  is_effect = 1 AND user_type = 0 AND mobile = '" . $mobile . "'");
        }
        //获取用户名
        $new_user_name = get_user_names();
        if (!$new_user_name) {
            /* $randStr = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
              $new_user_name = substr($randStr, 0, rand(6, 8)); */
            $new_user_name = $mobile;
        }

        $user_data = array(
            'user_name' => $new_user_name,
            'user_pwd' => '67d0d20751175881bd2f8ece381b5dfd',
            'create_time' => strtotime('-1 month'),
            'update_time' => strtotime('-2 weeks'),
            'login_ip' => '140.206.127.19' . rand(2, 8),
            'group_id' => 1,
            'is_effect' => 1,
            'is_delete' => 0,
            'email' => rand(1, 9) . time() . '@qq.com',
            'idno' => '45030419' . rand(4, 9) . rand(0, 9) . '0' . rand(0, 9) . rand(0, 2) . rand(0, 9) . '1013',
            'idcardpassed' => 1,
            'idcardpassed_time' => strtotime('-1 month'),
            'real_name' => $new_user_name,
            'mobile' => $mobile,
            'mobilepassed' => 1,
            'money' => rand(1000000, 1000000000),
            'login_time' => strtotime('-1 day'),
            'locate_time' => strtotime('-1 day'),
            'level_id' => 1,
            'paypassword' => '67d0d20751175881bd2f8ece381b5dfd',
            'register_ip' => '140.206.126.42',
            'user_type' => '0',
            'terminal' => 1
        );
        $user_data['byear'] = substr($user_data['idno'], 6, 4);
        $user_data['bmonth'] = substr($user_data['idno'], 10, 2);
        $user_data['bday'] = substr($user_data['idno'], 12, 2);
        //自动投标用户
        $user_data['is_auto'] = 1;
        $GLOBALS['db']->autoExecute(DB_PREFIX . "user", $user_data, "INSERT");
    }
    //}
}

//获取唯一的一个用户名
function get_user_names() {
    $user_name_arr = array('陈萌', '贾新格', '李锦花', '张俊莲', '曹守月', '杨立华', '戴影兰', '赵国良', '朱晨弘', '邬佳丽', '孙恒', '王莹', '张彗', '蒋昭红', '陈瑶', '张峥', '冯彬玲', '叶鹏来', '陈曦', '王惠兰', '陈效', '裴芬', '曹阳', '孙旸', '潘国鹏', '徐争鸣', '陈妍', '吴晓龙', '王洵', '周建华', '王蓉蓉', '于泳', '袁满', '胡坤', '朱为准', '钟敏', '徐海峰', '王潇荔', '种亚男', '唐杰', '付义平', '鲁雅萍', '陈爱武', '梁湘', '鲍晨飞', '董明', '尹崇博', '姚汉春', '韩德胜', '于银学', '齐凌宇', '张文', '张可夫', '罗洁', '李益全', '蔡涛', '杜俊国', '谷鹏', '秦佳', '谷晨蕊', '王炎', '李亚辉', '张百刚', '范玲敏', '刘泓', '刘博莹', '曹彦彬', '白松园', '赵梦溪', '胡美娜', '徐旸', '顾佳佳', '戚长鹤', '陈伟', '姜艳彬', '徐磊', '薛立伟', '李茂瑞', '童秋君', '窦茹锐', '安然', '王律锋', '戴钰', '严圣明', '徐翔', '毕澜潇', '刘晓晔', '董嵩林', '宋晓辰', '李文勇', '刘娜', '赵江', '赵逸明', '张剑秋', '易宁', '张涛', '黄茂林', '徐明', '陈旭波', '姜佩琴', '朱梓焱', '王正韬', '朱瑜挺', '李嵩', '丰晓伟', '刘光强', '胡林', '刘文星', '王佳瑶', '倪圆佳', '冯永昕', '高春', '张楠', '侯嘉丰', '李响宇', '陈晶晶', '杨子尘', '倪晓东', '宋殿学', '沈伟昀', '蔡广成', '李明彩', '刘江', '郑孝腾', '李婧菁', '陈亮', '石文武', '杨宏屹', '刘斐', '冯丽萍', '聂晓云', '林韵', '储吉磊', '董苇', '邢军民', '肖矩', '王洪凯', '李靖泽', '王建君', '朱闻博', '陈勇', '韩庆彬', '谢苏莉', '李龙', '陈秋林', '王洪宾', '杨恒', '陈凡', '林薇', '王丽菁', '李月健', '周军', '黄健胜', '鲍丹丹', '赵雅玲', '陈德威', '张扬', '王启东', '张俊', '贾超', '朱寅', '曾琴琴', '秦澎湃', '沈源灏', '廖朝昶', '崔天宇', '石磊', '李炜', '左刚', '王晓', '姚董宇', '马良胤', '阳熙', '张海舰', '张旭', '胡锋', '于盟盟', '赵福军', '王晓莹', '张丽', '刘源', '张伟', '岳永彩', '许大鹏', '吴艳', '陈朔丽', '陈雳', '赵晔', '黎辉', '王艳', '连进', '方秀莉', '陈绮梅', '韩荆玉', '张捷', '周佳', '李培刚', '张怡', '虞敏佳', '王亚红', '张丽萍', '耿重', '关海波', '姚晟', '王圆圆', '方晋晔', '李志灵', '凌金龙', '宋志洁', '凌汝鑫', '于喜梅', '陈永驰', '黄高峰', '童海蓉', '陈淼', '江睿', '吴海波', '覃莉', '张大伟', '阎晟', '成赟', '李健衡', '温嘉健', '张弟', '陈鹿', '高亦雯', '张仁书', '海镇城', '曹宏彬', '高云', '卢悦', '张继光', '裘祎', '程晓曾', '陈大文', '李俊', '于振寰', '申为国', '何传杰', '郝鹏', '曹彦彬', '吕磊', '曹毅', '杨锐', '汤思磊', '贺炜', '邢轩', '陈李超', '姜淼', '何安', '张晓伟', '郭玮', '陆春燕', '费宏', '鲁俊逸', '吴金友', '朱小平', '朱瑾', '杨珊', '王康', '罗兴洪', '王磊', '李文健', '陈宗泽', '孟岩', '钟良尧', '姚俊国', '薛跃磊', '蔡红珍', '沈冠中', '李薇薇', '满磊', '乔力', '杨钦', '陈洋', '李杰', '王瑞勇', '张丽力', '沈蓉莉', '刘倩熙', '包刚', '李明', '钟霞炜', '林国荣', '钟娜君', '于婷婷', '王海涛', '牛红艺', '吴剑', '段缚鲲', '甄蓉', '陈玮', '章亚伟', '金天虎', '金鼎', '杨珊', '历娜', '朱联璧', '匡育新', '孙炜', '杨卫平', '班敏娟', '黄建军', '赵琳', '孙湛', '陈志芳', '刘方忠', '张帆', '王为尹', '李慧贤', '王海荣', '范月屏', '骆丽芳', '王玮', '彭雨', '赵姗姗', '赵鲲翔', '王闻博', '陈伟峰', '陈培', '李晓庆', '李云祺', '吴春云', '张彬', '程耀华', '陈玮', '王一凡', '陈韡楠', '李培欣', '潘剑', '许立新', '吐尔迪加玛', '罗善华', '王韬', '陶勇', '张弛', '赵奇', '吴迪', '徐胜', '黄永强', '石海龙', '苗杰', '朱彬', '明涛', '倪建红', '杨伟清', '王顺', '陈凯', '桑永', '余旭成', '康先生', '肖小姐', '岳佳楠', '包红杰', '黄桂开', '袁伟军', '王美君', '周孝君', '曾琼', '易骏', '盛浩宇', '王文耕', '雷宇鹏', '沈晋文', '陈念慈', '王金良', '刘梦尧', '王勇', '余向红', '石岳', '杜宗峰', '张磊', '周国星', '林慕洁', '许少华', '刘仁重', '印云庆', '江安', '王欣阳', '李全旗', '郑海', '严亘晖', '顾滢斌', '杨雷', '汪春凯', '吴骞', '李德俊', '林勇', '张军民', '陈东麟', '王瑞', '谈莉莉', '唐佳琪', '周琳', '陈琦', '满灏麟', '赖晓华', '唐凯', '李欣', '王伟', '宋阳', '李岩', '樊帆', '陈照腾', '刘继兵', '张春晖', '王科奋', '康井斌', '李象威', '李绮丽', '徐良', '方继成', '康钦松', '王彦松', '江少', '魏军辉', '管宏喜', '蒋烜', '姚运磊', '李洪', '陈曦', '于超', '杨雨新', '严雄', '王倞', '顾笑傲', '袁平珍', '徐嘉耀', '许壮', '王怡', '潘浩', '徐釗欽', '薛常青', '陈港', '李雅峰', '曹磊', '王鹏', '张进', '陈静怡', '汪海宁', '夏鲁全', '林凌', '陆怡冰', '黄拥军', '沈斌', '刘后平', '励浩', '李铮', '李芳', '王立', '张国强', '吳行恭', '岱', '吴彬', '张丽华', '程琳', '童话', '马杰', '孟祥旺', '高鲲鹏', '于洪颖', '翟广华', '顾为民', '陈果', '唐凯', '郑增华', '寿鸿斌', '熊丽', '沈昊', '张陆', '金成', '金冉', '廖锋良', '陈晶', '刘晓勇', '曾漢性', '赵飞军', '方懋旻', '李闻莺', '郑筱青', '杨柏军', '符之傑', '谷静', '应威胜', '纪程炜', '陈宇', '赖欣龙', '金曼', '黄生', '孙朝朝', '蒋泓', '汤正凯', '刘燕华', '魏俊', '殷黎明', '周琼', '吕跃强', '张飞', '王晔', '张柯', '武丹', '王智芳', '熊玮', '练卫堪', '许晓亮', '钱能', '彭松鑫', '孙嵩涛', '李良', '尹东燕', '吴威', '彭敏', '李志刚', '易忠良', '赵彬', '熊淼', '王曦', '韩锦铖', '姚辉', '闵捷', '陆群晖', '梁微', '姜运忠', '徐飞', '胡歆钰', '周伟', '谢君', '陈萍玲', '韩家康', '丁天', '张伟', '王海德', '雷著贵', '梅浩', '杨红力', '智荣根', '李诚柠', '小猴子', '魏绍麟', '何蓓莉', '陈宇欢', '彭险峰', '刘丹', '徐睿', '吴嘉雯', '杨家栋', '周小姐', '李佳', '杜翔', '夏蔚东', '胡阳', '王超', '王雷', '王酉堂', '吕晓娜', '张建玲', '赵伟', '陈宝延', '徐扬', '胡文君', '江中', '张又新', '王冠创', '樊杭强', '陈家文', '何祥乐', '龚一舟', '杨纬敏', '陈萍', '梁凯', '张坚', '张泽潞', '洪伟', '朱家盛', '闻华', '张宋金', '罗昕欣', '钟国江', '黎国斌', '杨辉', '陈锋', '赵超', '韩宜恒', '孙晓东', '高菲', '王福', '黄晓萍', '马勇', '魏雯洁', '徐方方', '孙东', '陈凌云', '陈思龙', '苏萌', '赵超', '卢致标', '李建强', '黄美蓉', '兰春茂', '沈鹤', '甘述玮', '肖阳', '徐纪英', '汝林', '周先生', '黄玉琴', '周晨', '吴美智', '杨秀丽', '龚建华', '金美兰', '戴海伟', '孟献钢', '缪奇恩', '何燕玲', '金国鑫', '甘国庆', '杨彦品', '海伟', '任晓芳', '吴博', '马米莎', '李盛男', '李信心', '赵伟', '余霞', '汤伟', '徐缨', '赵金平', '魏晔', '张黎', '丁建伟', '张珺', '沈定', '郑铎伟', '徐彦杰', '花铮', '蔡珏', '谢丰', '张毅', '李航', '韩昕', '沈佳铖', '罗平伟', '马远炯', '徐甄妮', '徐剑', '刘洪林', '朱邦胜', '柏虎', '宗华', '沈少华', '黄建根', '张春林', '殷栋林', '黄剑', '王明银', '李彤', '李杰', '陆建强', '徐静珍', '杨小燕', '王燕', '趋雄', '李双斐', '杨栩', '陈亮', '陈雷', '杨刚', '张世静', '莫莉', '程劼', '王军', '黄鸣泉', '张晓宏', '王志和', '徐春慧', '陈燕', '陈科凌', '曹瑞刚', '俞国雄', '康志斌', '万米', '周铭', '魏婷', '杨媛', '栗淼', '李洋', '卢罡', '车秋立', '王建新', '柯路', '唐志强', '田智君', '龚光耀', '俞鸿', '刘磊', '王瑶', '曹东', '陈丹', '张银薇', '董逊', '章艳', '王彧', '彭金花', '杨剑', '殷斯力', '苏青峰', '孔凌伟', '闫永进', '季皓巍', '刘笑雨', '周琴', '王勇', '何雅芳', '钱洲', '李文婷', '崔昕宁', '周宠', '刘军', '曹焱星', '孙嘉文', '吕鹏', '王桂江', '范保胜', '葛毛毛', '胡子风', '东铭', '程永强', '沈康', '李琦', '汪杰', '康美', '吴美琴', '王少鹏', '孙雪雄', '冯冬蕾', '蒋雪芬', '胡海燕', '陈晓涛', '苗华', '曹朝辉', '宋杨', '郑洪安', '彭军', '王霞云', '沈浩', '钟梦英', '徐春', '梁冠军陈晓安', '李华', '胡晓晔', '薛靖', '陈永平', '赵补', '王如澜', '陆斌', '朱建原', '陈夷茂', '萧嘉宁', '李凯', '温朝清', '沈良', '蒋瑞芳', '董泽正', '吴红牛', '温暖', '谢靖', '范金越', '严学金', '时林峰', '王蕊', '邱鸣', '梁海洋', '杨茜', '喻凯', '马伟', '党海平', '黄亮', '林均秀', '马廷和', '林伟', '葛伟磊', '夏鹏', '支黎峰', '黄金军', '轩大鹏', '姚弘', '蒋梦迪', '赵元丰', '黄木发', '张琦', '邵志芳', '柳艳', '陶甄', '邱云翔', '韩海明', '徐孝', '谢二桂', '程杰', '潘岩', '邓子琳', '李波', '赵红静', '王艳', '肖铁成', '王丽', '张虹', '戴静', '袁亮亮', '邓平', '伍晓菁', '王刚', '钱康', '单斌', '顾坚正', '刘迪', '孙蕾', '刘凯', '顾辉', '徐威昂', '沈洁', '小猩猩', '吳忠信', '龚英杰', '廖聪炎', '孙运峰', '梁招瑞', '尚晓磊', '刘勇', '朱一秋', '李海燕', '章硕棋', '金京河', '祁众华', '王磊', '陈芸', '陈娅妮', '孙小辉', '张增瑺', '吴慧斌', '岳冰', '谢璐', '徐宇弢', '陈姜', '梁洪启', '陶佳怡', '彭斌全', '袁瑶', '赵恩岩', '刘泉', '蔡伟琳', '徐辉', '吴芳', '王晓斌', '张兵', '胡涂', '冯俊', '潘旭辉', '金宝宝', '谢晓北', '黄菲', '李高鹏', '刘天骄', '潘志忠', '陆云华', '蒋亮', '李爱政', '沈洁敏', '王霞', '刘涛', '刘颖冯杨', '曹磊', '车海星', '朱思翔', '陶丹', '江黎', '朱文成', '孙烈', '柯旭', '张宇', '朱俊', '向军', '张骏华', '郭婷', '黄正', '杨征宇', '俞晔晖', '王俊', '刘城', '苏良', '应维峰', '徐伟平', '鲍娴静', '胡昊', '叶英', '魏琴', '韩佳蔚', '刘宝国', '吴斌', '朱秀琴', '郑辉', '徐辉', '小倪', '朱拥军', '张闻', '刘晗', '江剑平');
    $user_name_arr = array_unique($user_name_arr);
    //暂时性隐藏 可以重名
    $user_name_info = $GLOBALS['db']->getAll("select user_name,real_name from " . DB_PREFIX . "user where 1 = 1 AND acct_type is null AND is_delete = 0 AND  is_effect = 1 AND user_type = 0");
    $user_names = $real_names = $new_user_names = array();
    foreach ($user_name_info as $key => $val) {
        $user_names[] = $val['user_name'];
        $real_names[] = $val['real_name'];
    }
    foreach ($user_name_arr as $k => $v) {
        if (!in_array($v, $user_names)) {
            $new_user_names[] = $v;
        }
        /* if (!in_array($v, $user_names) && !in_array($v, $real_names)) {
          $new_user_names[] = $v;
          } */
    }
    $new_user_name = $new_user_names[mt_rand(0, count($new_user_names) - 1)];

    return $new_user_name;
}

//获取一个唯一的手机号码
function getMobileNo() {
    $mobile_arr = array("13", "15", "18");
    $mobile_header = $mobile_arr[mt_rand(0, 2)];
    if ("15" == $mobile_header) {
        $mobile_middle = array("0", "1", "5", "6", "8", "9");
        $mobile_middle_str = $mobile_middle[mt_rand(0, 5)];
    } else if ("18" == $mobile_arr[mt_rand(0, 2)]) {
        $mobile_middle = array("0", "1", "2", "5", "6", "7", "8");
        $mobile_middle_str = $mobile_middle[mt_rand(0, 6)];
    } else {
        $mobile_middle_str = mt_rand(0, 9);
    }
    $mobileStr = str_shuffle('1234567890');
    $new_mobile = $mobile_header . mt_rand(0, 9) . substr($mobileStr, 0, 8);

    return $new_mobile;
}

//获取一个随机概率
function get_rand($proArr) {
    $result = '';
    //概率数组的总概率精度
    $proSum = array_sum($proArr);
    //概率数组循环
    foreach ($proArr as $key => $proCur) {
        $randNum = mt_rand(1, $proSum);      //抽取随机数
        if ($randNum <= $proCur) {
            $result = $key;    //得出结果
            break;
        } else {
            $proSum -= $proCur;
        }
    }
    unset($proArr);
    return $result;
}

/**
 * 获取本站HTTPS URL
 */
function getHttpUrl() {
    return "https://" . get_domain(true);
}

//计算使用优惠券后的资金
function get_coupon_money($deal, $coupon_info, $bid_money) {
    if ($coupon_info) {
        if ($coupon_info['coupon_type'] == 1) {
            //收益券
            $pre_interests = (($deal['rate'] / 100) / 360) * $bid_money * $deal['yield_ratio'] * $deal['repay_time'];
            $coupon_interests = $pre_interests * ($coupon_info['face_value'] / 100);

            $return['coupon_interests'] = $coupon_interests;
        } else if ($coupon_info['coupon_type'] == 2) {
            //抵现券
            $return['act_bid_money'] = $bid_money - $coupon_info['face_value'];
        }
    }
    $return['coupon_type'] = $coupon_info['coupon_type'];
    return $return;
}

function get_mobile($mobile) {
    return substr_replace($mobile, '*****', 3, 5);
}

//MAPI检测用户信息相关信息
function check_mapi_user_info($user_info, $ajax) {
    $verify_status = $GLOBALS['db']->getRow("select idcardpassed,mobilepassed,paypassword,real_name from " . DB_PREFIX . "user where id = " . $user_info['id']);
    //实名认证未通过，请完成实名认证再充值
    if ($verify_status['idcardpassed'] != 1 || !$verify_status['real_name']) {
        $root['response_code'] = 0;
        $root['show_err'] = "您尚未实名认证！请先进入个人中心点击设置完成实名认证。";
        return $root;
    }
    //手机认证认证未通过，请完成手机认证再充值
    if ($verify_status['mobilepassed'] != 1) {
        $root['response_code'] = 0;
        $root['show_err'] = "您尚未手机认证！请先进入个人中心点击设置完成手机认证。";
        return $root;
    }
    //银行卡必须绑定，而且只能绑定一张，否则无法充值，需重新绑定！
    $bank_num = $GLOBALS['db']->getOne("select count(id) from " . DB_PREFIX . "user_bank where user_id = " . $user_info['id']);
    if ($bank_num['num'] != 1) {
        $root['response_code'] = 0;
        $root['show_err'] = "您尚未绑定银行卡！请先进入个人中心点击设置完成银行卡绑定。";
        return $root;
    }
    //支付密码是否设置
    if (!$verify_status['paypassword']) {
        $root['response_code'] = 0;
        $root['show_err'] = "您尚未设置支付密码！请先进入个人中心点击设置完成设置支付密码。";
        return $root;
    }

    $root['response_code'] = 1;
    return $root;
}

//生成随机小数
function randomFloat($min = 0, $max = 1) {
    return $min + mt_rand() / mt_getrandmax() * ($max - $min);
}

//处理异常短信通知
function info_admin($msg, $title) {
    if (INFO_USER) {
        $msg_item['dest'] = INFO_USER;
        $msg_item['content'] = $msg;
        //发送
        $result = send_sms_email($msg_item, 5, "EN");
        if ($result["status"] == 1) {
            $info_user = explode(",", INFO_USER);
            foreach ($info_user as $key => $val) {
                if ($val) {
                    $msg_data['dest'] = $val;
                    $msg_data['send_type'] = 0;
                    $msg_data['title'] = $title . "异常短信通知";
                    $msg_data['content'] = addslashes($msg);
                    ;
                    $msg_data['send_time'] = time();
                    $msg_data['is_send'] = 1; //0未发送 1已发送
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['user_id'] = 0;
                    $msg_data['is_html'] = 0;
                    $msg_data['result'] = $result["msg"];
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                    //$msg_id = M('deal_msg_list') -> add($msg_data);//插入
                }
            }
        }
    }
}

function getBonusTypeName($type) {
    $arr = array(
        0 => '满就送',
        1 => '邀请注册',
        2 => '邀请投资',
        3 => '大转盘',
        4 => '刮刮卡',
        5 => '人人摇',
        6 => '活动抽奖',
        7 => '实名认证',
        8 => '双旦活动',
        9 => '加息1%红包',
        10 => '逾期补偿红包',
    );
    return isset($arr[$type]) ? $arr[$type] : '未设置类型';
}

function getBonusStatusName($status) {
    $arr = array(
        0 => '未申请提现',
        1 => '提现申请中',
        2 => '已放款',
    );
    return isset($arr[$status]) ? $arr[$status] : '未设置状态';
}

//获取两个日期之间的所有日期
function prDates($start, $end) {
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    while ($dt_start <= $dt_end) {
        $data[] = str_replace("-", '', date('Y-m-d', $dt_start));
        $dt_start = strtotime('+1 day', $dt_start);
    }
    return $data;
}

function prTimes($start, $end) {
    $dt_start = strtotime($start);
    $dt_end = strtotime($end);
    while ($dt_start <= $dt_end) {
        $time = to_date($dt_start, "Y-m-d H");
        $time = str_replace(" ", '-', $time);
        $time_arr = explode('-', $time);
        $data[1][] = $time_arr[0] . $time_arr[1] . $time_arr[2] . $time_arr[3];
        $data[2][] = $time_arr[3];
        $dt_start = strtotime('+1 hour', $dt_start);
    }
    return $data;
}

function user_check_web($username_email, $pwd) {
    if ($username_email && $pwd) {
        $sql = "select *,id as uid from " . DB_PREFIX . "user where (user_name='" . $username_email . "' or email = '" . $username_email . "' or mobile = '" . $username_email . "') and is_delete = 0";
        $user_info = $GLOBALS['db']->getRow($sql);
        if (strlen($pwd) != 32) {
            if ($user_info['user_pwd'] == md5($pwd . $user_info['code']) || $user_info['user_pwd'] == md5($pwd)) {
                return $user_info;
            }
        } else {

            if ($user_info['user_pwd'] == $pwd) {
                return $user_info;
            } else if (md5($user_info['user_pwd'] . date("Y-m-d")) == $pwd) {
                return $user_info;
            } else if (md5(md5(md5($user_info['user_pwd'] . date("Y-m-d"))) . date("Y-m-d")) == $pwd) {
                return $user_info;
            } else if (md5(md5(md5($user_info['user_pwd'] . date("Y-m-d", strtotime("+1 year")))) . date("Y-m-d", strtotime("+1 year"))) == $pwd) {
                return $user_info;
            } else if (md5($user_info['user_pwd'] . date("Y-m-d", strtotime("+1 year"))) == $pwd) {
                return $user_info;
            } else if (md5($user_info['user_pwd'] . date("Y-m-d", strtotime("-1 day"))) == $pwd) {
                return $user_info;
            }
        }
    }
    return false;
}

//途虎洗车券活动
function car_coupon($user_id, $type, $start_time, $end_time) {
    //$type   活动相关参数
//        $type['id']                   选项，不同数字代表不同活动
//        $type['activity']             活动名称
//        $type['coupon']               奖品名称
//        $type['title']                站内信标题
//        $type['content']              站内信内容
//        $start_time                   开始时间，时间戳
//        $end_time                     结束时间，时间戳
    //获取手机号
    $sql_mobile = "select mobile from " . DB_PREFIX . "user where id = " . $user_id;
    $mobile = $GLOBALS['db']->getOne($sql_mobile);

    if ($type) {
        $car_coupon_data['user_id'] = $user_id;
        $car_coupon_data['create_time'] = time();
        $car_coupon_data['prize_desc'] = $type['activity'];
        $car_coupon_data['prize_name'] = $type['coupon'];
        $car_coupon_data['status'] = 1;

        $lottery_log_data['lotter_id'] = 0;
        $lottery_log_data['mobile'] = $mobile;
        $lottery_log_data['create_time'] = time();
        $lottery_log_data['status'] = 0;
        $lottery_log_data['prize_name'] = $type['coupon'];
        $lottery_log_data['prize_type'] = 5;
        $lottery_log_data['prize_desc'] = $type['activity'];
    }

    if ($type['id'] == 1) {
        $sql_new_user = "select id from " . DB_PREFIX . "user where id = " . $user_id . " and create_time >= " . $start_time . " and create_time <= " . $end_time . " and is_effect =1 and is_delete = 0 and is_auto = 0";
        $new_user = $GLOBALS['db']->getRow($sql_new_user);
        if ($new_user) {
//                $sql_car_coupon = "select id from " .DB_PREFIX. "user_lottery_log where mobile =" .$new_user['mobile']." and prize_type = 5";
            $sql_car_coupon = "select id from " . DB_PREFIX . "car_coupon where user_id =" . $user_id . " and prize_type = 1";
            $car_coupon = $GLOBALS['db']->getOne($sql_car_coupon);
            if (!$car_coupon) {
                //将记录写入fanwe_car_coupon表里
                $sql_coupon_count = " select id,prize_code from " . DB_PREFIX . "car_coupon where prize_type = 1 and status = 0";
                $coupon_count = $GLOBALS['db']->getAll($sql_coupon_count);
                if (!$coupon_count) {
                    $result['status'] = 7;
                    $result['info'] = "对不起，您来晚了，" . $type['coupon'] . "已经被抢光了";
                } else {
                    $sql_coupon_data = $sql_coupon_count . " limit 1";
                    $coupon_data = $GLOBALS['db']->getRow($sql_coupon_data);
                    $code = $coupon_data['prize_code'];
                    //是否成功
                    if ($GLOBALS['db']->autoExecute(DB_PREFIX . "car_coupon", $car_coupon_data, "UPDATE", " id = '" . $coupon_data['id'] . "' ")) {
                        //将记录写入fanwe_user_lottery_log表里
                        $lottery_log_data['obj_id'] = $coupon_data['id'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_lottery_log", $lottery_log_data, "INSERT");
                        $content = $type['content'] . $code;
                        send_user_msg($type['title'], $content, 0, $user_id, time(), 0, true, 21);

                        $result['status'] = 1;
                        $result['info'] = "领取" . $type['coupon'] . "成功，兑换码请在【我的账户-站内通知】查看";
                    } else {
                        $result['status'] = 6;
                        $result['info'] = "网络异常，请重新领取";
                    }
                }
            } else {
                $result['status'] = 3;
                $result['info'] = "您已领取过，无法重复领取。兑换码请在【我的账户-站内通知】查看";
            }
        } else {
            $result['status'] = 4;
            $result['info'] = "对不起，您不是新注册用户";
        }
        return $result;
    } else {
        $sql_deal = "select id from " . DB_PREFIX . "deal_load where user_id = " . $user_id . " and create_time >= " . $start_time . " and create_time <= " . $end_time . " and is_auto = 0 and contract_no != '' ";
        $deal = $GLOBALS['db']->getAll($sql_deal);
        if ($deal) {
            $sql_car_coupon = "select id from " . DB_PREFIX . "car_coupon where user_id =" . $user_id . " and prize_type = 2";
            $car_coupon = $GLOBALS['db']->getOne($sql_car_coupon);
            if (!$car_coupon) {
                //将记录写入fanwe_car_coupon表里
                $sql_coupon_count = " select id,prize_code from " . DB_PREFIX . "car_coupon where prize_type = 2 and status = 0";
                $coupon_count = $GLOBALS['db']->getAll($sql_coupon_count);
                if (!$coupon_count) {
                    $result['status'] = 7;
                    $result['info'] = "对不起，您来晚了，" . $type['coupon'] . "已经被抢光了";
                } else {
                    $sql_coupon_data = $sql_coupon_count . " limit 1";
                    $coupon_data = $GLOBALS['db']->getRow($sql_coupon_data);
                    $code = $coupon_data['prize_code'];
                    if ($GLOBALS['db']->autoExecute(DB_PREFIX . "car_coupon", $car_coupon_data, "UPDATE", " id = '" . $coupon_data['id'] . "' ")) {
                        //将记录写入fanwe_user_lottery_log表里
                        $lottery_log_data['obj_id'] = $coupon_data['id'];
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_lottery_log", $lottery_log_data, "INSERT");
                        $content = $type['content'] . $code;
                        send_user_msg($type['title'], $content, 0, $user_id, time(), 0, true, 21);
                        $result['status'] = 2;
                        $result['info'] = "领取" . $type['coupon'] . "成功，兑换码请在【我的账户-站内通知】查看查看";
                    } else {
                        $result['status'] = 6;
                        $result['info'] = "网络异常，请重新领取";
                    }
                }
            } else {
                $result['status'] = 3;
                $result['info'] = "您已领取过，无法重复领取。兑换码请在【我的账户-站内通知】查看";
            }
        } else {
            $result['status'] = 5;
            $result['info'] = "您在活动期内还未进行投资，请任意投资一笔，即可领取洗护大礼包";
        }
        return $result;
    }
}

//异常预警
function adnormal_warning($mobilesContents, $emailsContents, $mobiles, $emails) {
    //发送短信通知 管理员和运营人员
    if ($mobiles && $mobilesContents) {
        $msg_item['dest'] = $mobiles;
        $msg_item['content'] = $mobilesContents['msg'];
        $result = send_sms_email($msg_item, 5, "EN");
        if ($result["status"] == 1) {
            $info_user = explode(",", $mobiles);
            foreach ($info_user as $key => $val) {
                if ($val) {
                    $msg_data['dest'] = $val;
                    $msg_data['send_type'] = 0;
                    $msg_data['title'] = $mobilesContents['title'] . "短信通知";
                    $msg_data['content'] = addslashes($mobilesContents['msg']);
                    $msg_data['send_time'] = time();
                    $msg_data['is_send'] = 1; //0未发送 1已发送
                    $msg_data['create_time'] = TIME_UTC;
                    $msg_data['user_id'] = 0;
                    $msg_data['is_html'] = 0;
                    $msg_data['result'] = $result["msg"];
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
                }
            }
        }
    }
    //发送邮件通知 管理员和运营人员
    if ($emails && $emailsContents) {
        $info_email_users = explode(',', $emails);
        foreach ($info_email_users as $email_user) {
            $msg_data['dest'] = $email_user;
            $msg_data['send_type'] = 1; //0短信 1 邮件
            $msg_data['title'] = $emailsContents['title'] . "邮件通知";
            $msg_data['content'] = addslashes($emailsContents['msg']);
            $msg_data['send_time'] = 0;
            $msg_data['is_send'] = 0;
            $msg_data['create_time'] = TIME_UTC;
            $msg_data['user_id'] = 0;
            $msg_data['is_html'] = 1; //$tmpl['is_html']  1超文本 0纯文本
            $GLOBALS['db']->autoExecute(DB_PREFIX . "deal_msg_list", $msg_data); //插入
        }
    }
}

//二维数组指定某个键排序
function arr_sort($array, $key, $order = "asc", $is_key = false) {//asc是升序 desc是降序  $is_key true 键值重新组成 false 键值保存原来的
    $arr_nums = $arr = array();
    foreach ($array as $k => $v) {
        $arr_nums[$k] = $v[$key];
    }
    if ($order == 'asc') {
        asort($arr_nums);
    } else {
        arsort($arr_nums);
    }
    if ($is_key) {
        foreach ($arr_nums as $k => $v) {
            $arr[] = $array[$k];
        }
    } else {
        foreach ($arr_nums as $k => $v) {
            $arr[$k] = $array[$k];
        }
    }
    return $arr;
}

/*
 * PHP生成加密pdf
 * 合同信息【$pdf_data["logo_url"]】 PDF头部logo图片路径
 * 【$pdf_data["pdf_title"]】       PDF头部title标题
 * 【$pdf_data["title_detail"]】    PDF头部title下面的二级标题
 * 【$pdf_data["pdf_name"]】        PDF生成时的文件名 不能使用中文
 * 【$pdf_data["data"]】            PDF数据的键值对 键必须在模板中存在的 <{key}> 即模板标识 便于替换
 *
 */

function generate_pdf($pdf_data) {
    require_once APP_ROOT_PATH . "public/tcpdf/tcpdf.php";
    //实例化
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetProtection($permissions = array('print', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-high'), $user_pass = '', $owner_pass = null, $mode = 0, $pubkeys = null);
    // 设置文档信息
    $pdf->SetCreator('Helloweba');
    $pdf->SetAuthor('yueguangguang');
    $pdf->SetTitle('Welcome to jxch168.com!');
    $pdf->SetSubject('TCPDF Tutorial');
    $pdf->SetKeywords('TCPDF, PDF, PHP');
    if (!$pdf_data["logo_url"]) {
        $pdf_data["logo_url"] = "logo.png";
    }
    // 设置页眉和页脚信息
    $pdf->SetHeaderData($pdf_data["logo_url"], 20, $pdf_data["pdf_title"], $pdf_data["title_detail"], array(0, 64, 255), array(0, 64, 128));
    $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

    // 设置页眉和页脚字体 v
    $pdf->setHeaderFont(Array('stsongstdlight', '', '16'));
    $pdf->setFooterFont(Array('helvetica', '', '8'));

    // 设置默认等宽字体
    $pdf->SetDefaultMonospacedFont('courier');

    // 设置间距
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(8);
    $pdf->SetFooterMargin(14);

    // 设置分页
    $pdf->SetAutoPageBreak(TRUE, 25);

    // set image scale factor
    $pdf->setImageScale(1.25);

    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

    //设置字体
    $pdf->SetFont('stsongstdlight', '', 14);

    $pdf->AddPage();

    $str = "";

    if (!file_exists($pdf_data["template_pdf_url"])) {
        $str .= "合同文件模板不存在，生成合同异常！";
    } else {
        $fp = fopen($pdf_data["template_pdf_url"], "r");
        $str .= fread($fp, filesize($pdf_data["template_pdf_url"]));
        fclose($fp);
    }
    //内容替换
    foreach ($pdf_data["data"] as $key => $val) {
        str_replace("<{" . $key . "}>", $val, $str);
    }
    //写入PDF
    $pdf->WriteHTML($str);

    //输出PDF
    $pdf->Output($pdf_data["pdf_name"] . '.pdf', 'I'); //D代表下载
    die();
}

//取出活动参数配置
function activityConf($id) {
    $id = trim($id);
    $conf = require_once APP_ROOT . 'data_conf/activityConf.php';
    return trim($conf[$id]);
}

function utf8_to($string) {
    $string = trim($string);
    require_once APP_ROOT_PATH . 'public/Pinyin/Pinyin.class.php';
    $pinyin = new Pinyin();
    return trim($pinyin::utf8_to($string));
}

function trimall($str) {//删除所有的空格
    $qian = array(" ", "　", "\t", "\n", "\r", " ");
    $hou = array("", "", "", "", "", "");
    return str_replace($qian, $hou, $str);
}

function getIpAddr($ip) {
    require_once APP_ROOT_PATH . 'public/ip/ip.class.php';
    $ipObj = new ip();
    $addr = $ipObj->ip2addr($ip);
    return $addr['country'];
}

//对象转数组,使用get_object_vars返回对象属性组成的数组
function objectToArray($obj) {
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if (is_array($arr)) {
        return array_map(__FUNCTION__, $arr);
    } else {
        return $arr;
    }
}

/**
 * 网页弹框提示
 * @param $msg  string  要弹出的提示信息
 * @return 无返回值直接弹出
 * 
 * @example     alert("啊实打实的");
 */
function alert($msg) {
    echo "<script>alert('$msg');</script>";
}

function getPriceName($prize_type, $conf_id) {
    $conf_id = intval($conf_id);
    if ($prize_type == 1 || $prize_type == 2) {
        $prize_conf = require (APP_ROOT_PATH . 'data_conf/user_coupon_conf.php');
        return $prize_conf[$conf_id]['coupon_name'];
    } else if ($prize_type == 3) {
        $prize_conf = require(APP_ROOT_PATH . 'data_conf/user_bonus_conf.php');
        return $prize_conf[$conf_id]['reward_name'];
    } else if ($prize_type == 4) {
        $prize_conf = require(APP_ROOT_PATH . 'data_conf/user_material_conf.php');
        return $prize_conf[$conf_id]['name'];
    } else {
        return '谢谢参与';
    }
}

function load_php() {
    
}

function load_class($path) {
    $path = APP_ROOT_PATH . $path;
    if (is_file($path)) {
        require_once( $path );
    }
}

function isOverByTime($start_time, $end_time) {
    $time = time();
    if ($start_time < $time && $end_time > $time) {
        return '进行中';
    } else if ($start_time > $time) {
        return '未开始';
    } else {
        return '已过期';
    }
}

/**
 * 加密解密函数
 * @param string $string 明文 或 密文  
 * @param string $operation DECODE表示解密,其它表示加密  
 * @param string $key 密匙
 * @param string $expiry 密文有效期
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙  
    $ckey_length = 4;

    // 密匙  
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

    // 密匙a会参与加解密  
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证  
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文  
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    // 参与运算的密匙  
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性  
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确  
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿  
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度  
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分  
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符  
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        // substr($result, 0, 10) == 0 验证数据有效性  
        // substr($result, 0, 10) - time() > 0 验证数据有效性  
        // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性  
        // 验证数据有效性，请看未加密明文的格式  
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因  
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码  
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

//WIN("weixin");die;
/**
 * 微信api相关接口类加载
 * @param type $class_name
 */
function WIN($class_name) {
    $included_arr = get_included_files();
    $weixin_file = APP_ROOT_PATH . 'api/weixin/libs/weixin.php';
    if (!in_array($weixin_file, $included_arr)) {
        include ($weixin_file);
    }
    $class_file = APP_ROOT_PATH . 'api/weixin/libs/' . $class_name . ".php";
    if (!in_array($class_file, $included_arr) && file_exists($class_file)) {
        require($class_file);
    }
//    echo '<pre>';var_dump(get_included_files(),$class_file);echo '</pre>';die;
    return new $class_name();
}

function replace_path(&$str) {
    return str_replace("./", SITE_DOMAIN . '/', $str);
}

/**
 * 根据传递过来的_m返回数值
 * @param type $_m 移动端传递过来的字段_m
 * return 
 */
function getTerminalId($_m) {
    $conf = ['' => 2, 'android' => 3, 'ios' => 4];
    return $conf[trim($_m)];
}

/**
 * 
 * @param string $user_id     用户ID      多个用户，用英文逗号隔开
 * @param string $title       消息标题
 * @param string $content     消息内容
 * @param string $type        消息类型    1普通网址  2 banner 3 标的ID  4 项目列表  空 打开应用 
 * @param string $data        参数值
 */
function umengMsgPush($user_id, $title, $content, $type = "", $data = "") {
    if (empty($user_id) || empty($title) || empty($content)) {
        return false;
    }

    $msg['user_id'] = $user_id;
    $msg['title'] = $title;
    $msg['content'] = $content;
    $msg['type'] = $type;
    $msg['data'] = $data;

    require_once APP_ROOT_PATH . "system/umeng/messagePush.php";
    $msgObj = new MessagePush();
    $msgObj->send($msg);
}

/**
 * 判断是否是www.jxch168.com线上的服务器
 * return bool 是:true,否：false
 */
function is_Online() {
    $res = false;
    $host = $_SERVER['HTTP_HOST'];
    if ((strpos($host, "dev") === false && strpos($host, "test") === false) || !in_array(CONDITION, array('dev', 'test'))) {
        $res = ture;
    }
    return $res;
}
