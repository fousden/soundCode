<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_add_bank
{
	public function index(){
		
		$root = array();
			
			//if(intval($user['idcardpassed'])==0){
			//	$root['response_code'] = 0;
			//	$root['show_err'] ="您的实名信息尚未认证,为保护您的账户安全，请先完成实名认证。";
			//}else{
				$root['response_code'] = 1;
//				$bank_list = $GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank where fuyou_bankid is not null ORDER BY is_rec DESC,sort DESC,id ASC");
				$root['item'] =$GLOBALS['db']->getAll("SELECT * FROM ".DB_PREFIX."bank  where fuyou_bankid != '' and is_rec = 1  ORDER BY is_rec DESC,sort DESC,id ASC");
			//}

		logger::write(json_encode($root));

		output($root);		
	}
}
?>
