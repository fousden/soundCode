<?php

/**
 * 后台共用model业务逻辑类
 *
 * @author jxch
 */

namespace base\model;

class Menu extends \base\model\backend{
    protected $options=array('field'=>array("id","name","pid","menu_level","module_name","controller","action_name","param","status","remark","sort","display"));

    protected $_validate = array(
        array('remark', 'require', '备注必须填写！')
    );
    protected $where=array(
        'status'=>'1',
    );
    protected $order=array(
        'sort'=>'desc',
    );
    
    public function get_menu_list(){
        return $this->where($this->where)->order($this->order)->select();
    }
    
    public function max_sort(){
        return $this->order(array("sort"=>"desc"))->getField("sort");
    }
    
    public function get_child_by_ids($ids){
        $where="pid in ($ids)";
        if($name=M('menu')->where($where)->getField('name')){
            return $name;
        }else{
            return false;
        }
    }

}
