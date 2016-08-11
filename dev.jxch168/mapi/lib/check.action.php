<?php

class check {

    public function check_field() {
        $field_name = isset($_REQUEST['field_name'])?addslashes(trim($_REQUEST['field_name'])):'';
        $field_data = isset($_REQUEST['field_data'])?addslashes(trim($_REQUEST['field_data'])):'';
        require_once APP_ROOT_PATH . "system/libs/user.php";
        $res = check_user($field_name, $field_data);
        $result = array("status" => 1, "info" => '');
        if ($res['status']) {
            es_session::set('check_' . $field_name, time());
            ajax_return($result);
        } else {
            $error = $res['data'];
            if (!$error['field_show_name']) {
                $error['field_show_name'] = $GLOBALS['lang']['USER_TITLE_' . strtoupper($error['field_name'])];
            }
            if ($error['error'] == EMPTY_ERROR) {
                $error_msg = sprintf($GLOBALS['lang']['EMPTY_ERROR_TIP'], $error['field_show_name']);
            }
            if ($error['error'] == FORMAT_ERROR) {
                $error_msg = sprintf($GLOBALS['lang']['FORMAT_ERROR_TIP'], $error['field_show_name']);
            }
            if ($error['error'] == EXIST_ERROR) {
                $error_msg = sprintf($GLOBALS['lang']['EXIST_ERROR_TIP'], $error['field_show_name']);
            }
            $result['status'] = 0;
            $result['info'] = $error_msg;
            ajax_return($result);
        }
    }

}
