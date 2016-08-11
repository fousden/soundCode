<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class SchemeAction extends CommonAction{
	public function index()
	{
		$everwin = M("PeiziScheme")->find();
		$this->assign("everwin",$everwin);
		$this->display();
	}
	
	public function rate()
	{
		$everwin_rate = M("PeiziSchemeRateList")->find();
		$this->assign("everwin_rate",$everwin_rate);
		$this->display();
	}
	
	public function update_index()
	{
		
	}
	
	public function update_rate()
	{
	
	}
}
?>