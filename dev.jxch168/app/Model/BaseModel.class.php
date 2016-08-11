<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of baseModel
 *
 * @author lujun
 */
class BaseModel
{

    function getTableName()
    {
        return DB_PREFIX . $this->tableName;
    }

}
