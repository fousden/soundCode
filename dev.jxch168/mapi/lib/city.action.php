<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author ningchengzeng
 */
class city {
    
    public function index(){
        $id = intval($GLOBALS['request']['id']);
        
        $region = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."district_info where ParentCode = {$id}");
        output($region);
    }
}
