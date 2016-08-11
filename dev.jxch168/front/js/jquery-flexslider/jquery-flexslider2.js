;(function($){
	$.fn.extend({
		slide: function(params){
			var ele = $(this);
			var defaults = {
					smNav: false,
					bgNav: false,
					active: 1,
					switchType: 'no',
					switchTrigger: 'click',
					direction: 'left',
					slideAuto: 5000,
					lilength : $(ele).find('ul.slidebox>li').length
				};
			var options = $.extend(true,{},defaults,params);
			// console.log(options.lilength);
			// 插入导航栏
			creatSmNav(ele);
			setSmNav(options.active);
			creatBgNav(ele);
			setBgNav(options.active);

			
			
			// 显示默认的内容
			// console.log('默认显示的index:'+options.active);
			$(ele).find('.slidebox>li').removeClass('active');
			$(ele).find('.slidebox>li').eq(options.active).addClass('active');

			// 根据切换方式改变显示的样式
			if(options.switchType == 'no' || options.switchType == 'fade'){
				$(ele).find('.slidebox').addClass('relative');
			}
			if(options.switchType == 'slide'){
				var slideboxW = $('.slidebox>li').outerWidth();
				$(ele).find('ul.slidebox').addClass('clear');
				$(ele).css({
					'overflow': 'hidden'
				})
				$(ele).find('ul.slidebox').css({
					'width': options.lilength * slideboxW
				})
				$(ele).find('ul.slidebox>li').css({
					'width': slideboxW
				})
			}

			// 设置小导航的点击事件
			$(ele).on(options.switchTrigger,'.sm_nav>.item',function(){
				var triggerIndex = $(ele).find('.sm_nav>.item').index($(this));
				// console.log('当前点击的第：'+triggerIndex);
				switchBegin(triggerIndex)
			})
			// 设置大导航的点击事件
			$(ele).on(options.switchTrigger,'.bg_nav>li.item.last',function(){
				var currentnIndex = $(ele).find('.slidebox>li').index($(ele).find('.slidebox>li.active'));
				var triggerIndex = currentnIndex - 1;
				if(currentnIndex == 0){
					triggerIndex = options.lilength-1;
				}
				switchBegin(triggerIndex)
			})
			$(ele).on(options.switchTrigger,'.bg_nav>li.item.next',function(){
				var currentnIndex = $(ele).find('.slidebox>li').index($(ele).find('.slidebox>li.active'));
				var triggerIndex = currentnIndex + 1;
				if(currentnIndex == options.lilength-1){
					triggerIndex = 0;
				}
				switchBegin(triggerIndex);
			})

			
			// 定时器
			var slidAuto;
			if(options.slideAuto){
				slideAuto = setInterval(function(){
					$(ele).find('.bg_nav>li.item.next').trigger(options.switchTrigger);
					// alert()
				},options.slideAuto);
			}
			$(ele).on('mouseleave',function(){
				slideAuto = setInterval(function(){
					$(ele).find('.bg_nav>li.item.next').trigger(options.switchTrigger);
				},options.slideAuto);
			})
			$(ele).on('mouseenter',function(){
				clearInterval(slideAuto);
			})
			function switchBegin(triggerIndex){
				if(options.switchType == 'no'){
					switchPageNoType(triggerIndex);
				}
				if(options.switchType == 'fade'){
					switchPageFade(triggerIndex);
				}
				if(options.switchType == 'slide'){
					switchPageSlide(triggerIndex,options.direction);
				}
				switchActive(triggerIndex);
			}

			function creatSmNav(ele){
				var nav = $('<ul class="sm_nav"></ul>');
				$(ele).append(nav);
				for(i=1;i <= options.lilength;i++){
					$(nav).append('<li class="item"></li>')
				}
				if(!options.smNav){
					$(ele).find('.sm_nav').fadeOut(1);
				};
			}
			function creatBgNav(ele){
				var nav = $('<ul class="bg_nav"></ul>')
				$(ele).append(nav);
				$(nav).append('<li class="item last"></li><li class="item next"></li>')
				if(!options.bgNav){
					$(ele).find('.bg_nav').fadeOut(1);
				};
			}


			function setSmNav(defaultIndex){
				var boxW = $(ele).outerWidth();
				var smnavW = $(ele).find('.sm_nav').outerWidth();
				$(ele).find('.sm_nav').css({
					'left': (boxW - smnavW)/2
				});
				$(ele).find('.sm_nav>li').eq(defaultIndex).addClass('active');
				// 当轮播方式为slide时，初始化显示的部分
				if(options.switchType == 'slide'){
					var slideboxW = $('.slidebox>li').outerWidth();
					$(ele).find('ul.slidebox').css({
						'left': '-'+(slideboxW*options.active)+'px'
					})
				}
			}
			function setBgNav(defaultIndex){
				var boxH = $(ele).outerHeight();
				var bgnavH = $(ele).find('.bg_nav>li.item').outerHeight();
				$(ele).find('.bg_nav>li.item').css({
					'top': (boxH - bgnavH)/2
				});
				$(ele).find('.sm_nav>li').eq(defaultIndex).addClass('active');
			}


			function switchPageNoType(triggerIndex){
				var boxli = $(ele).find('.slidebox>li');
				var smnav = $(ele).find('.sm_nav>li');
			}
			function switchPageFade(triggerIndex){
				var boxli = $(ele).find('.slidebox>li');
				var smnav = $(ele).find('.sm_nav>li');
				$(boxli).stop(true,true).fadeOut(1);
				$(boxli).eq(triggerIndex).fadeIn(500);
			}
			function switchPageSlide(triggerIndex){
				var boxli = $(ele).find('.slidebox>li');
				var smnav = $(ele).find('.sm_nav>li');
				var slideboxW = $('.slidebox>li').outerWidth();
                $(ele).find('.slidebox').stop(true,true).animate({
                	'left': '-'+slideboxW*triggerIndex+'px'
                },400);
			}
			function switchActive(triggerIndex){
				var boxli = $(ele).find('.slidebox>li');
				var smnav = $(ele).find('.sm_nav>li');
				$(boxli).removeClass('active');
				$(boxli).eq(triggerIndex).addClass('active');
				$(smnav).removeClass('active');
				$(smnav).eq(triggerIndex).addClass('active');
			}
		}
		
	})
})(jQuery)