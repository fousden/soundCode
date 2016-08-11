;(function($){
	// creatBy:liyun
	// url: www.chinaliyun.cn
	// creatDate:2015-11-03
	// verson: 2.0
	// 插件是基于jquery扩展的，引用前务必先引入jquery.js文件
	// 所有样式上的调整请全部在css文件中修改
	// 简单模式中，参数顺序为：标题，内容，z-index值，透明度；
	// 如果没有指定则使用默认值
	$.fn.extend({
		popup : function(params){
			var eles = $(this);
			var defaults,options;
			$.each(eles,function(index,value){
				// console.log($(eles[index]).attr('id'))
				var nodeId = $(eles[index]).attr('id'),
					nodeClass = $(eles[index]).attr('class');

				// 设置默认参数
				defaults = {
					boxClass: nodeId ? 'popup'+nodeId : 'popup'+nodeClass,
					coverClass: nodeId ? 'cover'+nodeId : 'cover'+nodeClass,
					closeClass:  nodeId ? 'close'+nodeId : 'close'+nodeClass,
					width: 500,
					height: 'auto',
					autoOpen: true,
					title: '请输入标题',
					titleHide: false,
					zIndex: 100,
					coverOpacity: 0.4,
					matte: true,
					content: '请输入弹窗内容'
				};
				// 合并用户参数
				options = typeof params == 'object' 
					? $.extend(true,{},defaults,params) 
					: $.extend(true,{},defaults,{
						handler: params
							}
						);
					// console.log(options);
				// for( i in options){
					// console.log(i+':'+options[i]);
				// }
				function isCreated(){
					return $('.'+options.boxClass).length == 0 ? false : true;
				}
				// 如果已经存在对应的弹窗则不生成新的弹窗
				if(!isCreated()){
					creatNewBox();
				}
				// if(options.handler == 'open'){
				// 	showBox();
				// }
				switch(options.handler){
					case  'open':
					showBox();
					break;

					case 'close':
					hideBox();
					break;

					case 'destory':
					destoryBox();
					break;
				}
			})
			

			
			function creatNewBox(){
				// 获取窗口尺寸
				var winW = $(window).width(),
					winH = $(window).height();
				// 如果matte为true创建、设置遮罩层，否则不显示遮罩层
				if(options.matte){
					var cover = $('<div></div>');
					cover.addClass(options.coverClass+' popupcover')
					$('body').append(cover);
					$(cover).css({
						'width': winW,
						'height': winH,
						'position': 'fixed',
						'top': 0,
						'left': 0,
						'z-index': options.zIndex,
						'backgroundColor': 'black',
						'display': 'none',
						'opacity': options.coverOpacity
					});
				}

				// 创建弹窗框架
				var box = $('<div></div>');
				box.addClass(options.boxClass+' popupbox');
				$('body').append(box);
				box.append(
					'<div class="popupmain">'
						+'<div class="popupheader"></div>'
						+'<div class="popupcontent"></div>'
						+'<div class="popupbtns"></div>'
						+'<div class="popupclose">×</div>'
					+'</div>'
					)

				// 设置box样式
				$(box).css({
					'position': 'fixed',
					// 'display': 'none',
					'z-index': options.zIndex+1
				})

				// 设置main
				$(box).find('.popupmain').css({
					'padding': '5px',
					'posiiton': 'relative'
				})

				// 设置header
				$(box).find('.popupheader').text(options.title)

				// 如果titleHide为true的时候，隐藏标题
				if(options.titleHide){
					$(box).find('.popupheader').hide();
				}
				
				// 设置关闭按钮事件
				$(box).on('click','.popupclose',function(){
					hideBox($(this));
				})
				$('.popupcover').click(function(){
					hideBox($(this));
				})
				// 设置content
				$(box).find('.popupcontent').html(options.content).css({
					'width': options.width,
					'height': options.height
				})

				// 设置btns
				$(box).find('.popupbtns').css({
					'text-align': 'right'
				})

				// 插入按钮
				if(options.buttons){
					var buttons = options.buttons;
					$.each(buttons,function(index,value){
						// console.log(index+':'+value);
						var newbtn = $('<input type="button"/>')
						$(box).find('.popupbtns').append(newbtn);
						$(newbtn).val(index).addClass('popupbtn')
					})
					// 设置按钮基本样式
					// $(ele).find('.popupbtns').css('padding','10px');
					$(box).find('.popupbtns input[type=button]').css('cursor','pointer');
				}
				// 设置按钮对应的点击事件
				$('.popupbtns').on('click','input',function(){
					var buttons = options.buttons;
					var indexValue = $(this).val();
					$.each(buttons,function(index,value){
						if(index == indexValue){
							value();
						}
					});
				})
				// 设置关闭按钮样式
				var header = $(box).find('.popupheader'),
					close = $(box).find('.popupclose');
				var closeT = options.titleHide 
						? '15px' 
						:  (parseInt(header.outerHeight()-close.outerHeight()))/2+parseInt($(box).css('padding')),
					closeL = options.titleHide 
						? '15px' 
						:  (parseInt(header.outerHeight()-close.outerHeight()))/2;
				$(box).find('.popupclose').css({
					'position': 'absolute',
					'cursor': 'pointer',
					'top': closeT,
					'right': closeL
				});
				// 当弹窗内容加载完成后设置居中
				$(box).css({
					'left': (winW-$(box).outerWidth())/2,
					'top': (winH-$(box).outerHeight())/2,
					'display': 'none'
				});
				
				// 显示遮罩层和弹窗
				if(options.autoOpen){
					showBox();
				}
			}

			function showBox(){
				$('.'+options.boxClass).fadeIn(100);
				$('.'+options.coverClass).fadeIn(100);
			}
			function hideBox(ele){
				$('.'+options.boxClass).fadeOut(100);
				$('.'+options.coverClass).fadeOut(100);
				if(ele){
					$(ele).parent().parent('.popupbox').fadeOut(100);
					$(ele).parent().parent().prev('.popupcover').fadeOut(100);
				}
			}
			function destoryBox(){
				$('.'+options.boxClass).remove();
				$('.'+options.coverClass).remove();
			}
		}
	})
})(jQuery)