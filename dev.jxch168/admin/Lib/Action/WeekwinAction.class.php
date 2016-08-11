<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class WeekwinAction extends CommonAction{
	public function index()
	{
		$everwin = M("PeiziWeekwin")->find();
		$this->assign("everwin",$everwin);
		$this->display();
	}
	
	public function update_index()
	{
		
	}
	
}
?>