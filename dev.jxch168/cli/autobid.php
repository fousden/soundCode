<?php
	/*
	*自动投标处理
	*/
        require_once 'init.php';

	$time = time();
	//查找所有正在进行中的借款标
	$deals_list = $GLOBALS['db']->getAll("select d.*,dc.name as cate_name from ".DB_PREFIX."deal d left join ".DB_PREFIX."deal_cate dc on d.cate_id = dc.id where d.deal_status = 1 AND d.is_has_loans = 0 AND d.start_time <= ".$time." AND d.is_effect = 1 AND d.is_delete = 0 AND (d.start_time + (d.enddate * 24 * 3600) - ".$time.") >= 0");

	//查询自动投标用户信息 在100个自动投标用户中随机产生一个用户进行自动投标
        $user_count = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where is_auto = 1");
        create_users(AUTO_USER_NUM - $user_count);
	$user_info_arr = $GLOBALS['db']->getAll("select id,user_name,real_name,mobile,money,paypassword from ".DB_PREFIX."user where is_auto = 1");

	$all_status = array();
	$is_auto_status = true;
	foreach($deals_list as $key => $value)
	{
		//标的ID
		$autoid = intval($value["id"]);
		if($value['cate_name'] == '金享票号' || $value['cate_name'] == '金享银行')
		{
			//计算自动投标金额 自动投标金额为标的总金额的2%-3% 票号和银行是投资比例的2%至3%
			$arr = array(2,randomFloat(2, 3),3);
			$autobid_rate = $arr[mt_rand(0,2)]/100;
		}
		elseif($value['cate_name'] == '金享租赁' || $value['cate_name'] == '金享保理')
		{
			//计算自动投标金额 自动投标金额为标的总金额的3%-4% 租赁和保理是投资比例的3%至4%
			$arr = array(3,randomFloat(3, 4),4);
			$autobid_rate=$arr[mt_rand(0,2)]/100;
		}
		//自动投标金额
		$autobid_money = intval($value["borrow_amount"] * $autobid_rate);
		//可投金额
		$remain_money = $value["borrow_amount"] - $value['load_money'];
		//如果投标自动投标金额大于剩余金额 就将剩余资金全部投掉
		if(($remain_money > 0 && $autobid_money > $remain_money))
		{
			$autobid_money = $remain_money;
		}
                //如果自动投标金额小于最低投标额度 则自动投标金额即为最低投标金额
		if($autobid_money < $value['min_loan_money'])
		{
			$autobid_money = $value['min_loan_money'];
		}
                //如果剩余金额小于最低投标额度 则自动投标金额即为剩余金额
                if($remain_money < $value['min_loan_money'])
		{
			$autobid_money = $remain_money;
		}
		//如果标的的到期时间在两小时之内的 就将剩余资金全部投掉
		$remain_time = $value['start_time'] + $value['enddate'] * 24 * 3600 - $time;
		//两小时时间戳表示
		$two_hours = 2 * 3600;
		if($remain_time > 0 && $remain_time < $two_hours)
		{
                    $autobid_money = $remain_money;
		}

                //100个自动投标用户随机产生
                $user_info = $user_info_arr[mt_rand(0,count($user_info_arr) - 1)];

		//累加需要充值的金额
		$msg ='自动投标充值'.$autobid_money.'，标名【'.$value['name'].'】标号【'.$value['id'].'】';
		modify_account(array('money'=>$autobid_money),$user_info['id'],$msg,13);

		if($autobid_money > 0){
			$all_status[] = autodobid2($autoid,$autobid_money,$user_info,1,false);
		}else{
			$is_auto_status = false;
			file_put_contents(dirname(dirname(__FILE__)).'/log/autobid/' . date('Y-m-d') . '_autobid_log.log', "POST:[" . json_encode('自动投标金额不能小于等于0') . "];return:[" .'自动投标金额不能小于等于0'. "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
		}
	}

	//循环取出所有自动投标状态
	foreach($all_status as $k=>$v){
            if($v['status'] == 0){
                    $is_auto_status = false;
            }
            file_put_contents(dirname(dirname(__FILE__)).'/log/autobid/' . date('Y-m-d') . '_autobid_log.log', "POST:[" . json_encode($v) . "];return:[" . $v . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
        }
	if($is_auto_status){
		file_put_contents(dirname(dirname(__FILE__)).'/log/autobid/' . date('Y-m-d') . '_autobid_log.log', "POST:[" . json_encode($GLOBALS['lang']['DEAL_BID_SUCCESS']) . "];return:[" . $GLOBALS['lang']['DEAL_BID_SUCCESS'] . "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
		die();
	}else{
		file_put_contents(dirname(dirname(__FILE__)).'/log/autobid/' . date('Y-m-d') . '_autobid_log.log', "POST:[" . json_encode('自动投标失败！') . "];return:[" .'自动投标失败！'. "];[" . date('Y-m-d H:i:s') . "]\r\n", FILE_APPEND);
		die();
	}

