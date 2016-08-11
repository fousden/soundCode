<?php
namespace admin\controller;
use base\controller\backend;

/**
 * 用户充值控制器
 *
 * @author jxch
 */

class UserIncharge extends backend{
    //表名
    protected $tableName = 'user_incharge';
    
    //所有用户充值列表
    public function index(){
        $incharge_list = D('user_cash')->getListData($this->tableName,$_REQUEST);
        $this->assign("page", $incharge_list['page']);
        $this->assign("nowPage", $incharge_list["nowPage"]);
        $this->assign("list",$incharge_list['data_list']);
        return $this->fetch();
    }
    
    //用户充值统计
    public function statistics(){
        //将获取的时间进行处理
        $date_list = D('user_cash')->getDateList($this->tableName,$_REQUEST);
        //获取图形数据
        if(count($date_list)==1){
            //时间段为一天，获取饼图数据

            $this->assign('series_name', json_encode("百分比"));//鼠标悬浮时，饼图中间文字显示的内容
            
            $data_name_raw   = ['web', 'wap', 'Android','IOS'];
            $this->assign('data_name', json_encode($data_name_raw));//传入的数据名数组，必填，数据名类型为字符串
            
            $pie_data = D('user_cash')->getPieData($this->tableName,$date_list);
            $this->assign('pie_data_array', json_encode($pie_data));//饼图的百分比数据，数据类型为[25,50,15,10]加起来要整等于100
            
            $this->assign('type', 'pie'); //告诉模块显示饼图
        }else{
            //时间段大于一天，获取折线图数据
            
            $this->assign('xAxis_pot', json_encode($date_list));//x轴数值名，必填，类型为字符串数组
            $this->assign('yAxis_title', json_encode("yaxis"));//y轴名，必填，类型为字符串
            
            $data_name_raw   = ['web', 'wap', 'Android','IOS','全部'];
            $this->assign('data_name', json_encode($data_name_raw));//传入的数据名数组，必填，数据名类型为字符串

            $line_data = D('user_cash')->getLineData($this->tableName,$date_list);
            $this->assign('data_array', json_encode(array_values($line_data)));//传入的数据数组，必填，类型为数组
            $this->assign('unit', json_encode("元"));//单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
            
            $this->assign('type', 'line'); //告诉模块显示折线图
        }
        
        //获取表格数据
        $incharge_account = D('user_cash')->getAccount($this->tableName,$date_list);
        $this->assign("list",$incharge_account);
        return $this->fetch();
    }
    
    //充值排行榜
    public function rank(){
        //获得表格数据
        $user_incharge_rank = D('user_cash')->getUserRank($this->tableName,$_REQUEST);
        $this->assign("page", $user_incharge_rank['page']);
        $this->assign("nowPage", $user_incharge_rank["nowPage"]);
        $this->assign("list",$user_incharge_rank['data_list']);
        return $this->fetch();
    }
}

