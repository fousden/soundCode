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
		popup : function(params,keyname,keyvalue){
			var eles = $(this),
			    defaults,		//原事件默认传进来的参数
				options,		//合并后的参数
				nodeId,			//原元素id
				nodeCLass,		//原元素类名
				nodeContent,	//原元素内容
				box,			//新建的元素
				newOptions,		//未知
				handlers = {	//单个字符串参数对应的事件
					'open': function(){
						showBox();
					},
					'close': function(){
						hideBox();
					},	
					'destory': function(){
						destoryBox();
					}
				};
			// console.log(eles.length);
			$.each(eles,function(index,value){
				nodeId = $(eles[index]).attr('id'),
				nodeClass = $(eles[index]).attr('class');
				nodeContent = $(eles[index]).html();
				// console.log(nodeContent);
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
					content: nodeContent ? nodeContent : '请输入弹窗内容',
					coverAllowClose: false
				};
				function isCreated(){
					return $('.'+defaults.boxClass).length == 0 ? false : true;
				}
				
				if(!isCreated()){
					// 如果没有则创建新的
					// console.log('hasn\'t');
					options = $.extend(true,{},defaults,typeof params  == 'object' ? params : {} );
					creatNewBox();
				}else{
					var success;
					
					if(params == 'options'){
						$.each(defaults,function(index,value){
							if(keyname == index){
								console.log('keyname:'+keyname)
								console.log('keyvalue:'+keyvalue)
								defaults[keyname] = keyvalue;
								newOptions = $.extend(true,{},defaults);
								console.log(newOptions);
								var cutBox = $('.popup'+nodeId);
								resetBox(cutBox);
								
								// options = $.extend(true,{},defaults);
							}
						})

						// console.log(options)
					}else{
						$.each(handlers,function(index,value){
							if(params == index){
								// console.log(value);
								success = value;
							}
						})
						success();
					}
				}
			});
			function resetBox(cutBox){
				var editHandlers = {
					'title': function(){
						$(cutBox).find('.popupheader').html(keyvalue);
					},
					'titleHide': function(){
						if(keyvalue){
							$(cutBox).find('.popupheader').css({
								'display': 'none'
							})
						}else{
							$(cutBox).find('.popupheader').css({
								'display': 'block'
							})
						}
					},
					'content': function(){
						$(cutBox).find('.popupcontent').html(keyvalue);
					},
					'width': function(){
						$(cutBox).find('.popupcontent').css({
							'width': keyvalue
						})
					}
				}
				$.each(editHandlers,function(index,value){
					if(keyname == index){
						success = value;
					}
					success();
				})
			}
			function creatNewBox(){
				// 先删除原有的元素
				$(eles).remove();
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
				box = $('<div></div>');
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
					// 'padding': '5px',
					'posiiton': 'relative'
				})

				// 设置header
				$(box).find('.popupheader').text(options.title).css({
					'width': options.width
				});

				// 如果titleHide为true的时候，隐藏标题
				if(options.titleHide){
					$(box).find('.popupheader').hide();
				}
				
				// 设置关闭按钮事件
				$(box).on('click','.popupclose',function(){
					hideBox($(this));
				})
				if(options.coverAllowClose){
					$(box).find('.popupcover').click(function(){
						hideBox($(this));
					})
				}
				
				// 设置content     可以修改
				$(box).find('.popupcontent').html(options.content).css({
					'width': options.width,
					'height': options.height
				});
				$(box).find('.popupcontent').attr('id',nodeId).html(nodeContent);

				// 设置btns		可以修改
				$(box).find('.popupbtns').css({
					// 'text-align': 'right',
					'width': options.width
				})

				// 插入按钮			可以修改
				if(options.buttons){
					var buttons = options.buttons;
					$.each(buttons,function(index,value){
						// console.log(index+':'+value);
						var newbtn = $('<input type="button"/>')
						$(box).find('.popupbtns').append(newbtn);
						$(newbtn).val(index).addClass('popupbtn')
					})
					// 设置按钮基本样式
					$(box).find('.popupbtns input[type=button]').css('cursor','pointer');
				}
				// 设置按钮对应的点击事件	可以修改
				$('.popupbtns').on('click','input',function(){
					var buttons = options.buttons;
					var indexValue = $(this).val();
					$.each(buttons,function(index,value){
						if(index == indexValue){
							value();
						}
					});
				})
				// 设置关闭按钮样式		可以修改
				var header = $(box).find('.popupheader'),
					close = $(box).find('.popupclose');
				/*var closeT = options.titleHide
						? '15px'
						:  (parseInt(header.outerHeight()-close.outerHeight()))/2+parseInt($(box).css('padding')),
					closeL = options.titleHide
						? '15px'
						:  (parseInt(header.outerHeight()-close.outerHeight()))/2;*/
				$(box).find('.popupclose').css({
					'position': 'absolute',
					'cursor': 'pointer'
					// 'top': closeT,
					// 'right': closeL
				});
				// 当弹窗内容加载完成后设置居中		最后一步
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
				$('.'+defaults.boxClass).fadeIn(100);
				$('.'+defaults.coverClass).fadeIn(100);
			}
			function hideBox(ele){
				$('.'+defaults.boxClass).fadeOut(100);
				$('.'+defaults.coverClass).fadeOut(100);
				if(ele){
					$(ele).parent().parent('.popupbox').fadeOut(100);
					$(ele).parent().parent().prev('.popupcover').fadeOut(100);
				}
			}
			function destoryBox(){
				$('.'+defaults.boxClass).remove();
				$('.'+defaults.coverClass).remove();
			}
		}
	})
})(jQuery)