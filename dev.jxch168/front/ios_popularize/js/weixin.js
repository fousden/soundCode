imgurl = "../images/p2_1.jpg";
var wxDefault = {
    title: "",
    desc: "【神指引】当整个世界仅剩下了它，你会如何选择…",
    imgUrl: imgurl,
    link: "http://wap.jxch168.com",
    success: function() {
    }
};
//$(function() {
//    var pageUrl = location.href;
//    $.ajax({
//        url: "https://wap.jxch168.com/wechart/index.php?ctl=wx&act=jssdk",
//        dataType: "jsonp",
//        jsonp: "jsoncallback",
//        data: { url: encodeURIComponent(pageUrl.trim()) },
//        success: function(data) {
//            data.debug = false;
//            wx.config(data);
//            wx.ready(function() {
//                wxShare();
//            });
//        }
//    });
//});

function wxShare(data) {
    if (typeof(wx) == "undefined") {
        return;
    }
    var newData = $.extend({}, wxDefault, data);
    wx.onMenuShareAppMessage({
        title: newData.title,
        desc: newData.desc,
        //imgUrl:newData.imgUrl,
        imgUrl: imgurl,
        link: $.trim(newData.link),
        success: function() {
            _mz_wx_friend();
        }
    });

    wx.onMenuShareQQ({
        title: newData.title,
        //imgUrl:newData.imgUrl,
        imgUrl: imgurl,
        link: $.trim(newData.link),
        success: function() {
            _mz_wx_qq();
        }
    });

    wx.onMenuShareWeibo({
        title: newData.title,
        //imgUrl:newData.imgUrl,
        imgUrl: imgurl,
        link: $.trim(newData.link),
        success: function() {
            _mz_wx_weibo();
        }
    });

    wx.onMenuShareTimeline({
        title: newData.title,
        //imgUrl:newData.imgUrl,
        imgUrl: imgurl,
        link: $.trim(newData.link),
        success: function() {
            _mz_wx_timeline();
        }
    });
}