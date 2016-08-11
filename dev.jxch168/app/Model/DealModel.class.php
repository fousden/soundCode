<?php

/**
 * Description of UserModel
 *
 * @author dch
 */
class DealModel extends BaseModel {

    protected $tableName = 'deal';

    /**
     * 根据标的id查询一条标的数据
     * @param type $deal_id 标的id
     * @param type $field 要查询的字段
     * @return array 一维数组
     */
    public function getDealInfoByDealId($deal_id, $field = "*") {
        $sql_str = "select $field from " . $this->getTableName() . " where id=$deal_id";
        return $GLOBALS['db']->getRow($sql_str);
    }

}
