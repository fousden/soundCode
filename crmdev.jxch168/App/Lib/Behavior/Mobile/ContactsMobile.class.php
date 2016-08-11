<?php

class ContactsMobile extends Action{
	
	 public function _initialize(){
		$action = array(
			'permission'=>array('radiolistdialog','radiolistdialogs')			
		);
		B('AppAuthenticate', $action);
		$this->_permissionRes = getPerByAction(MODULE_NAME,ACTION_NAME);
		Global $roles;
		$this->roles = $roles;
	}
	
	public function radioListDialogs(){
		if($this->isAjax()){
			$rcc =  M('RContactsCustomer');
			$m_contacts = M('contacts');
			$where['owner_role_id'] = array('in', implode(',', $this->_permissionRes));
			$where['is_deleted'] = 0;
			$p = isset($_GET['p']) ? intval($_GET['p']) : 1; 
			if($_GET['customer_id']){
				$customer_id = $_GET['customer_id'];
				$contacts_id = $rcc->where('customer_id = %d',$customer_id )->getField('contacts_id', true);
				$where['contacts_id'] = array('in', implode(',', $contacts_id));				
			}
			if($_GET['search']){
				$where['name'] = array('like','%'.trim($_GET['search']).'%');
			}
			$list = $m_contacts->where($where)->order('create_time desc')->page($p.',10')->field('name,contacts_id')->select();
			$count = $m_contacts->where($where)->order('create_time desc')->count();			
		
			$total = $count%10 > 0 ? ceil($count/10) : $count/10;			
			$data['list'] = $list;
			$data['total'] = $total;
			$data['p'] = $p;
			$data['customer_id'] = $customer_id;
			$this->ajaxReturn($data,'success',1);
		}else{
			$rcc =  M('RContactsCustomer');
			$m_contacts = M('contacts');
			$where['owner_role_id'] = array('in', implode(',', $this->_permissionRes));
			$where['is_deleted'] = 0;
			$where['contacts_id'] = 0;
			$p = isset($_GET['p']) ? intval($_GET['p']) : 1; 
			if($_GET['customer_id']){
				$customer_id = $_GET['customer_id'];
				$contacts_id = $rcc->where('customer_id = %d',$customer_id )->getField('contacts_id', true);
				$where['contacts_id'] = array('in', implode(',', $contacts_id));				
			}
			$list = $m_contacts->where($where)->order('create_time desc')->page($p.',10')->field('name,contacts_id')->select();
			$count = $m_contacts->where($where)->order('create_time desc')->count();
			
			$this->total = $count%10 > 0 ? ceil($count/10) : $count/10;			
			$this->contactsList = $list;			
			$this->p = $p;
			$this->customer_id = $customer_id;
			$this->display();
		}
	}
	
	
	public function radioListDialog(){
		if($this->isAjax()){
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
			if(isset($_GET['search'])){
				$where['name'] = array('like','%'.trim($_GET['search']).'%');
			}			
			$where['owner_role_id'] = array('in', implode(',', $this->_permissionRes));
			$where['is_deleted'] = 0;
			if($_GET['customer_id']){
				$contacts_id = $rcc->where('customer_id = %d', intval($_GET['customer_id']))->getField('contacts_id', true);
				$where['contacts_id'] = array('in', implode(',', $contacts_id));
				$this->customer_id = intval($_GET['customer_id']);
			}
			$p = isset($_GET['p']) ? intval($_GET['p']) : 1 ;
			$list = $m_contacts->where($where)->field('contacts_id,name')->order('create_time desc')->field('contacts_id,name,post')->page($p.',10')->select();	
			$this->customer_id = $customer_id;
			$count = $m_contacts->where($where)->count();			
			foreach ($list as $k=>$value) {
				$customer_id = $rcc->where('contacts_id = %d', $value['contacts_id'])->getField('customer_id');
				$list[$k]['customer'] = M('customer')->where('customer_id = %d', $customer_id)->field('name')->find();
			}		
			$total = $count%10 > 0 ? ceil($count/10) : $count/10;
			$data['list'] = $list;
			$data['total'] = $total;
			$data['p'] = $p;
			$this->ajaxReturn($data,'success',1);	
		}
	}
}