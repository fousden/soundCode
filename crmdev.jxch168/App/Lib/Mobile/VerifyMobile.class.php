<?php
class VerifyMobile extends Action{
	
	public function phone(){
		if($this->isPost()){
			$phone = trim($_POST['phone']);			
			if(is_phone($phone)){
				$code = mt_rand(10000,99999);
				$m_verify = M('Verify');
				$row = $m_verify->where('phone = "%s"',$phone)->find();
				$data['phone'] = $phone;
				$data['code'] = $code;
				$data['create_time'] = time();
				$data['num'] = 1;
				$data['status'] = 0;
				$message = '你的体验码为'.$code.',如非您本人操作，请忽略！';
				if($row){
					if(date('yz',time()) == date('yz',$row['create_time'])){
						if($row['num'] < 3){
							$m_verify->where('id = %d',$row['id'])->setField(array('code'=>$code,'create_time'=>time(),'status'=>0));
							$m_verify->where('id = %d',$row['id'])->setInc('num');
						}else{
							$this->ajaxReturn('','error',4);
						}
					}else{
						$m_verify->where('id = %d',$row['id'])->setField(array('code'=>$code,'create_time'=>time(),'status'=>0,'num'=>1));
					}
				}else{
					$m_verify->add($data);
				}
				if($this->send($phone,$message)){
					$this->ajaxReturn('','success',1);
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{
				$this->ajaxReturn('','error',3);
			}
		}	
	}
	
	public function send($telphone,$message){		
		$flag = 0; 
		$sms = array('uid'=>'DXX-BBX-010-18891','passwd'=>'3C98aC12','sign_name'=>'华陌通软件');
		$argv = array( 
			'sn'=>$sms['uid'],
			'pwd'=>strtoupper(md5($sms['uid'].$sms['passwd'])),
			'mobile'=>$telphone,
			'content'=>urlencode($message.'【'.$sms['sign_name'].'】'),
			'ext'=>'',
			'rrid'=>'',
			'stime'=>$sendtime
		); 
		foreach ($argv as $key=>$value) { 
			if ($flag!=0) { 
				$params .= "&"; 
				$flag = 1; 
			} 
			$params.= $key."="; $params.= urlencode($value); 
			$flag = 1; 
		} 
		$length = strlen($params); 
		$fp = fsockopen("sdk2.entinfo.cn",8060,$errno,$errstr,10) or exit($errstr."--->".$errno); 
		$header = "POST /webservice.asmx/mdSmsSend_u HTTP/1.1\r\n"; 
		$header .= "Host:sdk2.entinfo.cn\r\n"; 
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
		$header .= "Content-Length: ".$length."\r\n"; 
		$header .= "Connection: Close\r\n\r\n"; 
		$header .= $params."\r\n"; 
		fputs($fp,$header); 
		$inheader = 1; 
		while (!feof($fp)) { 
			$line = fgets($fp,1024);
			if ($inheader && ($line == "\n" || $line == "\r\n")) { 
				$inheader = 0; 
			} 
			if ($inheader == 0) { 
			} 
		} 
		preg_match('/<string xmlns=\"http:\/\/tempuri.org\/\">(.*)<\/string>/',$line,$str);
		$result=explode("-",$str[1]);	   
		if(count($result)>1){
			//echo '发送失败返回值为:'.$line."请查看webservice返回值";
			return $line;
		}else{
			//echo '发送成功 返回值为:'.$line;  
			return 1;
		}		
	}
	
	public function check(){
		if($this->isPost()){
			$phone = trim($_POST['phone']);
			$code = trim($_POST['code']);
			$m_verify = M('Verify');
			$row = $m_verify->where('phone = "%s"',$phone)->find();
			if($row){
				if($row['code'] == $code){
					if((time() - $row['create_time']) > 60*10){
						$this->ajaxReturn('','error',3);
					}else{					
						$m_verify->where('id = %d',$row['id'])->setField('status',1);
						$this->ajaxReturn('','success',1);
					}
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{
				$this->ajaxReturn('','error',4);
			}
		}
	}
	
	public function select(){		
		if($this->isPost()){			
			$type = intval($_POST['type']);
			$role_id = empty($_POST['role_id']) ? 0 : intval($_POST['role_id']);
			$m_user = M('user');
			if($type == 1){
				$user = $m_user->where('role_id = 16')->find();
			}elseif($type == 2){
				$user = $m_user->where('role_id != 16')->order('rand()')->find();
			}elseif($type == 3 && !empty($role_id)){
				$user = $m_user->where('role_id = %d',$role_id)->find();
			}
			$d_role = D('RoleView');
			$role = $d_role->where('user.user_id = %d', $user['user_id'])->find();
			if (!is_array($role) || empty($role)) {
				$this->ajaxReturn('','error',5);
			} else {						
				if($user['category_id'] == 1){
					session('admin', 1);
				}
				session('role_id', $role['role_id']);
				session('position_id', $role['position_id']);
				session('role_name', $role['role_name']);
				session('department_id', $role['department_id']);
				session('name', $user['name']);
				session('user_id', $user['user_id']);
				//userLog($user['user_id']);
				$data['info'] = 'success';
				$data['status'] = 1;
				$data['img'] = empty($user['img']) ? '' : $user['img'];
				$data['session_id'] = session_id();
				$data['role_id'] = $role['role_id'];
				$data['token'] = md5(md5($data['session_id']).time());
				M('user')->where('user_id = %d',session('user_id'))->setField(array('token'=>$data['token'],'token_time'=>time()));
				
				if(session('?admin')){
					$data['admin'] = 1;
				}else{
					$data['admin'] = 0;
					$data['permission'] = $this->permission();
				} 						
				$this->ajaxReturn($data,'JSON');
			}
		}
	}
	
	public function permission(){
		$m_permission = M('Permission');
		$row = $m_permission->where(array('position_id'=>session('position_id')))->field('url')->select();
		$permission = array();	
		$model = '';
		$existModel = array('customer','business','knowledge','contacts');	
		foreach($row as $v){
			$tmp = explode('/',$v['url']);				
			if($model!=$tmp[0]){
				$model = $tmp[0];				
				if(in_array($model,$existModel) && !in_array($model,$permission)){
					$permission[] = $model;		
				}							
			}			
		}		
		return $permission;
	}
	
	
}
?>