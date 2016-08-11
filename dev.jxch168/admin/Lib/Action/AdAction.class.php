<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class AdAction extends CommonAction{
    public $arr;
    public function __construct()
    {
        parent::__construct();
        $this->arr = array(
            0=>"电脑端",
            1=>"移动端",
        );
    }

    public function index()
    {
        $is_effect = isset($_REQUEST['is_effect']) ? trim($_REQUEST['is_effect']) : '' ;
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '' ;
        $type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '' ;
        $time=time();
        $map=array();
        if($is_effect==1){
            $map['is_effect'] = array("eq",0);// 无效
        }
        if($is_effect==2){
            $map['is_effect'] = array("eq",1);// 有效
        }

        if($status==1){
            $map['begin_time'] = array("gt",$time); // 未开始
        }

        if($status==2){
            $map['begin_time'] = array("lt",$time);
            $map['end_time'] = array("gt",$time);// 进行中
        }

        if($status==3){
            $map['end_time'] = array("lt",$time);// 已结束
        }

        if($type==1){
            $map['type'] = array("eq",0); // 电脑端
        }

        if($type==2){
            $map['type'] = array("eq",1);// 移动端
        }
        //列表过滤器，生成查询Map对象
//        $map = $this->_search ();
//        echo "<pre>";
//        print_r($map);exit;
//        追加默认参数
        if($this->get("default_map"))
            $map = array_merge($map,$this->get("default_map"));

        if (method_exists ( $this, '_filter' )) {
            $this->_filter ( $map );
        }
        $name=$this->getActionName();
        $model = D ($name);
        if (! empty ( $model )) {
            $this->_list ( $model, $map,'sort');
        }
        $this->display ();
        return;
    }
//    public function load_file()
//    {
//        $tmpl = $_REQUEST['tmpl'];
//        $directory = APP_ROOT_PATH."app/Tpl/".$tmpl."/";
//        $files = get_all_files($directory);
//        $tmpl_files = array();
//        foreach($files as $item)
//        {
//            if(substr($item,-5)==".html")
//            {
//                $item = explode($directory,$item);
//                $item = $item[1];
//                //只有首页有广告位 后期放宽条件可去掉该逻辑判断
//                if($item == 'inc/header.html')
//                {
//                    $tmpl_files[] = $item;
//                }
//            }
//        }
//        $this->ajaxReturn($tmpl_files);
//    }
//    public function load_adv_id()
//    {
//        $tmpl = $_REQUEST['tmpl'];
//        $file = $_REQUEST['file'];
//        $directory = APP_ROOT_PATH."app/Tpl/".$tmpl."/";
//        $file_content = @file_get_contents($directory.$file);
//
//
//        $layout_array = array();
//        $adv_ids = array();
//        preg_match_all("/<adv(\s+)adv_id=\"(\S+)\"([^>]*)>/",$file_content,$layout_array);
//        foreach($layout_array[2] as $item)
//        {
//            $adv_ids[] = $item;
//        }
//
//        $this->ajaxReturn($adv_ids);
//    }
    public function add()
    {
//            查询出最大的sort值
        $maxSort=D('ad')->getField("max(sort)");
        //end
//        $this->assign("rel_table",$_REQUEST['rel_table']);
//        $this->assign("rel_id",$_REQUEST['rel_id']);
        $this->assign("maxSort",$maxSort+1);
        $this->assign("arr",$this->arr);
        $this->display();
    }

    public function insert() {
        B('FilterString');
        $ajax = intval($_REQUEST['ajax']);
        $data = M(MODULE_NAME)->create ();
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        //开始验证有效性
        $this->assign("jumpUrl",u(MODULE_NAME."/add"));
        if(!check_empty($data['name']))
        {
            $this->error(L("ADV_NAME_EMPTY_TIP"));
        }
        if(!check_empty($data['img_url']))
        {
            $this->error("请上传一张图片");
        }
        if(!check_empty($data['href']))
        {
            $this->error("图片链接地址不能为空");
        }
//		if($data['adv_id']=='')
//		{
//			$this->error(L("ADV_IDS_EMPTY_TIP"));
//		}
        // 更新数据
        $log_info = $data['name'];
        $list=M(MODULE_NAME)->add($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info.L("INSERT_SUCCESS"),1);
            $this->success(L("INSERT_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info.L("INSERT_FAILED"),0);
            $this->error(L("INSERT_FAILED"));
        }
    }

    public function edit() {
        $id = intval($_REQUEST ['id']);
        $condition['id'] = $id;
        $vo = M(MODULE_NAME)->where($condition)->find();
        $this->assign("arr",$this->arr);
        $this->assign ('vo', $vo);
        $this->display();
    }

    public function update() {
        B('FilterString');
        $data = M(MODULE_NAME)->create ();
//                //echo $dada['sort']=$_REQUEST["sort"];die;
//                echo '<pre>';
//                print_r($data);
//                echo '</pre>';die;
        $log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
        //开始验证有效性
        $this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
//        if($data['code']=='')
//        {
//            $this->error(L("ADV_CODE_EMPTY_TIP"));
//        }
        // 更新数据
        $data['begin_time'] = strtotime($data['begin_time']);
        $data['end_time'] = strtotime($data['end_time']);
        $data['update_time'] = time();
        $list=M(MODULE_NAME)->save ($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info.L("UPDATE_SUCCESS"),1);
            $this->success(L("UPDATE_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info.L("UPDATE_FAILED"),0);
            $this->error(L("UPDATE_FAILED"),0,$log_info.L("UPDATE_FAILED"));
        }
    }


    public function foreverdelete() {
        //彻底删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id = $_REQUEST ['id'];
        if (isset ( $id )) {
            $condition = array ('id' => array ('in', explode ( ',', $id ) ) );
            $rel_data = M(MODULE_NAME)->where($condition)->findAll();
            foreach($rel_data as $data)
            {
                $info[] = $data['name'];
            }
            if($info) $info = implode(",",$info);
            $list = M(MODULE_NAME)->where ( $condition )->delete();

            if ($list!==false) {
                save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
                $this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
            } else {
                save_log($info.l("FOREVER_DELETE_FAILED"),0);
                $this->error (l("FOREVER_DELETE_FAILED"),$ajax);
            }
        } else {
            $this->error (l("INVALID_OPERATION"),$ajax);
        }
    }

    public function set_effect()
    {
        $id = intval($_REQUEST['id']);
        $ajax = intval($_REQUEST['ajax']);
        $info = M(MODULE_NAME)->where("id=".$id)->getField("name");
        $c_is_effect = M(MODULE_NAME)->where("id=".$id)->getField("is_effect");  //当前状态
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=".$id)->setField("is_effect",$n_is_effect);
        save_log($info.l("SET_EFFECT_".$n_is_effect),1);
        $this->ajaxReturn($n_is_effect,l("SET_EFFECT_".$n_is_effect),1)	;
    }
}
?>