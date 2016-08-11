<?php

/*
 * 功能：实现富有与金享财行平台用户账户银行卡同步 修改银行卡后台回调 客户资料更新 （或者脚本执行所有更新银行卡的用户资料）
 * 时间：2015年07月24日
 */
    require_once 'init.php';
    //实现用户平台账户与富友账户银行卡同步
    require_once APP_ROOT_PATH . "system/payment/fuyou.php";
    require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
    $fuyou_payment = new Fuyou_payment();
    $fuyou = new fuyou();
    
    $endUid = 0;
    while (1) {
        //查询所有用户信息   //判断是否绑过银行卡
        //接受参数cli
        $pre_time = strtotime("-10 minutes",time());//10分钟之前的数据
        if($argv[1] > 0){
            $user_bank_examines = $GLOBALS['db']->getAll("SELECT * from " . DB_PREFIX . "user_bank_examine  WHERE is_effect = 1 AND change_status = 0 AND create_time <= ".$pre_time." AND user_id = '" . $argv[1] . "' ");
        }else{
            $user_bank_examines = $GLOBALS['db']->getAll("SELECT * from " . DB_PREFIX . "user_bank_examine  WHERE is_effect = 1 AND change_status = 0 AND create_time <= ".$pre_time." AND id > '" . $endUid . "' order by id asc limit 100 ");
        }
        foreach ($user_bank_examines as $key => $bank_examine) {
            //获取现有银行卡信息
            $old_bank = $GLOBALS["db"]->getRow("SELECT * FROM ".DB_PREFIX."user_bank WHERE user_id = '".$bank_examine['user_id']."' ");
            if($old_bank){
                $user_info = $GLOBALS["db"]->getRow("SELECT id,idno,mobile,fuiou_account FROM ".DB_PREFIX."user WHERE id = '".$bank_examine['user_id']."' ");;
                //查询修改银行卡申请审核结果
                $bankResult = $fuyou->queryChangeCard($user_info,$bank_examine['mchnt_txn_ssn']);
                if($bankResult){
                    $bankResultArr = objectToArray($bankResult);
                    if("0000" == $bankResultArr["plain"]["resp_code"] && $bankResultArr["plain"]["examine_st"] == 1){//审核成功 
                        //如果富有审核成功 客户富有银行卡信息更新成功 则同步客户平台账号银行卡信息
                        //查询客户富有最新银行卡信息
                        $fuyou_user_data = $fuyou_payment->getFuYouUserInfo($user_info);
                        if ($fuyou_user_data['status'] == 1) {
                            //准备数据 更新平台用户银行卡信息
                            $user_idno = $fuyou_user_data['data']['results']['result']['certif_id'];//身份证号码
                            //开户行地区代码 省份
                            $parentCode = $GLOBALS["db"]->getOne("SELECT ParentCode FROM ".DB_PREFIX."district_info WHERE DistrictCode = '".$fuyou_user_data['data']['results']['result']['city_id']."' ");
                            $new_bank['user_id'] = $user_info["id"];//用户id
                            $new_bank['real_name'] = $fuyou_user_data['data']['results']['result']['cust_nm'];//客户姓名
                            $new_bank['region_lv1'] = $parentCode;//开户行地区代码 省份
                            $new_bank['region_lv2'] = $fuyou_user_data['data']['results']['result']['city_id'];//开户行地区代码 区县
                            $new_bank['bank_id'] = $fuyou_user_data['data']['results']['result']['parent_bank_id'];//开户行行别
                            $new_bank['bankzone'] = $fuyou_user_data['data']['results']['result']['bank_nm'] ? $fuyou_user_data['data']['results']['result']['bank_nm'] : '';//开户行支行名称
                            $new_bank['bankcard'] = $fuyou_user_data['data']['results']['result']['capAcntNo'];//卡号 账号
                            $new_bank['update_time'] = time();//卡号 账号
                            //判断用户信息
                            if($old_bank['user_id'] != $new_bank['user_id'] || $user_info["idno"] != $user_idno){
                                //用户信息不统一，无法同步
                                $resp_info = "用户信息不统一，无法同步";
                            }else if($old_bank['user_id'] == $new_bank['user_id'] && $old_bank['real_name'] == $new_bank['real_name'] && $old_bank['region_lv1'] == $new_bank['region_lv1'] && $old_bank['region_lv2'] == $new_bank['region_lv2'] && $old_bank['bank_id'] == $new_bank['bank_id'] && $old_bank['bankzone'] == $new_bank['bankzone'] && $old_bank['bankcard'] == $new_bank['bankcard']){
                                //银行卡信息一致，无需同步 判断富友与金享财行账户银行卡是否一致
                                $resp_info = '银行卡信息一致，无需同步';
                            }else{
                                //符合以下条件的执行同步
                                $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank", $new_bank, "UPDATE", "id = '" . $old_bank['id'] . "'");
                                $update_id = $GLOBALS['db']->affected_rows();
                                if($update_id){
                                    //更新审核记录
                                    $user_bank_examine["change_status"] = 1;
                                    $user_bank_examine["success_time"] = time();//银行卡审核时间
                                    $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $user_bank_examine, "UPDATE", "id = '" . $bank_examine['id'] . "'");
                                    $resp_info = "银行卡同步成功";
                                }else{
                                    $resp_info = "'银行卡数据信息更新失败'";
                                }
                            }
                            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "POST[".json_encode($bank_examine)."];new_bank:[" . json_encode($new_bank) . "];old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $resp_info . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                        }elseif ($fuyou_user_data['status'] == 2) {
                            //服务器忙 响应失败，请稍后重试！
                            $str = '该用户不存在或者服务器忙，响应失败，请稍后重试！';
                            file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "old_bank:[".json_encode($old_bank) ."];return:[".json_encode($fuyou_user_data)."];提示信息:[" . $str . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                        }
                    }else if("0000" == $bankResultArr["plain"]["resp_code"] && $bankResultArr["plain"]["examine_st"] == 2){//审核失败
                        //更新审核记录
                        $bank_examine_err["change_status"] = 2;//审核失败
                        $bank_examine_err["success_time"] = time();//银行卡审核时间
                        $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $bank_examine_err, "UPDATE", "id = '" . $bank_examine['id'] . "'");
                        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "POST[".json_encode($bank_examine)."];return:[".json_encode($bankResultArr)."];提示信息:[" . "该修改银行卡申请记录富有审核失败。" . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);                        
                    }else if("0000" == $bankResultArr["plain"]["resp_code"] && $bankResultArr["plain"]["examine_st"] == 0){//待审核
                        //待审核 不做任何处理
                        //file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "return:[".json_encode($bankResultArr)."];提示信息:[" . "该修改银行卡申请记录富有待审核。" . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    }else{
                        //其他验签错误
                        file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "POST[".json_encode($bank_examine)."];return:[".json_encode($bankResultArr)."];提示信息:[" . "其他验签错误。" . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                    } 
                }else{
                    //更新审核记录
                    $ub_examine["is_effect"] = 0;
                    $ub_examine["update_time"] = time();
                    $GLOBALS['db']->autoExecute(DB_PREFIX . "user_bank_examine", $ub_examine, "UPDATE", "id = '" . $bank_examine['id'] . "'");
                    file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "提示信息:[" . "该数据为脏数据，已置为无效数据！" . "];POST[".json_encode($bank_examine)."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
                }
            }else{
                file_put_contents(APP_ROOT_PATH . 'log/fuyou/' . date('Y-m-d') . '_updateUserBankInfo.log', "提示信息:[" . "尚未绑卡用户" . "];POST[".json_encode($bank_examine)."];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
            }
            $endUid = $bank_examine['id'];
        }
        if (count($user_bank_examines) < 100) {
            echo "finshed\r\n";
            exit;
        }
    }

