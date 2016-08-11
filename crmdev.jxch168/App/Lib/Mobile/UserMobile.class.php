<?php
/**
 *
 * 	手机相关模块
 *	登录接口，首页接口
 **/
class UserMobile extends Action {
	/**
	 *	permission 未登录可访问
	 * 	allow 登录访问
	 **/
	public function _initialize(){
		$action = array(
			'permission'=>array('login','aa'),
			'allow'=>array('mylog_add','mylog','mylog_view','permission','mylog_edit','index','update','logout','mylog_delete','uploadhead','comment_add','resetpw','praise_remove','praise_add')
		);
		B('AppAuthenticate', $action);
	}


	//获得岗位权限
	public function permission(){
		$m_permission = M('Permission');
		$row = $m_permission->where(array('position_id'=>session('position_id')))->field('url')->select();
		$permission = array();
		$model = '';
		$existModel = array('customer','business','knowledge','contacts','product','leads','contract','task');
		foreach($row as $v){
			$tmp = explode('/',$v['url']);
			if($model != $tmp[0] && $tmp[1] == 'index'){
				$model = $tmp[0];
				if(in_array($model,$existModel) && !in_array($model,$permission)){
					$permission[] = $model;
				}
			}
		}
		return $permission;
	}

	/*
		1.登录成功
		2.用户名或密码错误！
		3.您的账号未通过审核，请联系管理员！
		4.您的帐号正在审核中···请耐心等待！
		5.系统没有给您分配任何岗位，请联系管理员！
		6.用户名或密码为空
	*/
	public function login(){
		if ($this->isPost()){
			$m_user = M('user');
			$user = $m_user->where(array('name' => trim($_REQUEST['name'])))->find();
			if ($user['password'] == md5(trim($_POST['password']) . $user['salt'])){
				if (-1 == $user['status']) {
					$this->ajaxReturn('','error',3);
				} elseif (0 == $user['status']) {
					$this->ajaxReturn('','error',4);
				}else{
					$d_role = D('RoleView');
					$role = $d_role->where('user.user_id = %d', $user['user_id'])->find();
					if (!is_array($role) || empty($role)) {
						$this->ajaxReturn('','error',5);
					} else {
						$model = substr($_POST['model'],0,1);
						if($model == 'i'){
							session('model',1);//IOS
							$model_type = 1;
						}elseif($model == 'A'){
							session('model',2);//Android
							$model_type = 2;
						}
						$m_user->where(array('user_id'=>$user['user_id']))->setField('model',$model_type);
						if($user['category_id'] == 1){
							session('admin', 1);
						}else{
							session('admin', null);
						}
						session('role_id', $role['role_id']);
						session('position_id', $role['position_id']);
						session('role_name', $role['role_name']);
						session('department_id', $role['department_id']);
						session('name', $user['name']);
						session('user_id', $user['user_id']);
						//session('mobile_user_id', $user['user_id']);
						//userLog($user['user_id']);
						$data['info'] = 'success';
						$data['status'] = 1;
						$data['img'] = empty($user['img']) ? '' : $user['img'];
						$data['session_id'] = session_id();

						$data['token'] = md5(md5($data['session_id']).time());
						M('user')->where('user_id = %d',session('user_id'))->setField(array('token'=>$data['token'],'token_time'=>time()));

						$access_token = $_POST['access_token'];
						if($access_token){
							$user_id_info = M('user_udid')->where(array('access_token'=>$access_token))->getField('user_id');
							if(!$user_id_info){
								$data = array();
								$data['user_id'] = $user['user_id'];
								$data['access_token'] = $access_token;
								$data['update_time'] = time();
								M('user_udid')->add($data);
							}else{
								if($user_id_info != session('user_id')){
									$data = array();
									$data['user_id'] = session('user_id');
									$data['update_time'] = time();
									M('user_udid')->where(array('access_token'=>$access_token))->save($data);
								}
							}
						}
						//信鸽推送token
						$m_token = M('token');
						$token = $_POST['token'];
						if($_POST['token']){
							$token_info = $m_token->where(array('role_id'=>$user['role_id']))->find();
							if($token_info){
								if($token_info['token'] != $token){
									$m_token->where('role_id = %d',$user['role_id'])->setField('token',$token);
								}
							}else{
								$m_token->token = $token;
								$m_token->role_id = $user['role_id'];
								$m_token->add();
							}
						}
						//设备在线
						$m_user->where(array('user_id'=>$user['user_id']))->setField('online',1);
						if(session('?admin')){
							$data['admin'] = 1;
						}else{
							$data['admin'] = 0;
							$data['permission'] = $this->permission();
						}
						$data['role_id'] = $role['role_id'];
						$data['name'] = $user['name'];
						$this->ajaxReturn($data,'JSON');
					}
				}
			}else{
				$this->ajaxReturn('','error',2);
			}
		}
	}

	public function logout(){
		//$m_user = M('User');
		//$m_user->where(array('user_id'=>session('user_id')))->setField('online',2);
		session(null);
		$this->ajaxReturn('',"success",1);
	}

	/*
		1.日志添加成功
		2.日志添加失败
		3.没有接收到参数
		4.标题空
		5.内容空
	*/
	public function mylog_add(){
		if($this->isPost()){
			if(!trim($_POST['rztitle'])) $this->ajaxReturn('',"error",4);;
			if(!trim($_POST['rzcontent'])) $this->ajaxReturn('',"error",5);;
			$log = M('Log');
			$d_role = D('RoleView');
			$role_id = session('role_id');
			$data['subject'] = $_POST['rztitle'];
			$data['content'] = $_POST['rzcontent'];
			$data['create_date'] = time();
			$data['update_date'] = time();
			$data['role_id'] = $role_id;
			$data['category_id'] = $_POST['category_id'];

			if($log->add($data)){
				$this->ajaxReturn(session_id(),"success",1);
			}else{
				$this->ajaxReturn('',"error",2);
			}
		}
	}
	public function mylog(){
		if($this->isPost()){
			$m_log = D('LogView');
			$m_comment = M('Comment');
			$by = isset($_REQUEST['by']) ? trim($_REQUEST['by']) : '';
			$d_role = D('RoleView');
			$user_id = session('user_id');
			$role_id = session('role_id');
			$where = array();
			$params = array();
			$order = "";
			if(isset($_POST['search'])){
				$where['subject'] = array('like','%'.trim($_POST['search']).'%');
			}
			// $below_ids = getSubRoleByRole($role_id,false);
			// $all_ids = getSubRoleByRole($role_id);
			$below_ids = getSubRoleId(false);
			$all_ids = getSubRoleId();
			$module = isset($_GET['module']) ? trim($_GET['module']) : '';
			switch ($by) {
				case 'today' : $where['create_date'] =  array('gt',strtotime(date('Y-m-d', time()))); break;
				case 'week' : $where['create_date'] =  array('gt',(strtotime(date('Y-m-d', time())) - (date('N', time()) - 1) * 86400)); break;
				case 'month' : $where['create_date'] = array('gt',strtotime(date('Y-m-01', time()))); break;
				case 'add' : $order = 'create_date desc';  break;
				case 'update' : $order = 'update_date desc';  break;
				case 'sub' : $where['role_id'] = array('in',implode(',', $below_ids)); break;
				case 'me' : $where['role_id'] = $role_id; break;
				default :  $where['role_id'] = array('in',implode(',', $all_ids)); break;
			}
			if (!isset($where['role_id'])) {
				$where['role_id'] = array('in',implode(',', getSubRoleByRole($role_id)));
			}
			if ($order) {
				$list = $m_log->where($where)->field('log_id,role_id,subject')->order($order)->limit(15)->select();
			} else {
				$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
				if($_GET['act'] == 'new'){
					$time_now = time();
					$compare_time = $time_now - 86400*3;
					$where['role_id'] = array('in',implode(',', getSubRoleId()));
					$where['update_date'] = array('gt',$compare_time);
				}
				$list = $m_log->where($where)->page($p.',10')->order('log_id desc')->select();
				$count = $m_log->where($where)->count();
				$page = ceil($count/10);
			}

			foreach($list as $k=>$v){
				if(empty($v['subject'])){
					$list[$k]['subject'] = msubstr($content,0,15);
				}

				$list[$k]['content'] = msubstr($list[$k]['content'],0,50);
				$comment_cont = $m_comment->where("module='log' and module_id=%d", $list[$k]['log_id'])->count();
				$list[$k]['comment_count'] = $comment_cont;
				$list[$k]['praise_count'] = M('Praise')->where('log_id = %d',$list[$k]['log_id'])->count();
				if(M('Praise')->where('log_id = %d and role_id = %d',$list[$k]['log_id'],$role_id)->find()){
					$list[$k]['is_praised'] = 1;
				}else{
					$list[$k]['is_praised'] = 0;
				}
			}

			$list = empty($list) ? array() : $list;
			$data['data'] = $list;
			$data['page'] = $page;
			$data['status'] = 1;
			$data['info'] = 'success';
			$this->ajaxReturn($data,'JSON');
		}
	}
	//日志详情
	public function mylog_view(){
		if($this->isPost()){
			$m_log = D('LogView');
			$m_comment = M('Comment');
			$where = array();
			if(intval($_POST['log_id'])){
				$log_id = $_POST['log_id'];
				$where['log_id'] = $log_id;
			}else{
				$this->ajaxReturn('',"error",5);
			}

			$role_id = session('role_id');
			$list = $m_log->where($where)->find();
//			$list['create_date'] = date('Y-m-d H:i:s', $list['create_date']);
			$creator = getUserByRoleId($list['role_id']);
			$list['praise_count'] = M('Praise')->where('log_id = %d',$log_id)->count();
			$list['comment_count'] = $m_comment->where("module='log' and module_id=%d", $list['log_id'])->count();
			if(M('Praise')->where('log_id = %d and role_id = %d',$log_id,$role_id)->find()){
				$list['is_praised'] = 1;
			}else{
				$list['is_praised'] = 0;
			}
			if (in_array($list['role_id'], getSubRoleId(true))) {
				$list['is_comment'] = 1;
			}else{
				$list['is_comment'] = 0;
			}
			if($m_comment->where("module='log' and module_id=%d", $list['log_id'])->select()){
				$commnet = D('CommentView')->where('module = "log" and module_id = %d', $log_id)->order('comment.create_time desc')->select();
				foreach($commnet as $k => $v){
					$value[$k]['comment_id'] = $v['comment_id'];
					$value[$k]['content'] = $v['content'];
					$value[$k]['department_name'] = $v['department_name'];
					$value[$k]['user_name'] = $v['user_name'];
					$value[$k]['img'] = $v['img'];
					$value[$k]['create_time'] = $v['create_time'];
				}
				$list['comment_list'] = $value;
			}else{
				$list['comment_list'] = array();
			}
			if(!empty($list)){
				$data['list'] = $list;
				$data['status'] = 1;
				$data['info'] = 'success';
				$this->ajaxReturn($data,"JSON");
			}else{
				$this->ajaxReturn('',"error",0);
			}
		}

	}
	public function mylog_edit(){
		if($this->isPost()){
			if($_POST['log_id']){
				$create_time = M('Log')->where('log_id = %d',$_POST['log_id'])->getField('create_date');
				$long = time()-$create_time;
				if($long>86400){
					$this->ajaxReturn('','error',2);die();
				}
			}
			$data['subject'] = $_POST['rztitle'];
			$data['content'] = $_POST['rzcontent'];
			$data['update_date'] = time();
			$data['role_id'] = session('role_id');
			$data['category_id'] = $_POST['category_id'];
			$data['log_id'] =$_POST['log_id'];
			$m_log = M('Log');
			$m_log -> create($data);
			$result = $m_log->save();
			if($result !== false){
				$this->ajaxReturn('','success',1);
			}else{
				$this->ajaxReturn('','error',3);
			}
		}
	}
	public function mylog_delete(){
		if($this->isPost()){
			if(empty($_POST['log_id'])){
				$this->ajaxReturn('',"error",0);
			}else{
				$log_id = intval($_POST['log_id']);
				$m_log = M('Log');
				if ($m_log->where('log_id = %d',$log_id)->delete()){
					$this->ajaxReturn('',"success",1);
				} else {
					$this->ajaxReturn('',"error",2);
				}
			}
		}
	}
	/*
	*   日志评论
	*/
	public function comment_add(){

		if($this->isPost()){
			//$module = $_POST['module'];
			$module_id =  intval($_POST['log_id']);
			$to_role_id = intval($_POST['to_role_id']);
			$content = trim($_POST['content']);
			$m_comment = M('Comment');
			$m_comment->creator_role_id = session('role_id');
			$m_comment->to_role_id = $to_role_id;
			$m_comment->content = $content;
			$now = time();
			$m_comment->create_time = $now;
			$m_comment->update_time = $now;
			$m_comment->module = 'log';
			$m_comment->module_id = $module_id;
			if($comment_id = $m_comment->add()){
				$info['username'] = session("name");
				$info['time'] = $now;
				$data['list'] = $info;
				$data['status'] = 1;
				$data['info'] = 'success';

				$commnet = D('CommentView')->where('module = "log" and module_id = %d', $module_id)->order('comment.create_time desc')->select();
				foreach($commnet as $k => $v){
					$value[$k]['comment_id'] = $v['comment_id'];
					$value[$k]['content'] = $v['content'];
					$value[$k]['department_name'] = $v['department_name'];
					$value[$k]['user_name'] = $v['user_name'];
					$value[$k]['img'] = $v['img'];
					$value[$k]['create_time'] = $v['create_time'];
				}
				$data['comment_list'] = $value;

				$m_id =  'log_id';
				/* if(intval($_POST['message_alert']) == 1) {
					sendMessage($_POST['to_role_id'], L('THE MAIN CONTENTS ARE AS FOLLOWS',array(createCommentAlertInfo($module, $module_id),chr(10),$_POST['content'])),1);
				} */
				$this->ajaxReturn($data,"JSON");

			}else{
				$this->ajaxReturn('','error',2);
			}
		}
	}
	/*
	 *	点赞
	 */
	public function praise_add(){
		if($this->isPost()){
			$log_id = intval($_POST['log_id']);
			if($log_id){
				$m_praise = M('praise');
				$m_praise->role_id = session('role_id');
				$m_praise->log_id = intval($log_id);
				if($m_praise->add()){
					$number = $m_praise->where('log_id = %d',$log_id)->count();
					$this->ajaxReturn($number,'success',1);
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{
				$this->ajaxReturn('','error',3);
			}
		}
	}
	/*
	 *	取消赞
	 */
	public function praise_remove(){
		if($this->isPost()){
			$log_id = intval($_POST['log_id']);
			if($log_id){
				$m_praise = M('praise');
				$where['role_id'] = session('role_id');
				$where['log_id'] = $log_id;
				if($m_praise->where($where)->delete()){
					$number = $m_praise->where('log_id = %d',$log_id)->count();
					$this->ajaxReturn($number,'success',1);
				}else{
					$this->ajaxReturn('','error',2);
				}
			}else{
				$this->ajaxReturn('','error',3);
			}
		}
	}


	/*s
	 * 1、成功返回数据。
	 * 0、非POST方式提交。
	 */
	 //用户个人中心
	public function index(){
		if($this->isPost()){
			$user_id = M('User')->where('user_id = "%d"',session('user_id'))->getField('user_id');
			$d_role = D('RoleView');
			$role_info = $d_role->where('user.user_id = %d', $user_id)->find();
			/* $department_name = M('PositionDepartment')->where('department_id = "%d"',session('department_id'))->getField('name');
			$list['department_name'] = empty($department_name) ? "" : $department_name;
			$position_name = M('Position')->where('position_id = "%d"',session('position_id'))->getField('name');
			$list['position_name'] = empty($position_name) ? "" : $position_name; */
			$role_info['department_name'] = empty($role_info['department_name']) ? "" : $role_info['department_name'];
			$role_info['position_name'] = empty($role_info['role_name']) ? "" : $role_info['role_name'];
			$role_info['name'] = $role_info['user_name'];
			if(!empty($role_info)){
				$data['data'] = $role_info;
			}
			$data['status'] = 1;
			$data['info'] = 'success';
			$this->ajaxReturn($data,'JSON');
		}
	}

	/*
	 * 1、保存成功。
	 * 2、保存失败。
	 * 0、非POST方式提交。
	 */
	 //用户修改资料
	public function update(){
		if($this->isPost()){
			//session(null);
			$data['email'] = trim($_POST['email']);
			$data['telephone'] = trim($_POST['telephone']);
			$data['address'] = trim($_POST['address']);
			$result = M('User')->where('user_id = "%d"',session('user_id'))->save($data);
			if($result){
				$this->ajaxReturn($_SESSION,'success',1);
			}else{
				$this->ajaxReturn($_SESSION,'success',1);
			}
		}
	}

	/*
	 * 1.修改成功。
	 * 2.修改失败。
	 * 3.没有此用户。
	 * 4.旧密码不正确。
	 * 0.非POST方式提交。
	 */
	//修改密码
	public function resetpw(){
		if($this->isPost()){
			$verify_code = trim($_POST['verify_code']);
			$user_id = session('user_id');
			$m_user = M('User');
			$user = $m_user->where('user_id = %d', $user_id)->find();
			if (is_array($user) && !empty($user)) {
				if (md5(md5($verify_code) . $user['salt']) == $user['password']) {
					if ($_POST['password']) {
						$password = md5(md5(trim($_POST["password"])) . $user['salt']);
						if($m_user->where('user_id = %d',$user_id)->save(array('password'=>$password, 'lostpw_time'=>0))){
							$this->ajaxReturn('','success',1);
						}else{
							$this->ajaxReturn('','error',2);
						}
					}
				}else{
					$this->ajaxReturn('','error',4);
				}
			}else{
				$this->ajaxReturn('','error',3);
			}
		}
	}

	/*
	 * 0、附件上传目录不可写
	 * 1、上传成功
	 * 2、上传失败
	 * 3、各种错误
	 * 4、写入数据库失败
	 */
	 //上传头像
	public function uploadhead(){
       if($this->isPost()){
	       if (isset($_FILES['img']['size']) && $_FILES['img']['size'] > 0) {
				import('@.ORG.UploadFile');
				$upload = new UploadFile();
				$upload->maxSize = 2000000;
				$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');
				$dirname = UPLOAD_PATH.'/head/';
				if (!is_dir($dirname) && !mkdir($dirname, 0777, true)) {
					//附件上传目录不可写(不会返回)  $this->ajaxReturn('','error',0);
				}
				$upload->savePath = $dirname;
				if(!$upload->upload()) {
					  $data['info'] = $upload->getErrorMsg();
					  $data['status'] = 3;
					  $this->ajaxReturn($data,'JSON');
				}else{
					$info =  $upload->getUploadFileInfo();
				}
				if(is_array($info[0]) && !empty($info[0])){
					$imgPath = UPLOAD_PATH.'/head/' . $info[0]['savename'];
				}else{
					//上传失败(不会返回)  $this->ajaxReturn('','error',2);
				}
				$m_user = M('User');
				$uid = session('user_id');
				$oldImg = $m_user->where('user_id = %d',$uid)->getField('img');
				if($oldImg){
					if (file_exists($oldImg)) {
						@unlink($oldImg);
					}
				}
				$r = $m_user->where('user_id = %d',$uid)->setField('img',$imgPath);
				if($r){
				   $this->ajaxReturn($imgPath,'success',1);
				}else{
				   $this->ajaxReturn('','error',2); //写入数据库失败
				}
			}
	     }
	}
	//获取负责人列表
	public function listdialog(){
		if($this->isPost()){
			//获取部门列表
			$departments = M('roleDepartment')->select();
			$department_id = M('position')->where('position_id = %d', session('position_id'))->getField('department_id'); 
			$departmentList[] = M('roleDepartment')->where('department_id = %d', $department_id)->find();
			$departmentList = array_merge($departmentList, getSubDepartment($department_id,$departments,''));
			if($p == 1){
				$data['departmentList'] = $departmentList;
			}
			
			$d_role_view = D('RoleView');
			$where = '';
			if($_POST['department_id']){
				$where = 'position.department_id eq '.$_POST['department_id'].' and';
			}
			$all_role = M('role')->where('user_id <> 0')->select();
			$below_role = getSubRole(session('role_id'), $all_role);
			$below_ids[] = session('role_id');
			foreach ($below_role as $key=>$value) {
				$below_ids[] = $value['role_id'];
			}
			$where = 'role.role_id in ('.implode(',', $below_ids).') and';
			$where .= ' user.status = 1';
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$role_list =  $d_role_view->where($where)->order('role_id')->page($p.',10')->field('user_id,role_id,user_name,img,department_id,department_name')->select();
			if($_GET['by'] == 'examine'){
				$position_ids = M('Permission')->where("url = 'examine/add_examine'")->getField('position_id',true);
				 array_unshift($position_ids,"1");
				$role_list =  $d_role_view->where('user.status = 1 and role.position_id in ('.implode(',', $position_ids).')')->order('role_id')->limit(10)->select();
			}
			$role_list = empty($role_list) ? array() : $role_list;
			$count =  $d_role_view->where($where)->count();
			$page = ceil($count/10);
			$data['list'] = $role_list;
			$data['page'] = $page;
			$this->ajaxReturn($data,'success',1);
		}
	}
}