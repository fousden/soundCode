window.onload=function(){
	var img1 = document.getElementById('succ_bg');
	var img2 = document.getElementById('mobile_bg');
	// alert(typeof img1);
	img1.onload = new function(){
		setCentY($('.succ_bg'),'window');
		setCentX($('.succ_bg'),'window');
	}
	img2.onload = new function(){
		setCentY($('.mobile_bg'),'window');
		setCentX($('.mobile_bg'),'window');
	}

	// setCentY($('.alertsuccess .main'),'window');
	// setCentX($('.alertsuccess .main'),'window');
	// setCentY($('.alertmobile .main'),'window');
	// setCentX($('.alertmobile .main'),'window');
	$('.alertmobile,.alertcover,.alertsuccess').css({
		'display': 'none',
		'visibility': 'visible'
	});
	var machine1 = $("#machine1").slotMachine({
		active	: 0,
		delay	: 100
	});

	var machine2 = $("#machine2").slotMachine({
		active	: 0,
		delay	: 100
	});


	$("#beginBtn").click(function(){
		//alert(0)
		$.ajax({
			type:"post",
			dataType: "JSON",
			url:"./index.php?ctl=activity&act=new_year&s_ajax=1&is_pc=1",
			success:function(data){
				if(data.code==-1){
					$('.alertcover,.alertmobile').fadeIn(100);
				}else if(data.code==0){
					alert("当前不在活动时间内，请联系客服！");
					return;
				}else if(data.code==1){
					alert("每个手机号只能领一次");
				}else{
					beginRun(data.prize_num)
				}
			}
		});
	});
	$('.closebtn').click(function(){
		$(this).parent().parent().fadeOut(100);
		$('.alertcover').fadeOut(100);
	})
	function beginRun(setResult){
		var finallyResult = setResult ? setResult : parseInt(Math.random()*10);
		if (machine1.isRunning || machine2.isRunning){
			alert('正在努力为您加载哦！')
			return false;
		}else{
			machine1.setRandomize(0);
			machine1.shuffle(20);

			setTimeout(function(){
				machine2.setRandomize(finallyResult);
				machine2.shuffle(20,function(){
					checkResult();
				});
			}, 500);
		}
	}
	$('.alertmobile').find('#mobile').keyup(function(event){
		console.log(event.which);
		if(event.which == 13){
			checkMobile();
		}
	})
	$('.alertmobile').find('.gobegin').click(function(){
		checkMobile();
	})

	// 判断手机号码格式
	function checkMobile(){
		var mobilereg = /^\d{11}$/;
		if(!$('.alertmobile').find('#mobile').val().match(mobilereg)){
			$('.alertmobile').find('.error').addClass('visible');
			return false;
		}else{
			$('.alertcover,.alertmobile').fadeOut(100);
			var $mobile = $("#mobile").val();
			$.ajax({
				type:"post",
				dataType: "JSON",
				url:"./index.php?ctl=activity&act=new_year&is_pc=1&s_ajax=1&mobile="+$mobile,
				success:function(data){
					if(data.code==0){
						alert("当前不在活动时间内,请联系客服");
					}else if(data.code==1){
						alert("每个手机号只能领一次！")
					}else{
						beginRun(data.prize_num);
					}
				}
			})

		}
		// 这里要判断手机号码是否注册 传一个参数到beginRun()
	}
	function checkResult(){
		console.log(machine2.active);
		$('.alertcover,.alertsuccess').fadeIn(100);
		var result = ['一等奖','二等奖','三等奖','参与奖'];
		$.each(result,function(index,value){
			if(machine2.active == index){
				$('.alertsuccess').find('.succ_text').text(value);
			}
		})

	}
	$('.getgift').click(function () {
		var qudao = ((window.location.href.slice(window.location.href.indexOf("s=")).split("&"))[0].split('='))[1];
		if (!qudao||qudao.trim()=="") {
			qudao  = 'public';
		};
		var $mobile = $("#mobile").val();
		$.ajax({
			type:"get",
			dataType: "JSON",
			url:"./index.php?ctl=activity&act=get_bonus&is_pc=1&s_ajax=1&mobile="+$mobile,
			success:function(data){
				if(data.code==-1){
					alert("领取成功，请去我的红包中查看");
				}else if(data.code==0){
					alert("领取成功，注册登录之后可在我的红包中查看");
					if(data.is_mobile==0){
						location.href="./index.php?ctl=user&act=register&is_pc=1&s="+qudao;
					}
					if(data.is_mobile==1){
						// 客户端
						location.reload();
					}
					if(data.is_mobile==2){
						// 手机端网页
						location.href="wap/index.php?ctl=register&is_pc=1";
					}
				}else{
					alert("领取成功，登录之后可在我的红包中查看");
					if(data.is_mobile==0){
						location.href="./index.php?ctl=user&act=login&is_pc=1";
					}
					if(data.is_mobile==1){
						location.reload();// 客户端
					}
					if(data.is_mobile==2){
						location.href="wap/index.php?ctl=login&is_pc=1";// 手机端网页
					}
				}
			}
		});
	})
	function setCentY(ele1,ele2){
		var eleH1 = parseInt($(ele1).outerHeight()),
			eleH2 = (ele2 == 'window') ? parseInt($(window).height()) : parseInt($(ele2).outerHeight()),
			disT = (eleH2 - eleH1)/2;
		$(ele1).css({
			'top': disT
		})
	}
	function setCentX(ele1,ele2){
		var eleH1 = parseInt($(ele1).outerWidth()),
			eleH2 = (ele2 == 'window') ? parseInt($(window).width()) : parseInt($(ele2).outerWidth()),
			disL = (eleH2 - eleH1)/2;
		$(ele1).css({
			'left': disL
		})
	}
}