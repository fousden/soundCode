<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductSelectWidget
 *
 * @author lujun
 */
class ProductSelectWidget extends Widget
{

    public function render($data)
    {
            return $this->renderFile ("index", $menu);
    }

}
