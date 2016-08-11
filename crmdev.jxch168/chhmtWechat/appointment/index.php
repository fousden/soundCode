<?php
//include_once "comm/WechatHleper.php";

//$wxHelper = new WechartHleper();
//echo $wxHelper->getAccessToken();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>理财预约</title>
    <script type="text/javascript" src="scripts/jquery-2.1.0.js"></script>
    <script type="text/javascript" src="scripts/parms.js"></script>
    <script type="text/javascript" src="scripts/Comm.js" charset="utf-8"></script>
    <link rel="stylesheet" href="styles/style.css" />
</head>

<body>
<header>
    <img src="images/topBanner.png" alt=""/>
</header>
<section id="cntBody">
    <div class="prodBox">
        <div class="blank"></div>
    </div>
    <hr class="split"/>
    <div class="prodShow">
        <ul>
            <li>预期年化收益</li>
            <li class="big rate"></li>
            <li>
                <label>出借期待</label>
                <label class="month"></label>
            </li>
            <li>
                <hr/>
            </li>
            <li class="see">查看详情 >></li>
        </ul>
    </div>
    <div class="optBox">
        <div class="msg">
            <label>预约人数（个）</label>
            <label class="reserveNum"></label>
        </div>
        <div class="btn">
            立&nbsp;即&nbsp;预&nbsp;约
        </div>
    </div>
</section>
<footer>
    上海华陌通金融信息服务有限公司
</footer>
<script type="text/javascript" charset="UTF-8">
    var prodBox = $("#cntBody .prodBox");
    var prodShow = $("#cntBody .prodShow");
    $(document).ready(function() {
        $.each(parms, function(k, v) {
            prodBox.find(".blank").before('<img idx="' + v.idx + '" name="'+v.name+'" class=\"prod\" src="' + v.img + '"></img>');
        });
        //默认第1个选中
        changeProd(prodBox.find(".prod").eq(0));
        $(".prod", prodBox).click(function() {
            $(".prod", prodBox).removeClass("current");
            var now = $(this);
            now.addClass("current");
            changeProd(now);
        });
        $(".see", prodShow).click(function() {
            window.location.href = "detail.php?idx=" + $(this).attr("idx");
        });
        // 跳转预约
        $(".optBox .btn").click(function() {
            var prod = prodBox.find(".prod.current").attr("idx");
            window.location.href = 'form.php?pidx=' + prod;
        });
    });

//切换商品
    function changeProd(img) {
        img.addClass("current");
        var p = getProdByidx(img.attr("idx"));
        $(".rate", prodShow).html(p.rate);
        $(".month", prodShow).html(p.deadline + '个月');
        $(".see", prodShow).attr("idx", p.idx);
        $(".reserveNum").html(p.reserveNum);
    }
</script>
</body>

</html>