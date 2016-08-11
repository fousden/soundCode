<?php

class UserScoreModel extends BaseModel {

    protected $tableName = 'user_score';

    /**
     * 根据积分类型获取插入积分日志的配置文件
     * @param type $score_type 积分类型
     * @return array 一维数组
     */
    private static function get_add_score_data($score_type) {
        $conf = array(
            3 => array(
                'score_value' => 100,
                'remark' => '被邀请人每投资一笔邀请人获得100积分',
            ),
            5 => array(
                'score_value' => 500,
                'remark' => '实名获得',
            ),
        );
        return $conf[intval($score_type)];
    }

    /**
     * 向积分日志表中插入日志
     * @param type $user_id 用户id
     * @param type $score_type 积分类型
     * @return type bool 是否插入成功
     */
    public function add_score($user_id, $score_type) {
        $user_info = MO('User')->getUserInfoById($user_id, 'user_name,score');
        $score_data = self::get_add_score_data($score_type);
        $score_data['user_id'] = $user_id;
        $score_data['user_name'] = $user_info['user_name'];
        $score_data['start_time'] = time();
        $score_data['score_type'] = $score_type;
        $score_data['is_effect'] = 1;
        $res = $GLOBALS['db']->autoExecute($this->getTableName(), $score_data, "INSERT");
        if ($res) {
            $sql_str = 'update ' . DB_PREFIX . 'user set score= score + ' . $score_data['score_value'] . ' where id=' . $user_id;
            $res = $GLOBALS['db']->query($sql_str);
        }
        return $res;
    }

}
