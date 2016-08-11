<?php

/**
 * IOS渠道数据分析
 * 
 */
class IosChannel {

    function __construct() {
        $conf_lists = require APP_ROOT_PATH . "data_conf/search_channel_config.php";
        foreach ($conf_lists['ios'] as $key => $val) {
            $default_channel_arr[] = array('channel' => $key, 'c' => 0);
            $default_channel_arr2[] = array('channel' => $key, 'moenys' => 0);
        }
        $this->default_channel_arr = $default_channel_arr;
        $this->default_channel_arr2 = $default_channel_arr2;
    }

//    private static $default_channel = array(
//        array('channel' => 'app_store', 'c' => 0),
//        array('channel' => 'limei', 'c' => 0),
//        array('channel' => 'dianru', 'c' => 0)
//    );

    /**
     *
     * IOS 渠道分析信息
     * 
     * @param  [type] $date [时间]
     */
    public function analysis($date) {
        $date_star = to_date(strtotime($date), "Ymd");
        $day = $date_star;
        //先删除当天的数据
        $GLOBALS['db']->query("delete from ".DB_PREFIX."statistical_mchannel_analysis where datetime='$date'");
        //$time统计开始时间，$timeb统计结束时间
        $time = strtotime($day . " 00:00:00");
        $timeb = strtotime($day . " +1 day");

        //下载
        $down_sql = "select `source` as channel,count(1) as c from fanwe_mobile_extension 
            where state >= 0 and type = 0 
            and create_time >= " . $time . " AND create_time< " . $timeb .
                " group by `source`";

        //激活
        $activity_sql = "select `source` as channel,count(1) as c from fanwe_mobile_extension 
            where state >= 1 and type = 0 
            and test_time >= " . $time . " AND test_time< " . $timeb .
                " group by `source`";

        //注册成功
        $reg_sql = "select `search_channel` as channel,count(1) as c from fanwe_user 
            where create_time >= " . $time . " AND create_time< " . $timeb .
                " and terminal=4 group by `search_channel`";

        //回调数据成功
        $buy_sql = "select `source` as channel,count(1) as c from fanwe_mobile_extension 
            where state = 3 and type = 0 
            and test_time >= " . $time . " AND test_time< " . $timeb .
                " group by `source`";

        //回调数据失败  
        $fbuy_sql = "select `source` as channel,count(1) as c from fanwe_mobile_extension 
            where state = 4 and type = 0 
            and test_time >= " . $time . " AND test_time< " . $timeb .
                " group by `source`";

        $downList = $GLOBALS['db']->getAll($down_sql, false);
        $activityList = $GLOBALS['db']->getAll($activity_sql, false);
        $regList = $GLOBALS['db']->getAll($reg_sql, false);
        $buyList = $GLOBALS['db']->getAll($buy_sql, false);
        $fbuyList = $GLOBALS['db']->getAll($fbuy_sql, false);
        $moneyList = $this->money($date);
        $moneysList = $this->moneys($date);

        $result = $this->margeSource($date, $downList, $activityList, $regList, $buyList, $fbuyList, $moneyList, $moneysList);

        foreach ($result as $key => $value) {
            $count = $GLOBALS['db']->getOne("select count(1) from " . DB_PREFIX . "statistical_mchannel_analysis where `datetime` = '" . $date .
                    "' and channel = '" . $value['channel'] . "'");
            if ($count == 0) {
                $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_mchannel_analysis", $value);
            } else {
                $GLOBALS['db']->autoExecute(DB_PREFIX . "statistical_mchannel_analysis", $value, "UPDATE", "`datetime` = '" . $date .
                        "' and channel = '" . $value['channel'] . "'");
            }
        }
    }

    /**
     * 查询ios渠道用户当天投资金额
     * @param  [type] $time  [description]
     * @param  [type] $timeb [description]
     * @return [type]        [description]
     */
    private function money($date) {
        $sql_str = "SELECT
                    search_channel as channel,
                    sum(dl.money) as c
                    FROM
                            `fanwe_user` AS u
                    LEFT JOIN fanwe_deal_load AS dl ON (u.id = dl.user_id and dl.create_date='$date')
                    WHERE
                            u.terminal = 4 and u.create_date='$date'
                    GROUP BY
                            u.search_channel";
        $item = $GLOBALS['db']->getAll($sql_str);
        $deal_data = $this->default_channel_arr;
        foreach ($item as $key => $val) {
            $deal_data[$key] = $val;
        }
        return $deal_data;
//        $deal_load = "select deal_load.user_id,
//                            group_concat(deal_load.money order by deal_load.create_time asc) moneys,
//                            group_concat(deal_load.create_time order by deal_load.create_time asc) times
//                        from ".DB_PREFIX."deal_load deal_load " .
//                        " where exists(select 1 from ".DB_PREFIX."user u where u.id = deal_load.user_id and u.create_time >= ".$time.
//                        ") group by deal_load.user_id order by deal_load.user_id";
//        
//        $user_extension = "select user.id,user.search_channel as channel
//                        from ".DB_PREFIX."user user,".DB_PREFIX."mobile_extension extension
//                    where upper(extension.`source`) = upper(user.search_channel) and upper(extension.udid) = upper(user.mobile_id)
//                     and type = 0 and user.id = ";
//
//        $deal_load_list = $GLOBALS['db']->getAll($deal_load, false);
////        echo '<pre>';var_dump($deal_load_list);echo '</pre>';die;
//        $item = array();
//        foreach ($deal_load_list as $key => $value) {
//            $firstTime = explode(',',$value["times"],2)[0];
//            if($firstTime>=$time && $firstTime<$timeb){
//                $user_data = $GLOBALS['db']->getRow($user_extension.$value["user_id"] , false);
//                if($user_data){
//                    $item[$user_data['channel']] += explode(',',$value["moneys"],2)[0];
//                }
//            }
//        }
//        
//        $deal_data = $this->default_channel_arr;
//        $index = 0;
//        foreach ($item as $key => $value) {
//            $deal_data[$index]["channel"] = $key;
//            $deal_data[$index]['c'] = $value;
//        }
//        return $deal_data;
    }

    /**
     * 查询ios渠道用户当天首次投资非当天注册的user_id
     * @param  [type] $time  [description]
     * @param  [type] $timeb [description]
     * @return [type]        [description]
     */
    private function moneys($date) {
        //当天注册的user_id
        $sql_str = "select id from fanwe_user where create_date='$date'";
        $user_id_arr = $GLOBALS['db']->getAll($sql_str);
        if (!$user_id_arr) {
            return $this->default_channel_arr2;
        }
        $userIds = array_map('array_shift', $user_id_arr);


        //当天投资非当天注册的user_id
        $sql_str = "select user_id from fanwe_deal_load where create_date='$date' and user_id not in(" . implode(",", $userIds) . ") group by user_id";
        $user_id_arr = $GLOBALS['db']->getAll($sql_str);
        if (!$user_id_arr) {
            return $this->default_channel_arr2;
        }
        $userIds = array_map('array_shift', $user_id_arr);


        //当天投资非当天注册并且之前没有投资的user_id
        $sql_str = "select user_id from fanwe_deal_load where user_id in(" . implode(",", $userIds) . ") and create_time>=" . strtotime($date) . " group by user_id";
        $user_id_arr = $GLOBALS['db']->getAll($sql_str);
        if (!$user_id_arr) {
            return $this->default_channel_arr2;
        }
        $userIds2 = array_map('array_shift', $user_id_arr);
        //当天首次投资的user_id
        $user_id_arr = array_diff($userIds, $userIds2);
        if (!$user_id_arr) {
            return $this->default_channel_arr2;
        }
        $ids = implode(",", $user_id_arr);
        $sql_str = "SELECT
                    search_channel as channel,
                    sum(dl.money) as moneys
                    FROM
                            `fanwe_user` AS u
                    LEFT JOIN fanwe_deal_load AS dl ON (u.id = dl.user_id and dl.create_date='$date' and dl.user_id in ($ids))
                    WHERE
                            u.terminal = 4 and u.id in ($ids)
                    GROUP BY
                            u.search_channel";
        $item = $GLOBALS['db']->getAll($sql_str);
        $deal_data = $this->default_channel_arrs;
        foreach ($item as $key => $val) {
            $deal_data[$key] = $val;
        }
        return $deal_data;
    }

    /**
     * 合并渠道分析数据
     * 
     * @param  [array] $date         [时间]
     * @param  [array] $downList     [下载]
     * @param  [array] $activityList [激活]
     * @param  [array] $regList      [注册]
     * @param  [array] $buyList      [购买]
     * @param  [array] $fbuyList     [购买回调失败]
     * @param  [array] $moneyList    [天购买金额]
     * @return [array]               [合成数据]
     */
    private function margeSource($date, $downList, $activityList, $regList, $buyList, $fbuyList, $moneyList, $moneysList) {
        $year = to_date(strtotime($date), "Y");
        $month = to_date(strtotime($date), "m");

        if (count($downList) == 0) {
            $downList = $this->default_channel_arr;
        }
        if (count($activityList) == 0) {
            $activityList = $this->default_channel_arr;
        }
        if (count($regList) == 0) {
            $regList = $this->default_channel_arr;
        }
        if (count($buyList) == 0) {
            $buyList = $this->default_channel_arr;
        }
        if (count($fbuyList) == 0) {
            $fbuyList = $this->default_channel_arr;
        }
        if (count($fbuyLists) == 0) {
            $fbuyList = $this->default_channel_arr;
        }

        $tmp = $this->default_channel_arr;
        $result = array();

        foreach ($tmp as $key => $value) {
            $item = $this->searchSource($downList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['down'] = $item['c'];
                } else {
                    $value['down'] = 0;
                }
            } else {
                $value['down'] = 0;
            }

            $item = $this->searchSource($activityList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['activity'] = $item['c'];
                } else {
                    $value['activity'] = 0;
                }
            } else {
                $value['activity'] = 0;
            }

            $item = $this->searchSource($regList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['register'] = $item['c'];
                } else {
                    $value['register'] = 0;
                }
            } else {
                $value['register'] = 0;
            }

            $item = $this->searchSource($buyList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['buy'] = $item['c'];
                } else {
                    $value['buy'] = 0;
                }
            } else {
                $value['buy'] = 0;
            }

            $item = $this->searchSource($fbuyList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['ofbuy'] = $item['c'];
                } else {
                    $value['ofbuy'] = 0;
                }
            } else {
                $value['ofbuy'] = 0;
            }

            $item = $this->searchSource($moneyList, $value);
            if ($item) {
                if ($item['c']) {
                    $value['buymoney'] = $item['c'];
                } else {
                    $value['buymoney'] = 0;
                }
            } else {
                $value['buymoney'] = 0;
            }

            $item = $this->searchSource($moneysList, $value);
            if ($item) {
                if ($item['moneys']) {
                    $value['moneys'] = $item['moneys'];
                } else {
                    $value['moneys'] = 0;
                }
            } else {
                $value['moneys'] = 0;
            }

            $value['datetime'] = $date;
            $value['year'] = $year;
            $value['month'] = $month;

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 查找渠道信息
     * @param  [type] $data  [分析信息]
     * @param  [type] $value [匹配信息]
     * @return [type]        [匹配数据]
     */
    private function searchSource($data, $value) {
        foreach ($data as $key => $item) {
            if ($item['channel'] == $value['channel']) {
                return $item;
            }
        }
        return false;
    }

}

?>