$(document).ready(function(){
        switch_send_type();
	switch_msg_templet();
	switch_msg_type();
        $("select[name='send_type']").bind("change",function(){
		switch_send_type();
	});
	$("select[name='msg_templet']").bind("change",function(){
		switch_msg_templet();
	});
	$("select[name='msg_type']").bind("change",function(){
		switch_msg_type();
	});	
});

//根据发送方式选用相应的模板
function switch_send_type(){
    var send_type = $("select[name='send_type']").val();
    if(send_type == 0){
        $("#sms_temp").show();
        $("#mail_temp").hide();	
        $("#is_html").hide();
        $("textarea[name='content']").removeClass('cg');
    }else{
        $("#sms_temp").hide();
        $("#mail_temp").show();
        $("#is_html").show();
        $("textarea[name='content']").addClass('cg');
    }
}
//根据模板名称在content中显示模板内容
function switch_msg_templet()
{
        $("#cont").val('');
        var data = {};
        data.send_type = $("select[name='send_type']").val();
        console.log(data);
        if(data.send_type == 0){
            data.temp_id = $("#sms_temp").val();
        }else{
           data.temp_id = $("#mail_temp").val();
        }
	
	if(data.temp_id != 0){
            $.ajax({
                url: "/admin/msg_send/get_temp",
                data:data,
                type: "POST",
                dataType: "json",
                success: function(result){
                    if(result){
                        $("textarea[name='content']").val(result);
                    }else{
                        alert('未找到模板');
                    }

                }
            })
        }

}

//切换发送方式
function switch_msg_type()
{
	var msg_type = $("select[name='msg_type'] ").val();
	if(msg_type == 1){
            $("#send_define_data").hide();
	}else{
            $("#send_define_data").show();
	}
}


