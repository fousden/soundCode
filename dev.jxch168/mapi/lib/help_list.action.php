<?php
class help_list{
	public function index(){
		$root = array();
		$cate_id = $GLOBALS['request']['cate_id'];
		$sql = 'select title,content from '.DB_PREFIX.'article where cate_id ='.$cate_id.' and is_effect = 1 and is_delete = 0 order by sort desc';

		$list = $GLOBALS['db']->getAll($sql);

		$root['item'] = $list;
		output($root);
	}
}