/*
 * 红包页面公共js
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//红包放款
function grant_money(bonus_id, domobj){
        do_ajax = true;
        var now_action = document.getElementById('action').value;
        $.ajax({
                url: ROOT + "?" + VAR_MODULE + "=" + MODULE_NAME + "&" + VAR_ACTION + "=grant_money&bonus_id=" + bonus_id,
                data: "ajax=1",
                dataType: "json",
                success: function(data){
                            if(data.status == 1){
                                alert(data.info);
                                location.href = ROOT + '?m=UserBonus&a='+ now_action;
                            }else{
                                alert(data.info);
                            }
                }
        });
}
//批量放款
function batch_deal(){
        do_ajax = true;
        var now_action = document.getElementById('action').value;
        var begin_time = $("#begin_time").val();

        $.ajax({
                url: ROOT + "?" + VAR_MODULE + "=" + MODULE_NAME + "&" + VAR_ACTION + "=batch_deal&begin_time=" + begin_time,
                data: "ajax=1",
                dataType: "json",
                success: function(data){
                            if(data.status == 1){
                                alert(data.info);
                                location.href = ROOT + '?m=UserBonus&a='+ now_action;
                            }else{
                                alert(data.info);
                            }
                }
        });
}
//按要求搜索红包记录
function export_bonus(){
	do_ajax = true;
	var now_action = document.getElementById('action').value;
	var status = document.getElementById('status').value;
	var begin_time = $("#begin_time").val();
	
	var url= ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=export_bonus";
	var param = "&begin_time="+begin_time+"&now_action="+now_action+"&status="+status;
	location.href = url+param;
}

