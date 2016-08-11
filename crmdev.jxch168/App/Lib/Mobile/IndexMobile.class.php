<?php

class IndexMobile extends Action{
	/**
	 *	permission 未登录可访问
	 * 	allow 登录访问
	 **/
	public function _initialize(){
		$action = array(
			'permission'=>array(),
			'allow'=>array('home','index','view','inbox','outbox','boxdelete','boxview','send','message','messagehistory','comment','replay')
		);
		$this->_permissionRes = getPerByAction(MODULE_NAME,ACTION_NAME);
		B('AppAuthenticate', $action);	
		Global $roles;
		$this->roles = $roles;
	}
	/*
	 * 首页动态信息
	 */
	public function home(){
		if($this->isPost()){
			if(!empty($_POST['role_id'])){
				$where['role_id'] = $_POST['role_id'];
			}else{
				$user_info = D('RoleView')->where('role.role_id = %d', session('role_id'))->find();
				$user['user_name'] = $user_info['user_name'];
				$user['department_name'] = $user_info['department_name'];
				$user['role_name'] = $user_info['role_name'];
				if(!session('?admin')){
					$where['role_id'] = array('in',implode(',', getSubRoleId()));
				}
			}
			$where['action_name'] = array('not in',array('completedelete','delete','view'));
			$where['module_name'] = array('in',array('business','customer','sign'));
			$map['business.is_deleted'] = array('neq',1);
			$map['customer.is_deleted'] = array('neq',1);
			$map['sign.sign_id'] = array("gt",0);
			$map['_logic'] = 'or';
			$where['_complex'] = $map;

			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$log = D('ActionLogView')->where($where)->page($p,10)->order('create_time desc')->select();

			$logCount = D('ActionLogView')->where($where)->count();
			$page = ceil($logCount/10);
			$action_name = array('add'=>'新建','delete'=>'删除','view'=>'查看','edit'=>'修改','sign_in'=>'进行');
			$module_name = array('customer'=>'客户','business'=>'商机','sign'=>'签到');
			$list = array();

			foreach($log as $k=>$v){
				$role = array();
				if($v['module_name'] != 'sign'){
					$aname = M($v['module_name'])->where($v['module_name'].'_id = %d',$v['action_id'])->getField('name');					
					$sign_log = M($v['module_name'])->where($v['module_name'].'_id = %d',$v['action_id'])->getField('log');					
					if(empty($aname) && empty($sign_log)){
						continue;
					}
				}
				$role = D('RoleView')->where('role.role_id = %d', $v['role_id'])->find();
				$tmp = array();
				$tmp['type'] = $v['module_name'];
				$tmp['role_id'] = $v['role_id'];
				$tmp['user_name'] = $role['user_name'];
				$tmp['role_name'] = $role['department_name'].'-'.$role['role_name'];
				$tmp['img'] = $role['img'];
				$tmp['content'] = $action_name[$v['action_name']].'了'.$module_name[$v['module_name']];
				if('sign'==$v['module_name']){
					$tmp['log'] = $v['log'];
					$tmp['address'] = $v['address'];
					$tmp['x'] = $v['x'];
					$tmp['y'] = $v['y'];
					$tmp['title'] = $v['title'];
					$tmp['sign_customer_id'] = $v['sign_customer_id'];
					$tmp['sign_customer_name'] = M('Customer')->where('customer_id = %d',$v['sign_customer_id'])->getField('name');
				}else{
					$tmp['aname'] = M($v['module_name'])->where($v['module_name'].'_id = %d',$v['action_id'])->getField('name');
					$tmp['url'] = 'm='.ucfirst($v['module_name']).'&a=view&id='.$v['action_id'];
				}
				$tmp['id'] = $v['action_id'];
				$tmp['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
				$list[] = $tmp;
			}
			if(empty($_POST['role_id'])){
				$count = array();
				$time_now = time();
				$compare_time = $time_now - 86400*3;
				$customer['owner_role_id'] = array('in',implode(',', getSubRoleId()));
				$customer['is_deleted'] != 1;
				$customer['update_time'] = array('gt',$compare_time);
				$count['customer'] = M('Customer')->where($customer)->count();
				$business['owner_role_id'] = array('in',implode(',', getSubRoleId()));
				$business['is_deleted'] != 1;
				$business['update_time'] = array('gt',$compare_time);
				$count['business'] = M('Business')->where($business)->count();
				$daily['role_id'] = array('in',implode(',', getSubRoleId()));
				$daily['update_date'] = array('gt',$compare_time);
				$count['log'] = M('Log')->where($daily)->count();		
				$data['count'] = $count;		
				$data['user'] = $user;
			}			
			$data['page'] = $page;
			$data['list'] = $list;
			$data['info'] = 'success';
			$data['status'] = 1;			
			$this->ajaxReturn($data,'JSON');
		}
	}
	
	//公告
	public function index(){
		if($this->roles == 2){
			$this->ajaxReturn('您没有此权利!','error',-2);
		}		
		if($this->isPost()){
			getDateTime('announcement');
			$m_announcement = M('announcement');
			if($_REQUEST["name"]) {
				$where['title'] = array('like','%'.$_REQUEST["name"].'%');
			}
			if($this->_permissionRes) $where['role_id'] = array('in', $this->_permissionRes);
			$where['department'] = array('like', '%('.session('department_id').')%');
			$where['status'] = array('eq', 1);
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			
			$announcement_list = $m_announcement->where($where)->order('order_id')->field('title,announcement_id,update_time,role_id')->select();
			$announcementCount = $m_announcement->where($where)->count();
			$page = ceil($announcementCount/10);
			
			foreach($announcement_list as $k=>$v){
				$announcement_list[$k]['role_name'] = M('User')->where(array('role_id'=>$v['role_id'],'status'=>1))->getField('name');
				$owner_role_id = $v['role_id'];
				//获取操作权限
				$announcement_list[$k]['permission'] = permissionlist(MODULE_NAME,$owner_role_id);
			}
			if(empty($announcement_list)){
				$announcement_list = array();
			}
			$data['page'] = $page;
			$data['list'] = $announcement_list;
			$data['info'] = 'success';
			$data['status'] = 1;			
			$this->ajaxReturn($data,'JSON');
		}		
	}
	
	//公告详情
	public function view(){
		if($this->roles == 2){
			$this->ajaxReturn('您没有此权利!','error',-2);
		}
		if($this->isPost()){
			if($_GET['id']){
				$announcement = M('announcement')->where('announcement_id = %d',intval($_GET['id']))->find();				
				if($announcement){
					if(in_array($announcement['role_id'], $this->_permissionRes)){
						$announcement['name'] = M('User')->where('role_id = %d',$announcement['role_id'])->getField('name');	
						$this->ajaxReturn($announcement,'success',1);
					}else{
						$this->ajaxReturn('您没有此权利!','error',-2);
					}
				}else{
					$this->ajaxReturn('','error',2);
				}
			}	
		}			
	}
		
	//收件箱
	/* public function inbox(){
		if($this->isPost()){			
			import("@.ORG.Page");
			$p1 = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$m_r_message = D('MessageReceiveView');
			$r_where['to_role_id'] = session('role_id');
			$r_where['message.status'] = array('neq', 1);
			if($_REQUEST["name"]) {
				$r_where['content'] = array('like','%'.$_REQUEST["name"].'%');
			}
			$receive_list = $m_r_message->where($r_where)->order('read_time<>0 asc,send_time desc')->page($p1.',10')->field('from_role_id,to_role_id,message_id,content,send_time,read_time')->select();
			$count1 = $m_r_message->where($r_where)->count();			
			$page = ceil($count1/10);
			if(empty($receive_list)){
				$receive_list = array();
			}else{
				foreach($receive_list as &$v){
					if(empty($v['from_role_id'])){
						$v['from_user_name'] = "系统管理员";
					}else{
						$user_from = getUserByRoleId($v['from_role_id']);
						$v['from_user_name'] = $user_from['user_name'];
					}
					$user_to = getUserByRoleId($v['to_role_id']);
					$v['to_user_name'] = $user_to['user_name'];
				}
			}
			$data['list'] = $receive_list;
			$data['page'] = $page;
			$data['info'] = 'success';
			$data['status'] = 1;
			$this->ajaxReturn($data,'JSON');
		}	
	}	
   
	//发件箱
	public function outbox(){
		if($this->isPost()){			
			import("@.ORG.Page");
			$p1 = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$m_s_message = D('MessageSendView');
			$s_where['from_role_id'] = session('role_id');
			$s_where['message.status'] = array('neq', 2);
			if($_REQUEST["name"]) {
				$s_where['content'] = array('like','%'.$_REQUEST["name"].'%');
			}
			$send_list = $m_s_message->where($s_where)->order('send_time desc')->page($p1.',10')->field('from_role_id,message_id,to_role_id,content,send_time,read_time')->select();		
			$count1 = $m_s_message->where($s_where)->count();			
			$page = ceil($count1/10);
			if(empty($send_list)){
				$send_list = array();
			}else{
				foreach($send_list as &$v){
					$user_from = getUserByRoleId($v['from_role_id']);
					$v['from_user_name'] = $user_from['user_name'];
					if(empty($v['to_role_id'])){
						$v['from_user_name'] = '系统管理员';
					}else{
						$user_to = getUserByRoleId($v['to_role_id']);
						$v['to_user_name'] = $user_to['user_name'];
					}
				}
			}	
			$data['list'] = $send_list;
			$data['page'] = $page;
			$data['info'] = 'success';
			$data['status'] = 1;
			$this->ajaxReturn($data,'JSON');
		}	
	} */
	
	//消息列表
	public function message(){
		$m_message = M('message');
		$role_id = session('role_id');
		
		$where['to_role_id'] = $role_id;
		$where['from_role_id'] = $role_id;
		$where['_logic'] = 'OR';
		
		$message_list = $m_message->where($where)->select();

		$role_id_array = array();
		foreach($message_list as $v){
			$temp = $v['from_role_id'] == $role_id ? $v['to_role_id'] : $v['from_role_id'] ;
			$role_id_array[$temp] = $temp;
			if($v['read_time'] == 0){
				$data['read_time'] = time();
				M('message')->where('to_role_id = %d',$role_id)->save($data);
			}
		}
			
		$role_where['role_id'] = array('in', $role_id_array);
		$role_list = D('RoleView')->where($role_where)->getField('user_name,role_id,img', true);

		$data_array = array();
		
		foreach($role_list as $k=>$v){
			$temp_role_id = $v['role_id'];
			
			$map['to_role_id&from_role_id'] =array($role_id,$temp_role_id,'_multi'=>true);
			$map['from_role_id&to_role_id'] =array($role_id,$temp_role_id,'_multi'=>true);
			$map['_logic'] = 'or';
			
			$res = $m_message->where($map)->order('send_time desc')->find();
			
			$temp_role['user_name'] = $v['user_name'];
			$temp_role['role_id'] = $v['role_id'];
			$temp_role['img'] = $v['img'];
			$temp_role['content']  = $res['content'];
			$temp_role['last_send_time']  = date('Y年m月d日 H:i', $res['send_time']);

			$data_array_info[] = $temp_role; 
		}
		//二维数组排序
		$sort = array(
        'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
        'field'     => 'last_send_time',       //排序字段  
		);  
		$arrSort = array();  
		foreach($data_array_info AS $uniqid => $row){  
			foreach($row AS $key=>$value){  
				$arrSort[$key][$uniqid] = $value;  
			}  
		}
		if($sort['direction']){  
			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $data_array_info);  
		}
		//$data_array = array_multisort('last_send_time','SORT_DESC',$data_array_info);
		//系统消息
		$m_message = M('Message');
		$system['to_role_id'] = $role_id;
		$system['from_role_id'] = 0;
		$system['read_time'] = 0;
		$system_count = $m_message->where($system)->count();
		$data['system_count'] = $system_count;
		//公告数量
		$time_now = time();
		$compare_time = $time_now - 86400*3;//3天范围
		$m_announcement = M('announcement');
		$announcement['department'] = array('like', '%('.session('department_id').')%');
		$announcement['status'] = array('eq', 1);
		$announcement['update_time'] = array('gt',$compare_time);
		$data['announcement_count'] = $m_announcement->where($announcement)->count();
		//日志评论数量
		$log_list = M('log')->where(array('role_id'=>$role_id))->select();
		foreach($log_list as $k=>$v){
			$comment['module'] = 'log';
			$comment['module_id'] = $v['log_id'];
			$comment_list = M('comment')->where($comment)->select();
			if($comment_list){
				foreach($comment_list as $v){
					if($v['update_time'] > $compare_time){
						$log_update = 1;
					}
				}
			}
		}
		if($log_update == 1){
			$data['log_count'] = 1;
		}else{
			$data['log_count'] = 0;
		}
		if($data_array_info){
			$data['message'] = $data_array_info;
		}else{
			$data['message'] = array();
		}
		$this->ajaxReturn($data,'success',1);
	}
	//系统消息
	public function system_message(){
		if($this->isPost()){
			$m_message = M('Message');
			$role_id = session('role_id');
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$message_list = $m_message->where(array('to_role_id'=>$role_id,'from_role_id'=>0))->page($p,'10')->order('send_time desc')->select();
			foreach($message_list as $k=>$v){
				$now_time = time();
				$m_message->where('message_id = %d',$v['message_id'])->setField('read_time',$now_time);
			}
			$count = $m_message->where(array('to_role_id'=>$role_id,'from_role_id'=>0))->count();
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$page = ceil($count/10);
			$data_array = empty($message_list) ? array() : $message_list;
			$data['list'] = $data_array;
			$data['page'] = $page;
			$data['status'] = 1;
			$data['info'] = 'success';
			$this->ajaxReturn($data,'JSON');
		}
	}
	
	//站内信历史详情
	public function messagehistory(){
		$m_message = M('message');
		$role_id = $_REQUEST['role_id'];
		$page = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
		
		$map['to_role_id&from_role_id'] =array($role_id,session('role_id'),'_multi'=>true);
		$map['from_role_id&to_role_id'] =array($role_id,session('role_id'),'_multi'=>true);
		$map['_logic'] = 'or';
		
		$res = $m_message->where($map)->order('send_time desc')->page($page, '20')->select();
		$count_num = $m_message->where($map)->count();
		$page = ceil($count_num/20);
		foreach($res as $k=>$v){
			$temp['message_id'] = $v['message_id'];
			$temp['content'] = $v['content'];
			$temp['send_time'] =  $v['send_time'];
			$temp['self'] = session('role_id') == $v['from_role_id'] ? 1 : 0; 
			$data_array[] = $temp; 
		}
		$data_array = empty($data_array) ? array() : $data_array;
		$data['data'] = $data_array;
		$data['page'] = $page;
		$data['status'] = 1;
		$data['info'] = 'success';
		$this->ajaxReturn($data,'JSON');
	}
	
	//删除站内信
	public function boxdelete(){
		if($this->isPost()){
			$message_id = intval($_GET['message_id']);
			if($message_id){
				if(M('Message')->where(array('message_id'=>$message_id))->delete()){
					$this->ajaxReturn('','success',1);
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{
				$this->ajaxReturn('','error',3);
			}
		}
	}
	
	
	//站内信详情
	public function boxview(){
		if($this->isPost()){			
			$id = intval($_GET['id']);	
			if($id){
				$m_message = D('MessageView');
				$where['message_id'] = $id;
				$where['_complex'] = array('to_role_id'=>session('role_id'),'from_role_id'=>session('role_id'),'_logic'=>'or');
				$info = $m_message->where($where)->order('read_time<>0 asc,send_time desc')->page($p1.',10')->field('send_time,content,to_role_id,from_role_id')->find();				
				if($info){
					if($info['read_time'] == 0 && $info['to_role_id'] == session('role_id')){
						$m_message->where(array('message_id'=>$id,'to_role_id'=>session('role_id')))->save(array('read_time'=>time()));
					}
					if($info['from_role_id'] != session('role_id')){
						$name = M('User')->where('role_id = %d',$info['from_role_id'])->getField('name');
					}else{
					    $name = M('User')->where('role_id = %d',$info['to_role_id'])->getField('name');
					}					
					$info['name'] = $name ? $name : '系统管理员';
					foreach($info as &$v){
						$v = empty($v) ? ' ' : $v;
					}					
					$this->ajaxReturn($info,'success',1);	
				}else{
					$this->ajaxReturn($info,'error',3);	
				}
			}else{
				$this->ajaxReturn('','error',2);	
			}					
		}
	}
	
	//发送站内信
	public function send(){
		$m_user = M('User');
		$m_token = M('RUserToken');
		if($this->isPost()){
			if($_POST['to_role_id']){
				$role_id = explode(',',trim($_POST['to_role_id']));
				foreach($role_id as $v){
					$to_role = intval($v);
					sendMessage($to_role,trim($_POST['content']));
					$user_info = $m_user->where('role_id = %d',$v)->find();
					if($user_info['model'] == 1){
						$token_ios[] = $m_token->where('role_id = %d',$v)->getField('token');
					}elseif($user_info['model'] == 2){
						$token_and[] = $m_token->where('role_id = %d',$v)->getField('token');
					}
				}
				//$title = '站内信';
				//Xinge($token_ios,$title,$_POST['content'],2,1);
				//Xinge($token_and,$title,$_POST['content'],2,2);
				$this->ajaxReturn('','success',1);								
			}
		}
	}
	
	
	public function comment1(){
		$role_id = session('role_id');
		/* $time_now = time();
		$compare_time = $time_now - 86400*3;//3天范围
		$where['update_time'] = array('gt',$compare_time); */
		$where['module'] = 'log';
		$comment_list = D('CommentRoleView')->where($where)->order('update_time desc')->select();
		if($comment_list){
			//去除二维数组相同值
			function array_multi_unique($ar, $filter=array()){
				if(!empty($filter)) {
					$_v = array_fill_keys($filter, ' ');
					$_ar = array();
					foreach($ar as $k => $v) {
						$_ar[$k] = array_intersect_key($v, $_v);
					}
				} else {
					$_ar = $ar;
				}
				$_ar = array_map('serialize', $_ar);
				$_ar = array_unique($_ar);
				$_ar = array_map('unserialize', $_ar);
			 
				if(!empty($filter)) {       
					return array_intersect_key($ar, $_ar);
				} else {
					return $_ar;
				}
			}
			$comment_arr = array_multi_unique($comment_list, array('module_id'));
			foreach($comment_arr as $k=>$v){
				$log_info = D('LogRoleView')->where(array('log_id'=>$v['module_id']))->find();
				$comment_arr[$k] = array_merge($log_info,$v);
			}
			foreach ($comment_arr as $k => $v) {
				$edition[] = $v['update_time'];
			}
			array_multisort($edition, SORT_ASC, $comment_arr);
			$this->ajaxReturn($comment_arr,'success',1);
		}else{
			$this->ajaxReturn('','error',2);
		}
	}
	public function comment2(){
		$role_id = session('role_id');
		$page = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
		$comment_sql = M()->query("(select * from `5kcrm_comment` where module='log' and to_role_id = ".$role_id." group by module_id order by create_time desc) order by create_time desc limit 10");
		$comment_count_array = mysql_query("select count(*) from `5kcrm_comment` where module='log' and to_role_id = ".$role_id." group by module_id");
		while($count = mysql_fetch_array($comment_count_array)){
			$count_array[] = $count;
		}
		$count_num = count($count_array);
		$comment_list=array();
		while($row = mysql_fetch_array($comment_sql)){
			$comment_list[] = $row;
		}
		foreach($comment_list as $k=>$v){
			$log_info = D('LogRoleView')->where(array('log_id'=>$v['module_id']))->find();
			$omment_list[$k] = array_merge($log_info,$v);
		}
		$page = ceil($count_num/10);
		$data['comment_list'] = $omment_list;
		$data['page'] = $page;
		if($comment_list){
			$this->ajaxReturn($data,'success',1);
		}else{
			$this->ajaxReturn('','error',2);
		}
	}
	//查看全部评论
	public function comment3(){
		$where['to_role_id'] = session('role_id');
		$where['module'] = 'log';
		$p = isset($_POST['p']) ? intval($_POST['p']) : 1;
		$comment_list = D('CommentRoleView')->where($where)->page($p.',10')->order('update_time desc')->select();
		foreach($comment_list as $k=>$v){
			$log_info = D('LogRoleView')->where(array('log_id'=>$v['module_id']))->find();
			$comment_list_array[$k] = array_merge($log_info,$v);
		}
		foreach($comment_list_array as $k=>$v){
			if($v){
				$comment_list_info[] = $v;
			}
		}
		$comment_count = count($comment_list_info);
		$page = ceil($comment_count/10);
		$data['comment_list'] = $comment_list_info;
		$data['page'] = $page;
		if($comment_list_info){
			$this->ajaxReturn($data,'success',1);
		}else{
			$this->ajaxReturn('','error',2);
		}
	}
	//评论我的
	public function comment(){
		/* if($this->isPost()){ */
			$where['to_role_id'] = session('role_id');
			$where['module'] = 'log';
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1;
			$m_comment = M('Comment');
			$comment_list = $m_comment->where($where)->page($p.',10')->order('update_time desc')->select();
			foreach($comment_list as $k=>$v){
				$m_log = M('Log');
				$log_info = $m_log->where('log_id = %d',$v['module_id'])->find();
				$comment_list[$k]['subject'] = $log_info['subject'];
				$comment_list[$k]['update_date'] = $log_info['update_date'];
				$comment_list[$k]['role_id'] = $log_info['role_id'];
				$comment_list[$k]['log_id'] = $log_info['log_id'];
			}
			//$comment_list = D('CommentLogView')->where($where)->page($p.',10')->order('update_time desc')->select();
			$comment_count = D('CommentLogView')->where($where)->count();
			
			foreach($comment_list as $k=>$v){
				$role_info = M('role')->where(array('role_id'=>$v['creator_role_id']))->field('user_id,position_id')->find();
				$log_role_info = M('role')->where(array('role_id'=>$v['role_id']))->field('user_id,position_id')->find();
				$user_info = M('user')->where(array('user_id'=>$role_info['user_id']))->field('name,img')->find();
				$log_user_info = M('user')->where(array('user_id'=>$log_role_info['user_id']))->field('name,img')->find();
				$department_id = M('position')->where(array('position_id'=>$role_info['position_id']))->getField('department_id');
				$log_department_id = M('position')->where(array('position_id'=>$log_role_info['position_id']))->getField('department_id');
				$comment_list[$k]['user_name'] = $user_info['name'];
				$comment_list[$k]['log_user_name'] = $log_user_info['name'];
				$comment_list[$k]['img'] = $user_info['img'];
				$comment_list[$k]['log_img'] = $log_user_info['img'];
				$comment_list[$k]['role_name'] = M('position')->where(array('position_id'=>$role_info['position_id']))->getField('name');
				$comment_list[$k]['log_role_name'] = M('position')->where(array('position_id'=>$log_role_info['position_id']))->getField('name');
				$comment_list[$k]['department_name'] = M('role_department')->where(array('department_id'=>$department_id))->getField('name');
				$comment_list[$k]['log_department_name'] = M('role_department')->where(array('department_id'=>$log_department_id))->getField('name');
			}
			$page = ceil($comment_count/10);
			$data['comment_list'] = $comment_list;
			$data['page'] = $page;
			if($comment_list){
				$this->ajaxReturn($data,'success',1);
			}else{
				$data['comment_list'] = array();
				$data['page'] = 0;
				$this->ajaxReturn($data,'success',1);
			}
		/* } */
	}
	//查看我评论的
	public function replay(){
		if($this->isPost()){
			$where['creator_role_id'] = session('role_id');
			$where['to_role_id'] = array('neq',0);
			$where['module'] = 'log';
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1;
			$comment_list = $m_comment->where($where)->page($p.',10')->order('update_time desc')->select();
			foreach($comment_list as $k=>$v){
				$m_log = M('Log');
				$log_info = $m_log->where('log_id = %d',$v['module_id'])->find();
				$comment_list[$k]['subject'] = $log_info['subject'];
				$comment_list[$k]['update_date'] = $log_info['update_date'];
				$comment_list[$k]['role_id'] = $log_info['role_id'];
				$comment_list[$k]['log_id'] = $log_info['log_id'];
			}
			//$comment_list = D('CommentLogView')->where($where)->page($p.',10')->order('update_time desc')->select();
			$comment_count = D('CommentLogView')->where($where)->count();
			//var_dump($comment_list);die;
			
			foreach($comment_list as $k=>$v){
				$role_info = M('role')->where(array('role_id'=>$v['creator_role_id']))->field('user_id,position_id')->find();
				$log_role_info = M('role')->where(array('role_id'=>$v['role_id']))->field('user_id,position_id')->find();
				$user_info = M('user')->where(array('user_id'=>$role_info['user_id']))->field('name,img')->find();
				$log_user_info = M('user')->where(array('user_id'=>$log_role_info['user_id']))->field('name,img')->find();
				$department_id = M('position')->where(array('position_id'=>$role_info['position_id']))->getField('department_id');
				$log_department_id = M('position')->where(array('position_id'=>$log_role_info['position_id']))->getField('department_id');
				$comment_list[$k]['user_name'] = $user_info['name'];
				$comment_list[$k]['log_user_name'] = $log_user_info['name'];
				$comment_list[$k]['img'] = $user_info['img'];
				$comment_list[$k]['log_img'] = $log_user_info['img'];
				$comment_list[$k]['role_name'] = M('position')->where(array('position_id'=>$role_info['position_id']))->getField('name');
				$comment_list[$k]['log_role_name'] = M('position')->where(array('position_id'=>$log_role_info['position_id']))->getField('name');
				$comment_list[$k]['department_name'] = M('role_department')->where(array('department_id'=>$department_id))->getField('name');
				$comment_list[$k]['log_department_name'] = M('role_department')->where(array('department_id'=>$log_department_id))->getField('name');
			}
			$page = ceil($comment_count/10);
			$data['comment_list'] = $comment_list;
			$data['page'] = $page;
			if($comment_list){
				$this->ajaxReturn($data,'success',1);
			}else{
				$data['comment_list'] = array();
				$data['page'] = 0;
				$this->ajaxReturn($data,'success',1);
			}
		}
	}
	//获取权限
	public function permission(){
		if($this->isPost()){
			$params = json_decode($_POST['params'],true);
			$m = trim($params['module']);
			$a = trim($params['action']);
			if(checkPerByAction($m, $a)){
				$this->ajaxReturn('','success',1);
			}else{
				$this->ajaxReturn('您没有权限','error',-2);
			}
		}
	}
	//获取自定义字段
	public function fields(){
		if($this->isPost()){
			$m_fields = M('Fields');
			$params = json_decode($_POST['params'],true);
			$m = trim($params['module']);
			$a = trim($params['action']);
			if(checkPerByAction($m, $a)){
				if($m){
					$fields_list = $m_fields->where(array('model'=>$m))->order('is_main desc,order_id asc')->field('is_main,field,name,form_type,default_value,max_length,is_unique,is_null,is_validate,in_add,input_tips,setting')->select();
					foreach($fields_list as $k=>$v){
						if($v['setting']){
							//将内容为数组的字符串格式转换为数组格式
							eval("\$setting = ".$v['setting'].'; ');
							$fields_list[$k]['setting'] = $setting;
						}
					}
					$data['data'] = $fields_list;
					$data['info'] = 'success';
					$data['status'] = 1;
					$this->ajaxReturn($data,'JSON');
				}else{
					$this->ajaxReturn('参数错误','error',2);
				}
			}else{
				$this->ajaxReturn('您没有权限','error',-2);
			}
		}
	}
	//自定义字段验重
	//params : field 字段名, val 值 ,id 排除当前数据验重,model = 需要查询的模块名
	public function validate() {
		if($this->isPost()){
			$params = json_decode($_POST['params'],true);
			$model = trim($params['model']);
			$field = trim($params['field']);
			$val = trim($params['val']);
			if(!$field || !$val){
				$this->ajaxReturn('','',3);
			}
			$field_info = M('Fields')->where('model = "%s" and field = "%s"',$model,$field)->find();
			if($model == 'contacts'){
				$m_fields = $field_info['is_main'] ? D('contacts') : D('ContactsData');
			}elseif($model == 'customer'){
				$m_fields = $field['is_main'] ? D('Customer') : D('CustomerData');
			}elseif($model == 'business'){
				$m_fields = $field['is_main'] ? D('Business') : D('BusinessData');
			}elseif($model == 'product'){
				$m_fields = $field['is_main'] ? D('Product') : D('ProductData');
			}elseif($model == 'leads'){
				$m_fields = $field['is_main'] ? D('Leads') : D('LeadsData');
			}
			$where[$field] = array('eq',$val);
			if($params['id']){
                $where[$m_fields->getpk()] = array('neq',$params['id']);
            }
			if($fields){
				if ($m_fields->where($where)->find()) {
					$this->ajaxReturn("","",1);
				} else {
					$this->ajaxReturn("","",0);
				}
			}else{
				$this->ajaxReturn("","",0);
			}
		}
	}
}
