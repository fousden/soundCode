<?php

/*
 * 日历
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarModel
 *
 * @author lujun
 */
class CalendarModel extends CommonModel
{

    /**
     * 日历   6，7，1，2，3，4，5
     * @param type $yearMonth  2015-10
     */
    function calendarMonth($yearMonth)
    {
        // 当月第一天是星期几
        $oneDayW     = date('N', strtotime($yearMonth . '-01'));
        // 当月一共有多少天
        $monthDayCnt = date('t', strtotime($yearMonth));

        $cTmpInfo = array();
        $w        = 0;
        for ($i = 1; $i <= $monthDayCnt; $i++) {

            if ($i == 1) {

                if ($oneDayW < 6) {
                    $wtEnd = $oneDayW + 1;
                } else {
                    $wtEnd = $oneDayW-5-1;
                }
                for ($wt = $wtEnd; $wt > 0; $wt--) {
                    $cTmpInfo[$w][] = date('Y-m-d', strtotime($yearMonth . '-01') - $wt * 24 * 60 * 60);
                }
            }
            $tDay           = date('Y-m-d', strtotime($yearMonth . "-" . $i));
            $cTmpInfo[$w][] = $tDay;
            if (date('N', strtotime($tDay)) == 5) {
                $w++;
            }
        }
        array_unshift($cTmpInfo, array('周六', '周日', '周一', '周二', '周三', '周四', '周五'));
        return $cTmpInfo;
    }

    /**
     * 上一个月
     * @param type $yearMonth 2015-10
     * @return type
     */
    function onMonth($yearMonth)
    {

        return date('Y-m', strtotime("-1 month", strtotime($yearMonth)));
    }

    /**
     * 下一个月
     * @param type $yearMonth 2015-05
     * @return type
     */
    function lastMonth($yearMonth)
    {
        return date('Y-m', strtotime("+1 month", strtotime($yearMonth)));
    }

}
