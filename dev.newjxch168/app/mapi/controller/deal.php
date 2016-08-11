<?php

namespace mapi\controller;

class deal extends \think\controller {

    /**
     *  
     * @api {get} ?act=uc_money_desc&r_type=1&email=dch&&pwd=123456&year=2016 我的资产
     * @apiName 我的资产
     * @apiGroup jxch
     * @apiVersion 1.0.0 
     * @apiDescription 请求url 
     *  
     * @apiParam {string} cid 项目类型 1=金享票号，2=金享保理，3=金享租赁，4=金享银行
     * @apiParam {string} orderby 0=综合排序，1=收益降序，2=期限升序，3=还款期升序（默认0）
     * @apiParam {string} page 页码
     * 
     * @apiSuccess {string} response_code 结果码 
     * @apiSuccess {json} item 标的列表数据
     * @apiSuccess {string} .name 标的名称 
     * @apiSuccess {string} .borrow_amount_format 借款总额（单位万） 
     * @apiSuccess {string} .repay_time 投资期限 
     * @apiSuccess {int} .repay_time_type 投资期限的单位（0=天，1=月） 
     * @apiSuccess {string} .progress_point 投资进度 
     * @apiSuccess {string} .deal_status 标的状态 
     * @apiSuccess {string} .bfinish_time 是否显示预计发售时间 
     * @apiSuccess {string} .start_time 筹标开始时间 
     * @apiSuccess {string} .rate_foramt_w 年化利率（ios） 
     * @apiSuccess {string} .rate_foramt 年化利率（安卓） 
     * 
     */
    public function index() {
        $dealModel = D("deal");        
        // cid 项目类型 1=金享票号，2=金享保理，3=金享租赁，4=金享银行 
        $where['cate_id'] = isset($_REQUEST['cid']) ? (int) $_REQUEST['cid'] : 1;
        //orderby 0=综合排序，1=收益降序，2=期限升序，3=还款期升序（默认0）
        $orderby = isset($_REQUEST['orderby']) ? (int) $_REQUEST['orderby'] : 0;
        $page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
        //分页
        $pageInfo = $this->getPageInfo($page, $dealModel, $where);
        $limit = $pageInfo['limit'];
        $root['response_code'] = 1;
        $root['page'] = $pageInfo['page_info'];
        //orderby 0=综合排序，1=收益降序，2=期限升序，3=还款期升序（默认0）
        $orderby_conf = array("", "rate_e2 desc", "repay_time asc");
        $order = $orderby_conf[$orderby];    
        $root['item'] = $dealModel->getDealList($where,$order,$limit);        
        output($root);
    }
    public function getPageInfo($page, $model, $where = '', $page_size = 10) {
        //分页
        $conut = $model->where($where)->count();
        $page_total = ceil($conut / $page_size);
        $limit = ($page - 1) . "," . $page_size;

        $data['page_info'] = array(
            'page' => $page,
            'page_size' => $page_size,
            'page_total' => $page_total,
        );
        $data['limit'] = $limit;
        return $data;
    }

    /**
     *  
     * @api {get} ?act=uc_money_desc&r_type=1&email=dch&&pwd=123456&year=2016 关注以及标的详情
     * @apiName 关注以及标的详情
     * @apiGroup jxch
     * @apiVersion 1.0.0 
     * @apiDescription 请求url 
     *  
     * @apiParam {int} id 标的id
     * 
     * @apiSuccess {string} response_code 结果码 
     * @apiSuccess {json} item 标的详情数据
     * @apiSuccess {int} .id 标的id
     * @apiSuccess {string} .name 标的名称 
     * @apiSuccess {string} .borrow_amount_format 借款总额（单位万） 
     * @apiSuccess {string} .repay_time 投资期限 
     * @apiSuccess {string} .min_loan_money 最低起投金额 
     * @apiSuccess {string} .jiexi_time 结息日期（Y-m-d） 
     * @apiSuccess {string} .qixi_time 起息日期（Y-m-d） 
     * @apiSuccess {string} .last_mback_time 最迟还款日（Y-m-d） 
     * @apiSuccess {string} .guarantee 保障机构名称
     * @apiSuccess {string} .deal_status 标的状态
     * @apiSuccess {string} .need_money 可投金额
     * @apiSuccess {string} .remain_time_format 剩余时间（d天H时i分）
     * @apiSuccess {string} .bfinish_time 是否显示预计发售时间（ios）
     * @apiSuccess {string} .progress_point 投资进度百分比
     * @apiSuccess {string} .rate 年化利率（安卓）
     * @apiSuccess {string} .rate_foramt_w 年化利率（ios）
     * @apiSuccess {string} .loantype 1=付息还本,2=到期还本息
     * 
     */
    public function deal_collect() {
        $deal_id=  isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;
        $root['response_code'] = 1;
        $data_info=D('deal')->getDealInfoById($deal_id);
        $root = array(
            'response_code'=>1,
            "user_money_format"=> 0,
            "user_money"=> 0,
            "response_code"=> 1,
            "is_faved"=> 0,
            "ips_bill_no"=> 0,
        );
        
//        $data_info = array(
//        "id"=> "726",
//        "name"=> "1111111111111",
//        "rate"=> "8.00",
//        "repay_time"=> "30",
//        "min_loan_money"=> "10.00",
//        "loantype"=> "2",
//        "jiexi_time"=> "2016-03-23",
//        "qixi_time"=> "2016-02-22",
//        "last_mback_time"=> "2016-03-25",
//        "enddate"=> "30",
//        "deal_status"=> "1",
//        "progress_point"=> "22.3",
//        "borrow_amount_format"=> "10.00万",
//        "rate_foramt_w"=> "8.00%",
//        "need_money"=> "100000.00",
//        "remain_time_format"=> "29天23时59分",
//        "guarantee"=> "担保机构2",
//        "bfinish_time"=> 1
//        );	
        $root['item'] = $data_info;
        output($root);
    }

}
