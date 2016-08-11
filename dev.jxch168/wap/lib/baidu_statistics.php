<?php
require("hm.php");

class baidu_statistics{

    function htmlPixel(){
        $_hmt = new _HMT("8275aea4d6c86ea64ee5e0488ac8b330");
        return $_hmt->trackPageView();
    }
}
?>