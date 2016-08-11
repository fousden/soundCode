$(function(){
	//绑定提交按钮
	$("input[name='admin_name']").focus();
	$(".submit").bind("click",function(){ do_login();});
	$("input[name='admin_name']").bind("keypress",function(event){
		if(event.keyCode==13)
		{
			$("input[name='admin_pwd']").focus();
		}
	});
	$("input[name='admin_pwd']").bind("keypress",function(event){
		if(event.keyCode==13)
		{
			$("input[name='verify_code']").focus();
		}
	});
	$("input[name='verify_code']").bind("keypress",function(event){
		if(event.keyCode==13)
		{
			do_login();
		}
	})
	//绑定提交结束

	$("#verify").bind("click",function(){
		timenow = new Date().getTime();
		$(this).attr("src",$(this).attr("src")+"?rand="+timenow);
	});
});

function do_login(){

	$(this).attr("disabled",true);

	//验证帐号
	if($.trim($(".admin_name").val())=='')
	{
		$(".admin_name").val("");
		$(".admin_name").focus();
		$("#login_msg").html("账户不能为空");
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");
		    $(".submit").attr("disabled",false);
		 });
		return;
	}
	//验证密码
	if($.trim($(".admin_pwd").val())=='')
	{
		$(".admin_pwd").val("");
		$(".admin_pwd").focus();
		$("#login_msg").html("密码不能为空");
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");
		    $(".submit").attr("disabled",false);
		 });
		return;
	}

	//验证密码
	if($.trim($(".adm_verify").val())=='')
	{
		$(".adm_verify").val("");
		$(".adm_verify").focus();
		$("#login_msg").html("验证码不能为空");
		$("#login_msg").oneTime(2000, function() {
		    $(this).html("");
		    $(".submit").attr("disabled",false);
		 });
		return;
	}
	//表单参数
	var query = new Object();
	query.admin_name = $(".admin_name").val();
	query.admin_pwd = $(".admin_pwd").val();
	query.verify_code = $(".adm_verify").val();
	query.ajax = 1;
	url = $("form").attr("action");

	$(".admin_name").attr("disabled",true);
	$(".admin_pwd").attr("disabled",true);
	$(".adm_verify").attr("disabled",true);
	$.ajax({
		url: url,
		data: query,
		type:"post",
		dataType: "json",
		success: function(obj){
			if(obj.status)
			{
                            $("#login_msg").html(obj.info);
                            $("#login_msg").oneTime(2000, function() {
                                $(this).html("");
                                location.href = obj.url;
                             });
			}
			else
			{
				$("#login_msg").html(obj.info);
				$("#login_msg").oneTime(1000, function() {
				    $(this).html("");
				    $(".submit").attr("disabled",false);
				    $(".admin_name").attr("disabled",false);
                                    $(".admin_pwd").attr("disabled",false);
                                    $(".adm_verify").attr("disabled",false);
                                    $("#verify").click();
				 });
			}
	}});
}