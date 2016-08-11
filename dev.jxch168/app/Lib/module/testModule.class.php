<?php

class testModule extends SiteBaseModule {

    public function index() {
        echo "<a href='jxch://user/login?a=1&b=c'>jxch://user/login?a=1&b=c登录</a>";
        echo "<Br>";
        echo "<Br>";
        echo "<Br>";
        echo "<Br>";
         echo "<a href='jxch://deal/info?id=724'>jxch://deal/info?id=724标的</a>";
    }
}
