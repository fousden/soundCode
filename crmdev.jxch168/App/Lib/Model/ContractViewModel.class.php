<?php
	class contractViewModel extends ViewModel {
	   public $viewFields = array(
		'contract'=>array('contract_id','contract_file_pic','redeem_time','redeem_status','number','renew_status','outer_pack','pid_contract_id','qixi_type','original_creater','active_rate','business_id','is_deleted','examine_status','examine_remark','id_card_pic','bank_card_pic','other_file_pic','small_ticket_pic','start_date','end_date','delete_role_id','delete_time','receivables_doc_type','due_time','content','creator_role_id','owner_role_id','description','create_time','update_time','status','investment_money','customer_id','department_id','receivables_bank','receivables_bankcard','receivables_bankzone','receivables_name','receivables_idno','investment_money','investment_rate','month_investment_rate','closure_period','interest_days','total_interest','month_interest','total_receivables_money','product_id','_type'=>'LEFT'),
		//'business'=>array('name'=>'business_name','contacts_id'=>'contacts_id','customer_id'=>'customer_id', '_on'=>'contract.business_id=business.business_id','_type'=>'LEFT'),
		//'contacts'=>array('name'=>'contacts_name', '_on'=>'contacts.contacts_id=business.contacts_id','_type'=>'LEFT'),
		'customer'=>array('name'=>'customer_name', '_on'=>'customer.customer_id=contract.customer_id','_type'=>'LEFT'),
		'user'=>array('name'=>'owner_name', '_on'=>'contract.owner_role_id=user.role_id')
	   );
	}