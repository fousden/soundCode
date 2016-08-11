<?php
class deal_load{
    public function index(){
        $id = $_REQUEST['id']; // 传过来的id
        $sql = "select count(*) from ".DB_PREFIX."deal_load where deal_id={$id}";
        $count = $GLOBALS['db']->getOne($sql); // 一共几条数据
        $page = intval($_REQUEST['p']); // 传过来的页数
        $page_size = 15; // 后台每页几条数据
        $page_count = ceil($count/$page_size); //几页
        if($page==0)
            $page = 1; // 第几页
        $limit = (($page-1)*$page_size).",".$page_size;
        $sql = "select dl.money,dl.create_time,u.mobile from ".DB_PREFIX."deal_load as dl left join ".DB_PREFIX."user as u on dl.user_id=u.id where dl.deal_id={$id} order by dl.create_time desc limit ".$limit;
        $deal_info = $GLOBALS['db']->getAll($sql);
        foreach($deal_info as $key=>$val){
            $deal_info[$key]['create_time'] = date("Y-m-d H:i:s",$val['create_time']);
            $deal_info[$key]['mobile'] = hideMobile($val['mobile']);
        }
        if(!$deal_info){
            $deal_info="";
        }
        $root['p'] = (string)$page;// 当前第几页
        $root['page_count'] = (string)$page_count; // 一共几页
        $root['deal_info'] = $deal_info;
        output($root);
    }
}