// author: chinaliyun
// date:   2016-01-11
// PS:     You must load 'jquery' before user this plugin.
;(function($){
	$.fn.extend({
		slideNum: function(params){
			var ele = $(this);
			var defaults = {
					data: '',
					slideType: 'default',
					direction: 'top',
					timer: 10,
					minTimer: 0,
					splitIndex: 3
				};
			// console.log(typeof params);
			var options = (typeof params == 'number') 
					? $.extend(true,{},defaults,{data: params})
					: $.extend(true,{},defaults,params);
			// console.log(options.data);
			setSlideNum($(ele));	


			// 装填数字
			function setSlideNum(ele){
				console.log(0)
				// 清空原有数据
				var data_num =''+options.data;
				data_num = data_num.replace(/\B(?=(\d{3})+(?!\d))/g,',');
				$(ele).attr('data-num',parseInt(data_num) );
				// console.log(data_num);
				$(ele).text('');
				// 遍历数据，插入新的i标签
				$.each(data_num,function(index,value){
					var newele = $('<i></i>');
					$(newele).attr('data-subnum',data_num[index]);
					$(newele).text(data_num[index]);
					$(ele).append(newele);
					if(data_num[index] != ','){
						$(newele).text('' );
						// console.log(data_num[index]);
						insertSpan($(newele));
						begining($(newele));
					}
					
				})
			}
			
			function insertSpan(ele){
				$(ele).append('<div class="numbox"></div>')
				for(j=0;j<parseInt(options.timer)+options.minTimer;j++){
					for(i=0;i<=9;i++){
						var newele = $('<span></span>').text(i);
						$(ele).find('.numbox').append(newele);
					}
				}
				
			}

			function begining(ele){
				var timer = parseInt(options.timer);
				var minTimer = parseInt(options.minTimer);
				var data_subnum = parseInt( $(ele).attr('data-subnum') );
				var moveT = (timer + minTimer + data_subnum)*parseInt($(ele).outerHeight())
				var slidetime = parseInt((((Math.random()+1)*3-1))*1000);
				$(ele).find('.numbox').animate({
					'top': '-'+moveT
				},slidetime)
			}
			
		}
	})
})(jQuery);