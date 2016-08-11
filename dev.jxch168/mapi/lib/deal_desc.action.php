<?php
class deal_desc {

    public function index() {
        $id = strim($GLOBALS['request']['id']); //标的ID
        if ($id > 0) {
            $root["id"] = $id;
            $sql = "select d.id,d.description,d.risk_security,d.agency_id, 
					(select brief from " . DB_PREFIX . "user where id = d.agency_id and is_effect = 1) as agency_info 
				from " . DB_PREFIX . "deal d where d.id = " . $id;
            $deal = $GLOBALS['db']->getRow($sql);
            $deal=array_map("replace_path", $deal);
            
            $sql_deal_gallery = "SELECT icon_id,icon_url FROM " . DB_PREFIX . "deal_gallery where deal_id =" . $id;
            $deal_gallery = $GLOBALS['db']->getAll($sql_deal_gallery);

            $deal_gallery_list = array();
            
            foreach ($deal_gallery as $k => $v) {
                if ($v['icon_url'] != '') {
                    $url="./".substr($v['icon_url'],strpos($v['icon_url'], "public"));
                    $v['icon_url'] = get_abs_img_root(get_spec_image($url, 180, 150, 1));
                    $v['icon_url_s'] = get_abs_img_root($url);
                    array_push($deal_gallery_list, $v);
                }
            }

            $root['deal_gallery'] = $deal_gallery_list;
            $root['deal'] = $deal;
            $root['response_code'] = 1;
        } else {
            $root['response_code'] = 0;
        }

        output($root);
    }

}

?>