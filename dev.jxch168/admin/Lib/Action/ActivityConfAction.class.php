<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class ActivityConfAction extends CommonAction
{
    private $type_conf=array(
        '0'=>'请选择...',
        '1'=>'实名送红包活动',
        '2'=>'部分渠道注册送抵现劵活动',
        '3'=>'抽奖活动',
        '4'=>'提成奖励',
        '5'=>'投资成功后跳转URL',
        '6'=>'双旦活动',
        '7'=>'推荐赠大礼包',
        '8'=>'多层级返佣提成奖励',
    );

    public function index()
    {
        $list = M(MODULE_NAME)->order('id desc')->select();
        foreach($list as $key=>$val){
            $list[$key]['type_zh']=$this->type_conf[$val['type']];
        }
        $this->assign("list", $list);
        $this->display();
    }

    public function add()
    {
        $this->assign("type_conf",$this->type_conf);
        $this->display();
    }

    public function insert()
    {
        $data                = M(MODULE_NAME)->create();
        $data['name']        = isset($data['name']) ? trim($data['name']) : '';
        $data['start_time']  = isset($data['start_time']) ? trim(strtotime($data['start_time'])) : '';
        $data['end_time']    = isset($data['end_time']) ? trim(strtotime($data['end_time'])) : '';
        $data['type']        = isset($data['type']) ? trim($data['type']) : '';
         // 这个起始时间不能在其他活动周期内
        $start_time = $data['start_time'];
        $type = $data['type'];
        $count = M(MODULE_NAME)->where("end_time<$start_time AND type=$type AND status=1 ")->count();
//        if($count>0){
//             $this->error("当前时间与同一活动的其他时间有冲突", $ajax);
//        }
        $data['parameter']   = isset($data['parameter']) ? trim($data['parameter']) : '';
        $arr = array();
        $arr = explode(',',$data['parameter']);
        $count = intval(count($arr));
//        if($count<2){
//            $this->error("请按照规则填写参数", $ajax);
//        }
        $data['instruction'] = isset($data['instruction']) ? trim($data['instruction']) : '';
        M(MODULE_NAME)->add($data);
        $this->success(L("新增成功！"));
    }

    public function edit(){
        $id = $_GET['id'];
        $act = M("Activity_conf");
        $data = $act->where("id=$id")->select();
        $data = $data['0'];
        $data['start_time'] = date("Y-m-d H:i:s",$data['start_time']);
        $data['end_time'] = date("Y-m-d H:i:s",$data['end_time']);
        $data['id'] = $id;
        $this->assign("type_conf",$this->type_conf);
        $this->assign("data",$data);
        $this->display();
    }

    public function update(){
	$act = M("Activity_conf");
        $data = $act->create();
        $data['start_time'] = strtotime($_REQUEST['start_time']);
        $data['end_time'] = strtotime($_REQUEST['end_time']);
        $res = $act->where("id={$data['id']}")->data($data)->save();
        if($res==1){
            save_log($log_info . L("UPDATE_SUCCESS"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        }
    }
    
    public function get_pinyin(){
        $name=  trim($_REQUEST['name']);
        echo utf8_to($name);
    }
}

?>