<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class DealMsgListAction extends CommonAction{
	public function index()
	{
		if(trim($_REQUEST['dest'])!='')
		$condition['dest'] = array('like','%'.trim($_REQUEST['dest']).'%');
		if(trim($_REQUEST['content'])!='')
		$condition['content'] = array('like','%'.trim($_REQUEST['content']).'%');
		$this->assign("default_map",$condition);
		parent::index();
	}
	public function show_content()
	{
		$id = intval($_REQUEST['id']);
		header("Content-Type:text/html; charset=utf-8");
		echo htmlspecialchars(M("DealMsgList")->where("id=".$id)->getField("content"));
	}
	
	public function send()
	{
		$id = intval($_REQUEST['id']);
		$msg_item = M("DealMsgList")->getById($id);
		if($msg_item)
		{
			if($msg_item['send_type']==0)
			{
				//短信
				require_once APP_ROOT_PATH."system/utils/es_sms.php";
				$sms = new sms_sender();
		
				$result = $sms->sendSms($msg_item['dest'],$msg_item['content']);
				$msg_item['result'] = $result['msg'];
				$msg_item['is_success'] = intval($result['status']);
				$msg_item['send_time'] = TIME_UTC;
				M("DealMsgList")->save($msg_item);
				if($result['status'])
				{					
					header("Content-Type:text/html; charset=utf-8");
					echo l("SEND_NOW").l("SUCCESS");
				}
				else
				{
					
					header("Content-Type:text/html; charset=utf-8");
					echo l("SEND_NOW").l("FAILED").$result['msg'];
				}
			}
			else
			{			
				//邮件
				require_once APP_ROOT_PATH."system/utils/es_mail.php";
				$mail = new mail_sender();
		
				$mail->AddAddress($msg_item['dest']);
				$mail->IsHTML($msg_item['is_html']); 				  // 设置邮件格式为 HTML
				$mail->Subject = $msg_item['title'];   // 标题
				$mail->Body = $msg_item['content'];  // 内容	
				$result = $mail->Send();
				
				$msg_item['result'] = $mail->ErrorInfo;
				$msg_item['is_success'] = intval($result);
				$msg_item['send_time'] = TIME_UTC;
				M("DealMsgList")->save($msg_item);
				if($result)
				{					
					header("Content-Type:text/html; charset=utf-8");
					echo l("SEND_NOW").l("SUCCESS");
				}
				else
				{
					
					header("Content-Type:text/html; charset=utf-8");
					echo l("SEND_NOW").l("FAILED").$mail->ErrorInfo;
				}
				
			}
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			echo l("SEND_NOW").l("FAILED");
		}
	}
	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['id'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();	
			
				if ($list!==false) {
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
        /**
         * 业务队列统计（短信每日统计）
         */
        public function statistics(){
            $is_success=  isset($_REQUEST['is_success'])?(int)$_REQUEST['is_success']:1;
            $send_type=  isset($_REQUEST['send_type'])?(int)$_REQUEST['send_type']:0;
            $begin_time=  isset($_REQUEST['begin_time'])?strtotime($_REQUEST['begin_time']):strtotime("-30day");
            $end_time=  isset($_REQUEST['end_time'])?strtotime($_REQUEST['end_time'].'23:59:59'):time();
            $where='';
            if($begin_time){
                $where.=" and create_time>'$begin_time'";
            }
            if($end_time){
                $where.=" and create_time<'$end_time'";
            }
            $sql_str="SELECT FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_date,count(*) as c FROM `fanwe_deal_msg_list` where is_success=$is_success and send_type=$send_type $where GROUP BY create_date";
            $this->_Sql_list(D(), $sql_str, $map, "create_date");
            $this->display();
        }
		
}
?>