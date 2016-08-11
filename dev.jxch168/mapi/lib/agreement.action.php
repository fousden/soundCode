<?php

class agreement {

    public function index() {

	if ($type = $_REQUEST['type']) {
	    $root = array();
	    $agreement_bank_id = $GLOBALS['m_config']['agreement_' . $type];
	    $sql = 'select content from fanwe_article where id = ' . $agreement_bank_id . ' and is_effect = 1 and is_delete =0';
	    $result = $GLOBALS['db']->getRow($sql);
	    $root['content'] = $result['content'];
	    $root['type'] = $_REQUEST['type'];
	    output($root);
	}
    }

}
