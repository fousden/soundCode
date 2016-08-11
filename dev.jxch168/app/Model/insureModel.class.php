<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class insureModel extends BaseModel {
    public function pingan($uid,$name,$sex,$birth,$mobile){
         $channel = CHNNEL_CODE;
         $productCode = PRODUCT_CODE;
         $str = SIGN;
         $sign = md5($channel.$str.$mobile);
         // 同意请求将基本信息写进数据库
         // 判断之前是否已经存在该用户信息
         /*
         $refer_count = $GLOBALS['db']->getOne("SELECT count(*) FROM " . DB_PREFIX . "insure WHERE uid={$uid}");
         if($refer_count>0){
             showErr("您已经执行过该操作");
         }
          *
          */
         $data['uid'] = $uid;
         $data['channel'] = $channel;
         $data['productCode'] = $productCode;
         $data['mobile'] = $mobile;
         $data['name'] = $name;
         $data['birth'] = $birth;
         $data['sex'] = $sex;
         $data['sign'] = $sign;
         $data['create_time'] = TIME_UTC;
         $mode = "INSERT";
         $condition = "";
         $GLOBALS['db']->autoExecute(DB_PREFIX . "insure", $data, $mode, $condition);
         $id = $GLOBALS['db']->insert_id();
         $url="http://www.ilovepingan.com/newtank/thirdparty/interface/insure.do?channel={$channel}&productCode={$productCode}&sign={$sign}&name={$name}&sex={$sex}&birth={$birth}&mobile={$mobile}";
         $opts = array(
            'http'=>array(
                'method'=>'GET',
                'timeout'=>5,
            )
         );
         $context = stream_context_create($opts);
         $html =file_get_contents($url, false, $context);
         if($html==''){
             return false;
         }
         $arr = json_decode($html,true);
         $status = $arr['status'];
         if($status==0){
             $data['policyNo'] = $arr['policyNo'];
         }
         // 投保成功
         $data['message'] = $arr['message'];
         $data['status'] = $arr['status'];
         $mode = "UPDATE";
         $condition = "id={$id}";
         $GLOBALS['db']->autoExecute(DB_PREFIX . "insure", $data, $mode, $condition);
         if($status==0){
             $info = array(
                 "info" => '0',
                 "msg" =>$arr['policyNo'],
             );
         }else{
             $info = array(
                 'info' => '1',
                 'msg' => $arr['message'],
             );
         }
         return $info;
    }
}

