<?php
class help{
	public function index(){
		$root = array();

		$sql = "select id,title,icon from ".DB_PREFIX."article_cate where pid = ".$GLOBALS['m_config']['mobile_help_id']." and is_effect = 1 and is_delete = 0 order by sort desc";
		$list = $GLOBALS['db']->getAll($sql);

		$root['item'] = $list;
		
		output($root);
	}
}