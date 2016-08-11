<?php
/**
 * 后台共用model业务逻辑类
 *
 * @author jxch
 */
namespace base\model;
use \base\model\base;

class backend extends base{

    public function action(){
        //添加自己的业务逻辑
        // ...
    }

    public function db_config(){
        return array(
            'DB_HOST'=>'10.10.10.56',
            'DB_NAME'=>'jxch168.com',
            'DB_USER'=>'master',
            'DB_PWD'=>'lovejxch168',
            'DB_PORT'=>'3306',
            'DB_PREFIX'=>'jxch_',
        );
    }

    //删除记录
    function publicDelete($table_name,$ids = ""){
        if(!$table_name){
            $data["status"] = 0;
            $data["info"] = "表名不能为空";
            return $data;
        }
        if(!$ids){
            $data["status"] = 0;
            $data["info"] = "请选择要删除的记录id";
            return $data;
        }

        $res = M($table_name)->delete($ids);

        if($res){
            $data["status"] = 1;
            $data["info"] = "记录删除成功";
        }else{
            $data["status"] = 0;
            $data["info"] = "记录删除失败";
        }
        return $data;
    }

    //设置标的属性 只能修改 1 0两个值属性
    function set_deal_attr($table_name){
        $field_name = $_REQUEST["field_name"] ? $_REQUEST["field_name"] : "";
        $id = $_REQUEST["id"];

        if(!$id){
            $data["info"] = "标的ID不能为空";
            $data["status"] = 0;
            return $data;
        }
        if(!$field_name){
            $data["info"] = "数据信息不完整";
            $data["status"] = 0;
            return $data;
        }
        $field_value = M($table_name)->where(array("id"=>$id))->getField($field_name);
        if($field_value == 1){
            $update_value = 0;
        }else{
            $update_value = 1;
        }
        $new_data[$field_name] = $update_value;
        $new_data["id"] = $id;
        $up_id = $this->save($new_data);

        if($up_id){
            $data["info"] = "标的状态修改成功";
            if($field_name == "is_effect"){
                $data["status_desc"] = $update_value ? "有效" : "无效";
            }else{
                $data["status_desc"] = $update_value ? "是" : "否";
            }
            $data["status"] = 1;
        }else{
            $data["info"] = "标的状态修改失败";
            $data["status"] = 0;
        }

        return $data;
    }

    protected function ajaxReturn($data,$info='',$status=1,$type='')
    {
        // 保证AJAX返回后也能保存日志
        //if(C('LOG_RECORD')) Log::save();
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        if(empty($type)) $type  = 'JSON';
        if(strtoupper($type)=='JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        }elseif(strtoupper($type)=='EVAL'){
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            exit($data);
        }else{
            // TODO 增加其它格式
        }
    }

    //返回一个limit分页
    public function getLimit($page = 0,$page_size = 10){
        if ($page == 0){
            $page = 1;
        }
        $limit = (($page - 1) * $page_size) . "," . $page_size;
        return $limit;
    }

    //获取管理员信息
    function getAdminInfo(){
        //管理员信息
        $admin_id = session("admin_info.id");
        $admin_info = M("admin")->find($admin_id);
        return $admin_info;
    }

    //更新表信息
    public function updateModel($table,$data){
        //更新数据信息
        $up_id = M($table)->save($data);
        return $up_id;
    }

    //更新当前管理员密码信息
    function saveAdmin($data){
        $admin_info = session("admin_info");
        //保存密码
        $data['id'] = $admin_info["id"];
        $up_id = M('admin')->save($data);
        return $up_id;
    }

    //验证管理员操作密码是否正确
    public function checkAdminPwd($pwd,$type){
        //后台操作管理员信息
        $admin_info = session("admin_info");
        $admin_id = intval($admin_info["id"]);
        $admin_info = M('admin')->find($admin_id);
        //管理员密码验证
        if($pwd == $admin_info[$type]){
            return true;
        }else{
            return false;
        }
    }

    //验证资金池账户余额
    function checkFuyouBalance($id){
        $data = $this->getAccount();
        //该标的下还款总额
        $repay_money_all = M("deal_load_repay")->where(array("deal_id"=>$deal_info['id']))->getField("sum(all_repay_money_e2)");
        //验证
        if($data['ca_balance'] <= 0 || ($data['ca_balance'] > 0 && $data['ca_balance'] <  $repay_money_all)){
            return false;
        }else{
            return true;
        }
    }

    //获取资金池信息
    public function getAccount($account = "13999999999"){
        //富友转账 还款
        $fuyou = D("base/fuyou");
        //检测富友资金池数据
        $user_info['id'] = $account;
        $user_info['fuiou_account'] = $account;//PAY_LOG_NAME FUYOU_MCHNT_FR
        //富友余额查询 数据查询
        $cash_data = $fuyou->check_balance($user_info);
        return $cash_data;
    }
}