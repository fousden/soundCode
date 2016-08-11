$(document).ready(function(){
	// 下拉框效果
	$('.dropdown').find('.data').on({
		click: function(){
			$(this).find('div.value').slideToggle(100);
		},
		mouseleave:function(){
			$(this).find('div.value').slideUp(100);
		}
	});
	// 返回下拉框内容
	$('.dropdown').find('.valueitem').on({
		click: function(){
			$(this).parent().parent().find('input').val($(this).text());
		}
	});
	// 如果存在车贷和房贷，则打开后续内容
	$('#fangdai1,#chedai1').click(function(){
		$(this).parent().parent().next('li').slideDown(100);
	})
	// 根据不存在车贷和房贷，则关闭后续内容，并初始化input的val值
	$('#fangdai2,#chedai2').click(function(){
		$(this).parent().parent().next('li').slideUp(100);
		$(this).parent().parent().next('li').find('input:text').val('');
		$(this).parent().parent().parent().parent().find('.status_num').slideUp(100);
		$(this).parent().parent().parent().parent().find('.status_time').slideUp(100);
	});
	// 当选择车贷和房贷状态的时候，根据状态实际情况，决定是否显示后续内容
	$('.status').find('.valueitem').click(function(){
		if($(this).index($(this).parent().find('.valueitem')) == 0){
			$(this).parent().parent().parent().parent().find('.status_num').slideDown(100);
			$(this).parent().parent().parent().parent().find('.status_time').slideUp(100);
		}else{
			
			$(this).parent().parent().parent().parent().find('.status_time').slideDown(100);
                        $(this).parent().parent().parent().parent().find('.status_num').slideUp(100);
		}
		
	});
	// 显示错误提示
	function showError(ele){
		$(ele).parent().next('label').fadeIn(100);
		$(ele).focus();
	};
	// 隐藏错误提示
	function hideError(ele){
		$(ele).parent().next('label').fadeOut(100);
	};
	// 检查年龄
	function checkAge(ele){
		var reg = /^\d{1,2}$/;
		if(!$(ele).val().match(reg)){
			showError(ele);
			return false;
		}else{
			hideError(ele)
			return true;
		}
	};
	// 检查是否为空
	function checkEmpty(ele){
		if(!$(ele).val()){
			showError(ele);
			return false;
		}else{
			hideError(ele)
			return true;
		}
	};
	// 检查单选框
	function checkRadio (ele) {
		// console.log($(ele).find('input:checked').length);
		if($(ele).find('input:checked').length < 1){
			$(ele).find('.datatip').fadeIn(100);
			return false;
		}else{
			$(ele).find('.datatip').fadeOut(100);
			return true;
		}
	}
	/*$('.dataitem').mouseleave(function(){
		step1();
	})*/
	// 提交表单
	$('.submit').click(function(){
		if(!step1()){
			step2();
		}

	});
	function step1(){
		if(!checkAge($('#age'))){
			// 如果年龄为空
			return false;
		}
		if(!checkEmpty($('#city'))){
			// 如果城市为空
			return false;
		}
		if(!checkEmpty($('#live_time'))){
			// 如果居住时间为空
			return false;
		}
		if(!checkEmpty($('#salary'))){
			// 如果月薪为空
			return false;
		}
		if(!checkRadio($('.salary_way'))){
			return false;
		}
		if(!checkRadio($('.fangdai'))){
			return false;
		}
		if($('.fangdai_status').is(':visible')){
			if(!checkEmpty($('#fangdai_status'))){
				return false;
			}
		}
		
		if($('.fangdai_status_time').is(':visible')){
			if(!checkEmpty($('#fangdai_status_time'))){
				return false;
			}
		}
		
		if($('.fangdai_status_num').is(':visible')){
			if(!checkEmpty($('#fangdai_status_num'))){
				return false;
			}
		}

		if(!checkRadio($('.chedai'))){
			return false;
		}
		if($('.chedai_status').is(':visible')){
			if(!checkEmpty($('#chedai_status'))){
				return false;
			}
		}
		
		if($('.chedai_status_time').is(':visible')){
			if(!checkEmpty($('#chedai_status_time'))){
				return false;
			}
		}
		
		if($('.chedai_status_num').is(':visible')){
			if(!checkEmpty($('#chedai_status_num'))){
				return false;
			}
		}
		if(!checkRadio($('.chexian'))){
			return false;
		}
		if(!checkRadio($('.shouxian'))){
			return false;
		}
		if(!checkRadio($('.xinyongka'))){
			return false;
		}
		if(!checkEmpty($('#money'))){
			return false;
		}
	}
	function step2(){
		// 在此做ajax操作
		// 获取url中的参数
		var name = GetQueryString("name");
		var mobile = GetQueryString("mobile");
		var sex = GetQueryString("sex");
		var safe = GetQueryString("safe");
		var age = $("#age").val();
		var city = $("#city").val();
		var live_time = $("#live_time").val();
		var salary = $("#salary").val();
		var salary_way = $("input[name='salary_way']:checked").val();
		// 房贷
		var fangdai = $("input[name='fangdai']:checked").val();
		var fangdai_status = '无房贷';
		var fangdai_status_time='无房贷'; // 房贷还清距今天数
		var fangdai_status_num='无房贷'   // 房贷已还期数
		if(fangdai==1){
			var fangdai_status = $("#fangdai_status").val();
			if(fangdai_status=='房贷已还清'){
				var fangdai_status_time = $("#fangdai_status_time").val();// 房贷还清距今天数
				var fangdai_status_num = $("#fangdai_status_num").val(); // 房贷已还期数
			}else{
				var fangdai_status_time = "房贷未还清";
				var fangdai_status_num = $("#fangdai_status_num").val(); // 房贷已还期数
			}

		}
		/*处理下可能会出现的情况*/
		// 车贷
		var chedai = $("input[name='chedai']:checked").val();
		var chedai_status = '无车贷'
		var chedai_status_time = '无车贷'; // 车贷还清距今天数
		var chedai_status_num = '无车贷';  // 车贷已还期数
		if(chedai==1){
			var chedai_status = $("#chedai_status").val();
			if(chedai_status=='车贷已还清'){
				var chedai_status_time = $("#chedai_status_time").val();// 车贷还清距今天数
				var chedai_status_num = $("#chedai_status_num").val();// 车贷已还期数
			}else{
				var chedai_status_time = "车贷未还清";
				var chedai_status_num = $("#chedai_status_num").val();// 车贷已还期数
			}

		}
		var chexian = $("input[name='chexian']:checked").val();
		var shouxian = $("input[name='shouxian']:checked").val();
		var xinyongka = $("input[name='xinyongka']:checked").val();
		var money = $("#money").val();
                //数据处理
               switch(live_time){
                    case "1~5个月":
                        live_time = 1;
                        break;
                    case "6~12个月":
                        live_time = 2;
                        break;
                    case "1年以上":
                        live_time = 3;
                        break;
                    default:
                        live_time = 0;
                }
                switch(salary){
                    case "3000元以下":
                        salary = 1;
                        break;
                    case "3000~10000元":
                        salary = 2;
                        break;
                    case "10000~20000元":
                        salary = 3;
                        break;
                    case "20000元以上":
                        salary = 4;
                        break;
                    default:
                        salary = 0;
                }
                switch(fangdai_status){
                    case "正在还房贷":
                        fangdai_status = 1;
                        break;
                    default:
                        fangdai_status = 0;    
                }
                switch(fangdai_status_time){
                    case "正在还房贷":
                        fangdai_status_time = 1;
                        break;
                    case "房贷已还清":
                        fangdai_status_time = 2;
                        break;    
                    default:
                        fangdai_status_time = 0;    
                }
                switch(fangdai_status_num){
                    case "1~6个月":
                        fangdai_status_num = 1;
                        break;
                    case "7~12个月":
                        fangdai_status_num = 2;
                        break;
                     case "12个月以上":
                        fangdai_status_num = 3;
                        break;
                    default:
                        fangdai_status_num = 0;
                }
                
                switch(chedai_status){
                    case "正在还车贷":
                        chedai_status = 1;
                        break;
                    default:
                        chedai_status = 0;
                }
                switch(chedai_status_time){
                    case "正在还车贷":
                        chedai_status_time = 1;
                        break;
                    case "车贷已还清":
                        chedai_status_time = 2;
                        break;    
                    default:
                        chedai_status_time = 0;    
                }
                switch(chedai_status_num){
                    case "1~6个月":
                        chedai_status_num = 1;
                        break;
                    case "7~12个月":
                        chedai_status_num = 2;
                        break;
                     case "12个月以上":
                        chedai_status_num = 3;
                        break;
                    default:
                        chedai_status_num = 0;
                }
                
                switch(money){
                    case "1万以下":
                        money = 1;
                        break;
                    case "1~15万":
                        money = 2;
                        break;
                    case "15~30万":
                        money = 3;
                        break;
                    default:
                        money = 0;
                }
                
		$.ajax({
			type:"post",
			url:"/home/loan/details",
			data:{age:age,city:city,live_time:live_time,salary:salary,salary_way:salary_way,fangdai:fangdai,fangdai_status:fangdai_status,fangdai_status_time:fangdai_status_time,fangdai_status_num:fangdai_status_num,chedai:chedai,chedai_status:chedai_status,chedai_status_time:chedai_status_time,chedai_status_num:chedai_status_num,chexian:chexian,shouxian:shouxian,xinyongka:xinyongka,money:money},
			dataType: "json",
                        success:function(data){
				if(data==1){
					location.href="/home/loan/success";
				}
			}
		})
	}

	function GetQueryString(name)
	{
		var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
		var r = window.location.search.substr(1).match(reg);
		if(r!=null)return  decodeURIComponent(r[2]); return null;
	}

})