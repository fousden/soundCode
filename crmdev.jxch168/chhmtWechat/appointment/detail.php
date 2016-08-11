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
    <script type="text/javascript" src="scripts/Comm.js"></script>
    <link rel="stylesheet" href="styles/style.css"/>
</head>

<body>
<section id="cntBody">
    <div class="prodBox">
        <div class="blank"></div>
    </div>
    <hr class="split"/>
    <div class="detailShow">
        <div class="tit">
            <label>预期年华收益率</label>
            <label>累计预约人数11,256人</label>
        </div>
        <div class="rate">
        </div>
        <div class="infoBox">
            <div class="deadline ">
                <label class="tit">出借期限</label>
                <label class="msg">12个月</label>
            </div>
            <div class="rateStr">
                <label class="tit">预期年化收益</label>
                <label class="msg">12%</label>
            </div>
            <div class="payMethod">
                <label class="tit">还款方式</label>
                <label class="msg">分4期,每季返还本息</label>
            </div>
        </div>
        <div class="clear"></div>

    </div>
</section>

<footer class="optBox">
    <img id="calculator" src="images/calculator.png" alt=""/>
    <div class="btn">立&nbsp;即&nbsp;预&nbsp;约</div>
    <div class="clear"></div>
</footer>

<section class="shadowbox">
    <div class="box">
        <p class="tit">收益计算器</p>
        <div class="money">
            <label>输入金额</label>
            <input type="text" placeholder="请输入投资金额"/>
            <label>元</label>
        </div>

        <p class="result">-</p>
        <p class="str">预计收益(利息收益/元)</p>
        <div class="btngroup">
            <a href="javascript:;" class="countbtn">计算收益</a>
            <a href="javascript:;" class="closebtn">关闭计算器</a>
            <div class="Clear"></div>
        </div>

    </div>
</section>
<script type="text/javascript" charset="UTF-8">
    var prodBox = $("#cntBody .prodBox");
    var detailShow = $("#cntBody .detailShow");
    var idx = getQueryString('idx');
    idx = idx ? idx : 1;
    $(document).ready(function() {
        $.each(parms, function(k, v) {
            prodBox.find(".blank").before('<img idx="' + v.idx + '" name="' + v.name + '" class=\"prod\" src="' + v.img + '"></img>');
        });
        //默认第1个选中
        changeProd(prodBox.find(".prod[idx=" + idx + "]"));
        $(".prod", prodBox).click(function() {
            $(".prod", prodBox).removeClass("current");
            var now = $(this);
            now.addClass("current");
            changeProd(now);
        });
        // 计算收益
        $(".shadowbox").find(".countbtn").click(function() {
            var investnum = $(".shadowbox").find(".money input[type=text]").val();
            var deadline = parseFloat(detailShow.find(".deadline .msg").html());
            var rateStr = parseFloat(detailShow.find(".rateStr .msg").html());
            var reg = /^\d+$/;
            var result;
            if (reg.test(investnum)) {
                result = (investnum * rateStr / 100 / 360 * deadline * 30).toFixed(2);
                $(".result").html(result);
            } else {
                $(".result").html('-');
            }

        }); // 点击弹出计算器
        $("#calculator").click(function() {
            $(".shadowbox").slideDown(1);
            // $(".money").find("input[type=text]").focus();
        }); // 隐藏计算器
        $(".shadowbox").click(function(e) {
            if (e.target.className === "shadowbox") {
                $(this).slideUp(1);
            }
        });
        $(".shadowbox").find(".closebtn").click(function() {
            $(".shadowbox").slideUp(1);
        });
        // 跳转预约
        $(".optBox").find(".btn").click(function() {
            var prod = prodBox.find(".prod.current").attr("idx");
            window.location.href = 'form.php?pidx=' + prod;
        });
    });

//切换商品
    function changeProd(img) {
        img.addClass("current");
        var p = getProdByidx(img.attr("idx"));
        createRateEffects($(".rate"), p.rate);
        $(".rateStr >.msg", detailShow).html(p.rate);
        $(".deadline >.msg", detailShow).html(p.deadline + '个月');
        $(".payMethod >.msg", detailShow).html(p.payMethod);
    }

//创建卡牌特效  
    function createRateEffects(ele, rate) {
        ele.children().remove();
        for (i = 0; i < rate.length; i++) {
            ele.append("<label>" + rate.charAt(i) + "</label>");
            var divW = ($(".rate").width()) * 0.75 / rate.length;
            // alert(divW)
            ele.find("label").css({ "width": divW });
            ele.find("label").each(function(i, v) {
                if (v.innerHTML === '.') {
                    $(this).addClass("label_empty").css({ "width": "5%" });
                    // alert(v.innerHTML)
                }
            });
        }
    }
</script>
</body>

</html>