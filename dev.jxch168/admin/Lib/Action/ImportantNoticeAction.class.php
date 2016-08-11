<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 10:47
 */
class ImportantNoticeAction extends CommonAction{
    public function index(){
        $is_effect = $_REQUEST['is_effect'];
        $client_type = $_REQUEST['client_type'];
        $time=time();
        if($is_effect==1){
            $condition['_string'] = "begin_time>$time or end_time<$time";
//            $condition['begin_time'] = array("gt",$time);
//            $condition['end_time'] = array("lt",$time);
//            $condition['_logic'] = 'OR';
        }
        if($is_effect==2){
            $condition['begin_time']=  array("lt",$time);
            $condition['end_time']=  array("gt",$time);
        }
        if($client_type==1){
            $condition['client_type'] = array("eq",1);
        }
        if($client_type==2){
            $condition['client_type'] = array("eq",2);

        }
        $this->assign("default_map",$condition);
        parent::index();
    }

    public function add(){
        $this->display();
    }

    public function insert(){
        $title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : ''; // 标题
        $remark = isset($_REQUEST['remark']) ? trim($_REQUEST['remark']) : ''; // 备注
        $content_type = isset($_REQUEST['notice_content']) ? trim($_REQUEST['notice_content']) : ''; // 文字还是图片1为图片2文字
        $client_type = isset($_REQUEST['client_type']) ? trim($_REQUEST['client_type']) : ''; //  客户端1移动端2手机端
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : ''; //  开始时间
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : ''; //  开始时间
        if($content_type==1){
            $content = isset($_REQUEST['icon']) ? $_REQUEST['icon'] : ''; // 选择图片的时候，图片的URL地址
        }else{
            $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : ''; // 选择文字的时候，富文本编辑器的内容
        }
        $data['title'] = $title;
        $data['content'] = $content;
        $data['client_type'] = $client_type;
        $data['content'] = $content;
        $data['content_type'] = $content_type;
        $data['remark'] = $remark;
        $data['begin_time'] = strtotime($begin_time);
        $data['end_time'] = strtotime($end_time);
        $data['create_time'] = time();
        $data['update_time'] = time();
//        dump($data);exit;
        $id = M("ImportantNotice")->add($data);
        if($id){
            $this->success("添加成功！");
        }
    }

    public function edit(){
        $notice_info = M("ImportantNotice")->where("id=".$_REQUEST['id'])->find();
        // 数据做一些处理
        $notice_info["begin_time"] = to_date($notice_info["begin_time"]);
        $notice_info["end_time"] = to_date($notice_info["end_time"]);
//        dump($notice_info);
        $this->assign("list",$notice_info);
        $this->display();
    }

    public function update(){
//        echo "<pre>";
//        print_r($_REQUEST);exit;
        $id=  $title = isset($_REQUEST['notice_id']) ? trim($_REQUEST['notice_id']) : ''; // id
        $title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : ''; // 标题
        $remark = isset($_REQUEST['remark']) ? trim($_REQUEST['remark']) : ''; // 备注
        $content_type = isset($_REQUEST['notice_content']) ? trim($_REQUEST['notice_content']) : ''; // 文字还是图片1为图片2文字
        $client_type = isset($_REQUEST['client_type']) ? trim($_REQUEST['client_type']) : ''; //  客户端1移动端2手机端
        $begin_time = isset($_REQUEST['begin_time']) ? trim($_REQUEST['begin_time']) : ''; //  开始时间
        $end_time = isset($_REQUEST['end_time']) ? trim($_REQUEST['end_time']) : ''; //  开始时间
        if($content_type==1){
            $content = isset($_REQUEST['icon']) ? $_REQUEST['icon'] : ''; // 选择图片的时候，图片的URL地址
        }else{
            $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : ''; // 选择文字的时候，富文本编辑器的内容
        }
        $data['title'] = $title;
        $data['content'] = $content;
        $data['client_type'] = $client_type;
        $data['content'] = $content;
        $data['content_type'] = $content_type;
        $data['remark'] = $remark;
        $data['begin_time'] = strtotime($begin_time);
        $data['end_time'] = strtotime($end_time);
        $data['update_time'] = time();
        $res = M("ImportantNotice")->where("id=".$id)->save($data);
        if($res){
            $this->success("更新成功！");
        }
    }

    public function foreverdelete(){
        $id = $_REQUEST["id"];
        $res = M("ImportantNotice")->where("id=".$id)->delete();
        if($res){
            $data['status']=1;
            $data['info']="删除成功";
            ajax_return($data);
        }

    }
}