var reg = /^1[0-9]{10}$/;
var ROOTURL = "";
function checkphone(ele){
    var phonenum = ele.val();
    var tips = ele.next(".atips");
    var datadefault = tips.attr("data-default");
    var dataerror = tips.attr("data-error");
    var dataempty = tips.attr("data-empty");
    var datasuccess = tips.attr("data-success");
    var datacheck = tips.attr("data-check");
    var getcodebtn = ele.parent().parent().parent().find(".code").find(".getcodebtn");
    if(!phonenum){
        tips.html(dataempty).addClass("dataempty");
    } else {
       //验证是否为手机号码
        if (!reg.test(phonenum)) {
            //失败 弹出错误语句 格式不正确
            tips.html(dataerror).removeClass("datasuccess").addClass("dataerror");
            getcodebtn.removeClass("allowget");
            return false;
        }
//      if (getcodebtn.text().trim() != "获取验证码") {
//          return false;
//      }
        //手机号码判断地址
        var ajaxUrl ="/index.php?ctl=ajax&act=check_field";
        //发出的数据
        var queryData = { "field_name": "mobile", "field_data": phonenum };
        $.post(ajaxUrl,
           queryData,
            function (data) {
                data = JSON.parse(data);
                if (data.status == 1) {
                    //可以使用
                    tips.html(datasuccess).addClass("datasuccess");
                    //使验证码不能点击
                    ele.parent().parent().parent().find(".codebtnshadow").addClass("DisplayNone");
                    //判断 验证码按钮是否正确
                    if (getcodebtn.text().trim() == "获取验证码") {
                        //是的 可点击状态
                        getcodebtn.addClass("allowget");
                    } 
                } else {
                    //已存在
                    tips.html(datacheck).removeClass("datasuccess").addClass("dataerror");
                    getcodebtn.removeClass("allowget");
                    //getcodebtn.unbind("click");
                    ele.parent().parent().parent().find(".codebtnshadow").removeClass("DisplayNone");
                }
            });
    }
}
function checkusername(ele) {
    var value = ele.val().trim();
    var tips = ele.next(".atips");
    var dataerror = tips.attr("data-error");
    var dataempty = tips.attr("data-empty");
    var datasuccess = tips.attr("data-success");
    var datacheck = tips.attr("data-check");
    //长度小于3或大于15
    if (value.length < 3 || value.length > 15) {
        tips.html(dataerror).addClass("dataempty");
        return false;
    }
    //判断用户存在
    //手机号码判断地址
    var ajaxUrl = "/index.php?ctl=ajax&act=check_field";
    //发出的数据
    var queryData = { "field_name": "user_name", "field_data": value };
    $.post(ajaxUrl,
       queryData,
        function (data) {
            data = JSON.parse(data);
            if (data.status == 1) {
                //可以使用
                tips.html(datasuccess).addClass("datasuccess");
            } else {
                //已存在
                tips.html(datacheck).removeClass("datasuccess").addClass("dataerror");
            }
        });

}
function checkpassword(ele) {
    var value = ele.val().trim();
    var tips = ele.next(".atips");
    var dataerror = tips.attr("data-error"); 
    var dataempty = tips.attr("data-empty");
    var datasuccess = tips.attr("data-success");
    var datacheck = tips.attr("data-check");

    var reg = /^[a-zA-z0-9]{6,}$/;
    var regs = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,16}$/;

    if (reg.test(value)) {
        if (regs.test(value)) {
            //可以使用
            tips.html(datasuccess).addClass("datasuccess");
        }
        else {
            //安全系数低
            tips.html(datacheck).removeClass("datasuccess").addClass("dataerror");
            return false;
        }
    }
    else {
        tips.html(dataerror).removeClass("datasuccess").addClass("dataerror");
        return false;
    }
}
function codeclick(){
    $(".banner .getcodebtn").click(function(){
        var ele = $(this);
        getcode(ele);
    })
}
function getcode(ele){
    var phonenum = ele.parent(".cont").parent(".code").prev(".phone").find(".cont").find("input").val();
    if (!reg.test(phonenum)) {
        return false;
    }else{
        //取消click事件
        //ele.unbind("click");
        ele.next(".codebtnshadow").removeClass("DisplayNone");
        //取消可点击状态
        ele.removeClass("allowget");
        //鼠标焦点验证码输入框
        ele.parent(".cont").find("input").focus();

        // 这里开始发送验证码
        //手机号码判断地址
        var ajaxUrl = "/index.php?ctl=ajax&act=get_register_verify_code";
        //发出的数据
        var queryData = { "user_mobile":phonenum};
        $.post(ajaxUrl,
           queryData,
            function (data) {
                time(ele);
                //if (data.status == 1) {
                //    time(ele);
                //} else {
                    
                //}
            });
        
    }
}
var wait=60;//时间 
function time(o) {//o为按钮的对象，p为可选，这里是60秒过后，提示文字的改变 
	if (wait < 0) { 
	    o.html("获取验证码");//改变按钮中value的值 
	    // p.html("如果您在1分钟内没有收到验证码，请检查您填写的手机号码是否正确或重新发送"); 
	    wait = 60; 
	    o.addClass("allowget");
	    //o.bind("click",codeclick());
	    o.next(".codebtnshadow").addClass("DisplayNone");
	} else { 
	    // o.addAttr("disabled");//倒计时过程中禁止点击按钮 
	    o.html("已发送"+wait + "秒");//改变按钮中value的值 
	    wait--; 
	    setTimeout(function() { 
	    	time(o);//循环调用 
	    }, 1000) 
    } 
}

function code_click(){
    $("#alertregister .getcodebtn").click(function(){
        var ele = $(this);
        get_code(ele);
    })
}
function get_code(ele){
    var phonenum = ele.parent(".cont").parent(".code").prev(".phone").find(".cont").find("input").val();
    if (!reg.test(phonenum)) {
        return false;
    }else{
        //取消click事件
        //ele.unbind("click");
        ele.next(".codebtnshadow").removeClass("DisplayNone");
        //取消可点击状态
        ele.removeClass("allowget");
        //鼠标焦点验证码输入框
        ele.parent(".cont").find("input").focus();

        // 这里开始发送验证码
        //手机号码判断地址
        var ajaxUrl = "/index.php?ctl=ajax&act=get_register_verify_code";
        //发出的数据
        var queryData = { "user_mobile":phonenum};
        $.post(ajaxUrl,
           queryData,
            function (data) {
                time2(ele);
                //if (data.status == 1) {
                //    time(ele);
                //} else {
                    
                //}
            });
        
    }
}
var wait2=60;//时间 
function time2(o) {//o为按钮的对象，p为可选，这里是60秒过后，提示文字的改变 
    if (wait2 < 0) { 	
	    o.html("获取验证码");//改变按钮中value的值 
	    // p.html("如果您在1分钟内没有收到验证码，请检查您填写的手机号码是否正确或重新发送"); 
	    wait2 = 60; 
	    o.addClass("allowget");
	    //o.bind("click",codeclick());
	    o.next(".codebtnshadow").addClass("DisplayNone");
	} else { 
	    // o.addAttr("disabled");//倒计时过程中禁止点击按钮 
	    o.html("已发送"+wait2 + "秒");//改变按钮中value的值 
	    wait2--; 
	    setTimeout(function() { 
	       time2(o);//循环调用 
	    }, 1000) 
    } 
    
} 

function checkempty(ele){
    var num = ele.val();
    // alert(num);
    var dataempty = ele.next(".atips").attr("data-empty");
    var datasuccess = ele.next(".atips").attr("data-success");
    if(!num){
        ele.next("span").html(dataempty).removeClass("datasuccess").addClass("dataempty");
    }else{
        ele.next("span").html(datasuccess).removeClass("dataempty").addClass("datasuccess");
    }
}
//判断元素是否有事件
function checkEvent(ele,event) {
   return  $._data($(ele)[0], "events")[event];
}
$(function() {
    $(".phone input").blur(function() { 
        checkphone($(this));
    });
    $(".code input").blur(function() {
        checkempty($(this));
    });
    $(".name input").blur(function() {
        checkusername($(this));
        //checkempty(ele);
    });
    $(".password input").blur(function() {
        checkpassword($(this));
    });
    $(".password input").keyup(function () {
        $(".password .passwordConfirm").val($(this).val());
    });
    $(".banner .getcodebtn").click(function() {
        getcode($(this));
    });
    $("#alertregister .getcodebtn").click(function() {
        get_code($(this));
    });
    
});
