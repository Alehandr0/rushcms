/* 
	RSModal - создает модальные окна.
	
	Использование:
	
	$('body').RS_modalOpen({  // Открывает новое окно

		'content' : [ $('#content') | 'Some content' ],  // Контент, который будет помещен в модальное окно
		'max-width' : 450,  // Максимальная ширина окна
		'min-offset' : 50,  // Минимальный отступ от верхней части экрана
		'no_close_on_overlay_click' : false, // Если true, то нажатие на область вне окна не приведет к его закрытию
		'no_close_button' : false,  // Если true, то кнока «закрыть окно» (крестик) будет убрана из шапки окна
		'head' : 'Заголовок окна',  // Заголовок окна
		'id' : 'rs_modal_window_id',  // Id окна
		'buttons' : {  // Если содержит пустой объект, то ни одной кнопки выведено не будет
			
			'ok' : {
				'alias' : 'Тескт кнопки OK', 'disabled' : false  // Если disabled = true, то кнопка не выводится
			},
			
			'cancel' : {
				'alias' : 'Тескт кнопки Cancel', 'disabled' : false
			},
			
			'some_button' : {
				'alias' : 'Тескт кнопки Cancel', 'disabled' : false
			},
			
		},
		'onClick' : function(data){  // Событие «Клик по кнопке в подвале окна»
		
			// См. описание data ниже

		},
		'onOpen' : function(){  // Событие «Открытие окна»

		}
		'onClose' : function(){  // Событие «Закрытие окна»

		}

	});	
	
	===========================================================================
	
	$('#rs_modal_window_id.RS_modal_window').RS_modalClose();  // Закрывает окно с id rs_modal_window_id
	
	===========================================================================

	Описание data из onClick:
	
	data = {
		
		button_type : тип нажатой кнопки (ключи опции buttons: ok, cancel, some_button и т.д.),
		window_id : id окна,
		window : объект окна,
		content_wrap : объект контейнера для контента окна,
	
	};

*/

$(document).ready(function(){

	$('body').on('click', '.RS_modal_close',  function(event){
		
		var t = $(event.target);
		
		if(t.hasClass('RS_modal_close')){
			
			var o = $('.RS_modal_window:not(.disabled)'); 
			
			if(!o.hasClass('currently_close')) o.RS_modalClose();
		}
		
	});

});

$(window).resize(function(){
	
	var o = $('.RS_modal_window:not(.disabled)');

	if(o.hasClass('RS_modal_on')) o.RS_modalPlacing();

});

$.fn.RS_modalPlacing = function(){

	var o = $(this),
		cwo = o.find('.RS_modal_content_wrap'),
		co = cwo.find('.RS_modal_content'),
		wh = $(window).height(),
		h = cwo.outerHeight(),
		pt = pb = (wh - h)/2,
		mnofst = o.data('settings')['min-offset'] || 0;
		
	if(pt<mnofst) pt = pb = mnofst;
	else pb = 0;
	
	cwo.css({
		'padding-top' : pt+'px',
		'padding-bottom' : pb+'px',
	});	

}
$.fn.RS_modalClose = function(){
	
	if($(this).length>0){

		$(this).remove();
		$('body').removeClass('RS_body_fixed').css('padding-right', '0px');
	
	}

}

$.fn.RS_modalOpen = function(options){

	var settings = $.extend({
		
			'min-offset' : 50,
			'max-width' : 650,
			'content' : '',
			'head' : '',
			'buttons' : false,
			'id' : '',
			'onClick' : '',
			'onClose' : '',
			'onOpen' : '',
			'prev_window' : '',
			'no_close_on_overlay_click' : false,
			'no_close_button' : false,
		
		}, options),
		prev_window = $('body').find('.RS_modal_window:not(.disabled)') || false;
		
	if(prev_window.length>0){
		
		prev_window.addClass('disabled');
		
		settings.prev_window = prev_window;
		
	}

	$('body').addClass('RS_body_fixed')
			 .css('padding-right', getScrollBarWidth()+'px')
			 .append('<div class="RS_modal_window RS_modal_close"></div>')
			 
	var o = $('.RS_modal_window:last'),
		fixed_elements_for_corrections = $('.RS_modal_fixed_correction');
		
	fixed_elements_for_corrections.each(function(index){
		
		var fefc = $(this),
			cml = parseInt(fefc.css('margin-left')),
			correction = Math.floor(getScrollBarWidth()/2);
		
		if(fefc.css('position')=='fixed') fefc.css('margin-left', (cml-correction)+'px').data('RS_modal_correction', correction);

	});

	o.attr('id', settings.id);

	o.data('settings', settings)
	 .append(
	 '<div class="RS_modal_wrap">'+
	  '<div class="RS_modal_overlay RS_modal_close"></div>'+
	  '<div class="RS_modal_content_wrap RS_modal_close" data-id="'+settings.id+'">'+
	   '<div class="RS_modal_head"><span></span><div class="RS_modal_close"></div></div>'+
	   '<div class="RS_modal_content"></div>'+
	   '<div class="RS_modal_buttons"></div>'+
	  '</div>'+
	 '</div>')	
	 .css({
		 'margin-left' : '0px',
	  });
	  
	if(settings.no_close_on_overlay_click) o.find('.RS_modal_window, .RS_modal_overlay, .RS_modal_content_wrap').removeClass('RS_modal_close'); 
	if(settings.no_close_button) o.find('.RS_modal_head > .RS_modal_close').remove(); 
	 
	o.outerHeight();
	o.addClass('RS_modal_show RS_modal_on');
	
	var rsmcw_o = o.find('.RS_modal_content_wrap'),
		rsmc_o = rsmcw_o.find('.RS_modal_content'),
		rsmh_o = rsmcw_o.find('.RS_modal_head'),
		rsmb_o = rsmcw_o.find('.RS_modal_buttons'),
		max_width_rsmh = settings['max-width']-(parseInt(rsmh_o.css('padding-left')) + parseInt(rsmh_o.css('padding-right'))),
		max_width_rsmc = settings['max-width']-(parseInt(rsmc_o.css('padding-left')) + parseInt(rsmc_o.css('padding-right'))),
		max_width_rsmb = settings['max-width']-(parseInt(rsmb_o.css('padding-left')) + parseInt(rsmb_o.css('padding-right'))),
		buttons_html = '';
		
	if(jQuery.isPlainObject(settings.buttons)){
		
		for(var type in settings.buttons){
			
			var add_class = '';
			
			if(settings.buttons[type]['disabled']) add_class = ' disabled';
			
			buttons_html += '<div class="RS_modal_button'+add_class+'" data-type="'+type+'">'+settings.buttons[type]['alias']+'</div>';
			
		}
	
	}

	rsmh_o.css('max-width', max_width_rsmh + 'px').children('span').append(settings.head);
	rsmc_o.append(settings.content).css('max-width', max_width_rsmc + 'px');
	rsmb_o.css('max-width', max_width_rsmb + 'px').append(buttons_html);

	if(jQuery.isPlainObject(settings.buttons)){
		
		rsmb_o.children('.RS_modal_button:not(.disabled)').on('click',  function(event){

			if(typeof settings.onClick === 'function'){
				
				var bo = $(this),
					data = {
						
						'button_type' : bo.data('type'),
						'window_id' : settings.id,
						'window' : o,
						'content_wrap' : rsmcw_o,
						'prev_window' : prev_window,
						
					};
				
				settings.onClick(data);

				
			}
			
		});	
		
	}
	
	o.RS_modalPlacing();
	
	if(typeof settings.onClose === 'function') rsmcw_o.data('onClose_function', settings.onClose);
	
	if(typeof settings.onOpen === 'function'){
		
		var data = {
				
				'window' : o,
				'content_wrap' : rsmcw_o,
				'window_id' : settings.id,
				'prev_window' : prev_window,
				
			};
		
		settings.onOpen(data);

	}	
	
}

function getScrollBarWidth () {
	
	  var inner = document.createElement('p');
	  
	  inner.style.width = "100%";
	  inner.style.height = "200px";

	  var outer = document.createElement('div');
	  
	  outer.style.position = "absolute";
	  outer.style.top = "0px";
	  outer.style.left = "0px";
	  outer.style.visibility = "hidden";
	  outer.style.width = "200px";
	  outer.style.height = "150px";
	  outer.style.overflow = "hidden";
	  outer.appendChild(inner);

	  document.body.appendChild(outer);
	  
	  var w1 = inner.offsetWidth;
	  
	  outer.style.overflow = 'scroll';
	  
	  var w2 = inner.offsetWidth;
	  
	  if (w1 == w2) w2 = outer.clientWidth;

	  document.body.removeChild(outer);

	  return (w1 - w2);
	  
};