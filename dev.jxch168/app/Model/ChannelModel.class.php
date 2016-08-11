<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ChannelModel extends BaseModel {
    public function activity($m,$s){
        // 判断是否领取过该红包
        $sql = "select count(*) from ".DB_PREFIX."tv_channel where mobile = {$m}";
        $res = $GLOBALS['db']->getOne($sql);
        $info = array();
        if($res>0){
            $code =  1; // 已经参加过该活动
        }else{
            $data['mobile'] = $m;
            $data['channel'] = $s;
            $data['stime'] = TIME_UTC;
            $mode = "INSERT";
            $condition = "";
            $GLOBALS['db']->autoExecute(DB_PREFIX . "tv_channel", $data, $mode, $condition);
            $id = $GLOBALS['db']->insert_id();
            $code =  2; // 参加活动成功
        }
        return $code;
    }
    public function getMessege($code){
        if($code == 1){
            $msg['msg'] = "您已经参加过活动";
        }
        if($code == 2){
            $msg['msg'] = "参加活动成功";
        }
        if($code == -1){
            $msg['msg'] = "手机号填写不正确";
        }
        $msg['code'] = $code;
        return $msg;
    }

    public function updateChannel($mobile,$user_id){
        $sql = "select channel from ".DB_PREFIX."tv_channel where mobile={$mobile}"; // 查询该号码是否参加过活动
        $res = $GLOBALS['db']->getOne($sql);
        if(!empty($res)){
            // 如果该手机参加过活动,则更新
            $channel = $res;
            $GLOBALS['db']->query("update ".DB_PREFIX."user set `search_channel`='{$channel}' where id={$user_id}");
        }
    }
}


