<?php
class ProductMobile extends Action {

	public function _initialize(){
		$action = array(
			'permission'=>array(),
			'allow'=>array('radiolistdialog','getrole','checkrole','receive','allot','ajax','info')
		);
		B('AppAuthenticate', $action);
		$this->_permissionRes = getPerByAction(MODULE_NAME,ACTION_NAME);
		Global $roles;
		$this->roles = $roles;
	}
	
	//产品列表
	public function index(){
		if($this->isPost()){
			getDateTime('product');			
			$d_v_product = D('ProductView');
			$category = M('ProductCategory');
			if(isset($_POST['search'])){
				$where['name'] = array('like','%'.trim($_POST['search']).'%');
			}
			$p = isset($_POST['p']) ? intval($_POST['p']) : 1 ;
			if($_GET['category_id']){
				$idArray = Array();
				$categoryList = getSubCategory($_GET['category_id'],$category->select(),'');
				foreach ($categoryList as $value) {
					$idArray[] = $value['category_id'];
				}
				$idList  =empty($idArray) ? $_GET['category_id'] : $_GET['category_id'] . ',' . implode(',', $idArray);
				$where['category_id'] = array('in',$idList);
			}
			$list = $d_v_product->where($where)->order('create_time desc')->page($p.',10')->field('name,product_id,standard')->select();
			foreach($list as $k=>$v){
				$m_product_images = M('product_images');
				$m_product = M('product');
				$product_images_info = $m_product_images->where(array('product_id'=>$v['product_id'],'is_main'=>1))->find();
				if($product_images_info){
					$list[$k]['main_path'] = $product_images_info['path'];
				}else{
					$list[$k]['main_path'] = '';
				}
				$product_info = $m_product->where('product_id = %d',$v['product_id'])->find();
				$list[$k]['stock_count'] = $product_info['stock_count'];
				$list[$k]['suggested_price'] = $product_info['suggested_price'];
				$list[$k]['create_time'] = $product_info['create_time'];
				//获取操作权限
				$list[$k]['permission'] = getpermission(MODULE_NAME);
			}
			$list = empty($list) ? array() : $list;
			$count = $d_v_product->where($where)->count();
			$page = ceil($count/10);
			$category_list = $category->where('parent_id = 0')->field('name,category_id')->select();
			$category_list = empty($category_list) ? array() : $category_list ;			
			$data['category_list'] = $category_list;
			$data['list'] = $list;
			$data['page'] = $page;
			$data['info'] = 'success'; 
			$data['status'] = 1; 			
			$this->ajaxReturn($data,'JSON');
		}else{
			$this->ajaxReturn('非法请求',"error",3);
		}
	}
	//产品详情
	public function view(){
		if($this->isPost()){
			if($this->roles == 2){
				$this->ajaxReturn('您没有此权利!','error',-2);
			}
			$product_id =  isset($_POST['id']) ? intval($_POST['id']) : 0;
			$product = D('ProductView')->where('product.product_id = %d', $product_id)->find();
			//取得字段列表
			$field_list = M('Fields')->where('model = "product"')->order('order_id')->select();
			//查询固定信息
			$product['create'] = D('RoleView')->where('role.role_id = %d', $product['creator_role_id'])->find();
			foreach($field_list as $k=>$v){
				if($v['form_type'] == 'datetime'){
					$times = trim($product[$v['field']]);
					if($times){
						$data_a = date('Y-m-d H:i:s',$times);
					}else{
						$data_a = '';
					}
				}else{
					if($v['field'] == 'category_id'){
						$category_id = trim($product[$v['field']]);
						$data_a = M('ProductCategory')->where('category_id = %d',$category_id)->getField('name');
					}else{
						if($product[$v['field']]){
							$data_a = trim($product[$v['field']]);
						}else{
							$data_a = '';
						}
					}
				}
				$name = trim($v['name']);
				$data_list[$k][$name] = $data_a;
			}
			//获取权限
			$data['permission'] = permissionlist(MODULE_NAME);
			$data['data'] = $data_list;
			$data['info'] = 'success';
			$data['status'] = 1;
			//获取产品主图
			$path = M('ProductImages')->where(array('product_id'=>$product_id,'is_main'=>1))->getField('path');
			if($path){
				$data['main_path'] = $path;
			}else{
				$data['main_path'] = '';
			}
			$this->ajaxReturn($data,'JSON');
		}else{
			$this->ajaxReturn('非法请求',"error",3);
		}
	}
}