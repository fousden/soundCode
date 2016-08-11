<?php
/**
 *
 * @api {get} ?act=uc_address&r_type=1&email=dch&pwd=123456 地址展示页
 * @apiName 地址展示页
 * @apiGroup jxch
 * @apiVersion 1.0.0
 * @apiDescription 请求url
 *
 * @apiParam {string} act 动作{uc_address}
 * @apiParam {string} func 动作{index（可不传）}
 * @apiParam {string} email 用户名
 * @apiParam {string} pwd 密码
 *
 * @apiSuccess {string} response_code 结果码
 * @apiSuccess {string} show_err 消息说明
 * @apiSuccess {string} id 地址id
 * @apiSuccess {string} address 地址
 * @apiSuccess {string} real_name 收件人
 * @apiSuccess {string} mobile 手机号
 * @apiSuccess {string} code 邮政编码
 *
 * @apiSuccessExample 返回示范:
{
{
"address_info": {
"id": "86",
"user_id": "861",
"province": "",
"city": "",
"address": "ceshi",
"is_default": "0",
"real_name": "xuxuxu",
"mobile": "18712344321"
},
"act": "uc_address",
"func": "index"
}
 */
//require APP_ROOT_PATH.'app/Lib/uc.php';
class uc_address
{
	public $email;
	public $pwd;
	public $user_id;

	public function __construct(){
		$this->email = strim($GLOBALS['request']['email']);
		$this->pwd = strim($GLOBALS['request']['pwd']);
		$user = user_check($this->email,$this->pwd);
		$this->user_id = intval($user['id']);
		if($this->user_id<=0){
			$root['response_code'] = '0';
			$root['show_err'] ="未登录";
			$root['user_login_status'] = '0';
			output($root);
		}

	}

	public function index()
	{
		$root = array();
		$user_id  = $this->user_id;
		if ($user_id >0){
			$sql = "select * from ".DB_PREFIX."user_address where user_id = ".$user_id;
			$address_info = $GLOBALS['db']->getRow($sql);
			$root['address_info'] = $address_info;
		}
		output($root);		
	}
	/*
	* @api {get} ?act=uc_address&func=add_address&r_type=1&email=dch&&pwd=123456&real_name=徐超敏&mobile=18651935745&code=123456&address=古北亚繁&detaied_address=测试详细地址
		* @apiName 新增地址
	* @apiGroup jxch
	* @apiVersion 1.0.0
	* @apiDescription 请求url
	*
	 * @apiParam {string} act 动作{uc_address}
	 * @apiParam {string} func 动作{add_address（必传）}
	 * @apiParam {string} email 用户名
	* @apiParam {string} pwd 密码
	* @apiParam {string} real_name 收件人
	* @apiParam {string} mobile 手机号
	* @apiParam {string} address 地址
	* @apiParam {string} detailed_address 地址
	* @apiParam {string} code 邮政编码
	*
	 * @apiSuccess {string} response_code 结果码
	* @apiSuccess {string} show_err 消息说明
	*
	 * @apiSuccessExample 返回示范:
	{
		"response_code": 1,
	"show_err": "新增地址成功",
	"act": "uc_address",
	"func": "add_address"
	}
	*/
	public function add_address()
	{
		$root=array();
		$user_id = $this->user_id;
		if($user_id>0){
			$real_name = isset($GLOBALS['request']['real_name']) ? trim($GLOBALS['request']['real_name']) : '';
			$mobile = isset($GLOBALS['request']['mobile']) ? trim($GLOBALS['request']['mobile']) : '';
			$code = isset($GLOBALS['request']['code']) ? trim($GLOBALS['request']['code']) : '';
			$address = isset($GLOBALS['request']['address']) ? trim($GLOBALS['request']['address']) : '';
			$detailed_address = isset($GLOBALS['request']['detailed_address']) ? trim($GLOBALS['request']['detailed_address']) : "";

			// 判断mobile是否为11位数字
			if (!check_mobile($mobile)) {
				output("请填写正确的手机号");
			};

			if (empty($address)) {
				output("收件地址不能为空");
			}
			if (empty($real_name)) {
				output("收件人姓名不能为空");
			}
			// 新增地址前先判断该用户是否新增过
			$sql = "select * from ".DB_PREFIX."user_address where user_id=".$user_id;
			$info = $GLOBALS['db']->getRow($sql);
			if($info){
				output("请不要重复操作");
			}
			$user_address['user_id'] = $user_id;
			$user_address['address'] = $address."+".$detailed_address;
			$user_address['real_name'] = $real_name;
			$user_address['mobile'] = $mobile;
			$user_address['code'] = $code;
			$res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_address", $user_address, 'INSERT', '', 'SILENT');
			if($res){
				$root['response_code'] = '1';
				$root['show_err'] = "新增地址成功";
			}else{
				output("新增地址失败");
			}
		}
		output($root);
	}

	/*
	 *@api {get} ?act=uc_address&func=update_address&r_type=1&email=dch&&pwd=123456&real_name=徐超敏&mobile=18651935745&code=123456&address=古北亚繁&detailed_address=测试详细地址&address_id=86
	 * @apiName 修改地址
	 * @apiGroup jxch
	 * @apiVersion 1.0.0
	 * @apiDescription 请求url
	 *
	 * @apiParam {string} act 动作{uc_address}
	 * @apiParam {string} func 动作{update_address（必传）}
	 * @apiParam {string} email 用户名
	 * @apiParam {string} pwd 密码
	 * @apiParam {string} address_id 地址id
	 * @apiParam {string} real_name 收件人
	 * @apiParam {string} mobile 手机号
	 * @apiParam {string} address 地址
	 * @apiParam {string} detailed_address 详细地址
	 * @apiParam {string} code 邮政编码
	 *
	 * @apiSuccess {string} response_code 结果码
	 * @apiSuccess {string} show_err 消息说明
	 *
	 * @apiSuccessExample 返回示范:
	{
	"response_code": 1,
	"show_err": "修改地址成功",
	"act": "uc_address",
	"func": "update_address"
	}
	 *
	 *
	 * */
	public function update_address(){
		$root = array();
		$user_id = $this->user_id;
		if($user_id>0){
			$real_name = isset($GLOBALS['request']['real_name']) ? trim($GLOBALS['request']['real_name']) : '';
			$mobile = isset($GLOBALS['request']['mobile']) ? trim($GLOBALS['request']['mobile']) : '';
			$code = isset($GLOBALS['request']['code']) ? trim($GLOBALS['request']['code']) : '';
			$address = isset($GLOBALS['request']['address']) ? trim($GLOBALS['request']['address']) : '';
			$detailed_address = isset($GLOBALS['request']['detailed_address']) ? trim($GLOBALS['request']['detailed_address']) : "";

			// 判断mobile是否为11位数字
			if (!check_mobile($mobile)) {
				output("请填写正确的手机号");
			};

			if (empty($address)) {
				output("收件地址不能为空");
			}
			if (empty($real_name)) {
				output("收件人姓名不能为空");
			}
			// 查出对应的address_id
			$address_id = isset($_REQUEST['address_id']) ? trim($_REQUEST['address_id']) : '' ;
			$user_address['user_id'] = $user_id;
			$user_address['address'] = $address."+".$detailed_address;
			$user_address['real_name'] = $real_name;
			$user_address['mobile'] = $mobile;
			$user_address['code'] = $code;
			$res = $GLOBALS['db']->autoExecute(DB_PREFIX . "user_address", $user_address, 'UPDATE', 'id='.$address_id, 'SILENT');
			if($res){
				$root['response_code'] = '1';
				$root['show_err'] = "修改地址成功";
			}else{
				output("修改地址失败");
			}
		}
		output($root);
	}

	/*
	 * @api {get} ?act=uc_address&func=delete_address&r_type=1&email=dch&pwd=123456&address_id=96
	 * @apiName 删除地址
	 * @apiGroup jxch
	 * @apiVersion 1.0.0
	 * @apiDescription 请求url
	 *
	 * @apiParam {string} act 动作{uc_address}
	 * @apiParam {string} func 动作{delete_address（必传）}
	 * @apiParam {string} address_id 地址对应的id{在地址展示的时候后台传过去（必传）}
	 * */
	public function delete_address(){
		$root = array();
		$user_id = $this->user_id;
		if($user_id>0){
			// 查出address_id
			$address_id = isset($_REQUEST['address_id']) ? trim($_REQUEST['address_id']) : '' ;
			$sql = "delete from ".DB_PREFIX."user_address where id=".$address_id;
			$GLOBALS['db']->query($sql);
			if($GLOBALS['db']->affected_rows()){
				$root['response_code'] = '1';
				$root['show_err'] = "删除成功";
			}else{
				output("删除失败");
			}
		}
		output($root);
	}

	public function test(){

	}
}
?>
