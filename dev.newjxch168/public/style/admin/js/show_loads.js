/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function viewLoanItem(obj,deal_id,l_key){
        if($(obj).hasClass("hide")){
                $(obj).removeClass("hide");
        }
        else{
                $(obj).addClass("hide");
        }

        if($.trim($(obj).html()) == "" || $.trim($(obj).html()) == ""){
                getLoanItem(obj,deal_id,l_key,1);
        }
}


function getLoanItem(obj,deal_id,l_key,p){
        var query=new Object();
        query.deal_id = deal_id;
        query.l_key = l_key;
        query.obj = obj;
        query.p = p;
        $.ajax({
                url:"/admin/finance/get_deal_loads",
                data:query,
                type:"post",
                dataType:"json",
                success:function(result){
                        if(result.status==1){
                                $(obj).html(result.info);
                        }
                        else{
                                alert(result.info);
                        }
                },
                error:function(){
                        alert("请求数据失败");
                }
        });
}

/**
*到导出投标列表	**/
function do_repay_plan_export_load(id){
        window.location.href = ROOT+"?"+VAR_MODULE+"="+MODULE_NAME+"&"+VAR_ACTION+"=get_deal_loads"+"&deal_id="+id+"&type=do_repay_plan_export_load";
}

function closeWindow(){
    $.weeboxs.close();
}