<?php
class UserRewardModel extends BaseModel {

    protected $tableName = 'user_reward';

    /**
     * 通过用户id查询出该用户
     * @param type $user_id 用户id
     * @param type $field 查询的字段
     * @param type $where 查询的条件
     * 
     */
    public function getRewardInfoByUserId($user_id, $field = "*",$where=''){
        $sql_str="SELECT $field FROM ".$this->getTableName()." where user_id=$user_id $where ";
        return $GLOBALS['db']->getRow($sql_str);
    }

}