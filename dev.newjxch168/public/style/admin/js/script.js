var K = null;
var viewOpAct = null;
$(document).ready(function(){
	init_word_box();
	$("form").bind("submit",function(){
		var doms = $(".require");
		var check_ok = true;
		$.each(doms,function(i, dom){
			if($.trim($(dom).val())==''||($(dom).val()=='0'&& $(dom).is("select")))
			{
					var title = $(dom).parent().parent().find(".item_title").html();
					if(!title)
					{
						title = '';
					}
					if(title.substr(title.length-1,title.length)==':')
					{
						title = title.substr(0,title.length-1);
					}
					if($(dom).val()=='')
					TIP = "请填写";
					if($(dom).val()=='0')
					TIP = "请选择";
					alert(TIP+title);
					$(dom).focus();
					check_ok = false;
					return false;
			}
		});
		if(!check_ok){
			return false;
		}

		check_ok = true;
		$(".require_radio").each(function(){
			if ($(this).find("input[type='radio']").length != 0) {
				if ($(this).find("input[type='radio']:checked").length == 0) {
					var title = $(this).parent().find(".item_title").html();
					if (!title) {
						title = '';
					}
					if (title.substr(title.length - 1, title.length) == ':') {
						title = title.substr(0, title.length - 1);
					}

					alert("请选择" + title);
					check_ok = false;
					return false;
				}
			}
		});
		if(!check_ok){
			return false;
		}
	});

	$(".dataTable .row").hover(function(){
		$(this).addClass("row_cur");
	},function(){
		$(this).removeClass("row_cur");

	});

	$(".dataTable .row .opration").click(function(){
		if($(this).hasClass("v")){
//			$(this).removeClass("v");
//			$(this).parent().find(".viewOpBox").hide();
		}
		else{
			$(this).addClass("v");
			viewOp($(this).parent());
			$(this).parent().find(".viewOpBox").show();
			var obj = $(this);
			$("body").one("click",function(){
				$(".dataTable a.opration").removeClass("v");
				obj.parent().find(".viewOpBox").hide();
			});
			return false;
		}
	});

	$(".dataTable a.A_opration").click(function(){
		if($(this).hasClass("v")){
			$(this).removeClass("v");
			$(".dataTable .row .opration").removeClass("v");
			$(".dataTable .row .viewOpBox").hide();
		}
		else{
			$(this).addClass("v");
			$(".dataTable .row .opration").addClass("v");
			$(".dataTable .row .opration").each(function(){
				viewOp($(this).parent());
			});

			$(".dataTable .row .viewOpBox").show();
			var obj = $(this);
			$("body").one("click",function(){
				$(".dataTable a.A_opration").removeClass("v");
				$(".dataTable .row .viewOpBox").hide();
			});
			return false;
		}
	});


	$(".dataTable .row td input[name='key']").click(function(){
		if($(this).attr("checked")=="checked"||$(this).attr("checked")==true || $(this).attr("checked")=="true"){
			$(this).parent().parent().addClass("row_chk");
		}
		else{
			$(this).parent().parent().removeClass("row_chk");
		}
	});

	 $('.J_autoUserName').live('focus',function (event) {
	 	var obj = $(this);
	    obj.autocomplete(ROOT+"?m=Public&a=autoloaduser", {
			width: 260,
			selectFirst: false,
			autoFill: false,    //自动填充
			dataType: "json",
			extraParams:{
				user_type:function(){return (obj.attr("user_type")==undefined ? 0 : obj.attr("user_type"))}
			},
			parse: function(data) {

				return $.map(data, function(row) {
					return {
						data: row,
						value: row.user_name,
						result: function(){
							if (row.id > 0)
								return row.user_name;
							else
								return "";
						}
					}
				});
			},
			formatItem: function(row, i, max) {
				return row.user_name + (row.real_name =="" ? "" : " [" + row.real_name + "]");
			}
		}).result(function(e,item) {
			$('.J_autoUserId').val(item.id);
			return item.id;
		});
	  });

        //今天
        $("#submit_date_0").bind("click",function(){
            $("#start_date").val(dec_date(0));
            $("#end_date").val(dec_date(0));
            $('#search_form').submit();
	});

	//昨天
	$("#submit_date_1").bind("click",function(){

            $("#start_date").val(dec_date(1));
            $("#end_date").val(dec_date(1));
            $('#search_form').submit();
	});

	//最近一周
	$("#submit_date_7").bind("click",function(){
            $("#start_date").val(dec_date(7));
            $("#end_date").val(dec_date(0));
            $('#search_form').submit();
	});

	//上上周
	$("#submit_date_8_14").bind("click",function(){
            $("#start_date").val(dec_date(14));
            $("#end_date").val(dec_date(8));
            $('#search_form').submit();
	});


	//最近一个月
	$("#submit_date_30").bind("click",function(){
            $("#start_date").val(dec_date(30));
            $("#end_date").val(dec_date(0));
            $('#search_form').submit();
	});
});

//排序
function sortBy(field,sortType)
{
	location.href = CURRENT_URL+"&_sort="+sortType+"&_order="+field+"&";
}
//添加跳转(更新时间为16-1-12 下午2:53)
function add(id)
{
    if(!id){
        var id = $(".key:checked").val();
    }
    location.href = ROOT + MODULE_NAME + "/" + CONTROLLER_NAME + "/add?id="+id;
}
//编辑跳转(更新时间为16-1-12 下午2:53)
function edit(id)
{
        location.href = ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/edit?id="+id;
}

function viewOp(obj){
	var viewOx =  obj.find(".viewOpBox");

	var html = "";
	viewOx.find("a").each(function(){
		if($.trim($(this).html())==""){
			$(this).remove();
		}
	});

	var stop = obj.offset().top ;
	var sheight= obj.innerHeight() - 2;
	var lineheight = obj.outerHeight() - 2;

	viewOx.css({top:stop,height:sheight,"line-height":lineheight+"px"});
	viewOx.html(viewOx.html().replace(/&nbsp;/g,""));
}

//全选
function CheckAll(tableID)
{
	$("#"+tableID).find(".key").attr("checked",Boolean($("#check").attr("checked")));
	$("#"+tableID).find(".key").each(function(){
		if($(this).attr("checked")=="checked" || $(this).attr("checked")=="true" || $(this).attr("checked")==true){
                    $(this).parent().parent().addClass("row_chk");
                }else{
                    $(this).parent().parent().removeClass("row_chk");
		}
	});
}


//公共删除方法
function publicDelete(model_name,table_name){

    idBox = $(".key:checked");
    if(idBox.length == 0)
    {
            alert("请选择id");
            return;
    }
    idArray = new Array();
    $.each( idBox, function(i, n){
            idArray.push($(n).val());
    });
    id = idArray.join(",");

    if(!confirm("确定要删除该条记录吗？")){
        return false;
    }

    $.ajax({
                    url:ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/publicDelete?model_name="+model_name+"&table_name="+table_name+"&id="+id,
                    data: "ajax=1",
                    dataType: "json",
                    success: function(obj){
                            if(obj.status==1){
                                $.pinphp.tip({content:obj.info, icon:'success'});
                                location.href=location.href;
                            }else{
                                $.pinphp.tip({content:obj.info, icon:'alert'});
                            }

                    }
    });
}

//改变状态
function set_effect(id,domobj)
{
		$.ajax({
				url:"set_effect?id="+id,
				data: "ajax=1",
				dataType: "JSON",
				url:ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/set_effect?id="+id,
				data: "ajax=1",
				dataType: "json",
				success: function(obj){
				if(obj.data=='1')
				{
					$(domobj).html("有效");
				}
				else if(obj.data=='0')
				{
					$(domobj).html("无效");
				}
				else if(obj.data=='')
				{

				}
				$("#info").html(obj.info);
			}
		});
}


function set_black(id, domobj){
	var do_ajax = false;
	var data = $(domobj).attr("data");
	if (data == "0" && confirm("是否要设置成黑名单?\n设置成黑名单将无法 贷款 和 提现 ！")) {
		do_ajax = true;
	}

	if (data == "1" && confirm("是否要取消黑名单?")) {
		do_ajax = true;
	}

	if (do_ajax) {
		$.ajax({
			url:"set_black?id=" + id,
			data: "ajax=1",
			dataType: "json",
			success: function(obj){

				if (obj.data == '1') {
					$(domobj).html("是");
					$(domobj).attr("data",obj.data);
				}
				else
				if (obj.data == '0') {
					$(domobj).html("否");
					$(domobj).attr("data",obj.data);
				}
				else
				if (obj.data == '') {

				}
				$("#info").html(obj.info);
			}
		});
	}
}


//普通删除（更新于16-1-12 下午3:00）
function del(id)
{

    if (!id)
    {
        idBox = $(".key:checked");
        if (idBox.length == 0)
        {
            alert("请选择id");
            return;
        }
        if (!confirm("确定要删除该条记录吗？")) {
            return false;
        }
        idArray = new Array();
        $.each(idBox, function (i, n) {
            idArray.push($(n).val());
        });
        id = idArray.join(",");
    }
    $("#shclKeyframes").show();
    $.ajax({
        url: ROOT + MODULE_NAME + "/" + CONTROLLER_NAME + "/delete?id=" + id,
        data: "ajax=1",
        dataType: "json",
        dataType: "json",
        success: function (obj) {
            $("#shclKeyframes").hide();
            $("#info").html(obj.info);
            if (obj.status == 1) {
                location.href = location.href;
            } else {
                alert(obj.info);
            }

        }
    });

}

//恢复（更新于16-1-14 下午3:00）
function restore(id)
{
	if(!id)
	{
		idBox = $(".key:checked");
		if(idBox.length == 0)
		{
			alert("请选择id");
			return;
		}
		idArray = new Array();
		$.each( idBox, function(i, n){
			idArray.push($(n).val());
		});
		id = idArray.join(",");
	}
	$.ajax({
                        url:ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/restore?id="+id,
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				$("#info").html(obj.info);
				if(obj.status==1){
                                    location.href=location.href;
                                }else{
                                    alert(obj.info);
                                }

			}
	});
}

//永久删除（更新于16-1-14 下午3:00）
function foreverdel(id)
{
	if(!id)
	{
		idBox = $(".key:checked");
		if(idBox.length == 0)
		{
			alert("请选择id");
			return;
		}
		idArray = new Array();
		$.each( idBox, function(i, n){
			idArray.push($(n).val());
		});
		id = idArray.join(",");
	}
	$.ajax({
			url:ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/foreverdel?id="+id,
			data: "ajax=1",
			dataType: "json",
			success: function(obj){
				$("#info").html(obj.info);
				if(obj.status==1){
                                    location.href=location.href;
                                }else{
                                    alert(obj.info);
                                }

			}
	});
}
//节点全选
function check_node(obj)
{
	$(obj.parentNode.parentNode.parentNode).find(".node_item").attr("checked",$(obj).attr("checked"));
}
function check_is_all(obj)
{
	if($(obj.parentNode.parentNode.parentNode).find(".node_item:checked").length!=$(obj.parentNode.parentNode.parentNode).find(".node_item").length)
	{
		$(obj.parentNode.parentNode.parentNode).find(".check_all").attr("checked",false);
	}
	else
		$(obj.parentNode.parentNode.parentNode).find(".check_all").attr("checked",true);
}

function init_word_box()
{
	$(".word-only").bind("keydown",function(e){
		if(e.keyCode<65||e.keyCode>90)
		{
			if(e.keyCode != 8)
			return false;
		}
	});
}

function dec_date(num){
	var today = new Date();
	today.setDate(today.getDate() - num);
	var d = today.getFullYear();
	if ((today.getMonth()+1) < 10)
		d = d + "-0" + (today.getMonth()+1);
	else
		d = d + "-" + (today.getMonth()+1);

	if (today.getDate() < 10)
		d = d + "-0" + today.getDate();
	else
		d = d + "-" + today.getDate();

	return d;
}

function IsDate(str){
	var r = str.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
	if(r==null)return false;
	var d= new Date(r[1], r[3]-1, r[4]);
	return (d.getFullYear()==r[1]&&(d.getMonth()+1)==r[3]&&d.getDate()==r[4]);
}

function IsTime(str){
	var r = str.match(/^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/);
	if(r==null)return false;
	var d = new Date(r[1], r[3]-1,r[4],r[5],r[6],r[7]);
	return (d.getFullYear()==r[1]&&(d.getMonth()+1)==r[3]&&d.getDate()==r[4]&&d.getHours()==r[5]&&d.getMinutes()==r[6]&&d.getSeconds()==r[7]);
}


function dateCompare(date1,date2){
	date1 = date1.replace(/\-/gi,"/");
	date2 = date2.replace(/\-/gi,"/");
	var time1 = new Date(date1).getTime();
	var time2 = new Date(date2).getTime();
	if(time1 > time2){
		return 1;
	}else if(time1 == time2){
		return 2;
	}else{
		return 3;
	}
}

function GetDateDiff(date1,date2)
{
	date1 = date1.replace(/\-/gi,"/");
	date2 = date2.replace(/\-/gi,"/");
	var time1 = new Date(date1).getTime();
	var time2 = new Date(date2).getTime();
	var dates = Math.abs((time1 - time2))/(1000*60*60*24);
	return dates;
}

function change_tag(obj,id){
		var group = $(obj).attr("g");
		var tags = $("."+group);
		tags.each(function(){$(this).hide();});
		$("."+group+"_"+id).show();
};

//设置标的属性 动态修改 新增
function set_deal_attr(model_name,table_name,field_name,id,domobj)
{
    $.ajax({
        url: ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/set_deal_attr?model_name="+model_name+"&table_name="+table_name+"&field_name="+field_name+"&id=" + id,
        data: "ajax=1",
        dataType: "json",
        success: function (obj) {
            if (obj.status)
            {
                $(domobj).html(obj.status_desc);
            }
        }
    });
}

//状态的显示
function get_is_effect(id)
{
    $.ajax({
        url: ROOT+MODULE_NAME+"/"+CONTROLLER_NAME+"/set_effect?&id=" + id,
        data: "ajax=1",
        dataType: "json",
        success: function (obj) {
            if (obj.status)
            {
                $(domobj).html(obj.status_desc);
            }
        }
    });
}

function get_set_status(id, ele) {
    var field=$(ele).attr("data");
    $.ajax({
        url: ROOT + MODULE_NAME + "/" + CONTROLLER_NAME + "/global_set_status?id=" + id+"&field_name="+field,
        data: "ajax=1",
        dataType: "json",
        success: function (res) {
            if (res.status==1)
            {
                $(ele).html(res.status_desc);
            }else{
                alert(res.info);
            }
        }
    });
}

//软删（更新于：16-1-15 下午2:25）
function global_soft_delete(id)
{
    if (!id)
    {
        idBox = $(".key:checked");
        if (idBox.length == 0)
        {
            alert("请选择id");
            return;
        }
        idArray = new Array();
        $.each(idBox, function (i, n) {
            idArray.push($(n).val());
        });
        id = idArray.join(",");
    }
    $("#shclKeyframes").show();
    $.ajax({
        url: ROOT + MODULE_NAME + "/" + CONTROLLER_NAME + "/global_soft_delete?r_type=1&id=" + id,
        data: "ajax=1",
        dataType: "json",
        success: function (obj) {
            $("#shclKeyframes").hide();
            $("#info").html(obj.info);
            if (obj.response_code == 1) {
                location.href = location.href;
            } else {
                alert(obj.show_err);
            }

        }
    });


}

function dec_date(num){
    var today = new Date();
    today.setDate(today.getDate() - num);
    var d = today.getFullYear();
    if ((today.getMonth()+1) < 10)
            d = d + "-0" + (today.getMonth()+1);
    else
            d = d + "-" + (today.getMonth()+1);

    if (today.getDate() < 10)
            d = d + "-0" + today.getDate();
    else
            d = d + "-" + today.getDate();

    return d;
}

function toogle_status(id,domobj,field){
	$.ajax({
		url: ROOT + MODULE_NAME + "/" + CONTROLLER_NAME +"/toogle_status&field="+field+"&id="+id,
		data: "ajax=1",
		dataType: "json",
		success: function(obj){
			alert(obj);
			if(obj.data=='1')
			{
				$(domobj).html(LANG['YES']);
			}
			else if(obj.data=='0')
			{
				$(domobj).html(LANG['NO']);
			}
			else if(obj.data=='')
			{

			}
			$("#info").html(obj.info);
		}
	});
}