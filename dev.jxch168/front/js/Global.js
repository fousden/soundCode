//全局参数
parms =  function() {
    this.FadeTime = 200;//渐隐时间
    this.SlideTime = 50;//滑动时间
};

//倒计时jq 原有的jq 请根据现格式重新引用
var leftTimeAct = function () {
    clearTimeout(leftTimeActInv);
    $(".AdvLeftTime").each(function () {
        var leftTime = parseInt($(this).attr("data"));
        if (leftTime > 0) {
            var day = parseInt(leftTime / 24 / 3600);
            var hour = parseInt((leftTime % (24 * 3600)) / 3600);
            var min = parseInt((leftTime % 3600) / 60);
            var sec = parseInt((leftTime % 3600) % 60);
            $(this).find(".day").html((day < 10 ? "0" + day : day));
            $(this).find(".hour").html((hour < 10 ? "0" + hour : hour));
            $(this).find(".min").html((min < 10 ? "0" + min : min));
            $(this).find(".sec").html((sec < 10 ? "0" + sec : sec));
            leftTime--;
            $(this).attr("data", leftTime);
        } else {
            $(this).html('时间已结束');
        }
    });

    leftTimeActInv = setTimeout(function () {
        leftTimeAct();
    }, 1000);
};
var leftTimeActInv = null;
$(document).ready(function () {
    //iconfont旋转
    if ($(".iconfont").length > 0) {
        $(".iconfont").hover(
          function () {
            $(this).animate({ rotate: "90deg" }, 1000);
        },function() {
            $(this).animate({ rotate: "-90deg" }, 1000);
        });
        
    }
    

    //倒计时jq 当存在投资列表时才调用
    if ($("#investList .box").length > 0) {
        leftTimeAct();
    }

    if ($(".sample_goal").length > 0) {
        $(".sample_goal").each(function () {
            var ptc = $(this).attr("ptc");
            $(this).animate({ "width": ptc + "%" }, 1000);
        });
    }
    
    //关于我们左侧导航栏收起、展开效果
    $(".lasthd").click(function(){
        var lashhd = $(".aboutnews").css("display");
        if(lashhd == "none"){
            $(".aboutnews").slideDown(this.SlideTime);
        }else{
            $(".aboutnews").slideUp(this.SlideTime);
        }
    })

    //首页引导层js效果控制
    function indexguide(){

        if ($(".flexslider").length>0) {
        $(".flexslider").flexslider({
        animation: "slide",  // 改变切换方式
        slideshow: false, //是否自动开始动画
        slideshowSpeed: 4000, //展示时间间隔ms
        animationSpeed: 400, //滚动时间ms
        touch: true, //是否支持触屏滑动
        directionNav: true,//是否显示左右控制
        controlNav: true //  是否显示控制小手柄
        // slideToStart: 4000
        });
        var winH=$(window).height();
        // alert(abc);
        // document.title = abc;
        $("#indexguidecover").css({"height":winH+"px","display":"block"});
        $(".indexguideclose").click(function(){
            $("#indexguide").hide();
            $("#indexguidecover").hide();
        })

        var eleH=$("#indexguidebox").outerHeight();
        // alert((winH-eleH)/2);
        $(".indexguidenone").css({"height":(winH-eleH)/2-80+"px"});
        }
    }
    indexguide();
    
    
});