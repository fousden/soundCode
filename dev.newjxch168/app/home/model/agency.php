<?php

namespace home\model;

class Agency extends \base\model\frontend {
    public function getAgencyNameById($id){
        return $this->where(array("id"=>$id))->getField("name");
    }
}
