<?php

    class UserCouponAction extends CommonAction{
        public function third_coupon() {
            $type = M('car_coupon')->field('prize_type')->order('prize_type desc')->getField('prize_type');
            for ($i = 1; $i <= $type; $i++) {
//                $code[$i]['prize_desc'] = M('car_coupon')->where('prize_type= '.$i)->getField('prize_desc');
                $code[$i]['prize_name'] = M('car_coupon')->where('prize_type= '.$i)->getField('prize_name');
                $code[$i]['total'] = M('car_coupon')->where('prize_type= '.$i)->count();
                $code[$i]['use'] = M('car_coupon')->where('prize_type='.$i.' and status=1')->count();
                $code[$i]['new'] = $code[$i]['total']-$code[$i]['use'];
            }
            $sql_extent = "";
            if (trim($_REQUEST['real_name']) != '') {
                        $sql_extent .= " and u.real_name = '".trim($_REQUEST['real_name'])."'";
            }
            if (trim($_REQUEST['user_name']) != '') {
                $sql_extent .= " and u.user_name = '".trim($_REQUEST['user_name'])."'";
            }
            if (trim($_REQUEST['mobile']) != '') {
                $sql_extent .= " and u.mobile = '".trim($_REQUEST['mobile'])."'";
            }
            if (trim($_REQUEST['prize_name']) != '') {
                $sql_extent .= " and cc.prize_name = '".trim($_REQUEST['prize_name'])."'";
            }
            if (trim($_REQUEST['code']) != '') {
                $sql_extent .= " and cc.prize_code =".trim($_REQUEST['code']);
            }
            $sql_extent .= " order by cc.id"; 
            
            $sql_count = "select count(cc.id) from " .DB_PREFIX. "car_coupon cc left join " .DB_PREFIX. "user u on cc.user_id = u.id where cc.status = 1".$sql_extent;  
            //取得满足条件的记录数
            $count = $GLOBALS['db']->getOne($sql_count);
            if ($count > 0) {
                    //创建分页对象
                    if (! empty ( $_REQUEST ['listRows'] )) {
                            $listRows = $_REQUEST ['listRows'];
                    } else {
                            $listRows = '';
                    }
                    $p = new Page ( $count, $listRows );
                    //分页查询数据
                    $sql_lists = "select cc.id,u.id as user_id,cc.prize_desc,cc.prize_name,cc.prize_code,cc.create_time as get_time from " .DB_PREFIX. "car_coupon cc left join " .DB_PREFIX. "user u on cc.user_id = u.id where cc.status = 1".$sql_extent;      
                    //输出投标列表 分页参数
                    $page = intval($_REQUEST['p'])?intval($_REQUEST['p']):1;
                    $page_size = 30;
                    $limit = (($page - 1) * $page_size) . "," . $page_size;
                    
                    if($limit){
                        $sql_lists .=" limit ".$limit;
                    }
                    $lists = $GLOBALS['db']->getAll($sql_lists);
                    //分页数据
                    $rs_count = $count;
                    $page_all = ceil($rs_count / $page_size);
                    $this->assign("page_all", $page_all);
                    $this->assign("rs_count", $rs_count);
                    $this->assign("page", $page);
                    $this->assign("page_prev", $page - 1);
                    $this->assign("page_next", $page + 1);
                    $this->assign ( 'lists', $lists );
            }
            $this->assign('code', $code);
            $this->display();
            
        }
    }