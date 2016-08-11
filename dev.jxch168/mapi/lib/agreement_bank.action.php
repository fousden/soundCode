<?php

/**
 *
 * 
 */
class agreement_bank{
	public function index(){
		$root =array();

		$agreement_bank_id = $GLOBALS['m_config']['agreement_bank'];
		$sql = 'select content from fanwe_article where id = '.$agreement_bank_id.' and is_effect = 1 and is_delete =0';
		$result = $GLOBALS['db']->getRow($sql);

		$root['content'] = $result['content'];
		output($root);
	}
}	