<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/16
 * Time: 9:39
 */
class BlacklistAction extends CommonAction
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $mobile = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
        $where['is_delete'] = 0;
        if($mobile){
            $where['mobile'] = array("like","%$mobile%");
        }
        $mobile_blacklist_model=M("mobile_blacklist");
        $this->_list($mobile_blacklist_model, $where);
        $list_count = $mobile_blacklist_model->where("is_delete=0")->getField("count(*)");
        if($mobile_list){
            $data['list'] = $mobile_list;
            $this->assign("data",$data);
        }
        $this->assign("list_count",$list_count);
        $this->assign("all_black_count",$mobile_blacklist_model->getField("sum(black_count)"));
        $this->display();
    }

    public function add(){
        $this->display();
    }

    public function insert(){
        $mobile_string = isset($_REQUEST['mobile']) ? str_replace(' ', '',trim( $_REQUEST['mobile'])) : '';
        $mobile_string = str_replace('，',',',$mobile_string);// 将中文逗号统一换成字符串逗号;
        $mobile_list = explode(',',$mobile_string);
        $mobile_blacklist = M("mobile_blacklist");
//        echo "<pre>";
//        print_r($mobile_list);exit;
        if($mobile_list){
            foreach($mobile_list as $val){
                // 录进数据库如果数据库
                $black_count = $mobile_blacklist->where("mobile=$val")->getField("black_count");
                if($black_count>0){
                    $data['update_time'] = time();
                    $data['is_delete'] = 0;
                    $data['black_count'] = $black_count+1;
                    $mobile_blacklist->where("mobile=$val")->save($data); //已经有记录则更新下加入时间并且将拉黑次数加1
                }else{
                    $data['mobile'] = $val;
                    $data['add_time'] = time();
                    $data['update_time'] = time();
                    $data['is_delete'] = 0;
                    $data['black_count'] = 1;
                    $mobile_blacklist->add($data); // 没记录则插入数据
                }
            }
            $this->success("添加成功");
//            $this->redirect('Blacklist/add');
        }else{
            $this->error("请输入至少一个电话号码");
            exit;
        }
    }

    public function delete(){
        $ajax = intval($_REQUEST['ajax']);
        $id_arr = $_REQUEST['id'];
        $where = "id in (".$id_arr.")";
        $data['is_delete'] = 1;
        $data['remove_time'] = time();
        $res = M("mobile_blacklist")->where($where)->save($data);
        if($res){
            $this->success(l("DELETE_SUCCESS"), $ajax);
        }
    }

}