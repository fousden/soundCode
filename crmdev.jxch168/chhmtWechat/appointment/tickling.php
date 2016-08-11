<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>预约结果</title>
    <script type="text/javascript" src="scripts/jquery-2.1.0.js"></script>
    <script type="text/javascript" src="scripts/parms.js"></script>
    <script type="text/javascript" src="scripts/Comm.js"></script>
    <link rel="stylesheet" href="styles/style.css"/>
</head>

<body>
    <div class="tickling">
        <p class="red">您的预约信息正在受理中......</p>
        <p class="sm">用户姓名：<label class="name"></label></p>
        <p class="sm">联系方式：<label class="mobile"></label></p>
        <p class="sm">您的预约时间是：<label class="time"></label></p>
        <p class="sm">预约门店地址：<label class="shop"></label></p>
        <p class="tips">请耐心等待，客服确认后将与您联系</p>
    </div>
<script type="text/javascript" charset="UTF-8">
    var state = getQueryString("state");
    if (!state || state.replace(' ', '') == "") {
        window.location.href = "index.php";
    }
    var tickling = $(".tickling");
    var title = tickling.find(".red");
    var name = tickling.find(".name");
    var mobile = tickling.find(".mobile");
    var time = tickling.find(".time");
    var shop = tickling.find(".shop");

    var userdata = JSON.parse(localStorage.ReserveData);
    console.log(userdata.name + " ", userdata.mobile + " ", userdata.reserve_product + " ", userdata.reserve_shop + " ", userdata.reserve_time);
    //设置文字
    if (state > 0) {
        title.html("恭喜,您已经预约成功!");
    } else {
        title.html("您的预约正在受理中......  ");
    }
    tickling.find(".name").html(userdata.name);
    tickling.find(".mobile").html(userdata.mobile);
    tickling.find(".time").html(userdata.reserve_time);
    tickling.find(".shop").html(userdata.reserve_shop);

</script>
</body>

</html>