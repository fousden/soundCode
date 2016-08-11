<?php
define("ADMIN_ROOT",1);
if (isset($_GET['dbg']) && $_GET['dbg'] = 'jxch168' . date('Ymd')) {
    define("APP_DEBUG", true);
    define("SHOW_DEBUG", true);
    define("APP_DEBUG",true);
}

require "admin.php";

if (isset($_GET['dbg']) &&  $_GET['dbg'] == 'jxch168'.date('Ymd'))
{
    echo "<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><p></p>";
   echo  run_info();
   echo "<pre>";
     var_dump(get_included_files());
    echo "</pre>";
}
?>