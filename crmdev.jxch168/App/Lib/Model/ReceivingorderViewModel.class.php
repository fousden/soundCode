<?php

class ReceivingorderViewModel extends ViewModel {

    public $viewFields = array(
        'receivingorder' => array('receivingorder_id', 'name', 'money', 'receive_fee', 'real_money', 'status', 'receivables_id', 'owner_role_id', 'delete_role_id', 'is_deleted', 'delete_time', 'description', 'pay_time', 'creator_role_id', 'create_time', 'update_time', 'funds_gangway', 'bank_in_time', '_type' => 'LEFT'),
        'receivables' => array('name' => 'receivables_name','customer_id'=>'customer_id', 'price' => 'price', '_on' => 'receivingorder.receivables_id=receivables.receivables_id', '_type' => 'LEFT'),
        'role' => array('_on' => 'receivingorder.creator_role_id=role.role_id', '_type' => 'LEFT'),
        'user' => array('name' => 'creator_name', '_on' => 'role.user_id = user.user_id'),
        'contract' => array('start_date' => 'contract_start_date', '_on' => 'receivables.contract_id=contract.contract_id'),
    );

}
