;(function($){
	$.fn.extend({
		slide: function(params){
			console.log(this.attr('class'));
			var ele = $(this);
			var defaults = {
					smNav: true,
					bgNav: true,
					active: 0,
					switchType: 'no'
				};
			var options = $.extend(true,{},defaults,params);

			// 插入导航栏
			if(options.smNav){
				$(ele).creatSmNav();
				$(ele).setSmNav(options.active);
			};
			if(options.bgNav){
				$(ele).creatBgNav();
			};
			// 显示默认的内容
			console.log('默认显示的index:'+options.active);
			$(ele).find('.slidebox>li').removeClass('active');
			$(ele).find('.slidebox>li').eq(options.active).addClass('active');
			// 根据切换方式改变显示的样式
			if(options.switchType == 'no'){
				$(ele).find('.slidebox').addClass('relative');
				$(ele).find('.slidebox>li').addClass('absolute');
			}
			// 设置小导航的点击事件
			$(ele).on('mouseover','.sm_nav>.item',function(){
				var clickIndex = $(ele).find('.sm_nav>.item').index($(this));
				console.log('当前点击的第：'+clickIndex);
				if(options.switchType == 'no'){
					$(ele).togglePageWithNoType(clickIndex);
				}
				
			})
		},
		creatSmNav: function(){
			var ele = $(this);
			var nav = $('<ul class="sm_nav"></ul>')
			$(ele).append(nav);
			var ullength = $(ele).find('ul.slidebox>li').length;
			for(i=1;i <= ullength;i++){
				$(nav).append('<li class="item"></li>')
			}
		},
		creatBgNav: function(){
			var ele = $(this);
			var nav = $('<ul class="bg_nav"></ul>')
			$(ele).append(nav);
			var ullength = $(ele).find('ul>li').length;
			for(i=1;i <= ullength;i++){
				$(nav).append('<li class="item"></li>')
			}
		},
		setSmNav: function(defaultIndex){
			var ele = $(this);
			var parentW = $(ele).outerWidth();
			var smnavW = $(ele).find('.sm_nav').outerWidth();
			console.log(parentW);
			console.log(smnavW);
			$(ele).find('.sm_nav').css({
				'left': (parentW - smnavW)/2
			});
			$(ele).find('.sm_nav>li').eq(defaultIndex).addClass('active');
		},
		togglePageWithNoType: function(clickIndex){
			var ele = $(this);
			var boxli = $(ele).find('.slidebox>li');
			var smnav = $(ele).find('.sm_nav>li');
			$(boxli).removeClass('active');
			$(boxli).eq(clickIndex).addClass('active');
			$(smnav).removeClass('active');
			$(smnav).eq(clickIndex).addClass('active');
		}
	})
})(jQuery)