<?php
class agreement_reg{
	public function index(){
		$root =array();

		$agreement_reg_id = $GLOBALS['m_config']['agreement_reg'];

		$sql = 'select content from fanwe_article where id = '.$agreement_reg_id.' and is_effect = 1 and is_delete = 0';
		$result = $GLOBALS['db']->getRow($sql);
		$root['content'] = $result['content'];
		output($root);
	}
}