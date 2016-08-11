<?php

/**
 * 后台借款模块 model业务逻辑类
 *
 * @author jxch
 */
namespace admin\model;
use base\model\backend;

class Borrow extends backend{
    //表名
    protected $tableName = 'deal';

    //获取标的列表
    function getDealList(){
        //未删除的
        $condition["is_delete"] = 0;
        //查询条件
        if(@$_REQUEST["name"]){
            $condition["name"] = $_REQUEST["name"];
        }
        if(@$_REQUEST["borrow_amount"]){
            $condition["borrow_amount_e2"] = intval($_REQUEST["borrow_amount"] * 100);
        }
        if(@$_REQUEST["rate"]){
            $condition["rate_e2"] = intval($_REQUEST["rate"] * 100);
        }
        if(@$_REQUEST["borrower_name"]){
            $condition["borrower_id"] = M("borrower")->where(array("name"=>$_REQUEST["borrower_name"],"is_effect"=>1))->getField("id");
        }
        if(@$_REQUEST["cate_id"]){
            $condition["cate_id"] = $_REQUEST["cate_id"];
        }

        if($_REQUEST["deal_status"]){
            if(intval($_REQUEST["deal_status"]) == 5){
                $condition["deal_status"] = 0;
            }else if($_REQUEST["deal_status"] != "all"){
                $condition["deal_status"] = $_REQUEST["deal_status"];
            }
        }

        //排序
        $_order = $_REQUEST["_order"] ? $_REQUEST["_order"] : "id";
        $_sort = $_REQUEST["_sort"] == 1 ? "asc" : "desc";

        //取得满足条件的记录数
        $count = $this->where($condition)->count('id');
        if ($count > 0) {
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = 20;
            }
            $p = new \think\Page($count, $listRows);
            $deal_list = $this->where($condition)->order($_order ." ". $_sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            //分页跳转的时候保证查询条件
            foreach ($condition as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $return['page'] = $p->show();
            $return["nowPage"] = $p->nowPage;
            $return['deal_list_new'] = $this->formatDeal($deal_list);
        }
        return $return;
    }

    //格式化标的数据
    function formatDeal($deal_list){
        //格式化数据
        foreach($deal_list as $key=>$val){
            //借款账户信息
            $deal_list[$key]["borrower"] = M("borrower")->find($val["borrower_id"]);
            $deal_list[$key]["borrow_amount"] = num_format($val["borrow_amount_e2"] / 100);
            $deal_list[$key]["load_money"] = num_format($val["load_money_e2"] / 100);
            $deal_list[$key]["repay_money"] = num_format($val["repay_money_e2"] / 100);
            $deal_list[$key]["min_loan_money"] = num_format($val["min_loan_money_e2"] / 100);
            $deal_list[$key]["max_loan_money"] = num_format($val["max_loan_money_e2"] / 100);
            $deal_list[$key]["rate"] = num_format($val["rate_e2"] / 100);
            $deal_list[$key]["increase_rate"] = num_format($val["increase_rate_e2"] / 100);
            //deal_status 0待发布，1进行中，2满标，3还款中，4已还清
            $deal_status_desc = array(0 => "待发布",1 => "进行中",2 => "满标",3 => "还款中",4 => "已还清");
            $deal_list[$key]["deal_status_desc"] = $deal_status_desc[$val["deal_status"]] ? $deal_status_desc[$val["deal_status"]] : "无";
            //is_has_loans 是否已经满标放款 0未放款 1已放款
            $is_has_loans_desc = array(0 => "未放款",1 => "已放款");
            $deal_list[$key]["is_has_loans_desc"] = $is_has_loans_desc[$val["is_has_loans"]] ? $is_has_loans_desc[$val["is_has_loans"]] : "无";
            //状态描述 是否 0否 1是
            $status_desc = array(0 => "否",1 => "是");
            $deal_list[$key]["is_recommend_desc"] = $status_desc[$val["is_recommend"]] ? $status_desc[$val["is_recommend"]] : "无";
            //is_advance 是否预告 0 否 1 是
            $deal_list[$key]["is_advance_desc"] = $status_desc[$val["is_advance"]] ? $status_desc[$val["is_advance"]] : "无";
            //is_hidden 是否在投资列表隐藏 0：不隐藏，1：隐藏
            $deal_list[$key]["is_hidden_desc"] = $status_desc[$val["is_hidden"]] ? $status_desc[$val["is_hidden"]] : "无";
            //is_effect 是否有效 0否 1是
            $is_effect_desc = array(0 => "无效",1 => "有效");
            $deal_list[$key]["is_effect_desc"] = $is_effect_desc[$val["is_effect"]] ? $is_effect_desc[$val["is_effect"]] : "无";
            //is_delete 是否删除 0否 1是
            $deal_list[$key]["is_delete_desc"] = $status_desc[$val["is_delete"]] ? $status_desc[$val["is_delete"]] : "无";
            //is_moving 是否是移动端，1表示移动端
            $deal_list[$key]["is_moving_desc"] = $status_desc[$val["is_moving"]] ? $status_desc[$val["is_moving"]] : "无";
            //admin_id
            if($val["admin_id"]){
                $deal_list[$key]["admin_name"] = M("admin")->where(array("id"=>$val["admin_id"]))->getField("admin_name");
            }else{
                $deal_list[$key]["admin_name"] = "无";
            }
            //借款分类
            $deal_list[$key]["cate_name"] = M("deal_cate")->where(array("id"=>$val["cate_id"]))->getField("name");
        }
        return $deal_list;
    }

    //添加数据
    function addDeal(){
        $data = $this->create();
        //数据处理
        $data["borrow_amount_e2"] = $data["borrow_amount_e2"] ? intval($data["borrow_amount_e2"] * 100) : 0;
        $data["load_money_e2"] = $data["load_money_e2"] ? intval($data["load_money_e2"] * 100) : 0;
        $data["min_loan_money_e2"] = $data["min_loan_money_e2"] ? intval($data["min_loan_money_e2"] * 100) : 0;
        $data["max_loan_money_e2"] = $data["max_loan_money_e2"] ? intval($data["max_loan_money_e2"] * 100) : 0;
        $data["load_money_e2"] = $data["load_money_e2"] ? intval($data["load_money_e2"] * 100) : 0;
        $data["rate_e2"] = $data["rate_e2"] ? intval($data["rate_e2"] * 100) : 0;
        $data["increase_rate_e2"] = $data["increase_rate_e2"] ? intval($data["increase_rate_e2"] * 100) : 0;
        //标的开始时间
        $data["start_time"] = strtotime($data["start_time"]);
        $data["create_time"] = time();
        //标的结束时间
        $data["end_time"] = strtotime("+".$data["enddate"]." days",$data["start_time"]);
        //起息日期
        $data["qixi_date"] = date("Y-m-d",strtotime("+1 day",$data["end_time"]));
        //结息日期
        $data["jiexi_date"] = date("Y-m-d",strtotime("+".$data["repay_time"]." days",strtotime($data["qixi_date"])));
        //最迟还款日
        $data["last_repay_date"] = date("Y-m-d",strtotime("+2 days",strtotime($data["jiexi_date"])));

        if($id = $this->add($data)){
            return $id;
        }else{
            return false;
        }

    }

    //初始化数据
    function initAddDeal(){
        //借款人
        $data["borrower"] = M("user")->where(array("user_type"=>1,"is_effect"=>1))->select();
        //借款分类
        $data["deal_cate"] = M("deal_cate")->where(array("is_effect"=>1))->select();
        //担保机构
        $data["deal_agency"] = M("deal_agency")->where(array("is_effect"=>1))->select();
        //借款合同
        $data["contract"] = M("contract")->where(array("is_effect"=>1,"is_delete"=>0))->select();
        return $data;
    }

    //获取单个标的信息
    function getDealInfo($id){
        $deal_info = $this->find($id);
        $deal_list[0] = $deal_info;
        $deal_list = $this->formatDeal($deal_list);
        $deal_info = $deal_list[0] ? $deal_list[0] : "";
        return $deal_info;
    }

    //编辑修改借款
    function editDeal(){
        $data = $this->create();
        $deal_status = M("deal")->where(array("id"=>$data["id"]))->getField("deal_status");
        $update_data = array();
        if($deal_status){
            foreach($data as $key=>$val){
                if($key == "min_loan_money_e2"){
                    $edit_data[$key] = intval($val * 100);
                }else if($key == "max_loan_money_e2"){
                    $edit_data[$key] = intval($val * 100);
                }else{
                    $edit_data[$key] = $val;
                }
            }
            $update_data = $edit_data;
        }else{
            //数据处理
            $data["borrow_amount_e2"] = $data["borrow_amount_e2"] ? intval($data["borrow_amount_e2"] * 100) : 0;
            $data["load_money_e2"] = $data["load_money_e2"] ? intval($data["load_money_e2"] * 100) : 0;
            $data["min_loan_money_e2"] = $data["min_loan_money_e2"] ? intval($data["min_loan_money_e2"] * 100) : 0;
            $data["max_loan_money_e2"] = $data["max_loan_money_e2"] ? intval($data["max_loan_money_e2"] * 100) : 0;
            $data["load_money_e2"] = $data["load_money_e2"] ? intval($data["load_money_e2"] * 100) : 0;
            $data["rate_e2"] = $data["rate_e2"] ? intval($data["rate_e2"] * 100) : 0;
            $data["increase_rate_e2"] = $data["increase_rate_e2"] ? intval($data["increase_rate_e2"] * 100) : 0;
            //标的开始时间
            $data["start_time"] = strtotime($data["start_time"]);
            //标的结束时间
            $data["end_time"] = strtotime("+".$data["enddate"]." days",$data["start_time"]);
            //起息日期
            $data["qixi_date"] = strtotime("+1 day",$data["end_time"]);
            //结息日期
            $data["jiexi_date"] = strtotime("+".$data["repay_time"]." days",$data["qixi_date"]);
            //最迟还款日
            $data["last_repay_date"] = strtotime("+2 days",$data["jiexi_date"]);
            $update_data = $data;
        }

        if($id = $this->save($update_data)){
            return $id;
        }else{
            return false;
        }
    }
}
