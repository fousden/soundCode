<?php
/**
 *	签到
 **/
class SignMobile extends Action{
	/**
	 *	permission 未登录可访问
	 * 	allow 登录访问
	 **/
	public function _initialize(){
		$action = array(
			'permission'=>array(),
			'allow'=>array('index','view','sign_in','customer_list')
		);
		B('AppAuthenticate', $action);
	}
	public function index(){
		if($this->isPost()){
			$where = array();
			$by = isset($_GET['by']) ? trim($_GET['by']) : '';
			switch ($by) {			
				case 'sub' : $where['role_id'] = array('in',implode(',', getSubRoleId(false))); break;
				case 'me' : $where['role_id'] = session('role_id'); break;
				default :
					$where['role_id'] = array('in',implode(',', getSubRoleId())); break;
				break;
			}
			
			$where['action_name'] = array('not in',array('completedelete','delete','view'));
			$where['module_name'] = array('in',array('sign'));
			$map['business.is_deleted'] = array('neq',1);
			$map['customer.is_deleted'] = array('neq',1);
			$map['sign.sign_id'] = array("gt",0);
			$map['_logic'] = 'or';
			$where['_complex'] = $map;

			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$log = D('ActionLogView')->where($where)->page($p,10)->order('create_time desc')->select();

			$logCount = D('ActionLogView')->where($where)->count();
			$page = ceil($logCount/10);
			$action_name = array('sign_in'=>'进行');
			$module_name = array('sign'=>'签到');
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
				$daily['role_id'] = array('in',implode(',', getSubRoleId()));
				$daily['update_date'] = array('gt',$compare_time);
				$count['log'] = M('Log')->where($daily)->count();		
				$data['count'] = $count;
			}			
			$data['page'] = $page;
			$data['list'] = $list;
			$data['info'] = 'success';
			$data['status'] = 1;		
			$this->ajaxReturn($data,'JSON');
		}else{
			$this->ajaxReturn('非法请求',"error",3);
		}
	}
	
	public function view(){
		if($this->isPost()){
			$sign_id = $_REQUEST['sign_id'];
			if(empty($sign_id)){
				$this->ajaxReturn('','error','2');
			}else{
				$sign = M('Sign')->where('sign_id = %d',$sign_id)->find();
				$customer = M('Customer')->where('customer_id=%d',$sign['customer_id'])->find();
				$sign['customer_name'] = $customer['name'];
				$img = M('SignImg')->where('sign_id = %d',$sign['sign_id'])->select();
				if($img){
					foreach($img as $k => $v){
						$sign['img'][$k] = $v['path'];
					}
				}
				$this->ajaxReturn($sign,'success','1');
			}
		}
	}
	public function sign_in(){
		if($this->isPost()){
			$m_sign = M('Sign');
			$m_sign->create();
			$m_sign->role_id = session('role_id');
			$m_sign->create_time = time();
			$sign_id = $m_sign->add();
			if($sign_id){
				if (array_sum($_FILES['img']['size'])) {
					//如果有文件上传 上传附件
					import('@.ORG.UploadFile');
					//导入上传类
					$upload = new UploadFile();
					//设置上传文件大小
					$upload->maxSize = 20000000;
					//设置附件上传目录
					$dirname = UPLOAD_PATH . '/sign/'.date('Ym', time()).'/'.date('d', time()).'/';
					$upload->allowExts  = array('jpg','jpeg','png','gif');// 设置附件上传类型
					if (!is_dir($dirname) && !mkdir($dirname, 0777, true)) {
						$this->ajaxReturn('上传目录不可写',"error",3);
					}
					$upload->savePath = $dirname;
					
					if(!$upload->upload()) {// 上传错误提示错误信息
						$this->ajaxReturn('',$upload->getErrorMsg(),-10);
					}else{// 上传成功 获取上传文件信息
						$info = $upload->getUploadFileInfo();
						//写入数据库
						foreach($info as $iv){
							$img_data['sign_id'] = $sign_id;
							$img_data['name'] = $iv['name'];
							$img_data['save_name'] = $iv['savename'];
							//$img_data['size'] = sprintf("%.2f", $iv['size']/1024);
							$img_data['path'] = $iv['savepath'].$iv['savename'];
							$img_data['create_time'] = time();
							M('SignImg')->add($img_data);
						}
						actionLog($sign_id);
						$this->ajaxReturn('',"success",1);
					}
				}else{
					actionLog($sign_id);
					$this->ajaxReturn('',"success",1);
				}
			}else{
				$this->ajaxReturn('',"error",2);
			}
		}else{
			$this->ajaxReturn('非法请求',"error",3);
		}
	}
	
	public function customer_list(){
		if($this->isPost()){
			$role_id = session('role_id');
			$where['owner_role_id'] = $role_id;
			$where['is_deleted'] = 0;
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			$list = M('Customer')->where($where)->order("customer_id desc")->page($p.',10')->field('customer_id,name')->select();
			$count = M('Customer')->where($where)->count();
			$page = ceil($count/10);
			$list = empty($list) ? array() : $list;
			$data['list'] = $list;
			$data['page'] = $page;
			$data['info'] = 'success'; 
			$data['status'] = 1;
			$this->ajaxReturn($data,'JSON');
		}else{
			$this->ajaxReturn('非法请求',"error",3);
		}
	}
	
}