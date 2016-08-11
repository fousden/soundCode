<?php

namespace mapi\controller;

use think\controller;

class Index extends controller {

    /**
     *  
     * @api {get} ?act=uc_money_desc&r_type=1&email=dch&&pwd=123456&year=2016 首页
     * @apiName 首页
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
     * @apiSuccess {int} .cate_id 标的类型（安卓） 
     * 
     */
    public function index() {
        $dealModel = D('deal');
        $root['response_code'] = 1;
        $root['index_list']['adv_list'] = array();
        $where['end_time'] = array("lt", time());
        $where['deal_status'] = 1;
        if ($dealModel->where($where)->getField("id") < 0) {
            $where['deal_status'] = 2;
        }
        $field = "id,name,borrow_amount_e2,rate_e2,repay_time,load_money_e2,deal_status,start_time,cate_id";
        $oreder = "is_advance desc,is_recommend desc,rate_e2 desc,repay_time asc";
        $data[] = $dealModel->field($field)->where($where)->order($oreder)->find();
        $deal_info = $dealModel->getDataFormat($data);
        //`deal_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0待发布，1进行中，2满标，3还款中，4已还清',
        $deal_info[0]['name'].=$dealModel->getDealStatusName($deal_info[0]['deal_status']);
        $root['index_list']['deal_list'] = $deal_info;
        $root['act'] = 'home';
        return output($root);
    }

}
