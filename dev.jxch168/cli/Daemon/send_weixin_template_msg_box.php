<?php

require_once 'init.php';
set_time_limit(0);
while (true) {
    $TemplateList=MO('WeixinTemplateMsgBox')->getTemplateList('*'," and is_send=0 ");
    foreach ($TemplateList as $key=>$val){
        $res = WIN('templateMsg')->send_template($val['touser'], $val['template_id'], unserialize($val['data']), $val['url'],false);
        if($res){
            $data['is_send']=1;
            $data['send_time']=time();
            $GLOBALS['db']->autoExecute(DB_PREFIX."weixin_template_msg_box",$data,"UPDATE","id=".$val['id']);
        }
    }
    sleep(1);
}