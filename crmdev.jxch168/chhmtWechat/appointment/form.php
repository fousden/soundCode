<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>产品详情</title>
    <script type="text/javascript" src="scripts/jquery-2.1.0.js"></script>
    <script type="text/javascript" src="scripts/parms.js"></script>
    <script type="text/javascript" src="scripts/Comm.js" ></script>
    <!-- 时间选择插件begin -->
    <script type="text/javascript" src="scripts/date.js"></script>
    <script type="text/javascript" src="scripts/iscroll.js"></script>
    <link rel="stylesheet" type="text/css" href="styles/common.css">
    <!-- 时间选择插件end -->
    <link rel="stylesheet" href="styles/style.css"/>
    <script type="text/javascript">
        if(localStorage.ReserveData)
        {
            location.href = "tickling.php?state=-1"
        }
    </script>
</head>

<body>
<div class="formcontainer">
    <div class="name">
        <span class="title">姓名</span>
        <input type="text" name="yuyue" placeholder="请输入姓名"/>
        <span class="tips">姓名不能为空</span>
        <div class="Clear"></div>
    </div>
    <div class="mobile">
        <span class="title">手机号码</span>
        <input type="text" name="yuyue" placeholder="请输入手机号码" maxlength="11"/>
        <span class="tips">请检查手机号码</span>
        <div class="Clear"></div>
    </div>
    <div class="demo date">
        <span class="title">预约时间</span>
        <div class="lie">
            <input id="endTime" class="kbtn" placeholder="请选择时间" type="text"/>
        </div>
        <div class="Clear"></div>
        <span class="tips">请选择预约时间</span>
    </div>
    <div id="datePlugin"></div>
    <div class="place">
        <span class="title">预约门店</span>
        <input type="text" name="yuyue" placeholder="请选择门店"/>
        <div class="Clear"></div>
        <span class="tips">请选择预门店</span>
    </div>
    <div class="place_menu">
        <div class="container">
		
        </div>
    </div>
    <a href="javascript:;" class="submitbtn">提交信息</a>
</div>
<script type="text/javascript" charset="UTF-8">
    $(document).ready(function() {
        var formcontainer = $(".formcontainer");

        function checkempty(e) {
            var value = e.find("input[type=text]").val();
            if (value) {
                e.find(".tips").removeClass("tips_error");
                return true;
            } else {
                e.find(".tips").addClass("tips_error");
                return false;
            }
        }

        function checkmobile(e) {
            var mobile = e.find("input[type=text]").val();
            //第一位必须为1 后面10位纯数字 总共11位
            var reg = /^1\d{10}$/;
            if (reg.test(mobile)) {
                e.find(".tips").removeClass("tips_error");
                return true;
            } else {
                e.find(".tips").addClass("tips_error");
                return false;
            }
        }

// 点击提交按钮
        formcontainer.find(".submitbtn").click(function() {
            checkempty($(".name"));
            checkempty($(".date"));
            checkempty($(".place"));
            checkmobile($(".mobile"));
            if (checkempty($(".name")) && checkempty($(".date")) && checkempty($(".place")) && checkmobile($(".mobile"))) {
                //alert('success');
                var pidx = getQueryString("pidx") ? getQueryString("pidx") : 1;
                $.getJSON('../../index.php?m=Wxapi&a=reserve',
                {
                    "mobile": $(".mobile").find("input[type=text]").val(),
                    "name": $(".name").find("input[type=text]").val(),
                    "reserve_time": $(".date").find("input[type=text]").val(),
                    "reserve_product": getProdByidx(pidx).name,
                    "reserve_shop": $(".place").find("input[type=text]").val(),
                }, function (json) {
                    var userdata = {
                        "name": json.data.name,
                        "mobile": json.data.mobile,
                        "reserve_product": json.data.reserve_product,
                        "reserve_shop": json.data.reserve_shop,
                        "reserve_time": json.data.reserve_time
                    };

                    localStorage.setItem("ReserveData", JSON.stringify(userdata));
                    location.href = "tickling.php?state=" + json.state;
                });
            }
        }); // $('#beginTime').date();
        $('#endTime').date({ theme: "datetime" });
       
        //载入门店地址
        $.each(palcearr, function(i, v) {
            $(".place_menu").find(".container").append("<span class='option'>" + v + "</span>");
        }); // 初始化门店弹出框位置
        // setmenucenter()
        function setmenucenter() {
            var menu = $(".place_menu");
            var winH = $(window).height();
            var targetH = menu.find(".container").outerHeight();
            if (targetH > winH * 0.8) {
                menu.find(".container").addClass("container_scroll");
                targetH = menu.find(".container").outerHeight();
            }
            menu.find(".container").css({ "margin-top": (winH - targetH) / 2 });
        }

//弹出门店位置
        $(".formcontainer").find(".place input[type=text]").click(function() {
            $('.place_menu').fadeIn(200);
            setmenucenter();
        }); // 关闭门店弹出位置
        $(".place_menu").click(function(e) {
            if (e.target.className == "place_menu") {
                $(this).fadeOut(200);
            }
        }); // 选择门店
        $(".place_menu").find(".option").click(function() {
            $(".place_menu").fadeOut(200);
            // alert($(this).html())
            $(".formcontainer").find(".place input[type=text]").val($(this).html());
        });
    })
</script>
</body>

</html>