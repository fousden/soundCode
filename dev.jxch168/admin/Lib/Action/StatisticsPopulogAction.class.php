<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class StatisticsPopulogAction extends CommonAction {

    public function statistics_user() {
	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}
//如果是一天的日期显示饼图（今天，昨天）
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {

	    $list = $this->get_list_reg($start_time); 
	    //$series_name
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);
	    //今天
	    if ($start_time == date('Ymd', time())) {
		$this->assign('type', 'bingtua');
		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id > 0")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id = 0")->find();
		//$data_name

		$data_name_raw = ["'线下'.,.{$list_day_xx['count']}个", "'线上'.,.{$list_day_xs['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//表格
		$list_suma = $list_day_xx['count'] + $list_day_xs['count'];
		$xianxia = $list_day_xx['count'];
		$xianshang = $list_day_xs['count'];

		$this->assign('user_date', $start_time); 
		$this->assign('list_suma', $list_suma);
		$this->assign('xianxia', $xianxia);
		$this->assign('xianshang', $xianshang);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $xianxia)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $xianshang)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma)
		);
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 5) {
			$client_list[$val['user_reg_date']]['dowm'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 6) {
			$client_list[$val['user_reg_date']]['top'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    } else {
		$this->assign('type', 'bingtub');
		//昨天
		$list = $this->get_list_reg($start_time);
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5) {
			$a = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 6) {
			$b = $v['user_reg_count'];
		    }
		}

		$data_name_raw = ["'线下'.,.{$a}个", "'线上'.,.{$b}个"];
		$data_name = json_encode($data_name_raw);

		$arr_a = array();
		$arr_b = array();
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5)
			$arr_a[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 6)
			$arr_b[] = $v['user_reg_count'];
		}
		$count_sum = intval($arr_a[0]) + intval($arr_b[0]);
		$pie_data_array_raw = array($arr_a[0] / $count_sum, $arr_b[0] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);
		//表格
		$list_suma = M('statistical_user_reg')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
		$this->assign('user_date', $start_time);
		$this->assign('list_suma', $list_suma);
		$this->assign('list_xianxia', $arr_a);
		$this->assign('list_xianshang', $arr_b);
		//新表格数据
		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $arr_a[0])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $arr_b[0])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma['user_reg_count'])
		);
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 5) {
			$client_list[$val['user_reg_date']]['dowm'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 6) {
			$client_list[$val['user_reg_date']]['top'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    }
	} else {
	    $this->assign('type', 'xiantu');
	    //$xAxis_raw
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_user_reg')->where("$sql_x AND user_reg_type = 0")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);
	    $yAxis_title_raw = '单位（人）';
	    $yAxis_title = json_encode($yAxis_title_raw);

	    $data_name_raw = ['线下', '线上', '合计'];
	    $data_name = json_encode($data_name_raw);

	    $list = $this->get_list_reg($start_time, $end_time);
	    $arr_a = array();
	    $arr_b = array();
	    $all = array();
	    foreach ($list as $v) {
		if ($v['user_reg_type'] == 5) {
		    $arr_a[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 6) {
		    $arr_b[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 0) {
		    $all[] = (int) $v['user_reg_count'];
		}
	    }
	    $arrY = array($arr_a, $arr_b, $all);
	    $data_array = json_encode($arrY);

	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);
	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);
	    //新表格数据
	    $statistics_data = M('statistical_user_reg')->where($sql_x)->select();
	    $client_list = array();
	    foreach ($statistics_data as $key => $val) {
		$client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
		$client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		if ($val['user_reg_type'] == $statistics_data[$key]['user_reg_type']) {
		    $client_list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
		}
	    }
	    $client_list['gross']['user_reg_date'] = "总计";
	    krsort($client_list);
	    $this->assign('client_list', $client_list);
	}

	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);
	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));
	//表格数据 多天
	$list_sum = M('statistical_user_reg')->where("$sql_x AND user_reg_type = 0")->select();
	$this->assign('list_sum', $list_sum);
	$this->assign('list_date', $arrX);
	$this->assign('list_xianxia', $arr_a);
	$this->assign('list_xianshang', $arr_b);

	$this->display();
    }

    //平台注册统计
    public function statistical_pt() {
	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}

	//如果是一天的日期显示饼图（今天，昨天）
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {
	    $this->assign('type', 'bingtu');
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);

	    //今天
	    if ($start_time == date('Ymd', time())) {
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 1")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 2")->find();
		$list_day_android = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 3")->find();
		$list_day_ios = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 4")->find();
		//$data_name
		$data_name_raw = ["'web'.,.{$list_day_xx['count']}个", "'wap'.,.{$list_day_xs['count']}个", "'android'.,.{$list_day_android['count']}个", "'IOS'.,.{$list_day_ios['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'] + (int) $list_day_android['count'] + (int) $list_day_ios['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum, $list_day_android['count'] / $count_sum, $list_day_ios['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);
		//表格数据
		$this->assign('list_datea', $start_time);
		$this->assign('a', $list_day_xx['count']);
		$this->assign('b', $list_day_xs['count']);
		$this->assign('c', $list_day_android['count']);
		$this->assign('d', $list_day_ios['count']);
		$this->assign('e', $count_sum);
		//新表格数据
		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $count_sum)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $list_day_xx['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $list_day_xs['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $list_day_android['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $list_day_ios['count'])
		);

		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 1) {
			$client_list[$val['user_reg_date']]['web'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 2) {
			$client_list[$val['user_reg_date']]['wap'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 3) {
			$client_list[$val['user_reg_date']]['android'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 4) {
			$client_list[$val['user_reg_date']]['ios'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    } else {
		//昨天
		$list = $this->get_list_reg_pt($start_time); 
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 1) {
			$a = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 2) {
			$b = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 3) {
			$c = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 4) {
			$d = $v['user_reg_count'];
		    }
		}

		$data_name_raw = ["'web'.,.{$a}个", "'wap'.,.{$b}个", "'android'.,.{$c}个", "'IOS'.,.{$d}个"];
		$data_name = json_encode($data_name_raw);

		$arr_a = array();
		$arr_b = array();
		$arr_c = array();
		$arr_d = array();
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 1)
			$arr_a[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 2)
			$arr_b[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 3)
			$arr_c[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 4)
			$arr_d[] = $v['user_reg_count'];
		}
		$count_sum = (intval($arr_a[0]) + intval($arr_b[0]) + intval($arr_c[0]) + intval($arr_d[0])) / 100;

		$pie_data_array_raw = array($arr_a[0] / $count_sum, $arr_b[0] / $count_sum, $arr_c[0] / $count_sum, $arr_d[0] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw); 
		//表格数据
		$list_sum = M('statistical_user_reg')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
		$this->assign('list_datea', $start_time);
		$this->assign('a', $a);
		$this->assign('b', $b);
		$this->assign('c', $c);
		$this->assign('d', $d);
		$this->assign('e', $list_sum['user_reg_count']);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_sum['user_reg_count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $a)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $b)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $c)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $d)
		);

		//$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 1) {
			$client_list[$val['user_reg_date']]['web'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 2) {
			$client_list[$val['user_reg_date']]['wap'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 3) {
			$client_list[$val['user_reg_date']]['android'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 4) {
			$client_list[$val['user_reg_date']]['ios'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    }
	} else {
	    $this->assign('type', 'xiantu');

	    //$xAxis_raw
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_user_reg')->where("$sql_x AND user_reg_type = 0")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);

	    $yAxis_title_raw = '单位（人）';
	    $yAxis_title = json_encode($yAxis_title_raw);

	    $data_name_raw = ['web', 'wap', 'android', 'IOS', '合计'];
	    $data_name = json_encode($data_name_raw);

	    $list = $this->get_list_reg_pt($start_time, $end_time);
	    $arr_a = array();
	    $arr_b = array();
	    $arr_c = array();
	    $arr_d = array();
	    $all = array();
	    foreach ($list as $v) {
		if ($v['user_reg_type'] == 1) {
		    $arr_a[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 2) {
		    $arr_b[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 3) {
		    $arr_c[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 4) {
		    $arr_d[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 0) {
		    $all[] = (int) $v['user_reg_count'];
		}
	    }
	    $arrY = array($arr_a, $arr_b, $arr_c, $arr_d, $all); 
	    $data_array = json_encode($arrY);

	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);

	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);

	    $statistics_data = M('statistical_reg_from')->where($sql_x)->select();
	    $client_list = array();
	    foreach ($statistics_data as $key => $val) {
		$client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
		$client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		if ($val['user_reg_type'] == $statistics_data[$key]['user_reg_type']) {
		    $client_list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
		}
	    }
	    $client_list['gross']['user_reg_date'] = "总计";
	    krsort($client_list);
	    $this->assign('client_list', $client_list);
	}
	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);
	//表格数据 多天
	$list_table = M('statistical_reg_from')->where("$sql_x AND user_reg_type = 0")->select();
	$this->assign('list_table', $list_table);
	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));
	//表格数据 多天
	$list_sum = M('statistical_reg_from')->where("$sql_x AND user_reg_type = 0")->select();
	$this->assign('list_sum', $list_sum);
	$this->assign('list_date', $arrX);
	$this->assign('list_web', $arr_a);
	$this->assign('list_wap', $arr_b);
	$this->assign('list_android', $arr_c);
	$this->assign('list_ios', $arr_d);
	$this->display();
    }
    //注册总表
    public function statistics_sum() {
	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}

	//如果是一天的日期显示饼图（今天，昨天）
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {

	    $this->assign('type', 'bingtu');
	    //今天
	    if ($start_time == date('Ymd', time())) {
		//线上线下***************************************************

		$series_name_raw = '一天注册数';
		$series_name = json_encode($series_name_raw);

		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id > 0")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id = 0")->find();
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);
		//$data_name
		$data_name_raw = ["'线下'.,.{$list_day_xx['count']}个", "'线上'.,.{$list_day_xs['count']}个"];
		$data_name = json_encode($data_name_raw);
		//end线上线下***************************************************
		//平台***************************************************

		$series_name_rawa = '一天注册数';
		$series_namea = json_encode($series_name_rawa);

		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xxa = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 1")->find();
		$list_day_xsa = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 2")->find();
		$list_day_androida = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 3")->find();
		$list_day_iosa = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 4")->find();
		$count_suma = (int) $list_day_xxa['count'] + (int) $list_day_xsa['count'] + (int) $list_day_androida['count'] + (int) $list_day_iosa['count'];
		$pie_data_array_rawa = array($list_day_xxa['count'] / $count_suma, $list_day_xsa['count'] / $count_suma, $list_day_androida['count'] / $count_suma, $list_day_iosa['count'] / $count_suma);
		$pie_data_arraya = json_encode($pie_data_array_rawa);

		$data_name_rawa = ["'web'.,.{$list_day_xxa['count']}个", "'wap'.,.{$list_day_xsa['count']}个", "'android'.,.{$list_day_androida['count']}个", "'IOS'.,.{$list_day_iosa['count']}个"];
		$data_namea = json_encode($data_name_rawa);

		$this->assign('xAxis_pota', $xAxis_pota);
		$this->assign('yAxis_titlea', $yAxis_titlea);
		$this->assign('data_namea', $data_namea);
		$this->assign('data_arraya', $data_arraya);
		$this->assign('unita', $unita);
		$this->assign('series_namea', $series_namea);
		$this->assign('pie_data_arraya', $pie_data_arraya);


		//end平台***************************************************
	    } else {
		//昨天
		//线上线下***************************************************
		$series_name_raw = '一天注册数';
		$series_name = json_encode($series_name_raw);
		$list = $this->get_list_reg($start_time);
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5) {
			$a = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 6) {
			$b = $v['user_reg_count'];
		    }
		}
		$data_name_raw = ["'线下'.,.{$a}个", "'线上'.,.{$b}个"];
		$data_name = json_encode($data_name_raw);
		$arr_a = array();
		$arr_b = array();
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5)
			$arr_a[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 6)
			$arr_b[] = $v['user_reg_count'];
		}
		$count_sum = intval($arr_a[0]) + intval($arr_b[0]);
		$pie_data_array_raw = array($arr_a[0] / $count_sum, $arr_b[0] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//线上线下***************************************************
		//平台***************************************************
		$series_name_rawa = '一天注册数';
		$series_namea = json_encode($series_name_rawa);
		$lista = $this->get_list_reg_pt($start_time); 
		foreach ($lista as $v) {
		    if ($v['user_reg_type'] == 1) {
			$aa = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 2) {
			$bb = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 3) {
			$cc = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 4) {
			$dd = $v['user_reg_count'];
		    }
		}
		$data_name_rawa = ["'web'.,.{$aa}个", "'wap'.,.{$bb}个", "'android'.,.{$cc}个", "'IOS'.,.{$dd}个"];
		$data_namea = json_encode($data_name_rawa);
		$arr_aa = array();
		$arr_bb = array();
		$arr_cc = array();
		$arr_dd = array();
		foreach ($lista as $v) {
		    if ($v['user_reg_type'] == 1)
			$arr_aa[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 2)
			$arr_bb[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 3)
			$arr_cc[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 4)
			$arr_dd[] = $v['user_reg_count'];
		}
		$count_suma = (intval($arr_aa[0]) + intval($arr_bb[0]) + intval($arr_cc[0]) + intval($arr_dd[0])) / 100;

		$pie_data_array_rawa = array($arr_aa[0] / $count_suma, $arr_bb[0] / $count_suma, $arr_cc[0] / $count_suma, $arr_dd[0] / $count_suma);
		$pie_data_arraya = json_encode($pie_data_array_rawa); 

		$this->assign('xAxis_pota', $xAxis_pota);
		$this->assign('yAxis_titlea', $yAxis_titlea);
		$this->assign('data_namea', $data_namea);
		$this->assign('data_arraya', $data_arraya);
		$this->assign('unita', $unita);
		$this->assign('series_namea', $series_namea);
		$this->assign('pie_data_arraya', $pie_data_arraya);
		//end平台***************************************************
	    }
	}else {
	    $this->assign('type', 'xiantu');
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date <= {$end_time}";
	    $x = M('statistical_user_reg')->where("$sql_x AND user_reg_type = 0")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);

	    $yAxis_title_raw = '单位（人）';
	    $yAxis_title = json_encode($yAxis_title_raw);

	    $data_name_raw = ['注册总数'];
	    $data_name = json_encode($data_name_raw);

	    $list = M('statistical_user_reg')->where("$sql_x AND user_reg_type = 0")->order('user_reg_date desc')->select(); 
	    $arr_a = array();


	    $aa = 0;
	    foreach ($list as &$v) {
		$aa = $aa + $v['user_reg_count'];
		$arr_a[] = (int) $v['user_reg_count'];
	    }
	    krsort($arr_a);
	    $list['gross']['user_reg_date'] = "总计"; 
	    $list['gross']['user_reg_count'] = $aa;
	    $arrY = array(array_values($arr_a));
	    $data_array = json_encode($arrY);


	    //$unit
	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);


	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);
	}
	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);

	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));


	//表格数据
	$this->assign('list', $list);


	$this->display();
    }

    //注册总表
    public function statistics_qd() {
	$list_conf = require_once APP_ROOT_PATH . "data_conf/MarketingChannels.php";
	if (empty($_REQUEST['choise'])) {
	    $choise = "baidu,360,anzhuo,91,yingyongbao,huawei,xiaomi,anzhi,wandoujia,yingyonghui,Nduo,lianxiang,oppo,sogou,jifeng,fuyi,fensitong";
	} else {
	    $choise = implode(",", $_REQUEST['choise']);
	}
	$list_choise = explode(',', $choise);
	$this->assign('list_choise', $list_choise);

	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {


	    //$series_name
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);
	    if ($start_time == date('Ymd', time())) {
		//$pie_data_array
		$this->assign('type', 'bingtua');
		$day_date = strtotime("$start_time 00:00:00");
		$list_day = array();
		foreach ($list_choise as &$v) {
		    $res = M('user')->field('count(id) as count,create_time,search_channel')->where("create_time >= {$day_date} AND search_channel = '{$v}'")->find();
		    if (!$res['search_channel']) {
			$res['search_channel'] = $v;
		    }
		    $list_day[] = $res;
		}
		$count_sum = 0;
		$list_count = array();
		foreach ($list_day as $v) {

		    $count_sum = intval($v['count']) + $count_sum;
		    $list_count[] = $v['count'];
		}
		$pie_data_array_raw = array();
		foreach ($list_count as $v) {
		    $pie_data_array_raw[] = (int) $v / $count_sum;
		}
		$pie_data_array = json_encode($pie_data_array_raw);

		//$data_name
		$data_name_raw = array();
		$list_type = array();
		$list_count = array();
		foreach ($list_day as $v) {
		    $data_name_raw[] = "'{$v['search_channel']}'" . ',' . "{$v['count']}" . "个";
		$list_type[] = $list_conf[$v['search_channel']];
		    $list_count[] = $v['count'];
		}
		//表格数据

		$this->assign('start_time', $start_time);
		$this->assign('list_type', $list_type);
		$this->assign('list_count', $list_count);
	    } else {
		//昨天
		$this->assign('type', 'bingtub');
		$res = array();
		foreach ($list_choise as $v) {
		    $res[] = M('statistical_reg_qd')->where("user_reg_date = {$start_time}  AND user_reg_type = '{$v}'")->find();
		}
		foreach ($res as $v) {
		    $count_sum = $count_sum + (int) $v['user_reg_count'];
		}
		$pie_data_array_raw = array();
		foreach ($res as $v) {
		    $pie_data_array_raw[] = (int) $v['user_reg_count'] / $count_sum;
		}
		$pie_data_array = json_encode($pie_data_array_raw);
		//$data_name
		$data_name_raw = array();
		$list_type = array();
		$list_count = array();
		foreach ($res as $v) {
		    $data_name_raw[] = "'{$v['user_reg_type']}'" . ',' . "{$v['user_reg_count']}" . "个";
		    $list_type[] = $list_conf[$v['user_reg_type']];
		    $list_count[] = $v['user_reg_count'];
		}

		//表格数据
		$this->assign('start_time', $start_time);
		$this->assign('list_type', $list_type);
		$this->assign('list_count', $list_count);
	    }
	} else {
	    $this->assign('type', 'xiantu');
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_reg_qd')->where("$sql_x AND user_reg_type = 91")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);

	    $yAxis_title_raw = '单位(人)';
	    $yAxis_title = json_encode($yAxis_title_raw);

	    $data_name_raw = $list_choise;

	    $arr_Y = array();
	    $all = array();
	    foreach ($list_choise as $v) {

		$arr_Y[] = M('statistical_reg_qd')->where("$sql_x AND user_reg_type = '{$v}'")->select();
	    }
	    foreach ($arr_Y as $key => $val) {

		foreach ($val as $k => $v) {
		    if ($v['user_reg_type'] == $list_choise[$key]) {
			$list_choise_new[$key][] = (int) $v['user_reg_count'];
		    }
		}
	    }
	    $data_array_raw = $list_choise_new;
	    
	    $data_array = json_encode($data_array_raw);

	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);


	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);
	}
	
	$data_name_raw_zh=array();
	foreach($data_name_raw as $key=>$val){
	    $data_name_raw_zh[$key]=$list_conf[$val];
	}
	$data_name = json_encode($data_name_raw_zh);
	
	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);

	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));
	$this->assign('list', $list_conf);

	krsort($arrX);
	//表格数据
	$this->assign('list_x', $data_name_raw_zh);
	$this->assign('list_date', $arrX);
	$this->assign('date_array', $data_array_raw);


	$this->display();
    }

//邀请来源统计 线上线下
    public function statistics_yqly() {
	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}
	//如果是一天的日期显示饼图（今天，昨天）
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {
	    $list = $this->get_list_reg_yqly($start_time); 
	    //$series_name
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);
	    //今天
	    if ($start_time == date('Ymd', time())) {
		$this->assign('type', 'bingtua');
		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id > 0")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id = 0")->find();
		//$data_name

		$data_name_raw = ["'线下邀请'.,.{$list_day_xx['count']}个", "'线上邀请'.,.{$list_day_xs['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//表格
		$list_suma = $list_day_xx['count'] + $list_day_xs['count'];
		$xianxia = $list_day_xx['count'];
		$xianshang = $list_day_xs['count'];

		$this->assign('user_date', $start_time);
		$this->assign('list_suma', $list_suma);
		$this->assign('xianxia', $xianxia);
		$this->assign('xianshang', $xianshang);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $xianxia)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $xianshang)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma)
		);

		//$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 5) {
			$client_list[$val['user_reg_date']]['dowm'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 6) {
			$client_list[$val['user_reg_date']]['top'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    } else {
		$this->assign('type', 'bingtub');
		//昨天
		$list = $this->get_list_reg_yqly($start_time);
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5) {
			$a = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 6) {
			$b = $v['user_reg_count'];
		    }
		}
		//$data_name
		$data_name_raw = ["'线上邀请'.,.{$a}个", "'线下邀请'.,.{$b}个"];
		$data_name = json_encode($data_name_raw);
		//$pie_data_array
		$arr_a = array();
		$arr_b = array();
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 5)
			$arr_a[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 6)
			$arr_b[] = $v['user_reg_count'];
		}
		$count_sum = intval($arr_a[0]) + intval($arr_b[0]);
		$pie_data_array_raw = array($arr_a[0] / $count_sum, $arr_b[0] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);
		//表格
		$list_suma = M('statistical_reg_yqly')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
		$this->assign('user_date', $start_time);
		$this->assign('list_suma', $list_suma);
		$this->assign('list_xianxia', $arr_a);
		$this->assign('list_xianshang', $arr_b);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $arr_a[0])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $arr_b[0])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma['user_reg_count'])
		);

		//$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 5) {
			$client_list[$val['user_reg_date']]['dowm'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 6) {
			$client_list[$val['user_reg_date']]['top'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    }
	} else {
	    $this->assign('type', 'xiantu');
	    //$xAxis_raw
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_reg_yqly')->where("$sql_x AND user_reg_type = 0")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);


	    //$yAxis_title
	    $yAxis_title_raw = '单位（人）';
	    $yAxis_title = json_encode($yAxis_title_raw);


	    //$data_name
	    $data_name_raw = ['线上邀请', '线下邀请', '合计'];
	    $data_name = json_encode($data_name_raw);


	    //$data_array
	    $list = $this->get_list_reg_yqly($start_time, $end_time); 
	    $arr_a = array();
	    $arr_b = array();
	    $all = array();
	    foreach ($list as $v) {
		if ($v['user_reg_type'] == 5) {
		    $arr_a[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 6) {
		    $arr_b[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 0) {
		    $all[] = (int) $v['user_reg_count'];
		}
	    }
	    $arrY = array($arr_a, $arr_b, $all); 
	    $data_array = json_encode($arrY);


	    //$unit
	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);


	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);

	    //新表格数据
	    $statistics_data = M('statistical_reg_yqly')->where($sql_x)->select();
	    $client_list = array();
	    foreach ($statistics_data as $key => $val) {
		$client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
		$client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		if ($val['user_reg_type'] == $statistics_data[$key]['user_reg_type']) {
		    $client_list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
		}
	    }
	    $client_list['gross']['user_reg_date'] = "总计";
	    krsort($client_list);
	    $this->assign('client_list', $client_list);
	}

	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);

	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));

	//表格数据 多天
	$list_sum = M('statistical_reg_yqly')->where("$sql_x AND user_reg_type = 0")->select();

	$this->assign('list_sum', $list_sum);
	$this->assign('list_date', $arrX);
	$this->assign('list_xianxia', $arr_a);
	$this->assign('list_xianshang', $arr_b);





	$this->display();
    }

    //邀请渠道统计 web wap android ios
    public function statistics_yqqd() {

	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}
	//如果是一天的日期显示饼图（今天，昨天）
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {
	    $this->assign('type', 'bingtu');
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);

	    //今天
	    if ($start_time == date('Ymd', time())) {

		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0) AND terminal = 1")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 2")->find();
		$list_day_android = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 3")->find();
		$list_day_ios = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 4")->find();
		//$data_name
		$data_name_raw = ["'web'.,.{$list_day_xx['count']}个", "'wap'.,.{$list_day_xs['count']}个", "'android'.,.{$list_day_android['count']}个", "'IOS'.,.{$list_day_ios['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'] + (int) $list_day_android['count'] + (int) $list_day_ios['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum, $list_day_android['count'] / $count_sum, $list_day_ios['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//表格数据

		$this->assign('list_datea', $start_time);
		$this->assign('a', $list_day_xx['count']);
		$this->assign('b', $list_day_xs['count']);
		$this->assign('c', $list_day_android['count']);
		$this->assign('d', $list_day_ios['count']);
		$this->assign('e', $count_sum);


		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $count_sum)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $list_day_xx['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $list_day_xs['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $list_day_android['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $list_day_ios['count'])
		);

		//$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 1) {
			$client_list[$val['user_reg_date']]['web'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 2) {
			$client_list[$val['user_reg_date']]['wap'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 3) {
			$client_list[$val['user_reg_date']]['android'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 4) {
			$client_list[$val['user_reg_date']]['ios'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    } else {
		//昨天
		$list = $this->get_list_reg_pt_yqqd($start_time); 
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 1) {
			$a = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 2) {
			$b = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 3) {
			$c = $v['user_reg_count'];
		    }
		    if ($v['user_reg_type'] == 4) {
			$d = $v['user_reg_count'];
		    }
		}
		//$data_name
		$data_name_raw = ["'web'.,.{$a}个", "'wap'.,.{$b}个", "'android'.,.{$c}个", "'IOS'.,.{$d}个"];
		$data_name = json_encode($data_name_raw);
		//$pie_data_array
		$arr_a = array();
		$arr_b = array();
		$arr_c = array();
		$arr_d = array();
		foreach ($list as $v) {
		    if ($v['user_reg_type'] == 1)
			$arr_a[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 2)
			$arr_b[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 3)
			$arr_c[] = $v['user_reg_count'];
		    if ($v['user_reg_type'] == 4)
			$arr_d[] = $v['user_reg_count'];
		}
		$count_sum = (intval($arr_a[0]) + intval($arr_b[0]) + intval($arr_c[0]) + intval($arr_d[0])) / 100;

		$pie_data_array_raw = array($arr_a[0] / $count_sum, $arr_b[0] / $count_sum, $arr_c[0] / $count_sum, $arr_d[0] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw); 
		//表格数据
		$list_sum = M('statistical_reg_yqqd')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
		$this->assign('list_datea', $start_time);
		$this->assign('a', $a);
		$this->assign('b', $b);
		$this->assign('c', $c);
		$this->assign('d', $d);
		$this->assign('e', $list_sum['user_reg_count']);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_sum['user_reg_count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $a)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $b)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $c)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $d)
		);

		//$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
		$client_list = array();
		foreach ($statistics_data as $key => $val) {
		    $client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		    if ($val['user_reg_type'] == 0) {
			$client_list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 1) {
			$client_list[$val['user_reg_date']]['web'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 2) {
			$client_list[$val['user_reg_date']]['wap'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 3) {
			$client_list[$val['user_reg_date']]['android'] = $val['user_reg_count'];
		    }
		    if ($val['user_reg_type'] == 4) {
			$client_list[$val['user_reg_date']]['ios'] = $val['user_reg_count'];
		    }
		}
		$this->assign('client_list', $client_list);
	    }
	} else {
	    $this->assign('type', 'xiantu');

	    //$xAxis_raw
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_reg_yqqd')->where("$sql_x AND user_reg_type = 0")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);


	    //$yAxis_title
	    $yAxis_title_raw = '单位（人）';
	    $yAxis_title = json_encode($yAxis_title_raw);


	    //$data_name
	    $data_name_raw = ['web', 'wap', 'android', 'IOS', '合计'];
	    $data_name = json_encode($data_name_raw);


	    //$data_array
	    $list = $this->get_list_reg_pt_yqqd($start_time, $end_time);
	    $arr_a = array();
	    $arr_b = array();
	    $arr_c = array();
	    $arr_d = array();
	    $all = array();
	    foreach ($list as $v) {
		if ($v['user_reg_type'] == 1) {
		    $arr_a[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 2) {
		    $arr_b[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 3) {
		    $arr_c[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 4) {
		    $arr_d[] = (int) $v['user_reg_count'];
		}
		if ($v['user_reg_type'] == 0) {
		    $all[] = (int) $v['user_reg_count'];
		}
	    }
	    $arrY = array($arr_a, $arr_b, $arr_c, $arr_d, $all); 
	    $data_array = json_encode($arrY);


	    //$unit
	    $unit_raw = '人';
	    $unit = json_encode($unit_raw);

	    $series_name_raw = 'testname';
	    $series_name = json_encode($series_name_raw);
	    $pie_data_array_raw = [20, 25, 40, 15];
	    $pie_data_array = json_encode($pie_data_array_raw);

	    //新表格数据
	    $statistics_data = M('statistical_reg_yqqd')->where($sql_x)->select();
	    $client_list = array();
	    foreach ($statistics_data as $key => $val) {
		$client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
		$client_list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
		if ($val['user_reg_type'] == $statistics_data[$key]['user_reg_type']) {
		    $client_list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
		}
	    }
	    $client_list['gross']['user_reg_date'] = "总计";
	    krsort($client_list);
	    $this->assign('client_list', $client_list);
	}



	$this->assign('xAxis_pot', $xAxis_pot);
	$this->assign('yAxis_title', $yAxis_title);
	$this->assign('data_name', $data_name);
	$this->assign('data_array', $data_array);
	$this->assign('unit', $unit);
	$this->assign('series_name', $series_name);
	$this->assign('pie_data_array', $pie_data_array);


	//表格数据 多天
	$list_table = M('statistical_reg_yqqd')->where("$sql_x AND user_reg_type = 0")->select(); 
	$this->assign('list_table', $list_table);


	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));

	//表格数据 多天
	$list_sum = M('statistical_reg_yqqd')->where("$sql_x AND user_reg_type = 0")->select();
	$this->assign('list_sum', $list_sum);
	$this->assign('list_date', $arrX);
	$this->assign('list_web', $arr_a);
	$this->assign('list_wap', $arr_b);
	$this->assign('list_android', $arr_c);
	$this->assign('list_ios', $arr_d);




	$this->display();
    }

    private function history_user_count() {
	$time = time() - 30 * 24 * 3600;
	$arr = M()->query("SELECT admin_id,count(id) as num,FROM_UNIXTIME(create_time,'%Y%m%d') as Y from fanwe_user WHERE admin_id > 0 AND create_time >" . $time . " GROUP BY Y");
	$i = 0;
	foreach ($arr as $v) {
	    $data['type'] = 1;
	    $data['count'] = $arr[$i]['num'];
	    $data['date'] = $arr[$i]['Y'];
	    $res = M('register_statistical')->add($data);
	    $i++;
	}

	$arr_a = M()->query("SELECT admin_id,count(id) as numa,FROM_UNIXTIME(create_time,'%Y%m%d') as Ya from fanwe_user WHERE admin_id = 0 AND create_time >" . $time . " GROUP BY Ya");
	$j = 0;
	foreach ($arr_a as $va) {
	    $dataa['type'] = 0;
	    $dataa['count'] = $arr_a[$j]['numa'];
	    $dataa['date'] = $arr_a[$j]['Ya'];
	    $resa = M('register_statistical')->add($dataa);
	    $j++;
	}
    }

    // （脚本） 线上线下 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    private function statistical_user_reg_jb($data_star) {

	$day = $data_star;

	//$time统计开始时间，$timeb统计结束时间
	$time = strtotime("$data_star 00:00:00");
	$timeb = strtotime("$data_star +1 day");
	//$i==1当天'线上'注册数
	for ($i = 1; $i < 4; $i++) {
	    if ($i == 1) {
		$sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id = 0";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
		$data['user_reg_type'] = 6;
	    }
	    //$i==2当天'线下'注册数
	    if ($i == 2) {
		$sql_admin = "create_time >= {$time} AND create_time< {$timeb} AND admin_id > 0";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 1";
		$data['user_reg_type'] = 5;
	    }
	    //$i==2当天注册总数
	    if ($i == 3) {
		$sql_admin = "create_time >= {$time} AND create_time< {$timeb}";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
		$data['user_reg_type'] = 0;
	    }
	    $arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
	    $data['reg_year'] = mb_substr($day, 0, 4);
	    $data['reg_month'] = mb_substr($day, 4, 2);
	    $data['user_reg_count'] = (int) $arr_xs[0]['count'];
	    $data['user_reg_date'] = $day;

	    $res = M('statistical_user_reg')->where($sql_type)->find();
	    if ($res) {
		if ($res['user_reg_count'] != $data['user_reg_count']) {
		    M('statistical_user_reg')->where($sql_type)->save($data);
		}
	    } else {
		M('statistical_user_reg')->add($data);
	    }
	}
    }

// （脚本） 平台统计 统计之前的每天的增加用户脚本 $data_star传入格式20150801
    private function get_list_pt_jb($data_star) {
	$day = $data_star;
	//$time统计开始时间，$timeb统计结束时间
	$time = strtotime("$data_star 00:00:00");
	$timeb = strtotime("$data_star +1 day");
	//$i==1当天'web'注册数
	for ($i = 1; $i < 6; $i++) {
	    if ($i == 1) {
		$sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 1";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 1";
		$data['user_reg_type'] = 1;
	    }
	    //$i==2当天'wap'注册数
	    if ($i == 2) {
		$sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 2";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 2";
		$data['user_reg_type'] = 2;
	    }
	    //$i==3当天'android'注册数
	    if ($i == 3) {
		$sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 3";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 3";
		$data['user_reg_type'] = 3;
	    }
	    //$i==4当天'IOS'注册数
	    if ($i == 4) {
		$sql_admin = "create_time >= {$time} AND create_time < {$timeb} AND terminal = 4";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 4";
		$data['user_reg_type'] = 4;
	    }
	    //$i==5当天注册总数
	    if ($i == 5) {
		$sql_admin = "create_time >= {$time} AND create_time < {$timeb}";
		$sql_type = "user_reg_date = {$day} AND user_reg_type = 0";
		$data['user_reg_type'] = 0;
	    }
	    $arr_xs = M('user')->field('count(id) as count')->where($sql_admin)->select();
	    $data['user_reg_year'] = mb_substr($day, 0, 4);
	    $data['user_reg_month'] = mb_substr($day, 4, 2);
	    $data['user_reg_count'] = (int) $arr_xs[0]['count'];
	    $data['user_reg_date'] = $day;
	    $res = M('statistical_reg_from')->where($sql_type)->find();
	    if ($res) {
		if ($res['user_reg_count'] != $data['user_reg_count']) {
		    M('statistical_reg_from')->where($sql_type)->save($data);
		}
	    } else {
		M('statistical_reg_from')->add($data);
	    }
	}
    }

    // (脚本) 渠道统计脚本
    private function get_reg_qd_jb($data_star) {
	$day = $data_star;
	//$time统计开始时间，$timeb统计结束时间
	$time = strtotime("$data_star 00:00:00");
	$timeb = strtotime("$data_star +1 day");

	$list = $this->get_reg_from_qd();
	foreach ($list as $key => $v) {
	    $res = M('user')->field('count(id) as count')->where("create_time >= {$time} create_time < {$timeb} AND search_channel = {$v}")->find();
	    $data['user_reg_date'] = $day;
	    $data['user_reg_year'] = mb_substr($day, 0, 4);
	    $data['user_reg_month'] = mb_substr($day, 4, 2);
	    $data['user_reg_type'] = $v;
	    if ($res) {
		$data['user_reg_count'] = $res['count'];
	    } else {
		$data['user_reg_count'] = 0;
	    }

	    $rest = M('statistical_reg_qd')->where("user_reg_date = {$day} AND user_reg_type = {$v}")->find();
	    if ($rest) {
		if ($rest['user_reg_count'] != $data['user_reg_count']) {
		    M('statistical_reg_qd')->save($data);
		}
	    } else {
		M('statistical_reg_qd')->add($data);
	    }
	}
    }

    //取多个平台（ios，安卓，web，wap）数据
    private function get_list_reg_pt($date_star, $date_end = '') {
	//取一段时间范围内的注册用户数据
	if ($date_star && $date_end) {
	    $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
	    //取线上线下注册数据
	    $list = M('statistical_reg_from')->where($sql)->select();
	} else {
	    //判断日期是否是昨天
	    if ($date_star != date('Ymd', time())) {
		$list = M('statistical_reg_from')->where("user_reg_date = {$date_star}")->select();
	    } else {
		//取当天的用户注册 总数据
		$day_date = strtotime("$date_star 00:00:00");
		$list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
		//取当天的用户注册各平台数据（web，wap，ios,安卓）
		for ($i = 1; $i < 5; $i++) {
		    $list[] = M('user')->field('count(id) as count,terminal')->where("create_time >= {$day_date} AND terminal = {$i}")->find();
		    if (!$list[$i - 1]['terminal']) {
			$list[$i - 1]['terminal'] = $i;
			$list[$i - 1]['count'] = 0;
		    }
		}
	    }
	}

	return $list;
    }

    //线上线下统计，$date_star开始时间 $date_end结束时间  两个值都有就是去范围内的值
    private function get_list_reg($date_star, $date_end = '') {
//                $date_star = str_replace('-', $date_star);
//                $date_end = str_replace('-', $date_end);
	//取一段时间范围内的注册用户数据
	if ($date_star && $date_end) {
	    $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
	    //取线上线下注册数据
	    $list = M('statistical_user_reg')->where($sql)->select();
	} else {
	    //判断是否是昨天
	    if ($date_star != date('Ymd', time())) {
		$list = M('statistical_user_reg')->where("user_reg_date = {$date_star}")->select();
	    } else {
		//取当天的用户注册总数据
		$day_date = strtotime("$date_star 00:00:00");
		$list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
		//取当天的用户注册线上线下数据
		for ($i = 0; $i < 2; $i++) {
		    $sql_num = " =$i";
		    $list[] = M('user')->field('count(id) as count,admin_id')->where("create_time >= {$day_date} AND admin_id $sql_num")->find();
		}
	    }
	}
	return $list;
    }

    //邀请 线上线下，$date_star开始时间 $date_end结束时间  两个值都有就是去范围内的值
    private function get_list_reg_yqly($date_star, $date_end = '') {
//                $date_star = str_replace('-', $date_star);
//                $date_end = str_replace('-', $date_end);
	//取一段时间范围内的注册用户数据
	if ($date_star && $date_end) {
	    $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
	    //取线上线下注册数据
	    $list = M('statistical_reg_yqly')->where($sql)->select();
	} else {
	    //判断是否是昨天
	    if ($date_star != date('Ymd', time())) {
		$list = M('statistical_reg_yqly')->where("user_reg_date = {$date_star}")->select();
	    } else {
		//取当天的用户注册总数据
		$day_date = strtotime("$date_star 00:00:00");
		$list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
		//取当天的用户注册线上线下数据
		for ($i = 0; $i < 2; $i++) {
		    if ($i = 0) {
			$sql_num = " admin_id >0";
		    }
		    if ($i = 1) {
			$sql_num = " pid >0";
		    }

		    $list[] = M('user')->field('count(id) as count,admin_id')->where("create_time >= {$day_date} AND $sql_num")->find();
		}
	    }
	}
	return $list;
    }

    //取多个平台（ios，安卓，web，wap）数据
    private function get_list_reg_pt_yqqd($date_star, $date_end = '') {
	//取一段时间范围内的注册用户数据
	if ($date_star && $date_end) {
	    $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
	    //取线上线下注册数据
	    $list = M('statistical_reg_yqqd')->where($sql)->select();
	} else {
	    //判断日期是否是昨天
	    if ($date_star != date('Ymd', time())) {
		$list = M('statistical_reg_yqqd')->where("user_reg_date = {$date_star}")->select();
	    } else {
		//取当天的用户注册 总数据
		$day_date = strtotime("$date_star 00:00:00");
		$list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
		//取当天的用户注册各平台数据（web，wap，ios,安卓）
		for ($i = 1; $i < 5; $i++) {
		    $list[] = M('user')->field('count(id) as count,terminal')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0) AND terminal = {$i}")->find();
		    if (!$list[$i - 1]['terminal']) {
			$list[$i - 1]['terminal'] = $i;
			$list[$i - 1]['count'] = 0;
		    }
		}
	    }
	}

	return $list;
    }

    //取渠道统计数据 $date_star开始时间 $date_end结束时间  两个值都有就是去范围内的值
    private function get_list_reg_qd($date_star, $date_end = '') {
	//                $date_star = str_replace('-', $date_star);
	//                $date_end = str_replace('-', $date_end);
	//取一段时间范围内的注册用户数据
	if ($date_star && $date_end) {
	    $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
	    $list = M('statistical_reg_qd')->where($sql)->select();
	} else {
	    //判断是否是昨天
	    if ($date_star != date('Ymd', time())) {
		$sql = "user_reg_date = {$date_star}";
		$list = M('statistical_reg_qd')->where($sql)->select();
	    } else {
		//取当天的用户注册总数据
		$day_date = strtotime("$date_star 00:00:00");
		$list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date} AND search_channel != 0 AND search_channel != ''")->find();
		//取当天的用户注册线上线下数据
		$arr = $this->get_reg_from_qd();
		foreach ($arr as $key => $v) {
		    $list[] = M('user')->field('count(id) as count,search_channel')->where("create_time >= {$day_date} AND search_channel = {$v}")->find();
		}
	    }
	}
	return $list;
    }

    //线上线下表格数据
    private function get_list_reg_table() {
	$list = M('statistical_user_reg')->select();
	$this->assign('list', $list);
	$this->display("statisticspopulog/statistics_user.html");
    }

    //平台注册表格数据
    private function get_list_reg_pt_table() {
	$list = M('statistical_reg_from')->select();
	$this->assign('list', $list);
	$this->display();
    }

    //渠道来源表格
    private function get_list_reg_qd_table() {
	$list = M('statistical_reg_qd')->select();
	$this->assign('list', $list);
	$this->display();
    }

//注册渠道来源数组
    private function get_reg_from_qd() {
	$list = array(
	    //应用市场
	    "百度手机助手" => "baidu",
	    "360手机助手" => "360",
	    "安卓市场" => "anzhuo",
	    "91助手" => "91",
	    "腾讯应用宝" => "yingyongbao",
	    "华为应用商店" => "huawei",
	    "小米应用商店" => "xiaomi",
	    "安智市场" => "anzhi",
	    "豌豆荚" => "wandoujia",
	    "应用汇" => "yingyonghui",
	    "N多市场" => "Nduo",
	    "联想Store" => "lianxiang",
	    "oppo市场" => "oppo",
	    "搜狗" => "sogou",
	    "机锋" => "jifeng",
	    "扶翼" => "fuyi",
	    "粉丝通" => "fensitong",
	    //搜索
	    "百度搜索" => "baidu_search",
	    "sogo搜索" => "sugo_search",
	    "360搜索" => "360_search",
	    "google搜索" => "google_search",
	    "我们网站" => "public",
	    //IOS渠道
	    "点入" => "dianru",
	    "力美" => "limei",
	    "苹果市场" => "app_store",
	    "木蚂蚁" => "mumayi",
	    "易用汇" => "yiyonghui",
	    "魅族" => "meizu",
	    "酷派" => "kupai",
	    "vivo" => "vivo",
	    "苏宁应用商店" => "suning",
	    "历趣" => "liqu",
	    "优亿市场" => "youyi",
	    "pp助手" => "ppzhushou",
	    "宝软联盟" => "baoruan",
	    "十字猫" => "shizimao",
	    "三星" => "sanxing",
	    "中兴应用商店" => "zhongxing",
	    "TCL开发者平台" => "tcl",
	    "天语手机开发者" => "tianyu",
	    "新浪应用中心" => "xinlang",
	    "网易应用中心" => "wangyi",
	    "搜狐应用中心" => "souhu",
	    "奇珀市场" => "qipo",
	    "当贝市场" => "dangbei",
	    "冒泡市场" => "maopao",
	    "久邦" => "jiubang",
	    "飞流" => "feiliu",
	    "开奇" => "kaiqi",
	    "安极市场" => "anji",
	    "网讯" => "wangxun",
	    "开心网" => "kaixinwang",
	    "安卓乐园" => "anzhuoleyuan",
	    "飞信" => "feixin",
	    "手机乐园" => "shoujileyuan",
	    "宜搜" => "yisou",
	    "安卓之家" => "anzhuozhijia",
	    "机客" => "jike",
	    "卓乐网" => "zhuolewang",
	    "宝瓶网" => "baopingwang",
	    "酷传" => "kuchuan",
	    "安卓在线" => "anzhuozaixian",
	    "安贝市场" => "anbeishichang",
	    "当乐网" => "danglewang",
	    "手机之家" => "shoujizhijia",
	    "安粉丝" => "anfensi",
	    "安卓网" => "anzhuowang",
	    "飞鹏网" => "feipengwang",
	    "移动" => "yidong",
	    "联通" => "liantong",
	    "电信" => "dianxin"
	);
	return $list;
    }

//excel 导出**********************************************************************************************************************
    //注册总计 统计导出
    public function export_reg_sum($page = 1) {
	//从url中获取开始时间，
	$start_time = $_REQUEST["start_time"];
	//从url中获取结束时间
	$end_time = $_REQUEST["end_time"];
	$start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
	$end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
	//当天的数据或者昨天的数据
	if ($start_time == $end_time) {
	    
	} else {
	    $lists = M("statistical_user_reg")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
	}
	$list = array();
	foreach ($lists as $key => $val) {
	    $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
	    if ($val['user_reg_type'] == 0) {
		$list[$val['user_reg_date']]['all'] = $val['user_reg_count'];
	    }
	}
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $num, '时间')
			->setCellValue('B' . $num, "合计(人)");
		$num = 2;
	    }
	    $objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A' . $num, $val['user_reg_date'])
		    ->setCellValue('B' . $num, $val['all']);


	    $num++;
	}
	//设置属性
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
	$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的注册总计统计表";
	php_export_excel($objPHPExcel, $filename);
    }

    //注册来源 统计导出
    public function export_reg_ly($page = 1) {
	//从url中获取开始时间，
	$start_time = $_REQUEST["start_time"];
	//从url中获取结束时间
	$end_time = $_REQUEST["end_time"];
	$start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
	$end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
	//当天的数据或者昨天的数据
	if ($start_time == $end_time) {
	    if ($start_time_int == date('Ymd', time())) {
		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id > 0")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id = 0")->find();
		//$data_name

		$data_name_raw = ["'线下'.,.{$list_day_xx['count']}个", "'线上'.,.{$list_day_xs['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//表格
		$list_suma = $list_day_xx['count'] + $list_day_xs['count'];
		$xianxia = $list_day_xx['count'];
		$xianshang = $list_day_xs['count'];

		$this->assign('user_date', $start_time); 
		$this->assign('list_suma', $list_suma);
		$this->assign('xianxia', $xianxia);
		$this->assign('xianshang', $xianshang);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $xianxia)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $xianshang)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma)
		);
		$lists = $statistics_data;
	    } else {
		//昨天
		$lists = M('statistical_user_reg')->where("user_reg_date = {$start_time_int}")->select();
	    }
	} else {
	    $lists = M("statistical_user_reg")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
	}
	$list = array();
	foreach ($lists as $key => $val) {
	    $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
	    $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
	    if ($val['user_reg_type'] == $lists[$key]['user_reg_type']) {
		$list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
	    }
	}
	$list['gross']['user_reg_date'] = "总计";
	krsort($list);
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $num, '时间')
			->setCellValue('B' . $num, "线下注册(人)")
			->setCellValue('C' . $num, "线上注册(人)")
			->setCellValue('D' . $num, "合计(人)");
		$num = 2;
	    }
	    $objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A' . $num, $val['user_reg_date'])
		    ->setCellValue('B' . $num, $val['5'])
		    ->setCellValue('C' . $num, $val['6'])
		    ->setCellValue('D' . $num, $val['0']);


	    $num++;
	}
	//设置属性
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的注册来源统计表";
	php_export_excel($objPHPExcel, $filename);
    }

    //注册平台 统计导出
    public function export_reg_pt($page = 1) {
	//从url中获取开始时间，
	$start_time = $_REQUEST["start_time"];
	//从url中获取结束时间
	$end_time = $_REQUEST["end_time"];
	$start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
	$end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
	//当天的数据或者昨天的数据
	if ($start_time == $end_time) {
	    if ($start_time_int == date('Ymd', time())) {
		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 1")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 2")->find();
		$list_day_android = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 3")->find();
		$list_day_ios = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND terminal = 4")->find();
		//$data_name
		$data_name_raw = ["'web'.,.{$list_day_xx['count']}个", "'wap'.,.{$list_day_xs['count']}个", "'android'.,.{$list_day_android['count']}个", "'IOS'.,.{$list_day_ios['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'] + (int) $list_day_android['count'] + (int) $list_day_ios['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum, $list_day_android['count'] / $count_sum, $list_day_ios['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);


		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $count_sum)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $list_day_xx['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $list_day_xs['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $list_day_android['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $list_day_ios['count'])
		);
		$lists = $statistics_data;
	    } else {
		//昨天
		$lists = M('statistical_reg_from')->where("user_reg_date = {$start_time_int}")->select();
	    }
	} else {
	    $lists = M("statistical_reg_from")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
	}
	$list = array();
	foreach ($lists as $key => $val) {
	    $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
	    $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
	    if ($val['user_reg_type'] == $lists[$key]['user_reg_type']) {
		$list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
	    }
	}
	$list['gross']['user_reg_date'] = "总计";
	krsort($list);
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $num, '时间')
			->setCellValue('B' . $num, "web(人)")
			->setCellValue('C' . $num, "wap(人)")
			->setCellValue('D' . $num, "Android(人)")
			->setCellValue('E' . $num, "ios(人)")
			->setCellValue('F' . $num, "合计(人)");
		$num = 2;
	    }
	    $objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A' . $num, $val['user_reg_date'])
		    ->setCellValue('B' . $num, $val['1'])
		    ->setCellValue('C' . $num, $val['2'])
		    ->setCellValue('D' . $num, $val['3'])
		    ->setCellValue('E' . $num, $val['4'])
		    ->setCellValue('F' . $num, $val['0']);


	    $num++;
	}
	//设置属性
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的注册平台统计表";
	php_export_excel($objPHPExcel, $filename);
    }

    //邀请注册来源 统计导出
    public function export_reg_yqly($page = 1) {
	//从url中获取开始时间，
	$start_time = $_REQUEST["start_time"];
	//从url中获取结束时间
	$end_time = $_REQUEST["end_time"];
	$start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
	$end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
	//当天的数据或者昨天的数据
	if ($start_time == $end_time) {
	    if ($start_time_int == date('Ymd', time())) {

		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id > 0")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND admin_id = 0")->find();
		//$data_name

		$data_name_raw = ["'线下邀请'.,.{$list_day_xx['count']}个", "'线上邀请'.,.{$list_day_xs['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);

		//表格
		$list_suma = $list_day_xx['count'] + $list_day_xs['count'];
		$xianxia = $list_day_xx['count'];
		$xianshang = $list_day_xs['count'];

		$this->assign('user_date', $start_time); 
		$this->assign('list_suma', $list_suma);
		$this->assign('xianxia', $xianxia);
		$this->assign('xianshang', $xianshang);

		//新表格数据

		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 5, 'user_reg_count' => $xianxia)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 6, 'user_reg_count' => $xianshang)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $list_suma)
		);
		$lists = $statistics_data;
	    } else {
		//昨天
		$lists = M('statistical_reg_yqly')->where("user_reg_date = {$start_time_int}")->select();
	    }
	} else {
	    $lists = M("statistical_reg_yqly")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
	}
	$list = array();
	foreach ($lists as $key => $val) {
	    $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
	    $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
	    if ($val['user_reg_type'] == $lists[$key]['user_reg_type']) {
		$list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
	    }
	}
	$list['gross']['user_reg_date'] = "总计";
	krsort($list);
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $num, '时间')
			->setCellValue('B' . $num, "线下邀请注册(人)")
			->setCellValue('C' . $num, "线上邀请注册(人)")
			->setCellValue('D' . $num, "合计(人)");
		$num = 2;
	    }
	    $objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A' . $num, $val['user_reg_date'])
		    ->setCellValue('B' . $num, $val['5'])
		    ->setCellValue('C' . $num, $val['6'])
		    ->setCellValue('D' . $num, $val['0']);


	    $num++;
	}
	//设置属性
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的邀请注册来源统计表";
	php_export_excel($objPHPExcel, $filename);
    }

    //邀请注册平台 统计导出
    public function export_reg_yqqd($page = 1) {
	//从url中获取开始时间，
	$start_time = $_REQUEST["start_time"];
	//从url中获取结束时间
	$end_time = $_REQUEST["end_time"];
	$start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
	$end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
	//当天的数据或者昨天的数据
	if ($start_time == $end_time) {

	    //今天
	    if ($start_time_int == date('Ymd', time())) {

		//$pie_data_array
		$day_date = strtotime("$start_time 00:00:00");
		$list_day_xx = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0) AND terminal = 1")->find();
		$list_day_xs = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 2")->find();
		$list_day_android = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 3")->find();
		$list_day_ios = M('user')->field('count(id) as count,create_time')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0)  AND terminal = 4")->find();
		//$data_name
		$data_name_raw = ["'web'.,.{$list_day_xx['count']}个", "'wap'.,.{$list_day_xs['count']}个", "'android'.,.{$list_day_android['count']}个", "'IOS'.,.{$list_day_ios['count']}个"];
		$data_name = json_encode($data_name_raw);
		$count_sum = (int) $list_day_xx['count'] + (int) $list_day_xs['count'] + (int) $list_day_android['count'] + (int) $list_day_ios['count'];
		$pie_data_array_raw = array($list_day_xx['count'] / $count_sum, $list_day_xs['count'] / $count_sum, $list_day_android['count'] / $count_sum, $list_day_ios['count'] / $count_sum);
		$pie_data_array = json_encode($pie_data_array_raw);
		$statistics_data = array(array('user_reg_date' => $start_time, 'user_reg_type' => 0, 'user_reg_count' => $count_sum)
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 1, 'user_reg_count' => $list_day_xx['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 2, 'user_reg_count' => $list_day_xs['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 3, 'user_reg_count' => $list_day_android['count'])
		    , array('user_reg_date' => $start_time, 'user_reg_type' => 4, 'user_reg_count' => $list_day_ios['count'])
		);
		$lists = $statistics_data;
	    } else {
		//昨天
		$lists = M('statistical_reg_yqqd')->where("user_reg_date = {$start_time_int}")->select();
	    }
	} else {
	    $lists = M("statistical_reg_yqqd")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
	}
	$list = array();
	foreach ($lists as $key => $val) {
	    $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
	    $list[$val['user_reg_date']]['user_reg_date'] = $val['user_reg_date'];
	    if ($val['user_reg_type'] == $lists[$key]['user_reg_type']) {
		$list[$val['user_reg_date']][$val['user_reg_type']] = $val['user_reg_count'];
	    }
	}
	$list['gross']['user_reg_date'] = "总计";
	krsort($list);
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();
	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $num, '时间')
			->setCellValue('B' . $num, "web(人)")
			->setCellValue('C' . $num, "wap(人)")
			->setCellValue('D' . $num, "Android(人)")
			->setCellValue('E' . $num, "ios(人)")
			->setCellValue('F' . $num, "合计(人)");
		$num = 2;
	    }
	    $objPHPExcel->setActiveSheetIndex(0)
		    ->setCellValue('A' . $num, $val['user_reg_date'])
		    ->setCellValue('B' . $num, $val['1'])
		    ->setCellValue('C' . $num, $val['2'])
		    ->setCellValue('D' . $num, $val['3'])
		    ->setCellValue('E' . $num, $val['4'])
		    ->setCellValue('F' . $num, $val['0']);


	    $num++;
	}
	//设置属性
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的邀请注册平台统计表";
	php_export_excel($objPHPExcel, $filename);
    }

//注册渠道  统计导出
    public function export_reg_qd($page = 1) {
	if (empty($_REQUEST['choise'])) {
	    $choise = "91,baidu,360,anzhuo,yingyongbao,huawei,xiaomi,anzhi,wandoujia,yingyonghui,Nduo,lianxiang,oppo,sogou,jifeng,fuyi,fensitong";
	} else {
	    $choise = implode(",", $_REQUEST['choise']);
	}
	$list_choise = explode(',', $choise);
	$this->assign('list_choise', $list_choise);

	//时间接收
	if ($_REQUEST['start_time'] && $_REQUEST['end_time']) {
	    $start_time = date('Ymd', strtotime($_REQUEST['start_time']));
	    $end_time = date('Ymd', strtotime($_REQUEST['end_time']));
	} else {
	    $start_time = date('Ymd', strtotime("-7 day"));
	    $end_time = date('Ymd', time());
	}
	if ($start_time == date('Ymd', time()) || $start_time == $end_time) {


	    //$series_name
	    $series_name_raw = '一天注册数';
	    $series_name = json_encode($series_name_raw);
	    if ($start_time == date('Ymd', time())) {
		//$pie_data_array
		$this->assign('type', 'bingtua');
		$day_date = strtotime("$start_time 00:00:00");
		$list_day = array();
		foreach ($list_choise as &$v) {
		    $res = M('user')->field('count(id) as count,create_time,search_channel')->where("create_time >= {$day_date} AND search_channel = '{$v}'")->find();
		    if (!$res['search_channel']) {
			$res['search_channel'] = $v;
		    }
		    $list_day[] = $res;
		}
		$count_sum = 0;
		$list_count = array();
		foreach ($list_day as $v) {

		    $count_sum = intval($v['count']) + $count_sum;
		    $list_count[] = $v['count'];
		}
		$pie_data_array_raw = array();
		foreach ($list_count as $v) {
		    $pie_data_array_raw[] = (int) $v / $count_sum;
		}
		$pie_data_array = json_encode($pie_data_array_raw);

		//$data_name
		$data_name_raw = array();
		$list_type = array();
		$list_count = array();
		foreach ($list_day as $v) {
		    $data_name_raw[] = "'{$v['search_channel']}'" . ',' . "{$v['count']}" . "个";
		    $list_type[] = $v['search_channel'];
		    $list_count[] = $v['count'];
		}
		$data_name = json_encode($data_name_raw);


		//表格数据

		$this->assign('start_time', $start_time);
		$this->assign('list_type', $list_type);
		$this->assign('list_count', $list_count);
	    } else {
		//昨天
		//$pie_data_array
		$this->assign('type', 'bingtub');
		$res = array();
		foreach ($list_choise as $v) {
		    $res[] = M('statistical_reg_qd')->where("user_reg_date = {$start_time}  AND user_reg_type = '{$v}'")->find();
		}
		foreach ($res as $v) {
		    $count_sum = $count_sum + (int) $v['user_reg_count'];
		}
		$pie_data_array_raw = array();
		foreach ($res as $v) {
		    $pie_data_array_raw[] = (int) $v['user_reg_count'] / $count_sum;
		}
		$pie_data_array = json_encode($pie_data_array_raw);
		//$data_name
		$data_name_raw = array();
		$list_type = array();
		$list_count = array();
		foreach ($res as $v) {
		    $data_name_raw[] = "'{$v['user_reg_type']}'" . ',' . "{$v['user_reg_count']}" . "个";
		    $list_type[] = $v['user_reg_type'];
		    $list_count[] = $v['user_reg_count'];
		}
		$data_name = json_encode($data_name_raw);

		//表格数据
		$this->assign('start_time', $start_time);
		$this->assign('list_type', $list_type);
		$this->assign('list_count', $list_count);
	    }
	} else {
	    //$xAxis_raw
	    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
	    $x = M('statistical_reg_qd')->where("$sql_x AND user_reg_type = 91")->select(); 
	    $arrX = array();
	    foreach ($x as $vx) {
		$arrX[] = $vx['user_reg_date'];
	    }
	    $xAxis_pot = json_encode($arrX);

	    //$data_name
	    $data_name_raw = $list_choise;
	    $data_name = json_encode($data_name_raw);


	    //$data_array
	    $arr_Y = array();
	    $all = array();
	    foreach ($list_choise as $v) {

		$arr_Y[] = M('statistical_reg_qd')->where("$sql_x AND user_reg_type = '{$v}'")->select();
	    }
	    foreach ($arr_Y as $key => $val) {

		foreach ($val as $k => $v) {
		    if ($v['user_reg_type'] == $list_choise[$key]) {
			$list_choise_new[$key][] = (int) $v['user_reg_count'];
		    }
		}
	    }
	    $data_array_raw = $list_choise_new;
	    $data_array = json_encode($data_array_raw);
	}
	//时间分配
	$this->assign('start_time', date('Y-m-d', strtotime("$start_time 00:00:00")));
	$this->assign('end_time', date('Y-m-d', strtotime("$end_time 00:00:00")));

	$list_conf = require_once APP_ROOT_PATH . "data_conf/MarketingChannels.php";

	$arrtmp = array();
	foreach ($arrX as $key => $val) {
	    $arrtmp['user_reg_date'] = $val;
	    foreach ($data_name_raw as $k => $v) {
		$arrtmp[$v] = $data_array_raw[$k][$key];
	    }

	    $list[] = $arrtmp;
	}
	$count = count($data_name_raw) + 1;
	$count_X = $this->get_arr($count);
	$count_XX = array();
	foreach ($count_X as $vl) {
	    $count_XX[] = $vl;
	}
	require_once APP_ROOT_PATH . "public/PHPExcel/PHPExcel.php";
	$objPHPExcel = new PHPExcel();

	$num = 1;
	foreach ($list as $key => $val) {
	    if ($num == 1) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '时间');
		foreach ($data_name_raw as $k => $v) {
		    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($count_XX[$k + 1] . $num, $v);
		}
		$num = 2;
	    }
	    $tmp_val = array();
	    foreach ($val as $kyy => $vlll) {
		$tmp_val[] = array('key' => $kyy, 'count' => $vlll);
	    }
	    foreach ($tmp_val as $ky => $vll) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($count_XX[$ky] . $num, $vll['count']);
	    }
	    $num++;
	}

	//设置属性
	foreach ($count_X as $kkk => $vvv) {
	    $objPHPExcel->getActiveSheet()->getColumnDimension("$vvv")->setWidth(10);
	}


	$rel = "A1" . ':' . $count_XX[count($data_name_raw)] . '1';
	$objPHPExcel->getActiveSheet()->getStyle($rel)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);

	$objPHPExcel->getActiveSheet()->getStyle($rel)->getFill()->getStartColor()->setARGB('FFFFD700');
	$filename = $start_time . '~' . $end_time . "的推广渠道统计表";
	//sdie;
	php_export_excel($objPHPExcel, $filename);
    }

    private function get_arr($num) {
	$arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	for ($i = 0; $i <= $num - 1; $i++) {
	    $new_arr[$i - 1] = $arr[floor($i / 26 - 1)] . $arr[$i % 26];
	}
	return $new_arr;
    }

}
