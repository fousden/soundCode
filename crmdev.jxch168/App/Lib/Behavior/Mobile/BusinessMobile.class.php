<?php
class BusinessMobile extends Action {

	public function _initialize(){
		$action = array(
			'permission'=>array(),
			'allow'=>array('addproduct','ajax','info')
		);
		B('AppAuthenticate', $action);
		$this->_permissionRes = getPerByAction(MODULE_NAME,ACTION_NAME);
		Global $role;
		$this->role = $role;
	}
	
	public function info(){		
		$content = parseAlert();	
		if($content){
			// var_dump($content);
			foreach($content as $k=>$v){
				if($k == 'success'){					
					$this->model = 'Business';
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
	
	public function ajax(){
		//选择客户		
		$m_customer = M('Customer');
		$m_contacts = M('Contacts');
		$m_r_contacts_customer = M('RContactsCustomer');
		$underling_ids = getSubRoleId();
		$customer = $m_customer->where('owner_role_id in (%s) and is_deleted = 0',implode(',',$underling_ids))->field('name,customer_id,contacts_id')->order('create_time desc')->limit(10)->select();
		foreach($customer as $k=>$v){
			//如果存在首要联系人，则查出首要联系人。否则查出联系人中第一个。
			if(!empty($v['contacts_id'])){
				$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$v['contacts_id'])->field('name')->find();
				$customer[$k]['contacts_name'] = $contacts['name'];
			}else{
				$contacts_customer = $m_r_contacts_customer->where('customer_id = %d',$v['customer_id'])->limit(1)->field('name,contacts_id')->order('id desc')->select();
				$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$contacts_customer[0]['contacts_id'])->find();
				$customer[$k]['contacts_id'] = $contacts['contacts_id'];
				$customer[$k]['contacts_name'] = $contacts['name'];
			}
		}			
		$this->customerList = $customer;
		$count = $m_customer->where('owner_role_id in (%s) and is_deleted = 0',implode(',',$underling_ids))->count();
		$this->total = $count%10 > 0 ? ceil($count/10) : $count/10;		
		$this->display();
	}
	
	public function add(){
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}
		if($this->isPost()){
			$m_business = D('Business');
			$m_business_data = D('BusinessData');
			$field_list = M('Fields')->where('model = "business" and in_add = 1')->order('order_id')->select();
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
							$a =array_filter($_POST[$v['field']]);
							$_POST[$v['field']] = !empty($a) ? implode(chr(10),$a) : '';
						}
					break;
				}
			}			
			if($m_business->create() && $m_business_data->create()!==false){
				$m_business->create_time = $m_business->update_time = time();
				$m_business->creator_role_id = $m_business->update_role_id = session('role_id');
				if($business_id = $m_business->add()){
					$m_business_data->business_id = $business_id;
					if($m_business_data->add()){						
						actionLog($business_id);
						$info['info'] = '新商机添加成功！';
						$info['id'] = $business_id;
						if($_POST['submit'] == L('SAVE')) {
							alert('success', $info, U('Business/info'));
						} else {
							alert('success', $info, U('Business/info'));
						}						
					}else{
						$m_business->where(array('business_id'=>$business_id))->delete();
						alert('error','添加商机失败！', U('Business/info'));
					}
				} else {
					alert('error','添加商机失败！', U('Business/info'));
				}
			}else{
				alert('error', $m_business->getError().$m_business_data->getError(),U('Business/info'));
			}
		}else{
			$this->field_list = field_list_mobile_html('add','business');			
			//负责人列表
			$this->owner_name = owner_name_select();		
			$this->display();
		}
	}
	
	//商机放入回收站
	public function delete(){
		if($this->role == 1){
			$this->ajaxReturn('','error',-2);
		}
		if($this->isPost()){
			$m_business = M('business');			
			$business = $m_business ->where('business_id = %d',$this->_request('business_id'))->find();				
			if (!$business || !$this->_request('business_id')) {          
				$this->ajaxReturn('','error',3);	//参数错误
			}elseif(!in_array($business['owner_role_id'], $this->_permissionRes)){
				$this->ajaxReturn('','error',-2);	//没有权限
			}					
			$data = array('is_deleted'=>1, 'delete_role_id'=>session('role_id'), 'delete_time'=>time());
			if($m_business->where('business_id = %d', $business['business_id'])->setField($data)){	
				actionLog($business['business_id']);
				$this->ajaxReturn('','success',1);	//删除成功
			} else {
				$this->ajaxReturn('','error',2);	//删除失败
			}						
		}
	}
	
	//编辑商机
	public function edit(){	
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}
		
		$v_business = D('BusinessView');
		$business = $v_business ->where('business.business_id = %d',$this->_request('id'))->find();	
		
		
		if (!$business) {          
			$this->type = 1;
			$this->display('Public:role');
			die();
        }elseif(!in_array($business['owner_role_id'], $this->_permissionRes)){
			$this->display('Public:role');
			die();
		}	
		
        $field_list = M('Fields')->where('model = "business"')->order('order_id')->select();
		$business_id=$_POST['business_id'] ? intval($_POST['business_id']) : intval($_GET['id']);		
		if($this->isPost()){
			$m_business = D('business');
			$m_business_data = D('BusinessData');
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
			if($m_business->create() && $m_business_data->create()!==false){
				$m_business->update_time = time();
				$a = $m_business->where('business_id=' . $business['business_id'])->save();
				$b = $m_business_data->where('business_id=' . $business['business_id'])->save();
				if($a && $b!==false) {
					actionLog($business['business_id']);
					$info['info'] = '商机编辑成功!';
					$info['id'] = $business['business_id'];
					alert('success',$info, U('Business/info'));
				} else {
					alert('error', '修改商机信息失败!',U('Business/info'));
				}
			}else{
				alert('error', $m_business->getError().$m_business_data->getError(),U('business/info'));
			}
		}else{
			$business['owner'] = getUserByRoleId($business['owner_role_id']);
			$this->business = $business;		
			$this->field_list = field_list_mobile_html('edit','business',$business);	
			
			//负责人列表
			$this->owner_name = owner_name_select($business['owner']['role_id']);				
			$this->display();
		}
	}
	
	
	public function index(){
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}
		if($this->isPost()){
			getDateTime('business');
			$d_v_business = D('BusinessView');
			$below_ids = getPerByAction('business',ACTION_NAME,true);			
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$by = isset($_GET['by']) ? trim($_GET['by']) : '';
			$where = array();			
			$order = "";		
			switch ($by) {
				case 'create' : $where['creator_role_id'] = session('role_id'); break;
				case 'sub' : $where['owner_role_id'] = array('in',implode(',', $below_ids)); break;
				case 'subcreate' : $where['creator_role_id'] = array('in',implode(',', $below_ids)); break;		
				case 'me' : $where['business.owner_role_id'] = session('role_id'); break;				
			}
			$where['business.is_deleted'] = 0;	
			if ($_REQUEST["name"]) {
				$where['name'] = array('like','%'.$_REQUEST["name"].'%');
			}
			if (!isset($where['business.owner_role_id'])) {
				$where['business.owner_role_id'] = array('in', $this->_permissionRes);
			}
			$order = empty($order) ? 'business.update_time desc' : $order;
			if($_GET['act'] == 'new'){
				$time_now = time();
				$compare_time = $time_now - 86400*3;
				$where['owner_role_id'] = array('in',implode(',', getSubRoleId()));
				$where['update_time'] = array('gt',$compare_time);
			}
			$list = $d_v_business->where($where)->field('name,business_id,total_price,customer_id,owner_role_id')->order($order)->page($p.',10')->select();
			foreach($list as $k=>$v){
				$list[$k]['customer_name'] = M('Customer')->where('customer_id = %d',$v['customer_id'])->getField('name');
				$owner_role_id = $v['owner_role_id'];
				//获取操作权限
				$list[$k]['permission'] = permissionlist(MODULE_NAME,$owner_role_id);
			}
			$list = empty($list) ? array() : $list;
			$count = $d_v_business->where($where)->count();
			$page = ceil($count/10);
			$data['list'] = $list;
			$data['page'] = $page;
			$data['info'] = 'success';
			$data['status'] = 1;
			$this->ajaxReturn($data,'JSON');
		}else{
			$this->ajaxReturn('','error',0);
		}
	}
	
	
	public function view(){
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}
		$business_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$business = D('BusinessView')->where('business.business_id = %d', $business_id)->find();	
		if (!$business || $business['is_deleted'] == 1) { 
			$this->type = 1;
			$this->display('Public:role');
			die();
        }elseif(!in_array($business['owner_role_id'], $this->_permissionRes)){
			$this->display('Public:role');
			die();
		}			
		//取得字段列表
		$field_list = M('Fields')->where('model = "business"')->order('order_id')->select();
		
		//查询固定信息
		$business['owner'] = D('RoleView')->where('role.role_id = %d', $business['owner_role_id'])->find();
		$business['create'] = D('RoleView')->where('role.role_id = %d', $business['creator_role_id'])->find();
		$business['status_id'] = M('BusinessStatus')->where('status_id = %d', $business['status_id'])->getField('name');			
		if($business['contacts_id']) $business['contacts_name'] = M('contacts')->where('contacts_id = %d', $business['contacts_id'])->getField('name');
		//日程统计
		$event_ids = M('rBusinessEvent')->where('business_id = %d', $business_id)->getField('event_id', true);
		$event_count = M('event')->where('event_id in (%s)', implode(',', $event_ids))->count();
		$business['event_count'] = empty($event_count)? 0 : $event_count;
		
		//产品统计
		$product_count =  M('rBusinessProduct')->where('business_id = %d', $business_id)->count();
		$business['product_count'] = empty($product_count)? 0 : $product_count;
		
		//客户
		$business['customer'] = M('Customer')->where('customer_id = %d and is_deleted=0', $business['customer_id'])->find();		
		
		//判断客户是否为客户池中
		$outdays = M('config')->where('name="cutomer_outdays"')->getField('value');
		$outdate = empty($outdays) ? 0 :time()-86400*$outdays;
		if($business['customer']['owner_role_id'] == 0 || ($business['customer']['update_time'] < $outdate && $business['customer']['id_locked'] = 0)){
			$this->flag = 1;
		}else{
		    $this->flag = 0;
		}
		
		//文件统计
		$file_ids = M('rBusinessFile')->where('business_id = %d', $business_id)->getField('file_id', true);
		$business['file'] = M('file')->where('file_id in (%s)', implode(',', $file_ids))->select();
		$file_count = 0;
		foreach ($business['file'] as $key=>$value) {
			$business['file'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['role_id'])->find();
			$business['file'][$key]['file_path'] = U('file/filedownload','path='.urlencode($value['file_path']).'&name='.urlencode($value['name']));
			$file_count ++;
		}
		
		$business['file_count'] = $file_count;
		
		//任务统计
		$task_ids = M('rBusinessTask')->where('business_id = %d', $business_id)->getField('task_id', true);
		$business['task'] = M('task')->where('task_id in (%s) and is_deleted=0', implode(',', $task_ids))->select();
		$task_count = 0;
		foreach ($business['task'] as $key=>$value) {
			$business['task'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['owner_role_id'])->find();
			$task_count ++;
		}
		$business['task_count'] = $task_count;
		//获取权限
		$business['permission'] = permissionlist(MODULE_NAME,$business['owner_role_id']);
		$this->business = $business;
		$this->field_list = $field_list;				
		$this->display();
		
	}
	//选择商机列表
	public function business_list(){
		if($this->isPost()){
			$d_v_business = D('BusinessView');
			$m_customer = M('customer');
			$m_contacts = M('contacts');
			$m_r_contacts_customer = M('RContactsCustomer');
			$below_ids = getPerByAction(MODULE_NAME,ACTION_NAME,true);
			$where = array();
			if(isset($_POST['search'])){
				$where['name'] = array('like','%'.trim($_POST['search']).'%');
			}	
			$p = isset($_GET['p']) ? intval($_GET['p']) : 1 ;
			$by = isset($_GET['by']) ? trim($_GET['by']) : '';
			$order = "create_time desc";
			$where['business.status_id'] = array(array('neq', 99), array('neq', 100), 'and');
			$where['owner_role_id'] = array('in', $this->_permissionRes);
			$where['is_deleted'] = 0;	
			$order = empty($order) ? 'business.update_time desc' : $order;
			$list = $d_v_business->where($where)->order($order)->page($p.',10')->field('name,business_id,total_price,customer_id,owner_role_id,creator_role_id,status_id')->select();
			$count =  $d_v_business->where($where)->count();
			$page = ceil($count/10);
			
			foreach($list as $key => $value){
				$list[$key]['owner_name'] = D('RoleView')->where('role.role_id = %d', $value['owner_role_id'])->getField('user_name');
				$list[$key]['customer_name'] = $m_customer->where('customer_id = %s',$value['customer_id'])->getField('name');
				$customer = $m_customer->where('customer_id = %s',$value['customer_id'])->find();
				foreach($customer as $k=>$v){
					//如果存在首要联系人，则查出首要联系人。否则查出联系人中第一个。
					if(!empty($v['contacts_id'])){
						$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$v['contacts_id'])->find();
						$list[$key]['contacts_name'] = $contacts['name'];
						$list[$key]['contacts_id'] = $contacts['contacts_id'];
					}else{
						$contacts_customer = $m_r_contacts_customer->where('customer_id = %d',$v['customer_id'])->limit(1)->order('id desc')->select();
						if(!empty($contacts_customer)){
							$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$contacts_customer[0]['contacts_id'])->find();
						}
						$list[$key]['contacts_id'] = $contacts['contacts_id'];
						$list[$key]['contacts_name'] = $contacts['name'];
					}
				}
				$list[$key]['status_name'] = M('BusinessStatus')->where('status_id = %d', $value['status_id'])->getField('name');
			}
			$list = empty($list) ? array() : $list;
			$data['list'] = $list;
			$data['page'] = $page;
			$this->ajaxReturn($data,'success',1);
		}
	}
	
}