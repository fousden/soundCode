<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行 客户管理模块 充值单业务逻辑相关处理类
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class PaymentNoticeModel extends CommonModel {

        protected $tableName = 'payment_notice';

        //自动验证
        protected $_validate = array(
			  // array("username","require","用户名必须填写!")
		);

        //自动完成
        protected $_auto=array(
                       // array('reg_time','time',1,'function'), //注册时间
        );
        //获取充值列表
       public function getInchargeList($map,$start_time, $final_time, $sortBy = '', $asc = false){


            //排序字段 默认为主键名
            if (isset ( $_REQUEST ['_order'] )) {
                    $order = $_REQUEST ['_order'];
            } else {
                    $order = ! empty ( $sortBy ) ? $sortBy : $this->getPk();
            }
            //排序方式默认按照倒序排列
            //接受 sost参数 0 表示倒序 非0都 表示正序
            if (isset ( $_REQUEST ['_sort'] )) {
                    $sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
            } else {
                    $sort = $asc ? 'asc' : 'desc';
            }
            //异常条件 异常充值订单集合
            $map['create_time'] = array(array('egt',strtotime($start_time)),array('elt',strtotime($final_time)));
            $map['is_paid'] = 0;
            $incharge_list = $this->where($map)->order("create_time DESC")->findAll();

            $ab_incharge_map = $this->getAbnormalInchargeList($incharge_list,$start_time, $final_time);
            $map['notice_sn'] = array("in",$ab_incharge_map);
            //取得满足条件的记录数
            $count = $this->where ( $map )->count ( 'id' );

            if ($count > 0) {
                    //创建分页对象
                    if (! empty ( $_REQUEST ['listRows'] )) {
                            $listRows = $_REQUEST ['listRows'];
                    } else {
                            $listRows = '';
                    }
                    $p = new Page ( $count, $listRows );
                    //分页查询数据

                    $voList = $this->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ();


                    //分页跳转的时候保证查询条件
                    foreach ( $map as $key => $val ) {
                            if (! is_array ( $val )) {
                                    $p->parameter .= "$key=" . urlencode ( $val ) . "&";
                            }
                    }
                    //分页显示

                    $page = $p->show ();
                    //列表排序显示
                    $sortImg = $sort; //排序图标
                    $sortAlt = $sort == 'desc' ? l("ASC_SORT") : l("DESC_SORT"); //排序提示
                    $sort = $sort == 'desc' ? 1 : 0; //排序方式

                    $return['list'] =$voList;
                    $return['sort'] =$sort;
                    $return['order'] =$order;
                    $return['sortImg'] =$sortImg;
                    $return['sortType'] =$sortAlt;
                    $return['page'] =$page;
                    $return['nowPage'] =$p->nowPage;
		}
            return $return;
       }

        //获取异常充值记录
        public  function getAbnormalInchargeList($incharge_list,$start_time, $final_time){
            //富友转账 还款
            require_once APP_ROOT_PATH . "system/payment/fuyou.php";
            $fuyou      = new fuyou();

            $abnormal_notice_sn_list = array();

            foreach($incharge_list as $key => $vl){

                $user_info  = M('user')->find($vl['user_id']);
                //默认为充值
                $trade_type = "PW11";

                //转账记录数据
                $arr = $fuyou->findInchargeCarryRecord($user_info, $trade_type, $start_time, $final_time);

                $arr = json_decode(json_encode($arr->plain), TRUE);

                if (is_array($arr['results']['result'][0])) {
                    foreach ($arr['results']['result'] as $key => $val) {
                        $arr['results']['result'][$key]['id']       = $key + 1;
                        $arr['results']['result'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                        $arr['results']['result'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                        $arr['results']['result'][$key]['txn_amt']  = $val['txn_amt'] / 100;
                    }
                } else {
                    foreach ($arr['results'] as $key => $val) {
                        $arr['results'][$key]['id']       = $key + 1;
                        $arr['results'][$key]['txn_date'] = substr($val['txn_date'], 0, 4) . "-" . substr($val['txn_date'], 4, 2) . "-" . substr($val['txn_date'], 6, 2);
                        $arr['results'][$key]['txn_time'] = substr($val['txn_time'], 0, 2) . ":" . substr($val['txn_time'], 2, 2) . ":" . substr($val['txn_time'], 4, 2);
                        $arr['results'][$key]['txn_amt']  = $val['txn_amt'] / 100;
                    }
                }
                //数据格式化
                if (is_array($arr['results']['result'][0])) {
                    $list = $arr['results']['result'];
                } else {
                    $list = $arr['results'];
                }

                foreach($list as $k => $v){
                    if($vl['notice_sn'] == $v['mchnt_ssn'] && $v['txn_rsp_cd']== "0000" && $vl['is_paid']== 0){
                        $abnormal_notice_sn_list[] = $vl['notice_sn'];
                    }
                }
            }

            return $abnormal_notice_sn_list;
        }

       function getCondition(){
            //增加会员名查找
            if(trim($_REQUEST['user_name'])!=''){
                $condition['user_id'] = M("User")->where("user_name='".trim($_REQUEST['user_name'])."'")->getField("id");
            }
            //增加手机号查找
            if(trim($_REQUEST['mobile'])!=''){
                $condition['user_id'] = M("User")->where("mobile='".trim($_REQUEST['mobile'])."'")->getField("id");
            }
            if(trim($_REQUEST['notice_sn'])!='')
            {
                    $condition['notice_sn'] = $_REQUEST['notice_sn'];
            }

            return $condition;
       }
}
?>