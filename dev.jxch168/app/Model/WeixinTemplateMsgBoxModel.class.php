<?php

class WeixinTemplateMsgBoxModel extends BaseModel {

    protected $tableName = 'weixin_template_msg_box';

    public function addTemplate($data){
        $data['create_time']=time();
        return $GLOBALS['db']->autoExecute($this->getTableName(), $data, "INSERT");
    }
    public function getTemplateList($field = "*", $where = '', $group_by = "user_id"){
        $sql_str="select $field from ".$this->getTableName()." where 1=1 $where";
        return $GLOBALS['db']->getAll($sql_str);
    }

}
