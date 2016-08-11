<?php

class DealLoadModel extends BaseModel {

    protected $tableName = 'deal_load';

    /**
     * 将该用户下的所有投资记录都标识成抽过奖的状态
     * @param int $user_id 用户id
     * @return bool
     */
    public function setWinnig($user_id,$where='') {
        $sql = "update " . DB_PREFIX . "deal_load set is_winning=1 where user_id=$user_id ".$where;
        return $GLOBALS['db']->query($sql);
    }

    /**
     * 根据记录id查询出投资记录的一条数据
     * @param type $load_id 投资记录id
     * @param type $field 要查询的字段
     * @return array 一维数组
     */
    public function getDealLoadInfoByLoadId($load_id, $field = "*") {
        $sql = "select $field from " . DB_PREFIX . "deal_load where id=$load_id ";
        return $GLOBALS['db']->getRow($sql);
    }

    /**
     * 查询投资记录中的一些数据
     * @param int $deal_id 标的id
     * @param string $field 要查询的字段
     * @param string $where 查询的where条件
     * @param string $group_by 查询的分组条件
     * @return array 二维数组
     */
    public function getDealLoadListByDealId($deal_id, $field = "*", $where = '', $group_by = "user_id") {
        $sql = "select $field from " . $this->getTableName() . " where deal_id=$deal_id $where ";
        if ($group_by) {
            $sql.=" group by $group_by ";
        }
        return $GLOBALS['db']->getAll($sql);
    }

}
