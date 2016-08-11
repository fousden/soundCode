<?php
class CustomerMobile extends Action {

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
	
	public function info(){		
		$content = parseAlert();	
		if($content){
			foreach($content as $k=>$v){
				if($k == 'success'){					
					$this->model = 'customer';
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
		//联系人列表
		$customer_id = $_REQUEST['customer_id'];
		$contacts_id_list = M('RContactsCustomer')->where('customer_id = %d',$customer_id)->select();
		foreach($contacts_id_list as $v){
			$contacts_ids[] = $v['contacts_id'];
		}
		if($contacts_ids){
			$tmp['contacts_id'] = array('in',$contacts_ids);
		}
		$rcc =  M('RContactsCustomer');
		$m_contacts = M('contacts');
		$permissionRes =  getPerByAction('contacts',ACTION_NAME);
		$tmp['owner_role_id'] = array('in', implode(',',$permissionRes));
		$tmp['is_deleted'] = 0;
		$list = $m_contacts->where($tmp)->order('create_time desc')->limit(10)->field('contacts_id,name,post')->select();
		$count = $m_contacts->where($tmp)->count();				
		$this->total = $count%10 > 0 ? ceil($count/10) : $count/10;	
		$this->customer_id = $customer_id;		
		foreach ($list as $k=>$value) {
			$customer_id = $rcc->where('contacts_id = %d', $value['contacts_id'])->getField('customer_id');
			$list[$k]['customer'] = M('customer')->where('customer_id = %d', $customer_id)->field('name')->find();
		}
		$this->contactsList = $list;	
		$this->display();
		
	}
	
	public function radioListDialog(){
		if($this->isAjax()){
			//选择客户		
			$m_customer = M('Customer');
			$m_contacts = M('Contacts');
			$m_r_contacts_customer = M('RContactsCustomer');
			$underling_ids = $this->_permissionRes;
			if(isset($_GET['search'])){
				$where['name'] = array('like','%'.trim($_GET['search']).'%');
			}
			$where['owner_role_id'] = array('in',implode(',',$underling_ids));
			$where['is_deleted'] = 0;
			$p = isset($_GET['p']) ? intval($_GET['p']) : 1 ;
			$customer = $m_customer->where($where)->field('name,customer_id,contacts_id')->order('create_time desc')->page($p.',10')->select();
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
			$count = $m_customer->where($where)->count();
			$total = $count%10 > 0 ? ceil($count/10) : $count/10;	
			$data['list'] =  $customer;
			$data['total'] =  $total;
			$data['p'] =  $p;
			
			$this->ajaxReturn($data,'success',1);
		}		
	}
	
	public function add(){		
		if($this->role == 1){			
			$this->display('Public:role');
			die();
		}
		if($this->isPost()){
			$m_customer = D('Customer');
			$m_customer_data = D('CustomerData');
			$field_list = M('Fields')->where('model = "customer"')->order('order_id')->select();
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
			if($m_customer->create() && $m_customer_data->create()!==false){
				if($_POST['con_name']){
					$contacts = array();
					if($_POST['con_name']) $contacts['name'] = $_POST['con_name'];
					if($_POST['owner_role_id']) $contacts['owner_role_id'] = $_POST['owner_role_id'];
					if($_POST['saltname']) $contacts['saltname'] = $_POST['saltname'];
					if($_POST['con_email']) $contacts['email'] = $_POST['con_email'];
					if($_POST['con_post']) $contacts['post'] = $_POST['con_post'];
					if($_POST['con_qq']) $contacts['qq'] = $_POST['con_qq'];
					if($_POST['con_telephone']) $contacts['telephone'] = $_POST['con_telephone'];
					if($_POST['con_description']) $contacts['description'] = $_POST['con_description'];
					if(!empty($contacts)){
						$contacts['creator_role_id'] = session('role_id');
						$contacts['create_time'] = time();
						$contacts['update_time'] = time();
						if(!$contacts_id = M('Contacts')->add($contacts)){
							alert('error','添加首要联系人失败！', U('customer/info'));
						}
					}
				}			
                $m_customer->create_time = time();
                $m_customer->update_time = time();
                if($contacts_id) $m_customer->contacts_id = $contacts_id;
                $m_customer->creator_role_id = session('role_id');
                if(!$customer_id = $m_customer->add()){
                    alert('error','添加客户失败,请联系管理员！', U('customer/info'));
                }
                $m_customer_data->customer_id = $customer_id;
                $m_customer_data->add();
				
				if ($_POST['leads_id']) {
					$leads_id = intval($_POST['leads_id']);
					$r_module = array(
						array('key'=>'log_id','r1'=>'RCustomerLog','r2'=>'RLeadsLog'), 
						array('key'=>'file_id','r1'=>'RCustomerFile','r2'=>'RFileLeads'),
						array('key'=>'event_id','r1'=>'RCustomerEvent','r2'=>'REventLeads'),
						array('key'=>'task_id','r1'=>'RCustomerTask','r2'=>'RLeadsTask')
					);
					
					foreach ($r_module as $key=>$value) {
						$key_id_array = M($value['r2'])->where('leads_id = %d', $leads_id)->getField($value['key'],true);
						$r1 = M($value['r1']);
						$data['customer_id'] = $customer_id;
						foreach($key_id_array as $k=>$v){
							$data[$value['key']] = $v;
							$r1->add($data);
						}
					}
					$leads_data['is_transformed'] = 1;
					$leads_data['update_time'] = time();
					$leads_data['customer_id'] = $customer_id;
					$leads_data['contacts_id'] = $contacts_id;
					$leads_data['transform_role_id'] = session('role_id');
					M('Leads')->where('leads_id = %d', $leads_id)->save($leads_data);
				}
				
                //记录操作记录
                actionLog($customer_id);
                if ($contacts_id && $customer_id) {
                    $rcc['contacts_id'] = $contacts_id;
                    $rcc['customer_id'] = $customer_id;
                    M('RContactsCustomer')->add($rcc);
                }
				$info['id'] = $customer_id;
				$info['info'] = '新客户添加成功!';
                if(intval($_POST['create_business1']) == 1 || intval($_POST['create_business2']) == 1){					
                    alert('success',$info,U('customer/info'));
                }else{
                    if($_POST['submit'] == '保存') {
                        alert('success',$info, U('customer/info'));
                    } else {
                        alert('success',$info, U('customer/info'));
                    }
                }
			}else{
                alert('error', $m_customer->getError().$m_customer_data->getError(),U('customer/info'));				
            }
		}else{
			$role_id = session('role_id');
			$this->field_list = field_list_mobile_html("add","customer",$leads);				
			//负责人列表
			$this->owner_name = owner_name_select($role_id);		
			$this->display();
		}
	}
	
	//客户放入回收站
	public function delete(){
		if($this->role == 1){
			$this->ajaxReturn('','error',-2);
		}
		if($this->isPost()){
			$m_customer = M('Customer');		
			$customer_id = intval($_REQUEST['customer_id']);
			$customer = $m_customer->where('customer_id = %d',$customer_id)->find();			
			if(!$customer_id || !$customer){
				$this->ajaxReturn('','error',3);	//参数错误
			}elseif(!in_array($customer['owner_role_id'], $this->_permissionRes)){
				$this->ajaxReturn('','error',-2);	//没有权限
			}
			$data = array('is_deleted'=>1, 'delete_role_id'=>session('role_id'), 'delete_time'=>time());
			if($m_customer->where('customer_id = %d', $customer_id)->setField($data)){
				actionLog($customer_id);
				$this->ajaxReturn('','success',1);	//删除成功
			}else{
				$this->ajaxReturn('','error',2);	//删除失败
			}	
		}
	}
	
	public function edit(){
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}		
		$customer_id =  isset($_GET['id']) ? intval($_GET['id']) : 0;
		$customer = D('CustomerView')->where('customer.customer_id = %d', $customer_id)->find();	
		if (!$customer) {          
			$this->type = 1;
			$this->display('Public:role');
			die();
        }elseif(!in_array($customer['owner_role_id'], $this->_permissionRes)){
			$this->display('Public:role');
			die();
		}
        $customer['owner'] = D('RoleView')->where('role.role_id = %d', $customer['owner_role_id'])->find();
        $customer['contacts_name'] = M('contacts')->where('contacts_id = %d', $customer['contacts_id'])->getField('name');
        $field_list = M('Fields')->where('model = "customer"')->order('order_id')->select();
		
		if($this->isPost()){
			$m_customer = D('Customer');
			$m_customer_data = D('CustomerData');
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
            
			if($m_customer->create() && $m_customer_data->create()!==false){
				$m_customer->update_time = time();
				$a = $m_customer->where('customer_id=' . $customer['customer_id'])->save();
				$b = $m_customer_data->where('customer_id=' . $customer['customer_id'])->save();
				if($a !== false && $b !== false){
					actionLog($customer['customer_id']);
					$info['info'] = '客户编辑成功！';
					$info['id'] = $customer['customer_id'];
					alert('success',$info , U('customer/info'));
				}else{
					alert('error', '客户编辑失败！',U('customer/info'));
				}
            
            }else{
                alert('error', $m_customer->getError().$m_customer_data->getError(),U('customer/info'));				
            }
		}else{          
            $this->customer = $customer;
            $this->field_list = field_list_mobile_html("edit","customer",$customer);
			$this->owner_name = owner_name_select($customer['owner']['role_id']);
            $this->display();
		}
	}
		
	
	//客户池领取
	/*
	 * 1.领取成功
	 * 2.领取失败
	 * 3.领取失败，您的领取次数已超过领取限制
	 * 0.非POST方式提交
	 */
	public function receive(){
		if($this->isPost()){			
			$m_customer = M('Customer');
			$m_config = M('Config');
			$m_customer_record = M('customer_record');				
			$data['owner_role_id'] = session('role_id');	
			$data['update_time'] = time();
			$customer_id = isset($_REQUEST['customer_id']) ? intval(trim($_REQUEST['customer_id'])) : 0;
			//判断是否符合领取条件
			$customer_limit_counts = $m_config->where('name = "customer_limit_counts"')->getField('value');				
			$m_config = M('config');
			$m_customer_record = M('customer_record');
			$customer_limit_condition = $m_config->where('name = "customer_limit_condition"')->getField('value');			
		
			$today_begin = strtotime(date('Y-m-d',time()));
			$today_end = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
			$this_week_begin = ($today_begin -((date('w'))-1)*86400);
			$this_week_end = ($today_end+(7-(date('w')==0?7:date('w')))*86400); 
			$this_month_begain = strtotime(date('Y-m', time()).'-01 00:00:00');
			$this_month_end = mktime(23,59,59,date('m'),date('t'),date('Y'));
			
			$condition['user_id'] = session('user_id');
			$condition['type'] = 1;
			if($customer_limit_condition == 'day'){
				$condition['start_time'] = array('between', array($today_begin, $today_end)); 
			}elseif($customer_limit_condition == 'week'){
				$condition['start_time'] = array('between', array($this_week_begin, $this_week_end));
			}elseif($customer_limit_condition == 'month'){
				$condition['start_time'] = array('between', array($this_month_begain, $this_month_end));
			}			
			$customer_record_count = $m_customer_record->where($condition)->count();
			
			if($customer_record_count < $customer_limit_counts){
				if($m_customer->where('customer_id = %d', $customer_id)->save($data)){
					$info['customer_id'] = $customer_id;
					$info['user_id'] = session('user_id');
					$info['start_time'] = time();
					$info['type'] = 1;
					$m_customer_record->add($info);
					$this->ajaxReturn('','success',1);
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{				
				$this->ajaxReturn('','error',3);
			}			
		}
	}
	
	
	//得到可操作分配的员工列表
	public function getrole(){
		if($this->isPost()){
			$role_ids = getSubRoleId();
			$user_info = D('RoleView')->where('role.role_id in (%s)',implode(',',$role_ids))->field('user_name,role_id,department_id,department_name,role_name')->select();			
			$user_info = empty($user_info) ? array() : $user_info; 
			$this->ajaxReturn($user_info,'success',1);	
		}
	}
	
	//客户池分配
	public function allot(){
		if($this->isPost()){
			$m_customer = M('Customer');			
			if(!empty($_POST['owner_role_id'])){
				$owner_role_id = $_GET['role_id'];
			}else{
				$owner_role_id = session('role_id');
			}
			$customer_id = isset($_GET['customer_id']) ? intval(trim($_GET['customer_id'])) : 0;
			$data['owner_role_id'] = $owner_role_id;
			$data['update_time'] = time();
			
			$where['update_time'] = array('lt',(time()-86400));
			$where['customer_id'] = intval($customer_id);
			$where['owner_role_id'] = array('gt',0);
			$updated_owner = $m_customer->where($where)->save($data);
			
			unset($where['update_time']);
			$where['owner_role_id'] = array('eq',0);
			$updated_time = $m_customer->where($where)->save($data);			
			if($updated_owner || $updated_time){
				$customer = $m_customer->where('customer_id = %d', intval($customer_id))->find();					
				$content= session('name').'将客户资源:'.$customer['name'].'分配给了你负责!请注意跟进!';
				sendMessage($owner_role_id,$content,1);
				$this->ajaxReturn('','success',1);
			}else{
				$this->ajaxReutnr('','error',2);
			}
		}
	}
	
	
	//客户列表
	public function index(){
		if($this->role == 1){
			$this->display('Public:role');			
			die();
		}
		if($this->isPost()){
			$permission_list = apppermission(MODULE_NAME,ACTION_NAME);
			if($permission_list){
				$data['permission_list'] = $permission_list;
			}else{
				$data['permission_list'] = array();
			}
			getDateTime('customer');			
			$d_v_customer = D('CustomerView');
			$m_user = M('User');
			$m_fields = M('Fields');
			$by = isset($_GET['by']) ? trim($_GET['by']) : '';
			$below_ids = getPerByAction('customer',ACTION_NAME,true);
			$outdays = M('config') -> where('name="customer_outdays"')->getField('value');
			$outdate = empty($outdays) ? 0 : time()-86400*$outdays;
			$where = array();			
			switch ($by) {			
				case 'sub' : $where['owner_role_id'] = array('in',implode(',', $below_ids)); break;
				case 'me' : $where['owner_role_id'] = session('role_id'); break;
				default :
					if($this->_get('content') == 'resource'){
						getDateTime('customer_resource');	
						$where['_string'] = "customer.owner_role_id=0 or (customer.update_time < $outdate and customer.is_locked = 0)";
					}else{
						$where['owner_role_id'] = array('in',implode(',', $this->_permissionRes));
					}
				break;
			}
			$where['is_deleted'] = array('neq',1);	
			if($this->_get('content') != 'resource'){
				$where['_string'] = 'update_time > '.$outdate.' OR is_locked = 1';
			}
			if(isset($_POST['search'])){
				$where['name'] = array('like','%'.trim($_POST['search']).'%');
			}
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			if($_GET['act'] == 'new'){
				$time_now = time();
				$compare_time = $time_now - 86400*3;
				$where['owner_role_id'] = array('in',implode(',', getSubRoleId()));
				$where['update_time'] = array('gt',$compare_time);
			}
			$list = $d_v_customer->where($where)->order('create_time desc')->page($p.',10')->field('name,customer_id,owner_role_id,industry,creator_role_id')->select();
			foreach($list as $k=>$v){
				$list[$k]['owner_name'] = $m_user->where(array('role_id'=>$v['owner_role_id'],'status'=>1))->getField('name');
				$list[$k]['creator_name'] = $m_user->where(array('role_id'=>$v['creator_role_id'],'status'=>1))->getField('name');
				$owner_role_id = $v['owner_role_id'];
				//获取操作权限
				if($this->_get('content') == 'resource'){
					$list[$k]['permission'] = array("view"=>1);
				}else{
					$list[$k]['permission'] = permissionlist(MODULE_NAME,$owner_role_id);
				}
			}
			$list = empty($list) ? array() : $list;
			$count = $d_v_customer->where($where)->count();
			//获取查询条件信息
			$fields_list = $m_fields->where(array('model'=>'customer','form_type'=>array('in','text,box'),'is_main'=>1))->field('name,field,setting,form_type,input_tips')->select();
			foreach($fields_list as $k=>$v){
				if($v['setting']){
					eval("\$setting = ".$v['setting'].'; ');
				}else{
					$setting = array();
				}
				$fields_list[$k]['setting'] = $setting;
			}
			$page = ceil($count/10);
			if($p == 1){
				$data['fields_list'] = $fields_list;
			}else{
				$data['fields_list'] = array();
			}
			$data['list'] = $list;
			$data['page'] = $page;
			$data['info'] = 'success'; 
			$data['status'] = 1;
			$this->ajaxReturn($data,'JSON');
		}
	}
	
	
	public function view(){	
		if($this->role == 1){
			$this->display('Public:role');
			die();
		}
		$customer_id =  isset($_GET['id']) ? intval($_GET['id']) : 0;
		$customer = D('CustomerView')->where('customer.customer_id = %d', $customer_id)->find();
		if (!$customer || $customer['is_deleted'] == 1) {   
			$this->type = 1;
			$this->display('Public:role');
			die();
        }
		$outdays = M('config') -> where('name="customer_outdays"')->getField('value');
		$outdate = empty($outdays) ? 0 : time()-86400*$outdays;
		if($customer['owner_role_id'] != 0 && ($customer['update_time'] > $outdate || $customer['is_locked'] == 1)){
			if(!in_array($customer['owner_role_id'], $this->_permissionRes)){
				$this->display('Public:role');
				die();
			}
		}
		//取得字段列表
		$field_list = M('Fields')->where('model = "customer"')->order('order_id')->select();
		//查询固定信息
		$customer['owner'] = D('RoleView')->where('role.role_id = %d', $customer['owner_role_id'])->find();
		$customer['create'] = D('RoleView')->where('role.role_id = %d', $customer['creator_role_id'])->find();
		if($customer['contacts_id']) $customer['contacts_name'] = M('contacts')->where('contacts_id = %d', $customer['contacts_id'])->getField('name');
		//联系人统计
		$contacts_ids = M('rContactsCustomer')->where('customer_id = %d', $customer_id)->getField('contacts_id', true);
		$customer['contacts'] = M('contacts')->where('contacts_id in (%s) and is_deleted=0', implode(',', $contacts_ids))->select();
		$contacts_count = M('contacts')->where('contacts_id in (%s) and is_deleted=0', implode(',', $contacts_ids))->count();
		$customer['contacts_count'] = empty($contacts_count)?0:$contacts_count;
		
		//商机统计
		$customer['business'] = M('business')->where('customer_id = %d and is_deleted=0', $customer['customer_id'])->select();
		$customer['business_count'] = sizeof($customer['business']);
		
		//文件统计
		$file_ids = M('rCustomerFile')->where('customer_id = %d', $customer_id)->getField('file_id', true);
		$customer['file'] = M('file')->where('file_id in (%s)', implode(',', $file_ids))->select();
		$file_count = 0;
		foreach ($customer['file'] as $key=>$value) {
			$customer['file'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['role_id'])->find();
			$customer['file'][$key]['file_path'] = U('file/filedownload','path='.urlencode($value['file_path']).'&name='.urlencode($value['name']));
			$file_count ++;
		}
		$customer['file_count'] = $file_count;
		//沟通日志统计
		$customer_log_ids = M('rCustomerLog')->where('customer_id = %d', $customer_id)->getField('log_id', true);
		$customer_log_ids = $customer_log_ids ? $customer_log_ids : array();
		$business_log_ids = M('rBusinessLog')->where('business_id in (%s)', implode(',', $business_id))->getField('log_id', true);
		$business_log_ids = $business_log_ids ? $business_log_ids : array();
		$customer['log'] = M('log')->where('log_id in (%s)', implode(',', array_merge($customer_log_ids,$business_log_ids)))->select();
		$log_count = 0;
		foreach ($customer['log'] as $key=>$value) {
				$customer['log'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['role_id'])->find();
				$log_count ++;
		}
		$customer['log_count'] = $log_count;
		
		//任务统计
		$task_ids = M('rCustomerTask')->where('customer_id = %d', $customer_id)->getField('task_id', true);
		$customer['task'] = M('task')->where('task_id in (%s) and is_deleted=0', implode(',', $task_ids))->select();
		$task_count = 0;
		foreach ($customer['task'] as $key=>$value) {
			$customer['task'][$key]['owner'] = D('RoleView')->where('role.role_id = %d', $value['owner_role_id'])->find();
			$task_count ++;
		}
		$customer['task_count'] = $task_count;
		if(empty($customer['owner_role_id'])){
			$this->purview="客户池客户池";
		}
		$tags = $customer['tags'];
		$laststr = substr($tags,0,strlen($tags)-1);
		$newstr = substr($laststr,1);
		$tags_list = explode(',',$newstr);
		foreach($tags_list as $k=>$v){
			$m_tags = M('tags');
			$tags_name = $m_tags->where('tags_id = %d',$v)->getField('name');
			$tagss[] = $tags_name;
		}
		//获取权限
		$customer['permission'] = permissionlist(MODULE_NAME,$customer['owner_role_id']);
		$customer['tags'] = implode(',',$tagss);
		$this->customer = $customer;
		$this->field_list = $field_list;
		$this->display();	
	}
	//选择客户列表
	public function customer_list(){
		if($this->isPost()){
			if(isset($_POST['search'])){
				$where['name'] = array('like','%'.trim($_POST['search']).'%');
			}
			$m_customer = M('Customer');
			$m_contacts = M('Contacts');
			$m_r_contacts_customer = M('RContactsCustomer');
			$outdays = M('config') -> where('name="customer_outdays"')->getField('value');
			$outdate = empty($outdays) ? 0 : time()-86400*$outdays;
			$where['owner_role_id'] = array('in',implode(',', getPerByAction(customer,index)));
			$where['is_deleted'] = array('neq',1);
			$where['_string'] = 'update_time > '.$outdate.' OR is_locked = 1';
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$customer = $m_customer->where($where)->order('create_time desc')->page($p.',10')->field('name,customer_id,contacts_id')->select();
			foreach($customer as $k=>$v){
				//如果存在首要联系人，则查出首要联系人。否则查出联系人中第一个。
				if(!empty($v['contacts_id'])){
					$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$v['contacts_id'])->find();
					$customer[$k]['contacts_name'] = $contacts['name'];
				}else{
					$contacts_customer = $m_r_contacts_customer->where('customer_id = %d',$v['customer_id'])->limit(1)->order('id desc')->select();
					if(!empty($contacts_customer)){
						$contacts = $m_contacts->where('is_deleted = 0 and contacts_id = %d',$contacts_customer[0]['contacts_id'])->find();
					}
					$customer[$k]['contacts_id'] = $contacts['contacts_id'];
					$customer[$k]['contacts_name'] = $contacts['name'];
				}
			}
			$customer = empty($customer) ? array() : $customer;
			$count = $m_customer->where($where)->count();
			$page = ceil($count/10);
			$data['list'] = $customer;
			$data['page'] = $page;
			$this->ajaxReturn($data,'success',1);	
		}
	}
}