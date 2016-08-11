<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class BaseAction extends Action{
        //初始化函数
        public function _initialize() {
            //用户操作日志
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_id = intval($adm_session['adm_id']);
            $admin_info = M('admin')->find($adm_id);
            $data['admin_name'] = $admin_info['adm_name'];
            $data['admin_id'] = $admin_info['id'];
            $data['operate_type'] = 5;//操作类型：5 普通日志
            $data['request_data']= json_encode($_REQUEST);
            $data['module_name'] = MODULE_NAME;
            $data['action_name'] = ACTION_NAME;
            $data['operate_time'] = time();
            $data['operate_date'] = date('Y-m-d');
            $data['operate_ip'] =  get_client_ip();
            M('admin_log')->add($data);
        }

	//后台基础类构造
	protected $lang_pack;
	public function __construct()
	{
		parent::__construct();
		check_install();
		//重新处理后台的语言加载机制，后台语言环境配置于后台config.php文件
		$langSet = conf('DEFAULT_LANG');
		// 定义当前语言
		define('LANG_SET',strtolower($langSet));
		 // 读取项目公共语言包
		if (is_file(LANG_PATH.$langSet.'/common.php'))
		{
			L(include LANG_PATH.$langSet.'/common.php');
			$this->lang_pack = require LANG_PATH.$langSet.'/common.php';

			if(!file_exists(APP_ROOT_PATH."public/runtime/admin/lang.js"))
			{
				$str = "var LANG = {";
				foreach($this->lang_pack as $k=>$lang)
				{
					$str .= "\"".$k."\":\"".$lang."\",";
				}
				$str = substr($str,0,-1);
				$str .="};";
				file_put_contents(APP_ROOT_PATH."public/runtime/admin/lang.js",$str);
			}
		}
		es_session::close();
	}


	protected function error($message,$ajax = 0)
	{

		if(!$this->get("jumpUrl"))
		{
			if($_SERVER["HTTP_REFERER"]) $default_jump = $_SERVER["HTTP_REFERER"]; else $default_jump = u("Index/main");
			$this->assign("jumpUrl",$default_jump);
		}
		parent::error($message,$ajax);
	}
	protected function success($message,$ajax = 0)
	{

		if(!$this->get("jumpUrl"))
		{
			if($_SERVER["HTTP_REFERER"]) $default_jump = $_SERVER["HTTP_REFERER"]; else $default_jump = u("Index/main");
			$this->assign("jumpUrl",$default_jump);
		}
		parent::success($message,$ajax);
	}
}
?>