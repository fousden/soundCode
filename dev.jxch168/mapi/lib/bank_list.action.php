<?php

/**
 * 富有可用的银行列表获取
 * 
 */
class bank_list{
    public function index(){
        $bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank  where fuyou_bankid != '' and is_rec = 1  ORDER BY is_rec DESC,sort DESC,id ASC");
        output($bank_list);
    }
}
?>