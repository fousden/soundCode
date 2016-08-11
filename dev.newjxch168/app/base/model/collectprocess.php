<?php

/*
 * 手于收集SQL，折分WHERE 条件，然后进行优化索引创建和使用时候强制WHERE条件排序
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace base\model;
use think\Model;
use think\Log;

class Collectprocess extends base
{

    public function runCollect()
    {
        $allLog = Log::getsortLog();
        if ($allLog)
        {
            foreach ($allLog as $row)
            {
//                var_dump($row);
//        exit;
            }
        }

    }

    public function addSql($sql, $runTime)
    {

    }

}
