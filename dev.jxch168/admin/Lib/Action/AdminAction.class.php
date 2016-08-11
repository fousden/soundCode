<?php

// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------

class AdminAction extends CommonAction
{

    public function index()
    {
        $adm_name   = $_REQUEST['adm_name'];
        $role       = $_REQUEST['role'];
        $begin_time = $_REQUEST['begin_time'];
        $end_time   = $_REQUEST['end_time'];
        //按管理员姓名搜索
        if ($adm_name) {
            $where['adm_name'] = $adm_name;
        }
        //按管理员所属组搜索
        if ($role) {
            $role_id          = M("Role")->where(array("name" => $role))->getField('id');
            $where['role_id'] = $role_id;
        }
        if ($begin_time) {
            $where['login_time'] = array("gt", $begin_time);
        }
        if ($end_time) {
            $where['login_time'] = array("lt", $end_time);
        }
        $this->assign("default_map", $where);
        parent::index();
    }

    public function insert()
    {
        B('FilterString');
        $data = M(MODULE_NAME)->create();
        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/add"));
        if (!check_empty($data['adm_name'])) {
            $this->error(L("ADM_NAME_EMPTY_TIP"));
        }
        if (!check_empty($data['adm_password'])) {
            $this->error(L("ADM_PASSWORD_EMPTY_TIP"));
        }
        //                if(!check_empty($data['oper_password']))
        //		{
        //			$this->error(L("管理员操作密码不能为空"));
        //		}
        if ($data['role_id'] == 0) {
            $this->error(L("ROLE_EMPTY_TIP"));
        }
        if (M("Admin")->where("adm_name='" . $data['adm_name'] . "'")->count() > 0) {
            $this->error(L("ADMIN_EXIST_TIP"));
        }
        // 更新数据
        $log_info             = $data['adm_name'];
        $data['adm_password'] = md5(trim($data['adm_password']));
        $data['mobile']       = trim($data['mobile']);
        //$data['oper_password'] = md5(trim($data['oper_password']));
        $list                 = M(MODULE_NAME)->add($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info . L("INSERT_SUCCESS"), 1);
            $this->success(L("INSERT_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info . L("INSERT_FAILED"), 0);
            $this->error(L("INSERT_FAILED"));
        }
    }

    public function trash()
    {
        $condition['is_delete'] = 1;
        $this->assign("default_map", $condition);
        parent::index();
    }

    public function update()
    {
        B('FilterString');
        $data     = M(MODULE_NAME)->create();
        $log_info = M(MODULE_NAME)->where("id=" . intval($data['id']))->getField("adm_name");

        //开始验证有效性
        $this->assign("jumpUrl", u(MODULE_NAME . "/edit", array("id" => $data['id'])));
        if (!check_empty($data['adm_password'])) {
            unset($data['adm_password']);  //不更新密码
        } else {
            $data['adm_password'] = md5(trim($data['adm_password']));
        }
        //                if(!check_empty($data['oper_password']))
        //		{
        //			unset($data['oper_password']);  //不更新密码
        //		}
        //		else
        //		{
        //			$data['oper_password'] = md5(trim($data['oper_password']));
        //		}
        if ($data['role_id'] == 0) {
            $this->error(L("ROLE_EMPTY_TIP"));
        }
        if (conf("DEFAULT_ADMIN") == $log_info) {
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_name    = $adm_session['adm_name'];
            if ($log_info != $adm_name)
                $this->error(l("DEFAULT_ADMIN_CANNOT_MODIFY"));

            if ($data['is_effect'] == 0) {
                $this->error(l("DEFAULT_ADMIN_CANNOT_EFFECT"));
            }
        }
        // 更新数据
        $list = M(MODULE_NAME)->save($data);
        if (false !== $list) {
            //成功提示
            save_log($log_info . L("UPDATE_SUCCESS"), 1);
            $this->success(L("UPDATE_SUCCESS"));
        } else {
            //错误提示
            save_log($log_info . L("UPDATE_FAILED"), 0);
            $this->error(L("UPDATE_FAILED"), 0, $log_info . L("UPDATE_FAILED"));
        }
    }

    public function add()
    {
        //查询部门列表
        $adm_sql  = " SELECT * FROM " . DB_PREFIX . "admin WHERE is_delete= 0 and is_effect=1 and is_department = 1";
        $adm_list = $GLOBALS['db']->getAll($adm_sql);
        $this->assign('departs', $adm_list);

        //输出分组列表
        $this->assign("role_list", M("Role")->where("is_delete = 0")->findAll());
        $this->display();
    }

    //保存管理员操作密码
    public function saveOperPwd()
    {
        $oper_password         = strim($_REQUEST['oper_password']);
        $confirm_oper_password = strim($_REQUEST['confirm_oper_password']);
        $oper_admin_id         = strim($_REQUEST['oper_admin_id']);
        if ($oper_password != $confirm_oper_password) {
            $result['status'] = 0;
            $result['info']   = "密码不一致，请重新输入！";
            $this->ajaxReturn($result, $result['info'], 0);
        }
        $data['oper_password'] = md5(trim($oper_password));
        $data['id']            = $oper_admin_id;
        $up_id                 = M(MODULE_NAME)->save($data);
        if ($up_id) {
            $result['status'] = 1;
            $result['info']   = "管理员操作密码设置成功！";
            $this->ajaxReturn($result, $result['info'], 1);
        } else {
            $result['status'] = 0;
            $result['info']   = "管理员操作密码设置失败！";
            $this->ajaxReturn($result, $result['info'], 0);
        }
    }

    public function edit()
    {
        $id                     = intval($_REQUEST ['id']);
        $condition['is_delete'] = 0;
        $condition['id']        = $id;
        $vo                     = M(MODULE_NAME)->where($condition)->find();
        $this->assign('vo', $vo);

        //查询部门列表
        $adm_sql  = " SELECT * FROM " . DB_PREFIX . "admin WHERE is_delete= 0 and is_effect=1 and is_department = 1";
        $adm_list = $GLOBALS['db']->getAll($adm_sql);

        $this->assign('departs', $adm_list);

        $this->assign("role_list", M("Role")->where("is_delete = 0")->findAll());
        $this->display();
    }

//相关操作
    public function set_effect()
    {
        $id          = intval($_REQUEST['id']);
        $ajax        = intval($_REQUEST['ajax']);
        $info        = M(MODULE_NAME)->where("id=" . $id)->getField("adm_name");
        $c_is_effect = M(MODULE_NAME)->where("id=" . $id)->getField("is_effect");  //当前状态
        if (conf("DEFAULT_ADMIN") == $info) {
            $this->ajaxReturn($c_is_effect, l("DEFAULT_ADMIN_CANNOT_EFFECT"), 1);
        }
        $n_is_effect = $c_is_effect == 0 ? 1 : 0; //需设置的状态
        M(MODULE_NAME)->where("id=" . $id)->setField("is_effect", $n_is_effect);
        save_log($info . l("SET_EFFECT_" . $n_is_effect), 1);
        $this->ajaxReturn($n_is_effect, l("SET_EFFECT_" . $n_is_effect), 1);
    }

    public function delete()
    {
        //删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id   = $_REQUEST ['id'];
        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data  = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['adm_name'];
                if (conf("DEFAULT_ADMIN") == $data['adm_name']) {
                    $this->error($data['adm_name'] . l("DEFAULT_ADMIN_CANNOT_DELETE"), $ajax);
                }
            }
            if ($info)
                $info = implode(",", $info);
            $list = M(MODULE_NAME)->where($condition)->setField('is_delete', 1);
            if ($list !== false) {
                save_log($info . l("DELETE_SUCCESS"), 1);
                $this->success(l("DELETE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("DELETE_FAILED"), 0);
                $this->error(l("DELETE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function restore()
    {
        //删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id   = $_REQUEST ['id'];
        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data  = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['adm_name'];
            }
            if ($info)
                $info = implode(",", $info);
            $list = M(MODULE_NAME)->where($condition)->setField('is_delete', 0);
            if ($list !== false) {
                save_log($info . l("RESTORE_SUCCESS"), 1);
                $this->success(l("RESTORE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("RESTORE_FAILED"), 0);
                $this->error(l("RESTORE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function foreverdelete()
    {
        //彻底删除指定记录
        $ajax = intval($_REQUEST['ajax']);
        $id   = $_REQUEST ['id'];
        if (isset($id)) {
            $condition = array('id' => array('in', explode(',', $id)));
            $rel_data  = M(MODULE_NAME)->where($condition)->findAll();
            foreach ($rel_data as $data) {
                $info[] = $data['adm_name'];
                if (conf("DEFAULT_ADMIN") == $data['adm_name']) {
                    $this->error($data['adm_name'] . l("DEFAULT_ADMIN_CANNOT_DELETE"), $ajax);
                }
            }
            if ($info)
                $info = implode(",", $info);
            $list = M(MODULE_NAME)->where($condition)->delete();
            if ($list !== false) {
                save_log($info . l("FOREVER_DELETE_SUCCESS"), 1);
                $this->success(l("FOREVER_DELETE_SUCCESS"), $ajax);
            } else {
                save_log($info . l("FOREVER_DELETE_FAILED"), 0);
                $this->error(l("FOREVER_DELETE_FAILED"), $ajax);
            }
        } else {
            $this->error(l("INVALID_OPERATION"), $ajax);
        }
    }

    public function set_default()
    {
        $adm_id = intval($_REQUEST['id']);
        $admin  = M("Admin")->getById($adm_id);
        if ($admin) {
            M("Conf")->where("name = 'DEFAULT_ADMIN'")->setField("value", $admin['adm_name']);
            //开始写入配置文件
            $sys_configs = M("Conf")->findAll();
            $config_str  = "<?php\n";
            $config_str .= "return array(\n";
            foreach ($sys_configs as $k => $v) {
                $config_str.="'" . $v['name'] . "'=>'" . addslashes($v['value']) . "',\n";
            }
            $config_str.=");\n ?>";

            $filename = get_real_path() . "public/sys_config.php";

            if (!$handle = fopen($filename, 'w')) {
                $this->error(l("OPEN_FILE_ERROR") . $filename);
            }


            if (fwrite($handle, $config_str) === FALSE) {
                $this->error(l("WRITE_FILE_ERROR") . $filename);
            }

            fclose($handle);


            save_log(l("CHANGE_DEFAULT_ADMIN"), 1);
            clear_cache();
            $this->success(L("SET_DEFAULT_SUCCESS"));
        } else {
            $this->error(L("NO_ADMIN"));
        }
    }

}

?>