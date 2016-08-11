<?php

/**
 * Description of UserModel
 *
 * @author dch
 */
class WeixinUserModel extends BaseModel {

    protected $tableName = 'weixin_user';

    /**
     * 根据微信的openid值获取平台用户信息
     * @param type $openid 微信的openid
     * @return type
     */
    public function getUserInfoByOpenid($openid) {
        $user_id = $this->getUserIdByOpenid($openid);
        return MO('User')->getUserInfoById($user_id);
    }

    /**
     * 根据微信的openid值获取平台用户id
     * @param type $openid  微信的openid
     * @return type
     */
    public function getUserIdByOpenid($openid) {
        $sql_str = "select user_id from " . $this->getTableName() . " where openid='$openid' limit 1";
        return $GLOBALS['db']->getOne($sql_str);
    }

    /**
     * 根据平台的用户id获取微信的Openid
     * @param type $user_id
     * @return string 返回微信的Openid值，查不到值返回false
     */
    public function getUserOpenidByUserId($user_id) {
        $sql_str = "select openid from " . $this->getTableName() . " where user_id='$user_id' limit 1";
        return $GLOBALS['db']->getOne($sql_str);
    }

    /**
     * 根据微信的openid更新weixin_user表中的openid值
     * @param int $user_id 用户id
     * @param string $openid 微信的openid
     * @return bool
     */
    public function updates($user_id, $openid) {
        $data['update_time'] = time();
        $data['openid'] = $openid;
        return $GLOBALS['db']->autoExecute($this->getTableName(), $data, "UPDATE", "user_id=" . $user_id);
    }

    /**
     * 往weixin_user表中插入相应的数据
     * @param int $user_id 用户id
     * @param sting $openid  微信的openid
     * @return bool
     */
    public function inserts($user_id, $openid) {
        $time = time();
        $data['create_time'] = $time;
        $data['update_time'] = $time;
        $data['openid'] = $openid;
        $data['user_id'] = $user_id;
        return $GLOBALS['db']->autoExecute($this->getTableName(), $data, "INSERT");
    }

    /**
     * 查询微信用户表中的数据
     * @param string $field 查询的字段
     * @param string $where 查询的where条件
     * @return array 二维数组
     */
    public function getWeixinUserList($field = "*", $where = '') {
        $sql_str = "select $field from " . $this->getTableName() . " where 1=1 ";
        return $GLOBALS['db']->getAll($sql_str);
    }

    /**
     * 更新openid的值
     * @param type $user_id 用户id
     * @param type $openid 微信的openid
     */
    public function updateOpenidBy($user_id, $openid) {
        $res = true;
        $user_openid = MO("WeixinUser")->getUserOpenidByUserId($user_id);
        if ($user_openid != $openid) {
            if ($user_openid) {
                $res = $this->updates($user_id, $openid);
            } else {
                $res = $this->inserts($user_id, $openid);
            }
        }
        return $res;
    }

}
