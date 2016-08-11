<?php

class DealLoadRepayModel extends BaseModel {

    protected $tableName = 'deal_load_repay';

    /**
     * 查询还款表中的一些数据
     * @param int $user_id 用户id
     * @param string $field 要查询的字段
     * @param string $where 查询的where条件
     * @param string $group_by 查询的分组条件
     * @return array 一维数组
     */
    public function getRepayInfoByUserId($user_id, $field = "*", $where = '', $group_by = "user_id") {
        $sql_str = "select $field from " . $this->getTableName() . " where user_id=$user_id ";
        if ($group_by) {
            $sql_str.=" group by $group_by ";
        }
        return $GLOBALS['db']->getRow($sql_str);
    }

}
