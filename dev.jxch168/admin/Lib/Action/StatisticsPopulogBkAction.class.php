<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class StatisticsPopulogBkAction extends CommonAction{


        public function statistics_bksum(){
                //时间接收
        if($_REQUEST['start_time'] && $_REQUEST['end_time']){
            $start_time = date('Ymd',strtotime($_REQUEST['start_time']));
            $end_time = date('Ymd',strtotime($_REQUEST['end_time']));
        }else{
            $start_time = date('Ymd',strtotime("-7 day"));
            $end_time = date('Ymd',time());
        }

        //如果是一天的日期显示饼图（今天，昨天）
        if(  $start_time == date( 'Ymd',time() ) || $start_time == $end_time ){

                            $this->assign('type','bingtu');
                                    //今天
                                if($start_time == date('Ymd',time())){
                        //线上线下***************************************************
                                //$series_name
                                    $series_name_raw = '一天绑卡数';
                                    $series_name = json_encode($series_name_raw);
                                //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    $list_day_xx = D()->query("SELECT count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id > 0");
                                    $list_day_xs = D()->query("SELECT count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id = 0");
                            //dump("SELECT count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id > 0");


                                    $count_sum = (int)$list_day_xx[0]['count'] + (int)$list_day_xs[0]['count'];
                                    $pie_data_array_raw = array($list_day_xx[0]['count']/$count_sum,$list_day_xs[0]['count']/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);
                                //$data_name
                                    $data_name_raw = ["'线下'.,.{$list_day_xx[0]['count']}个","'线上'.,.{$list_day_xs[0]['count']}个"];
                                    $data_name = json_encode($data_name_raw);
                        //end线上线下***************************************************
                        //平台***************************************************
                                //$series_namea
                                    $series_name_rawa = '一天绑卡数';
                                    $series_namea = json_encode($series_name_rawa);
                                //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    $list_day_xxa = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 1")->find();
                                    $list_day_xsa = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 2")->find();
                                    $list_day_androida = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 3")->find();
                                    $list_day_iosa = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 4")->find();
                                    $count_suma = (int)$list_day_xxa['count'] + (int)$list_day_xsa['count']+(int)$list_day_androida['count'] + (int)$list_day_iosa['count'];
                                    $pie_data_array_rawa = array($list_day_xxa['count']/$count_suma,$list_day_xsa['count']/$count_suma,$list_day_androida['count']/$count_suma,$list_day_iosa['count']/$count_suma);
                                    $pie_data_arraya = json_encode($pie_data_array_rawa);

                                //$data_name
                                    $data_name_rawa = ["'web'.,.{$list_day_xxa['count']}个","'wap'.,.{$list_day_xsa['count']}个","'android'.,.{$list_day_androida['count']}个","'IOS'.,.{$list_day_iosa['count']}个"];
                                    $data_namea = json_encode($data_name_rawa);

                                    $this->assign('xAxis_pota',$xAxis_pota);
                                    $this->assign('yAxis_titlea',$yAxis_titlea);
                                    $this->assign('data_namea',$data_namea);
                                    $this->assign('data_arraya',$data_arraya);
                                    $this->assign('unita',$unita);
                                    $this->assign('series_namea',$series_namea);
                                    $this->assign('pie_data_arraya',$pie_data_arraya);


                        //end平台***************************************************
                                }else{
                                    //昨天
                        //线上线下***************************************************
                            //$series_name
                                    $series_name_raw = '一天绑卡数';
                                    $series_name = json_encode($series_name_raw);
                                    $list = $this->get_list_reg($start_time);
                            //$data_name
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 5){
                                            $a = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 6){
                                            $b = $v['user_reg_count'];
                                        }
                                    }
                                    $data_name_raw = ["'线下'.,.{$a}个","'线上'.,.{$b}个"];
                                    $data_name = json_encode($data_name_raw);
                            //$pie_data_array
                                    $arr_a = array();
                                    $arr_b = array();
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 5)
                                            $arr_a[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 6)
                                            $arr_b[] = $v['user_reg_count'];
                                    }
                                    $count_sum = intval($arr_a[0])+intval($arr_b[0]);
                                    $pie_data_array_raw = array($arr_a[0]/$count_sum,$arr_b[0]/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);

                        //线上线下***************************************************
                        //平台***************************************************
                            //$series_name
                                    $series_name_rawa = '一天绑卡数';
                                    $series_namea = json_encode($series_name_rawa);

                            //$data_name
                                    $lista = $this->get_list_reg_pt($start_time);//dump($list);
                                    foreach($lista as $v){
                                        if($v['user_reg_type'] == 1){
                                            $aa = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 2){
                                            $bb = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 3){
                                            $cc = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 4){
                                            $dd = $v['user_reg_count'];
                                        }
                                    }
                                    $data_name_rawa = ["'web'.,.{$aa}个","'wap'.,.{$bb}个","'android'.,.{$cc}个","'IOS'.,.{$dd}个"];
                                    $data_namea = json_encode($data_name_rawa);
                            //$pie_data_array
                                    $arr_aa = array();
                                    $arr_bb = array();
                                    $arr_cc = array();
                                    $arr_dd = array();
                                    foreach($lista as $v){
                                        if($v['user_reg_type'] == 1)
                                            $arr_aa[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 2)
                                            $arr_bb[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 3)
                                            $arr_cc[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 4)
                                            $arr_dd[] = $v['user_reg_count'];
                                    }
                                    $count_suma = (intval($arr_aa[0])+intval($arr_bb[0])+intval($arr_cc[0])+intval($arr_dd[0]))/100;

                                    $pie_data_array_rawa = array($arr_aa[0]/$count_suma,$arr_bb[0]/$count_suma,$arr_cc[0]/$count_suma,$arr_dd[0]/$count_suma);
                                    $pie_data_arraya = json_encode($pie_data_array_rawa);//dump($pie_data_array);

                                    $this->assign('xAxis_pota',$xAxis_pota);
                                    $this->assign('yAxis_titlea',$yAxis_titlea);
                                    $this->assign('data_namea',$data_namea);
                                    $this->assign('data_arraya',$data_arraya);
                                    $this->assign('unita',$unita);
                                    $this->assign('series_namea',$series_namea);
                                    $this->assign('pie_data_arraya',$pie_data_arraya);
                        //end平台***************************************************
                                }

        }else{
                    $this->assign('type','xiantu');
               //$xAxis_raw
                    $sql_x = "user_reg_date >= {$start_time} AND user_reg_date <= {$end_time}";
                    $x = M('statistical_bk_bkpt')->where("$sql_x AND user_reg_type = 0")->select();//dump($x);
                    $arrX = array();
                    foreach($x as $vx){
                        $arrX[] = $vx['user_reg_date'];
                    }
                    $xAxis_pot = json_encode($arrX);
                //$yAxis_title
                    $yAxis_title_raw = '单位（人）';
                    $yAxis_title = json_encode($yAxis_title_raw);

                //$data_name
                    $data_name_raw = ['绑卡总数'];
                    $data_name = json_encode($data_name_raw);

                //$data_array
                    $list = M('statistical_bk_bkpt')->where("$sql_x AND user_reg_type = 0")->order("user_reg_date desc")->select();//dump($list);
                    $arr_a = array();
                    $aa = 0;
                    foreach($list as $v){
                        $aa = $aa  + $v['user_reg_count'];
                         $arr_a[] = (int)$v['user_reg_count'];
                    }
                    $list['gross']['user_reg_date']="总计";//dump($list);
                    $list['gross']['user_reg_count']=$aa;
		    krsort($arr_a);
                    $arrY = array(array_values($arr_a));//dump($arrY);
                    $data_array = json_encode($arrY);


                    //$unit
                    $unit_raw = '人';
                    $unit = json_encode($unit_raw);


                            $series_name_raw = 'testname';
                            $series_name = json_encode($series_name_raw);
                            $pie_data_array_raw = [20,25,40,15];
                            $pie_data_array = json_encode($pie_data_array_raw);
        }
                            $this->assign('xAxis_pot',$xAxis_pot);
                            $this->assign('yAxis_title',$yAxis_title);
                            $this->assign('data_name',$data_name);
                            $this->assign('data_array',$data_array);
                            $this->assign('unit',$unit);
                            $this->assign('series_name',$series_name);
                            $this->assign('pie_data_array',$pie_data_array);

                //时间分配
                $this->assign('start_time',date('Y-m-d',strtotime("$start_time 00:00:00")));
                $this->assign('end_time',date('Y-m-d',strtotime("$end_time 00:00:00")));


        //表格数据
            $this->assign('list',$list);


        $this->display();



        }



        //绑卡来源 线上线下
        public function statistics_bkly(){

            //时间接收
               if($_REQUEST['start_time'] && $_REQUEST['end_time']){
                   $start_time = date('Ymd',strtotime($_REQUEST['start_time']));
                   $end_time = date('Ymd',strtotime($_REQUEST['end_time']));
               }else{
                   $start_time = date('Ymd',strtotime("-7 day"));
                   $end_time = date('Ymd',time());
               }
       //如果是一天的日期显示饼图（今天，昨天）
               if(  $start_time == date( 'Ymd',time() ) || $start_time == $end_time ){
                                          //$list = $this->get_list_reg($start_time);//dump($list);exit;
                            //$series_name
                                    $series_name_raw = '一天绑卡数';
                                    $series_name = json_encode($series_name_raw);
                                    //今天
                                if($start_time == date('Ymd',time())){
                                    $this->assign('type','bingtua');
                            //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    //$list_day_xx = M('user_bank')->field('count(id) as count,binling_time')->where("binling_time >= {$day_date} AND admin_id > 0")->find();
                                    //$list_day_xs = M('user_bank')->field('count(id) as count,binling_time')->where("binling_time >= {$day_date} AND admin_id = 0")->find();
                                    $list_day_xx = D()->query("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id > 0");
                                    $list_day_xs = D()->query("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id = 0");
                            //$data_name
//dump("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binling_time >= {$day_date} AND  b.admin_id > 0");

                                    $data_name_raw = ["'线下绑卡'.,.{$list_day_xx[0]['count']}个","'线上绑卡'.,.{$list_day_xs[0]['count']}个"];
                                    $data_name = json_encode($data_name_raw);
                                    $count_sum = (int)$list_day_xx[0]['count'] + (int)$list_day_xs[0]['count'];
                                    $pie_data_array_raw = array($list_day_xx[0]['count']/$count_sum,$list_day_xs[0]['count']/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);

                                    //表格
                                    $list_suma = $list_day_xx[0]['count']+$list_day_xs[0]['count'];
                                    $xianxia = $list_day_xx[0]['count'];
                                    $xianshang = $list_day_xs[0]['count'];

                                    $this->assign('user_date',$start_time);//dump($list_suma);
                                    $this->assign('list_suma',$list_suma);
                                    $this->assign('xianxia',$xianxia);
                                    $this->assign('xianshang',$xianshang);

                    //新表格数据

        $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>5,'user_reg_count'=>$xianxia)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>6,'user_reg_count'=>$xianshang)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$list_suma)
                            );

        //$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
        $client_list=array();
            foreach($statistics_data as $key=>$val){
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==0){
                    $client_list[$val['user_reg_date']]['all']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==5){
                    $client_list[$val['user_reg_date']]['dowm']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==6){
                    $client_list[$val['user_reg_date']]['top']=$val['user_reg_count'];
                }
            }
        $this->assign('client_list',$client_list);




                                }else{
                                    $this->assign('type','bingtub');
                                    //昨天
                                    $list = $this->get_list_reg($start_time);
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 5){
                                            $a = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 6){
                                            $b = $v['user_reg_count'];
                                        }
                                    }
                            //$data_name
                                    $data_name_raw = ["'线下绑卡'.,.{$a}个","'线上绑卡'.,.{$b}个"];
                                    $data_name = json_encode($data_name_raw);
                            //$pie_data_array
                                    $arr_a = array();
                                    $arr_b = array();
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 5)
                                            $arr_a[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 6)
                                            $arr_b[] = $v['user_reg_count'];
                                    }
                                    $count_sum = intval($arr_a[0])+intval($arr_b[0]);
                                    $pie_data_array_raw = array($arr_a[0]/$count_sum,$arr_b[0]/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);
                                    //表格
                                    $list_suma = M('statistical_bk_bkly')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
                                    $this->assign('user_date',$start_time);
                                    $this->assign('list_suma',$list_suma);
                                    $this->assign('list_xianxia',$arr_a);
                                    $this->assign('list_xianshang',$arr_b);

                //新表格数据

        $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>5,'user_reg_count'=>$arr_a[0])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>6,'user_reg_count'=>$arr_b[0])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$list_suma['user_reg_count'])
                            );

        //$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
        $client_list=array();
            foreach($statistics_data as $key=>$val){
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==0){
                    $client_list[$val['user_reg_date']]['all']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==5){
                    $client_list[$val['user_reg_date']]['dowm']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==6){
                    $client_list[$val['user_reg_date']]['top']=$val['user_reg_count'];
                }
            }
        $this->assign('client_list',$client_list);



                                }





               }else{
                           $this->assign('type','xiantu');
                       //$xAxis_raw
                           $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
                           $x = M('statistical_bk_bkly')->where("$sql_x AND user_reg_type = 0")->select();//dump($x);
                           $arrX = array();
                           foreach($x as $vx){
                               $arrX[] = $vx['user_reg_date'];
                           }
                           $xAxis_pot = json_encode($arrX);


                       //$yAxis_title
                           $yAxis_title_raw = '单位（人）';
                           $yAxis_title = json_encode($yAxis_title_raw);


                       //$data_name
                           $data_name_raw = ['线下绑卡','线上绑卡','合计'];
                           $data_name = json_encode($data_name_raw);


                       //$data_array
                           $list = $this->get_list_reg($start_time,$end_time);//dump($list);
                           $arr_a = array();
                           $arr_b = array();
                           $all = array();
                           foreach($list as $v){
                               if($v['user_reg_type'] == 5){
                                   $arr_a[] = (int)$v['user_reg_count'];
                               }
                               if($v['user_reg_type'] == 6){
                                   $arr_b[] = (int)$v['user_reg_count'];
                               }
                               if($v['user_reg_type'] == 0){
                                   $all[] = (int)$v['user_reg_count'];
                               }
                           }
                           $arrY = array($arr_a,$arr_b,$all);//dump($arrY);
                           $data_array = json_encode($arrY);


                       //$unit
                           $unit_raw = '人';
                           $unit = json_encode($unit_raw);


                           $series_name_raw = 'testname';
                           $series_name = json_encode($series_name_raw);
                           $pie_data_array_raw = [20,25,40,15];
                           $pie_data_array = json_encode($pie_data_array_raw);

            //新表格数据
        $statistics_data = M('statistical_bk_bkly')->where($sql_x)->select();
        $client_list=array();
           foreach($statistics_data as $key=>$val){
                $client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==$statistics_data[$key]['user_reg_type']){
                    $client_list[$val['user_reg_date']][$val['user_reg_type']]=$val['user_reg_count'];

                }
            }
            $client_list['gross']['user_reg_date']="总计";
            krsort($client_list);
        $this->assign('client_list',$client_list);



               }

               $this->assign('xAxis_pot',$xAxis_pot);
               $this->assign('yAxis_title',$yAxis_title);
               $this->assign('data_name',$data_name);
               $this->assign('data_array',$data_array);
               $this->assign('unit',$unit);
               $this->assign('series_name',$series_name);
               $this->assign('pie_data_array',$pie_data_array);

           //时间分配
               //dump(to_date($start_time,'Y-m-d'));
               $this->assign('start_time',date('Y-m-d',strtotime("$start_time 00:00:00")));
               $this->assign('end_time',date('Y-m-d',strtotime("$end_time 00:00:00")));

           //表格数据 多天
               $list_sum = M('statistical_bk_bkly')->where("$sql_x AND user_reg_type = 0")->select();

               $this->assign('list_sum',$list_sum);
               $this->assign('list_date',$arrX);
               $this->assign('list_xianxia',$arr_a);
               $this->assign('list_xianshang',$arr_b);





               $this->display();

        }



    public function statistics_bkpt(){
                    //时间接收
        if($_REQUEST['start_time'] && $_REQUEST['end_time']){
            $start_time = date('Ymd',strtotime($_REQUEST['start_time']));
            $end_time = date('Ymd',strtotime($_REQUEST['end_time']));
        }else{
            $start_time = date('Ymd',strtotime("-7 day"));
            $end_time = date('Ymd',time());
        }

        //如果是一天的日期显示饼图（今天，昨天）
        if(  $start_time == date( 'Ymd',time() ) || $start_time == $end_time ){
                                $this->assign('type','bingtu');

                                    //$list = $this->get_list_reg_pt($start_time);//dump($list);
                            //$series_name
                                    $series_name_raw = '一天绑卡数';
                                    $series_name = json_encode($series_name_raw);

                                    //今天
                                if(  $start_time == date('Ymd',time() )  ){

                            //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    $list_day_xx = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 1")->find();
                                    $list_day_xs = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 2")->find();
                                    $list_day_android = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 3")->find();
                                    $list_day_ios = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 4")->find();
                            //$data_name
                                    $data_name_raw = ["'web'.,.{$list_day_xx['count']}个","'wap'.,.{$list_day_xs['count']}个","'android'.,.{$list_day_android['count']}个","'IOS'.,.{$list_day_ios['count']}个"];
                                    $data_name = json_encode($data_name_raw);
                                    $count_sum = (int)$list_day_xx['count'] + (int)$list_day_xs['count']+(int)$list_day_android['count'] + (int)$list_day_ios['count'];
                                    $pie_data_array_raw = array($list_day_xx['count']/$count_sum,$list_day_xs['count']/$count_sum,$list_day_android['count']/$count_sum,$list_day_ios['count']/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);

                            //表格数据

                                    $this->assign('list_datea',$start_time);
                                    $this->assign('a',$list_day_xx['count']);
                                    $this->assign('b',$list_day_xs['count']);
                                    $this->assign('c',$list_day_android['count']);
                                    $this->assign('d',$list_day_ios['count']);
                                    $this->assign('e',$count_sum);


                    //新表格数据

        $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$count_sum)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>1,'user_reg_count'=>$list_day_xx['count'])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>2,'user_reg_count'=>$list_day_xs['count'])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>3,'user_reg_count'=>$list_day_android['count'])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>4,'user_reg_count'=>$list_day_ios['count'])
                            );

        //$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
        $client_list=array();
            foreach($statistics_data as $key=>$val){
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==0){
                    $client_list[$val['user_reg_date']]['all']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==1){
                    $client_list[$val['user_reg_date']]['web']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==2){
                    $client_list[$val['user_reg_date']]['wap']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==3){
                    $client_list[$val['user_reg_date']]['android']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==4){
                    $client_list[$val['user_reg_date']]['ios']=$val['user_reg_count'];
                }
            }
        $this->assign('client_list',$client_list);



                                }else{
                                    //$this->assign('type','bingtub');
                                    //昨天
                                    $list = $this->get_list_reg_pt_yqqd($start_time);//dump($list);
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 1){
                                            $a = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 2){
                                            $b = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 3){
                                            $c = $v['user_reg_count'];
                                        }
                                        if($v['user_reg_type'] == 4){
                                            $d = $v['user_reg_count'];
                                        }
                                    }
                            //$data_name
                                    $data_name_raw = ["'web'.,.{$a}个","'wap'.,.{$b}个","'android'.,.{$c}个","'IOS'.,.{$d}个"];
                                    $data_name = json_encode($data_name_raw);
                            //$pie_data_array
                                    $arr_a = array();
                                    $arr_b = array();
                                    $arr_c = array();
                                    $arr_d = array();
                                    foreach($list as $v){
                                        if($v['user_reg_type'] == 1)
                                            $arr_a[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 2)
                                            $arr_b[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 3)
                                            $arr_c[] = $v['user_reg_count'];
                                        if($v['user_reg_type'] == 4)
                                            $arr_d[] = $v['user_reg_count'];
                                    }
                                    $count_sum = (intval($arr_a[0])+intval($arr_b[0])+intval($arr_c[0])+intval($arr_d[0]))/100;

                                    $pie_data_array_raw = array($arr_a[0]/$count_sum,$arr_b[0]/$count_sum,$arr_c[0]/$count_sum,$arr_d[0]/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);//dump($pie_data_array);

                            //表格数据
                            //dump($start_time);
                                    $list_sum = M('statistical_bk_bkpt')->where("user_reg_date = {$start_time} AND user_reg_type = 0")->find();
                                    $this->assign('list_datea',$start_time);
                                    $this->assign('a',$a);
                                    $this->assign('b',$b);
                                    $this->assign('c',$c);
                                    $this->assign('d',$d);
                                    $this->assign('e',$list_sum['user_reg_count']);

                //新表格数据

        $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$list_sum['user_reg_count'])
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>1,'user_reg_count'=>$a)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>2,'user_reg_count'=>$b)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>3,'user_reg_count'=>$c)
                                ,array('user_reg_date'=>$start_time,'user_reg_type'=>4,'user_reg_count'=>$d)
                            );

        //$statistics_data = M('statistical_user_reg')->where($sql_x)->select();
        $client_list=array();
            foreach($statistics_data as $key=>$val){
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==0){
                    $client_list[$val['user_reg_date']]['all']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==1){
                    $client_list[$val['user_reg_date']]['web']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==2){
                    $client_list[$val['user_reg_date']]['wap']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==3){
                    $client_list[$val['user_reg_date']]['android']=$val['user_reg_count'];
                }
                if($val['user_reg_type']==4){
                    $client_list[$val['user_reg_date']]['ios']=$val['user_reg_count'];
                }
            }
        $this->assign('client_list',$client_list);




                                }



        }else{
                        $this->assign('type','xiantu');

                        //$xAxis_raw
                            $sql_x = "user_reg_date >= {$start_time} AND user_reg_date < {$end_time}";
                            $x = M('statistical_bk_bkpt')->where("$sql_x AND user_reg_type = 0")->select();//dump($x);
                            $arrX = array();
                            foreach($x as $vx){
                                $arrX[] = $vx['user_reg_date'];
                            }
                            $xAxis_pot = json_encode($arrX);


                        //$yAxis_title
                            $yAxis_title_raw = '单位（人）';
                            $yAxis_title = json_encode($yAxis_title_raw);


                        //$data_name
                            $data_name_raw = ['web','wap','android','IOS','合计'];
                            $data_name = json_encode($data_name_raw);


                        //$data_array
                            $list = $this->get_list_reg_pt_yqqd($start_time,$end_time);
                            $arr_a = array();
                            $arr_b = array();
                            $arr_c = array();
                            $arr_d = array();
                            $all = array();
                            foreach($list as $v){
                                if($v['user_reg_type'] == 1){
                                    $arr_a[] = (int)$v['user_reg_count'];
                                }
                                if($v['user_reg_type'] == 2){
                                    $arr_b[] = (int)$v['user_reg_count'];
                                }
                                if($v['user_reg_type'] == 3){
                                    $arr_c[] = (int)$v['user_reg_count'];
                                }
                                if($v['user_reg_type'] == 4){
                                    $arr_d[] = (int)$v['user_reg_count'];
                                }
                                if($v['user_reg_type'] == 0){
                                    $all[] = (int)$v['user_reg_count'];
                                }
                            }
                            $arrY = array($arr_a,$arr_b,$arr_c,$arr_d,$all);//dump($arrY);
                            $data_array = json_encode($arrY);


                        //$unit
                            $unit_raw = '人';
                            $unit = json_encode($unit_raw);

                            $series_name_raw = 'testname';
                            $series_name = json_encode($series_name_raw);
                            $pie_data_array_raw = [20,25,40,15];
                            $pie_data_array = json_encode($pie_data_array_raw);

                 //新表格数据
        $statistics_data = M('statistical_bk_bkpt')->where($sql_x)->select();
        $client_list=array();
           foreach($statistics_data as $key=>$val){
                $client_list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
                $client_list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==$statistics_data[$key]['user_reg_type']){
                    $client_list[$val['user_reg_date']][$val['user_reg_type']]=$val['user_reg_count'];

                }
            }//dump($client_list);
            $client_list['gross']['user_reg_date']="总计";
            krsort($client_list);
        $this->assign('client_list',$client_list);



            }



                            $this->assign('xAxis_pot',$xAxis_pot);
                            $this->assign('yAxis_title',$yAxis_title);
                            $this->assign('data_name',$data_name);
                            $this->assign('data_array',$data_array);
                            $this->assign('unit',$unit);
                            $this->assign('series_name',$series_name);
                            $this->assign('pie_data_array',$pie_data_array);


        //表格数据 多天
        $list_table = M('statistical_bk_bkpt')->where("$sql_x AND user_reg_type = 0")->select();//dump($list_table);
        //$list_tb = M('statistical_reg_from')->where("user_reg_date >= {$start_time} AND user_reg_date < {$end_time} AND user_reg_type = 0")->select();
        $this->assign('list_table',$list_table);


        //时间分配
        //dump(to_date($start_time,'Y-m-d'));
        $this->assign('start_time',date('Y-m-d',strtotime("$start_time 00:00:00")));
        $this->assign('end_time',date('Y-m-d',strtotime("$end_time 00:00:00")));

    //表格数据 多天
        $list_sum = M('statistical_bk_bkpt')->where("$sql_x AND user_reg_type = 0")->select();
        $this->assign('list_sum',$list_sum);
        $this->assign('list_date',$arrX);
        $this->assign('list_web',$arr_a);
        $this->assign('list_wap',$arr_b);
        $this->assign('list_android',$arr_c);
        $this->assign('list_ios',$arr_d);




        $this->display();
    }





            //线上线下统计，$date_star开始时间 $date_end结束时间  两个值都有就是去范围内的值
    private function get_list_reg($date_star,$date_end=''){
//                $date_star = str_replace('-', $date_star);
//                $date_end = str_replace('-', $date_end);
        //取一段时间范围内的绑卡用户数据
        if($date_star && $date_end){
            $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
            //取线上线下绑卡数据
            $list = M('statistical_bk_bkly')->where($sql)->select();
        }else{
            //判断是否是昨天
            if($date_star != date('Ymd',time() )){
                    $list = M('statistical_bk_bkly')->where("user_reg_date = {$date_star}")->select();
            }else{
                    //取当天的用户绑卡总数据
                    $day_date = strtotime("$date_star 00:00:00");
                    $list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
                    //取当天的用户绑卡线上线下数据
                    for($i=0;$i<2;$i++){
                        $sql_num = " =$i";
                        $list[] = M('user')->field('count(id) as count,admin_id')->where("create_time >= {$day_date} AND admin_id $sql_num")->find();
                    }
            }

        }
        //return $list_day;
        return $list;
        //dump($list);

    }




    //绑卡 取多个平台（ios，安卓，web，wap）数据
    private function get_list_reg_pt_yqqd($date_star,$date_end=''){
        //取一段时间范围内的绑卡用户数据
        if($date_star && $date_end){
            $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
            //取线上线下绑卡数据
            $list = M('statistical_bk_bkpt')->where($sql)->select();
        }else{
            //判断日期是否是昨天
            if($date_star != date('Ymd',time() )){
                    $list = M('statistical_bk_bkpt')->where("user_reg_date = {$date_star}")->select();
            }else{
                    //取当天的用户绑卡 总数据
                    $day_date = strtotime("$date_star 00:00:00");
                    $list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
                    //取当天的用户绑卡各平台数据（web，wap，ios,安卓）
                    for($i=1;$i<5;$i++){
                        $list[] = M('user')->field('count(id) as count,terminal')->where("create_time >= {$day_date} AND (admin_id <> 0 OR pid <> 0) AND terminal = {$i}")->find();
                        if(!$list[$i-1]['terminal']){
                            $list[$i-1]['terminal'] = $i;
                            $list[$i-1]['count'] = 0;
                        }
                    }
            }

//dump($list);
        }

        return $list;
    }





//取多个平台（ios，安卓，web，wap）数据
    private function get_list_reg_pt($date_star,$date_end=''){
        //取一段时间范围内的绑卡用户数据
        if($date_star && $date_end){
            $sql = "user_reg_date >= {$date_star} AND user_reg_date < {$date_end}";
            //取线上线下绑卡数据
            $list = M('statistical_bk_bkpt')->where($sql)->select();
        }else{
            //判断日期是否是昨天
            if($date_star != date('Ymd',time() )){
                    $list = M('statistical_bk_bkpt')->where("user_reg_date = {$date_star}")->select();
            }else{
                    //取当天的用户绑卡 总数据
                    $day_date = strtotime("$date_star 00:00:00");
                    $list_day = M('user')->field('count(id) as count')->where("create_time >= {$day_date}")->find();
                    //取当天的用户绑卡各平台数据（web，wap，ios,安卓）
                    for($i=1;$i<5;$i++){
                        $list[] = M('user')->field('count(id) as count,terminal')->where("create_time >= {$day_date} AND terminal = {$i}")->find();
                        if(!$list[$i-1]['terminal']){
                            $list[$i-1]['terminal'] = $i;
                            $list[$i-1]['count'] = 0;
                        }
                    }
            }

//dump($list);
        }

        return $list;
    }











    //excel 导出**********************************************************************************************************************


    //绑卡总计 统计导出
    public function export_bk_bksum($page = 1) {
        //从url中获取开始时间，
        $start_time = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {



        } else {
            $lists = M("statistical_bk_bkly")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
        }
        $list=array();
            foreach($lists as $key=>$val){
                $list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==0){
                    $list[$val['user_reg_date']]['all']=$val['user_reg_count'];
                }
            }
             require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num=1;
            foreach($list as $key=>$val){
                if($num==1){
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, '时间')
                          ->setCellValue('B'.$num, "合计(人)");
                $num=2;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, $val['user_reg_date'])
                          ->setCellValue('B'.$num, $val['all']);


                $num++;
            }
            //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:B1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename =  $start_time.'~'.$end_time. "的绑卡总计统计表";
        php_export_excel($objPHPExcel,$filename);
    }






    //绑卡来源 统计导出
    public function export_bk_bkly($page = 1) {
        //从url中获取开始时间，
        $start_time = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
                        if(  $start_time_int == date('Ymd',time() )  ){
                         //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    //$list_day_xx = M('user_bank')->field('count(id) as count,binling_time')->where("binling_time >= {$day_date} AND admin_id > 0")->find();
                                    //$list_day_xs = M('user_bank')->field('count(id) as count,binling_time')->where("binling_time >= {$day_date} AND admin_id = 0")->find();
                                    $list_day_xx = D()->query("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id > 0");
                                    $list_day_xs = D()->query("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binding_time >= {$day_date} AND  b.admin_id = 0");
                            //$data_name
//dump("SELECT a.*,b.*,count(a.id) as count FROM ".DB_PREFIX."user_bank a LEFT JOIN ".DB_PREFIX."user b ON a.user_id = b.id WHERE a.binling_time >= {$day_date} AND  b.admin_id > 0");

                                    $data_name_raw = ["'线下绑卡'.,.{$list_day_xx[0]['count']}个","'线上绑卡'.,.{$list_day_xs[0]['count']}个"];
                                    $data_name = json_encode($data_name_raw);
                                    $count_sum = (int)$list_day_xx[0]['count'] + (int)$list_day_xs[0]['count'];
                                    $pie_data_array_raw = array($list_day_xx[0]['count']/$count_sum,$list_day_xs[0]['count']/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);

                                    //表格
                                    $list_suma = $list_day_xx[0]['count']+$list_day_xs[0]['count'];
                                    $xianxia = $list_day_xx[0]['count'];
                                    $xianshang = $list_day_xs[0]['count'];

                    //新表格数据

                                    $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>5,'user_reg_count'=>$xianxia)
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>6,'user_reg_count'=>$xianshang)
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$list_suma)
                                                        );
                                    $lists = $statistics_data;
                     }else{
                         //昨天
                         $lists = M('statistical_bk_bkly')->where("user_reg_date = {$start_time_int}")->select();
                     }

        } else {
            $lists = M("statistical_bk_bkly")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
        }
        $list=array();
            foreach($lists as $key=>$val){
                $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
                $list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==$lists[$key]['user_reg_type']){
                    $list[$val['user_reg_date']][$val['user_reg_type']]=$val['user_reg_count'];

                }
            }
            $list['gross']['user_reg_date']="总计";
            krsort($list);
             require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num=1;
            foreach($list as $key=>$val){
                if($num==1){
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, '时间')
                          ->setCellValue('B'.$num, "线下绑卡(人)")
                          ->setCellValue('C'.$num, "线上绑卡(人)")
                          ->setCellValue('D'.$num, "合计(人)");
                $num=2;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, $val['user_reg_date'])
                          ->setCellValue('B'.$num, $val['5'])
                          ->setCellValue('C'.$num, $val['6'])
                          ->setCellValue('D'.$num, $val['0']);


                $num++;
            }
            //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:D1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename =  $start_time.'~'.$end_time. "的绑卡来源统计表";
        php_export_excel($objPHPExcel,$filename);
    }







    //绑卡平台 统计导出
    public function export_bk_bkpt($page = 1) {
        //从url中获取开始时间，
        $start_time = $_REQUEST["start_time"];
        //从url中获取结束时间
        $end_time = $_REQUEST["end_time"];
        $start_time_int = str_replace("-", '', $start_time); //将开始时间转换成int类型
        $end_time_int = str_replace("-", '', $end_time); //将结束时间转换成int类型
        //当天的数据或者昨天的数据
        if ($start_time == $end_time) {
                            if(  $start_time_int == date('Ymd',time() )  ){
                         //$pie_data_array
                                    $day_date = strtotime("$start_time 00:00:00");
                                    $list_day_xx = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 1")->find();
                                    $list_day_xs = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 2")->find();
                                    $list_day_android = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 3")->find();
                                    $list_day_ios = M('user_bank')->field('count(id) as count,binding_time')->where("binding_time >= {$day_date} AND binding_source = 4")->find();
                            //$data_name
                                    $data_name_raw = ["'web'.,.{$list_day_xx['count']}个","'wap'.,.{$list_day_xs['count']}个","'android'.,.{$list_day_android['count']}个","'IOS'.,.{$list_day_ios['count']}个"];
                                    $data_name = json_encode($data_name_raw);
                                    $count_sum = (int)$list_day_xx['count'] + (int)$list_day_xs['count']+(int)$list_day_android['count'] + (int)$list_day_ios['count'];
                                    $pie_data_array_raw = array($list_day_xx['count']/$count_sum,$list_day_xs['count']/$count_sum,$list_day_android['count']/$count_sum,$list_day_ios['count']/$count_sum);
                                    $pie_data_array = json_encode($pie_data_array_raw);

                            //表格数据

                                    $this->assign('list_datea',$start_time);
                                    $this->assign('a',$list_day_xx['count']);
                                    $this->assign('b',$list_day_xs['count']);
                                    $this->assign('c',$list_day_android['count']);
                                    $this->assign('d',$list_day_ios['count']);
                                    $this->assign('e',$count_sum);


                    //新表格数据

                                    $statistics_data = array(array('user_reg_date'=>$start_time,'user_reg_type'=>0,'user_reg_count'=>$count_sum)
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>1,'user_reg_count'=>$list_day_xx['count'])
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>2,'user_reg_count'=>$list_day_xs['count'])
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>3,'user_reg_count'=>$list_day_android['count'])
                                                            ,array('user_reg_date'=>$start_time,'user_reg_type'=>4,'user_reg_count'=>$list_day_ios['count'])
                                                        );
                                    $lists = $statistics_data;
                     }else{
                         //昨天
                         $lists = M('statistical_bk_bkpt')->where("user_reg_date = {$start_time_int}")->select();
                     }

        } else {
            $lists = M("statistical_bk_bkpt")->where("user_reg_date >= $start_time_int and user_reg_date < $end_time_int")->findAll();
        }
        $list=array();
            foreach($lists as $key=>$val){
                $list['gross'][$val['user_reg_type']]+=$val['user_reg_count'];
                $list[$val['user_reg_date']]['user_reg_date']=$val['user_reg_date'];
                if($val['user_reg_type']==$lists[$key]['user_reg_type']){
                    $list[$val['user_reg_date']][$val['user_reg_type']]=$val['user_reg_count'];

                }
            }
            $list['gross']['user_reg_date']="总计";
            krsort($list);
             require_once APP_ROOT_PATH."public/PHPExcel/PHPExcel.php";
            $objPHPExcel = new PHPExcel();
            $num=1;
            foreach($list as $key=>$val){
                if($num==1){
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, '时间')
                          ->setCellValue('B'.$num, "web(人)")
                          ->setCellValue('C'.$num, "wap(人)")
                          ->setCellValue('D'.$num, "Android(人)")
                          ->setCellValue('E'.$num, "ios(人)")
                          ->setCellValue('F'.$num, "合计(人)");
                $num=2;
                }
                $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('A'.$num, $val['user_reg_date'])
                          ->setCellValue('B'.$num, $val['1'])
                          ->setCellValue('C'.$num, $val['2'])
                          ->setCellValue('D'.$num, $val['3'])
                          ->setCellValue('E'.$num, $val['4'])
                          ->setCellValue('F'.$num, $val['0']);


                $num++;
            }
            //设置属性
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:F1')->getFill()->getStartColor()->setARGB('FFFFD700');
        $filename =  $start_time.'~'.$end_time. "的绑卡统计表";
        php_export_excel($objPHPExcel,$filename);
    }





}