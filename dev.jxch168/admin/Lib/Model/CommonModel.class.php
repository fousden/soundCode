<?php
// +----------------------------------------------------------------------
// | jxch168 金享财行
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.jxch168.com/ All rights reserved.
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------

class CommonModel extends Model {
     /**
     +----------------------------------------------------------
     * 把返回的数据集转换成Tree
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $list 要转换的数据集
     * @param string $pid parent标记字段
     * @param string $level level标记字段
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function toTree($list=null, $pk='id',$pid = 'pid',$child = '_child')
    {
        if(null === $list) {
            // 默认直接取查询返回的结果集合
            $list   =   &$this->dataList;
        }
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();

            foreach ($list as $key => $data) {
                $_key = is_object($data)?$data->$pk:$data[$pk];
                $refer[$_key] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = is_object($data)?$data->$pid:$data[$pid];
                $is_exist_pid = false;
                foreach($refer as $k=>$v)
                {
                	if($parentId==$k)
                	{
                		$is_exist_pid = true;
                		break;
                	}
                }
                if ($is_exist_pid) {
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                } else {
                    $tree[] =& $list[$key];
                }
            }
        }
        return $tree;
    }


	/**
	 * 将格式数组转换为树
	 *
	 * @param array $list
	 * @param integer $level 进行递归时传递用的参数
	 */
	private $formatTree; //用于树型数组完成递归格式的全局变量
	private function _toFormatTree($list,$level=0,$title = 'title')
	{
			  foreach($list as $key=>$val)
			  {
				$tmp_str=str_repeat("&nbsp;&nbsp;",$level*2);
				$tmp_str.="|--";

				$val['level'] = $level;
				$val['title_show'] = $tmp_str.$val[$title];
				if(!array_key_exists('_child',$val))
				{
				   array_push($this->formatTree,$val);
				}
				else
				{
				   $tmp_ary = $val['_child'];
				   unset($val['_child']);
				   array_push($this->formatTree,$val);
				   $this->_toFormatTree($tmp_ary,$level+1,$title); //进行下一层递归
				}
			  }
			  return;
	}

	public function toFormatTree($list,$title = 'title')
	{
		$list = $this->toTree($list);
		$this->formatTree = array();
		$this->_toFormatTree($list,0,$title);
		return $this->formatTree;
	}


	//无限递归获取子数据ID集合
	private $childIds;
	private function _getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$childItem_arr = $this->field('id')->where($pid_str."=".$pid)->findAll();
		if($childItem_arr)
		{
			foreach($childItem_arr as $childItem)
			{
				$this->childIds[] = $childItem[$pk_str];
				$this->_getChildIds($childItem[$pk_str],$pk_str,$pid_str);
			}
		}
	}
	public function getChildIds($pid = '0', $pk_str='id' , $pid_str ='pid')
	{
		$this->childIds = array();
		$this->_getChildIds($pid,$pk_str,$pid_str);
		return $this->childIds;
	}

        //返回一个limit分页
        public function getLimit($page = 0,$page_size = 1){
            if ($page == 0){
                $page = 1;
            }
            $limit = (($page - 1) * $page_size) . "," . $page_size;
            return $limit;
        }

        //获取资金池信息
        public function getAccount($account = FUYOU_MCHNT_FR){
            //检测富友资金池数据
            require_once APP_ROOT_PATH . "system/payment/Fuyou_payment.php";
            $fuyou_payment   = new Fuyou_payment();
            $user_info['id'] = $account;
            $user_info['fuiou_account'] = $account;//PAY_LOG_NAME FUYOU_MCHNT_FR
            //富友余额查询 数据查询
            $cash_data      = $fuyou_payment->check_balance($user_info);
            return $cash_data;
        }

        //获取当前管理员信息
        public function getAdminInfo(){
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_id = intval($adm_session['adm_id']);
            $admin_info = M('admin')->find($adm_id);
            //返回管理员信息
            return $admin_info;
        }

        //验证管理员操作密码是否正确
        public function checkAdminPwd($pwd,$type){
            //后台操作管理员信息
            $adm_session = es_session::get(md5(conf("AUTH_KEY")));
            $adm_id = intval($adm_session['adm_id']);
            $admin_info = M('admin')->find($adm_id);
            //管理员密码验证
            if($pwd == $admin_info[$type]){
                return true;
            }else{
                return false;
            }
        }
}
?>