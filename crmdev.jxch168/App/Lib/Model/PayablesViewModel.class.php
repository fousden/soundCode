<?php
	class PayablesViewModel extends ViewModel{
		public $viewFields = array(
			'payables'=>array('payables_id','name','price',"renew_status",'creator_role_id','owner_role_id','delete_role_id','delete_time','is_deleted','pay_time','contract_id','customer_id','create_time','update_time','description','status', '_type'=>'LEFT'),
			'role'=>array('_on'=>'payables.creator_role_id=role.role_id', '_type'=>'LEFT'),
			'user'=>array('name'=>'creator_name', '_on'=>'role.user_id = user.user_id' ,'_type'=>'LEFT'),
			'customer'=>array('name'=>'customer_name', '_on'=>'payables.customer_id=customer.customer_id' ,'_type'=>'LEFT'),
			'contract'=>array('start_date'=>'contract_start_date','number'=>'contract_name','redeem_status','end_date','redeem_time','id_card_pic'=>'id_card_pic','bank_card_pic'=>'bank_card_pic','small_ticket_pic'=>'small_ticket_pic','other_file_pic'=>'other_file_pic', '_on'=>'payables.contract_id=contract.contract_id'),
		);
	}