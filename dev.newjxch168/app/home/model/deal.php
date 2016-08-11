<?php

namespace home\model;
use base\model\frontend;

/**
 * 前台borrow借款公用业务逻辑类
 *
 * @author jxch
 */

class Deal extends frontend{
    //表名
    protected $tableName = 'deal';

    //获取理财标的列表
    function getDealList($request,$condition = [],$orderby = "create_time desc"){
        $condition["deal_status"] = array("in","1,2,3,4");
        //过滤过期标的
        $condition["end_time"] = array("gt",time());
        //分类查询
        if($request["cate_id"]){
            $condition["cate_id"] = $request["cate_id"];
        }
        //标的状态查询
        if($request["deal_status"]){
            $condition["deal_status"] = (intval($request["deal_status"]) - 1);
        }
        //标的利率查询
        if($request["rate_e2"]){
            switch (intval($request["rate_e2"]))
            {
            case 1000:
              $condition["rate_e2"] = array("lt",1000);
              break;
            case 1200:
              $condition["rate_e2"] = array(array('egt',1000),array('lt',1200)) ;
              break;
            case 1400:
              $condition["rate_e2"] = array(array('egt',1200),array('lt',1400)) ;
              break;
            case 1401:
              $condition["rate_e2"] = array("egt",1400);
              break;
            }
        }
        //标的期限查询
        if($request["repay_time"]){
            switch (intval($request["repay_time"]))
            {
            case 90:
              $condition["repay_time"] = array("lt",90);
              break;
            case 180:
              $condition["repay_time"] = array(array('egt',90),array('lt',180)) ;
              break;
            case 360:
              $condition["repay_time"] = array(array('egt',180),array('lt',360)) ;
              break;
            case 361:
              $condition["repay_time"] = array("egt",360);
              break;
            }
        }
        //排序
        $sort_field = $request["sort_field"] ? $request["sort_field"] : "create_time";
        $sort_type = $request["sort_type"] ? $request["sort_type"] : "desc";
        $orderby = " ".$sort_field." ".$sort_type.",deal_status asc";

        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($request ['listRows'])) {
                $listRows = $request ['listRows'];
            } else {
                $listRows = 10;
            }
            $p = new \think\Page($count, $listRows);
            $deal_list = $this->where($condition)->order($orderby)->limit($p->firstRow . ',' . $p->listRows)->select();

            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            foreach($deal_list as $key=>$val){
                //判断是否已经开始
                $val['is_wait'] = 0;
                if ($val['start_time'] > time()) {
                    $deal_list[$key]['is_wait']     = 1;
                    $deal_list[$key]['remain_time'] = $val['start_time'] - time();
                } else {
                    $deal_list[$key]['remain_time'] = $val['start_time'] + $val['enddate'] * 24 * 3600 - time();
                }
                //进度
                $deal_list[$key]['progress_point'] = $val["load_money_e2"] / $val["borrow_amount_e2"] * 100;
                $deal_list[$key]["agency_info"] = M("agency")->find($val["agency_id"]);
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['deal_list'] = $deal_list;
        }
        return $return;
    }

    //初始化标的信息
    function initDealInfo(){
        //标的分类
        $cate_list_url[0]['name'] = "不限";
        $cate_list_url[0]['cate_id'] = "";
        $cate_list_url[0]['url'] = $_SERVER['REQUEST_URI']."&cate_id=";
        $deal_cates = M("deal_cate")->where(array("is_effect"=>1))->select();
        foreach ($deal_cates as $k => $v) {
            $cate_list_url[$k + 1] = $v;
            $cate_list_url[$k + 1]['url'] = $_SERVER['REQUEST_URI']."&cate_id=".$v["id"];
            $cate_list_url[$k + 1]['id'] = $k + 1;
        }
        $result['cate_list_url'] = $cate_list_url;

        //标状态
        $deal_status_url = array(
            array(
                "key" => '',
                "name" => "不限",
            ),
            array(
                "key" => 1,
                "name" => "待发布",
            ),
            array(
                "key" => 2,
                "name" => "可投资",
            ),
            array(
                "key" => 3,
                "name" => "满标",
            ),
            array(
                "key" => 4,
                "name" => "还款中",
            ),
            array(
                "key" => 5,
                "name" => "已还清",
            ),
        );
        foreach ($deal_status_url as $k => $v) {
            $deal_status_url[$k]['url'] = $v['key'] ? $_SERVER['REQUEST_URI']."&deal_status=".$v["key"] : $_SERVER['REQUEST_URI']."&deal_status=";
        }
        $result['deal_status_url'] = $deal_status_url;
        //利率
        $interest_url = array(
            array(
                "rate_e2" => "",
                "name" => "不限",
            ),
            array(
                "rate_e2" => "1000",
                "name" => "10%以下",
            ),
            array(
                "rate_e2" => "1200",
                "name" => "10%-12%",
            ),
            array(
                "rate_e2" => "1400",
                "name" => "12%-14%",
            ),
            array(
                "rate_e2" => "1401",
                "name" => "14%以上",
            ),
        );
        foreach ($interest_url as $k => $v) {
            $interest_url[$k]['url'] = $v['rate_e2'] ? $_SERVER['REQUEST_URI']."&rate_e2=".$v["rate_e2"] : $_SERVER['REQUEST_URI']."&rate_e2=";
        }
        $result['interest_url'] = $interest_url;
        //借款期限
        $months_type_url = array(
            array(
                "name" => "不限",
                "value"=>"",
            ),
            array(
                "name" => "3 个月以下",
                "value"=>"90",
            ),
            array(
                "name" => "3-6 个月",
                "value"=>"180",
            ),
            array(
                "name" => "6-12 个月",
                "value"=>"360",
            ),
            array(
                "name" => "12 个月以上",
                "value"=>"361",
            ),
        );

        foreach ($months_type_url as $k => $v) {
            $months_type_url[$k]['url'] = $v["value"] ? $_SERVER['REQUEST_URI']."&repay_time=".$v["value"] : $_SERVER['REQUEST_URI']."&repay_time=";
        }
        $result['months_type_url'] = $months_type_url;
        //排序
        $sort_url = array(
            array(
                'sort' => 'create_time',
                'name' => "发布时间",
            ),
            array(
                'sort' => 'borrow_amount_e2',
                'name' => "标的金额",
            ),
            array(
                'sort' => 'rate_e2',
                'name' => "年化利率",
            ),
            array(
                'sort' => 'jiexi_date',
                'name' => "还款日期",
            ),
        );
        foreach ($sort_url as $k => $v) {
            $sort_url[$k]['url'] = $_SERVER['REQUEST_URI']."&sort_field=".$v["sort"];
        }
        $result['sort_url'] = $sort_url;
        return $result;
    }
    
    public function getDealInfoById($id){
//        $where['is_hidden']=0;
//        $where['is_effect']=1;
//        $where['is_delete']=0;
        $where['id']=$id;
        return $this->where($where)->find();
    }
}

