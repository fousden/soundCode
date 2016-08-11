<?php
class MsgTemplateModel extends BaseModel {

    protected $tableName = 'msg_template';

    /**
     * 通过name值获取短信模板
     * @param type $name
     * @return content 短信模板的内容
     */
    public function getContentByName($name) {
	$sql = "select content from " . $this->getTableName() . " where name ='{$name}' limit 1";
	return $GLOBALS['db']->getOne($sql);
    }

}
