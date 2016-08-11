<?php

class TestAction extends Action {

    public function test() {
        set_time_limit(0);
        for ($i=0;$i<=10000;$i++){
            file_get_contents("http://dch.crmdev.jxch168.com/index.php?g=mapi&m=user&a=index&r_type=1&sid=0rksrmsclh7na8m3eeahkmi9c4");
        }
    }
    
    public function user_qrcode_card11() {
        $contacts_id = 3;
        if ($contacts = M('Contacts')->where('contacts_id = %d', $contacts_id)->find()) {
            $customer_id = M('RContactsCustomer')->where('contacts_id = %d', $contacts_id)->getField('customer_id');
            $contacts['customer'] = M('Customer')->where('customer_id = %d', $customer_id)->getField('name');
            $qrOpt = '';
            $qrOpt = "BEGIN:VCARD\nVERSION:3.0\n";
            $qrOpt .= $contacts['name'] ? ("FN:" . $contacts['name'] . "\n") : "";
            $qrOpt .= $contacts['telephone'] ? ("TEL:" . $contacts['telephone'] . "\n") : "";
            $qrOpt .= $contacts['email'] ? ("EMAIL;PREF;INTERNET:" . $contacts['email'] . "\n") : "";
            $qrOpt .= $contacts['customer'] ? ("ORG:" . $contacts['customer'] . "\n") : "";
            $qrOpt .= $contacts['post'] ? ("TITLE:" . $contacts['post'] . "\n") : "";
            $qrOpt .= $contacts['address'] ? ("ADR;WORK;POSTAL:" . $contacts['address'] . "\n") : "";
            $qrOpt .= "END:VCARD";
            $qrOpt = 'http://dch.crmdev.jxch168.com/index.php?g=mapi&m=user&a=user_qrcode_card_html';
            $png_temp_dir = UPLOAD_PATH . '/qrpng/';
            $filename = $png_temp_dir . $contacts['contacts_id'] . '.png';
            if (!is_dir($png_temp_dir) && !mkdir($png_temp_dir, 0777, true)) {
                $this->error('二维码保存目录不可写');
            }
//            $qrOpt = "BEGIN:VCARD\nVERSION:3.0\n END:VCARD";
            import("@.ORG.QRCode.qrlib");
            QRcode::png($qrOpt, $filename, 'M', 4, 2);
            header('Content-type: image/png');
            header("Content-Disposition: attachment; filename=" . $contacts['contacts_id'] . '.png');
            echo file_get_contents($filename);
            unlink($filename);
        }
    }

}
