$(document).ready(function(){
	$('#listBox').on('mouseenter','li',function(){
		var url = $(this).find('img').attr('src'),
			name = $(this).attr('data-name'),
			jg = $(this).attr('data-jg'),
			tj = $(this).attr('data-tj'),
			itemW = $(this).outerWidth(),
			setT = $(this).offset().top,
			setL = $(this).offset().left,
			alertW = $('#showitem').outerWidth(),
			alertH = $('#showitem').outerHeight();
		// console.log(alertH);
			$('#showitem').stop(true,true).fadeIn(100).css({
				'left': setL-(alertW-itemW)/2,
				'top': setT-alertH
			});
		
		$('#showitem').find('img').attr('src',url);
		$('#showitem').find('.name').html(name);
		$('#showitem').find('.jg').html('价值￥'+jg);
		// $('#showitem').find('.tj').html(tj);
	})

	$('#listBox').on('mouseleave','li',function(){
		$('#showitem').fadeOut(1)
	})
	// 获取机会的点击事件
	$(".getchance").click(function(){
	    $("#cover").fadeIn(100);
	    $("#alertshare").fadeIn(100);
	    var winH = $(window).height();
	    var thisH = $("#alertshare .main").outerHeight();
	    $("#alertshare .blank").animate({"height":(winH-thisH)/2+"px"},200)
	})
    // 获取机会的关闭按钮
    $("#alertshare .closebtn").click(function(){
        $("#alertshare .blank").animate({"height":"0px"},100,function(){
        $("#alertshare").fadeOut(1);
        });
        if($(".alertresult").css("display") == "block"){
            return false;
        }else{
            $("#cover").fadeOut(200);
        }
        
    })
    // 获奖记录的点击事件
    $(".getrecord").click(function(){
        $("#cover").fadeIn(100);
        $("#alertrecord").fadeIn(100);
        var winH = $(window).height();
        var thisH = $("#alertrecord .main").outerHeight();
        $("#alertrecord .blank").animate({"height":(winH-thisH)/2+"px"},200)
    })
    // 获奖记录的关闭按钮
    $("#alertrecord .closebtn").click(function(){
        $("#alertrecord .blank").animate({"height":"0px"},100,function(){
        $("#alertrecord").fadeOut(1);
        });
        // if($("#alertresult").css("display") == "block"){
        //     return false;
        // }else{
            $("#cover").fadeOut(200);
        // }
        
    })
    // 抽奖成功的确定按钮
    $(".successbtn").click(function(){
        $("#alertresult .blank").animate({"height":"0px"},100,function(){
        $("#alertresult").fadeOut(1);
        });
        $("#cover").fadeOut(200);
    })
})