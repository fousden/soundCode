;(function($){
    $.pinphp = $.pinphp || {version: "v1.0.0"},
    $.extend($.pinphp, {
        util: {
            getStrLength: function(str) {
                str = $.trim(str);
                var length = str.replace(/[^\x00-\xff]/g, "**").length;
                return parseInt(length / 2) == length / 2 ? length / 2: parseInt(length / 2) + .5;
            },
            empty: function(str) {
                return void 0 === str || null === str || "" === str
            },
            isURl: function(str) {
                return /([\w-]+\.)+[\w-]+.([^a-z])(\/[\w-.\/?%&=]*)?|[a-zA-Z0-9\-\.][\w-]+.([^a-z])(\/[\w-.\/?%&=]*)?/i.test(str) ? !0 : !1
            },
            isEmail: function(str) {
                return /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(str);
            },
            minLength: function(str, length) {
                var strLength = $.qupai.util.getStrLength(str);
                return strLength >= length;
            },
            maxLength: function(str, length) {
                var strLength = $.qupai.util.getStrLength(str);
                return strLength <= length;
            },
            redirect: function(uri, toiframe) {
                if(toiframe != undefined){
                    $('#' + toiframe).attr('src', uri);
                    return !1;
                }
                location.href = uri;
            },
            base64_decode: function(input) {
                var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                var output = "";
                var chr1, chr2, chr3 = "";
                var enc1, enc2, enc3, enc4 = "";
                var i = 0;
                //if(typeof input.length=='undefined')return '';
                if(input.length%4!=0){
                    return "";
                }
                var base64test = /[^A-Za-z0-9\+\/\=]/g;

                if(base64test.exec(input)){
                    return "";
                }

                do {
                    enc1 = keyStr.indexOf(input.charAt(i++));
                    enc2 = keyStr.indexOf(input.charAt(i++));
                    enc3 = keyStr.indexOf(input.charAt(i++));
                    enc4 = keyStr.indexOf(input.charAt(i++));

                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;

                    output = output + String.fromCharCode(chr1);

                    if (enc3 != 64) {
                        output+=String.fromCharCode(chr2);
                    }
                    if (enc4 != 64) {
                        output+=String.fromCharCode(chr3);
                    }

                    chr1 = chr2 = chr3 = "";
                    enc1 = enc2 = enc3 = enc4 = "";

                } while (i < input.length);
                return output;
            }
        }
    });
})(jQuery);

;(function($){
    //把对象调整到中心位置
    $.fn.setmiddle = function() {
        var dl = $(document).scrollLeft(),
            dt = $(document).scrollTop(),
            ww = $(window).width(),
            wh = $(window).height(),
            ow = $(this).width(),
            oh = $(this).height(),
            left = (ww - ow) / 2 + dl,
            top = (oh < 4 * wh / 7 ? wh * 0.382 - oh / 2 : (wh - oh) / 2) + dt;

        $(this).css({left:Math.max(left, dl) + 'px',top:Math.max(top, dt) + 'px'});
        return this;
    }
    //返回顶部
    $.fn.returntop = function() {
        var self = $(this);
        self.live({
            mouseover: function() {
                $(this).addClass('return_top_hover');
            },
            mouseout: function() {
                $(this).removeClass('return_top_hover');
            },
            click: function() {
                $("html, body").animate({scrollTop: 0}, 120);
            }
        });
        $(window).bind("scroll", function() {
            $(document).scrollTop() > 0 ? self.fadeIn() : self.fadeOut();
        });
    }
})(jQuery);

;(function($){
    //提示信息
    $.pinphp.tip = function(options) {
        var settings = {
            content: '',
            icon: 'success',
            time: 5000,
            close: false,
            zindex: 2999
        };
        if(options) {
            $.extend(settings, options);
        }
        if(settings.close){
            $(".tipbox").hide();
            return;
        }
        if(!$('.tipbox')[0]){
            $('body').append('<div align="center" class="tipbox"><div class="tip-l"></div><div align="center" class="tip-c"></div><div class="tip-r"></div></div>');
            $('.tipbox').css('z-index', parseInt(settings.zindex));
        }
        $('.tipbox').attr('class', 'tipbox tip-' + settings.icon);
        $('.tipbox .tip-c').html(settings.content);
        $('.tipbox').css('z-index', parseInt($('.tipbox').css('z-index'))+1).setmiddle().show();

        if(settings.time>0){
            setTimeout(function() {
                $(".tipbox").fadeOut()
            }, settings.time);
        }
    }
})(jQuery);
