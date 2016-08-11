<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

//后台验证的基础类

class AuthAction extends BaseAction{
        //初始化函数
        public function _initialize() {
            parent::_initialize();
        }

	public function __construct()
	{
		parent::__construct();
		$this->check_auth();
	}

	/**
	 * 验证检限
	 * 已登录时验证用户权限, Index模块下的所有函数无需权限验证
	 * 未登录时跳转登录
	 */
	private function check_auth()
	{
		if(intval(app_conf("EXPIRED_TIME"))>0&&es_session::is_expired())
		{
			es_session::delete(md5(conf("AUTH_KEY")));
			es_session::delete("expire");
		}

		//管理员的SESSION
		$adm_session = es_session::get(md5(conf("AUTH_KEY")));
		$adm_name = $adm_session['adm_name'];
		$adm_id = intval($adm_session['adm_id']);
		$ajax = intval($_REQUEST['ajax']);
		$is_auth = 0;
		$user_info =  es_session::get("user_info");

		if(intval($user_info['id'])>0) //会员允许使用后台上传功能
		{
			if((MODULE_NAME=='File'&&ACTION_NAME=='do_upload')||(MODULE_NAME=='File'&&ACTION_NAME=='do_upload_img'))
			{
				$is_auth = 1;
			}
		}

		if($adm_id == 0&&$is_auth==0)
		{
			if($ajax == 0)
			$this->redirect("Public/login");
			else
			$this->error(L("NO_LOGIN"),$ajax);
		}

		//开始验证权限，当管理员名称不为默认管理员时
		//开始验证模块是否需要授权
		global $access_list;
		$access_list = require APP_ROOT_PATH."system/admnode_cfg.php";
		$count = 0;
		foreach($access_list as $k=>$v){
			if(strpos($k,",")){
				$k_str = explode(",",$k);
				if(in_array(MODULE_NAME,$k_str)){
					if($access_list[$k]['node'][ACTION_NAME]['module']==MODULE_NAME || isset($access_list[$k]['node'][MODULE_NAME."_".ACTION_NAME])){
						$count ++;
					}
				}
			}
			else{
				if(isset($access_list[MODULE_NAME]['node'][ACTION_NAME])){
					$count ++;
				}

			}
		}

		//$count = isset($access_list[MODULE_NAME]['node'][ACTION_NAME])?1:0;

		if($adm_name != app_conf("DEFAULT_ADMIN")&&$count>0&&$is_auth==0)
		{
			$sql = "select count(*) from ".DB_PREFIX."role_access as role left join ".
					DB_PREFIX."admin as admin on admin.role_id = role.role_id  ".
					"where admin.id = ".$adm_id." and role.node = '".ACTION_NAME."' and role.module = '".MODULE_NAME."' ";

			$count = $GLOBALS['db']->getOne($sql);

			if($count == 0)
			{
				//节点授权不足，开始判断是否有模块授权

				$module_sql =  "select count(*) from ".DB_PREFIX."role_access as role left join ".
						DB_PREFIX."admin as admin on admin.role_id = role.role_id ".
						"where admin.id = ".$adm_id." and role.node = '' and role.module = '".MODULE_NAME."' ";

				$module_count = $GLOBALS['db']->getOne($module_sql);
                                if(MODULE_NAME=="Index" && ACTION_NAME=='main'){
                                    $module_count=1;
                                }
				if($module_count == 0)
				{
					if((MODULE_NAME=='File'&&ACTION_NAME=='do_upload')||(MODULE_NAME=='File'&&ACTION_NAME=='do_upload_img'))
					{
						//echo "<script>alert('".L("NO_AUTH")."');</script>";
                                                $this->error(L("NO_AUTH"),$ajax);
						exit;
					}
					else{
                                            //判断该动作是否是查看私人资料，如果是则提示权限不足，并且刷新当前页面
                                            if(MODULE_NAME=="User" && ACTION_NAME=='private_info'){
                                                echo "<script>alert('".L("NO_AUTH")."'); location.reload();</script>";exit;
                                            }else{
                                                $this->error(L("NO_AUTH"),$ajax);
                                            }
                                        }

				}
			}
		}
	}

	//index列表的前置通知,输出页面标题
	public function _before_index()
	{
		$this->assign("main_title",L(MODULE_NAME."_INDEX"));
	}
	public function _before_trash()
	{
		$this->assign("main_title",L(MODULE_NAME."_INDEX"));
	}
}
?>