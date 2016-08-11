<?php
class LeadsMobile extends Action {

	public function _initialize(){
		$action = array(
			'permission'=>array(),
			'allow'=>array('radiolistdialog','getrole','checkrole','receive','allot','ajax','info')
		);
		B('AppAuthenticate', $action);
		$this->_permissionRes = getPerByAction(MODULE_NAME,ACTION_NAME);
		Global $role;
		$this->role = $role;
	}
	
	//线索列表
	public function index(){
		if($this->role == 1){
			$this->display('Public:role');			
			die();
		}
		/* if($this->isPost()){ */
			//获取权限
			$permission_list = apppermission(MODULE_NAME,ACTION_NAME);
			if($permission_list){
				$data['permission_list'] = $permission_list;
			}else{
				$data['permission_list'] = array();
			}
			getDateTime('leads');			
			$d_v_leads = D('LeadsView');
			$by = isset($_GET['by']) ? trim($_GET['by']) : '';
			$below_ids = getPerByAction('leads',ACTION_NAME,true);
			$outdays = M('config') -> where('name="leads_outdays"')->getField('value');
			$outdate = empty($outdays) ? 0 : time()-86400*$outdays;
			$where = array();
			$where['have_time'] = array('egt',$outdate);
			if(isset($_POST['search'])){
				$where['name'] = array('like','%'.trim($_POST['search']).'%');
			}		
			switch ($by) {			
				case 'sub' : $where['owner_role_id'] = array('in',implode(',', $below_ids)); break;
				case 'me' : $where['owner_role_id'] = session('role_id'); break;
				default :
					if($this->_get('by') == 'public'){
						getDateTime('leads_resource');
						unset($where['have_time']);
						$where['_string'] = "leads.owner_role_id=0 or leads.have_time < $outdate";
					}else{
						$where['owner_role_id'] = array('in',implode(',', $this->_permissionRes));
					}
				break;
			}
			$where['is_deleted'] = array('neq',1);
			$where['is_transformed'] = array('neq',1);
			if ($_REQUEST["name"] != "") {
				$where['name'] = array('like','%'.$_REQUEST["name"].'%');
			}
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			if($_GET['act'] == 'new'){
				$time_now = time();
				$compare_time = $time_now - 86400*3;
				$where['owner_role_id'] = array('in',implode(',', getSubRoleId()));
				$where['update_time'] = array('gt',$compare_time);
			}
			$list = $d_v_leads->where($where)->order('create_time desc')->page($p.',10')->select();
			foreach($list as $k=>$v){
				$list[$k]['user_name'] = M('user')->where('role_id = %d',$v['owner_role_id'])->getField('name');
				$owner_role_id = $v['owner_role_id'];
				//获取操作权限
				$list[$k]['permission'] = permissionlist(MODULE_NAME,$owner_role_id);
			}
			$list = empty($list) ? array() : $list;
			$count = $d_v_leads->where($where)->count();
			$page = ceil($count/10);
			$data['list'] = $list;
			$data['page'] = $page;
			$data['info'] = 'success'; 
			$data['status'] = 1; 			
			$this->ajaxReturn($data,'JSON');
		/* }else{
			$this->ajaxReturn('非法请求',"error",3);
		} */
	}
	//线索查看
	public function view(){
		if($this->role == 1){
			$this->display('Public:role');			
			die();
		}
		$leads_id =  isset($_GET['id']) ? intval($_GET['id']) : 0;
		$leads = D('LeadsView')->where('leads.leads_id = %d', $leads_id)->find();
		if (!$leads || $leads['is_deleted'] == 1) {   
			$this->type = 1;
			$this->display('Public:role');
			die();
        }
		$owner_role_id = $leads['owner_role_id'];
		//获取权限
		$leads['permission'] = permissionlist(MODULE_NAME,$owner_role_id);
		$outdays = M('config') -> where('name="leads_outdays"')->getField('value');
		$outdate = empty($outdays) ? 0 : time()-86400*$outdays;	
		$where['have_time'] = array('egt',$outdate);
		$where['owner_role_id'] = array('neq',0);
		$where['leads_id'] = $leads_id;
		
		if($leads['owner_role_id'] != 0 && ($leads['update_time'] > $outdate)){
			if(!in_array($leads['owner_role_id'], $this->_permissionRes)){
				$this->display('Public:role');
				die();
			}
		}
		//取得字段列表
		$field_list = M('Fields')->where('model = "leads"')->order('order_id')->select();
		$leads['owner'] = D('RoleView')->where('role.role_id = %d', $leads['owner_role_id'])->find();
		$leads['creator'] = D('RoleView')->where('role.role_id = %d', $leads['creator_role_id'])->find();
		$log_ids = M('rLeadsLog')->where('leads_id = %d', $leads_id)->getField('log_id', true);
		$leads['log'] = M('log')->where('log_id in (%s)', implode(',', $log_ids))->select();
		$log_count = 0;
		foreach ($leads['log'] as $key=>$value) {
			$leads['log'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['role_id'])->find();
			$log_count ++;
		}
		$leads['log_count'] = $log_count;
		//$this->statusList = M('BusinessStatus')->order('order_id')->select();
		$this->leads = $leads;
		$this->field_list = $field_list;
		$this->alert = parseAlert();
		$this->display();
	}
	//线索添加
	public function add(){
		if($this->role == 1){			
			$this->display('Public:role');
			die();
		}
		if($this->isPost()){
			$m_leads = D('Leads');
			$m_leads_data = D('LeadsData');
			$field_list = M('Fields')->where('model = "leads"  and in_add = 1')->order('order_id')->select();
			foreach ($field_list as $v){
				switch($v['form_type']) {
					case 'address':
						$a = array_filter($_POST[$v['field']]);
						$_POST[$v['field']] = !empty($a) ? implode(chr(10),$a) : '';
					break;
					case 'datetime':
						$_POST[$v['field']] = strtotime($_POST[$v['field']]);
					break;
					case 'box':
						eval('$field_type = '.$v['setting'].';');
						if($field_type['type'] == 'checkbox'){
							$b = array_filter($_POST[$v['field']]);
							$_POST[$v['field']] = !empty($b) ? implode(chr(10),$b) : '';
						}
					break;
				}
			}
			if($m_leads->create()){
				if($m_leads_data->create()!==false){
					if($_POST['nextstep_time']) $m_leads->nextstep_time = $_POST['nextstep_time'];
					$m_leads->create_time = time();
					$m_leads->update_time = time();
					$m_leads->creator_role_id = session('role_id');
					$m_leads->have_time = time();
					if ($leads_id = $m_leads->add()) {
						$m_leads_data->leads_id = $leads_id;
						$m_leads_data->add();
						actionLog($leads_id);
						$info['id'] = $leads_id;
						$info['info'] = '新线索添加成功!';
						if($_POST['submit'] == '保存') {
							alert('success', $info, U('leads/info'));
						} else {
							alert('success', $info, U('leads/info'));
						}
					} else {
						alert('error', $m_leads->getError().$m_leads_data->getError(),U('leads/info'));
					}
				}else{
					$this->error($m_leads_data->getError());
				}
			}else{
				$this->error($m_leads->getError());
			}
			
		}else{
			$role_id = session('role_id');
			$field_list = field_list_mobile_html("add","leads");
		 	$this->field_list = $field_list;
			//负责人列表
			$this->owner_name = owner_name_select($role_id);
			$this->display();
		}
	}
	//线索编辑
	public function edit(){
		if($this->role == 1){			
			$this->display('Public:role');
			die();
		}
		$leads_id =  isset($_GET['id']) ? intval($_GET['id']) : 0;
		$leads = D('LeadsView')->where('leads.leads_id = %d', $leads_id)->find();
		if (!$leads || $leads['is_deleted'] == 1) {
			$this->type = 1;
			$this->display('Public:role');
			die();
        }elseif(!in_array($leads['owner_role_id'], $this->_permissionRes)){
			$this->display('Public:role');
			die();
		}
		$field_list = M('Fields')->where('model = "leads"')->order('order_id')->select();
		if($this->isPost()){
			$m_leads = M('Leads');
			$m_leads_data = M('LeadsData');
			foreach ($field_list as $v){
				switch($v['form_type']) {
					case 'address':
						$_POST[$v['field']] = implode(chr(10),$_POST[$v['field']]);
					break;
					case 'datetime':
						$_POST[$v['field']] = strtotime($_POST[$v['field']]);
					break;
					case 'box':
						eval('$field_type = '.$v['setting'].';');
						if($field_type['type'] == 'checkbox'){
							$_POST[$v['field']] = implode(chr(10),$_POST[$v['field']]);
						}
					break;
				}
			}
			if($m_leads->create() && $m_leads_data->create()!==false){
				$m_leads->update_time = time();
				$a = $m_leads->where('leads_id= %d',$_REQUEST['id'])->save();
				$b = $m_leads_data->where('leads_id=%d',$_REQUEST['id'])->save();
				if($a && $b!==false) {
					actionLog($_REQUEST['id']);
					$info['info'] = '线索编辑成功！';
					$info['id'] = $leads_id;
					alert('success',$info , U('leads/info'));
				} else {
					alert('error', '线索编辑失败！',U('leads/info'));
				}
			}else{
				alert('error', $m_leads_data->getError());
			}
		}else{
			$leads['owner'] = D('RoleView')->where('role.role_id = %d', $leads['owner_role_id'])->find();
			$field_list = field_list_mobile_html("edit","leads",$leads);
			$this->field_list = $field_list;
			$this->leads = $leads;
			//负责人列表
			$this->owner_name = owner_name_select($leads['owner']['role_id']);
			$this->owner_role_id = $leads['owner']['role_id'];
			$this->display();
		}
	}
	//线索放入回收站
	public function delete(){
		if($this->role == 1){
			$this->ajaxReturn('','error',-2);
		}
		if($this->isPost()){
			$m_leads = M('leads');		
			$leads_id = intval($_REQUEST['id']);
			$leads = $m_leads->where('leads_id = %d',$leads_id)->find();			
			if(!$leads_id || !$leads){
				$this->ajaxReturn('','error',3);	//参数错误
			}elseif(!in_array($leads['owner_role_id'], $this->_permissionRes)){
				$this->ajaxReturn('您没有此权利!','error',-2);	//没有权限
			}
			$data = array('is_deleted'=>1, 'delete_role_id'=>session('role_id'), 'delete_time'=>time());
			if($m_leads->where('leads_id = %d', $leads_id)->setField($data)){
				actionLog($leads_id);
				$this->ajaxReturn('','success',1);	//删除成功
			}else{
				$this->ajaxReturn('','error',2);	//删除失败
			}
		}
	}
	
	public function info(){		
		$content = parseAlert();	
		if($content){
			foreach($content as $k=>$v){
				if($k == 'success'){					
					$this->model = 'leads';
					$this->id = $v[0]['id'];
					$this->content = $v[0]['info'];
					$this->display('Public:app_success');
				}
				if($k == 'error'){					
					$this->content = $v[0];
					$this->display('Public:role');
				}				
			}
		}			
	}
}